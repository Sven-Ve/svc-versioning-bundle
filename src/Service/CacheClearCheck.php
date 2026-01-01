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

namespace Svc\VersioningBundle\Service;

/**
 * @internal
 */
class CacheClearCheck
{
    /**
     * Check if production cache can be cleared without errors.
     *
     * @param bool $cleanup Whether to delete the cache directory after successful check
     *
     * @return array{success: bool, output: string, error_output: string}
     */
    public function checkProductionCacheClear(bool $cleanup = false): array
    {
        $command = 'bin/console cache:clear --env=prod --no-debug 2>&1';
        $output = [];
        $returnCode = 0;

        exec($command, $output, $returnCode);

        $outputString = implode("\n", $output);
        $success = $returnCode === 0;

        $result = [
            'success' => $success,
            'output' => $outputString,
            'error_output' => $success ? '' : $outputString,
        ];

        // Cleanup cache directory if requested and check was successful
        if ($success && $cleanup) {
            $cacheDir = 'var/cache/prod';
            if (is_dir($cacheDir)) {
                $this->removeCacheDirectory($cacheDir);
            }
        }

        return $result;
    }

    /**
     * Recursively remove cache directory.
     */
    private function removeCacheDirectory(string $dir): void
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
                $this->removeCacheDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
