<?php

namespace EasyBib\Api\Client\Session;

use EasyBib\Api\Client\TokenStore\TokenStoreInterface;
use Guzzle\Http\ClientInterface;

class ApiSession
{
    private $baseUrl;
    private $tokenStore;
    private $httpClient;

    public function __construct($baseUrl, TokenStoreInterface $tokenStore, ClientInterface $httpClient)
    {
        $this->baseUrl = $baseUrl;
        $this->tokenStore = $tokenStore;
        $this->httpClient = $httpClient;
    }

    public function handleIncomingToken(IncomingTokenInterface $tokenRequest)
    {
        $this->tokenStore->setToken($tokenRequest);
    }

    public function ensureToken(RedirectorInterface $redirector)
    {
        if (!$this->tokenStore->getToken()) {
            $redirector->redirect($this->getAuthorizeUrl());
        }

        // set token listener thingy
    }

    private function getAuthorizeUrl()
    {
        return $this->baseUrl . '/authorize';
    }
}
