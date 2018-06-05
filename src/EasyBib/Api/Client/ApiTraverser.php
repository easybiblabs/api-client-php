<?php

namespace EasyBib\Api\Client;

use Doctrine\Common\Cache\CacheProvider;
use EasyBib\Api\Client\ApiResource\ApiPromise;
use EasyBib\Api\Client\ApiResource\ApiResource;
use EasyBib\Api\Client\ApiResource\Collection;
use EasyBib\Api\Client\ApiResource\ResourceFactory;
use EasyBib\Api\Client\Validation\ResponseValidatorMiddleware;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods, PHPMD.CouplingBetweenObjects)
 */
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
     * @param CacheProvider $cacheProvider
     */
    public function __construct(ClientInterface $httpClient, CacheProvider $cacheProvider)
    {
        $this->setCache($cacheProvider);

        $this->httpClient = $httpClient;
        $this->resourceFactory = new ResourceFactory($this);

        /** @var HandlerStack $handler */
        $handler = $httpClient->getConfig('handler');
        $handler->remove('http_errors');
        $handler->remove('allow_redirects');
        $this->addHeader('Accept', 'application/vnd.com.easybib.data+json');
        $handler->push(Middleware::mapResponse(new ResponseValidatorMiddleware()));
    }

    /**
     * @param string $header
     * @param string $value
     */
    public function addHeader($header, $value)
    {
        /** @var HandlerStack $handler */
        $handler = $this->httpClient->getConfig('handler');
        $handler->push(Middleware::mapRequest(function (RequestInterface $request) use ($header, $value) {
            return $request->withHeader($header, $value);
        }));
    }

    /**
     * @param string|Uri $url
     * @param array $queryParams
     * @return ApiResource
     */
    public function get($url, array $queryParams = null)
    {
        return $this->cache->cacheAndReturn(function () use ($url, $queryParams) {
            $uri = new Uri($url);
            if (null !== $queryParams) {
                $uri = $uri->withQuery(http_build_query($queryParams));
            }
            $response = $this->httpClient->request('GET', $uri);

            return $this->resourceFactory->createFromResponse($response);
        }, new CacheKey([$url, $queryParams]));
    }

    /**
     * @param string|Uri $url
     * @param array $resource
     * @return ApiResource
     */
    public function post($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResource('post', $url, $resource);
    }

    /**
     * @param string|Uri $url
     * @param array $resource
     * @return ApiResource
     */
    public function put($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResource('put', $url, $resource);
    }

    /**
     * @param string|Uri $url
     * @param array $resource
     * @return ApiResource
     */
    public function patch($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResource('patch', $url, $resource);
    }

    /**
     * @param string|Uri $url
     * @return ApiResource
     */
    public function delete($url)
    {
        $this->cache->clear();
        $response = $this->httpClient->request('delete', $url);

        return $this->resourceFactory->createFromResponse($response);
    }

    /**
     * @param string|Uri $url
     * @param array $queryParams
     * @return ApiPromise
     */
    public function getAsync($url, array $queryParams = null)
    {
        return $this->cache->cacheAndReturn(function () use ($url, $queryParams) {
            $uri = new Uri($url);
            if (null !== $queryParams) {
                $uri = $uri->withQuery(http_build_query($queryParams));
            }
            $responsePromise = $this->httpClient->requestAsync('GET', $uri);

            return new ApiPromise($responsePromise, $this->resourceFactory);
        }, new CacheKey([$url, $queryParams]));
    }

    /**
     * @param string|Uri $url
     * @param array $resource
     * @return ApiPromise
     */
    public function postAsync($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResourceAsync('post', $url, $resource);
    }

    /**
     * @param string|Uri $url
     * @param array $resource
     * @return ApiPromise
     */
    public function putAsync($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResourceAsync('put', $url, $resource);
    }

    /**
     * @param string|Uri $url
     * @param array $resource
     * @return ApiPromise
     */
    public function patchAsync($url, array $resource)
    {
        $this->cache->clear();
        return $this->sendResourceAsync('patch', $url, $resource);
    }

    /**
     * @param string|Uri $url
     * @return ApiPromise
     */
    public function deleteAsync($url)
    {
        $this->cache->clear();
        $responsePromise = $this->httpClient->requestAsync('delete', $url);

        return new ApiPromise($responsePromise, $this->resourceFactory);
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
     * @return ApiResource
     */
    public function getSubject($subjectId)
    {
        return $this->get($this->getSubjectsBaseUrl() . $subjectId);
    }

    /**
     * This bootstraps the session by returning the user's "root" Resource
     *
     * @return ApiResource
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
     * @return ApiResource
     */
    public function getProject($projectId)
    {
        return $this->get($this->getProjectsBaseUrl() . $projectId);
    }

    /**
     * @param array $projectData
     * @return ApiResource
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
        return $this->post($this->httpClient->getConfig('base_uri') . '/resolve', $payload);
    }

    /**
     * @return string
     */
    public function getUserBaseUrl()
    {
        return $this->httpClient->getConfig('base_uri') . '/user/';
    }

    /**
     * @return string
     */
    public function getProjectsBaseUrl()
    {
        return $this->httpClient->getConfig('base_uri') . '/projects/';
    }

    /**
     * @return string
     */
    public function getSubjectsBaseUrl()
    {
        return $this->httpClient->getConfig('base_uri') . '/subjects/';
    }

    /**
     * @param CacheProvider $cacheProvider
     */
    public function setCache(CacheProvider $cacheProvider)
    {
        $this->cache = new Cache($cacheProvider);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $resource
     * @return ApiResource
     */
    private function sendResource($method, $url, array $resource)
    {
        $payload = json_encode($resource);
        $response = $this->httpClient->request($method, $url, ['body' => $payload]);

        return $this->resourceFactory->createFromResponse($response);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $resource
     * @return ApiPromise
     */
    private function sendResourceAsync($method, $url, array $resource)
    {
        $payload = json_encode($resource);
        $responsePromise = $this->httpClient->requestAsync($method, $url, ['body' => $payload]);

        return new ApiPromise($responsePromise, $this->resourceFactory);
    }
}
