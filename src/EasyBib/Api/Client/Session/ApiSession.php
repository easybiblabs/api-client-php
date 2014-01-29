<?php

namespace EasyBib\Api\Client\Session;

use EasyBib\Api\Client\TokenStore\TokenStoreInterface;

class ApiSession
{
    private $baseUrl;
    private $tokenStore;

    public function __construct($baseUrl, TokenStoreInterface $tokenStore)
    {
        $this->baseUrl = $baseUrl;
        $this->tokenStore = $tokenStore;
    }

    public function getToken()
    {
        $this->ensureToken();
        return $this->tokenStore->getToken();
    }

    public function handleIncomingToken(IncomingTokenInterface $tokenRequest)
    {
        $this->tokenStore->setToken($tokenRequest);
    }

    private function ensureToken(RedirectorInterface $redirector)
    {
        if (!$this->tokenStore->getToken()) {
            call_user_func($redirector->getCallback($this->getAuthorizeUrl()));
        }
    }

    private function getAuthorizeUrl()
    {
        return $this->baseUrl . '/authorize';
    }
}
