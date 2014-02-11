<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\Resource\Reference;
use EasyBib\Api\Client\ResourceDataContainer;
use Guzzle\Http\Message\Response;

class ResourceDataContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetData()
    {
        $container = $this->getResourceContainer('{"data":{"foo":"bar"}}');
        $this->assertEquals((object) ['foo' => 'bar'], $container->getData());
    }

    public function testGetLinks()
    {
        $container = $this->getResourceContainer(
            '{"links":[{"href":"http://api.example.org/foo/bar/","rel":"foo",
                "type":"application/vnd.com.easybib.data+json","title":"Some link"}]}'
        );

        $this->assertInternalType('array', $container->getReferences());
        $this->assertInstanceOf(Reference::class, $container->getReferences()[0]);

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
            $container->getReferences()
        );
    }

    public function testToArray()
    {
        $container = $this->getResourceContainer('{"data":{"foo":"bar"}}');
        $this->assertEquals(['data' => ['foo' => 'bar']], $container->toArray());
    }

    public function testIsList()
    {
        $hashData = '{"data":{"foo":"bar"}}';
        $listData = '{"data":[{"foo":"bar"}]}';

        $this->assertFalse($this->getResourceContainer($hashData)->isList());
        $this->assertTrue($this->getResourceContainer($listData)->isList());
    }

    /**
     * @param string $body A string of JSON
     * @return ResourceDataContainer
     */
    private function getResourceContainer($body)
    {
        $response = new Response(200);
        $response->setBody($body);

        return ResourceDataContainer::fromResponse($response);
    }
}
