<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\ResourceDataContainer;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return [
            [[
                'data' => [
                    [
                        'links' => [
                            0 => [
                                'title' => 'James',
                                'type' => 'text/html',
                                'href' => 'http://api.example.org/foo/',
                                'ref' => 'foo resource',
                            ],
                        ],
                    ],
                ],
            ]],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param array $payload
     */
    public function testOffsetExists(array $payload)
    {
        $resourceList = $this->getResourceList(json_encode($payload));
        $this->assertTrue(isset($resourceList[0]));
        $this->assertFalse(isset($resourceList[1]));
    }

    /**
     * @dataProvider dataProvider
     * @param array $payload
     */
    public function testOffsetGet(array $payload)
    {
        $resourceList = $this->getResourceList(json_encode($payload));
        $this->assertInstanceOf(Resource::class, $resourceList[0]);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage offsetSet() is not supported.
     */
    public function testOffsetSet()
    {
        $resourceList = $this->getResourceList();
        $resourceList->offsetSet(0, (object) []);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage offsetUnset() is not supported.
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

        $container = ResourceDataContainer::fromResponse($response);

        $apiTraverser = new ApiTraverser(new Client());
        $resourceList = new Collection($container, $apiTraverser);

        return $resourceList;
    }
}
