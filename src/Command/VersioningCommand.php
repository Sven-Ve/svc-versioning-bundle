<?php

declare(strict_types=1);

/*
 * This file is part of the SvcVersioning bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\VersioningBundle\Command;

use Svc\VersioningBundle\Service\SentryReleaseHandling;
use Svc\VersioningBundle\Service\VersionHandling as ServiceVersionHandling;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'svc:versioning:new',
    description: 'Create a new application version, prepare and release it to prod.',
    hidden: false,
    aliases: ['svc_versioning:new'],
)]
class VersioningCommand extends Command
{
    protected function configure(): void
    {
        $this
          ->addOption('major', null, InputOption::VALUE_NONE, 'Add major version')
          ->addOption('minor', 'm', InputOption::VALUE_NONE, 'Add minor version')
          ->addOption('patch', 'p', InputOption::VALUE_NONE, 'Add patch version')
          ->addOption('init', 'i', InputOption::VALUE_NONE, 'Init versioning (set to 0.0.1)')
          ->addArgument('commitMessage', InputArgument::OPTIONAL, 'Commit message');
    }

    public function __construct(
        private readonly ServiceVersionHandling $versionHandling,
        private readonly SentryReleaseHandling $sentryReleaseHandling,
        private readonly bool $run_git,
        private readonly bool $run_deploy,
        private readonly ?string $pre_command,
        private readonly bool $createSentryRelease,
        private readonly ?string $sentryAppName,
        private readonly ?string $deployCommand,
        private readonly bool $ansibleDeploy,
        private readonly ?string $ansibleInventory,
        private readonly ?string $ansiblePlaybook,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->pre_command) {
            $this->validateCommand($this->pre_command);
            $io->writeln('Running pre command: ' . $this->pre_command);
            system($this->pre_command, $res);
            if ($res > 0) {
                $output->writeln('<error> Error during execution pre command. Versioning canceled. </error>');

                return Command::FAILURE;
            }
        }

        $init = $input->getOption('init');

        if (!$init) {
            $version = $this->versionHandling->getCurrentVersion();
            $io->writeln("Current version: $version");
        } else {
            $io->writeln('Initializing versioning...');
            $version = null;
        }

        $newVersion = $this->versionHandling->getNewVersion(
            $version,
            $input->getOption('major'),
            $input->getOption('minor'),
            $input->getOption('patch'),
            $input->getOption('init')
        );
        $io->writeln("New version: $newVersion");

        $commitMessage = $input->getArgument('commitMessage');
        if (!$commitMessage) {
            $commitMessage = "Increase version to $newVersion";
        }

        if (!$this->versionHandling->writeTwigFile('templates/_version.html.twig', $newVersion)) {
            $output->writeln('<error> Cannot write template file. </error>');
        }

        if (!$this->versionHandling->appendCHANGELOG('CHANGELOG.md', $newVersion, $commitMessage)) {
            $output->writeln('<error> Cannot write CHANGELOG.md </error>');
        }

        // create Sentry release
        if ($this->createSentryRelease) {
            if (!$this->sentryReleaseHandling->WriteNewSentryRelease($newVersion, $this->sentryAppName, $io)) {
                return Command::FAILURE;
            }
        }

        if ($this->run_git) {
            $escapedMessage = escapeshellarg($commitMessage);
            $escapedVersion = escapeshellarg('v' . $newVersion);

            exec('git add .', $output, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git add failed');

                return Command::FAILURE;
            }

            exec("git commit -S -m $escapedMessage", $output, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git commit failed');

                return Command::FAILURE;
            }

            exec('git push', $output, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git push failed');

                return Command::FAILURE;
            }

            exec("git tag -a -s $escapedVersion -m $escapedMessage", $output, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git tag creation failed');

                return Command::FAILURE;
            }

            exec("git push origin $escapedVersion", $output, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git push tag failed');

                return Command::FAILURE;
            }
        }

        // runs easycorp/easy-deploy-bundle
        if ($this->run_deploy && $this->deployCommand === null) {
            $deployCommand = $this->getApplication()->find('deploy');
            $emptyInput = new ArrayInput([]);
            $returnCode = $deployCommand->run($emptyInput, $output);
        }

        // runs other deploy commands
        if ($this->deployCommand) {
            $this->validateCommand($this->deployCommand);
            $io->writeln('Running deploy command: ' . $this->deployCommand);
            system($this->deployCommand, $res);
            if ($res > 0) {
                $output->writeln('<error> Error during execution deploy command. Versioning canceled. </error>');

                return Command::FAILURE;
            }
        }

        // run ansible deploy
        if ($this->ansibleDeploy) {
            if (!$this->ansiblePlaybook) {
                $output->writeln('<error> ansible_deploy is true - but no playbook defined (parameter ansible_playbook). </error>');

                return Command::FAILURE;
            }

            // Validate playbook and inventory paths
            if ($this->ansibleInventory) {
                $this->validateFilePath($this->ansibleInventory);
            }
            $this->validateFilePath($this->ansiblePlaybook);

            $ansibleCommand = 'ansible-playbook';
            if ($this->ansibleInventory) {
                $ansibleCommand .= ' -i ' . escapeshellarg($this->ansibleInventory);
            }
            $ansibleCommand .= ' ' . escapeshellarg($this->ansiblePlaybook);

            $io->writeln('Running ansible deploy command: ' . $ansibleCommand);
            system($ansibleCommand, $res);
            if ($res > 0) {
                $output->writeln('<error> Error during execution ansible deploy command. Versioning canceled. </error>');

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Validates that a command does not contain dangerous characters.
     *
     * @throws \InvalidArgumentException if command contains unsafe characters
     */
    private function validateCommand(string $command): void
    {
        // Check for command chaining and other shell injection patterns
        if (preg_match('/[;&|`$()<>]/', $command)) {
            throw new \InvalidArgumentException('Command contains potentially unsafe characters: ' . $command);
        }
    }

    /**
     * Validates that a file path does not contain dangerous characters.
     *
     * @throws \InvalidArgumentException if path contains unsafe characters
     */
    private function validateFilePath(string $path): void
    {
        // Check for path traversal and command injection
        if (preg_match('/[;&|`$()<>]/', $path)) {
            throw new \InvalidArgumentException('File path contains potentially unsafe characters: ' . $path);
        }
    }
}
