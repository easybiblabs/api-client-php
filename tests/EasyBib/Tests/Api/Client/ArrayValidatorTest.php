<?php

namespace EasyBib\Tests\Api\Client;

use EasyBib\Api\Client\ArrayValidator;

class ArrayValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getValidData()
    {
        return [
            [
                [
                    'foo' => 'foo123',
                    'bar' => 'bar123',
                    'baz' => 'baz123',
                ],
                [
                    'foo',
                    'bar',
                    'baz',
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function getInvalidData()
    {
        $invalidData = [];

        foreach (array_keys($this->getValidData()[0][0]) as $key) {
            $data = $this->getValidData()[0];
            unset($data[0][$key]);
            $invalidData[] = [$data[0], $data[1], $key];
        }

        return $invalidData;
    }

    /**
     * @dataProvider getInvalidData
     * @params array $input
     * @params array $requiredKeys
     * @params string $expectedMissingKey
     */
    public function testValidateWithInvalidData(array $input, array $requiredKeys, $expectedMissingKey)
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Missing key ' . $expectedMissingKey
        );

        $validator = new ArrayValidator($requiredKeys);
        $validator->validate($input);
    }

    /**
     * @dataProvider getValidData
     * @params array $input
     * @params array $requiredKeys
     */
    public function testValidateWithValidData(array $input, array $requiredKeys)
    {
        $validator = new ArrayValidator($requiredKeys);
        $validator->validate($input);
    }
}
