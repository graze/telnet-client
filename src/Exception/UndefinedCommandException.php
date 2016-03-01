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

namespace Graze\TelnetClient\Exception;

use \Exception;
use \OutOfBoundsException;
use \Graze\TelnetClient\Exception\TelentExceptionInterface;

class UndefinedCommandException extends OutOfBoundsException implements TelentExceptionInterface
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @param string $command
     * @param Exception $previous
     */
    public function __construct($command, Exception $previous = null)
    {
        $this->command = $command;
        $message = sprintf('unable to interpret as command [%s]', $command);
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }
}
