# telnet-client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/graze/telnet-client.svg?style=flat-square)](https://packagist.org/packages/graze/telnet-client)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/graze/telnet-client/master.svg?style=flat-square)](https://travis-ci.org/graze/telnet-client)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/graze/telnet-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/telnet-client/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/graze/telnet-client.svg?style=flat-square)](https://scrutinizer-ci.com/g/graze/telnet-client)
[![Total Downloads](https://img.shields.io/packagist/dt/graze/telnet-client.svg?style=flat-square)](https://packagist.org/packages/graze/telnet-client)

A telnet client written in PHP

## Install

Via Composer

``` bash
composer require graze/telnet-client
```

## Usage

### Instantiating a client

Use the `factory` method to return a `TelnetClientInterface` instance:

``` php
$client = Graze\TelnetClient\TelnetClient::factory();
```

### Issuing commands

Connect to the remote endpoint using the `connect` method:

``` php
$dsn = '127.0.0.1:23';
$client->connect($dsn);
```

Once connected, the `execute` method can be used to write `$command` to the socket:

``` php
$command = 'Open the pod bay doors, HAL';
$resp = $client->execute($command);
```

### Responses

Once a command has been sent, the socket is read until a specific sequence is encountered. This is a line ending immediately preceeded by either a prompt or an error prompt.
At this point the `execute` method returns a `TelnetResponseInterface` object:

```php
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
```

A success response object might look like:

``` php
Graze\TelnetClient\TelnetResponse {#2
  #isError: false
  #responseText: "Affirmative, Dave"
  #promptMatches: array:1 [
    0 => "$"
  ]
}
```

Or if the server responded with an error:

``` php
Graze\TelnetClient\TelnetResponse {#2
  #isError: true
  #responseText: " I'm sorry, Dave. I'm afraid I can't do that"
  #promptMatches: array:1 [
    0 => "ERROR"
  ]
}
```

**Note:** `responseText` and `promptMatches` are trimmed of line endings.

### Client configuration

The client uses the following defaults:

* standard prompt `$`
* error prompt `ERROR`
* line endings `\n`

Custom configuration can be passed to the `connect` method like so:

``` php
$dsn = '127.0.0.1:23';
$prompt = 'OK';
$promptError = 'ERR';
$lineEnding = "\r\n";
$client->connect($dsn, $prompt, $promptError, $lineEnding);
```

The client's global `$prompt` can be temporarily overridden on a per-execute basis:

``` php
$command = 'login';
$prompt = 'Username:';
$resp = $client->execute($command, $prompt);
```

### Complex prompts

Some operations may respond with a more complex prompt. These instances can be handled by using a [regular expression](http://www.regular-expressions.info) to match the prompt.
For instance, a server may respond with `ERROR n` (where n is an integer) when an error condition is encountered. The client could be configured as such:

``` php
$dsn = '127.0.0.1:23';
$promptError = 'ERROR [0-9]';
$client->connect($dsn, null, $promptError);
```

An error response would look like:

``` php
Graze\TelnetClient\TelnetResponse {#2
  #isError: true
  #responseText: "unknown command"
  #promptMatches: array:1 [
    0 => "ERROR 6"
  ]
}
```

We can take the regex one further by using a [named capturing group](http://www.regular-expressions.info/named.html), this makes the error code easily available to us in the `$promptMatches` array.

```php
$dsn = '127.0.0.1:23';
$promptError = 'ERROR (?<errorNum>[0-9])';
$client->connect($dsn, null, $promptError);
```

which gives us:

``` php
Graze\TelnetClient\TelnetResponse {#2
  #isError: true
  #responseText: "unknown command"
  #promptMatches: array:3 [
    0 => "ERROR 6",
    "errorNum" => "6",
    1 => "6"
  ]
}
```

**Note:** it's important to escape any characters in your regex that may have special meaning when interpreted by [preg_match](http://php.net/manual/en/function.preg-match.php).

### Socket settings

For timeouts and more, PHP's `socket_set_option` is exposed via

```php
$client->getSocket()->setOption();
```

See [clue/php-socket-raw](https://github.com/clue/php-socket-raw) and [socket_set_option](http://php.net/manual/en/function.socket-set-option.php) for more info.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
make test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@graze.com instead of using the issue tracker.

## Inspired by

Based on [bestnetwork/Telnet](https://github.com/bestnetwork/Telnet).

## Credits

* [John Smith](https://github.com/john-n-smith)
* Bestnetwork <reparto.sviluppo@bestnetwork.it>
* Dalibor Andzakovic <dali@swerve.co.nz>
* Marc Ennaji
* Matthias Blaser <mb@adfinis.ch>
* Christian Hammers <chammers@netcologne.de>
* [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
