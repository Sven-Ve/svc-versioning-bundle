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

namespace Svc\VersioningBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SvcVersioningBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
          ->children()
            ->booleanNode('run_git')->defaultTrue()->info('Should git runs after version increase?')->end()
            ->booleanNode('run_deploy')->defaultTrue()->info('Should deploy runs after git?')->end()
            ->stringNode('deploy_command')->info('run this command for deployment, disable default deployment with easycorp/easy-deploy-bundle')->end()
            ->booleanNode('ansible_deploy')->defaultFalse()->info('Deploy via Ansible')->end()
            ->stringNode('ansible_inventory')->defaultValue('inventory.yaml')->info('if ansible_deploy==true the name of the inventory file (default="inventory.yaml")')->end()
            ->stringNode('ansible_playbook')->info('if ansible_deploy==true the name of the ansible playbook')->end()
            ->stringNode('pre_command')->info('run this command before start versioning, stop on error')->example('composer run-script phpstan')->end()
            ->booleanNode('check_cache_clear')->defaultFalse()->info('Check if production cache clear runs without errors after pre_command')->end()
            ->booleanNode('cleanup_cache_dir')->defaultFalse()->info('Delete var/cache/prod directory after cache clear check')->end()
            ->booleanNode('create_sentry_release')->defaultFalse()->info('Create a new release in config/packages/sentry.yaml')->end()
            ->stringNode('sentry_app_name')->info('Sentry application name (included in release)')->end()
          ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $container->services()
          ->get('Svc\VersioningBundle\Command\VersioningCommand')
          ->arg('$run_git', $config['run_git'])
          ->arg('$run_deploy', $config['run_deploy'])
          ->arg('$pre_command', $config['pre_command'] ?? null)
          ->arg('$checkCacheClear', $config['check_cache_clear'])
          ->arg('$cleanupCacheDir', $config['cleanup_cache_dir'])
          ->arg('$createSentryRelease', $config['create_sentry_release'])
          ->arg('$sentryAppName', $config['sentry_app_name'] ?? null)
          ->arg('$deployCommand', $config['deploy_command'] ?? null)
          ->arg('$ansibleDeploy', $config['ansible_deploy'])
          ->arg('$ansibleInventory', $config['ansible_inventory'] ?? null)
          ->arg('$ansiblePlaybook', $config['ansible_playbook'] ?? null);
    }
}
