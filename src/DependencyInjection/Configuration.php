<?php

namespace Svc\VersioningBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
  public function getConfigTreeBuilder(): TreeBuilder
  {
    $treeBuilder = new TreeBuilder('svc_versioning');
    $rootNode = $treeBuilder->getRootNode();

    $rootNode
      ->children()
        ->booleanNode('run_git')->defaultTrue()->info('Should git runs after version increase?')->end()
        ->booleanNode('run_deploy')->defaultTrue()->info('Should deploy runs after git?')->end()
        ->scalarNode('pre_command')->info('run this command before start versioning, stop on error (e.q. phpstan, tests, ...)')
      ->end();

    return $treeBuilder;
  }
}
