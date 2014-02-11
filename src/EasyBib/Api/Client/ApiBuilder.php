<?php

namespace EasyBib\Api\Client;

use EasyBib\OAuth2\Client\AbstractSession;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\AuthorizationCodeSession;
use EasyBib\OAuth2\Client\JsonWebTokenGrant;
use EasyBib\OAuth2\Client\JsonWebTokenGrant\JsonWebTokenSession;
use EasyBib\OAuth2\Client\RedirectorInterface;
use EasyBib\OAuth2\Client\Scope;
use EasyBib\OAuth2\Client\ServerConfig;
use Guzzle\Http\Client;

class ApiBuilder
{
    /**
     * @var RedirectorInterface
     */
    private $redirector;

    /**
     * @param RedirectorInterface $redirector
     */
    public function __construct(RedirectorInterface $redirector)
    {
        $this->redirector = $redirector;
    }

    public function createWithAuthorizationCodeGrant(array $params, $url = 'https://data.easybib.com')
    {
        $clientConfig = new AuthorizationCodeGrant\ClientConfig([
            'client_id' => $params['client_id'],
            'redirect_url' => $params['redirect_url'],
        ]);

        $serverConfig = new ServerConfig([
            'authorize_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);

        $oauthHttpClient = new Client($url);

        $oauthSession = new AuthorizationCodeSession(
            $oauthHttpClient,
            $this->redirector,
            $clientConfig,
            $serverConfig
        );

        return $this->buildApiTraverser($oauthSession);
    }

    public function createWithJsonWebTokenGrant(array $params, $url = 'https://data.easybib.com')
    {
        $clientConfig = new JsonWebTokenGrant\ClientConfig([
            'client_id' => $params['client_id'],
            'client_secret' => $params['client_secret'],
            'subject' => $params['user_id'],
        ]);

        $serverConfig = new ServerConfig([
            'authorize_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);

        $oauthHttpClient = new Client($url);

        $oauthSession = new JsonWebTokenSession(
            $oauthHttpClient,
            $this->redirector,
            $clientConfig,
            $serverConfig
        );

        return $this->buildApiTraverser($oauthSession, $url);
    }

    /**
     * @param AbstractSession $oauthSession
     * @param string $url
     * @return ApiTraverser
     */
    private function buildApiTraverser(AbstractSession $oauthSession, $url)
    {
        $oauthSession->setScope(new Scope(['USER_READ', 'DATA_READ_WRITE']));
        $apiHttpClient = new Client($url);
        $oauthSession->addResourceClient($apiHttpClient);

        return new ApiTraverser($apiHttpClient);
    }
}
