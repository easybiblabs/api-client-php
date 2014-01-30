<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\TokenRequest;
use EasyBib\Tests\Api\Client\TestCase;
use Guzzle\Http\Client;

class TokenRequestTest extends TestCase
{
    public function testSend()
    {
        $tokenRequest = new TokenRequest($this->config, $this->httpClient, $this->authorization);
        $tokenRequest->send();

        $this->shouldHaveMadeATokenRequest();
    }
}
