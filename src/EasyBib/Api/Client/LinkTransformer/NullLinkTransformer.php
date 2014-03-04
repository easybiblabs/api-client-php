<?php

namespace EasyBib\Api\Client\LinkTransformer;

class NullLinkTransformer implements LinkTransformerInterface
{
    /**
     * @param string $url
     * @return string
     */
    public function transform($url)
    {
        return $url;
    }
}
