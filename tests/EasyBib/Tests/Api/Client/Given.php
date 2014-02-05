<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\OAuth2\Client\JsonWebTokenGrant\ClientConfig;
use EasyBib\OAuth2\Client\JsonWebTokenGrant\JsonWebTokenSession;
use EasyBib\OAuth2\Client\Scope;
use EasyBib\OAuth2\Client\ServerConfig;
use EasyBib\OAuth2\Client\TokenStore;
use EasyBib\Tests\Mocks\OAuth2\Client\ExceptionMockRedirector;
use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class Given
{
    public function iHaveAnAccessToken()
    {
        return 'ABC123';
    }

    public function iAmReadyToReturnAResource(
        MockPlugin $mockResponses,
        array $resource = ['data' => []]
    ) {
        $payload = ['status' => 'ok'] + $resource;

        $mockResponses->addResponse(
            new Response(200, [], json_encode($payload))
        );
    }

    public function iAmReadyToReturnAnExpiredTokenError(MockPlugin $mockResponses)
    {
        $body = json_encode([
            'error' => 'invalid_grant',
            'error_description' => 'The access token provided has expired',
        ]);

        $mockResponses->addResponse(
            new Response(400, [], $body)
        );
    }

    public function iHaveAGoodJwtOauthSession($accessToken, ClientInterface $resourceHttpClient)
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set(TokenStore::KEY_ACCESS_TOKEN, $accessToken);

        $tokenStore = new TokenStore($session);

        $clientConfig = new ClientConfig([
            'client_id' => 'client_123',
            'client_secret' => 'secret_123',
            'subject' => 'user_123',
        ]);

        $serverConfig = new ServerConfig([
            'authorize_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);

        $oauthHttpClient = new Client();

        // TODO remove redirector from JWT
        $oauthSession = new JsonWebTokenSession(
            $oauthHttpClient,
            new ExceptionMockRedirector(),
            $clientConfig,
            $serverConfig
        );

        $oauthSession->setTokenStore($tokenStore);
        $oauthSession->setScope(new Scope(['USER_READ', 'DATA_READ_WRITE']));
        $oauthSession->addResourceClient($resourceHttpClient);
    }
}
