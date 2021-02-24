<?php

namespace Svc\VersioningBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder('svc_versioning');
    $rootNode = $treeBuilder->getRootNode();

    $rootNode
      ->children()
        ->booleanNode('run_git')->defaultTrue()->info('Should git runs after version increase?')->end()
        ->booleanNode('run_deploy')->defaultTrue()->info('Should deploy runs after git?')->end()
      ->end();
    return $treeBuilder;

    }

}