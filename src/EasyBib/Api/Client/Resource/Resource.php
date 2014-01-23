<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiSession;
use EasyBib\Api\Client\ResponseDataContainer;

class Resource
{
    use HasRestfulLinks;

    /**
     * @var ResponseDataContainer
     */
    private $container;

    /**
     * @var \EasyBib\Api\Client\ApiSession
     */
    private $apiSession;

    public function __construct(ResponseDataContainer $container, ApiSession $apiSession)
    {
        $this->container = $container;
        $this->apiSession = $apiSession;
    }

    /**
     * @param string $name
     * @return string
     */
    public function __get($name)
    {
        return $this->container->getData()->$name;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->container->getData()->$name);
    }
}
