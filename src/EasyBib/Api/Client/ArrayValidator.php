<?php

namespace EasyBib\Api\Client;

class ArrayValidator
{
    /**
     * @var array
     */
    private $requiredKeys;

    public function __construct(array $requiredKeys)
    {
        $this->requiredKeys = $requiredKeys;
    }

    /**
     * @param array $params
     * @throws \InvalidArgumentException
     */
    public function validate(array $params)
    {
        foreach ($this->requiredKeys as $key) {
            if (!isset($params[$key])) {
                throw new \InvalidArgumentException('Missing key ' . $key);
            }
        }
    }
}
