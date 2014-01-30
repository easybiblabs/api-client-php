<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\TokenRequest;
use EasyBib\Api\Client\Session\TokenResponse;
use EasyBib\Tests\Api\Client\TestCase;
use Guzzle\Http\Client;

class TokenRequestTest extends TestCase
{
    public function testSend()
    {
        $token = 'token_ABC123';
        $this->given->iAmReadyToRespondToATokenRequest($token, $this->mockResponses);

        $tokenRequest = new TokenRequest(
            $this->clientConfig,
            $this->serverConfig,
            $this->httpClient,
            $this->authorization
        );

        $tokenResponse = $tokenRequest->send();

        $this->shouldHaveMadeATokenRequest();
        $this->assertInstanceOf(TokenResponse::class, $tokenResponse);
        $this->assertEquals($token, $tokenResponse->getToken());
    }
}
