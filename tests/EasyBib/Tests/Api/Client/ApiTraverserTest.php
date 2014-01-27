<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiSession;
use EasyBib\Api\Client\ApiTraverser;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;

class ApiTraverserTest extends TestCase
{
    public function testGetWithoutUrlGetsUser()
    {
        $httpClient = new Client();

        $responses = new MockPlugin([
            new Response(200, [], '{}'),
        ]);

        $history = new HistoryPlugin();

        $httpClient->addSubscriber($responses);
        $httpClient->addSubscriber($history);

        $api = new ApiTraverser($httpClient);
        $api->get();

        $this->stringEndsWith('/user/', $history->getLastRequest()->getUrl());
    }

    public function testGetCorrectAcceptHeader()
    {
        $httpClient = new Client();

        $responses = new MockPlugin([
            new Response(200, [], '{}'),
        ]);

        $history = new HistoryPlugin();

        $httpClient->addSubscriber($responses);
        $httpClient->addSubscriber($history);

        $api = new ApiTraverser($httpClient);
        $api->get('url placeholder');

        $this->assertTrue(
            $history->getLastRequest()->getHeader('Accept')
                ->hasValue('application/vnd.com.easybib.data+json')
        );
    }

    public function testGetPassesTokenInHeader()
    {
        $httpClient = new Client();

        $responses = new MockPlugin([
            new Response(200, [], '{}'),
        ]);

        $history = new HistoryPlugin();

        $httpClient->addSubscriber($responses);
        $httpClient->addSubscriber($history);

        $accessToken = $this->given->iHaveAnAccessToken()->getAccessToken();
        $bearerAuth = new BearerAuth($accessToken);
        $httpClient->addSubscriber($bearerAuth);

        $api = new ApiTraverser($httpClient);
        $api->get('url placeholder');

        $this->assertTrue(
            $history->getLastRequest()->getHeader('Authorization')
                ->hasValue('Bearer ' . $accessToken)
        );
    }

    /**
     * @expectedException EasyBib\Api\Client\ExpiredTokenException
     */
    public function testGetWithExpiredToken()
    {
        $body = json_encode([
            'error' => 'invalid_grant',
            'error_description' => 'The access token provided has expired',
        ]);

        $responses = new MockPlugin([
            new Response(400, [], $body),
        ]);

        $history = new HistoryPlugin();

        $httpClient = new Client();
        $httpClient->setDefaultOption('exceptions', false);

        $httpClient->addSubscriber($responses);
        $httpClient->addSubscriber($history);

        $api = new ApiTraverser($httpClient);
        $api->get('url placeholder');
    }
}
