<?php

declare(strict_types=1);

namespace Aranyasen\HL7;

use Aranyasen\Exceptions\HL7ConnectionException;
use Aranyasen\Exceptions\HL7Exception;
use Exception;
use ReflectionException;

/**
 * Usage:
 * ```php
 * $connection = new Connection('127.0.0.1', 5002);
 * $req = new Message();
 * // ... set some request attributes
 * $response = $connection->send($req);
 * $response->toString(); // Read ACK message from remote
 * ```
 *
 * The Connection object represents the tcp connection to the HL7 message broker. The Connection has only one public
 * method (apart from the constructor), send(). The 'send' method takes a Message object as argument, and also
 * returns a Message object. The send method can be used more than once, before the connection is closed.
 * Connection is closed automatically when the connection object is destroyed.
 *
 * The Connection object holds the following fields:
 *
 * MESSAGE_PREFIX
 *
 * The prefix to be sent to the HL7 server to initiate the
 * message. Defaults to \013.
 *
 * MESSAGE_SUFFIX
 * End of message signal for HL7 server. Defaults to \034\015.
 *
 */
class Connection
{
    protected $socket;
    protected $timeout;
    protected $MESSAGE_PREFIX;
    protected $MESSAGE_SUFFIX;

    /**
     * Creates a connection to a HL7 server, or throws exception when a connection could not be established.
     *
     * @param string $host Host to connect to
     * @param int $port Port to connect to
     * @param int $timeout Connection timeout
     * @throws HL7ConnectionException
     */
    public function __construct(string $host, int $port, int $timeout = 10)
    {
        if (!extension_loaded('sockets')) {
            throw new HL7ConnectionException('Please install ext-sockets to run Connection');
        }
        $this->setSocket($host, $port, $timeout);
        $this->MESSAGE_PREFIX = "\013";
        $this->MESSAGE_SUFFIX = "\034\015";
        $this->timeout = $timeout;
    }

    /**
     * Create a client-side TCP socket
     *
     * @param string $host
     * @param int $port
     * @param int $timeout Connection timeout
     * @throws HL7ConnectionException
     */
    protected function setSocket(string $host, int $port, int $timeout = 10): void
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket) {
            $this->throwSocketError('Failed to create socket');
        }

        if (!socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => $timeout, 'usec' => 0])) {
            $this->throwSocketError('Unable to set timeout on socket');
        }

        if (!socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0])) {
            $this->throwSocketError('Unable to set timeout on socket');
        }

        if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            $this->throwSocketError('Unable to set reuse-address on socket');
        }

        // Uncomment this if server requires a certain client-side port to be used
        // if (!socket_bind($socket, "0.0.0.0", $localPort)) {
        //     $this->throwSocketError('Unable to bind socket');
        // }

        $result = null;
        try {
            $result = socket_connect($socket, $host, $port);
        } catch (Exception $exception) {
            $this->throwSocketError("Failed to connect to server ($host:$port)");
        }
        if (!$result) {
            $this->throwSocketError("Failed to connect to server ($host:$port)");
        }

        $this->socket = $socket;
    }

    /**
     * @param string $message
     * @throws HL7ConnectionException
     */
    protected function throwSocketError(string $message): void
    {
        throw new HL7ConnectionException($message . ': ' . socket_strerror(socket_last_error()));
    }

    /**
     * Sends a Message object over this connection.
     *
     * @param Message $msg
     * @param string $responseCharEncoding The expected character encoding of the response.
     * @param bool $noWait Do no wait for ACK. Helpful for building load testing tools...
     * @return Message|null
     * @throws HL7ConnectionException
     * @throws HL7Exception
     * @throws ReflectionException
     * @access public
     */
    public function send(Message $msg, string $responseCharEncoding = 'UTF-8', bool $noWait = false): ?Message
    {
        $message = $this->MESSAGE_PREFIX . $msg->toString(true) . $this->MESSAGE_SUFFIX; // As per MLLP protocol
        if (!socket_write($this->socket, $message, strlen($message))) {
            throw new HL7Exception("Could not send data to server: " . socket_strerror(socket_last_error()));
        }

        if ($noWait) {
            return null;
        }

        $data = null;

        $startTime = time();
        while (($buf = socket_read($this->socket, 1024)) !== false) { // Read ACK / NACK from server
            $data .= $buf;
            if (preg_match('/' . $this->MESSAGE_SUFFIX . '$/', $data)) {
                break;
            }
            if ((time() - $startTime) > $this->timeout) {
                throw new HL7ConnectionException("Response partially received. Timed out listening for end-of-message from server");
            }
        }

        if (empty($data)) {
            throw new HL7ConnectionException("No response received within {$this->timeout} seconds");
        }

        // Remove message prefix and suffix added by the MLLP server
        $data = preg_replace('/^' . $this->MESSAGE_PREFIX . '/', '', $data);
        $data = preg_replace('/' . $this->MESSAGE_SUFFIX . '$/', '', $data);

        // set character encoding
        $data = mb_convert_encoding($data, $responseCharEncoding);

        return new Message($data, null, true, true);
    }

    /*
     * Return the socket opened/used by this class
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * Close the socket
     * TODO: Close only when the socket is open
     */
    private function close(): void
    {
        try {
            socket_close($this->socket);
        }
        catch (Exception $e) {
            echo 'Failed to close socket: ' . socket_strerror(socket_last_error()) . PHP_EOL;
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
