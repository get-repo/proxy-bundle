<?php

namespace GetRepo\ProxyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class ProxyExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
    }

    public function prepend(ContainerBuilder $container)
    {
        $yamlParser = new \Symfony\Component\Yaml\Parser();
        $configs = ($yamlParser->parse(file_get_contents(__DIR__.'/../Resources/config/config.yml'), \Symfony\Component\Yaml\Yaml::PARSE_CONSTANT));

        foreach($configs as $name => $config) {
            if ($container->hasExtension($name)) {
                $container->prependExtensionConfig($name, $config);
            }
        }

        $container->setParameter('proxy.config', $configs['proxy']);
    }
}
