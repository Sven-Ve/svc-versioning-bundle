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

class VersionStringTest extends TestCase
{
    private VersionString $versionString;

    protected function setUp(): void
    {
        $this->versionString = new VersionString();
    }

    public function testVersionStringToVersionArray(): void
    {
        $result = $this->versionString->versionStringToVersionArray('1.2.3');

        $expected = [
            'major' => 1,
            'minor' => 2,
            'patch' => 3,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testVersionStringToVersionArrayWithZeros(): void
    {
        $result = $this->versionString->versionStringToVersionArray('0.0.1');

        $expected = [
            'major' => 0,
            'minor' => 0,
            'patch' => 1,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testVersionStringToVersionArrayWithLargeNumbers(): void
    {
        $result = $this->versionString->versionStringToVersionArray('10.25.999');

        $expected = [
            'major' => 10,
            'minor' => 25,
            'patch' => 999,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetInitial(): void
    {
        $result = $this->versionString->getInitial();

        $this->assertEquals('0.0.1', $result);
    }

    public function testVersionArrayToVersionString(): void
    {
        $versionArray = [
            'major' => 2,
            'minor' => 5,
            'patch' => 10,
        ];

        $result = $this->versionString->versionArrayToVersionString($versionArray);

        $this->assertEquals('2.5.10', $result);
    }

    public function testVersionArrayToVersionStringWithZeros(): void
    {
        $versionArray = [
            'major' => 1,
            'minor' => 0,
            'patch' => 0,
        ];

        $result = $this->versionString->versionArrayToVersionString($versionArray);

        $this->assertEquals('1.0.0', $result);
    }

    public function testRoundTripConversion(): void
    {
        $originalVersion = '3.14.159';

        $versionArray = $this->versionString->versionStringToVersionArray($originalVersion);
        $convertedBack = $this->versionString->versionArrayToVersionString($versionArray);

        $this->assertEquals($originalVersion, $convertedBack);
    }
}
