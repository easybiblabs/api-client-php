<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use Guzzle\Http\Message\Response;

class Resource
{
    const STATUS_ERROR = 'error';

    /**
     * @var \stdClass
     */
    private $rawData;

    /**
     * @var string
     */
    private $location;

    /**
     * @var ApiTraverser
     */
    private $apiTraverser;

    public function __construct(\stdClass $data, ApiTraverser $apiTraverser)
    {
        $this->rawData = $data;
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
     * @return array Relation[]
     */
    public function getRelations()
    {
        return array_map(
            function ($relation) {
                return new Relation($relation);
            },
            $this->getRawLinks()
        );
    }

    /**
     * Allows retrieval of the URL; useful e.g. when GETting exported
     * documents
     *
     * @param string $rel
     * @return Relation
     */
    public function findRelation($rel)
    {
        foreach ($this->getRelations() as $relation) {
            if ($relation->getRel() == $rel) {
                return $relation;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function listRelations()
    {
        return array_map(
            function ($link) {
                return $link->rel;
            },
            $this->getRawLinks()
        );
    }

    public function addRelation(\stdClass $data)
    {
        if (isset($this->rawData->links)) {
            $this->rawData->links[] = $data;
        } else {
            $this->rawData->links = [$data];
        }
    }

    /**
     * @param string $rel
     * @return bool
     */
    public function hasRelation($rel)
    {
        return in_array($rel, $this->listRelations());
    }

    /**
     * @return ApiTraverser
     */
    public function getApiTraverser()
    {
        return $this->apiTraverser;
    }

    /**
     * Convenience method to follow RESTful links
     *
     * @param string $ref
     * @return Resource
     */
    public function get($ref)
    {
        $link = $this->findRelation($ref);

        if (!$link) {
            return null;
        }

        return $this->apiTraverser->get($link->getHref());
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
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
     * @return array
     */
    public function toArray()
    {
        return json_decode(json_encode($this->rawData), true);
    }

    /**
     * Whether the data contained is an indexed array, as opposed to key-value
     * pairs, a.k.a. associative array. This mirrors an ambiguity in the API
     * payloads. The `data` section can contain either a set of key-value
     * pairs, *or* an array of "child" items.
     *
     * @param \stdClass $data
     * @return bool
     */
    public static function isList(\stdClass $data)
    {
        return isset($data->data) && is_array($data->data);
    }

    /**
     * @param Response $response
     * @param ApiTraverser $apiTraverser
     * @return Resource
     */
    public static function fromResponse(Response $response, ApiTraverser $apiTraverser)
    {
        $data = json_decode($response->getBody(true));
        $resource = self::factory($data, $apiTraverser);

        if ($locationHeaders = $response->getHeader('Location')) {
            $resource->setLocation($locationHeaders->toArray()[0]);
        }

        return $resource;
    }

    /**
     * @param \stdClass $data
     * @param ApiTraverser $apiTraverser
     * @throws ResourceErrorException
     * @return Resource
     */
    public static function factory(\stdClass $data, ApiTraverser $apiTraverser)
    {
        if (isset($data->status) && $data->status == self::STATUS_ERROR) {
            $message = isset($data->message) ? $data->message : 'Unspecified resource error';
            throw new ResourceErrorException($message);
        }

        if (self::isList($data)) {
            return new Collection($data, $apiTraverser);
        }

        return new Resource($data, $apiTraverser);
    }

    /**
     * @return array
     */
    private function getRawLinks()
    {
        return isset($this->rawData->links) ? $this->rawData->links : [];
    }
}
