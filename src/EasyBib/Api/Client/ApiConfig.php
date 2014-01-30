<?php

namespace EasyBib\Api\Client;

class ApiConfig
{
    /**
     * @var array
     */
    private $params;

    private static $requiredParams = [
        'client_id',
    ];

    private static $alwaysParams = [
        'response_type' => 'code',
    ];

    public function __construct(array $params)
    {
        self::validate($params);
        $this->params = self::$alwaysParams + $params;
    }

    private static function validate(array $params)
    {
        $validator = new ArrayValidator(self::$requiredParams);
        $validator->validate($params);
    }
}
