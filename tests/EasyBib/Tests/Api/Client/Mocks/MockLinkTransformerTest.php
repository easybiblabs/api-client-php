<?php

namespace EasyBib\Tests\Api\Client\Mocks;

use EasyBib\Tests\Mocks\Api\Client\LinkTransformer\MockLinkTransformer;

class MockLinkTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var MockLinkTransformer
     */
    private $linkTransformer;

    public function setUp()
    {
        parent::setUp();

        $this->callback = function ($input) {
            return $input . 'bar';
        };

        $this->linkTransformer = new MockLinkTransformer($this->callback);
    }

    public function testTransform()
    {
        $text = 'foo';

        $this->assertEquals(
            call_user_func($this->callback, $text),
            $this->linkTransformer->transform($text)
        );
    }
}
