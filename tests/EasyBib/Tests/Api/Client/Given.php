<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\OAuth2\Client\AuthorizationCodeGrant\AuthorizationCodeSession;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\ClientConfig as AuthCodeClientConfig;
use EasyBib\OAuth2\Client\JsonWebTokenGrant\ClientConfig as JwtClientConfig;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Given
{
    /**
     * @param MockPlugin $mockResponses
     * @param array $resource
     */
    public function iAmReadyToReturnAResource(
        MockPlugin $mockResponses,
        array $resource = ['data' => []]
    ) {
        $payload = ['status' => 'ok'] + $resource;

        $mockResponses->addResponse(
            new Response(200, [], json_encode($payload))
        );
    }

    /**
     * @param MockPlugin $mockResponses
     */
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

    /**
     * @param $accessToken
     * @param ClientInterface $resourceHttpClient
     */
    public function iHaveRegisteredWithAJwtSession($accessToken, ClientInterface $resourceHttpClient)
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set(TokenStore::KEY_ACCESS_TOKEN, $accessToken);

        $tokenStore = new TokenStore($session);

        $clientConfig = new JwtClientConfig([
            'client_id' => 'client_123',
            'client_secret' => 'secret_123',
            'subject' => 'user_123',
        ]);

        $serverConfig = new ServerConfig([
            'authorize_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);

        $oauthHttpClient = new Client('http://data.easybib.com');

        $oauthSession = new JsonWebTokenSession(
            $oauthHttpClient,
            $clientConfig,
            $serverConfig
        );

        $oauthSession->setTokenStore($tokenStore);
        $oauthSession->setScope(new Scope(['USER_READ', 'DATA_READ_WRITE']));
        $oauthSession->addResourceClient($resourceHttpClient);
    }

    /**
     * @param $accessToken
     * @param ClientInterface $resourceHttpClient
     */
    public function iHaveRegisteredWithAnAuthCodeSession($accessToken, ClientInterface $resourceHttpClient)
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set(TokenStore::KEY_ACCESS_TOKEN, $accessToken);

        $tokenStore = new TokenStore($session);

        $clientConfig = new AuthCodeClientConfig([
            'client_id' => 'client_123',
        ]);

        $serverConfig = new ServerConfig([
            'authorize_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);

        $oauthHttpClient = new Client('http://data.easybib.com');

        $oauthSession = new AuthorizationCodeSession(
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
