<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\ApiSession;
use EasyBib\Tests\Mocks\Api\Client\Session\ExceptionMockRedirector;
use EasyBib\Tests\Mocks\Api\Client\Session\MockIncomingToken;
use EasyBib\Tests\Mocks\Api\Client\TokenStore\MockTokenStore;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;

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
    }

    /**
     * @expectedException \EasyBib\Tests\Mocks\Api\Client\Session\MockRedirectException
     * @expectedExceptionMessage Redirecting to https://data.playground.easybib.example.com/authorize
     */
    public function testEnsureTokenWhenNotSet()
    {
        $session = $this->getSession();
        $redirector = new ExceptionMockRedirector();
        $session->ensureToken($redirector);
    }

    public function testEnsureTokenWhenSet()
    {
        $this->tokenStore->forceToken('ABC123');
        $session = $this->getSession();
        $redirector = new ExceptionMockRedirector();
        $session->ensureToken($redirector);

        $lastRequest = $this->makeRequest();
        $this->assertEquals('Bearer ABC123', $lastRequest->getHeader('Authorization'));
    }

    public function testHandleIncomingToken()
    {
        $session = $this->getSession();
        $tokenRequest = new MockIncomingToken('ABC123');
        $session->handleIncomingToken($tokenRequest);

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
        $session = new ApiSession($apiRootUrl, $this->tokenStore, $this->httpClient);
        return $session;
    }

    /**
     * @return \Guzzle\Http\Message\RequestInterface
     */
    private function makeRequest()
    {
        $request = $this->httpClient->get('http://example.org');
        $request->send();
        $lastRequest = $this->history->getLastRequest();
        return $lastRequest;
    }
}
