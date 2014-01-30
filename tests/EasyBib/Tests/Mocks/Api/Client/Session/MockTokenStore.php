<?php

namespace EasyBib\Tests\Mocks\Api\Client\Session;

use EasyBib\Api\Client\Session\TokenStore\TokenStoreInterface;

class MockTokenStore implements \EasyBib\Api\Client\Session\TokenStore\TokenStoreInterface
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $expirationTime;

    /**
     * @param string $token
     */
    public function setToken($token)
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

    /**
     * @param int $time
     * @return void
     */
    public function setExpirationTime($time)
    {
        $this->expirationTime = $time;
    }
}
