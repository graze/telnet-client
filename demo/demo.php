<?php

require __DIR__ . '/../vendor/autoload.php';


$dsn = '192.168.50.59:23';
$client = Graze\TelnetClient\TelnetClient::factory();
$client->connect($dsn);

// send something ("\r\n") to the server to trigger its initial response. Look for "login: " in the response
$resp = $client->execute("\r\n", "login: ");

// send username with line ending appended as it is no longer included. Look for "password: " prompt
$resp = $client->execute("erick\r\n", "password: ");

// send password with line ending. Look for welcome banner
$welcomePrompt = "*======="; //... full string or regex
$resp = $client->execute("my password\r\n", $welcomePrompt);
// ...
