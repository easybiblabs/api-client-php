<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use Guzzle\Http\Message\Response;

class Resource
{
    /**
     * @var \stdClass
     */
    private $rawData;

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
     * @return array Reference[]
     */
    public function getRelations()
    {
        return array_map(
            function ($reference) {
                return new Relation($reference);
            },
            $this->rawData->links
        );
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
            $this->rawData->links
        );
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
     * Allows retrieval of the URL; useful e.g. when GETting exported
     * documents
     *
     * @param string $rel
     * @return Relation
     */
    public function findRelation($rel)
    {
        foreach ($this->getRelations() as $reference) {
            if ($reference->getRel() == $rel) {
                return $reference;
            }
        }

        return null;
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
        return is_array($data->data);
    }

    /**
     * @param Response $response
     * @param ApiTraverser $apiTraverser
     * @return Resource
     */
    public static function fromResponse(Response $response, ApiTraverser $apiTraverser)
    {
        $data = json_decode($response->getBody(true));

        return self::factory($data, $apiTraverser);
    }

    /**
     * @param \stdClass $data
     * @param ApiTraverser $apiTraverser
     * @return Resource
     */
    public static function factory(\stdClass $data, ApiTraverser $apiTraverser)
    {
        if (self::isList($data)) {
            return new Collection($data, $apiTraverser);
        }

        return new Resource($data, $apiTraverser);
    }
}
