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
        $this->pushTokenToHttpClient($tokenRequest->getToken());
    }

    public function ensureToken(RedirectorInterface $redirector)
    {
        // TODO handle expired token
        $token = $this->tokenStore->getToken();

        if (!$token) {
            $redirector->redirect($this->getAuthorizeUrl());
        }

        $this->pushTokenToHttpClient($token);
    }

    private function getAuthorizeUrl()
    {
        return $this->baseUrl . '/authorize';
    }

    /**
     * @param $token
     */
    private function pushTokenToHttpClient($token)
    {
        $this->httpClient->addSubscriber(new BearerAuth($token));
    }
}
