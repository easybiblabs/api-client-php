<?php

namespace EasyBib\Api\Client;

use EasyBib\Api\Client\Resource\ResourceLink;
use Guzzle\Http\Message\Response;

class ResponseDataContainer
{
    /**
     * @var \stdClass
     */
    private $rawData;

    public function __construct(\stdClass $rawData)
    {
        $this->rawData = $rawData;
    }

    public function getData()
    {
        return $this->rawData->data;
    }

    public function getLinks()
    {
        return array_map(
            function ($link) {
                return new ResourceLink($link);
            },
            $this->rawData->links
        );
    }

    /**
     * Whether the data contained is an indexed array, as opposed to key-value
     * pairs, a.k.a. associative array. This mirrors an ambiguity in the API
     * payloads. The `data` section can contain either a set of key-value
     * pairs, *or* an array of "child" items.
     *
     * @link http://stackoverflow.com/a/4254008/614709 Regarding the implemented strategy
     * @return bool
     */
    public function isList()
    {
        return is_array($this->getData());
    }

    public static function fromResponse(Response $response)
    {
        $data = json_decode($response->getBody(true)) ?: (object) [];

        return new ResponseDataContainer($data);
    }
}
