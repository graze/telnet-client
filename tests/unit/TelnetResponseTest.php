<?php

namespace Graze\TelnetClient\Test\Unit;

use Graze\TelnetClient\TelnetResponse;
use PHPUnit_Framework_TestCase;

class TelnetResponseTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $isError = true;
        $responseText = 'this is text';
        $promptMatches = [1, 'two'];

        $response = new TelnetResponse($isError, $responseText, $promptMatches);

        $this->assertEquals($isError, $response->isError());
        $this->assertEquals($responseText, $response->getResponseText());
        $this->assertEquals($promptMatches, $response->getPromptMatches());
    }
}
