<?php

namespace EasyBib\Api\Client;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\Resource\ResourceFactory;
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
     * @var ResourceFactory
     */
    private $resourceFactory;

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
        $this->resourceFactory = new ResourceFactory($this);
    }

    /**
     * @param string $url
     * @param array $queryParams
     * @return Resource
     */
    public function get($url, array $queryParams = null)
    {
        return $this->cache->cacheAndReturn(function () use ($url, $queryParams) {
            $request = $this->httpClient->get($url);
            if (null !== $queryParams) {
                $request->getQuery()->replace($queryParams);
            }

            return $this->resourceFactory->createFromResponse($this->send($request));
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

        return $this->resourceFactory->createFromResponse($this->send($request));
    }

    /**
     * This bootstraps the session by returning the public subject Collection
     *
     * @param array $queryParams
     * @return Collection
     */
    public function getSubjects(array $queryParams = [])
    {
        return $this->get($this->getSubjectsBaseUrl(), $queryParams);
    }

    /**
     * @param string $subjectId
     * @return Resource
     */
    public function getSubject($subjectId)
    {
        return $this->get($this->getSubjectsBaseUrl() . $subjectId);
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
     * @param string $projectId
     * @return Resource
     */
    public function getProject($projectId)
    {
        return $this->get($this->getProjectsBaseUrl() . $projectId);
    }

    /**
     * @param array $projectData
     * @return Resource
     */
    public function postProject(array $projectData)
    {
        return $this->post($this->getProjectsBaseUrl(), $projectData);
    }

    /**
     * @param array $links
     * @return Collection
     */
    public function postToBulkResolver(array $links)
    {
        $payload = ['links' => $links];
        return $this->post($this->httpClient->getBaseUrl() . '/resolve', $payload);
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
     * @return string
     */
    public function getSubjectsBaseUrl()
    {
        return $this->httpClient->getBaseUrl() . '/subjects/';
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

        return $this->resourceFactory->createFromResponse($this->send($request));
    }
}
