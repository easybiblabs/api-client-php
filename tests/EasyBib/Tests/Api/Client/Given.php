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
    public function iHaveAnOauthClient(StorageInterface $storage, Client $httpClient)
    {
        return new Api(
            'config-id',
            new ClientConfig([
                'authorize_endpoint' => 'http://foo/oauth/authorize',
                'token_endpoint' => 'http://foo/oauth/token',
                'client_id' => 'foo_id',
                'client_secret' => 'bar_secret',
            ]),
            $storage,
            $httpClient
        );
    }

    public function iHaveAnApiTraverser(Api $oauthClient, Client $httpClient)
    {
        return new ApiTraverser($oauthClient, $httpClient);
    }

    public function iHaveATokenStore(AccessToken $accessToken)
    {
        $storage = new MockStorage();
        $storage->storeAccessToken($accessToken);

        return $storage;
    }

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

    public function iHaveAnAccessContext()
    {
        return new Context('bar', ['USER_READ', 'DATA_READ_WRITE']);
    }
}
