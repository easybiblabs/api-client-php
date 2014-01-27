<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ApiSession;
use EasyBib\Api\Client\ApiTraverser;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;

class ApiTraverserTest extends \PHPUnit_Framework_TestCase
{
    private $session;
    private $httpClient;
    private $request;

    public function setUp()
    {
        $this->session = $this->getMock(ApiSession::class);
        $this->httpClient = $this->getMock(Client::class);

        $this->request = $this->getMockBuilder(Request::class)
            ->setConstructorArgs(['get', 'url placeholder'])
            ->getMock();

        $this->request->setClient($this->httpClient);

        $this->httpClient->expects($this->any())
            ->method('get')
            ->will($this->returnValue($this->request));
    }

    public function testGetWithoutUrlGetsUser()
    {
        $this->setResponse(new Response(200, [], '{}'));

        // this is the mock assertion
        $this->httpClient->expects($this->once())
            ->method('get')
            ->with($this->stringEndsWith('/user/'))
            ->will($this->returnValue($this->request));

        $api = new ApiTraverser($this->session, $this->httpClient);
        $api->get();
    }

    public function testGetCorrectAcceptHeader()
    {
        $this->setResponse(new Response(200, [], '{}'));

        // this is the mock assertion
        $this->request->expects($this->at(0))
            ->method('setHeader')
            ->with('Accept', 'application/vnd.com.easybib.data+json');

        $api = new ApiTraverser($this->session, $this->httpClient);
        $api->get('url placeholder');
    }

    public function testGetPassesTokenInHeader()
    {
        $this->setResponse(new Response(200, [], '{}'));
        $this->session->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue('ABC123'));

        // this is the mock assertion
        $this->request->expects($this->at(1))
            ->method('setHeader')
            ->with('Authorization', 'Bearer ABC123');

        $api = new ApiTraverser($this->session, $this->httpClient);
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

        $api = new ApiTraverser($this->session, $this->httpClient);
        $api->get('url placeholder');
    }

    private function setResponse(Response $response)
    {
        $this->request->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));
    }
}
