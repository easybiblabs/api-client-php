<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Guzzle\BearerAuthMiddleware;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\AuthorizationCodeSession;
use EasyBib\OAuth2\Client\JsonWebTokenGrant;
use EasyBib\OAuth2\Client\JsonWebTokenGrant\TokenRequestFactory;
use EasyBib\OAuth2\Client\Scope;
use EasyBib\OAuth2\Client\ServerConfig;
use EasyBib\OAuth2\Client\SimpleSession;
use EasyBib\OAuth2\Client\TokenStore;
use EasyBib\Tests\Mocks\Api\Client\ExceptionMockRedirector;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApiMockResponses
{
    private $mocks;

    /**
     * @param MockHandler $mocks
     */
    public function __construct(MockHandler $mocks)
    {
        $this->mocks = $mocks;
    }

    /**
     * @param array $resource An array representing the resource to return. Uses
     *     an empty resource by default.
     * @return array
     */
    public function prepareResource(
        array $resource = ['data' => []]
    ) {
        $payload = ['status' => 'ok'] + $resource;

        $this->mocks->append(
            new Response(200, [], json_encode($payload))
        );

        return $resource;
    }

    public function prepareExpiredTokenError()
    {
        $body = json_encode([
            'error' => 'invalid_grant',
            'error_description' => 'The access token provided has expired',
        ]);

        $this->mocks->append(
            new Response(400, [], $body)
        );
    }

    public function prepareUnauthorizedTokenError()
    {
        $body = json_encode([
            'msg' => 'The project you requested is not valid for this token.',
        ]);

        $this->mocks->append(
            new Response(403, [], $body)
        );
    }

    public function prepareInvalidJson()
    {
        $body = 'blah';

        $this->mocks->append(
            new Response(200, [], $body)
        );
    }

    /**
     * @param array $error
     * @param int $code
     */
    public function prepareApiError(array $error, $code = 400)
    {
        $body = json_encode($error);

        $this->mocks->append(
            new Response($code, [], $body)
        );
    }

    /**
     * @param int $code
     */
    public function prepareInfrastructureError($code)
    {
        $headers = ['Content-Type' => 'text/html'];
        $body = '<html><head></head><body>Some error</body></html>';

        $this->mocks->append(
            new Response($code, $headers, $body)
        );
    }

    /**
     * @param string $message
     */
    public function prepareApiMsg($message)
    {
        $body = json_encode(['msg' => $message]);

        $this->mocks->append(
            new Response(400, [], $body)
        );
    }

    /**
     * @param $accessToken
     * @param ClientInterface $resourceHttpClient
     */
    public function registerWithJwtSession($accessToken, ClientInterface $resourceHttpClient)
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

        $oauthHttpClient = new Client(['base_uri' => 'http://id.easybib.example.com']);

        $tokenRequestFactory = new TokenRequestFactory(
            $clientConfig,
            $serverConfig,
            $oauthHttpClient,
            new Scope(['USER_READ', 'DATA_READ_WRITE'])
        );

        $tokenStore = new TokenStore($session);

        $oauthSession = new SimpleSession($tokenRequestFactory);
        $oauthSession->setTokenStore($tokenStore);

        /** @var HandlerStack $handler */
        $handler = $resourceHttpClient->getConfig('handler');
        $handler->push(function ($callable) use ($oauthSession) {
            return new BearerAuthMiddleware($callable, $oauthSession);
        });
    }

    /**
     * @param $accessToken
     * @param ClientInterface $resourceHttpClient
     */
    public function registerWithAuthCodeSession($accessToken, ClientInterface $resourceHttpClient)
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

        $oauthHttpClient = new Client(['base_uri' => 'http://id.easybib.example.com']);

        $oauthSession = new AuthorizationCodeSession(
            $oauthHttpClient,
            new ExceptionMockRedirector(),
            $clientConfig,
            $serverConfig
        );

        $oauthSession->setTokenStore($tokenStore);
        $oauthSession->setScope(new Scope(['USER_READ', 'DATA_READ_WRITE']));

        /** @var HandlerStack $handler */
        $handler = $resourceHttpClient->getConfig('handler');
        $handler->push(function ($callable) use ($oauthSession) {
            return new BearerAuthMiddleware($callable, $oauthSession);
        });
    }
}
