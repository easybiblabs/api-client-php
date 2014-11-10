<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Validation\ResourceNotFoundException;

class Resource
{
    /**
     * @var \stdClass
     */
    private $rawData;

    /**
     * @var string
     */
    private $location;

    /**
     * @var RelationsContainer
     */
    private $relationsContainer;

    /**
     * @var ApiTraverser
     */
    private $apiTraverser;

    /**
     * @param \stdClass $rawData
     * @param ApiTraverser $apiTraverser
     */
    public function __construct(\stdClass $rawData, ApiTraverser $apiTraverser)
    {
        $this->rawData = $rawData;
        $this->relationsContainer = new RelationsContainer($this->getRawLinks());
        $this->apiTraverser = $apiTraverser;
    }

    /**
     * @return \stdClass
     */
    public function getData()
    {
        return isset($this->rawData->data) ? $this->rawData->data : null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return json_decode(json_encode($this->rawData), true);
    }

    /**
     * @return string
     */
    public function getId()
    {
        $meRelation = $this->relationsContainer->get('me');

        if (!$meRelation) {
            return null;
        }

        preg_match('_([^/]+)$_', $meRelation->getHref(), $matches);

        if ($matches) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * This is a customized location for a resource. In practice it is passed
     * from the EasyBib API in a Location header. The only current use case
     * is for the location of created documents on a remote filestore.
     *
     * @param string $location
     * @throws \InvalidArgumentException
     */
    public function setLocation($location)
    {
        if (!is_string($location)) {
            throw new \InvalidArgumentException('Location must be a string');
        }

        $this->location = $location;
    }

    /**
     * @return RelationsContainer
     */
    public function getRelationsContainer()
    {
        return $this->relationsContainer;
    }

    /**
     * @param string $rel
     * @return Resource
     */
    public function get($rel)
    {
        return $this->requestRelation('get', $rel);
    }

    /**
     * @param string $rel
     * @param array $data
     * @return Resource
     */
    public function post($rel, array $data)
    {
        return $this->requestRelation('post', $rel, $data);
    }

    /**
     * @param string $rel
     * @param array $data
     * @return Resource
     */
    public function put($rel, array $data)
    {
        return $this->requestRelation('put', $rel, $data);
    }

    /**
     * @param string $rel
     */
    public function delete($rel)
    {
        $this->requestRelation('delete', $rel);
    }

    /**
     * @param string $method
     * @param string $rel
     * @param array $data
     * @throws ResourceNotFoundException
     * @return Resource
     */
    private function requestRelation($method, $rel, array $data = null)
    {
        $relation = $this->relationsContainer->get($rel);

        if (!$relation) {
            throw new ResourceNotFoundException(
                sprintf('Not Found: relation "%s"', $rel),
                404
            );
        }

        return $this->apiTraverser->$method($relation->getHref(), $data);
    }

    /**
     * @return array
     */
    private function getRawLinks()
    {
        return isset($this->rawData->links) ? $this->rawData->links : [];
    }
}
