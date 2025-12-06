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

use Svc\VersioningBundle\Service\CacheClearCheck;
use Svc\VersioningBundle\Service\VersionHandling;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'svc:versioning:new',
    description: 'Create a new application version, prepare and release it to prod.',
    hidden: false,
    aliases: ['svc_versioning:new'],
)]
class VersioningCommand extends Command
{
    public function __construct(
        private readonly VersionHandling $versionHandling,
        private readonly CacheClearCheck $cacheClearCheck,
        private readonly bool $run_git,
        private readonly bool $run_deploy,
        private readonly ?string $pre_command,
        private readonly bool $checkCacheClear,
        private readonly bool $cleanupCacheDir,
        private readonly ?string $deployCommand,
        private readonly bool $ansibleDeploy,
        private readonly ?string $ansibleInventory,
        private readonly ?string $ansiblePlaybook,
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument(name: 'commitMessage', description: 'Commit message')]
        #[Ask('Please enter the commit message:')]
        string $commitMessage,
        #[Option(shortcut: null, description: 'Add major version')] bool $major = false,
        #[Option(shortcut: 'm', description: 'Add minor version')] bool $minor = false,
        #[Option(shortcut: 'p', description: 'Add patch version')] bool $patch = false,
        #[Option(shortcut: 'i', description: 'Init versioning (set to 0.0.1)')] bool $init = false,
    ): int {

        if ($this->pre_command) {
            $this->validateCommand($this->pre_command);
            $io->writeln('Running pre command: ' . $this->pre_command);
            system($this->pre_command, $res);
            if ($res > 0) {
                $io->writeln('<error> Error during execution pre command. Versioning canceled. </error>');

                return Command::FAILURE;
            }
        }

        // Check production cache clear
        if ($this->checkCacheClear) {
            $io->writeln('Checking production cache clear...');
            $cacheResult = $this->cacheClearCheck->checkProductionCacheClear($this->cleanupCacheDir);

            if (!$cacheResult['success']) {
                $io->writeln('<error> Error during production cache clear. Versioning canceled. </error>');
                $io->writeln('<error> ' . $cacheResult['error_output'] . ' </error>');

                return Command::FAILURE;
            }

            $io->writeln('<info>Production cache cleared successfully.</info>');
            if ($this->cleanupCacheDir) {
                $io->writeln('<info>Cache directory var/cache/prod has been cleaned up.</info>');
            }
        }

        if (!$init) {
            $version = $this->versionHandling->getCurrentVersion();
            $io->writeln("Current version: $version");
        } else {
            $io->writeln('Initializing versioning...');
            $version = null;
        }

        $newVersion = $this->versionHandling->getNewVersion(
            $version,
            $major,
            $minor,
            $patch,
            $init
        );
        $io->writeln("<info>New version: $newVersion</info>");

        if (!$this->versionHandling->writeTwigFile('templates/_version.html.twig', $newVersion)) {
            $io->writeln('<error> Cannot write template file. </error>');
        }

        if (!$this->versionHandling->appendCHANGELOG('CHANGELOG.md', $newVersion, $commitMessage)) {
            $io->writeln('<error> Cannot write CHANGELOG.md </error>');
        }

        if ($this->run_git) {
            $escapedMessage = escapeshellarg($commitMessage);
            $escapedVersion = escapeshellarg('v' . $newVersion);

            exec('git add .', $execOutput, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git add failed');

                return Command::FAILURE;
            }

            exec("git commit -S -m $escapedMessage", $execOutput, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git commit failed');

                return Command::FAILURE;
            }

            exec('git push', $execOutput, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git push failed');

                return Command::FAILURE;
            }

            exec("git tag -a -s $escapedVersion -m $escapedMessage", $execOutput, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git tag creation failed');

                return Command::FAILURE;
            }

            exec("git push origin $escapedVersion", $execOutput, $returnCode);
            if ($returnCode !== 0) {
                $io->error('Git push tag failed');

                return Command::FAILURE;
            }
        }

        // runs easycorp/easy-deploy-bundle
        if ($this->run_deploy && $this->deployCommand === null) {
            $deployCommand = $this->getApplication()->find('deploy');
            $emptyInput = new ArrayInput([]);
            $returnCode = $deployCommand->run($emptyInput, $io);
        }

        // runs other deploy commands
        if ($this->deployCommand) {
            $this->validateCommand($this->deployCommand);
            $io->writeln('Running deploy command: ' . $this->deployCommand);
            system($this->deployCommand, $res);
            if ($res > 0) {
                $io->writeln('<error> Error during execution deploy command. Versioning canceled. </error>');

                return Command::FAILURE;
            }
        }

        // run ansible deploy
        if ($this->ansibleDeploy) {
            if (!$this->ansiblePlaybook) {
                $io->writeln('<error> ansible_deploy is true - but no playbook defined (parameter ansible_playbook). </error>');

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
                $io->writeln('<error> Error during execution ansible deploy command. Versioning canceled. </error>');

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
