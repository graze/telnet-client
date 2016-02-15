<?php

namespace Graze\TelnetClient\Test\Unit;

use Mockery as m;
use Socket\Raw\Factory as SocketFactory;
use Socket\Raw\Socket;
use Graze\TelnetClient\PromptMatcher;
use Graze\TelnetClient\InterpretAsCommand;
use Graze\TelnetClient\TelnetClientBuilder;
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
    public function testBuild($prompt, $promptError, $lineEnding)
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

        $telnetClient = m::mock(TelnetClientInterface::class)
            ->shouldReceive('setSocket')
            ->with($socket)
            ->once()
            ->shouldReceive('setPromptMatcher')
            ->with($promptMatcher)
            ->once()
            ->shouldReceive('setInterpretAsCommand')
            ->with($interpretAsCommand)
            ->once();

        if ($prompt) {
            $telnetClient
                ->shouldReceive('setPrompt')
                ->with($prompt)
                ->once();
        } else {
            $telnetClient
                ->shouldReceive('setPrompt')
                ->never();
        }

        if ($promptError) {
            $telnetClient
                ->shouldReceive('setPromptError')
                ->with($promptError)
                ->once();
        } else {
            $telnetClient
                ->shouldReceive('setPromptError')
                ->never();
        }

        if ($lineEnding) {
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

        $clientBuilder = m::mock(TelnetClientBuilder::class)
            ->shouldReceive('getSocketFactory')
            ->andReturn($socketFactory)
            ->once()
            ->shouldReceive('getPromptMatcher')
            ->andReturn($promptMatcher)
            ->once()
            ->shouldReceive('getInterpretAsCommand')
            ->andReturn($interpretAsCommand)
            ->once()
            ->shouldReceive('getTelnetClient')
            ->andReturn($telnetClient)
            ->once()
            ->getMock()
            ->makePartial();

        $client = $clientBuilder->build($dsn, $prompt, $promptError, $lineEnding);

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
