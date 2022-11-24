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
        ->scalarNode('pre_command')->info('run this command before start versioning, stop on error (e.q. phpstan, tests, ...)')->end()
        ->booleanNode('create_sentry_release')->defaultFalse()->info('Create a new release in config/packages/sentry.yaml')->end()
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
      ->arg(3, $config['create_sentry_release']);
  }
}
