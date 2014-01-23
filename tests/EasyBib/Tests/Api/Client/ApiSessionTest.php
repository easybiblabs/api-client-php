<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiSession;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

class ApiSessionTest extends \PHPUnit_Framework_TestCase
{
    private $httpClient;
    private $request;

    public function setUp()
    {
        $this->httpClient = $this->getMock(Client::class);

        $this->request = $this->getMockBuilder(Request::class)
            ->setConstructorArgs(['get', 'url placeholder'])
            ->getMock();

        $this->request->setClient($this->httpClient);

        $this->httpClient->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->request));
    }

    public function testGetUser()
    {
        $this->setResponse(new Response(200, [], '{}'));

        // this is the system-under-test mock
        $this->httpClient->expects($this->once())
            ->method('get')
            ->with($this->stringEndsWith('/user/'))
            ->will($this->returnValue($this->request));

        $api = new ApiSession('ABC123', $this->httpClient);
        $api->getUser();
    }

    public function testGetPassesTokenInHeader()
    {
        $this->setResponse(new Response(200, [], '{}'));

        // this is the system-under-test mock
        $this->request->expects($this->once())
            ->method('setHeader')
            ->with('Authorization', 'Bearer ABC123');

        $api = new ApiSession('ABC123', $this->httpClient);
        $api->get('url placeholder');
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

        $this->setResponse(new Response(400, [], $body));

        $api = new ApiSession('ABC123', $this->httpClient);
        $api->get('url placeholder');
    }

    private function setResponse(Response $response)
    {
        $this->request->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));
    }
}
