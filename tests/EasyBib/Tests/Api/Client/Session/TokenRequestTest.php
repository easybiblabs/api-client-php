<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\ApiConfig;
use EasyBib\Api\Client\Session\AuthorizationResponse;
use EasyBib\Api\Client\Session\TokenRequest;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;

class TokenRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $rootUrl = 'http://data.easybib.example.com';

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var ApiConfig
     */
    private $config;

    /**
     * @var HistoryPlugin
     */
    private $history;

    /**
     * @var AuthorizationResponse
     */
    private $authorizationResponse;

    public function setUp()
    {
        $this->httpClient = new Client($this->rootUrl);

        $mockResponses = new MockPlugin([
            new Response(200, [], '{}'),
        ]);

        $this->history = new HistoryPlugin();

        $this->httpClient->addSubscriber($mockResponses);
        $this->httpClient->addSubscriber($this->history);

        $this->config = new ApiConfig([
            'client_id' => 'client_123',
            'redirect_url' => 'http://myapp.example.com/',
        ]);

        $this->authorizationResponse = new AuthorizationResponse(['code' => 'ABC123']);
    }

    public function testSend()
    {
        $tokenRequest = new TokenRequest($this->config, $this->httpClient, $this->authorizationResponse);
        $tokenRequest->send();

        $lastRequest = $this->history->getLastRequest();

        $expectedParams = [
            'grant_type' => 'authorization_code',
            'code' => $this->authorizationResponse->getCode(),
            'redirect_uri' => $this->config->getParams()['redirect_url'],
            'client_id' => $this->config->getParams()['client_id'],
        ];

        $this->assertEquals('POST', $lastRequest->getMethod());
        $this->assertEquals($expectedParams, $lastRequest->getPostFields()->toArray());
        $this->assertEquals($this->rootUrl . '/oauth/token', $lastRequest->getUrl());
        // TODO assert return value
    }
}
