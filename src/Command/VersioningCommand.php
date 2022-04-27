<?php

namespace Svc\VersioningBundle\Command;

use Svc\VersioningBundle\Service\VersionHandling as ServiceVersionHandling;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class VersioningCommand extends Command
{
  protected static $defaultName = 'app:versioning';
  protected static $defaultDescription = 'Versioning application, prepare releasing to prod';

  protected function configure()
  {
    $this
      ->setDescription(self::$defaultDescription)
      ->addOption('major', null, InputOption::VALUE_NONE, 'Add major version')
      ->addOption('minor', 'm', InputOption::VALUE_NONE, 'Add minor version')
      ->addOption('patch', 'p', InputOption::VALUE_NONE, 'Add patch version')
      ->addOption('init', 'i', InputOption::VALUE_NONE, 'Init versioning (set to 0.0.1)')
      ->addArgument('commitMessage', InputArgument::OPTIONAL, 'Commit message');
  }

  private ServiceVersionHandling $versionHandling;
  private bool $run_deploy;
  private bool $run_git;
  private ?string $pre_command;

  public function __construct(bool $run_git, bool $run_deploy, ?string $pre_command)
  {
    parent::__construct();
    $this->versionHandling = new ServiceVersionHandling();
    $this->run_deploy = $run_deploy;
    $this->run_git = $run_git;
    $this->pre_command = $pre_command;
  }


  protected function execute(InputInterface $input, OutputInterface $output): int
  {

    $io = new SymfonyStyle($input, $output);


    if ($this->pre_command) {
      $io->writeln("Running pre command: " . $this->pre_command);
      system($this->pre_command, $res);
      if ($res>0) {
        $output->writeln('<error> Error during execution pre command. Versioning cannceled. </error>');
        return Command::FAILURE;
      }
    }

    return 1;

    $init = $input->getOption('init');

    if (!$init) {
      $version = $this->versionHandling->getCurrentVersion();
      $io->writeln("Current version: $version");
    } else {
      $io->writeln("Initializing versioning...");
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
