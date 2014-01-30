<?php

namespace EasyBib\Tests\Api\Client\Session;

use EasyBib\Api\Client\Session\TokenResponse;

class ClientConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getValidParamSets()
    {
        return [
            [
                [
                    'client_id' => 'ABC123',
                ],
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
        new TokenResponse($params);
    }
}
