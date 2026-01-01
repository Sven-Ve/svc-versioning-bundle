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
use Svc\VersioningBundle\Service\CacheClearCheck;

class CacheClearCheckTest extends TestCase
{
    private CacheClearCheck $cacheClearCheck;

    private string $testCacheDir;

    protected function setUp(): void
    {
        $this->cacheClearCheck = new CacheClearCheck();
        $this->testCacheDir = sys_get_temp_dir() . '/svc_versioning_test_cache_' . uniqid();
    }

    protected function tearDown(): void
    {
        // Cleanup test cache directory if it exists
        if (is_dir($this->testCacheDir)) {
            $this->recursiveRemove($this->testCacheDir);
        }
    }

    public function testCheckProductionCacheClearReturnsArray(): void
    {
        $result = $this->cacheClearCheck->checkProductionCacheClear(false);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('output', $result);
        $this->assertArrayHasKey('error_output', $result);
    }

    public function testCheckProductionCacheClearWithoutCleanup(): void
    {
        $result = $this->cacheClearCheck->checkProductionCacheClear(false);

        // We expect this to potentially fail in test environment (no Symfony app)
        // but the structure should be correct
        $this->assertArrayHasKey('success', $result);
    }

    public function testRemoveCacheDirectoryRemovesEmptyDirectory(): void
    {
        // Create test directory structure
        mkdir($this->testCacheDir, 0777, true);
        $this->assertTrue(is_dir($this->testCacheDir));

        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->cacheClearCheck);
        $method = $reflection->getMethod('removeCacheDirectory');

        // Remove directory
        $method->invoke($this->cacheClearCheck, $this->testCacheDir);

        $this->assertFalse(is_dir($this->testCacheDir));
    }

    public function testRemoveCacheDirectoryRemovesDirectoryWithFiles(): void
    {
        // Create test directory structure with files
        mkdir($this->testCacheDir, 0777, true);
        file_put_contents($this->testCacheDir . '/test1.txt', 'test content');
        file_put_contents($this->testCacheDir . '/test2.txt', 'test content');

        $subDir = $this->testCacheDir . '/subdir';
        mkdir($subDir, 0777, true);
        file_put_contents($subDir . '/test3.txt', 'test content');

        $this->assertTrue(is_dir($this->testCacheDir));
        $this->assertTrue(is_dir($subDir));

        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->cacheClearCheck);
        $method = $reflection->getMethod('removeCacheDirectory');

        // Remove directory
        $method->invoke($this->cacheClearCheck, $this->testCacheDir);

        $this->assertFalse(is_dir($this->testCacheDir));
        $this->assertFalse(is_dir($subDir));
    }

    public function testRemoveCacheDirectoryHandlesNonExistentDirectory(): void
    {
        $nonExistentDir = $this->testCacheDir . '/non_existent';

        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->cacheClearCheck);
        $method = $reflection->getMethod('removeCacheDirectory');

        // Should not throw exception
        $method->invoke($this->cacheClearCheck, $nonExistentDir);

        $this->assertFalse(is_dir($nonExistentDir));
    }

    private function recursiveRemove(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->recursiveRemove($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
