<?php

namespace EasyBib\Tests\Mocks\Client\TokenStore;

use EasyBib\Api\Client\Session\IncomingTokenInterface;
use EasyBib\Api\Client\TokenStore\TokenStoreInterface;

class MockTokenStore implements TokenStoreInterface
{
    private $token;

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
        // TODO: Implement setExpirationTime() method.
    }
}
