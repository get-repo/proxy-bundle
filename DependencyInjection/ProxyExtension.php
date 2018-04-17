<?php

namespace GetRepo\ProxyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

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
        $configs = (new Parser())->parse(
            file_get_contents(__DIR__ . '/../Resources/config/config.yml'),
            Yaml::PARSE_CONSTANT
        );

        if ($container->hasExtension('proxy') && isset($configs['proxy'])) {
            $c = $container->getExtensionConfig('proxy');
            array_unshift($c, $configs['proxy']);
            $configuration = new Configuration();
            $config = $this->processConfiguration($configuration, $c);
            $container->setParameter('proxy.config', $config);
        }
    }
}
