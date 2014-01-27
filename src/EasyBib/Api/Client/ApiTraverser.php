<?php

namespace EasyBib\Api\Client;

use EasyBib\Api\Client\Resource\Resource;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

class ApiTraverser
{
    /**
     * @var ApiSession
     */
    private $session;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @param ApiSession $session
     * @param ClientInterface $httpClient
     */
    public function __construct(ApiSession $session, ClientInterface $httpClient)
    {
        $this->session = $session;
        $this->httpClient = $httpClient;
    }

    /**
     * @param $url
     * @return \Guzzle\Http\Message\Response
     */
    public function get($url = null)
    {
        if (!$url) {
            return $this->getUser();
        }

        $request = $this->httpClient->get($url);
        $request->setHeader('Accept', 'application/vnd.com.easybib.data+json');
        $request->setHeader('Authorization', 'Bearer ' . $this->session->getToken());

        return $this->send($request);
    }

    /**
     * This bootstraps the session by returning the user's "root" Resource
     *
     * @return Resource
     */
    private function getUser()
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
