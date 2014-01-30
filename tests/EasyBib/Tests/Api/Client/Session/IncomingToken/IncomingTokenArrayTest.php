<?php

namespace EasyBib\Tests\Api\Client\Session\IncomingToken;

use EasyBib\Api\Client\Session\TokenResponse;

class IncomingTokenArrayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getValidParamSets()
    {
        return [
            [
                [
                    'access_token' => 'ABC123',
                ],
                'ABC123',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getInvalidParamSets()
    {
        $validSet = $this->getValidParamSets()[0][0];

        $invalidSets = [];

        foreach (array_keys($validSet) as $key) {
            $set = $validSet;
            unset($set[$key]);
            $invalidSets[] = [$set];
        }

        return $invalidSets;
    }

    /**
     * @dataProvider getInvalidParamSets
     * @param array $params
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorValidates(array $params)
    {
        new \EasyBib\Api\Client\Session\TokenResponse($params);
    }

    /**
     * @dataProvider getValidParamSets
     * @param array $params
     * @param string $token
     */
    public function testGetToken(array $params, $token)
    {
        $incomingToken = new \EasyBib\Api\Client\Session\TokenResponse($params);
        $this->assertEquals($token, $incomingToken->getToken());
    }
}
