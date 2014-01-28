<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\Resource\Reference;

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function dataProviderValid()
    {
        return [
            ['{"href":"http://foo/bar/","ref":"some ref","type":"text/html","title":"James"}'],
        ];
    }

    public function dataProviderInvalid()
    {
        return [
            ['{"href":"http://foo/bar/","ref":"some ref","type":"text/html"}'],
            ['{"href":"http://foo/bar/","ref":"some ref","title":"James"}'],
            ['{"href":"http://foo/bar/","type":"text/html","title":"James"}'],
            ['{"ref":"some ref","type":"text/html","title":"James"}'],
        ];
    }

    /**
     * @param string $data
     * @dataProvider dataProviderValid
     */
    public function testGetHref($data)
    {
        $reference = new Reference(json_decode($data));
        $this->assertEquals('http://foo/bar/', $reference->getHref());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderValid
     */
    public function testGetRef($data)
    {
        $reference = new Reference(json_decode($data));
        $this->assertEquals('some ref', $reference->getRef());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderValid
     */
    public function testGetType($data)
    {
        $reference = new Reference(json_decode($data));
        $this->assertEquals('text/html', $reference->getType());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderValid
     */
    public function testGetTitle($data)
    {
        $reference = new Reference(json_decode($data));
        $this->assertEquals('James', $reference->getTitle());
    }

    /**
     * @expectedException \EasyBib\Api\Client\Resource\InvalidResourceLinkException
     * @param string $data
     * @dataProvider dataProviderInvalid
     */
    public function testInvalidData($data)
    {
        new Reference(json_decode($data));
    }
}
