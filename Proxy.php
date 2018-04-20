<?php

namespace GetRepo\ProxyBundle;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use SahusoftCom\ProxyChecker\ProxyCheckerService;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Proxy.
 */
class Proxy
{
    /**
     * @var array
     */
    private $ip;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $port;

    /**
     * @param array $config
     */
    public function __construct($ip, $port)
    {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function __toString()
    {
        return "{$this->ip}:{$this->port}";
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function check($timeout = 0)
    {
        $proxy = (string) $this;
        $proxyCheckObject = new ProxyCheckerService(
            'http://captive.apple.com/',
            ['timeout' => $timeout]
        );
        $result = $proxyCheckObject->checkProxies([$proxy]);

        if ($result && isset($result[$proxy]['info'])) {
            return $result;
        }

        return false;
    }

    public function call($url)
    {
        return $client->request(
            'GET',
            $url,
            ['proxy' => ((string) $this)]
        );
    }
}