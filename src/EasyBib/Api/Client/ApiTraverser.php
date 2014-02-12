<?php

namespace EasyBib\Api\Client;

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
     * @param array $queryParams
     * @return Resource
     */
    public function get($url, array $queryParams = [])
    {
        $request = $this->httpClient->get($url);
        $request->getQuery()->replace($queryParams);

        return Resource::fromResponse($this->send($request), $this);
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

        return Resource::fromResponse($this->send($request), $this);
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
     * This bootstraps the session by returning the user's projects Collection
     *
     * @return Collection
     */
    public function getProjects()
    {
        return $this->get('/projects/');
    }

    /**
     * @param RequestInterface $request
     * @throws ExpiredTokenException
     * @return Response
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

        return Resource::fromResponse($this->send($request), $this);
    }
}
