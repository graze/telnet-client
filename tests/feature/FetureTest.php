<?php

namespace Graze\TelnetClient\Test\Feature;

use Graze\TelnetClient\TelnetClient;
use Graze\TelnetClient\Test\FeatureTestCase;
use Graze\TelnetClient\PromptMatcher;
use Graze\TelnetClient\InterpretAsCommand;
use Mockery as m;
use Socket\Raw\Factory as SocketFactory;
use Socket\Raw\Socket;

class FeatureTest extends FeatureTestCase
{
    public function test()
    {
        $commandToResponse = [
            "\n"                    => "$\n",
            "john\n"                => "password: \n",
            "battery horse staple"  => "welcome to the server!\n",
        ];

        $client = $this->buildClient($commandToResponse);
        $client->setLineEnding("\n");
        $client->setPrompt("$");
        $client->connect('dsn');
        foreach ($commandToResponse as $command => $responseExpected) {
            $response = $client->execute($command, $responseExpected);
            dump($response);
            dump($response->getResponseText());
            $this->assertEquals($responseExpected, $response->getResponseText());
        }

    }

    private function buildClient(array $commandToResponse)
    {
        $socket = $this->buildSocketMock($commandToResponse);
        $socketFactory = m::mock(SocketFactory::class)
            ->shouldReceive('createClient')
            ->andReturn($socket)
            ->getMock();

        return new TelnetClient(
            $socketFactory,
            new PromptMatcher(),
            new InterpretAsCommand()
        );
    }
}
