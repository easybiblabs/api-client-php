<?php

namespace EasyBib\Api\Client\Session\TokenResponse;

interface TokenResponseInterface
{
    /**
     * @return string
     */
    public function getToken();
}
