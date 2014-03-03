<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\Resource\Relation;

class RelationTest extends \PHPUnit_Framework_TestCase
{
    public function dataProviderFull()
    {
        return [
            ['{"href":"http://foo/bar/","rel":"some rel","type":"text/html","title":"James"}'],
        ];
    }

    public function dataProviderValid()
    {
        return [
            ['{"href":"http://foo/bar/","rel":"some rel","type":"text/html"}'],
            ['{"href":"http://foo/bar/","rel":"some rel","title":"James"}'],
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
     * @param string $data
     * @dataProvider dataProviderFull
     */
    public function testGetHref($data)
    {
        $reference = new Relation(json_decode($data));
        $this->assertEquals('http://foo/bar/', $reference->getHref());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderFull
     */
    public function testGetRel($data)
    {
        $reference = new Relation(json_decode($data));
        $this->assertEquals('some rel', $reference->getRel());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderFull
     */
    public function testGetType($data)
    {
        $reference = new Relation(json_decode($data));
        $this->assertEquals('text/html', $reference->getType());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderFull
     */
    public function testGetTitle($data)
    {
        $reference = new Relation(json_decode($data));
        $this->assertEquals('James', $reference->getTitle());
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
