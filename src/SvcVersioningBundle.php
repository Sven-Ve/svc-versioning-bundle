<?php

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
        ->scalarNode('deploy_command')->info('run this command for deployment, disable default deployment with easycorp/easy-deploy-bundle')->end()
        ->booleanNode('ansible_deploy')->defaultFalse()->info('Deploy via Ansible')->end()
        ->scalarNode('ansible_inventory')->defaultValue("inventory.yaml")->info('if ansible_deploy==true the name of the inventory file (default="inventory.yaml")')->end()
        ->scalarNode('ansible_playbook')->info('if ansible_deploy==true the name of the ansible playbook')->end()
        ->scalarNode('pre_command')->info('run this command before start versioning, stop on error (e.q. phpstan, tests, ...)')->end()
        ->scalarNode('deploy_command')->info('run this command for deployment, disable default deployment with easycorp/easy-deploy-bundle')->end()
        ->booleanNode('create_sentry_release')->defaultFalse()->info('Create a new release in config/packages/sentry.yaml')->end()
        ->scalarNode('sentry_app_name')->info('Sentry application name (included in release)')->end()
      ->end();
  }

  public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
  {
    $container->import('../config/services.yaml');

    $container->services()
      ->get('Svc\VersioningBundle\Command\VersioningCommand')
      ->arg(0, $config['run_git'])
      ->arg(1, $config['run_deploy'])
      ->arg(2, $config['pre_command'] ?? null)
      ->arg(3, $config['create_sentry_release'])
      ->arg(4, $config['sentry_app_name'] ?? null)
      ->arg(5, $config['deploy_command'] ?? null)
      ->arg(6, $config['ansible_deploy'])
      ->arg(7, $config['ansible_inventory'] ?? null)
      ->arg(8, $config['ansible_playbook'] ?? null);
  }
}
