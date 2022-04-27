<?php

namespace Svc\VersioningBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;


class SvcVersioningExtension extends Extension
{
  public function load(array $configs, ContainerBuilder $container)
  {
    $rootPath = $container->getParameter("kernel.project_dir");
    $this->createConfigIfNotExists($rootPath);

    $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
    $loader->load('services.xml');

    $configuration = $this->getConfiguration($configs, $container);
    $config = $this->processConfiguration($configuration, $configs);

    $configuration = $this->getConfiguration($configs, $container);
    $config = $this->processConfiguration($configuration, $configs);

    $definition = $container->getDefinition('svc_versioning.versioning_command');
    $definition->setArgument(0, $config['run_git']);
    $definition->setArgument(1, $config['run_deploy']);
    $definition->setArgument(2, $config['pre_command'] ?? null);
  }


  private function createConfigIfNotExists($rootPath) {
    $fileName= $rootPath . "/config/packages/svc_versioning.yaml";
    if (file_exists($fileName)) {
      return false;
    }
    
    $text="svc_versioning:\n";
    $text.="    # should git checkin and push runs? Have to be configured first.\n";
    $text.="    run_git: false\n";
    $text.="    # should easycorp/easy-deploy-bundle runs? Have to be installed and configured first.\n";
    $text.="    run_deploy: false\n";
    file_put_contents($fileName, $text);
    dump("Please check and adapt config 'file config/packages/svc_versioning.yaml'");
  }
}