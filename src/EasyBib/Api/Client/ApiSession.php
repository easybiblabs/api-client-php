<?php

namespace EasyBib\Api\Client;

use EasyBib\Api\Client\Resource\Resource;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

class ApiSession
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @param string $token
     * @param ClientInterface $httpClient
     */
    public function __construct($token, ClientInterface $httpClient)
    {
        $this->token = $token;
        $this->httpClient = $httpClient;
    }

    /**
     * @param $url
     * @return \Guzzle\Http\Message\Response
     */
    public function get($url)
    {
        $request = $this->httpClient->get($url);
        $request->setHeader('Authorization', 'Bearer ' . $this->token);

        return $this->send($request);
    }

    /**
     * This bootstraps the session by returning the user's "root" Resource
     *
     * @return Resource
     */
    public function getUser()
    {
        $response = $this->get('/user/');
        $dataContainer = ResponseDataContainer::fromResponse($response);

        return new Resource($dataContainer, $this);
    }

    /**
     * @param RequestInterface $request
     * @throws ExpiredTokenException
     * @return \Guzzle\Http\Message\Response
     */
    private function send(RequestInterface $request)
    {
        $response = $request->send();

        if ($this->isTokenExpired($response)) {
            throw new ExpiredTokenException();
        }

        return $response;
    }

    /**
     * @param Response $response
     * @return bool
     */
    private function isTokenExpired(Response $response)
    {
        if ($response->getStatusCode() != 400) {
            return false;
        }

        return json_decode($response->getBody(true))->error == 'invalid_grant';
    }
}
