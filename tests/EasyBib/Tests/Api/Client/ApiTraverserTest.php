<?php

namespace EasyBib\Tests\Api\Client;

use Doctrine\Common\Cache\ArrayCache;
use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\CacheKey;
use EasyBib\Api\Client\ApiResource\Collection;
use EasyBib\Api\Client\ApiResource\Relation;
use EasyBib\Api\Client\ApiResource\ApiResource;
use EasyBib\Api\Client\Validation\ApiErrorException;
use EasyBib\Api\Client\Validation\ExpiredTokenException;
use EasyBib\Api\Client\Validation\InfrastructureErrorException;
use EasyBib\Api\Client\Validation\InvalidJsonException;
use EasyBib\Api\Client\Validation\ApiException;
use EasyBib\Api\Client\Validation\ResourceNotFoundException;
use EasyBib\Api\Client\Validation\UnauthorizedActionException;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\Authorization\AuthorizationResponse;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\ClientConfig;
use EasyBib\OAuth2\Client\ServerConfig;
use EasyBib\OAuth2\Client\TokenStore;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ApiTraverserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiMockResponses
     */
    protected $apiResponses;

    /**
     * @var string
     */
    protected $dataBaseUrl = 'http://data.easybib.example.com';

    /**
     * @var Client
     */
    protected $resourceHttpClient;

    /**
     * @var ApiTraverser
     */
    protected $api;

    /**
     * @var TokenStore
     */
    protected $tokenStore;

    /**
     * @var ClientConfig
     */
    protected $clientConfig;

    /**
     * @var ServerConfig
     */
    protected $serverConfig;

    /**
     * @var AuthorizationResponse
     */
    protected $authorization;

    /** @var MockHandler */
    private $mockHandler;

    public function setUp()
    {
        parent::setUp();

        $this->clientConfig = new ClientConfig([
            'client_id' => 'client_123',
            'redirect_uri' => 'http://myapp.example.com/',
        ]);

        $this->mockHandler = new MockHandler();
        $this->resourceHttpClient = new Client([
            'base_uri' => $this->dataBaseUrl,
            'handler' => HandlerStack::create($this->mockHandler),
        ]);

        $this->tokenStore = new TokenStore(new Session(new MockArraySessionStorage()));
        $this->authorization = new AuthorizationResponse(['code' => 'ABC123']);

        $this->api = new ApiTraverser($this->resourceHttpClient, new ArrayCache());
        $this->apiResponses = new ApiMockResponses($this->mockHandler);
    }

    /**
     * @return array
     */
    public function getValidCitations()
    {
        $citation = [
            'source' => 'book',
            'pubtype' => ['main' => 'pubnonperiodical'],
            'contributors' => [
                [
                    'last' => 'Salinger',
                    'first' => 'J. D.',
                    'function' => 'author',
                ]
            ],
            'pubnonperiodical' => [
                'title' => 'The Catcher in the Rye',
                'publisher' => 'Little, Brown',
                'year' => '1951',
            ]
        ];

        $expectedResponseResource = [
            'links' => [
                [
                    'href' => 'http://example.org/projects/123/citations/456',
                    'rel' => 'me',
                    'type' => 'application/vnd.com.easybib.data+json',
                    'title' => 'The Catcher in the Rye',
                ],
            ],
            'data' => $citation,
        ];

        return [
            [$citation, $expectedResponseResource],
        ];
    }

    /**
     * @return string[]
     */
    public function getWriteMethods()
    {
        return [
            ['put'],
            ['post'],
            ['patch'],
            ['delete'],
        ];
    }

    public function testGetForCollection()
    {
        $collection = [
            'data' => [
                [
                    'links' => [],
                    'data' => [
                        'href' => 'http://foo.example.com/',
                        'rel' => 'me',
                        'type' => 'text',
                        'title' => 'Bar',
                    ],
                ]
            ],
            'links' => [
            ],
        ];

        $this->apiResponses->prepareResource($collection);

        $response = $this->api->get('url placeholder');

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->shouldHaveReturnedACollection($collection, $response);
    }

    public function testGetUser()
    {
        $user = [
            'links' => [],
            'data' => [
                'first' => 'Jim',
                'last' => 'Johnson',
                'email' => 'jj@example.org',
                'role' => 'mybib',
            ]
        ];

        $this->apiResponses->prepareResource($user);

        $response = $this->api->getUser();

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->shouldHaveReturnedAResource($user, $response);
    }

    public function testGetProjects()
    {
        $projects = [
            'links' => [],
            'data' => [
                [
                    'links' => [],
                    'data' => ['foo' => 'bar'],
                ]
            ]
        ];

        $this->apiResponses->prepareResource($projects);

        $response = $this->api->getProjects();

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->shouldHaveReturnedACollection($projects, $response);
    }

    public function testGetProjectsWithParams()
    {
        $projects = [
            'links' => [],
            'data' => [
                [
                    'links' => [],
                    'data' => ['foo' => 'bar'],
                ]
            ]
        ];

        $this->apiResponses->prepareResource($projects);

        $queryParams = ['foo' => 'bar'];

        $response = $this->api->getProjects($queryParams);

        $this->shouldHaveMadeAnApiRequest('GET', $queryParams);
        $this->shouldHaveReturnedACollection($projects, $response);
    }

    public function testGetUrlWithQuery()
    {
        $this->apiResponses->prepareResource([]);

        $response = $this->api->get('foo/?x=y');

        $this->shouldHaveMadeAnApiRequest('GET', ['x' => 'y']);
    }

    public function testGetProject()
    {
        $project = [
            'data' => ['foo' => 'bar'],
            'links' => [],
        ];

        $projectId = '123';

        $this->apiResponses->prepareResource($project);

        $response = $this->api->getProject($projectId);

        $lastRequest = $this->mockHandler->getLastRequest();

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->assertEquals($this->api->getProjectsBaseUrl() . $projectId, $lastRequest->getUri());
        $this->shouldHaveReturnedAResource($project, $response);
    }

    public function testPostProject()
    {
        $project = [
            'data' => ['foo' => 'bar'],
            'links' => [],
        ];

        $this->apiResponses->prepareResource($project);

        $response = $this->api->postProject($project);
        $lastRequest = $this->mockHandler->getLastRequest();

        $this->shouldHaveMadeAnApiRequest('POST');
        $this->assertEquals($this->api->getProjectsBaseUrl(), $lastRequest->getUri());
        $this->shouldHaveReturnedAResource($project, $response);
    }

    public function testPostToBulkResolver()
    {
        $projects = [
            'data' => [
                [
                    'data' => ['foo' => 'bar'],
                    'links' =>  [
                        [
                            'rel' => 'me',
                            'href' => 'foo',
                        ],
                    ],
                ],
            ],
        ];

        $links = [
            [
                'href' => 'foo',
            ],
        ];

        $this->apiResponses->prepareResource($projects);

        $response = $this->api->postToBulkResolver($links);
        $lastRequest = $this->mockHandler->getLastRequest();

        $this->shouldHaveMadeAnApiRequest('POST');
        $this->assertEquals($this->dataBaseUrl . '/resolve', $lastRequest->getUri());
        $this->assertEquals(json_encode(['links' => $links]), (string)$lastRequest->getBody());
        $this->shouldHaveReturnedACollection($projects, $response);
    }

    public function testGetCitationsReturnsCollection()
    {
        $collection = [
            'links' => [],
            'data' => [
                [
                    'links' => [],
                    'data' => [
                        'source' => 'book',
                        'pubtype' => ['main' => 'pubnonperiodical'],
                    ],
                ],
            ]
        ];

        $this->apiResponses->prepareResource($collection);

        $response = $this->api->get('citations');

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->shouldHaveReturnedACollection($collection, $response);
    }

    public function testGetPassesTokenInHeaderWithJwt()
    {
        $accessToken = 'ABC123';

        $this->apiResponses->registerWithJwtSession($accessToken, $this->resourceHttpClient);
        $this->apiResponses->prepareResource();

        $this->api->get('url placeholder');

        $this->shouldHaveHadATokenWithLastRequest($accessToken);
    }

    public function testGetWithParams()
    {
        $accessToken = 'ABC123';
        $params = ['filter' => 'XYZ'];

        $this->apiResponses->registerWithJwtSession($accessToken, $this->resourceHttpClient);
        $this->apiResponses->prepareResource();

        $this->api->get('url placeholder', $params);

        $this->shouldHaveMadeAnApiRequest('GET', $params);
    }

    public function testGetPassesTokenInHeaderWithAuthCodeGrant()
    {
        $accessToken = 'ABC123';

        $this->apiResponses->registerWithAuthCodeSession($accessToken, $this->resourceHttpClient);
        $this->apiResponses->prepareResource();

        $this->api->get('url placeholder');

        $this->shouldHaveHadATokenWithLastRequest($accessToken);
    }

    public function testGetWithExpiredToken()
    {
        $this->apiResponses->prepareExpiredTokenError();

        $this->setExpectedException(ExpiredTokenException::class);

        $this->api->get('url placeholder');
    }

    public function testGetWithUnauthorizedProject()
    {
        $this->apiResponses->prepareUnauthorizedTokenError();

        $this->setExpectedException(
            UnauthorizedActionException::class,
            'for this token'
        );

        $this->api->get('url placeholder');
    }

    public function testGetWithInvalidJson()
    {
        $this->apiResponses->prepareInvalidJson();

        $this->setExpectedException(InvalidJsonException::class);

        $this->api->get('url placeholder');
    }

    public function testGetWithInfrastructureError()
    {
        $this->apiResponses->prepareInfrastructureError(504);

        $this->setExpectedException(InfrastructureErrorException::class, (string)504);

        $this->api->get('url placeholder');
    }

    public function testGetWithNotFoundError()
    {
        $response = [
            'status' => 'error',
            'msg' => 'Not Found',
        ];

        $this->apiResponses->prepareApiError($response, 404);

        $this->setExpectedException(
            ResourceNotFoundException::class,
            $response['msg'],
            404
        );

        $this->api->get('url placeholder');
    }

    public function testGetWithErrorMessageInJson()
    {
        $response = [
            'error' => 'fail',
            'error_description' => 'You done messed up good.',
        ];

        $this->apiResponses->prepareApiError($response);

        $this->setExpectedException(
            ApiErrorException::class,
            $response['error_description'],
            400
        );

        $this->api->get('url placeholder');
    }

    public function testGetWithMsgInJson()
    {
        $message = 'What you done now?';

        $this->apiResponses->prepareApiMsg($message);

        $this->setExpectedException(
            ApiErrorException::class,
            $message,
            400
        );

        $this->api->get('url placeholder');
    }

    public function testGetWithGenericHttpError()
    {
        $response = [
            'foo' => 'bar',
        ];

        $this->apiResponses->prepareApiError($response);

        $this->setExpectedException(
            ApiException::class,
            var_export($response, true),
            500
        );

        $this->api->get('url placeholder');
    }

    /**
     * @dataProvider getValidCitations
     * @param array $citation
     * @param array $expectedResponseResource
     */
    public function testPost(array $citation, array $expectedResponseResource)
    {
        $this->apiResponses->prepareResource($expectedResponseResource);

        $response = $this->api->post('/projects/123/citations', $citation);

        $this->shouldHaveMadeAnApiRequest('POST');
        $this->shouldHaveReturnedAResource($expectedResponseResource, $response);
    }

    /**
     * @dataProvider getValidCitations
     * @param array $citation
     * @param array $expectedResponseResource
     */
    public function testPut(array $citation, array $expectedResponseResource)
    {
        $this->apiResponses->prepareResource($expectedResponseResource);

        $response = $this->api->put('/projects/123/citations/456', $citation);

        $this->shouldHaveMadeAnApiRequest('PUT');
        $this->shouldHaveReturnedAResource($expectedResponseResource, $response);
    }

    public function testDelete()
    {
        $expectedResource = [
            'data' => [],
        ];

        $this->apiResponses->prepareResource();

        $response = $this->api->delete('/projects/123/citations/456');

        $this->shouldHaveMadeAnApiRequest('DELETE');
        $this->shouldHaveReturnedADeletedResource($expectedResource, $response);
    }

    public function testPatch()
    {
        $project = [
            'links' => [],
            'data' => [
                'name' => 'Some project',
            ],
        ];

        $this->apiResponses->prepareResource($project);

        $resourcePatch = [
            'href' => 'http://foo.example.com/user/456',
            'rel' => 'author',
        ];

        $this->api->patch('/projects/123', $resourcePatch);

        $this->shouldHaveMadeAnApiRequest('PATCH');
    }

    public function testGetDoesNotFollowRedirects()
    {
        $this->mockHandler->append(new Response(302, ['Location' => 'http://foo.example.com/'], '{}'));
        $this->mockHandler->append(new Response(200, [], '{}'));

        $this->api->getUser();

        $this->assertEquals(1, $this->mockHandler->count());
    }

    public function testGetWritesToCache()
    {
        $cache = new ArrayCache();
        $this->api->setCache($cache);

        $this->apiResponses->prepareResource();

        $url = '/';
        $arguments = ['jim' => 'bob'];
        $key = (new CacheKey([$url, $arguments]))->toString();

        $this->api->get($url, $arguments);
        $this->assertTrue($cache->contains($key));
        $this->assertInstanceOf(ApiResource::class, $cache->fetch($key));
    }

    /**
     * @dataProvider getWriteMethods
     * @param string $method
     */
    public function testWritesClearCache($method)
    {
        $cache = new ArrayCache();
        $this->api->setCache($cache);

        $this->apiResponses->prepareResource();
        $this->apiResponses->prepareResource();

        $url = '/';
        $arguments = ['jim' => 'bob'];
        $key = (new CacheKey([$url, $arguments]))->toString();

        $this->api->get($url, $arguments);
        $this->api->$method('/', ['foo' => 'bar']);
        $this->assertFalse($cache->contains($key));
    }

    public function testGetUserBaseUrl()
    {
        $this->assertEquals($this->dataBaseUrl . '/user/', $this->api->getUserBaseUrl());
    }

    public function testGetProjectsBaseUrl()
    {
        $this->assertEquals($this->dataBaseUrl . '/projects/', $this->api->getProjectsBaseUrl());
    }

    public function testAsyncGet()
    {
        $this->apiResponses->prepareResource([]);

        $apiPromise = $this->api->getAsync('foo/', ['x' => 'y']);
        $apiPromise->wait();

        $this->shouldHaveMadeAnApiRequest('GET', ['x' => 'y']);
    }

    /**
     * @dataProvider getValidCitations
     * @param array $citation
     * @param array $expectedResponseResource
     */
    public function testAsyncPost(array $citation, array $expectedResponseResource)
    {
        $this->apiResponses->prepareResource($expectedResponseResource);

        $apiPromise = $this->api->postAsync('/projects/123/citations', $citation);
        $response = $apiPromise->wait();

        $this->shouldHaveMadeAnApiRequest('POST');
        $this->shouldHaveReturnedAResource($expectedResponseResource, $response);
    }

    /**
     * @dataProvider getValidCitations
     * @param array $citation
     * @param array $expectedResponseResource
     */
    public function testAsyncPut(array $citation, array $expectedResponseResource)
    {
        $this->apiResponses->prepareResource($expectedResponseResource);

        $apiPromise = $this->api->putAsync('/projects/123/citations/456', $citation);
        $response = $apiPromise->wait();

        $this->shouldHaveMadeAnApiRequest('PUT');
        $this->shouldHaveReturnedAResource($expectedResponseResource, $response);
    }

    public function testDeleteAsync()
    {
        $expectedResource = [
            'data' => [],
        ];

        $this->apiResponses->prepareResource();

        $apiPromise = $this->api->deleteAsync('/projects/123/citations/456');
        $response = $apiPromise->wait();

        $this->shouldHaveMadeAnApiRequest('DELETE');
        $this->shouldHaveReturnedADeletedResource($expectedResource, $response);
    }

    public function testPatchAsync()
    {
        $project = [
            'links' => [],
            'data' => [
                'name' => 'Some project',
            ],
        ];

        $this->apiResponses->prepareResource($project);

        $resourcePatch = [
            'href' => 'http://foo.example.com/user/456',
            'rel' => 'author',
        ];

        $apiPromise = $this->api->patchAsync('/projects/123', $resourcePatch);
        $apiPromise->wait();

        $this->shouldHaveMadeAnApiRequest('PATCH');
    }

    /**
     * @param string $httpMethod
     * @param array $queryParams
     */
    private function shouldHaveMadeAnApiRequest($httpMethod, array $queryParams = [])
    {
        $lastRequest = $this->mockHandler->getLastRequest();

        $this->assertEquals($httpMethod, $lastRequest->getMethod());
        $parsedQuery = \GuzzleHttp\Psr7\parse_query($lastRequest->getUri()->getQuery());
        $this->assertEquals($queryParams, $parsedQuery);

        $this->assertTrue(
            in_array('application/vnd.com.easybib.data+json', $lastRequest->getHeader('Accept'))
        );
    }

    /**
     * @param array $expectedResponseArray
     * @param ApiResource $resource
     */
    private function shouldHaveReturnedAResource(
        array $expectedResponseArray,
        ApiResource $resource
    ) {
        $this->assertSameData($expectedResponseArray, $resource);

        $this->assertEquals(
            $this->extractRelations($expectedResponseArray['links']),
            $resource->getRelationsContainer()->getAll()
        );
    }

    /**
     * @param array $expectedResponseArray
     * @param ApiResource $resource
     */
    private function shouldHaveReturnedADeletedResource(
        array $expectedResponseArray,
        ApiResource $resource
    ) {
        $this->assertSameData($expectedResponseArray, $resource);
    }

    /**
     * @param array $expectedResponseArray
     * @param Collection $collection
     */
    private function shouldHaveReturnedACollection(
        array $expectedResponseArray,
        Collection $collection
    ) {
        $this->assertEquals(count($expectedResponseArray['data']), count($collection));
    }

    /**
     * @param array $expectedResponseArray
     * @param ApiResource $resource
     */
    private function assertSameData(array $expectedResponseArray, ApiResource $resource)
    {
        $this->assertEquals(
            $this->recursiveCastObject($expectedResponseArray['data']),
            $resource->getData()
        );
    }

    /**
     * @param $accessToken
     */
    private function shouldHaveHadATokenWithLastRequest($accessToken)
    {
        $this->assertTrue(in_array(
            'Bearer ' . $accessToken,
            $this->mockHandler->getLastRequest()->getHeader('Authorization')
        ));
    }

    /**
     * @param array $array
     * @return \stdClass
     */
    private function recursiveCastObject(array $array)
    {
        return json_decode(json_encode($array));
    }

    /**
     * @param array $links
     * @return array
     */
    private function extractRelations(array $links)
    {
        return array_map(
            function ($relation) {
                return new Relation($this->recursiveCastObject($relation));
            },
            $links
        );
    }
}
