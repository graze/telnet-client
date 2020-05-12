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

class TelnetResponse implements TelnetResponseInterface
{
    /**
     * @var bool
     */
    protected $isError;

    /**
     * @var string
     */
    protected $responseText;

    /**
     * @var array
     */
    protected $promptMatches;

    /**
     * @param bool $isError
     * @param string $responseText
     * @param array $promptMatches
     */
    public function __construct($isError, $responseText, array $promptMatches)
    {
        $this->isError = (bool) $isError;
        $this->responseText = $responseText;
        $this->promptMatches = $promptMatches;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->isError;
    }

    /**
     * @return string
     */
    public function getResponseText()
    {
        return $this->responseText;
    }

    /**
     * @return array
     */
    public function getPromptMatches()
    {
        return $this->promptMatches;
    }
}
