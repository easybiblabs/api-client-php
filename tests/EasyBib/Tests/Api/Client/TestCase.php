<?php

namespace EasyBib\Tests\Api\Client;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Given
     */
    protected $given;

    public function __construct()
    {
        parent::__construct();

        $this->given = new Given();
    }
}
