<?php

namespace Graze\TelnetClient;

interface PromptMatcherInterface
{
    /**
     * @param string $prompt
     * @param string $subject
     * @param string $lineEnding
     * @return bool
     */
    public function isMatch($prompt, $subject, $lineEnding = null);

    /**
     * @return string[]
     */
    public function getMatches();

    /**
     * @return string
     */
    public function getResponseText();
}
