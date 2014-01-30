<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\Scope;
use EasyBib\Api\Client\Session\ApiSession;
use EasyBib\Tests\Api\Client\TestCase;
use EasyBib\Tests\Mocks\Api\Client\Session\ExceptionMockRedirector;
use EasyBib\Tests\Mocks\Api\Client\Session\MockRedirectException;
use Guzzle\Http\Client;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApiSessionTest extends TestCase
{
    /**
     * @var string
     */
    private $redirectUrl = 'http://myapp.example.org/handle/oauth';

    /**
     * @var ApiSession
     */
    private $session;

    public function setUp()
    {
        parent::setUp();

        $this->session = $this->getSession();
    }

    public function testEnsureTokenWhenNotSet()
    {
        $redirectUrl = urlencode($this->config->getParams()['redirect_url']);

        $message = "Redirecting to $this->apiBaseUrl/oauth/authorize"
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

    public function testHandleAuthorizationResponse()
    {
        $token = 'token_ABC123';
        $this->given->iAmReadyToRespondToATokenRequest($token, $this->mockResponses);

        $this->session->handleAuthorizationResponse($this->authorization);

        $this->shouldHaveMadeATokenRequest();
        $this->shouldHaveATokenAssigned($token);
    }

    private function shouldHaveATokenAssigned($token)
    {
        $lastRequest = $this->makeRequest();

        $this->assertEquals($token, $this->tokenStore->getToken());
        $this->assertEquals('Bearer ' . $token, $lastRequest->getHeader('Authorization'));
    }

    /**
     * @return ApiSession
     */
    private function getSession()
    {
        $session = new ApiSession(
            $this->tokenStore,
            $this->httpClient,
            new ExceptionMockRedirector(),
            $this->config
        );

        $scope = new Scope(['USER_READ', 'DATA_READ_WRITE']);
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
