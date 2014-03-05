<?php

namespace EasyBib\Api\Client\LinkTransformer;

interface LinkTransformerInterface
{
    /**
     * @param string $url
     * @return string
     */
    public function transform($url);
}
