<?php

namespace EasyBib\Api\Client\TokenStore;

use EasyBib\Api\Client\Session\IncomingTokenInterface;

interface TokenStoreInterface
{
    /**
     * @param \EasyBib\Api\Client\Session\IncomingTokenInterface $incomingToken
     * @return void
     */
    public function setToken(IncomingTokenInterface $incomingToken);

    /**
     * @return string
     */
    public function getToken();

    /**
     * @param int $time
     * @return void
     */
    public function setExpirationTime($time);
}
