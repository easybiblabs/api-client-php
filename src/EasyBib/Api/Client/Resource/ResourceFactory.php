<?php

namespace EasyBib\Api\Client\Resource;

use Guzzle\Http\Message\Response;

class ResourceFactory
{
    /**
     * @param Response $response
     * @return LinkSourceInterface
     */
    public function get(Response $response)
    {
        // $container = new ResponseDataContainer($response);
        // switch ==> new ResourceList($container, $this->apiSession);
        // switch ==> new Resource($container, $this->apiSession);
    }
}
