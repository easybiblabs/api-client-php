<?php

namespace EasyBib\Api\Client\Session;

interface IncomingTokenInterface
{
    /**
     * @return string
     */
    public function getToken();
}
