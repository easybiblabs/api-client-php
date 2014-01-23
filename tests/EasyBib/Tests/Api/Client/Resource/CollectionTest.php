<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiSession;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\LinkSourceInterface;
use EasyBib\Api\Client\Resource\ResourceLink;
use EasyBib\Api\Client\ResponseDataContainer;
use Guzzle\Http\Message\Response;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testOffsetExists()
    {
        $body = '{"data":[{"links":[{"title":"James","type":"text/html",
            "href":"http://api.example.org/foo/","ref":"foo resource"}]}]}';
        $resourceList = $this->getResourceList($body);
        $this->assertTrue(isset($resourceList[0]));
        $this->assertFalse(isset($resourceList[1]));
    }

    public function testOffsetGet()
    {
        $body = '{"data":[{"links":[{"title":"James","type":"text/html",
            "href":"http://api.example.org/foo/","ref":"foo resource"}]}]}';
        $resourceList = $this->getResourceList($body);
        $this->assertInstanceOf(LinkSourceInterface::class, $resourceList[0]);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage offsetSet() is degenerate
     */
    public function testOffsetSet()
    {
        $resourceList = $this->getResourceList();
        $resourceList->offsetSet(0, (object) []);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage offsetUnset() is degenerate
     */
    public function testOffsetUnset()
    {
        $resourceList = $this->getResourceList();
        $resourceList->offsetUnset(0);
    }

    /**
     * @param string $body
     * @return Collection
     */
    private function getResourceList($body = '')
    {
        $response = new Response(200);
        $response->setBody($body);

        $container = ResponseDataContainer::fromResponse($response);
        $apiSession = new ApiSession();
        $resourceList = new Collection($container, $apiSession);

        return $resourceList;
    }
}
