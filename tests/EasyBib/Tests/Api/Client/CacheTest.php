<?php

namespace EasyBib\Tests\Api\Client;

use Doctrine\Common\Cache\FilesystemCache;
use EasyBib\Api\Client\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testNamespacedCache()
    {
        $cacheProvider = new FilesystemCache(sys_get_temp_dir());
        $cacheProvider->setNamespace('foo');
        new Cache($cacheProvider);
    }

    public function testUnsafeCache()
    {
        $cacheProvider = new FilesystemCache(sys_get_temp_dir());
        $this->setExpectedException(\RuntimeException::class);
        new Cache($cacheProvider);
    }
}
