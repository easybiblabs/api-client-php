<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\ApiConfig;
use EasyBib\Api\Client\Session\Scope;
use EasyBib\Api\Client\Session\ApiSession;
use EasyBib\Api\Client\Session\TokenResponse;
use EasyBib\Tests\Mocks\Api\Client\Session\ExceptionMockRedirector;
use EasyBib\Tests\Mocks\Api\Client\Session\MockRedirectException;
use EasyBib\Tests\Mocks\Api\Client\Session\MockTokenStore;
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
     * @var string
     */
    private $redirectUrl = 'http://myapp.example.org/handle/oauth';

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

    public function testEnsureTokenWhenNotSet()
    {
        $redirectUrl = urlencode($this->redirectUrl);

        $message = 'Redirecting to https://data.playground.easybib.example.com/oauth/authorize'
            . "?response_type=code&client_id=client_123&redirect_url=$redirectUrl"
            . "&scope=USER_READ+DATA_READ_WRITE";

        $this->setExpectedException(MockRedirectException::class, $message);

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
        $scope = new Scope(['USER_READ', 'DATA_READ_WRITE']);

        $session = new ApiSession(
            $apiRootUrl,
            $this->tokenStore,
            $this->httpClient,
            new ExceptionMockRedirector(),
            new ApiConfig([
                'client_id' => 'client_123',
                'redirect_url' => $this->redirectUrl,
            ])
        );

        $session->setScope($scope);

        return $session;
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
