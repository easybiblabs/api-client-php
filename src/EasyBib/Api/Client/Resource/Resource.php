<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\ResourceDataContainer;

class Resource
{
    use HasRestfulLinks;

    /**
     * @var ResourceDataContainer
     */
    private $container;

    /**
     * @var \EasyBib\Api\Client\ApiTraverser
     */
    private $apiTraverser;

    public function __construct(ResourceDataContainer $container, ApiTraverser $apiTraverser)
    {
        $this->container = $container;
        $this->apiTraverser = $apiTraverser;
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

    /**
     * @return ApiTraverser
     */
    public function getApiTraverser()
    {
        return $this->apiTraverser;
    }

    /**
     * @return ResourceDataContainer
     */
    public function getResourceData()
    {
        return $this->container;
    }
}
