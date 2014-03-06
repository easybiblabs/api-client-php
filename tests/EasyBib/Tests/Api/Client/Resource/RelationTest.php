<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\Resource\Relation;
use EasyBib\Tests\Mocks\Api\Client\LinkTransformer\MockLinkTransformer;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    public function dataProviderFull()
    {
        return [
            [
                (object) [
                    'href' => 'http://foo/bar/',
                    'rel' => 'some rel ',
                    'type' => 'text/html ',
                    'title' => 'James ',
                ],
            ],
        ];
    }

    public function dataProviderValid()
    {
        return [
            [
                '{"href":"http://foo/bar/","rel":"some rel ","type":"text/html "}',
                [
                    'href' => 'http://foo/bar/',
                    'rel' => 'some rel',
                    'type' => 'text/html',
                    'title' => null,
                ],
            ],
            [
                '{"href":"http://foo/bar/","rel":"some rel ","title":"James "}',
                [
                    'href' => 'http://foo/bar/',
                    'rel' => 'some rel',
                    'title' => 'James',
                    'type' => null,
                ],
            ],
        ];
    }

    public function dataProviderInvalid()
    {
        return [
            ['{"href":"http://foo/bar/","type":"text/html","title":"James"}'],
            ['{"rel":"some rel","type":"text/html","title":"James"}'],
        ];
    }

    /**
     * @param \stdClass $data
     * @dataProvider dataProviderFull
     */
    public function testGetHref(\stdClass $data)
    {
        $relation = new Relation($data);
        $this->assertEquals('http://foo/bar/', $relation->getHref());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderFull
     */
    public function testGetHrefWithLinkTransformer($data)
    {
        $callback = function ($input) {
            return $input . 'baz';
        };

        $linkTransformer = new MockLinkTransformer($callback);

        $relation = new Relation($data, $linkTransformer);
        $relation->setLinkTransformer($linkTransformer);

        $this->assertEquals('http://foo/bar/baz', $relation->getHref());
    }

    /**
     * @param \stdClass $data
     * @dataProvider dataProviderFull
     */
    public function testGetRel(\stdClass $data)
    {
        $relation = new Relation($data);
        $this->assertEquals('some rel', $relation->getRel());
    }

    /**
     * @param \stdClass $data
     * @dataProvider dataProviderFull
     */
    public function testGetType($data)
    {
        $relation = new Relation($data);
        $this->assertEquals('text/html', $relation->getType());
    }

    /**
     * @param \stdClass $data
     * @dataProvider dataProviderFull
     */
    public function testGetTitle($data)
    {
        $relation = new Relation($data);
        $this->assertEquals('James', $relation->getTitle());
    }

    /**
     * @param \stdClass $data
     * @param array $expectedOutput
     * @dataProvider dataProviderValid
     */
    public function testGetAttributes($data, array $expectedOutput)
    {
        $relation = new Relation(json_decode($data));
        $this->assertEquals($expectedOutput, $relation->getAttributes());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderValid
     */
    public function testValidData($data)
    {
        new Relation(json_decode($data));
    }

    /**
     * @expectedException \EasyBib\Api\Client\Resource\InvalidResourceLinkException
     * @param string $data
     * @dataProvider dataProviderInvalid
     */
    public function testInvalidData($data)
    {
        new Relation(json_decode($data));
    }
}
