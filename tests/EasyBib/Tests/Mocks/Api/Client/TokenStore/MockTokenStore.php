<?php

namespace EasyBib\Tests\Mocks\Api\Client\TokenStore;

use EasyBib\Api\Client\Session\IncomingTokenInterface;
use EasyBib\Api\Client\TokenStore\TokenStoreInterface;

class MockTokenStore implements TokenStoreInterface
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
     * @param \EasyBib\Api\Client\Session\IncomingTokenInterface $tokenInterface
     * @return void
     */
    public function setToken(IncomingTokenInterface $tokenInterface)
    {
        $this->token = $tokenInterface->getToken();
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

    /**
     * For testing purposes
     *
     * @param $token
     */
    public function forceToken($token)
    {
        $this->token = $token;
    }
}
