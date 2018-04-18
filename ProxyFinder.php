<?php

namespace GetRepo\ProxyBundle;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * ProxyFinder.
 */
class ProxyFinder
{
    /**
     * @var string
     */
    const TYPE_JSON = 'json';

    /**
     * @var string
     */
    const TYPE_STRING = 'string';

    /**
     * @var string
     */
    const FILTERS = ['port', 'country'];

    use ContainerAwareTrait;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $config;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->config = $this->container->getParameter('proxy.config');
        $this->accessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->enableMagicCall()
            ->getPropertyAccessor();
    }

    public function find(array $filters = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            array_combine(self::FILTERS, array_fill(0, count(self::FILTERS), null))
        );
        $filters = array_filter($resolver->resolve($filters));

        $sites = $this->readConfig('[sites]');
        $check = $this->readConfig('[check]');

        foreach($sites as $name => $site) {
            if ($filters) {
                $query = [];
                foreach($filters as $k => $v) {
                    if (!isset($site['filters'][$k])) {
                        $query = false;
                        break;
                    }
                    $query[$site['filters'][$k]] = $v;
                }

                // filters can not be applied to this site
                if (!$query) {
                    continue;
                }

                $site['url'] .= '?' . http_build_query($query);
            }

            $response = $this->getResponse($site);

            if ($response) {
                $ip = $this->accessor->getValue(
                    $response,
                    '[' . $this->accessor->getValue($site, '[paths][ip]') . ']'
                );
                $port = $this->accessor->getValue(
                    $response,
                    '[' . $this->accessor->getValue($site, '[paths][port]') . ']'
                );

                if ($ip && $port) {
                    $proxy = new Proxy($ip, $port);

                    if ($check['enabled'] && !$proxy->check($check['timeout'])) {
                        continue;
                    }

                    return $proxy;
                }
            }
        }

        return false;
    }

    private function readConfig($path)
    {
        return $this->accessor->getValue($this->config, $path);
    }

    private function getResponse(array $site)
    {
        $type = $this->accessor->getValue($site, '[type]');

        switch ($type) {
            case self::TYPE_JSON:
                // TODO use guzzle
                $response = json_decode(file_get_contents($site['url']), true);
                break;

            default:
                throw new \LogicException("Proxy type '{$type}' is not implemented");
                break;
        }

        return $response;
    }
}
