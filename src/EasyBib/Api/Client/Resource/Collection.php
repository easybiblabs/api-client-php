<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\ResponseDataContainer;

class Collection implements \ArrayAccess
{
    use HasRestfulLinks;

    /**
     * @var ResponseDataContainer
     */
    private $container;

    /**
     * @var \EasyBib\Api\Client\ApiTraverser
     */
    private $apiTraverser;

    public function __construct(ResponseDataContainer $container, ApiTraverser $apiTraverser)
    {
        $this->container = $container;
        $this->apiTraverser = $apiTraverser;
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

        return new Resource($containerForChild, $this->apiTraverser);
    }

    /**
     * This class is read-only, so this method is made degenerate
     *
     * @param mixed $offset
     * @param mixed $value
     * @throws \BadMethodCallException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('offsetSet() is not supported.');
    }

    /**
     * This class is read-only, so this method is made degenerate
     *
     * @param mixed $offset
     * @throws \BadMethodCallException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('offsetUnset() is not supported.');
    }

    /**
     * @return ApiTraverser
     */
    public function getApiTraverser()
    {
        return $this->apiTraverser;
    }

    /**
     * @return ResponseDataContainer
     */
    public function getResponseDataContainer()
    {
        return $this->container;
    }
}
