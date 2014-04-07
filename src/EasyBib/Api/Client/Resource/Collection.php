<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class Collection extends Resource implements \ArrayAccess, \Iterator
{
    /**
     * @var \ArrayIterator
     */
    private $iterator;

    /**
     * @var \stdClass
     */
    private $rawData;

    /**
     * @param \stdClass $rawData
     * @param ApiTraverser $apiTraverser
     */
    public function __construct(\stdClass $rawData, ApiTraverser $apiTraverser)
    {
        parent::__construct($rawData, $apiTraverser);

        $this->rawData = $rawData;

        $filtered = array_filter(array_map(function ($resourceData) {
            try {
                return $this->getResourceFactory()->createFromData($resourceData);
            } catch (ResourceErrorException $e) {
                return null;
            }
        }, $rawData->data));

        $this->iterator = new \ArrayIterator($filtered);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->getData()[$offset]);
    }

    /**
     * @param mixed $offset
     * @return Resource
     */
    public function offsetGet($offset)
    {
        $childData = $this->getData()[$offset];
        return $this->getResourceFactory()->createFromData($childData);
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
     * @return mixed
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * @return scalar
     */
    public function key()
    {
        return $this->iterator->key();
    }

    public function next()
    {
        return $this->iterator->next();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    public function rewind()
    {
        return $this->iterator->rewind();
    }

    /**
     * @return bool
     */
    public function hasResourceError()
    {
        $initial = false;

        return array_reduce($this->rawData->data, function ($carry, $resourceData) {
            try {
                $this->getResourceFactory()->createFromData($resourceData);
                return $carry;
            } catch (ResourceErrorException $e) {
                return true;
            }
        }, $initial);
    }

    /**
     * @param callable $callback
     * @return array
     */
    public function map(callable $callback)
    {
        $output = [];

        foreach ($this as $resource) {
            $output[] = call_user_func($callback, $resource);
        }

        return $output;
    }

    /**
     * @return ResourceFactory
     */
    private function getResourceFactory()
    {
        return new ResourceFactory($this->getApiTraverser());
    }
}
