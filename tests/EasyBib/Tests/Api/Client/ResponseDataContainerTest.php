<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\Resource\ResourceLink;
use EasyBib\Api\Client\ResponseDataContainer;
use Guzzle\Http\Message\Response;

class ResponseDataContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetData()
    {
        $container = $this->getResponseContainer('{"data":{"foo":"bar"}}');
        $this->assertEquals((object) ['foo' => 'bar'], $container->getData());
    }

    public function testGetLinks()
    {
        $container = $this->getResponseContainer(
            '{"links":[{"href":"http://api.example.org/foo/bar/","ref":"foo",
                "type":"application/vnd.com.easybib.data+json","title":"Some link"}]}'
        );

        $this->assertInternalType('array', $container->getLinks());
        $this->assertInstanceOf(ResourceLink::class, $container->getLinks()[0]);

        $this->assertEquals(
            [
                new ResourceLink(
                    (object) [
                        'href' => 'http://api.example.org/foo/bar/',
                        'ref' => 'foo',
                        'type' => 'application/vnd.com.easybib.data+json',
                        'title' => 'Some link',
                    ]
                )
            ],
            $container->getLinks()
        );
    }

    private function getResponseContainer($body)
    {
        $response = new Response(200);
        $response->setBody($body);

        return ResponseDataContainer::fromResponse($response);
    }
}
