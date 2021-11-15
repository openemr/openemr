<?php

namespace Aranyasen\HL7\Tests;

use Aranyasen\Exceptions\HL7Exception;
use Aranyasen\HL7\Message;
use Aranyasen\HL7\Messages\ACK;

/**
 * Trait Hl7ListenerTrait
 *
 * Create a TCP socket server to receive HL7 messages. It responds to HL7 messages with an ACK
 * It also creates a pipe so client can get back exactly what it sent. Useful for testing...
 * To close the server, send "\n" or "shutdown\n"
 * @package Aranyasen\HL7\Tests
 */
trait Hl7ListenerTrait
{
    private $pipeName = "pipe1";

    // As per MLLP protocol, the sender prefixes and suffixes the HL7 message with certain codes. If these need to be
    // overwritten, simply declare these after the 'use Hl7ListenerTrait' statement in the calling class
    protected $MESSAGE_PREFIX = "\013";
    protected $MESSAGE_SUFFIX = "\034\015";

    public function writeToPipe(string $value): void
    {
        $pipe = fopen($this->pipeName,'wb');
        fwrite($pipe, $value);
    }

    public function readFromPipe(): string
    {
        $pipe = fopen($this->pipeName,'rb');
        return fread($pipe, 1024);
    }

    public function getWhatServerGot(): string
    {
        return $this->readFromPipe();
    }

    /**
     * @param int $port
     * @param int $totalClientsToConnect How many clients are expected to connect to this server, once it's up
     * @throws HL7Exception
     * @throws \ReflectionException
     */
    public function createTcpServer(int $port, int $totalClientsToConnect): void
    {
        if (($socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
            throw new \RuntimeException('socket_create() failed: reason: ' . socket_strerror(socket_last_error()) . "\n");
        }

        // This is to avoid "address already in use" error while doing ->bind()
        if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            echo socket_strerror(socket_last_error($socket));
            exit(-1);
        }

        if (($ret = socket_bind($socket, "localhost", $port)) === false) {
            throw new \RuntimeException('socket_bind() failed: reason: ' . socket_strerror($ret) . "\n");
        }
        if (($ret = socket_listen($socket, 5)) === false) {
            throw new \RuntimeException('socket_listen() failed: reason: ' . socket_strerror($ret) . "\n");
        }

        $clientCount = 0;
        while (true) { // Loop over each client
            if (($clientSocket = socket_accept($socket)) === false) {
                echo 'socket_accept() failed: reason: ' . socket_strerror(socket_last_error()) . "\n";
                socket_close($clientSocket);
                exit();
            }
            if ($clientSocket === false) {
                continue;
            }

            $clientCount++;
            $clientName = 'Unknown';
            socket_getpeername($clientSocket, $clientName);
            // echo "Client {$clientCount} ({$clientName}) connected\n"; // Uncomment to debug

            while (true) { // Keep reading a given client until they send "shutdown" or an empty string
                $buffer = socket_read($clientSocket, 1024); // Keeps reading until bytes exhaust, /n or /r
                if (false === $buffer) {
                    break;
                }
                // echo "\n--- From client: '$buffer' ---\n\n"; // Uncomment to debug
                if (!$buffer || empty(trim($buffer)) || false !== stripos($buffer, 'shutdown')) {
                    break;
                }

                $ackString = $this->getAckString($buffer);
                $message = $this->MESSAGE_PREFIX . $ackString . $this->MESSAGE_SUFFIX;
                socket_write($clientSocket, $message, strlen($message));

                // Also write to a pipe/msg queue for client to get the actual message
                $this->writeToPipe($buffer);
            }

            socket_shutdown($clientSocket);
            socket_close($clientSocket);

            if ($totalClientsToConnect > 0 && $clientCount >= $totalClientsToConnect) {
                break;
            }
        }
        socket_close($socket);
        exit(0); // Child process needs it
    }

    /**
     * @param $socket
     */
    public function closeTcpSocket($socket): void
    {
        $msg = "\n"; // Or send "shutdown\n"
        socket_write($socket, $msg, strlen($msg)); // Tell the client to shutdown
    }

    /**
     * @param string $hl7
     * @return string ACK string
     * @throws HL7Exception
     * @throws \ReflectionException
     */
    private function getAckString(string $hl7): string
    {
        // Remove message prefix and suffix
        $hl7 = preg_replace('/^' . $this->MESSAGE_PREFIX . '/', '', $hl7);
        $hl7 = preg_replace('/' . $this->MESSAGE_SUFFIX . '$/', '', $hl7);

        $msg = new Message(trim($hl7), null, true, true);
        $ack = new ACK($msg);
        return $ack->toString();
    }

    /**
     * Clean up temporary pipe file generated for testing
     */
    private function deletePipe()
    {
        if (file_exists($this->pipeName)) {
            unlink($this->pipeName);
        }
    }
}
