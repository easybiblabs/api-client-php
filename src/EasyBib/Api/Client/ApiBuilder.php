<?php

namespace EasyBib\Api\Client;

use EasyBib\OAuth2\Client\AbstractSession;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\AuthorizationCodeSession;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\RedirectorInterface;
use EasyBib\OAuth2\Client\AuthorizationCodeGrant\State\StateStore;
use EasyBib\OAuth2\Client\JsonWebTokenGrant;
use EasyBib\OAuth2\Client\JsonWebTokenGrant\TokenRequestFactory;
use EasyBib\OAuth2\Client\Scope;
use EasyBib\OAuth2\Client\ServerConfig;
use EasyBib\OAuth2\Client\SimpleSession;
use EasyBib\OAuth2\Client\TokenStore;
use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use Symfony\Component\HttpFoundation\Session\Session;

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
     * Dependency-injectable for testing
     *
     * @var ClientInterface
     */
    private $oauthHttpClient;

    /**
     * Dependency-injectable for testing
     *
     * @var ClientInterface
     */
    private $apiHttpClient;

    /**
     * Dependency-injectable for custom session backend, or testing
     *
     * @var Session
     */
    private $session;

    /**
     * @param array $params
     * @param string $dataBaseUrl
     * @param string $idBaseUrl
     * @return ApiTraverser
     */
    public function createWithAuthorizationCodeGrant(
        array $params,
        $dataBaseUrl = 'https://data.easybib.com',
        $idBaseUrl = 'https://id.easybib.com'
    ) {
        $clientConfig = new AuthorizationCodeGrant\ClientConfig([
            'client_id' => $params['client_id'],
            'redirect_url' => $params['redirect_url'],
        ]);

        $serverConfig = new AuthorizationCodeGrant\ServerConfig([
            'authorization_endpoint' => '/oauth/authorize',
            'token_endpoint' => '/oauth/token',
        ]);

        $oauthSession = new AuthorizationCodeSession(
            $this->getOauthHttpClient($idBaseUrl),
            $this->redirector,
            $clientConfig,
            $serverConfig
        );

        $oauthSession->setScope(new Scope(['USER_READ', 'DATA_READ_WRITE']));
        $oauthSession->setStateStore($this->getStateStore());

        return $this->buildApiTraverser($oauthSession, $dataBaseUrl);
    }

    /**
     * @param array $params
     * @param string $dataBaseUrl
     * @param string $idBaseUrl
     * @return ApiTraverser
     */
    public function createWithJsonWebTokenGrant(
        array $params,
        $dataBaseUrl = 'https://data.easybib.com',
        $idBaseUrl = 'https://id.easybib.com'
    ) {
        $clientConfig = new JsonWebTokenGrant\ClientConfig([
            'client_id' => $params['client_id'],
            'client_secret' => $params['client_secret'],
            'subject' => $params['user_id'],
        ]);

        $serverConfig = new ServerConfig([
            'token_endpoint' => '/oauth/token',
        ]);

        $tokenRequestFactory = new TokenRequestFactory(
            $clientConfig,
            $serverConfig,
            $this->getOauthHttpClient($idBaseUrl),
            new Scope(['USER_READ', 'DATA_READ_WRITE', 'GUEST_READ_WRITE'])
        );

        $oauthSession = new SimpleSession($tokenRequestFactory);

        return $this->buildApiTraverser($oauthSession, $dataBaseUrl);
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
     * @param Session $session
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param RedirectorInterface $redirector
     */
    public function setRedirector(RedirectorInterface $redirector)
    {
        $this->redirector = $redirector;
    }

    /**
     * @param AbstractSession $oauthSession
     * @param string $url
     * @return ApiTraverser
     */
    private function buildApiTraverser(AbstractSession $oauthSession, $url)
    {
        $oauthSession->setTokenStore($this->getTokenStore());
        $apiHttpClient = $this->getApiHttpClient($url);
        $oauthSession->addResourceClient($apiHttpClient);

        return new ApiTraverser($apiHttpClient);
    }

    /**
     * @param string $url
     * @return ClientInterface
     */
    private function getOauthHttpClient($url)
    {
        // if none has been provided for testing, instantiate a blank Client()
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
        // if none has been provided for testing, instantiate a blank Client()
        $apiHttpClient = $this->apiHttpClient ?: new Client();
        $apiHttpClient->setBaseUrl($url);

        return $apiHttpClient;
    }

    /**
     * @return TokenStore
     */
    private function getTokenStore()
    {
        // if none has been provided, create one with native PHP sessions
        $session = $this->session ?: new Session();
        return new TokenStore($session);
    }

    /**
     * @return StateStore
     */
    private function getStateStore()
    {
        // if none has been provided, create one with native PHP sessions
        $session = $this->session ?: new Session();
        return new StateStore($session);
    }
}
