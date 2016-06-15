<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiBuilder;
use EasyBib\Api\Client\ApiResource\ApiResource;
use EasyBib\OAuth2\Client\TokenStore;
use EasyBib\Tests\Mocks\OAuth2\Client\ExceptionMockRedirector;
use EasyBib\Tests\Mocks\OAuth2\Client\MockRedirectException;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ApiBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiMockResponses
     */
    protected $apiResponses;

    /**
     * @var string
     */
    protected $idBaseUrl = 'http://id.easybib.example.com';

    /**
     * @var string
     */
    protected $dataBaseUrl = 'http://data.easybib.example.com';

    /**
     * @var HistoryPlugin
     */
    protected $history;

    /**
     * @var Client
     */
    protected $apiHttpClient;

    /**
     * @var Client
     */
    protected $oauthHttpClient;

    /**
     * @var ApiTraverser
     */
    protected $api;

    /**
     * @var MockPlugin
     */
    protected $apiMockResponses;

    /**
     * @var MockPlugin
     */
    protected $oauthMockResponses;

    /**
     * @var TokenStore
     */
    protected $tokenStore;

    /**
     * @var ApiBuilder
     */
    protected $builder;

    public function setUp()
    {
        parent::setUp();

        $this->apiHttpClient = new Client($this->dataBaseUrl);
        $this->apiMockResponses = new MockPlugin();
        $this->history = new HistoryPlugin();
        $this->apiHttpClient->addSubscriber($this->apiMockResponses);
        $this->apiHttpClient->addSubscriber($this->history);
        $this->apiResponses = new ApiMockResponses($this->apiMockResponses);

        $this->oauthMockResponses = new MockPlugin();

        $this->oauthHttpClient = new Client($this->idBaseUrl);
        $this->oauthHttpClient->addSubscriber(new HistoryPlugin());
        $this->oauthHttpClient->addSubscriber($this->oauthMockResponses);

        $session = new Session(new MockArraySessionStorage());
        $this->tokenStore = new TokenStore($session);

        $this->builder = new ApiBuilder();
        $this->builder->setRedirector(new ExceptionMockRedirector());
        $this->builder->setOauthHttpClient($this->oauthHttpClient);
        $this->builder->setApiHttpClient($this->apiHttpClient);
        $this->builder->setSession($session);
    }

    public function testAuthorizationCodeGrant()
    {
        $api = $this->builder->createWithAuthorizationCodeGrant([
            'client_id' => 'ABC123',
            'redirect_url' => 'http://foo.example.com/handle-auth-code',
        ]);

        $this->setExpectedException(MockRedirectException::class);

        $api->getUser();
    }

    public function testJsonWebTokenGrant()
    {
        $api = $this->builder->createWithJsonWebTokenGrant([
            'client_id' => 'ABC123',
            'client_secret' => 'XYZ987',
            'user_id' => 'user_456',
        ]);

        $this->prepareTokenResponse();
        $this->apiResponses->prepareResource(
            ['data' => ['foo' => 'bar']]
        );

        $this->assertInstanceOf(ApiResource::class, $api->getUser());
    }

    private function prepareTokenResponse()
    {
        $response = new Response(
            200,
            [],
            json_encode([
                'access_token' => 'token_ABC123',
                'token_type' => 'bearer',
            ])
        );

        $this->oauthMockResponses->addResponse($response);
    }
}
