<?php

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
  name: 'svc_versioning:new',
  description: 'Create a new application version, prepare and release it to prod.',
  hidden: false
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

  private readonly ServiceVersionHandling $versionHandling;

  private readonly SentryReleaseHandling $sentryReleaseHandling;

  public function __construct(
    private readonly bool $run_git,
    private readonly bool $run_deploy,
    private readonly ?string $pre_command,
    private readonly bool $createSentryRelease,
    private readonly ?string $sentryAppName
   ) {
    parent::__construct();
    $this->versionHandling = new ServiceVersionHandling();
    $this->sentryReleaseHandling = new SentryReleaseHandling();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    if ($this->pre_command) {
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
      $output->writeln('<error> Cannot write README.md </error>');
    }

    // create Sentry release
    if ($this->createSentryRelease) {
      if (!$this->sentryReleaseHandling->WriteNewSentryRelease($newVersion, $this->sentryAppName, $io)) {
        return Command::FAILURE;
      }
    }

    if ($this->run_git) {
      $res = shell_exec('git add .');
      $res = shell_exec('git commit -S -m "' . $commitMessage . '"');
      $res = shell_exec('git push');
      $res = shell_exec('git tag -a -s v' . $newVersion . ' -m "' . $commitMessage . '"');
      $res = shell_exec('git push origin v' . $newVersion);
    }

    // runs easycorp/easy-deploy-bundle
    if ($this->run_deploy) {
      $deployCommand = $this->getApplication()->find('deploy');
      $emptyInput = new ArrayInput([]);
      $returnCode = $deployCommand->run($emptyInput, $output);
    }

    return Command::SUCCESS;
  }
}
