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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Svc\VersioningBundle\Command\VersioningCommand;
use Svc\VersioningBundle\Service\CacheClearCheck;
use Svc\VersioningBundle\Service\VersionHandling;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure()
            ->private();

    // Services
    $services->set(VersionHandling::class);
    $services->set(CacheClearCheck::class);

    // Command (arguments are injected via loadExtension in SvcVersioningBundle)
    $services->set(VersioningCommand::class);
};
