<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\Resource\Reference;
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

        $this->assertInternalType('array', $container->getReferences());
        $this->assertInstanceOf(Reference::class, $container->getReferences()[0]);

        $this->assertEquals(
            [
                new Reference(
                    (object) [
                        'href' => 'http://api.example.org/foo/bar/',
                        'ref' => 'foo',
                        'type' => 'application/vnd.com.easybib.data+json',
                        'title' => 'Some link',
                    ]
                )
            ],
            $container->getReferences()
        );
    }

    public function testIsList()
    {
        $hashData = '{"data":{"foo":"bar"}}';
        $listData = '{"data":[{"foo":"bar"}]}';

        $this->assertFalse($this->getResponseContainer($hashData)->isList());
        $this->assertTrue($this->getResponseContainer($listData)->isList());
    }

    private function getResponseContainer($body)
    {
        $response = new Response(200);
        $response->setBody($body);

        return ResponseDataContainer::fromResponse($response);
    }
}
