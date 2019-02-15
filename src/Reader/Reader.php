<?php

/**
 * This file is part of graze/telnet-client.
 *
 * Copyright (c) 2018 Nature Delivered Ltd. <https://www.graze.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license https://github.com/graze/telnet-client/blob/master/LICENSE
 * @link https://github.com/graze/telnet-client
 */

namespace Graze\TelnetClient\Reader;

use Exception;
use Graze\TelnetClient\Exception\TelnetException;
use Graze\TelnetClient\Exception\TelnetExceptionInterface;
use Graze\TelnetClient\Matcher\MatcherInterface;
use Graze\TelnetClient\Matcher\MatcherRegex;
use Graze\TelnetClient\Reader\InterpretAsCommand;
use Graze\TelnetClient\Reader\InterpretAsCommandInterface;
use Graze\TelnetClient\Reader\ReaderInterface;
use Graze\TelnetClient\TelnetResponse;
use Graze\TelnetClient\TelnetResponseInterface;
use Socket\Raw\Socket;

class Reader implements ReaderInterface
{
    /**
     * @var MatcherInterface
     */
    private $matcher;

    /**
     * @var InterpretAsCommandInterface
     */
    private $interpretAsCommand;

    /**
     * @var string
     */
    private $NULL;

    /**
     * @var string
     */
    private $DC1;

    /**
     * @param MatcherInterface $mathcher
     * @param InterpretAsCommandInterface $interpretAsCommand
     */
    public function __construct(
        MatcherInterface $matcher,
        InterpretAsCommandInterface $interpretAsCommand
    ) {
        $this->matcher = $matcher;
        $this->interpretAsCommand = $interpretAsCommand;

        $this->NULL = chr(0);
        $this->DC1 = chr(17);
    }

    /**
     * @param Socket $socket
     * @param string $expected
     * @param string $expectedError
     * @return TelnetResponseInterface
     * @throws TelnetExceptionInterface
     */
    public function read(Socket $socket, $expected, $expectedError)
    {
        $buffer = '';
        $matches = [];
        $isError = false;
        do {
            // process one character at a time
            try {
                $character = $socket->read(1);
            } catch (Exception $e) {
                throw new TelnetException('Failed reading from socket', 0, $e);
            }

            if (in_array($character, [$this->NULL, $this->DC1])) {
                break;
            }

            if ($this->interpretAsCommand->interpret($character, $socket)) {
                continue;
            }

            $buffer .= $character;

            if ($this->matcher->match($expected, $buffer, $matches)) {
                break;
            }

            if ($this->matcher->match($expectedError, $buffer, $matches)) {
                $isError = true;
                break;
            }

            if (preg_match($expectedError, $buffer, $matches)) {
                $isError = true;
                break;
            }
        } while (true);

        return new TelnetResponse(
            $isError,
            $matches
        );
    }

    /**
     * @return ReaderInterface
     */
    public static function factory()
    {
        return new static(
            new MatcherRegex(),
            new InterpretAsCommand()
        );
    }
}
