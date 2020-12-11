<?php

namespace Graze\TelnetClient\Test\Unit;

use Exception;
use Graze\TelnetClient\Exception\TelnetExceptionInterface;
use Graze\TelnetClient\InterpretAsCommand;
use Graze\TelnetClient\PromptMatcher;
use Graze\TelnetClient\TelnetClient;
use Graze\TelnetClient\TelnetResponseInterface;
use Mockery as m;
use PHPUnit_Framework_TestCase;
use Socket\Raw\Factory as SocketFactory;
use Socket\Raw\Socket;

class TelnetClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderConnect
     *
     * @param string $prompt
     * @param string $promptError
     * @param string $lineEnding
     * @param float|null $timeout
     *
     * @return void
     */
    public function testConnect($prompt = null, $promptError = null, $lineEnding = null, $timeout = null)
    {
        $dsn = 'localhost:80';
        $socket = m::mock(Socket::class);
        $socketFactory = m::mock(SocketFactory::class)
            ->shouldReceive('createClient')
            ->with($dsn, $timeout)
            ->andReturn($socket)
            ->once()
            ->getMock();

        $telnetClient = m::mock(
            TelnetClient::class,
            [$socketFactory, m::mock(PromptMatcher::class), m::mock(InterpretAsCommand::class)]
        )
            ->shouldReceive('setSocket')
            ->with($socket)
            ->once();

        if ($prompt !== null) {
            $telnetClient
                ->shouldReceive('setPrompt')
                ->with($prompt)
                ->once();
        } else {
            $telnetClient
                ->shouldReceive('setPrompt')
                ->never();
        }

        if ($promptError !== null) {
            $telnetClient
                ->shouldReceive('setPromptError')
                ->with($promptError)
                ->once();
        } else {
            $telnetClient
                ->shouldReceive('setPromptError')
                ->never();
        }

        if ($lineEnding !== null) {
            $telnetClient
                ->shouldReceive('setLineEnding')
                ->with($lineEnding)
                ->once();
        } else {
            $telnetClient
                ->shouldReceive('setLineEnding')
                ->never();
        }

        $telnetClient = $telnetClient->getMock()->makePartial();
        $telnetClient->connect($dsn, $prompt, $promptError, $lineEnding, $timeout);
    }

    /**
     * @return array
     */
    public function dataProviderConnect()
    {
        return [
            ['PROMPT', 'PROMPTERROR', 'LINENDING'],
            ['PROMPT', 'PROMPTERROR', 'LINENDING', 5],
            [null, null, null]
        ];
    }
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

        /** @var SocketFactory $socketFactory */
        $socketFactory = m::mock(SocketFactory::class)
            ->shouldReceive('createClient')
            ->andReturn($socket)
            ->once()
            ->getMock();

        $promptMatcher = new PromptMatcher();

        /** @var InterpretAsCommand $interpretAsCommand */
        $interpretAsCommand = m::mock(InterpretAsCommand::class)
            ->shouldReceive('interpret')
            ->times(count($commandResponse))
            ->andReturn(false)
            ->getMock();

        $telnetClient = new TelnetClient($socketFactory, $promptMatcher, $interpretAsCommand);
        $telnetClient->connect('127.0.0.1:23', $promptGlobal, $promptError, $lineEnding);

        $response = $telnetClient->execute($command, $promptLocal);
        $this->assertInstanceOf(TelnetResponseInterface::class, $response);
        $this->assertEquals($isError, $response->isError());
        $this->assertEquals($command, $response->getResponseText());
        $this->assertEquals($promptMatches, $response->getPromptMatches());
    }
    
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
    public function testExecutePromptError(
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

        /** @var SocketFactory $socketFactory */
        $socketFactory = m::mock(SocketFactory::class)
            ->shouldReceive('createClient')
            ->andReturn($socket)
            ->once()
            ->getMock();

        $promptMatcher = new PromptMatcher();

        /** @var InterpretAsCommand $interpretAsCommand */
        $interpretAsCommand = m::mock(InterpretAsCommand::class)
            ->shouldReceive('interpret')
            ->times(count($commandResponse))
            ->andReturn(false)
            ->getMock();

        $telnetClient = new TelnetClient($socketFactory, $promptMatcher, $interpretAsCommand);
        $telnetClient->connect('127.0.0.1:23', $promptGlobal, null, $lineEnding);

        $response = $telnetClient->execute($command, $promptLocal, $promptError);
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

    public function testExecuteException()
    {
        $this->setExpectedException(TelnetExceptionInterface::class);

        $client = m::mock(TelnetClient::class)->makePartial();
        $client->execute('aCommand');
    }

    public function testWriteException()
    {
        $this->setExpectedException(TelnetExceptionInterface::class);

        $client = m::mock(TelnetClient::class)->makePartial();

        $socket = m::mock(Socket::class)
            ->shouldReceive('write')
            ->andThrow(Exception::class)
            ->shouldReceive('close')
            ->getMock();

        $client->setSocket($socket);
        $client->execute('aCommand');
    }

    public function testReadException()
    {
        $this->setExpectedException(TelnetExceptionInterface::class, 'failed reading from socket');

        $client = m::mock(TelnetClient::class)->makePartial();

        $socket = m::mock(Socket::class)
            ->shouldReceive('write')
            ->shouldReceive('close')
            ->shouldReceive('read')
            ->andThrow(Exception::class)
            ->getMock();

        $client->setSocket($socket);
        $client->execute('aCommand');
    }

    public function testMaxReadCharactersException()
    {
        $this->setExpectedException(
            TelnetExceptionInterface::class, 
            'Maximum number of characters read (20), the last characters were 0123456789'
        );

        $socket = m::mock(Socket::class)
            ->shouldReceive('write')
            ->shouldReceive('close')
            ->shouldReceive('read');

        call_user_func_array([$socket, 'andReturn'], str_split('0123456789012345678912'));
        $socket = $socket->getMock();

        $socketFactory = m::mock(SocketFactory::class)
            ->shouldReceive('createClient')
            ->andReturn($socket)
            ->once()
            ->getMock();

        $promptMatcher = new PromptMatcher();

        $interpretAsCommand = m::mock(InterpretAsCommand::class)
            ->shouldReceive('interpret')
            ->andReturn(false)
            ->getMock();

        $client = new TelnetClient($socketFactory, $promptMatcher, $interpretAsCommand);
        $client->connect('dsn');
        $client->setMaxReadCharacters(20);

        $client->execute('aCommand');
    }

    public function testFactory()
    {
        $client = TelnetClient::factory();
        $this->assertInstanceOf(TelnetClient::class, $client);
    }
}
