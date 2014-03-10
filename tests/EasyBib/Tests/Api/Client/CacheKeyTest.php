<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\CacheKey;

class CacheKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $arguments = ['jim', ['foo' => 'bar']];
        $cacheKey = new CacheKey($arguments);
        $this->assertEquals(md5(serialize($arguments)), $cacheKey->toString());
    }
}
