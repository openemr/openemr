<?php
declare(strict_types=1);

namespace Aranyasen\HL7\Tests;

use Aranyasen\Exceptions\HL7ConnectionException;
use Aranyasen\Exceptions\HL7Exception;
use Aranyasen\HL7\Message;
use Aranyasen\HL7\Connection;
use RuntimeException;

class ConnectionTest extends TestCase
{
    use Hl7ListenerTrait;

    protected $port = 12011;

    protected function tearDown(): void
    {
        $this->deletePipe();
        parent::tearDown();
    }

    /**
     * @test
     * @throws HL7ConnectionException
     * @throws HL7Exception
     * @throws \ReflectionException
     */
    public function a_message_can_be_sent_to_a_hl7_server(): void
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new RuntimeException('Could not fork');
        }
        if (!$pid) { // In child process
            $this->createTcpServer($this->port, 1);
        }
        if ($pid) { // in Parent process...
            sleep(2); // Give a second to server (child) to start up. TODO: Speed up by polling

            $connection = new Connection('localhost', $this->port);
            $msg = new Message("MSH|^~\\&|1|\rPV1|1|O|^AAAA1^^^BB|", null, true, true);
            $ack = $connection->send($msg);
            self::assertInstanceOf(Message::class, $ack);
            self::assertSame('MSH|^~\&|1||||||ACK|\nMSA|AA|\n|\n', $ack->toString());

            self::assertStringContainsString("MSH|^~\\&|1|\nPV1|1|O|^AAAA1^^^BB|", $this->getWhatServerGot());

            $this->closeTcpSocket($connection->getSocket()); // Clean up listener
            pcntl_wait($status); // Wait till child is closed
        }
    }

    /**
     * @test
     * @throws HL7ConnectionException
     * @throws HL7Exception
     * @throws \ReflectionException
     */
    public function do_not_wait_for_ack_after_sending_if_corresponding_parameter_is_set(): void
    {
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new RuntimeException('Could not fork');
        }
        if (!$pid) { // In child process
            $this->createTcpServer($this->port, 1);
        }
        if ($pid) { // in Parent process...
            sleep(2); // Give a second to server (child) to start up

            $connection = new Connection('localhost', $this->port);
            $msg = new Message("MSH|^~\\&|1|\rPV1|1|O|^AAAA1^^^BB|", null, true, true);
            self::assertNull($connection->send($msg,' UTF-8', true));

            $this->closeTcpSocket($connection->getSocket()); // Clean up listener
            pcntl_wait($status); // Wait till child is closed
        }
    }
}
