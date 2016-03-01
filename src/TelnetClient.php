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

use \Graze\TelnetClient\TelnetClientInterface;
use \Graze\TelnetClient\PromptMatcher;
use \Graze\TelnetClient\InterpretAsCommand;
use \Socket\Raw\Socket;
use \Socket\Raw\Factory as SocketFactory;
use \Graze\TelnetClient\TelnetClientBuilder;

class TelnetClient implements TelnetClientInterface
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
     * @var string
     */
    protected $prompt = '\$';

    /**
     * @var string
     */
    protected $promptError = 'ERROR';

    /**
     * @var string
     */
    protected $lineEnding = "\n";

    /**
     * @var Socket
     */
    protected $socket;

    /**
     * @var string
     */
    protected $buffer;

    /**
     * @var string
     */
    protected $NULL;

    /**
     * @var string
     */
    protected $DC1;

    /**
     * @var string
     */
    protected $IAC;

    /**
     * @param SocketFactory $socketFactory
     * @param PromptMatcher $promptMatcher
     * @param InterpretAsCommand $interpretAsCommand
     */
    public function __construct(
        SocketFactory $socketFactory,
        PromptMatcher $promptMatcher,
        InterpretAsCommand $interpretAsCommand
    ) {
        $this->socketFactory = $socketFactory;
        $this->promptMatcher = $promptMatcher;
        $this->interpretAsCommand = $interpretAsCommand;

        $this->NULL = chr(0);
        $this->DC1 = chr(17);
    }

    /**
     * @param string $dsn
     * @param string $prompt
     * @param string $promptError
     * @param string $lineEnding
     */
    public function connect($dsn, $prompt = null, $promptError = null, $lineEnding = null)
    {
        if ($prompt !== null) {
            $this->setPrompt($prompt);
        }

        if ($promptError !== null) {
            $this->setPromptError($promptError);
        }

        if ($lineEnding !== null) {
            $this->setLineEnding($lineEnding);
        }

        $this->setSocket($this->socketFactory->createClient($dsn));
    }

    /**
     * @param string $prompt
     */
    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;
    }

    /**
     * @param string $promptError
     */
    public function setPromptError($promptError)
    {
        $this->promptError = $promptError;
    }

    /**
     * @param string $lineEnding
     */
    public function setLineEnding($lineEnding)
    {
        $this->lineEnding = $lineEnding;
    }

    /**
     * @param Socket $socket
     */
    public function setSocket(Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @param string $command
     * @param string $prompt
     *
     * @return \Graze\TelnetClient\TelnetResponseInterface
     */
    public function execute($command, $prompt = null)
    {
        $this->write($command);
        return $this->getResponse($prompt);
    }

    /**
     * @param string $command
     *
     * @return void
     */
    protected function write($command)
    {
        $this->socket->write($command . $this->lineEnding);
    }

    /**
     * @param string $prompt
     *
     * @return \Graze\TelnetClient\TelnetResponseInterface
     */
    protected function getResponse($prompt = null)
    {
        $isError = false;
        $buffer = '';
        do {
            // process one character at a time (RFC 854 states 8-bit ASCII characters)
            $character = $this->socket->read(1);

            if (in_array($character, [$this->NULL, $this->DC1])) {
                break;
            }

            if ($this->interpretAsCommand->interpret($character, $this->socket)) {
                continue;
            }

            $buffer .= $character;

            // check for prompt
            if ($this->promptMatcher->isMatch($prompt ?: $this->prompt, $buffer, $this->lineEnding)) {
                break;
            }

            // check for error prompt
            if ($this->promptMatcher->isMatch($this->promptError, $buffer, $this->lineEnding)) {
                $isError = true;
                break;
            }

        } while (true);

        return new TelnetResponse(
            $isError,
            $this->promptMatcher->getResponseText(),
            $this->promptMatcher->getMatches()
        );
    }


    /**
     * @return TelnetClientInterface
     */
    public static function factory()
    {
        new static(
            new SocketFactory(),
            new PromptMatcher(),
            new InterpretAsCommand()
        );
    }

    public function __destruct()
    {
        if (!$this->socket) {
            return;
        }

        $this->socket->close();
    }
}
