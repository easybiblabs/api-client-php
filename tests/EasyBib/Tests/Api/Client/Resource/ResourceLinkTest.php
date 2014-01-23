<?php

namespace EasyBib\Tests\Api\Client\Resource;

use EasyBib\Api\Client\Resource\ResourceLink;

class ResourceLinkTest extends \PHPUnit_Framework_TestCase
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
        $link = new ResourceLink(json_decode($data));
        $this->assertEquals('http://foo/bar/', $link->getHref());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderValid
     */
    public function testGetRef($data)
    {
        $link = new ResourceLink(json_decode($data));
        $this->assertEquals('some ref', $link->getRef());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderValid
     */
    public function testGetType($data)
    {
        $link = new ResourceLink(json_decode($data));
        $this->assertEquals('text/html', $link->getType());
    }

    /**
     * @param string $data
     * @dataProvider dataProviderValid
     */
    public function testGetTitle($data)
    {
        $link = new ResourceLink(json_decode($data));
        $this->assertEquals('James', $link->getTitle());
    }

    /**
     * @expectedException EasyBib\Api\Client\Resource\InvalidResourceLinkException
     * @param string $data
     * @dataProvider dataProviderInvalid
     */
    public function testInvalidData($data)
    {
        new ResourceLink(json_decode($data));
    }
}
