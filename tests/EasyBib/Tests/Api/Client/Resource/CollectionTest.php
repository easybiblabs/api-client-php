<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use Guzzle\Http\Client;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return [
            [[
                'data' => [
                    [
                        'data' => ['foo' => 'bar'],
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
                'links' => [],
            ]],
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param array $data
     */
    public function testOffsetExists(array $data)
    {
        $collection = $this->getCollection($data);
        $this->assertTrue(isset($collection[0]));
        $this->assertFalse(isset($collection[1]));
    }

    /**
     * @dataProvider dataProvider
     * @param array $data
     */
    public function testOffsetGet(array $data)
    {
        $collection = $this->getCollection($data);
        $this->assertInstanceOf(Resource::class, $collection[0]);
    }

    /**
     * @dataProvider dataProvider
     * @param array $data
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage offsetSet() is not supported.
     */
    public function testOffsetSet(array $data)
    {
        $collection = $this->getCollection($data);
        $collection->offsetSet(0, (object) []);
    }

    /**
     * @dataProvider dataProvider
     * @param array $data
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage offsetUnset() is not supported.
     */
    public function testOffsetUnset(array $data)
    {
        $collection = $this->getCollection($data);
        $collection->offsetUnset(0);
    }

    /**
     * @dataProvider dataProvider
     * @param array $data
     */
    public function testMap(array $data)
    {
        $collection = $this->getCollection($data);

        $callback = function ($resource) {
            return $resource->getData()->foo;
        };

        $this->assertEquals(['bar'], $collection->map($callback));
    }

    public function testHavingResourceError()
    {
        $message = 'Somn done gone wrong';

        $data = [
            'data' => [
                [
                    'status' => 'error',
                    'message' => $message,
                ],
                [
                    'data' => ['foo' => 'bar'],
                    'links' => [],
                ],
            ],
            'links' => [],
        ];

        $collection = $this->getCollection($data);

        $collection->map(function () {
            // ensure this does not throw an exception
        });

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertTrue($collection->hasResourceError());
        $this->assertCount(1, $collection);
    }

    /**
     * @dataProvider dataProvider
     * @param array $data
     */
    public function testNotHavingResourceError(array $data)
    {
        $collection = $this->getCollection($data);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertFalse($collection->hasResourceError());
    }

    /**
     * @param array $data
     * @return Collection
     */
    private function getCollection(array $data = [])
    {
        $apiTraverser = new ApiTraverser(new Client());
        $data = json_decode(json_encode($data));

        return Resource::factory($data, $apiTraverser);
    }
}
