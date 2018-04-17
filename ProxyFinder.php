<?php

namespace GetRepo\ProxyBundle;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ProxyFinder.
 */
class ProxyFinder
{
    use ContainerAwareTrait;

    /**
     * @var ContainerInterface
     */
    private $config;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->config = $this->container->getParameter('proxy.config');
    }

    public function find(/* use option resolver for country */)
    {
        foreach($this->config['sites'] as $name => $site) {
            // TODO use Guzzle
            $response = json_decode(file_get_contents($site['url']), true);

            // use path parser
            return $response;
        }
    }
}
