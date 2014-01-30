<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\Scope;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetQuerystringParams()
    {
        $scope = new \EasyBib\Api\Client\Session\Scope(['USER_READ', 'DATA_READ_WRITE']);
        $this->assertEquals(['scope' => 'USER_READ DATA_READ_WRITE'], $scope->getQuerystringParams());

        $scope = new \EasyBib\Api\Client\Session\Scope([]);
        $this->assertSame([], $scope->getQuerystringParams());
    }
}
