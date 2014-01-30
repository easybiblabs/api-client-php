<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\ApiConfig;
use EasyBib\Api\Client\Session\ApiSession;
use EasyBib\Api\Client\Session\TokenResponse;
use EasyBib\Tests\Mocks\Api\Client\Session\ExceptionMockRedirector;
use EasyBib\Tests\Mocks\Api\Client\TokenStore\MockTokenStore;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApiSessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HistoryPlugin
     */
    private $history;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var MockTokenStore
     */
    private $tokenStore;

    /**
     * @var ApiSession
     */
    private $session;

    public function setUp()
    {
        $this->httpClient = new Client();

        $mockResponses = new MockPlugin([
            new Response(200, [], '{}'),
        ]);

        $this->history = new HistoryPlugin();

        $this->httpClient->addSubscriber($mockResponses);
        $this->httpClient->addSubscriber($this->history);
        $this->tokenStore = new MockTokenStore();
        $this->session = $this->getSession();
    }

    /**
     * @expectedException \EasyBib\Tests\Mocks\Api\Client\Session\MockRedirectException
     * @expectedExceptionMessage Redirecting to https://data.playground.easybib.example.com/authorize
     */
    public function testEnsureTokenWhenNotSet()
    {
        $this->session->ensureToken();
    }

    public function testEnsureTokenWhenSet()
    {
        $this->tokenStore->setToken('ABC123');
        $this->session->ensureToken();

        $lastRequest = $this->makeRequest();

        $this->assertEquals('Bearer ABC123', $lastRequest->getHeader('Authorization'));
    }

    public function testHandleIncomingToken()
    {
        $tokenRequest = new TokenResponse([
            'access_token' => 'ABC123',
        ]);
        
        $this->session->handleIncomingToken($tokenRequest);

        $lastRequest = $this->makeRequest();

        $this->assertEquals('ABC123', $this->tokenStore->getToken());
        $this->assertEquals('Bearer ABC123', $lastRequest->getHeader('Authorization'));
    }

    /**
     * @return ApiSession
     */
    private function getSession()
    {
        $apiRootUrl = 'https://data.playground.easybib.example.com';

        return new ApiSession(
            $apiRootUrl,
            $this->tokenStore,
            $this->httpClient,
            new ExceptionMockRedirector(),
            new ApiConfig(['client_id' => 'client_123'])
        );
    }

    /**
     * @return \Guzzle\Http\Message\RequestInterface
     */
    private function makeRequest()
    {
        $request = $this->httpClient->get('http://example.org');
        $request->send();

        return $this->history->getLastRequest();
    }
}
