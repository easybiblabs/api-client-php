<?php

namespace EasyBib\Api\Client\Session;

use EasyBib\Api\Client\ApiConfig;
use Guzzle\Http\ClientInterface;

class TokenRequest
{
    const GRANT_TYPE = 'authorization_code';

    /**
     * @var \EasyBib\Api\Client\ApiConfig
     */
    private $config;

    /**
     * @var \Guzzle\Http\ClientInterface
     */

    private $httpClient;

    /**
     * @var AuthorizationResponse
     */
    private $authorizationResponse;

    /**
     * @param ApiConfig $config
     * @param ClientInterface $httpClient
     * @param AuthorizationResponse $authorization
     */
    public function __construct(
        ApiConfig $config,
        ClientInterface $httpClient,
        AuthorizationResponse $authorization
    ) {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->authorizationResponse = $authorization;
    }

    /**
     * @return TokenResponse
     */
    public function send()
    {
        $request = $this->httpClient->post('/oauth/token', [], $this->getParams());
        $request->send();
    }

    private function getParams()
    {
        return [
            'grant_type' => self::GRANT_TYPE,
            'code' => $this->authorizationResponse->getCode(),
            'redirect_uri' => $this->config->getParams()['redirect_url'],
            'client_id' => $this->config->getParams()['client_id'],
        ];
    }
}
