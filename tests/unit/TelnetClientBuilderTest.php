<?php

namespace Graze\TelnetClient\Test\Unit;

use Mockery as m;
use Socket\Raw\Factory as SocketFactory;
use Socket\Raw\Socket;
use Graze\TelnetClient\PromptMatcher;
use Graze\TelnetClient\InterpretAsCommand;
use Graze\TelnetClient\TelnetClientBuilder;
use Graze\TelnetClient\TelnetClient;
use Graze\TelnetClient\TelnetClientInterface;

class TelnetClientBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderBuild
     *
     * @param string $prompt
     * @param string $promptError
     * @param string $lineEnding
     *
     * @return void
     */
    public function testBuildClient($prompt = null, $promptError = null, $lineEnding = null)
    {
        $dsn = 'localhost:80';
        $socket = m::mock(Socket::class);
        $socketFactory = m::mock(SocketFactory::class)
            ->shouldReceive('createClient')
            ->with($dsn)
            ->andReturn($socket)
            ->once()
            ->getMock();

        $promptMatcher = new PromptMatcher();
        $interpretAsCommand = new InterpretAsCommand();

        $telnetClient = m::mock(TelnetClient::class)
            ->shouldReceive('setSocket')
            ->with($socket)
            ->once()
            ->shouldReceive('setPromptMatcher')
            ->with($promptMatcher)
            ->once()
            ->shouldReceive('setInterpretAsCommand')
            ->with($interpretAsCommand)
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

        $telnetClient = $telnetClient->getMock();

        $clientBuilder = m::mock(
            TelnetClientBuilder::class,
            [$socketFactory, $promptMatcher, $interpretAsCommand, $telnetClient]
        )
            ->makePartial();

        $client = $clientBuilder->buildClient($dsn, $prompt, $promptError, $lineEnding);
        $this->assertInstanceOf(TelnetClientInterface::class, $client);
    }

    /**
     * @return array
     */
    public function dataProviderBuild()
    {
        return [
            ['PROMPT', 'PROMPTERROR', 'LINENDING'],
            [null, null, null]
        ];
    }
}
