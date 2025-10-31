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

namespace Svc\VersioningBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\Service\VersionString;
use Svc\VersioningBundle\ValueObject\Version;

class VersionStringTest extends TestCase
{
    private VersionString $versionString;

    protected function setUp(): void
    {
        $this->versionString = new VersionString();
    }

    public function testParse(): void
    {
        $result = $this->versionString->parse('1.2.3');

        $this->assertInstanceOf(Version::class, $result);
        $this->assertEquals(1, $result->major);
        $this->assertEquals(2, $result->minor);
        $this->assertEquals(3, $result->patch);
    }

    public function testParseWithZeros(): void
    {
        $result = $this->versionString->parse('0.0.1');

        $this->assertEquals(0, $result->major);
        $this->assertEquals(0, $result->minor);
        $this->assertEquals(1, $result->patch);
    }

    public function testParseWithLargeNumbers(): void
    {
        $result = $this->versionString->parse('10.25.999');

        $this->assertEquals(10, $result->major);
        $this->assertEquals(25, $result->minor);
        $this->assertEquals(999, $result->patch);
    }

    public function testGetInitial(): void
    {
        $result = $this->versionString->getInitial();

        $this->assertInstanceOf(Version::class, $result);
        $this->assertEquals(0, $result->major);
        $this->assertEquals(0, $result->minor);
        $this->assertEquals(1, $result->patch);
    }

    public function testToString(): void
    {
        $version = new Version(2, 5, 10);
        $result = $this->versionString->toString($version);

        $this->assertEquals('2.5.10', $result);
    }

    public function testToStringWithZeros(): void
    {
        $version = new Version(1, 0, 0);
        $result = $this->versionString->toString($version);

        $this->assertEquals('1.0.0', $result);
    }

    public function testRoundTripConversion(): void
    {
        $originalVersion = '3.14.159';

        $version = $this->versionString->parse($originalVersion);
        $convertedBack = $this->versionString->toString($version);

        $this->assertEquals($originalVersion, $convertedBack);
    }
}
