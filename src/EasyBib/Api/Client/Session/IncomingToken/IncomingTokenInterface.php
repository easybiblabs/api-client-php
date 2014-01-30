<?php

namespace EasyBib\Api\Client\Session\IncomingToken;

interface IncomingTokenInterface
{
    /**
     * @return string
     */
    public function getToken();
}
