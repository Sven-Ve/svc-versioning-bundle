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

use Svc\VersioningBundle\ValueObject\Version;

class VersionHandling
{
    private VersionString $versionString;

    private VersionFile $versionFile;

    public function __construct()
    {
        $path = '.';
        $this->versionString = new VersionString();
        $this->versionFile = new VersionFile();
        $this->versionFile->setPath($path);
    }

    /**
     * get a new version.
     */
    public function getNewVersion(?string $version, bool $majorVer, bool $minorVer, bool $patchVer, bool $init = false): string
    {
        if ($init) {
            $newVersion = $this->versionString->getInitial();
        } else {
            if (!$version) {
                $version = $this->getCurrentVersion();
            }

            $currentVersion = $this->versionString->parse($version);

            if (!$majorVer && !$minorVer) {
                $patchVer = true;
            }

            $newVersion = match (true) {
                $majorVer => $currentVersion->incrementMajor(),
                $minorVer => $currentVersion->incrementMinor(),
                $patchVer => $currentVersion->incrementPatch(),
                default => $currentVersion,
            };
        }

        $fileName = $this->versionFile->getFilename();
        $this->versionFile->write($fileName, $newVersion->toString());

        return $newVersion->toString();
    }

    /**
     * get the current version from version file.
     */
    public function getCurrentVersion(): string
    {
        $fileName = $this->versionFile->getFilename();
        if ($this->versionFile->isValid($fileName)) {
            $versionString = $this->versionFile->read($fileName);
        } else {
            $version = $this->versionString->getInitial();
            $versionString = $version->toString();
            $this->versionFile->write($fileName, $versionString);
        }

        return $versionString;
    }

    /**
     * write the twig version file.
     */
    public function writeTwigFile(string $fileName, string $version): bool
    {
        $text = "<span title='Release " . date('d.m.Y H:i:s T') . "'>";
        $text .= "Version: $version";
        $text .= '</span>';

        return $this->versionFile->write($fileName, $text);
    }

    /**
     * append new version to CHANGELOG (or README).
     */
    public function appendCHANGELOG(string $fileName, string $version, string $message): bool
    {
        $text = "\n## Version $version\n";
        $text .= '*' . date('r') . "*\n";
        $text .= "- $message\n";

        return $this->versionFile->write($fileName, $text, true);
    }
}
