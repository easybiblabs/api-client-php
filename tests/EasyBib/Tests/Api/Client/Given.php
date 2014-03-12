<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\Resource\Resource;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\AuthorizationCodeSession;
use EasyBib\OAuth2\Client\JsonWebTokenGrant;
use EasyBib\OAuth2\Client\JsonWebTokenGrant\TokenRequestFactory;
use EasyBib\OAuth2\Client\Scope;
use EasyBib\OAuth2\Client\ServerConfig;
use EasyBib\OAuth2\Client\SimpleSession;
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
     * @param array $resource An array representing the resource to return. Uses
     *     an empty resource by default.
     * @return array
     */
    public function iAmReadyToRespondWithAResource(
        MockPlugin $mockResponses,
        array $resource = ['data' => []]
    ) {
        $payload = ['status' => 'ok'] + $resource;

        $mockResponses->addResponse(
            new Response(200, [], json_encode($payload))
        );

        return $resource;
    }

    /**
     * @param MockPlugin $mockResponses
     */
    public function iAmReadyToRespondWithAToken(MockPlugin $mockResponses)
    {
        $response = new Response(
            200,
            [],
            json_encode([
                'access_token' => 'token_ABC123',
                'token_type' => 'bearer',
            ])
        );

        $mockResponses->addResponse($response);
    }

    /**
     * @param MockPlugin $mockResponses
     */
    public function iAmReadyToRespondWithAnExpiredTokenError(MockPlugin $mockResponses)
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
     * @param MockPlugin $mockResponses
     */
    public function iAmReadyToRespondWithAnUnauthorizedTokenError(MockPlugin $mockResponses)
    {
        $body = json_encode([
            'msg' => 'The project you requested is not valid for this token.',
        ]);

        $mockResponses->addResponse(
            new Response(403, [], $body)
        );
    }

    /**
     * @param MockPlugin $mockResponses
     */
    public function iAmReadyToRespondWithInvalidJson(MockPlugin $mockResponses)
    {
        $body = 'blah';

        $mockResponses->addResponse(
            new Response(200, [], $body)
        );
    }

    /**
     * @param MockPlugin $mockResponses
     * @param array $error
     * @param int $code
     */
    public function iAmReadyToRespondWithAnApiError(MockPlugin $mockResponses, array $error, $code = 400)
    {
        $body = json_encode($error);

        $mockResponses->addResponse(
            new Response($code, [], $body)
        );
    }

    /**
     * @param MockPlugin $mockResponses
     * @param string $message
     */
    public function iAmReadyToRespondWithAnApiMsg(MockPlugin $mockResponses, $message)
    {
        $body = json_encode(['msg' => $message]);

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

        $clientConfig = new JsonWebTokenGrant\ClientConfig([
            'client_id' => 'client_123',
            'client_secret' => 'secret_123',
            'subject' => 'user_123',
        ]);

        $serverConfig = new ServerConfig([
            'token_endpoint' => '/oauth/token',
        ]);

        $oauthHttpClient = new Client('http://id.easybib.example.com');

        $tokenRequestFactory = new TokenRequestFactory(
            $clientConfig,
            $serverConfig,
            $oauthHttpClient,
            new Scope(['USER_READ', 'DATA_READ_WRITE'])
        );

        $tokenStore = new TokenStore($session);

        $oauthSession = new SimpleSession($tokenRequestFactory);
        $oauthSession->setTokenStore($tokenStore);
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

        $clientConfig = new AuthorizationCodeGrant\ClientConfig([
            'client_id' => 'client_123',
        ]);

        $serverConfig = new AuthorizationCodeGrant\ServerConfig([
            'authorization_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);

        $oauthHttpClient = new Client('http://id.easybib.example.com');

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
