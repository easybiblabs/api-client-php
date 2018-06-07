<?php

namespace EasyBib\Tests\Api\Client\ApiResource;

use Doctrine\Common\Cache\ArrayCache;
use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\ApiResource\ApiResource;
use EasyBib\Api\Client\ApiResource\ResourceFactory;
use EasyBib\Api\Client\Validation\ResourceNotFoundException;
use EasyBib\Tests\Api\Client\ApiMockResponses;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class ApiResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var MockHandler */
    protected $mockHandler;

    /**
     * @var ApiMockResponses
     */
    private $apiResponses;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var ApiTraverser
     */
    private $api;

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $this->httpClient = new Client(['handler' => HandlerStack::create($this->mockHandler)]);

        $this->api = new ApiTraverser($this->httpClient, new ArrayCache());
        $this->apiResponses = new ApiMockResponses($this->mockHandler);
    }

    public function testGetWithGoodRel()
    {
        $firstResource = $this->getResource();

        $nextResource = [
            'data' => [
                'foo' => 'bar',
            ]
        ];

        $this->apiResponses->prepareResource($nextResource);

        $goodLinkedResource = $firstResource->get('foo rel');

        $this->assertInstanceOf(ApiResource::class, $goodLinkedResource);
        $this->assertEquals('bar', $goodLinkedResource->getData()->foo);
    }

    public function testGetWithBadRel()
    {
        $firstResource = $this->getResource();

        $nextResource = [
            'data' => [
                'foo' => 'bar',
            ]
        ];

        $this->apiResponses->prepareResource($nextResource);

        $this->setExpectedException(ResourceNotFoundException::class);
        $firstResource->get('no such rel');
    }

    public function testPostWithGoodRel()
    {
        $firstResource = $this->getResource();

        $nextResource = [
            'data' => [
                'foo' => 'bar',
            ]
        ];

        $this->apiResponses->prepareResource($nextResource);

        $goodLinkedResource = $firstResource->post('foo rel', $nextResource);

        $this->assertInstanceOf(ApiResource::class, $goodLinkedResource);
        $this->assertEquals('bar', $goodLinkedResource->getData()->foo);
    }

    public function testPostWithBadRel()
    {
        $firstResource = $this->getResource();

        $nextResource = [
            'data' => [
                'foo' => 'bar',
            ]
        ];

        $this->apiResponses->prepareResource($nextResource);

        $this->setExpectedException(ResourceNotFoundException::class);
        $firstResource->post('no such rel', []);
    }

    public function testPutWithGoodRel()
    {
        $firstResource = $this->getResource();

        $nextResource = [
            'data' => [
                'foo' => 'bar',
            ]
        ];

        $this->apiResponses->prepareResource($nextResource);

        $goodLinkedResource = $firstResource->put('foo rel', $nextResource);

        $this->assertInstanceOf(ApiResource::class, $goodLinkedResource);
        $this->assertEquals('bar', $goodLinkedResource->getData()->foo);
    }

    public function testPutWithBadRel()
    {
        $firstResource = $this->getResource();

        $nextResource = [
            'data' => [
                'foo' => 'bar',
            ]
        ];

        $this->apiResponses->prepareResource($nextResource);

        $this->setExpectedException(ResourceNotFoundException::class);
        $firstResource->put('no such rel', []);
    }

    public function testDeleteWithGoodRel()
    {
        $firstResource = $this->getResource();

        $nextResource = ['status' => 'ok'];

        $this->apiResponses->prepareResource($nextResource);

        $firstResource->delete('foo rel');
        $lastRequest = $this->mockHandler->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest->getMethod());
        $this->assertEquals('http://foo/', $lastRequest->getUri());
    }

    public function testDeleteWithBadRed()
    {
        $firstResource = $this->getResource();

        $nextResource = ['status' => 'ok'];

        $this->apiResponses->prepareResource($nextResource);

        $this->setExpectedException(ResourceNotFoundException::class);
        $firstResource->delete('no such rel');
    }

    /**
     * @return array
     */
    public function dataProviderGetId()
    {
        return [
            [
                json_decode(json_encode([
                    'links' => [
                    ]
                ])),
                null,
            ],
            [
                json_decode(json_encode([
                    'links' => [
                        [
                            'rel' => 'me',
                            'href' => 'http://foo/bar/baz/',
                        ]
                    ]
                ])),
                null,
            ],
            [
                json_decode(json_encode([
                    'links' => [
                        [
                            'rel' => 'me',
                            'href' => 'http://foo/bar/baz/123',
                        ]
                    ]
                ])),
                '123',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGetId
     * @param \stdClass $data
     * @param mixed $expectedValue
     */
    public function testGetId(\stdClass $data, $expectedValue)
    {
        $resource = $this->getResourceFactory()->createFromData($data);
        $this->assertSame($expectedValue, $resource->getId());
    }

    public function testGetData()
    {
        $resource = $this->getResource(json_decode(json_encode(['data' => ['foo' => 'bar']])));
        $this->assertEquals((object) ['foo' => 'bar'], $resource->getData());
    }

    public function testGetLocation()
    {
        $location = 'http://example.com/foo/bar.doc';

        $resource = $this->getResourceFromResponse(
            '{"data":{"foo":"bar"}}',
            ['Location' => $location]
        );

        $this->assertEquals($location, $resource->getLocation());
    }

    public function testSetLocationWhereInvalid()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $resource = new ApiResource(new \stdClass(), $this->api);
        $resource->setLocation([]);
    }

    public function testToArray()
    {
        $resource = $this->getResource((object) ['data' => (object) ['foo' => 'bar']]);
        $this->assertEquals(['data' => ['foo' => 'bar']], $resource->toArray());
    }

    public function testIsCurrentUserAuthor()
    {
        $resource = $this->getResource((object) ['data' => (object) ['foo' => 'bar']]);
        $this->assertFalse($resource->isCurrentUserAuthor());
    }

    public function testIsCurrentUserAuthorNoAuthorRelation()
    {
        $resource = $this->getResource((object) [
            'data' => (object) ['foo' => 'bar'],
            'links' => [
                (object) [
                    'href' => '/user/',
                    'rel' => 'author',
                    'type' => 'application/vnd.com.easybib.data+json',
                    'title' => 'user@example.com',
                ]
            ]
        ]);
        $this->assertTrue($resource->isCurrentUserAuthor());
    }

    /**
     * @param \stdClass $data
     * @return ApiResource
     */
    private function getResource(\stdClass $data = null)
    {
        if (!$data) {
            $data = (object) [
                'data' => (object) ['foo' => 'bar'],
                'links' => [
                    (object) [
                        'href' => 'http://foo/',
                        'rel' => 'foo rel',
                        'type' => 'text',
                        'title' => 'The Foo',
                    ]
                ],
            ];
        }

        return $this->getResourceFactory()->createFromData($data);
    }

    /**
     * @param string $body
     * @param array $headers
     * @return Resource
     */
    private function getResourceFromResponse($body = null, array $headers = [])
    {
        if (!$body) {
            $body = json_encode([
                'data' => ['foo' => 'bar'],
                'links' => [
                    [
                        'href' => 'http://foo/',
                        'rel' => 'foo rel',
                        'type' => 'text',
                        'title' => 'The Foo',
                    ]
                ],
            ]);
        }

        return $this->getResourceFactory()->createFromResponse(new Response(200, $headers, $body));
    }

    /**
     * @return ResourceFactory
     */
    private function getResourceFactory()
    {
        return new ResourceFactory($this->api);
    }
}
