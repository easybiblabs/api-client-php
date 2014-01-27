<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiSession;
use EasyBib\Api\Client\ResponseDataContainer;

class Collection implements \ArrayAccess
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
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->container->getData()[$offset]);
    }

    /**
     * @param mixed $offset
     * @return Resource
     */
    public function offsetGet($offset)
    {
        $containerForChild = new ResponseDataContainer(
            $this->container->getData()[$offset]
        );

        return new Resource($containerForChild, $this->apiSession);
    }

    /**
     * This class is read-only, so this method is made degenerate
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('offsetSet() is degenerate');
    }

    /**
     * This class is read-only, so this method is made degenerate
     *
     * @param mixed $offset
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('offsetUnset() is degenerate');
    }

    public function getApiSession()
    {
        return $this->apiSession;
    }

    public function getResponseDataContainer()
    {
        return $this->container;
    }
}
