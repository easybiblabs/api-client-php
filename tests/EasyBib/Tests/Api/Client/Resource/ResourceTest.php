<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Reference;
use EasyBib\Api\Client\ResourceDataContainer;
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

    public function testMagicGet()
    {
        $resource = $this->getResource('{"data":{"foo":"bar"}}');
        $this->assertEquals('bar', $resource->foo);
    }

    public function testMagicIsset()
    {
        $resource = $this->getResource('{"data":{"foo":"bar"}}');
        $this->assertTrue(isset($resource->foo));
        $this->assertFalse(isset($resource->baz));
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
        $this->assertEquals('bar', $goodLinkedResource->foo);
        $this->assertNull($nullLinkedResource);
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
            $body = '{"links":[{"href":"http://foo/","rel":"foo rel","type":"text","title":"The Foo"}]}';
        }

        $response = new Response(200);
        $response->setBody($body);
        $container = ResourceDataContainer::fromResponse($response);

        return new Resource($container, $this->api);
    }
}
