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
use Socket\Raw\Socket;
use Graze\TelnetClient\Exception\TelnetException;
use Graze\TelnetClient\Exception\TelnetExceptionInterface;
use Graze\TelnetClient\Exception\UndefinedCommandException;
use Graze\TelnetClient\Reader\InterpretAsCommandInterface;

class InterpretAsCommand implements InterpretAsCommandInterface
{
    /**
     * @var string
     */
    private $WILL;

    /**
     * @var string
     */
    private $WONT;

    /**
     * @var string
     */
    private $DO;

    /**
     * @var string
     */
    private $DONT;

    /**
     * @var string
     */
    private $IAC;

    public function __construct()
    {
        $this->WILL = chr(251);
        $this->WONT = chr(252);
        $this->DO = chr(253);
        $this->DONT = chr(254);
        $this->IAC = chr(255);
    }

    /**
     * @param string $character
     * @param Socket $socket
     * @return bool
     * @throws TelnetExceptionInterface
     */
    public function interpret($character, Socket $socket)
    {
        if ($character != $this->IAC) {
            return false;
        }

        try {
            $command = $socket->read(1);
            $option = $socket->read(1);

            if (in_array($command, [$this->DO, $this->DONT])) {
                $socket->write($this->IAC . $this->WONT . $option);
                return true;
            }

            if (in_array($command, [$this->WILL, $this->WONT])) {
                $socket->write($this->IAC . $this->DONT . $option);
                return true;
            }
        } catch (Exception $e) {
            throw new TelnetException('Failed negotiating IAC', 0, $e);
        }

        throw new UndefinedCommandException($command);
    }
}
