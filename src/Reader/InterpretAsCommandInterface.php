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

use Socket\Raw\Socket;
use Graze\TelnetClient\Exception\TelnetExceptionInterface;

interface InterpretAsCommandInterface
{
    /**
     * @param string $character
     * @param Socket $socket
     * @return bool
     * @throws TelnetExceptionInterface
     */
    public function interpret($character, Socket $socket);
}
