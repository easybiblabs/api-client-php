<?php

namespace EasyBib\Api\Client\Session;

class TokenRequest
{
    public function __construct(ApiSession $session, AuthorizationResponse $authorizationResponse)
    {

    }

    /**
     * @return TokenResponse
     */
    public function send()
    {
    }
}
