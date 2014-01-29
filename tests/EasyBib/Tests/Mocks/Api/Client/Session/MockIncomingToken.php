<?php

namespace EasyBib\Tests\Mocks\Api\Client\Session;

use EasyBib\Api\Client\Session\IncomingTokenInterface;

class MockIncomingToken implements IncomingTokenInterface
{
    /**
     * @var string
     */
    private $token;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
