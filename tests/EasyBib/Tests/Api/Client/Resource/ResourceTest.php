<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\Resource\ResourceErrorException;
use EasyBib\Api\Client\Validation\ResourceNotFoundException;
use EasyBib\Tests\Api\Client\ApiMockResponses;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiMockResponses
     */
    private $apiResponses;

    /**
     * @var HistoryPlugin
     */
    private $history;

    /**
     * @var MockPlugin
     */
    private $mockResponses;

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
        $this->history = new HistoryPlugin();
        $this->mockResponses = new MockPlugin();

        $this->httpClient = new Client();
        $this->httpClient->addSubscriber($this->history);
        $this->httpClient->addSubscriber($this->mockResponses);

        $this->api = new ApiTraverser($this->httpClient);
        $this->apiResponses = new ApiMockResponses($this->mockResponses);
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

        $this->assertInstanceOf(Resource::class, $goodLinkedResource);
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

        $this->assertInstanceOf(Resource::class, $goodLinkedResource);
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

        $this->assertInstanceOf(Resource::class, $goodLinkedResource);
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

        $firstResource->delete('foo rel', $nextResource);
        $lastRequest = $this->history->getLastRequest();
        $this->assertEquals('DELETE', $lastRequest->getMethod());
        $this->assertEquals('http://foo/', $lastRequest->getUrl());
    }

    public function testDeleteWithBadRed()
    {
        $firstResource = $this->getResource();

        $nextResource = ['status' => 'ok'];

        $this->apiResponses->prepareResource($nextResource);

        $this->setExpectedException(ResourceNotFoundException::class);
        $firstResource->delete('no such rel', []);
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
        $resource = Resource::factory($data, $this->api);
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
        $resource = new Resource(new \stdClass(), $this->api);
        $resource->setLocation([]);
    }

    public function testToArray()
    {
        $resource = $this->getResource((object) ['data' => (object) ['foo' => 'bar']]);
        $this->assertEquals(['data' => ['foo' => 'bar']], $resource->toArray());
    }

    public function testFactory()
    {
        $listData = json_decode(json_encode([
            'data' => [
                [
                    'foo' => 'bar',
                ],
            ],
            'links' => [],
        ]));

        $hashData = json_decode(json_encode([
            'data' => [
                'foo' => 'bar',
            ],
            'links' => [],
        ]));

        // used with responses to DELETE requests
        $noData = (object) ['links' => []];

        $this->assertInstanceOf(Collection::class, $this->getResource($listData));
        $this->assertNotInstanceOf(Collection::class, $this->getResource($hashData));
        $this->assertNotInstanceOf(Collection::class, $this->getResource($noData));
    }

    public function testFactoryWithError()
    {
        $message = 'somn done messed up';

        $data = (object) [
            'status' => 'error',
            'message' => $message,
        ];

        $this->setExpectedException(
            ResourceErrorException::class,
            $message
        );

        Resource::factory($data, $this->api);
    }

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

        return Resource::factory($data, $this->api);
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

        $response = new Response(200);
        $response->setBody($body);
        $response->setHeaders($headers);

        return Resource::fromResponse($response, $this->api);
    }
}
