<?php

namespace EasyBib\Api\Client\Resource;


class Collection extends Resource implements \ArrayAccess
{
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
}
