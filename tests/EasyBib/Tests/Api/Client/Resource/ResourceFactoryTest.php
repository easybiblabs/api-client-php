<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\ResourceErrorException;
use EasyBib\Api\Client\Resource\ResourceFactory;
use Guzzle\Http\Client;

class ResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceFactory
     */
    private $factory;

    public function setUp()
    {
        parent::setUp();
        $this->factory = new ResourceFactory(new ApiTraverser(new Client()));
    }

    public function testFromData()
    {
        $listData = json_decode(json_encode([
            'data' => [
                [
                    'foo' => 'bar',
                ],
            ],
            'links' => [],
        ]));

        $hashData = json_decode(json_encode([
            'data' => [
                'foo' => 'bar',
            ],
            'links' => [],
        ]));

        // used with responses to DELETE requests
        $noData = (object) ['links' => []];

        $this->assertInstanceOf(Collection::class, $this->factory->createFromData($listData));
        $this->assertNotInstanceOf(Collection::class, $this->factory->createFromData($hashData));
        $this->assertNotInstanceOf(Collection::class, $this->factory->createFromData($noData));
    }

    public function testFromDataWithError()
    {
        $message = 'somn done messed up';

        $data = (object) [
            'status' => 'error',
            'message' => $message,
        ];

        $this->setExpectedException(
            ResourceErrorException::class,
            $message
        );

        $this->factory->createFromData($data);
    }
}
