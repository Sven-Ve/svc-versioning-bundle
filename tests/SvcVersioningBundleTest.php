<?php

declare(strict_types=1);

/*
 * This file is part of the svc-versioning bundle.
 *
 * (c) 2025 Sven Vetter <dev@sv-systems.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Svc\VersioningBundle\Tests;

use PHPUnit\Framework\TestCase;
use Svc\VersioningBundle\SvcVersioningBundle;

class SvcVersioningBundleTest extends TestCase
{
    private SvcVersioningBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new SvcVersioningBundle();
    }

    public function testGetPath(): void
    {
        $path = $this->bundle->getPath();

        $this->assertStringEndsWith('SvcVersioningBundle', $path);
        $this->assertTrue(is_dir($path));
    }

    public function testBundleExtendsAbstractBundle(): void
    {
        $this->assertInstanceOf(\Symfony\Component\HttpKernel\Bundle\AbstractBundle::class, $this->bundle);
    }

    public function testBundleNamespace(): void
    {
        $this->assertEquals('Svc\VersioningBundle\SvcVersioningBundle', get_class($this->bundle));
    }
}
