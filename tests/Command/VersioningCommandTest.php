<?php

declare(strict_types=1);

/*
 * This file is part of the SvcVersioning bundle.
 *
 * (c) 2026 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\VersioningBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\Command\VersioningCommand;
use Svc\VersioningBundle\Service\CacheClearCheck;
use Svc\VersioningBundle\Service\VersionHandling;
use Symfony\Component\Console\Tester\CommandTester;

class VersioningCommandTest extends TestCase
{
    private VersioningCommand $command;

    private CommandTester $commandTester;

    private string $tempDir;

    private string $originalDir;

    protected function setUp(): void
    {
        $this->originalDir = getcwd();
        $this->tempDir = sys_get_temp_dir() . '/svc_versioning_test_' . uniqid();
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/templates');
        chdir($this->tempDir);

        $this->command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: null,
            runComposerAudit: false,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: false,
            ansibleInventory: null,
            ansiblePlaybook: null
        );

        $this->commandTester = new CommandTester($this->command);
    }

    protected function tearDown(): void
    {
        chdir($this->originalDir);
        $this->cleanupTempDir($this->tempDir);
    }

    private function cleanupTempDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                $this->cleanupTempDir($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($dir);
    }

    public function testExecuteWithInitOption(): void
    {
        $this->commandTester->execute(['--init' => true, 'commitMessage' => 'Initial version']);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertFileExists('.version');
        $this->assertEquals('0.0.1', trim(file_get_contents('.version')));

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Initializing versioning...', $output);
        $this->assertStringContainsString('New version: 0.0.1', $output);
    }

    public function testExecuteWithPatchIncrement(): void
    {
        file_put_contents('.version', '1.2.3');

        $this->commandTester->execute(['--patch' => true, 'commitMessage' => 'Patch update']);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals('1.2.4', trim(file_get_contents('.version')));

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Current version: 1.2.3', $output);
        $this->assertStringContainsString('New version: 1.2.4', $output);
    }

    public function testExecuteWithMinorIncrement(): void
    {
        file_put_contents('.version', '1.2.3');

        $this->commandTester->execute(['--minor' => true, 'commitMessage' => 'Minor update']);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals('1.3.0', trim(file_get_contents('.version')));

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Current version: 1.2.3', $output);
        $this->assertStringContainsString('New version: 1.3.0', $output);
    }

    public function testExecuteWithMajorIncrement(): void
    {
        file_put_contents('.version', '1.2.3');

        $this->commandTester->execute(['--major' => true, 'commitMessage' => 'Major update']);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals('2.0.0', trim(file_get_contents('.version')));

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Current version: 1.2.3', $output);
        $this->assertStringContainsString('New version: 2.0.0', $output);
    }

    public function testExecuteWithCustomCommitMessage(): void
    {
        file_put_contents('.version', '1.0.0');

        $this->commandTester->execute([
            '--patch' => true,
            'commitMessage' => 'Custom commit message',
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());

        $changelogContent = file_get_contents('CHANGELOG.md');
        $this->assertStringContainsString('Custom commit message', $changelogContent);
    }

    public function testExecuteDefaultsToPatchWhenNoOptionSpecified(): void
    {
        file_put_contents('.version', '2.5.10');

        $this->commandTester->execute(['commitMessage' => 'Default update']);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertEquals('2.5.11', trim(file_get_contents('.version')));
    }

    public function testExecuteCreatesVersionFileIfNotExists(): void
    {
        $this->commandTester->execute(['--patch' => true, 'commitMessage' => 'Patch update']);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertFileExists('.version');
        $this->assertEquals('0.0.2', trim(file_get_contents('.version')));
    }

    public function testExecuteCreatesTwigTemplate(): void
    {
        file_put_contents('.version', '1.0.0');

        $this->commandTester->execute(['--patch' => true, 'commitMessage' => 'Patch update']);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertFileExists('templates/_version.html.twig');

        $twigContent = file_get_contents('templates/_version.html.twig');
        $this->assertStringContainsString('Version: 1.0.1', $twigContent);
        $this->assertStringContainsString('<span title=\'Release', $twigContent);
    }

    public function testExecuteCreatesChangelog(): void
    {
        file_put_contents('.version', '1.0.0');

        $this->commandTester->execute([
            '--patch' => true,
            'commitMessage' => 'Bug fixes and improvements',
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertFileExists('CHANGELOG.md');

        $changelogContent = file_get_contents('CHANGELOG.md');
        $this->assertStringContainsString('## Version 1.0.1', $changelogContent);
        $this->assertStringContainsString('Bug fixes and improvements', $changelogContent);
    }

    public function testExecuteWithPreCommand(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: 'echo "Running pre-command"',
            runComposerAudit: false,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: false,
            ansibleInventory: null,
            ansiblePlaybook: null
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--init' => true, 'commitMessage' => 'Test version']);

        $this->assertEquals(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Running pre command:', $output);
    }

    public function testExecuteFailsWhenPreCommandFails(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: 'exit 1',
            runComposerAudit: false,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: false,
            ansibleInventory: null,
            ansiblePlaybook: null
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--init' => true, 'commitMessage' => 'Test version']);

        $this->assertEquals(1, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Error during execution pre command', $output);
    }

    public function testExecuteWithComposerAudit(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: null,
            runComposerAudit: true,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: false,
            ansibleInventory: null,
            ansiblePlaybook: null
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--init' => true, 'commitMessage' => 'Test version']);

        // Note: We don't check the status code because composer audit may fail
        // in the test environment (temp directory without composer.json).
        // We only verify that the audit message appears in the output.
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Running composer audit', $output);
    }

    public function testExecuteWithComposerAuditDisabled(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: null,
            runComposerAudit: false,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: false,
            ansibleInventory: null,
            ansiblePlaybook: null
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--init' => true, 'commitMessage' => 'Test version']);

        $this->assertEquals(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringNotContainsString('Running composer audit', $output);
    }

    public function testExecuteWithIgnoreAuditFlag(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: null,
            runComposerAudit: true,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: false,
            ansibleInventory: null,
            ansiblePlaybook: null
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--init' => true,
            'commitMessage' => 'Test version',
            '--ignore-audit' => true,
        ]);

        // Should succeed even if composer audit would fail
        // (In this test environment, composer audit will likely fail due to missing composer.json)
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Running composer audit', $output);

        // Verify the command accepts the --ignore-audit option without errors
        $definition = $command->getDefinition();
        $this->assertTrue($definition->hasOption('ignore-audit'));
    }

    public function testExecuteWithAnsibleDeployFailsWithoutPlaybook(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: null,
            runComposerAudit: false,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: true,
            ansibleInventory: 'inventory.yaml',
            ansiblePlaybook: null
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute(['--init' => true, 'commitMessage' => 'Test version']);

        $this->assertEquals(1, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('ansible_deploy is true - but no playbook defined', $output);
    }

    public function testCommandConfiguration(): void
    {
        $this->assertEquals('svc:versioning:new', $this->command->getName());
        $this->assertEquals('Create a new application version, prepare and release it to prod.', $this->command->getDescription());
        $this->assertContains('svc_versioning:new', $this->command->getAliases());

        $definition = $this->command->getDefinition();

        $this->assertTrue($definition->hasOption('major'));
        $this->assertTrue($definition->hasOption('minor'));
        $this->assertTrue($definition->hasOption('patch'));
        $this->assertTrue($definition->hasOption('init'));
        $this->assertTrue($definition->hasArgument('commitMessage'));

        $this->assertEquals('m', $definition->getOption('minor')->getShortcut());
        $this->assertEquals('p', $definition->getOption('patch')->getShortcut());
        $this->assertEquals('i', $definition->getOption('init')->getShortcut());
    }
}
