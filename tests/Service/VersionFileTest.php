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

namespace Svc\VersioningBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\Service\VersionFile;

class VersionFileTest extends TestCase
{
    private VersionFile $versionFile;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->versionFile = new VersionFile();
        $this->tempDir = sys_get_temp_dir() . '/svc_versioning_test_' . uniqid();
        mkdir($this->tempDir);
        $this->versionFile->setPath($this->tempDir);
    }

    protected function tearDown(): void
    {
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

    public function testSetPath(): void
    {
        $newPath = '/custom/path';
        $this->versionFile->setPath($newPath);

        $filename = $this->versionFile->getFilename();

        $this->assertEquals($newPath . DIRECTORY_SEPARATOR . '.version', $filename);
    }

    public function testGetFilename(): void
    {
        $filename = $this->versionFile->getFilename();

        $expectedFilename = $this->tempDir . DIRECTORY_SEPARATOR . '.version';
        $this->assertEquals($expectedFilename, $filename);
    }

    public function testIsValidReturnsTrueForExistingFile(): void
    {
        $filename = $this->versionFile->getFilename();
        file_put_contents($filename, '1.0.0');

        $result = $this->versionFile->isValid($filename);

        $this->assertTrue($result);
    }

    public function testIsValidReturnsFalseForNonExistentFile(): void
    {
        $filename = $this->tempDir . '/nonexistent.version';

        $result = $this->versionFile->isValid($filename);

        $this->assertFalse($result);
    }

    public function testWriteAndRead(): void
    {
        $filename = $this->versionFile->getFilename();
        $content = '2.5.10';

        $writeResult = $this->versionFile->write($filename, $content);
        $this->assertTrue($writeResult);

        $readResult = $this->versionFile->read($filename);
        $this->assertEquals($content, $readResult);
    }

    public function testWriteWithAppend(): void
    {
        $filename = $this->versionFile->getFilename();
        $firstContent = 'First line';
        $secondContent = 'Second line';

        $this->versionFile->write($filename, $firstContent);
        $writeResult = $this->versionFile->write($filename, $secondContent, true);

        $this->assertTrue($writeResult);

        $readResult = $this->versionFile->read($filename);
        $this->assertEquals($firstContent . $secondContent, $readResult);
    }

    public function testWriteOverwrite(): void
    {
        $filename = $this->versionFile->getFilename();
        $firstContent = 'First content';
        $secondContent = 'Second content';

        $this->versionFile->write($filename, $firstContent);
        $writeResult = $this->versionFile->write($filename, $secondContent, false);

        $this->assertTrue($writeResult);

        $readResult = $this->versionFile->read($filename);
        $this->assertEquals($secondContent, $readResult);
    }

    public function testReadTrimsWhitespace(): void
    {
        $filename = $this->versionFile->getFilename();
        $contentWithWhitespace = "  1.2.3  \n\t";

        $this->versionFile->write($filename, $contentWithWhitespace);
        $readResult = $this->versionFile->read($filename);

        $this->assertEquals('1.2.3', $readResult);
    }

    public function testWriteReturnsFalseForInvalidPath(): void
    {
        $invalidFilename = '/invalid/path/that/does/not/exist/.version';

        $result = $this->versionFile->write($invalidFilename, '1.0.0');

        $this->assertFalse($result);
    }

    public function testReadThrowsExceptionForNonExistentFile(): void
    {
        $nonExistentFile = $this->tempDir . '/nonexistent.version';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Cannot read version file: $nonExistentFile");

        $this->versionFile->read($nonExistentFile);
    }

    public function testReadThrowsExceptionForUnreadableFile(): void
    {
        $filename = $this->versionFile->getFilename();
        file_put_contents($filename, '1.0.0');
        chmod($filename, 0000); // Make file unreadable

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Cannot read version file: $filename");

        try {
            $this->versionFile->read($filename);
        } finally {
            // Cleanup: restore permissions
            chmod($filename, 0644);
        }
    }
}
