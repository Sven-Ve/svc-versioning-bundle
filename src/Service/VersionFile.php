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

class VersionFile
{
    private const VERSION_FILE = '.version';

    private string $path = '.';

    /**
     * set the path of the config file.
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * get the config file name.
     */
    public function getFilename(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . self::VERSION_FILE;
    }

    public function isValid(string $filename): bool
    {
        return is_readable($filename);
    }

    /**
     * Read version from file.
     *
     * @throws \RuntimeException if file cannot be read
     */
    public function read(string $filename): string
    {
        $buffer = @file_get_contents($filename);

        if ($buffer === false) {
            throw new \RuntimeException("Cannot read version file: $filename");
        }

        return trim($buffer);
    }

    /**
     * write/append text in a file.
     *
     * @param string $buffer the message text
     */
    public function write(string $filename, string $buffer, bool $append = false): bool
    {
        $flag = $append ? FILE_APPEND : 0;

        return @file_put_contents($filename, $buffer, $flag) !== false;
    }
}
