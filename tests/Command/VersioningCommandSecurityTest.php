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

namespace Svc\VersioningBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\Command\VersioningCommand;
use Svc\VersioningBundle\Service\CacheClearCheck;
use Svc\VersioningBundle\Service\VersionHandling;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Security tests for VersioningCommand to verify protection against command injection.
 */
class VersioningCommandSecurityTest extends TestCase
{
    private string $tempDir;

    private string $originalDir;

    protected function setUp(): void
    {
        $this->originalDir = getcwd();
        $this->tempDir = sys_get_temp_dir() . '/svc_versioning_security_test_' . uniqid();
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/templates');
        chdir($this->tempDir);
        file_put_contents('.version', '1.0.0');
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

    public function testPreCommandWithDangerousCharactersThrowsException(): void
    {
        $dangerousCommands = [
            'phpstan; rm -rf /',
            'phpstan && cat /etc/passwd',
            'phpstan | grep secret',
            'phpstan `whoami`',
            'phpstan $(cat /etc/passwd)',
            'phpstan < /etc/passwd',
            'phpstan > /tmp/output',
        ];

        foreach ($dangerousCommands as $dangerousCommand) {
            $command = new VersioningCommand(
                versionHandling: new VersionHandling(),
                cacheClearCheck: new CacheClearCheck(),
                run_git: false,
                run_deploy: false,
                pre_command: $dangerousCommand,
                checkCacheClear: false,
                cleanupCacheDir: false,
                deployCommand: null,
                ansibleDeploy: false,
                ansibleInventory: null,
                ansiblePlaybook: null
            );

            $commandTester = new CommandTester($command);

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Command contains potentially unsafe characters');

            $commandTester->execute(['--init' => true]);
        }
    }

    public function testDeployCommandWithDangerousCharactersThrowsException(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: null,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: 'deploy.sh; rm -rf /',
            ansibleDeploy: false,
            ansibleInventory: null,
            ansiblePlaybook: null
        );

        $commandTester = new CommandTester($command);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Command contains potentially unsafe characters');

        $commandTester->execute(['--init' => true]);
    }

    public function testAnsibleInventoryWithDangerousCharactersThrowsException(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: null,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: true,
            ansibleInventory: 'inventory.yaml; rm -rf /',
            ansiblePlaybook: 'deploy.yml'
        );

        $commandTester = new CommandTester($command);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File path contains potentially unsafe characters');

        $commandTester->execute(['--init' => true]);
    }

    public function testAnsiblePlaybookWithDangerousCharactersThrowsException(): void
    {
        $command = new VersioningCommand(
            versionHandling: new VersionHandling(),
            cacheClearCheck: new CacheClearCheck(),
            run_git: false,
            run_deploy: false,
            pre_command: null,
            checkCacheClear: false,
            cleanupCacheDir: false,
            deployCommand: null,
            ansibleDeploy: true,
            ansibleInventory: null,
            ansiblePlaybook: 'deploy.yml && whoami'
        );

        $commandTester = new CommandTester($command);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File path contains potentially unsafe characters');

        $commandTester->execute(['--init' => true]);
    }

    public function testSafeCommandsAreAccepted(): void
    {
        $safeCommands = [
            'composer run-script phpstan',
            'vendor/bin/phpunit',
            './bin/console app:validate',
            'npm run test',
        ];

        foreach ($safeCommands as $safeCommand) {
            $command = new VersioningCommand(
                versionHandling: new VersionHandling(),
                cacheClearCheck: new CacheClearCheck(),
                run_git: false,
                run_deploy: false,
                pre_command: $safeCommand,
                checkCacheClear: false,
                cleanupCacheDir: false,
                deployCommand: null,
                ansibleDeploy: false,
                ansibleInventory: null,
                ansiblePlaybook: null
            );

            $commandTester = new CommandTester($command);

            // Should not throw validation exception
            // Note: The command itself may fail, but validation should pass
            try {
                $commandTester->execute(['--init' => true]);
                // Validation passed - command executed (even if it failed)
                $this->addToAssertionCount(1);
            } catch (\InvalidArgumentException $e) {
                if (str_contains($e->getMessage(), 'unsafe characters')) {
                    $this->fail("Safe command should pass validation: $safeCommand");
                }
                // Other InvalidArgumentExceptions are ok
                $this->addToAssertionCount(1);
            } catch (\Exception) {
                // Other exceptions (command execution failures) are ok
                $this->addToAssertionCount(1);
            }
        }
    }
}
