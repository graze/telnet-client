<?php

namespace Graze\TelnetClient\Test;

use Graze\TelnetClient\Test\SocketMock;

abstract class FeatureTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //
    }

    protected function buildSocketMock($commandToResponse)
    {
        return new SocketMock($commandToResponse);
    }

    // abstract protected function getServer()
    // {
    //
    // }

}
