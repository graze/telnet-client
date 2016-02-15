<?php

/**
 * This file is part of graze/telnet-client.
 *
 * Copyright (c) 2016 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/telnet-client/blob/master/LICENSE
 * @link https://github.com/graze/telnet-client
 */

namespace Graze\TelnetClient;

use Socket\Raw\Factory as SocketFactory;
use Graze\TelnetClient\PromptMatcher;
use Graze\TelnetClient\InterpretAsCommand;
use Graze\TelnetClient\TelnetClient;

class TelnetClientBuilder
{
    /**
     * @param string $dsn
     * @param string $prompt
     * @param string $promptError
     * @param string $lineEnding
     *
     * @return Graze\TelnetClient\TelnetClientInterface
     */
    public function build($dsn, $prompt = null, $promptError = null, $lineEnding = null)
    {
        $socketFactory = $this->getSocketFactory();
        $socket = $socketFactory->createClient($dsn);

        $promptMatcher = $this->getPromptMatcher();

        $interpretAsCommand = $this->getInterpretAsCommand();

        $telnetClient = $this->getTelnetClient();
        $telnetClient->setSocket($socket);
        $telnetClient->setPromptMatcher($promptMatcher);
        $telnetClient->setInterpretAsCommand($interpretAsCommand);

        if ($prompt) {
            $telnetClient->setPrompt($prompt);
        }

        if ($promptError) {
            $telnetClient->setPromptError($promptError);
        }

        if ($lineEnding) {
            $telnetClient->setLineEnding($lineEnding);
        }

        return $telnetClient;
    }

    /**
     * @return SocketFactory
     */
    public function getSocketFactory()
    {
        return new SocketFactory();
    }

    /**
     * @return PromptMatcher
     */
    public function getPromptMatcher()
    {
        return new PromptMatcher();
    }

    /**
     * @return InterpretAsCommand
     */
    public function getInterpretAsCommand()
    {
        return new InterpretAsCommand();
    }

    /**
     * @return TelnetClient
     */
    public function getTelnetClient()
    {
        return new TelnetClient();
    }
}
