<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\Scope;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetQuerystringParams()
    {
        $scope = new Scope(['USER_READ', 'DATA_READ_WRITE']);
        $this->assertEquals(['scope' => 'USER_READ DATA_READ_WRITE'], $scope->getQuerystringParams());

        $scope = new Scope([]);
        $this->assertSame([], $scope->getQuerystringParams());
    }
}
