<?php

namespace EasyBib\Api\Client;

use Guzzle\Http\ClientInterface;

class ApiSession
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function authenticate()
    {
    }

    /**
     * @param $url
     * @return \Guzzle\Http\Message\Response
     */
    public function get($url)
    {
        return $this->httpClient->get($url)->send();
    }
}
