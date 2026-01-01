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

use Svc\VersioningBundle\ValueObject\Version;

class VersionString
{
    /**
     * Parses a version string into a Version object.
     */
    public function parse(string $versionString): Version
    {
        return Version::fromString($versionString);
    }

    /**
     * Returns the initial version "0.0.1".
     */
    public function getInitial(): Version
    {
        return Version::initial();
    }

    /**
     * Converts a Version object to a string.
     */
    public function toString(Version $version): string
    {
        return $version->toString();
    }
}
