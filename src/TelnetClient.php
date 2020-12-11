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

use Exception;
use Graze\TelnetClient\Exception\TelnetException;
use Graze\TelnetClient\Exception\TelnetExceptionInterface;
use Socket\Raw\Factory as SocketFactory;
use Socket\Raw\Socket;

class TelnetClient implements TelnetClientInterface
{
    /**
     * @var SocketFactory
     */
    protected $socketFactory;

    /**
     * @var PromptMatcherInterface
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
     * @var int
     */
    protected $maxReadCharacters = 0;

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
     * @param PromptMatcherInterface $promptMatcher
     * @param InterpretAsCommand $interpretAsCommand
     */
    public function __construct(
        SocketFactory $socketFactory,
        PromptMatcherInterface $promptMatcher,
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
     * @param float|null $timeout
     *
     * @throws TelnetExceptionInterface
     */
    public function connect($dsn, $prompt = null, $promptError = null, $lineEnding = null, $timeout = null)
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

        try {
            $socket = $this->socketFactory->createClient($dsn, $timeout);
        } catch (Exception $e) {
            throw new TelnetException(sprintf('unable to create socket connection to [%s]', $dsn), 0, $e);
        }

        $this->setSocket($socket);
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
     * Set the maximum number of characters that can be read per request
     *
     * @param int $maxReadCharacters
     */
    public function setMaxReadCharacters($maxReadCharacters)
    {
        $this->maxReadCharacters = $maxReadCharacters;
    }

    /**
     * @param Socket $socket
     */
    public function setSocket(Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @return Socket
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param string $command
     * @param string $prompt
     * @param string $promptError
     *
     * @return TelnetResponseInterface
     */
    public function execute($command, $prompt = null, $promptError = null)
    {
        if (!$this->socket) {
            throw new TelnetException('attempt to execute without a connection - call connect first');
        }

        $this->write($command);
        return $this->getResponse($prompt, $promptError);
    }

    /**
     * @param string $command
     *
     * @return void
     * @throws TelnetExceptionInterface
     */
    protected function write($command)
    {
        try {
            $this->socket->write($command . $this->lineEnding);
        } catch (Exception $e) {
            throw new TelnetException(sprintf('failed writing to socket [%s]', $command), 0, $e);
        }
    }

    /**
     * @param string $prompt
     * @param string $promptError
     *
     * @return TelnetResponseInterface
     * @throws TelnetExceptionInterface
     */
    protected function getResponse($prompt = null, $promptError = null)
    {
        $isError = false;
        $buffer = '';
        do {
            // process one character at a time
            try {
                $character = $this->socket->read(1);
            } catch (Exception $e) {
                throw new TelnetException('failed reading from socket', 0, $e);
            }

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
            if ($this->promptMatcher->isMatch($promptError ?: $this->promptError, $buffer, $this->lineEnding)) {
                $isError = true;
                break;
            }

            // throw an exception if the number of characters read is greater than the limit
            if ($this->maxReadCharacters > 0 && strlen($buffer) >= $this->maxReadCharacters) {
                throw new TelnetException(sprintf(
                    'Maximum number of characters read (%d), the last characters were %s',
                    $this->maxReadCharacters,
                    substr($buffer, -10)
                ));
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
        return new static(
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
