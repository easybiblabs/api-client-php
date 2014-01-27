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

    public static function fromResponse(Response $response)
    {
        $data = json_decode($response->getBody(true)) ?: (object) [];

        return new ResponseDataContainer($data);
    }
}
