<?php

namespace EasyBib\Api\Client\Session;

use EasyBib\Api\Client\TokenStore\TokenStoreInterface;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
use Guzzle\Http\ClientInterface;

class ApiSession
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var \EasyBib\Api\Client\TokenStore\TokenStoreInterface
     */
    private $tokenStore;

    /**
     * @var \Guzzle\Http\ClientInterface
     */
    private $httpClient;

    /**
     * @var RedirectorInterface
     */
    private $redirector;

    /**
     * @param string $baseUrl
     * @param TokenStoreInterface $tokenStore
     * @param ClientInterface $httpClient
     * @param RedirectorInterface $redirector
     */
    public function __construct(
        $baseUrl,
        TokenStoreInterface $tokenStore,
        ClientInterface $httpClient,
        RedirectorInterface $redirector
    ) {
        $this->baseUrl = $baseUrl;
        $this->tokenStore = $tokenStore;
        $this->httpClient = $httpClient;
        $this->redirector = $redirector;
    }

    public function authorize()
    {
        $this->redirector->redirect($this->getAuthorizeUrl());
    }

    /**
     * @param AuthorizationResponse $authorizationResponse
     */
    public function handleAuthorizationResponse(AuthorizationResponse $authorizationResponse)
    {
        $tokenRequest = new TokenRequest($this, $authorizationResponse);
        $this->handleIncomingToken($tokenRequest->send());
    }

    /**
     * @todo this will become private
     * @param TokenResponse $tokenResponse
     */
    public function handleIncomingToken(TokenResponse $tokenResponse)
    {
        $token = $tokenResponse->getToken();
        $this->tokenStore->setToken($token);
        $this->pushTokenToHttpClient($token);
    }

    public function ensureToken()
    {
        // TODO handle expired token
        $token = $this->tokenStore->getToken();

        if (!$token) {
            $this->authorize();
        }

        $this->pushTokenToHttpClient($token);
    }

    /**
     * @return string
     */
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
