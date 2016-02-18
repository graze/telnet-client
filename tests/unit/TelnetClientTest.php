<?php

namespace Graze\TelnetClient\Test\Unit;

use Mockery as m;
use Socket\Raw\Socket;
use Graze\TelnetClient\PromptMatcher;
use Graze\TelnetClient\InterpretAsCommand;
use Graze\TelnetClient\TelnetClient;
use Graze\TelnetClient\TelnetResponseInterface;

class TelnetClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderExecute
     *
     * @param string $command
     * @param array $promptMatches
     * @param bool $isError
     * @param string $promptResponse
     * @param string $promptGlobal
     * @param string $promptLocal
     * @param string $promptError
     * @param string $lineEnding
     *
     * @return void
     */
    public function testExecute(
        $command,
        array $promptMatches,
        $isError,
        $promptResponse,
        $promptGlobal = null,
        $promptLocal = null,
        $promptError = null,
        $lineEnding = null
    ) {
        $lineEnding = $lineEnding ?: "\n";
        $socket = m::mock(Socket::class)
            ->shouldReceive('write')
            ->with($command.$lineEnding)
            ->once();

        // echo the command back when reading each character one-by-one from the socket
        $commandResponse = str_split($command.$lineEnding.$promptResponse.$lineEnding);
        $socket = $socket
            ->shouldReceive('read')
            ->with(1);
        call_user_func_array([$socket, 'andReturn'], $commandResponse);
        $socket = $socket
            ->times(count($commandResponse))
            ->shouldReceive('close')
            ->once()
            ->getMock();

        $promptMatcher = new PromptMatcher();

        $interpretAsCommand = m::mock(InterpretAsCommand::class)
            ->shouldReceive('interpret')
            ->times(count($commandResponse))
            ->andReturn(false)
            ->getMock();

        $telnetClient = new TelnetClient();
        $telnetClient->setSocket($socket);
        $telnetClient->setPromptMatcher($promptMatcher);
        $telnetClient->setInterpretAsCommand($interpretAsCommand);

        if ($promptGlobal !== null) {
            $telnetClient->setPrompt($promptGlobal);
        }

        if ($promptError !== null) {
            $telnetClient->setPromptError($promptError);
        }

        if ($lineEnding !== null) {
            $telnetClient->setLineEnding($lineEnding);
        }

        $response = $telnetClient->execute($command, $promptLocal);
        $this->assertInstanceOf(TelnetResponseInterface::class, $response);
        $this->assertEquals($isError, $response->isError());
        $this->assertEquals($command, $response->getResponseText());
        $this->assertEquals($promptMatches, $response->getPromptMatches());
    }

    /**
     * @return array
     */
    public function dataProviderExecute()
    {
        return [
            ['party', ['$'], false, "\$"], // success
            ['party', ['OK'], false, 'OK', 'OK'], // success custom global prompt
            ['party', ['OK'], false, 'OK', null, 'OK'], // success custom local prompt
            ['party', ['ERROR'], true, 'ERROR'], // error
             // error custom prompt
            ['party', ['DAMN 123', 'DAMN', '123'], true, 'DAMN 123', null, null, '(DAMN) ([0-9]{3})'],
            ['party', ['$'], false, "\$", null, null, null, 'LOL'], // success custom line ending
            ['party', ['ERROR'], true, "ERROR", null, null, null, 'LOL'], // error custom line ending
        ];
    }
}
