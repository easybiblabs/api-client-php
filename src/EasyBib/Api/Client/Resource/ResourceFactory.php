<?php

namespace EasyBib\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use Guzzle\Http\Message\Response;

class ResourceFactory
{
    const STATUS_ERROR = 'error';

    private $apiTraverser;

    /**
     * @param ApiTraverser $apiTraverser
     */
    public function __construct(ApiTraverser $apiTraverser)
    {
        $this->apiTraverser = $apiTraverser;
    }

    public function createFromData(\stdClass $data)
    {
        if (isset($data->status) && $data->status == self::STATUS_ERROR) {
            $message = isset($data->message) ? $data->message : 'Unspecified resource error';
            throw new ResourceErrorException($message);
        }

        if ($this->isList($data)) {
            return new Collection($data, $this->apiTraverser);
        }

        return new Resource($data, $this->apiTraverser);
    }

    /**
     * @param Response $response
     * @return Resource
     */
    public function createFromResponse(Response $response)
    {
        $data = json_decode($response->getBody(true));
        $resource = $this->createFromData($data, $this->apiTraverser);

        $locationHeaders = $response->getHeader('Location');
        if ($locationHeaders) {
            $resource->setLocation($locationHeaders->toArray()[0]);
        }
        $totalRowsHeaders = $response->getHeader('X-EasyBib-TotalRows');
        if ($totalRowsHeaders) {
            $resource->setTotalRows($totalRowsHeaders->toArray()[0]);
        }

        return $resource;
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
    private function isList(\stdClass $data)
    {
        return isset($data->data) && is_array($data->data);
    }
}
