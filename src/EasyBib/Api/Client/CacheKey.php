<?php

namespace EasyBib\Api\Client;

class CacheKey
{
    /**
     * @var string
     */
    private $string;

    /**
     * @param \Serializable[] $arguments
     */
    public function __construct(array $arguments)
    {
        $this->string = md5(serialize($arguments));
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->string;
    }
}
