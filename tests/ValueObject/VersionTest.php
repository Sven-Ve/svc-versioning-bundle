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

namespace Svc\VersioningBundle\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\ValueObject\Version;

class VersionTest extends TestCase
{
    public function testConstruct(): void
    {
        $version = new Version(1, 2, 3);

        $this->assertEquals(1, $version->major);
        $this->assertEquals(2, $version->minor);
        $this->assertEquals(3, $version->patch);
    }

    public function testConstructWithNegativeMajorThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Version numbers must be non-negative');

        new Version(-1, 0, 0);
    }

    public function testConstructWithNegativeMinorThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Version numbers must be non-negative');

        new Version(1, -1, 0);
    }

    public function testConstructWithNegativePatchThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Version numbers must be non-negative');

        new Version(1, 0, -1);
    }

    public function testFromString(): void
    {
        $version = Version::fromString('1.2.3');

        $this->assertEquals(1, $version->major);
        $this->assertEquals(2, $version->minor);
        $this->assertEquals(3, $version->patch);
    }

    public function testFromStringWithZeros(): void
    {
        $version = Version::fromString('0.0.1');

        $this->assertEquals(0, $version->major);
        $this->assertEquals(0, $version->minor);
        $this->assertEquals(1, $version->patch);
    }

    public function testFromStringWithInvalidFormatThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid version format: 'abc.def.ghi'");

        Version::fromString('abc.def.ghi');
    }

    public function testFromStringWithEmptyStringThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid version format: ''");

        Version::fromString('');
    }

    public function testFromStringWithMissingPartsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid version format: '1.2'");

        Version::fromString('1.2');
    }

    public function testFromStringWithExtraPartsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid version format: '1.2.3.4'");

        Version::fromString('1.2.3.4');
    }

    public function testFromStringWithNegativeNumbersThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid version format: '1.-2.3'");

        Version::fromString('1.-2.3');
    }

    public function testInitial(): void
    {
        $version = Version::initial();

        $this->assertEquals(0, $version->major);
        $this->assertEquals(0, $version->minor);
        $this->assertEquals(1, $version->patch);
    }

    public function testToString(): void
    {
        $version = new Version(1, 2, 3);

        $this->assertEquals('1.2.3', $version->toString());
    }

    public function testMagicToString(): void
    {
        $version = new Version(1, 2, 3);

        $this->assertEquals('1.2.3', (string) $version);
    }

    public function testIncrementMajor(): void
    {
        $version = new Version(1, 2, 3);
        $newVersion = $version->incrementMajor();

        $this->assertEquals(2, $newVersion->major);
        $this->assertEquals(0, $newVersion->minor);
        $this->assertEquals(0, $newVersion->patch);

        // Original version should be unchanged (immutability)
        $this->assertEquals(1, $version->major);
        $this->assertEquals(2, $version->minor);
        $this->assertEquals(3, $version->patch);
    }

    public function testIncrementMinor(): void
    {
        $version = new Version(1, 2, 3);
        $newVersion = $version->incrementMinor();

        $this->assertEquals(1, $newVersion->major);
        $this->assertEquals(3, $newVersion->minor);
        $this->assertEquals(0, $newVersion->patch);

        // Original version should be unchanged (immutability)
        $this->assertEquals(1, $version->major);
        $this->assertEquals(2, $version->minor);
        $this->assertEquals(3, $version->patch);
    }

    public function testIncrementPatch(): void
    {
        $version = new Version(1, 2, 3);
        $newVersion = $version->incrementPatch();

        $this->assertEquals(1, $newVersion->major);
        $this->assertEquals(2, $newVersion->minor);
        $this->assertEquals(4, $newVersion->patch);

        // Original version should be unchanged (immutability)
        $this->assertEquals(1, $version->major);
        $this->assertEquals(2, $version->minor);
        $this->assertEquals(3, $version->patch);
    }

    public function testIsGreaterThanByMajor(): void
    {
        $version1 = new Version(2, 0, 0);
        $version2 = new Version(1, 9, 9);

        $this->assertTrue($version1->isGreaterThan($version2));
        $this->assertFalse($version2->isGreaterThan($version1));
    }

    public function testIsGreaterThanByMinor(): void
    {
        $version1 = new Version(1, 2, 0);
        $version2 = new Version(1, 1, 9);

        $this->assertTrue($version1->isGreaterThan($version2));
        $this->assertFalse($version2->isGreaterThan($version1));
    }

    public function testIsGreaterThanByPatch(): void
    {
        $version1 = new Version(1, 2, 4);
        $version2 = new Version(1, 2, 3);

        $this->assertTrue($version1->isGreaterThan($version2));
        $this->assertFalse($version2->isGreaterThan($version1));
    }

    public function testIsGreaterThanWithEqualVersions(): void
    {
        $version1 = new Version(1, 2, 3);
        $version2 = new Version(1, 2, 3);

        $this->assertFalse($version1->isGreaterThan($version2));
        $this->assertFalse($version2->isGreaterThan($version1));
    }

    public function testEquals(): void
    {
        $version1 = new Version(1, 2, 3);
        $version2 = new Version(1, 2, 3);

        $this->assertTrue($version1->equals($version2));
        $this->assertTrue($version2->equals($version1));
    }

    public function testNotEquals(): void
    {
        $version1 = new Version(1, 2, 3);
        $version2 = new Version(1, 2, 4);

        $this->assertFalse($version1->equals($version2));
        $this->assertFalse($version2->equals($version1));
    }
}
