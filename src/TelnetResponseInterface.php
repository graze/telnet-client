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

interface TelnetResponseInterface
{
    /**
     * Whether an error prompt was encountered.
     *
     * @return bool
     */
    public function isError();

    /**
     * Any response from the server up until a prompt is encountered.
     *
     * @return string
     */
    public function getResponseText();

    /**
     * The portion of the server's response that caused execute() to return.
     *
     * @return array
     */
    public function getPromptMatches();
}
