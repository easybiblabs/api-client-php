<?php

namespace EasyBib\Api\Client\Session;


class AuthorizationResponse
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private static $validParams = ['code'];

    /**
     * @param array $params
     */
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

    /**
     * @param array $params
     */
    private static function validate(array $params)
    {
        $validator = new ArrayValidator(self::$validParams, self::$validParams);
        $validator->validate($params);
    }
}
