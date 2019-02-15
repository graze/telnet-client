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

namespace Graze\TelnetClient;

use Graze\TelnetClient\TelnetResponseInterface;

class TelnetResponse implements TelnetResponseInterface
{
    /**
     * @var bool
     */
    private $isError;

    /**
     * @var array
     */
    private $matches = [];

    /**
     * @param bool $isError
     * @param array $matches
     */
    public function __construct($isError, array $matches)
    {
        $this->isError = (bool) $isError;
        $this->matches = $matches;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->isError;
    }

    /**
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }
}
