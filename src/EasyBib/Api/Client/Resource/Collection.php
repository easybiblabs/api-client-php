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

    public function __construct(\stdClass $data, ApiTraverser $apiTraverser)
    {
        parent::__construct($data, $apiTraverser);

        $this->iterator = new \ArrayIterator($this->getData());
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

        return Resource::factory($childData, $this->getApiTraverser());
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
        $data = json_decode(json_encode($this->iterator->current()));
        return new Resource($data, $this->getApiTraverser());
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

    public function map(callable $callback)
    {
        $output = [];

        foreach ($this as $resource) {
            $output[] = call_user_func($callback, $resource);
        }

        return $output;
    }
}
