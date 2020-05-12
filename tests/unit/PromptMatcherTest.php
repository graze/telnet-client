<?php

namespace Graze\TelnetClient\Test\Unit;

use Graze\TelnetClient\PromptMatcher;

class PromptMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderIsMatch
     *
     * @param string $prompt
     * @param string $subject
     * @param string $lineEnding
     * @param bool $isMatch
     * @param array $matches
     * @param string $response
     *
     * @return void
     */
    public function testIsMatch($prompt, $subject, $lineEnding, $isMatch, array $matches = null, $response = null)
    {
        $promptMatcher = new PromptMatcher();

        $this->assertEquals($isMatch, $promptMatcher->isMatch($prompt, $subject, $lineEnding));

        if (!$isMatch) {
            return;
        }

        $this->assertEquals($matches, $promptMatcher->getMatches());
        $this->assertEquals($response, $promptMatcher->getResponseText());
    }

    /**
     * @return array
     */
    public function dataProviderIsMatch()
    {
        return [
            ['OK', 'this is a response', "\n", false],
            ['OK', 'this is a response', null, false], // null line ending
            ['OK', "this is a response\nOK\n", "\n", true, ['OK'], 'this is a response'],
            ['(ERROR) ([0-9]{1,3})', "party\r\nERROR 123\r\n", "\r\n", true, ['ERROR 123', 'ERROR', '123'], 'party']
        ];
    }
}
