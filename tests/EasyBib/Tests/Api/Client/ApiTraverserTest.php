<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\Session\ApiSession;
use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use fkooman\Guzzle\Plugin\BearerAuth\BearerAuth;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\History\HistoryPlugin;
use Guzzle\Plugin\Mock\MockPlugin;

class ApiTraverserTest extends TestCase
{
    public function testGetCorrectAcceptHeader()
    {
        $httpClient = new Client();

        $responses = new MockPlugin([
            new Response(200, [], '{"data":{}}'),
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

    public function testGetUserReturnsResource()
    {
        $httpClient = new Client();

        $responseBody = json_encode([
            'status' => 'ok',
            'data' => [
                'first' => 'Jim',
                'last' => 'Johnson',
                'email' => 'jj@example.org',
                'role' => 'mybib',
            ],
        ]);

        $responses = new MockPlugin([
            new Response(200, [], $responseBody),
        ]);

        $history = new HistoryPlugin();

        $httpClient->addSubscriber($responses);
        $httpClient->addSubscriber($history);

        $api = new ApiTraverser($httpClient);

        $this->assertInstanceOf(Resource::class, $api->getUser());
    }

    public function testGetCitationsReturnsCollection()
    {
        $httpClient = new Client();

        $responseBody = json_encode([
            'status' => 'ok',
            'data' => [
                [
                    'data' => [
                        'source' => 'book',
                        'pubtype' => ['main' => 'pubnonperiodical'],
                    ],
                ],
            ],
        ]);

        $responses = new MockPlugin([
            new Response(200, [], $responseBody),
        ]);

        $history = new HistoryPlugin();

        $httpClient->addSubscriber($responses);
        $httpClient->addSubscriber($history);

        $api = new ApiTraverser($httpClient);

        $this->assertInstanceOf(Collection::class, $api->get('citations'));
    }

    public function testGetPassesTokenInHeader()
    {
        $httpClient = new Client();

        $responses = new MockPlugin([
            new Response(200, [], '{"data":""}'),
        ]);

        $history = new HistoryPlugin();

        $httpClient->addSubscriber($responses);
        $httpClient->addSubscriber($history);

        $accessToken = $this->given->iHaveAnAccessToken();
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
        $httpClient->addSubscriber($responses);
        $httpClient->addSubscriber($history);

        $api = new ApiTraverser($httpClient);
        $api->get('url placeholder');
    }
}
