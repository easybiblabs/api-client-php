<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\ExpiredTokenException;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Reference;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\Authorization\AuthorizationResponse;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\ClientConfig;
use EasyBib\OAuth2\Client\ServerConfig;
use EasyBib\OAuth2\Client\TokenStore;
use Guzzle\Http\Client;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ApiTraverserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Given
     */
    protected $given;

    /**
     * @var string
     */
    protected $apiBaseUrl = 'http://data.easybib.example.com';

    /**
     * @var HistoryPlugin
     */
    protected $history;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var ApiTraverser
     */
    protected $api;

    /**
     * @var MockPlugin
     */
    protected $mockResponses;

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

    public function setUp()
    {
        parent::setUp();

        $this->given = new Given();

        $this->clientConfig = new ClientConfig([
            'client_id' => 'client_123',
            'redirect_url' => 'http://myapp.example.com/',
        ]);

        $this->serverConfig = new ServerConfig([
            'authorization_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);

        $this->httpClient = new Client($this->apiBaseUrl);
        $this->mockResponses = new MockPlugin();
        $this->history = new HistoryPlugin();
        $this->httpClient->addSubscriber($this->mockResponses);
        $this->httpClient->addSubscriber($this->history);

        $this->tokenStore = new TokenStore(new Session(new MockArraySessionStorage()));
        $this->authorization = new AuthorizationResponse(['code' => 'ABC123']);

        $this->api = new ApiTraverser($this->httpClient);
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

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $collection);

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

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $user);

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

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $projects);

        $response = $this->api->getProjects();

        $this->shouldHaveMadeAnApiRequest('GET');
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

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $collection);

        $response = $this->api->get('citations');

        $this->shouldHaveMadeAnApiRequest('GET');
        $this->shouldHaveReturnedACollection($collection, $response);
    }

    public function testGetPassesTokenInHeaderWithJwt()
    {
        $accessToken = 'ABC123';

        $this->given->iHaveRegisteredWithAJwtSession($accessToken, $this->httpClient);
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses);

        $this->api->get('url placeholder');

        $this->shouldHaveHadATokenWithLastRequest($accessToken);
    }

    public function testGetWithParams()
    {
        $accessToken = 'ABC123';
        $params = ['filter' => 'XYZ'];

        $this->given->iHaveRegisteredWithAJwtSession($accessToken, $this->httpClient);
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses);

        $this->api->get('url placeholder', $params);

        $this->shouldHaveMadeAnApiRequest('GET', $params);
    }

    public function testGetPassesTokenInHeaderWithAuthCodeGrant()
    {
        $accessToken = 'ABC123';

        $this->given->iHaveRegisteredWithAnAuthCodeSession($accessToken, $this->httpClient);
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses);

        $this->api->get('url placeholder');

        $this->shouldHaveHadATokenWithLastRequest($accessToken);
    }

    public function testGetWithExpiredToken()
    {
        $this->given->iAmReadyToRespondWithAnExpiredTokenError($this->mockResponses);

        $this->setExpectedException(ExpiredTokenException::class);

        $this->api->get('url placeholder');
    }

    /**
     * @dataProvider getValidCitations
     * @param array $citation
     * @param array $expectedResponseResource
     */
    public function testPost(array $citation, array $expectedResponseResource)
    {
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $expectedResponseResource);

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
        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $expectedResponseResource);

        $response = $this->api->put('/projects/123/citations/456', $citation);

        $this->shouldHaveMadeAnApiRequest('PUT');
        $this->shouldHaveReturnedAResource($expectedResponseResource, $response);
    }

    public function testDelete()
    {
        $expectedResource = [
            'data' => [],
        ];

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses);

        $response = $this->api->delete('/projects/123/citations/456');

        $this->shouldHaveMadeAnApiRequest('DELETE');
        $this->shouldHaveReturnedADeletedResource($expectedResource, $response);
    }

    /**
     * @param string $httpMethod
     * @param array $queryParams
     */
    private function shouldHaveMadeAnApiRequest($httpMethod, array $queryParams = [])
    {
        $lastRequest = $this->history->getLastRequest();

        $this->assertEquals($httpMethod, $lastRequest->getMethod());
        $this->assertEquals($queryParams, $lastRequest->getQuery()->toArray());

        $this->assertTrue(
            $lastRequest->getHeader('Accept')
                ->hasValue('application/vnd.com.easybib.data+json')
        );
    }

    /**
     * @param array $expectedResponseArray
     * @param Resource $resource
     */
    private function shouldHaveReturnedAResource(
        array $expectedResponseArray,
        Resource $resource
    ) {
        $this->assertSameData($expectedResponseArray, $resource);

        $this->assertEquals(
            $this->extractReferences($expectedResponseArray['links']),
            $resource->getReferences()
        );
    }

    /**
     * @param array $expectedResponseArray
     * @param Resource $resource
     */
    private function shouldHaveReturnedADeletedResource(
        array $expectedResponseArray,
        Resource $resource
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
     * @param Resource $resource
     */
    private function assertSameData(array $expectedResponseArray, Resource $resource)
    {
        $this->assertEquals(
            $this->recursiveCastObject($expectedResponseArray['data']),
            $resource->getData()
        );
    }

    public function shouldHaveMadeATokenRequest()
    {
        $lastRequest = $this->history->getLastRequest();

        $expectedParams = [
            'grant_type' => 'authorization_code',
            'code' => $this->authorization->getCode(),
            'redirect_uri' => $this->clientConfig->getParams()['redirect_url'],
            'client_id' => $this->clientConfig->getParams()['client_id'],
        ];

        $this->assertEquals('POST', $lastRequest->getMethod());
        $this->assertEquals($expectedParams, $lastRequest->getPostFields()->toArray());
        $this->assertEquals($this->apiBaseUrl . '/oauth/token', $lastRequest->getUrl());
    }

    /**
     * @param $accessToken
     */
    private function shouldHaveHadATokenWithLastRequest($accessToken)
    {
        $this->assertTrue(
            $this->history->getLastRequest()->getHeader('Authorization')
                ->hasValue('Bearer ' . $accessToken)
        );
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
    private function extractReferences(array $links)
    {
        return array_map(
            function ($reference) {
                return new Reference($this->recursiveCastObject($reference));
            },
            $links
        );
    }
}
