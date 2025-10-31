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

namespace Svc\VersioningBundle\Service;

class VersionString
{
    private const VERSION_SEPARATOR = '.';

    /**
     * @return array{major: int, minor: int, patch: int}
     */
    public function versionStringToVersionArray(string $versionString): array
    {
        $versionArray = $this->parser($versionString);

        return [
            'major' => intval($versionArray[0]),
            'minor' => intval($versionArray[1]),
            'patch' => intval($versionArray[2]),
        ];
    }

    public function getInitial(): string
    {
        $format = '0%s0%s1';
        $ret = sprintf($format, self::VERSION_SEPARATOR, self::VERSION_SEPARATOR);

        return $ret;
    }

    /**
     * @param array{major: int, minor: int, patch: int} $versionArray
     */
    public function versionArrayToVersionString(array $versionArray): string
    {
        return implode(self::VERSION_SEPARATOR, $versionArray);
    }

    /**
     * convert version string (1.2.3) in an array.
     *
     * @return list<string>
     */
    private function parser(string $version): array
    {
        $parts = explode('.', $version);

        return $parts;
    }
}
