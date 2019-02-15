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

use Graze\TelnetClient\Exception\TelnetException;
use Graze\TelnetClient\Exception\TelnetExceptionInterface;
use Graze\TelnetClient\Reader\Reader;
use Graze\TelnetClient\Reader\ReaderInterface;
use Graze\TelnetClient\TelnetResponse;
use Graze\TelnetClient\TelnetResponseInterface;
use Graze\TelnetClient\TelnetClientInterface;
use Socket\Raw\Factory as SocketFactory;
use Socket\Raw\Socket;

class TelnetClient implements TelnetClientInterface
{
    /**
     * @var SocketFactory
     */
    private $socketFactory;

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var Socket
     */
    private $socket;

    /**
     * @param SocketFactory $socketFactory
     * @param ReaderInterface $reader
     */
    public function __construct(
        SocketFactory $socketFactory,
        ReaderInterface $reader
    ) {
        $this->socketFactory = $socketFactory;
        $this->reader = $reader;
    }

    /**
     * @param string $dsn
     * @param float|null $timeout
     * @throws TelnetExceptionInterface
     */
    public function connect($dsn, $timeout = null)
    {
        try {
            $this->setSocket($this->socketFactory->createClient($dsn, $timeout));
        } catch (Exception $e) {
            throw new TelnetException(sprintf('unable to create socket connection to [%s]', $dsn), 0, $e);
        }
    }

    /**
     * @param Socket $socket
     */
    public function setSocket(Socket $socket)
    {
        $this->socket = $socket;
    }

    /**
     * @return Socket
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param string $command
     * @param string $expected
     * @param string $expectedError
     * @return TelnetResponseInterface
     */
    public function execute($command, $expected = null, $expectedError = null)
    {
        if (!$this->socket) {
            throw new TelnetException('Attempt to execute without a connection - was connect() called?');
        }

        $this->write($command);
        return $this->reader->read($this->socket, $expected, $expectedError);
    }

    /**
     * @param string $command
     * @return void
     * @throws TelnetExceptionInterface
     */
    private function write($command)
    {
        try {
            $this->socket->write($command);
        } catch (Exception $e) {
            throw new TelnetException(sprintf('Failed writing to socket [%s]', $command), 0, $e);
        }
    }

    /**
     * @return TelnetClientInterface
     */
    public static function factory()
    {
        return new static(
            new SocketFactory(),
            Reader::factory()
        );
    }

    public function __destruct()
    {
        if (is_null($this->socket)) {
            return;
        }

        $this->socket->close();
    }
}
