<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Reference;
use EasyBib\Api\Client\Resource\Resource;
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
    public function dataProviderForReferences()
    {
        return [
            ['{"data":{"foo":"bar"},"links":[{"href":"http://api.example.org/foo/bar/","rel":"foo",
                "type":"application/vnd.com.easybib.data+json","title":"Some link"}]}']
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

    public function testGetData()
    {
        $resource = $this->getResource('{"data":{"foo":"bar"}}');
        $this->assertEquals((object) ['foo' => 'bar'], $resource->getData());
    }

    /**
     * @dataProvider dataProviderForReferences
     * @param string $json
     */
    public function testGetReferences($json)
    {
        $resource = $this->getResource($json);

        $this->assertInternalType('array', $resource->getReferences());
        $this->assertInstanceOf(Reference::class, $resource->getReferences()[0]);

        $this->assertEquals(
            [
                new Reference(
                    (object) [
                        'href' => 'http://api.example.org/foo/bar/',
                        'rel' => 'foo',
                        'type' => 'application/vnd.com.easybib.data+json',
                        'title' => 'Some link',
                    ]
                )
            ],
            $resource->getReferences()
        );
    }

    /**
     * @dataProvider dataProviderForReferences
     * @param string $json
     */
    public function testListReferences($json)
    {
        $resource = $this->getResource($json);

        $this->assertEquals(['foo'], $resource->listReferences());
    }

    /**
     * @dataProvider dataProviderForReferences
     * @param string $json
     */
    public function testHasReference($json)
    {
        $resource = $this->getResource($json);

        $this->assertTrue($resource->hasReference('foo'));
        $this->assertFalse($resource->hasReference('bar'));
    }

    public function testToArray()
    {
        $resource = $this->getResource('{"data":{"foo":"bar"}}');
        $this->assertEquals(['data' => ['foo' => 'bar']], $resource->toArray());
    }

    public function testFactory()
    {
        $listData = '{"data":[{"foo":"bar"}],"links":[]}';
        $hashData = '{"data":{"foo":"bar"},"links":[]}';

        $this->assertInstanceOf(Collection::class, $this->getResource($listData));
        $this->assertNotInstanceOf(Collection::class, $this->getResource($hashData));
    }

    public function testFindLink()
    {
        $resource = $this->getResource();

        $goodLink = $resource->findReference('foo rel');
        $nullLink = $resource->findReference('no such rel');

        $this->assertInstanceOf(Reference::class, $goodLink);
        $this->assertEquals('http://foo/', $goodLink->getHref());
        $this->assertNull($nullLink);
    }

    /**
     * @param string $body
     * @return Resource
     */
    private function getResource($body = null)
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

        return Resource::fromResponse($response, $this->api);
    }
}
