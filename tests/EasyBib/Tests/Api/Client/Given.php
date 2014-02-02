<?php

namespace EasyBib\Tests\Api\Client;

use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;

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
}
