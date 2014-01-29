<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\ApiSession;
use EasyBib\Tests\Mocks\Api\Client\Session\ExceptionMockRedirector;
use EasyBib\Tests\Mocks\Api\Client\TokenStore\MockTokenStore;
use Guzzle\Http\Client;

class ApiSessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \EasyBib\Tests\Mocks\Api\Client\Session\MockRedirectException
     * @expectedExceptionMessage Redirecting to https://data.playground.easybib.example.com/authorize
     */
    public function testEnsureToken()
    {
        $apiRootUrl = 'https://data.playground.easybib.example.com';
        $tokenStore = new MockTokenStore();
        $guzzleClient = new Client();
        $session = new ApiSession($apiRootUrl, $tokenStore, $guzzleClient);
        $redirector = new ExceptionMockRedirector();
        $session->ensureToken($redirector);
    }
}
