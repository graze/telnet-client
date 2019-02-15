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

namespace Graze\TelnetClient\Matcher;

use Graze\TelnetClient\Matcher\MatcherInterface;
use Graze\TelnetClient\Exception\TelnetException;

class MatcherRegex implements MatcherInterface
{
    /**
     * @param string $expected
     * @param string $subject
     * @param array $matches
     * @return bool
     */
    public function match($expected, $subject, array &$matches)
    {
        $result = preg_match($expected, $subject, $matches);
        if ($result === false) {
            throw new TelnetException(sprintf('preg_match failed for pattern [%s]', $expected));
        }

        // preg_match returns 1 for match, 0 for none. false on error
        return (bool) $result;
    }
}
