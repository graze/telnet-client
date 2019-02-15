<?php

namespace Graze\TelnetClient\Test;

use Socket\Raw\Socket;

class SocketMock extends Socket
{
    /**
     * @var array
     */
    private $commandToResponse;

    /**
     * @var string
     */
    private $response;

    public function __construct(array $commandToResponse)
    {
        $this->commandToResponse = $commandToResponse;
    }

    public function write($command)
    {
        if (!isset($this->commandToResponse[$command])) {
            throw new \Exception(sprintf('Command does not exist [%s]', $command));
        }

        $this->response = $this->commandToResponse[$command];
    }

    public function read($length, $type = PHP_BINARY_READ)
    {
        if (is_null($this->response)) {
            throw new \Exception('Attempt to read from empty response. Was read() called before write()?');
        }

        $string = substr($this->response, 0, 1);
        $this->response = substr($this->response, 1);

        return $string;
    }

    public function close()
    {
        return $this;
    }
}
