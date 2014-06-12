<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use EasyBib\Api\Client\Resource\ResourceFactory;
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
                        'data' => ['foo' => 'bar'],
                        'links' => [
                            (object) [
                                'title' => 'James',
                                'type' => 'text/html',
                                'href' => 'http://api.example.org/foo/',
                                'rel' => 'foo resource',
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

    public function testMapWithEmptyData()
    {
        $collection = $this->getCollection(['data' => []]);
        $this->assertEquals([], $collection->map(function ($item) {
            throw new \Exception('This should never get called');
        }));
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
    }

    public function invalidTotalRowsProvider()
    {
        return [['yes'], [null], [true], [false]];
    }

    /**
     * @dataProvider invalidTotalRowsProvider
     * @param mixed $invalidTotalRows
     */
    public function testSetTotalRowsWhereInvalid($invalidTotalRows)
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $collection = $this->getCollection($this->dataProvider()[0][0]);
        $collection->setTotalRows($invalidTotalRows);
    }

    public function totalRowsProvider()
    {
        return [['123'], [123], ['0'], [0]];
    }

    /**
     * @dataProvider totalRowsProvider
     * @param mixed $totalRows
     */
    public function testSetTotalRows($totalRows)
    {
        $collection = $this->getCollection($this->dataProvider()[0][0]);
        $collection->setTotalRows($totalRows);
        $this->assertSame($totalRows, $collection->getTotalRows());
    }

    public function testTotalRowsCreatedFromResponse()
    {
        $collection = $this->createFromResponseWithHeaders(['X-EasyBib-TotalRows' => 42]);
        $this->assertSame(42, $collection->getTotalRows());

        $collection = $this->createFromResponseWithHeaders([]);
        $this->assertNull($collection->getTotalRows());
    }

    private function createFromResponseWithHeaders($headers)
    {
        $data = $this->dataProvider()[0][0];
        $response = new Response(200);
        $response->setBody(json_encode($data));
        $response->setHeaders($headers);

        $resourceFactory = new ResourceFactory(new ApiTraverser(new Client()));
        return $resourceFactory->createFromResponse($response);
    }

    /**
     * @param array $rawData
     * @return Collection
     */
    private function getCollection(array $rawData = [])
    {
        $data = json_decode(json_encode($rawData));
        $resourceFactory = new ResourceFactory(new ApiTraverser(new Client()));

        return $resourceFactory->createFromData($data);
    }
}
