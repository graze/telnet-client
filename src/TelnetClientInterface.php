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

interface TelnetClientInterface
{
    /**
     * @param string $dsn
     * @param string $prompt
     * @param string $promptError
     * @param string $lineEnding
     */
    public function connect($dsn, $prompt = null, $promptError = null, $lineEnding = null);

    /**
     * @param string $command
     * @param string $prompt
     *
     * @return \Graze\TelnetClient\TelnetResponseInterface
     */
    public function execute($command, $prompt = null);

    /**
     * @return \Socket\Raw\Socket
     */
    public function getSocket();
}
