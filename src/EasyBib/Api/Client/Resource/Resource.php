<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Validation\ResourceNotFoundException;
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
     * @var RelationsContainer
     */
    private $relationsContainer;

    /**
     * @var ApiTraverser
     */
    private $apiTraverser;

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
     * @return RelationsContainer
     */
    public function getRelationsContainer()
    {
        return $this->relationsContainer;
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
     * @return ApiTraverser
     */
    public function getApiTraverser()
    {
        return $this->apiTraverser;
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
     * @param string $method
     * @param string $rel
     * @param array $data
     * @throws ResourceNotFoundException
     * @return Resource
     */
    private function requestRelation($method, $rel, array $data = [])
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
