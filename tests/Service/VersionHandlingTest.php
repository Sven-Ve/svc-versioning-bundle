<?php

declare(strict_types=1);

namespace Svc\VersioningBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\Service\VersionHandling;

class VersionHandlingTest extends TestCase
{
    private VersionHandling $versionHandling;
    private string $tempDir;
    private string $originalDir;

    protected function setUp(): void
    {
        $this->originalDir = getcwd();
        $this->tempDir = sys_get_temp_dir() . '/svc_versioning_test_' . uniqid();
        mkdir($this->tempDir);
        chdir($this->tempDir);
        $this->versionHandling = new VersionHandling();
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

    public function testGetNewVersionWithInit(): void
    {
        $result = $this->versionHandling->getNewVersion('', false, false, false, true);
        
        $this->assertEquals('0.0.1', $result);
        $this->assertFileExists('.version');
        $this->assertEquals('0.0.1', trim(file_get_contents('.version')));
    }

    public function testGetNewVersionPatchIncrement(): void
    {
        file_put_contents('.version', '1.2.3');
        
        $result = $this->versionHandling->getNewVersion('1.2.3', false, false, true);
        
        $this->assertEquals('1.2.4', $result);
        $this->assertEquals('1.2.4', trim(file_get_contents('.version')));
    }

    public function testGetNewVersionMinorIncrement(): void
    {
        file_put_contents('.version', '1.2.3');
        
        $result = $this->versionHandling->getNewVersion('1.2.3', false, true, false);
        
        $this->assertEquals('1.3.0', $result);
        $this->assertEquals('1.3.0', trim(file_get_contents('.version')));
    }

    public function testGetNewVersionMajorIncrement(): void
    {
        file_put_contents('.version', '1.2.3');
        
        $result = $this->versionHandling->getNewVersion('1.2.3', true, false, false);
        
        $this->assertEquals('2.0.0', $result);
        $this->assertEquals('2.0.0', trim(file_get_contents('.version')));
    }

    public function testGetNewVersionDefaultsToPatchWhenNoFlagsSet(): void
    {
        file_put_contents('.version', '1.2.3');
        
        $result = $this->versionHandling->getNewVersion('1.2.3', false, false, false);
        
        $this->assertEquals('1.2.4', $result);
    }

    public function testGetNewVersionUsesCurrentVersionWhenVersionIsEmpty(): void
    {
        file_put_contents('.version', '2.5.10');
        
        $result = $this->versionHandling->getNewVersion('', false, false, true);
        
        $this->assertEquals('2.5.11', $result);
    }

    public function testGetCurrentVersionFromExistingFile(): void
    {
        file_put_contents('.version', '3.14.159');
        
        $result = $this->versionHandling->getCurrentVersion();
        
        $this->assertEquals('3.14.159', $result);
    }

    public function testGetCurrentVersionCreatesInitialVersionWhenFileDoesNotExist(): void
    {
        $result = $this->versionHandling->getCurrentVersion();
        
        $this->assertEquals('0.0.1', $result);
        $this->assertFileExists('.version');
        $this->assertEquals('0.0.1', trim(file_get_contents('.version')));
    }

    public function testWriteTwigFile(): void
    {
        $templateDir = $this->tempDir . '/templates';
        mkdir($templateDir);
        $templateFile = 'templates/_version.html.twig';
        $version = '1.2.3';
        
        $result = $this->versionHandling->writeTwigFile($templateFile, $version);
        
        $this->assertTrue($result);
        $this->assertFileExists($templateFile);
        
        $content = file_get_contents($templateFile);
        $this->assertStringContainsString('Version: 1.2.3', $content);
        $this->assertStringContainsString('<span title=\'Release', $content);
        $this->assertStringContainsString('</span>', $content);
    }

    public function testWriteTwigFileReturnsFalseForInvalidPath(): void
    {
        $invalidPath = '/invalid/path/that/does/not/exist/template.twig';
        
        $result = $this->versionHandling->writeTwigFile($invalidPath, '1.0.0');
        
        $this->assertFalse($result);
    }

    public function testAppendCHANGELOG(): void
    {
        $changelogFile = 'CHANGELOG.md';
        $version = '2.1.0';
        $message = 'Added new features';
        
        $result = $this->versionHandling->appendCHANGELOG($changelogFile, $version, $message);
        
        $this->assertTrue($result);
        $this->assertFileExists($changelogFile);
        
        $content = file_get_contents($changelogFile);
        $this->assertStringContainsString('## Version 2.1.0', $content);
        $this->assertStringContainsString('- Added new features', $content);
        $this->assertMatchesRegularExpression('/\*[A-Za-z]{3}, \d{2} [A-Za-z]{3} \d{4}/', $content);
    }

    public function testAppendCHANGELOGAppendsToExistingFile(): void
    {
        $changelogFile = 'CHANGELOG.md';
        $existingContent = '# Changelog

## Version 1.0.0
- Initial release';
        
        file_put_contents($changelogFile, $existingContent);
        
        $result = $this->versionHandling->appendCHANGELOG($changelogFile, '1.1.0', 'Bug fixes');
        
        $this->assertTrue($result);
        
        $content = file_get_contents($changelogFile);
        $this->assertStringContainsString('- Initial release', $content);
        $this->assertStringContainsString('## Version 1.1.0', $content);
        $this->assertStringContainsString('- Bug fixes', $content);
    }

    public function testAppendCHANGELOGReturnsFalseForInvalidPath(): void
    {
        $invalidPath = '/invalid/path/that/does/not/exist/CHANGELOG.md';
        
        $result = $this->versionHandling->appendCHANGELOG($invalidPath, '1.0.0', 'Test');
        
        $this->assertFalse($result);
    }
}