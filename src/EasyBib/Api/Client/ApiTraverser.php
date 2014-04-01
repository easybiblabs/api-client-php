<?php

namespace EasyBib\Api\Client;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\Validation\ResponseValidator;
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
     * @var Cache
     */
    private $cache;

    /**
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->httpClient->setDefaultOption('exceptions', false);
        $this->httpClient->setDefaultOption('allow_redirects', false);
        $this->httpClient->addSubscriber(
            new RequestHeader('Accept', 'application/vnd.com.easybib.data+json')
        );

        $this->cache = new Cache(new ArrayCache());
    }

    /**
     * @param string $url
     * @param array $queryParams
     * @return Resource
     */
    public function get($url, array $queryParams = [])
    {
        return $this->cache->cacheAndReturn(function () use ($url, $queryParams) {
            $request = $this->httpClient->get($url);
            $request->getQuery()->replace($queryParams);

            return Resource::fromResponse($this->send($request), $this);
        }, new CacheKey([$url, $queryParams]));
    }

    /**
     * @param string $url
     * @param array $resource
     * @return Resource
     */
    public function post($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResource('post', $url, $resource);
    }

    /**
     * @param string $url
     * @param array $resource
     * @return Resource
     */
    public function put($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResource('put', $url, $resource);
    }

    /**
     * @param $url
     * @param array $resource
     * @return Resource
     */
    public function patch($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResource('patch', $url, $resource);
    }

    /**
     * @param $url
     * @return Resource
     */
    public function delete($url)
    {
        $this->cache->clear();
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
        return $this->get($this->getUserBaseUrl());
    }

    /**
     * This bootstraps the session by returning the user's projects Collection
     *
     * @param array $queryParams
     * @return Collection
     */
    public function getProjects(array $queryParams = [])
    {
        return $this->get($this->getProjectsBaseUrl(), $queryParams);
    }

    /**
     * @return string
     */
    public function getUserBaseUrl()
    {
        return $this->httpClient->getBaseUrl() . '/user/';
    }

    /**
     * @return string
     */
    public function getProjectsBaseUrl()
    {
        return $this->httpClient->getBaseUrl() . '/projects/';
    }

    /**
     * @param CacheProvider $cacheProvider
     */
    public function setCache(CacheProvider $cacheProvider)
    {
        $this->cache = new Cache($cacheProvider);
    }

    /**
     * @param RequestInterface $request
     * @return Response
     */
    private function send(RequestInterface $request)
    {
        $response = $request->send();

        $validator = new ResponseValidator($response);
        $validator->validate();

        return $response;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $resource
     * @return Resource
     */
    private function sendResource($method, $url, array $resource)
    {
        $payload = json_encode($resource);
        $request = $this->httpClient->$method($url, [], $payload);

        return Resource::fromResponse($this->send($request), $this);
    }
}
