<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Reference;
use EasyBib\Api\Client\ResponseDataContainer;
use EasyBib\Api\Client\Resource\Resource;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
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
        $resource = $this->getResource();

        $goodLinkedResource = $resource->get('foo ref');
        $nullLinkedResource = $resource->get('no such ref');

        $this->assertInstanceOf(Resource::class, $goodLinkedResource);
        $this->assertEquals('bar', $goodLinkedResource->foo);
        $this->assertNull($nullLinkedResource);
    }

    public function testFindLink()
    {
        $resource = $this->getResource();

        $goodLink = $resource->findReference('foo ref');
        $nullLink = $resource->findReference('no such ref');

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
            $body = '{"links":[{"href":"http://foo/","ref":"foo ref","type":"text","title":"The Foo"}]}';
        }

        $response = new Response(200);
        $response->setBody($body);
        $container = ResponseDataContainer::fromResponse($response);

        return new Resource($container, $this->getApiTraverser());
    }

    private function getResponse()
    {
        $response = new Response(200);
        $response->setBody('{"data":{"foo":"bar"}}');

        return $response;
    }

    private function getRequest($fakeHttpClient)
    {
        $request = new Request('GET', 'http://jim/');
        $request->setClient($fakeHttpClient);

        return $request;
    }

    private function getHttpClient()
    {
        $previousResponse = $this->getResponse();
        $fakeHttpClient = $this->getMock(Client::class);
        $request = $this->getRequest($fakeHttpClient);

        $this->registerStubBehaviorsOnHttpClient(
            $fakeHttpClient,
            $request,
            $previousResponse
        );

        return $fakeHttpClient;
    }

    private function registerStubBehaviorsOnHttpClient(
        $fakeHttpClient,
        $request,
        $response
    ) {
        $fakeHttpClient->expects($this->any())
            ->method('get')
            ->will($this->returnValue($request));

        $fakeHttpClient->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));
    }

    /**
     * @return ApiTraverser
     */
    private function getApiTraverser()
    {
        return new ApiTraverser($this->getHttpClient());
    }
}
