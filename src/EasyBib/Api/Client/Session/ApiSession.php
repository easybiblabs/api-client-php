<?php

namespace EasyBib\Api\Client\Session;

use EasyBib\Api\Client\ApiConfig;
use EasyBib\Api\Client\Scope;
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
     * @var ApiConfig
     */
    private $config;

    /**
     * @var Scope
     */
    private $scope;

    /**
     * @param string $baseUrl
     * @param TokenStoreInterface $tokenStore
     * @param ClientInterface $httpClient
     * @param RedirectorInterface $redirector
     * @param ApiConfig $config
     */
    public function __construct(
        $baseUrl,
        TokenStoreInterface $tokenStore,
        ClientInterface $httpClient,
        RedirectorInterface $redirector,
        ApiConfig $config
    ) {
        $this->baseUrl = $baseUrl;
        $this->tokenStore = $tokenStore;
        $this->httpClient = $httpClient;
        $this->redirector = $redirector;
        $this->config = $config;
    }

    public function setScope(Scope $scope)
    {
        $this->scope = $scope;
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
        $tokenResponse = $tokenRequest->send();
        $this->handleIncomingToken($tokenResponse);
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
        $params = ['response_type' => 'code'] + $this->config->getParams();

        if ($this->scope) {
            $params += $this->scope->getQuerystringParams();
        }

        return $this->baseUrl . '/authorize?' . http_build_query($params);
    }

    /**
     * @param $token
     */
    private function pushTokenToHttpClient($token)
    {
        $this->httpClient->addSubscriber(new BearerAuth($token));
    }
}
