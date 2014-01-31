<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\AuthorizationResponse;

class AuthorizationResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCode()
    {
        $response = new AuthorizationResponse(['code' => 'ABC123']);
        $this->assertEquals('ABC123', $response->getCode());
    }
}
