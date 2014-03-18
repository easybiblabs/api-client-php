<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Relation;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\Resource\ResourceErrorException;
use EasyBib\Tests\Api\Client\Given;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Given
     */
    private $given;

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
        $this->given = new Given();

        $this->history = new HistoryPlugin();
        $this->mockResponses = new MockPlugin();

        $this->httpClient = new Client();
        $this->httpClient->addSubscriber($this->history);
        $this->httpClient->addSubscriber($this->mockResponses);

        $this->api = new ApiTraverser($this->httpClient);
    }

    /**
     * @return array
     */
    public function dataProviderResourceWithRelations()
    {
        return [
            [
                json_decode(json_encode([
                    'data' => [
                        'foo' => 'bar',
                    ],
                    'links' => [
                        [
                            'href' => 'http://api.example.org/foo/bar/',
                            'rel' => 'foo',
                            'type' => 'application/vnd.com.easybib.data+json',
                            'title' => 'Some link',
                        ]
                    ]
                ]))
            ]
        ];
    }

    /**
     * @return array
     */
    public function dataProviderRelationsToAdd()
    {
        return [
            [
                (object) [],
                (object) ['href' => 'http://foo.example.com/user/123', 'rel' => 'author']
            ],
            [
                (object) ['links' => [ (object) ['href' => 'foo', 'rel' => 'bar'] ]],
                (object) ['href' => 'http://foo.example.com/user/123', 'rel' => 'author']
            ],
        ];
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

    public function testGet()
    {
        $firstResource = $this->getResource();

        $nextResource = [
            'data' => [
                'foo' => 'bar',
            ]
        ];

        $this->given->iAmReadyToRespondWithAResource($this->mockResponses, $nextResource);

        $goodLinkedResource = $firstResource->get('foo rel');
        $nullLinkedResource = $firstResource->get('no such rel');

        $this->assertInstanceOf(Resource::class, $goodLinkedResource);
        $this->assertEquals('bar', $goodLinkedResource->getData()->foo);
        $this->assertNull($nullLinkedResource);
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

    /**
     * @dataProvider dataProviderResourceWithRelations
     * @param string $json
     */
    public function testGetRelations($json)
    {
        $resource = $this->getResource($json);

        $this->assertInternalType('array', $resource->getRelations());
        $this->assertInstanceOf(Relation::class, $resource->getRelations()[0]);

        $this->assertEquals(
            [
                new Relation(
                    (object) [
                        'href' => 'http://api.example.org/foo/bar/',
                        'rel' => 'foo',
                        'type' => 'application/vnd.com.easybib.data+json',
                        'title' => 'Some link',
                    ]
                )
            ],
            $resource->getRelations()
        );
    }

    /**
     * @dataProvider dataProviderResourceWithRelations
     * @param string $json
     */
    public function testListRelations($json)
    {
        $resource = $this->getResource($json);

        $this->assertEquals(['foo'], $resource->listRelations());
    }

    /**
     * @dataProvider dataProviderResourceWithRelations
     * @param string $json
     */
    public function testHasRelation($json)
    {
        $resource = $this->getResource($json);

        $this->assertTrue($resource->hasRelation('foo'));
        $this->assertFalse($resource->hasRelation('bar'));
    }

    /**
     * @dataProvider dataProviderRelationsToAdd
     * @param string $resourceData
     * @param \stdClass $relationToAdd
     */
    public function testAddRelation($resourceData, \stdClass $relationToAdd)
    {
        $resource = $this->getResource($resourceData);
        $originalRelations = $resource->getRelations();

        $resource->addRelation($relationToAdd);

        $expectedRelations = array_merge($originalRelations, [new Relation($relationToAdd)]);
        $this->assertEquals($expectedRelations, $resource->getRelations());
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

    public function testFindLink()
    {
        $resource = $this->getResource();

        $goodLink = $resource->findRelation('foo rel');
        $nullLink = $resource->findRelation('no such rel');

        $this->assertInstanceOf(Relation::class, $goodLink);
        $this->assertEquals('http://foo/', $goodLink->getHref());
        $this->assertNull($nullLink);
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
