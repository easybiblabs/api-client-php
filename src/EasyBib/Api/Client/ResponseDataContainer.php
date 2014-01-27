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
     * Whether the data contained is a hash, as opposed to an indexed array
     *
     * @return bool
     */
    public function isHash()
    {
        $dataArray = (array) $this->getData();

        return (bool) count(array_filter(array_keys($dataArray), 'is_string'));
    }

    public static function fromResponse(Response $response)
    {
        $data = json_decode($response->getBody(true)) ?: (object) [];

        return new ResponseDataContainer($data);
    }
}
