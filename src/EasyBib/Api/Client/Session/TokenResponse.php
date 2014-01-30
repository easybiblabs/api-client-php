<?php

namespace EasyBib\Api\Client\Session;

class TokenResponse
{
    private $token;

    private static $requiredParams = [
        'access_token',
    ];

    public function __construct(array $params)
    {
        self::validate($params);
        $this->token = $params['access_token'];
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param array $params
     * @throws \InvalidArgumentException
     */
    private static function validate(array $params)
    {
        foreach (self::$requiredParams as $key) {
            if (!isset($params[$key])) {
                throw new \InvalidArgumentException('Missing key ' . $key);
            }
        }
    }
}
