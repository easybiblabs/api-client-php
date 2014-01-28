<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiTraverser;
use fkooman\OAuth\Client\AccessToken;
use fkooman\OAuth\Client\Api;
use fkooman\OAuth\Client\ClientConfig;
use fkooman\OAuth\Client\Context;
use fkooman\OAuth\Client\MockStorage;
use fkooman\OAuth\Client\StorageInterface;
use fkooman\OAuth\Common\Scope;
use Guzzle\Http\Client;

class Given
{
    public function iHaveAnAccessToken()
    {
        return new AccessToken([
            'client_config_id' => 'foo',
            'user_id' => 'bar',
            'scope' => new Scope(['USER_READ', 'DATA_READ_WRITE']),
            'issue_time' => 1,
            'token_type' => 'Bearer',
            'access_token' => 'ABC123',
        ]);
    }
}
