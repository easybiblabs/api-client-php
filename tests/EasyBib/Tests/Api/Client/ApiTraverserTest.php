<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiTraverser;
use EasyBib\Api\Client\ExpiredTokenException;
use EasyBib\Api\Client\Resource\Collection;
use EasyBib\Api\Client\Resource\Resource;
use Guzzle\Http\Client;

class ApiTraverserTest extends TestCase
{
    public function testGetCorrectAcceptHeader()
    {
        $this->given->iAmReadyToReturnAResource($this->mockResponses);

        $api = new ApiTraverser($this->httpClient);
        $api->get('url placeholder');

        $this->assertTrue(
            $this->history->getLastRequest()->getHeader('Accept')
                ->hasValue('application/vnd.com.easybib.data+json')
        );
    }

    public function testGetUserReturnsResource()
    {
        $resource = ['data' => [
            'first' => 'Jim',
            'last' => 'Johnson',
            'email' => 'jj@example.org',
            'role' => 'mybib',
        ]];

        $this->given->iAmReadyToReturnAResource($this->mockResponses, $resource);

        $api = new ApiTraverser($this->httpClient);

        $this->assertInstanceOf(Resource::class, $api->getUser());
    }

    public function testGetCitationsReturnsCollection()
    {
        $resource = ['data' => [
            [
                'data' => [
                    'source' => 'book',
                    'pubtype' => ['main' => 'pubnonperiodical'],
                ],
            ],
        ]];

        $this->given->iAmReadyToReturnAResource($this->mockResponses, $resource);

        $api = new ApiTraverser($this->httpClient);

        $this->assertInstanceOf(Collection::class, $api->get('citations'));
    }

    public function testGetPassesTokenInHeaderWithJwt()
    {
        $accessToken = 'ABC123';

        $this->given->iHaveAGoodJwtOauthSession($accessToken, $this->httpClient);
        $this->given->iAmReadyToReturnAResource($this->mockResponses);

        $api = new ApiTraverser($this->httpClient);
        $api->get('url placeholder');

        $this->assertTrue(
            $this->history->getLastRequest()->getHeader('Authorization')
                ->hasValue('Bearer ' . $accessToken)
        );
    }

    public function testGetWithExpiredToken()
    {
        $this->given->iAmReadyToReturnAnExpiredTokenError($this->mockResponses);

        $this->setExpectedException(ExpiredTokenException::class);

        $api = new ApiTraverser($this->httpClient);
        $api->get('url placeholder');
    }
}
