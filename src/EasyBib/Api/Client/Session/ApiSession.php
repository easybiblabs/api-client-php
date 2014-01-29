<?php

namespace EasyBib\Api\Client\Session;

use EasyBib\Api\Client\TokenStore\TokenStoreInterface;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
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
        $authentication = new BearerAuth($tokenRequest->getToken());
        $this->httpClient->addSubscriber($authentication);
    }

    public function ensureToken(RedirectorInterface $redirector)
    {
        // TODO handle expired token
        if (!$this->tokenStore->getToken()) {
            $redirector->redirect($this->getAuthorizeUrl());
        }
    }

    private function getAuthorizeUrl()
    {
        return $this->baseUrl . '/authorize';
    }
}
