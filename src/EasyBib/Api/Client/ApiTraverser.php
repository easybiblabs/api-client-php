<?php

namespace EasyBib\Api\Client;

use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Guzzle\Plugin\RequestHeader;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

class ApiTraverser
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->httpClient->setDefaultOption('exceptions', false);
        $this->httpClient->addSubscriber(
            new RequestHeader('Accept', 'application/vnd.com.easybib.data+json')
        );
    }

    /**
     * @param string $url
     * @return HasRestfulLinks
     */
    public function get($url)
    {
        $request = $this->httpClient->get($url);

        $dataContainer = ResourceDataContainer::fromResponse($this->send($request));

        if ($dataContainer->isList()) {
            return new Collection($dataContainer, $this);
        }

        return new Resource($dataContainer, $this);
    }

    /**
     * @param string $url
     * @param array $resource
     * @return Resource
     */
    public function post($url, array $resource)
    {
        return $this->sendResource('post', $url, $resource);
    }

    /**
     * @param string $url
     * @param array $resource
     * @return Resource
     */
    public function put($url, array $resource)
    {
        return $this->sendResource('put', $url, $resource);
    }

    /**
     * @param $url
     * @return Resource
     */
    public function delete($url)
    {
        $request = $this->httpClient->delete($url);
        $dataContainer = ResourceDataContainer::fromResponse($this->send($request));

        return new Resource($dataContainer, $this);
    }

    /**
     * This bootstraps the session by returning the user's "root" Resource
     *
     * @return Resource
     */
    public function getUser()
    {
        return $this->get('/user/');
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

    /**
     * @param string $method
     * @param string $url
     * @param array $resource
     * @return Resource
     */
    private function sendResource($method, $url, array $resource)
    {
        $payload = json_encode(['data' => $resource]);
        $request = $this->httpClient->$method($url, [], $payload);
        $dataContainer = ResourceDataContainer::fromResponse($this->send($request));

        return new Resource($dataContainer, $this);
    }
}
