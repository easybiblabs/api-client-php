<?php

namespace EasyBib\Tests\Mocks\Api\Client\LinkTransformer;

use EasyBib\Api\Client\LinkTransformer\LinkTransformerInterface;

class MockLinkTransformer implements LinkTransformerInterface
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param string $url
     * @return string
     */
    public function transform($url)
    {
        return call_user_func($this->callback, $url);
    }
}
