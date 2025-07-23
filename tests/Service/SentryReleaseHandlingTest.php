<?php

declare(strict_types=1);

/*
 * This file is part of the svc-versioning bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\VersioningBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\Service\SentryReleaseHandling;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class SentryReleaseHandlingTest extends TestCase
{
    private SentryReleaseHandling $sentryReleaseHandling;

    private string $tempDir;

    private string $originalDir;

    private SymfonyStyle $io;

    protected function setUp(): void
    {
        $this->originalDir = getcwd();
        $this->tempDir = sys_get_temp_dir() . '/svc_versioning_test_' . uniqid();
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/config');
        mkdir($this->tempDir . '/config/packages');
        chdir($this->tempDir);

        $this->sentryReleaseHandling = new SentryReleaseHandling();

        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        $this->io = new SymfonyStyle($input, $output);
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

    public function testWriteNewSentryReleaseWithBasicConfiguration(): void
    {
        $sentryConfig = [
            'sentry' => [
                'dsn' => 'https://example@sentry.io/123456',
                'options' => [
                    'environment' => 'prod',
                ],
            ],
        ];

        file_put_contents('config/packages/sentry.yaml', Yaml::dump($sentryConfig));

        $result = $this->sentryReleaseHandling->WriteNewSentryRelease('1.2.3', null, $this->io);

        $this->assertTrue($result);

        $updatedConfig = Yaml::parseFile('config/packages/sentry.yaml');
        $this->assertEquals('1.2.3', $updatedConfig['sentry']['options']['release']);
    }

    public function testWriteNewSentryReleaseWithAppName(): void
    {
        $sentryConfig = [
            'sentry' => [
                'dsn' => 'https://example@sentry.io/123456',
                'options' => [
                    'environment' => 'prod',
                ],
            ],
        ];

        file_put_contents('config/packages/sentry.yaml', Yaml::dump($sentryConfig));

        $result = $this->sentryReleaseHandling->WriteNewSentryRelease('1.2.3', 'MyApp', $this->io);

        $this->assertTrue($result);

        $updatedConfig = Yaml::parseFile('config/packages/sentry.yaml');
        $this->assertEquals('MyApp@1.2.3', $updatedConfig['sentry']['options']['release']);
    }

    public function testWriteNewSentryReleaseWithAppNameRemovesSpaces(): void
    {
        $sentryConfig = [
            'sentry' => [
                'dsn' => 'https://example@sentry.io/123456',
                'options' => [
                    'environment' => 'prod',
                ],
            ],
        ];

        file_put_contents('config/packages/sentry.yaml', Yaml::dump($sentryConfig));

        $result = $this->sentryReleaseHandling->WriteNewSentryRelease('1.2.3', 'My App Name', $this->io);

        $this->assertTrue($result);

        $updatedConfig = Yaml::parseFile('config/packages/sentry.yaml');
        $this->assertEquals('MyAppName@1.2.3', $updatedConfig['sentry']['options']['release']);
    }

    public function testWriteNewSentryReleaseWithEnvironmentSpecificConfig(): void
    {
        $sentryConfig = [
            'when@dev' => [
                'sentry' => [
                    'dsn' => 'https://dev@sentry.io/123456',
                    'options' => [
                        'environment' => 'dev',
                    ],
                ],
            ],
            'when@prod' => [
                'sentry' => [
                    'dsn' => 'https://prod@sentry.io/123456',
                    'options' => [
                        'environment' => 'prod',
                    ],
                ],
            ],
            'when@test' => [
                'sentry' => [
                    'dsn' => 'https://test@sentry.io/123456',
                    'options' => [
                        'environment' => 'test',
                    ],
                ],
            ],
        ];

        file_put_contents('config/packages/sentry.yaml', Yaml::dump($sentryConfig));

        $result = $this->sentryReleaseHandling->WriteNewSentryRelease('2.0.0', 'TestApp', $this->io);

        $this->assertTrue($result);

        $updatedConfig = Yaml::parseFile('config/packages/sentry.yaml');
        $this->assertEquals('TestApp@2.0.0', $updatedConfig['when@dev']['sentry']['options']['release']);
        $this->assertEquals('TestApp@2.0.0', $updatedConfig['when@prod']['sentry']['options']['release']);
        $this->assertEquals('TestApp@2.0.0', $updatedConfig['when@test']['sentry']['options']['release']);
    }

    public function testWriteNewSentryReleaseReturnsFalseWhenFileNotFound(): void
    {
        $result = $this->sentryReleaseHandling->WriteNewSentryRelease('1.0.0', null, $this->io);

        $this->assertFalse($result);
    }

    public function testWriteNewSentryReleaseReturnsFalseForInvalidYaml(): void
    {
        file_put_contents('config/packages/sentry.yaml', 'invalid: yaml: content: [');

        $result = $this->sentryReleaseHandling->WriteNewSentryRelease('1.0.0', null, $this->io);

        $this->assertFalse($result);
    }

    public function testWriteNewSentryReleaseHandlesPartialConfiguration(): void
    {
        $sentryConfig = [
            'when@prod' => [
                'sentry' => [
                    'dsn' => 'https://prod@sentry.io/123456',
                    'options' => [
                        'environment' => 'prod',
                    ],
                ],
            ],
            'sentry' => [
                'dsn' => 'https://example@sentry.io/123456',
            ],
        ];

        file_put_contents('config/packages/sentry.yaml', Yaml::dump($sentryConfig));

        $result = $this->sentryReleaseHandling->WriteNewSentryRelease('1.5.0', null, $this->io);

        $this->assertTrue($result);

        $updatedConfig = Yaml::parseFile('config/packages/sentry.yaml');
        $this->assertEquals('1.5.0', $updatedConfig['when@prod']['sentry']['options']['release']);
        $this->assertEquals('1.5.0', $updatedConfig['sentry']['options']['release']);
    }

    public function testWriteNewSentryReleaseDoesNotModifyNonExistentEnvironments(): void
    {
        $sentryConfig = [
            'when@prod' => [
                'sentry' => [
                    'dsn' => 'https://prod@sentry.io/123456',
                    'options' => [
                        'environment' => 'prod',
                    ],
                ],
            ],
        ];

        file_put_contents('config/packages/sentry.yaml', Yaml::dump($sentryConfig));

        $result = $this->sentryReleaseHandling->WriteNewSentryRelease('1.0.0', null, $this->io);

        $this->assertTrue($result);

        $updatedConfig = Yaml::parseFile('config/packages/sentry.yaml');
        $this->assertArrayNotHasKey('when@dev', $updatedConfig);
        $this->assertArrayNotHasKey('when@test', $updatedConfig);
        $this->assertEquals('1.0.0', $updatedConfig['when@prod']['sentry']['options']['release']);
    }
}
