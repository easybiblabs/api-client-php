<?php

namespace EasyBib\Tests\Api\Client\LinkTransformer;

use EasyBib\Api\Client\LinkTransformer\NullLinkTransformer;

class NullLinkTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransform()
    {
        $linkTransformer = new NullLinkTransformer();
        $text = 'jimbob';

        $this->assertEquals($text, $linkTransformer->transform($text));
    }
}
