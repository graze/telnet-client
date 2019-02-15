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

use Graze\TelnetClient\Exception\TelnetExceptionInterface;
use Graze\TelnetClient\TelnetResponseInterface;
use Socket\Raw\Socket;

interface ReaderInterface
{
    /**
     * @param Socket $socket
     * @param string $expected
     * @param string $expectedError
     * @return TelnetResponseInterface
     * @throws TelnetExceptionInterface
     */
    public function read(Socket $socket, $expected, $expectedError);
}
