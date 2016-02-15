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
use Graze\TelnetClient\TelnetClientInterface;

class TelnetClientBuilder
{
    /**
     * @var SocketFactory
     */
    protected $socketFactory;

    /**
     * @var PromptMatcher
     */
    protected $promptMatcher;

    /**
     * @var InterpretAsCommand
     */
    protected $interpretAsCommand;

    /**
     * @var TelnetClientInterface
     */
    protected $telnetClient;

    /**
     * @param SocketFactory $socketFactory
     * @param PromptMatcher $promptMatcher
     * @param InterpretAsCommand $interpretAsCommand
     * @param TelnetClientInterface $telnetClient
     */
    public function __construct(
        SocketFactory $socketFactory,
        PromptMatcher $promptMatcher,
        InterpretAsCommand $interpretAsCommand,
        TelnetClientInterface $telnetClient
    ) {
        $this->socketFactory = $socketFactory;
        $this->promptMatcher = $promptMatcher;
        $this->interpretAsCommand = $interpretAsCommand;
        $this->telnetClient = $telnetClient;
    }

    /**
     * @param string $dsn
     * @param string $prompt
     * @param string $promptError
     * @param string $lineEnding
     *
     * @return TelnetClientInterface
     */
    public function buildClient($dsn, $prompt = null, $promptError = null, $lineEnding = null)
    {
        $socket = $this->socketFactory->createClient($dsn);

        $this->telnetClient->setSocket($socket);
        $this->telnetClient->setPromptMatcher($this->promptMatcher);
        $this->telnetClient->setInterpretAsCommand($this->interpretAsCommand);

        if ($prompt) {
            $this->telnetClient->setPrompt($prompt);
        }

        if ($promptError) {
            $this->telnetClient->setPromptError($promptError);
        }

        if ($lineEnding) {
            $this->telnetClient->setLineEnding($lineEnding);
        }

        return $this->telnetClient;
    }

    /**
     * @param string $dsn
     * @param string $prompt
     * @param string $promptError
     * @param string $lineEnding
     *
     * @return TelnetClientInterface
     */
    public static function build($dsn, $prompt = null, $promptError = null, $lineEnding = null)
    {
        $builder = new static(
            new SocketFactory(),
            new PromptMatcher(),
            new InterpretAsCommand(),
            new TelnetClient()
        );

        return $builder->buildClient($dsn, $prompt, $promptError, $lineEnding);
    }
}
