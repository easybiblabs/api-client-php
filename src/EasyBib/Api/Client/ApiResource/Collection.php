<?php

namespace EasyBib\Api\Client\ApiResource;

use EasyBib\Api\Client\ApiTraverser;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods, PHPMD.TooManyPublicMethods)
 */
class Collection extends ApiResource implements \ArrayAccess, \Iterator
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
     * @var int|null
     */
    private $totalRows;

    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    /**
     * @param \stdClass $rawData
     * @param ApiTraverser $apiTraverser
     */
    public function __construct(\stdClass $rawData, ApiTraverser $apiTraverser)
    {
        parent::__construct($rawData, $apiTraverser);

        $this->rawData = $rawData;
        $this->resourceFactory = new ResourceFactory($apiTraverser);

        $filtered = array_filter(array_map([$this, 'resourceOrNull'], $rawData->data));
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
        return $this->resourceFactory->createFromData($childData);
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
     * @return int|null - the over-all total rows, or null
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }

    /**
     * Virtual total row count - passed by the EasyBib API in the X-EasyBib-TotalRows header.
     *
     * @param int $totalRows
     * @throws \InvalidArgumentException
     */
    public function setTotalRows($totalRows)
    {
        if (!is_numeric($totalRows)) {
            throw new \InvalidArgumentException('Total number of rows must totally be a number');
        }

        $this->totalRows = $totalRows;
    }

    /**
     * @param \stdClass $resourceData
     * @return Resource
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function resourceOrNull(\stdClass $resourceData)
    {
        try {
            return $this->resourceFactory->createFromData($resourceData);
        } catch (ResourceErrorException $e) {
            return null;
        }
    }
}
