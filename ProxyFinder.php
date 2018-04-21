<?php

namespace GetRepo\ProxyBundle;

use GuzzleHttp\Client;
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
     * Available filters for all sites
     *
     * @var string
     */
    const FILTERS = [
        'port',
        'country',
        'protocol',
        'https',
        'get',
        'post',
    ];

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

        // default options
        $defaultCheck = $this->readConfig('[default][check]');
        $defaultTimeout = $this->readConfig('[default][timeout]');
        $defaultTries = $this->readConfig('[default][tries]');

        foreach($sites as $name => $site) {
            // options per site or global
            $check = (bool) (is_null($site['check']) ? $defaultCheck : $site['check']);
            $timeout = (int) (is_null($site['timeout']) ? $defaultTimeout : $site['timeout']);
            $tries = (int) (is_null($site['tries']) ? $defaultTries : $site['tries']);

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

            for ($x = 1; $x <= $tries; $x++) {
                $response = $this->getResponse($site);

                if ($response) {
                    $ip = $this->accessor->getValue(
                        $response,
                        $this->getPropertyPath($site, 'ip')
                    );
                    $port = $this->accessor->getValue(
                        $response,
                        $this->getPropertyPath($site, 'port')
                    );

                    if ($ip && $port) {
                        $proxy = new Proxy($ip, $port);

                        if (!$check
                            || ($check && $proxy->check($timeout))) {
                            return $proxy;
                        }
                    }
                }
            }
        }

        return false;
    }

    private function readConfig($path)
    {
        return $this->accessor->getValue($this->config, $path);
    }

    private function getPropertyPath($site, $path)
    {
        return '[' . implode('][', explode(
            '.',
            $this->accessor->getValue($site, "[paths][{$path}]")
        )) . ']';
    }

    private function getResponse(array $site)
    {
        $type = $this->accessor->getValue($site, '[type]');

        switch ($type) {
            case self::TYPE_JSON:
                try {
                    $response = (new Client())->request(
                        'GET',
                        $site['url']
                    );

                    if (200 != ($code = $response->getStatusCode())) {
                        throw new \Exception("Returned HTTP code {$code}");
                    }

                    $response = json_decode((string) $response->getBody(), true);
                } catch (\Exception $e) {
                    $response = false;
                }

                break;

            default:
                throw new \LogicException("Proxy type '{$type}' is not implemented");
                break;
        }

        return $response;
    }
}