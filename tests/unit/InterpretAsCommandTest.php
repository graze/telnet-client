<?php

namespace Graze\TelnetClient\Test\Unit;

use Graze\TelnetClient\Exception\TelnetExceptionInterface;
use Graze\TelnetClient\Exception\UndefinedCommandException;
use Graze\TelnetClient\InterpretAsCommand;
use Mockery as m;
use Socket\Raw\Socket;

class InterpretAsCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderInterpret
     *
     * @param bool $isIac
     * @param string $character
     * @param string $command
     * @param string $option
     * @param string $response
     *
     * @return void
     */
    public function testInterpret($isIac, $character, $command = null, $option = null, $response = null)
    {
        $socket = m::mock(Socket::class);

        if ($isIac) {
            $socket
                ->shouldReceive('read')
                ->andReturn($command, $option)
                ->twice()
                ->shouldReceive('write')
                ->with($response)
                ->once()
                ->getMock();
        }

        $interpretAsCommand = new InterpretAsCommand();
        $this->assertEquals($isIac, $interpretAsCommand->interpret($character, $socket));
    }

    /**
     * @return array
     */
    public function dataProviderInterpret()
    {
        // can't put this in setUp() as dataProviders are called first
        $WILL = chr(251);
        $WONT = chr(252);
        $DO = chr(253);
        $DONT = chr(254);
        $IAC = chr(255);

        return [
            [true, $IAC, $DO, '1', $IAC.$WONT.'1'],
            [true, $IAC, $DONT, '3', $IAC.$WONT.'3'],
            [true, $IAC, $WILL, '5', $IAC.$DONT.'5'],
            [true, $IAC, $WONT, '6', $IAC.$DONT.'6'],
            [false, 'A']
        ];
    }

    public function testUndefinedCommandException()
    {
        $socket = m::mock(Socket::class)
            ->shouldReceive('read')
            ->with(1)
            ->andReturn('A', 'B')
            ->twice()
            ->getMock();
        $interpretAsCommand = new InterpretAsCommand();

        $this->setExpectedException(UndefinedCommandException::class);
        $interpretAsCommand->interpret(chr(255), $socket);
    }

    public function testNegotiationException()
    {
        $this->setExpectedException(TelnetExceptionInterface::class);

        $socket = m::mock(Socket::class)
            ->shouldReceive('read')
            ->andThrow(\Exception::class)
            ->getMock();
        $iac = new InterpretAsCommand();
        $iac->interpret(chr(255), $socket);
    }
}
