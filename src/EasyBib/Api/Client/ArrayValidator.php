<?php

namespace EasyBib\Api\Client;

class ArrayValidator
{
    /**
     * @var array
     */
    private $requiredKeys;

    /**
     * @var array
     */
    private $permittedKeys;

    /**
     * @param array $requiredKeys
     * @param array $permittedKeys
     */
    public function __construct(array $requiredKeys, array $permittedKeys = null)
    {
        $this->requiredKeys = $requiredKeys;
        $this->permittedKeys = $permittedKeys;
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

        if (!$this->permittedKeys) {
            return;
        }

        foreach (array_keys($params) as $key) {
            if (!in_array($key, $this->permittedKeys)) {
                throw new \InvalidArgumentException('Unexpected key ' . $key);
            }
        }
    }
}
