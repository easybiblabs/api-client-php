<?php

namespace EasyBib\Api\Client;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;

class Cache
{
    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @param CacheProvider $cacheProvider
     */
    public function __construct(CacheProvider $cacheProvider)
    {
        self::assertCacheProviderIsSafe($cacheProvider);
        $this->cacheProvider = $cacheProvider;
    }

    /**
     * @param callable $callback
     * @param CacheKey $cacheKey
     * @return mixed
     */
    public function cacheAndReturn(callable $callback, CacheKey $cacheKey)
    {
        $cacheKeyString = $cacheKey->toString();

        if ($this->cacheProvider->contains($cacheKeyString)) {
            return $this->cacheProvider->fetch($cacheKeyString);
        }

        $value = $callback();
        $this->cacheProvider->save($cacheKeyString, $value);

        return $value;
    }

    public function clear()
    {
        $this->cacheProvider->deleteAll();
    }

    /**
     * @param CacheProvider $cacheProvider
     * @throws \RuntimeException
     */
    private static function assertCacheProviderIsSafe(CacheProvider $cacheProvider)
    {
        if ($cacheProvider instanceof ArrayCache) {
            return;
        }

        if (!$cacheProvider->getNamespace()) {
            $msg = 'Cache must either have a namespace or be specific to this ApiTraverser\'s memory';
            throw new \RuntimeException($msg);
        }
    }
}
