<?php

namespace EasyBib\Api\Client\Session;

use EasyBib\Api\Client\ArrayValidator;

class AuthorizationResponse
{
    /**
     * @var string
     */
    private $code;

    private static $validParams = ['code'];

    public function __construct(array $params)
    {
        self::validate($params);
        $this->code = $params['code'];
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    private static function validate(array $params)
    {
        $validator = new ArrayValidator(self::$validParams);
        $validator->validate($params);
    }
}
