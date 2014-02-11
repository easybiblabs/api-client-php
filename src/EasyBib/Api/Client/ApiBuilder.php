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
use EasyBib\OAuth2\Client\TokenStore;
use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApiBuilder
{
    /**
     * @var RedirectorInterface
     */
    private $redirector;

    /**
     * @var ClientInterface
     */
    private $oauthHttpClient;

    /**
     * @var ClientInterface
     */
    private $apiHttpClient;

    /**
     * @var TokenStore
     */
    private $tokenStore;

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

        $oauthSession = new AuthorizationCodeSession(
            $this->getOauthHttpClient($url),
            $this->redirector,
            $clientConfig,
            $this->getServerConfig()
        );

        return $this->buildApiTraverser($oauthSession, $url);
    }

    public function createWithJsonWebTokenGrant(array $params, $url = 'https://data.easybib.com')
    {
        $clientConfig = new JsonWebTokenGrant\ClientConfig([
            'client_id' => $params['client_id'],
            'client_secret' => $params['client_secret'],
            'subject' => $params['user_id'],
        ]);

        $oauthSession = new JsonWebTokenSession(
            $this->getOauthHttpClient($url),
            $clientConfig,
            $this->getServerConfig()
        );

        return $this->buildApiTraverser($oauthSession, $url);
    }

    /**
     * @param ClientInterface $httpClient
     */
    public function setOauthHttpClient(ClientInterface $httpClient)
    {
        $this->oauthHttpClient = $httpClient;
    }

    /**
     * @param ClientInterface $httpClient
     */
    public function setApiHttpClient(ClientInterface $httpClient)
    {
        $this->apiHttpClient = $httpClient;
    }

    /**
     * @param TokenStore $tokenStore
     */
    public function setTokenStore(TokenStore $tokenStore)
    {
        $this->tokenStore = $tokenStore;
    }

    /**
     * @param AbstractSession $oauthSession
     * @param string $url
     * @return ApiTraverser
     */
    private function buildApiTraverser(AbstractSession $oauthSession, $url)
    {
        $oauthSession->setTokenStore($this->tokenStore);
        $oauthSession->setScope(new Scope(['USER_READ', 'DATA_READ_WRITE']));
        $apiHttpClient = $this->getApiHttpClient($url);
        $oauthSession->addResourceClient($apiHttpClient);

        return new ApiTraverser($apiHttpClient);
    }

    /**
     * @return ServerConfig
     */
    private function getServerConfig()
    {
        return new ServerConfig([
            'authorization_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);
    }

    /**
     * @param string $url
     * @return ClientInterface
     */
    private function getOauthHttpClient($url)
    {
        $oauthHttpClient = $this->oauthHttpClient ?: new Client();
        $oauthHttpClient->setBaseUrl($url);

        return $oauthHttpClient;
    }

    /**
     * @param string $url
     * @return ClientInterface
     */
    private function getApiHttpClient($url)
    {
        $oauthHttpClient = $this->apiHttpClient ?: new Client();
        $oauthHttpClient->setBaseUrl($url);

        return $oauthHttpClient;
    }
}
