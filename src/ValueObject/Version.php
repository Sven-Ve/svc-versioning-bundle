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

namespace Svc\VersioningBundle\ValueObject;

/**
 * Immutable value object representing a semantic version (major.minor.patch).
 */
final readonly class Version
{
    private const VERSION_SEPARATOR = '.';

    public function __construct(
        public int $major,
        public int $minor,
        public int $patch,
    ) {
        if ($major < 0 || $minor < 0 || $patch < 0) {
            throw new \InvalidArgumentException('Version numbers must be non-negative');
        }
    }

    /**
     * Creates a Version from a string like "1.2.3".
     *
     * @throws \InvalidArgumentException if version string format is invalid
     */
    public static function fromString(string $version): self
    {
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            throw new \InvalidArgumentException("Invalid version format: '$version'. Expected format: 'major.minor.patch' (e.g., '1.2.3')");
        }

        $parts = explode(self::VERSION_SEPARATOR, $version);

        return new self(
            (int) $parts[0],
            (int) $parts[1],
            (int) $parts[2],
        );
    }

    /**
     * Creates initial version "0.0.1".
     */
    public static function initial(): self
    {
        return new self(0, 0, 1);
    }

    /**
     * Converts version to string format "major.minor.patch".
     */
    public function toString(): string
    {
        return $this->major . self::VERSION_SEPARATOR . $this->minor . self::VERSION_SEPARATOR . $this->patch;
    }

    /**
     * Increments major version and resets minor and patch to 0.
     */
    public function incrementMajor(): self
    {
        return new self($this->major + 1, 0, 0);
    }

    /**
     * Increments minor version and resets patch to 0.
     */
    public function incrementMinor(): self
    {
        return new self($this->major, $this->minor + 1, 0);
    }

    /**
     * Increments patch version.
     */
    public function incrementPatch(): self
    {
        return new self($this->major, $this->minor, $this->patch + 1);
    }

    /**
     * Checks if this version is greater than another version.
     */
    public function isGreaterThan(self $other): bool
    {
        if ($this->major !== $other->major) {
            return $this->major > $other->major;
        }
        if ($this->minor !== $other->minor) {
            return $this->minor > $other->minor;
        }

        return $this->patch > $other->patch;
    }

    /**
     * Checks if this version equals another version.
     */
    public function equals(self $other): bool
    {
        return $this->major === $other->major
            && $this->minor === $other->minor
            && $this->patch === $other->patch;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
