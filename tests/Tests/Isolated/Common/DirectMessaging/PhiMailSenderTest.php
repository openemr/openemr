<?php

/**
 * Isolated tests for sending a Direct message, against a scripted server on a socket pair.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\DirectMessaging\DirectMessageLogRepository;
use OpenEMR\Common\DirectMessaging\PhiMailAttachmentType;
use OpenEMR\Common\DirectMessaging\PhiMailConfig;
use OpenEMR\Common\DirectMessaging\PhiMailConnection;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionException;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionFactory;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionFailure;
use OpenEMR\Common\DirectMessaging\PhiMailOutgoingAttachment;
use OpenEMR\Common\DirectMessaging\PhiMailOutgoingMessage;
use OpenEMR\Common\DirectMessaging\PhiMailRequester;
use OpenEMR\Common\DirectMessaging\PhiMailSender;
use OpenEMR\Common\DirectMessaging\PhiMailSendResult;
use OpenEMR\Common\DirectMessaging\PhiMailServer;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

#[BackupGlobals(true)]
final class PhiMailSenderTest extends TestCase
{
    private const LF = "\n";
    private const CONFIG_ERROR = 'Direct messaging is currently unavailable. EC:';

    /** @var resource the server's end of the socket pair */
    private $server;

    private PhiMailConnection $connection;

    /** @var list<array{string, ?string, ?string, int, string, ?int}> event, user, group, success flag, comments, and patient of each audit row */
    private array $auditRows = [];

    /** Thrown by the audit log in place of writing, when set */
    private ?\RuntimeException $auditFailure = null;

    private DirectMessageLogRepository&MockObject $messageLog;

    protected function setUp(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', true);

        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pair === false) {
            // @codeCoverageIgnoreStart
            // Defensive — socket pairs are always available on the platforms CI runs.
            self::fail('could not create a socket pair');
            // @codeCoverageIgnoreEnd
        }
        [$client, $this->server] = $pair;
        // Fail a test that leaves the connection open instead of hanging it.
        stream_set_timeout($this->server, 5);
        $this->connection = new PhiMailConnection($client);

        $this->messageLog = $this->createMock(DirectMessageLogRepository::class);
    }

    /**
     * Sends a message and logs it with no patient or referral.
     */
    public function testSendsAMessage(): void
    {
        $this->reply(self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'OK', 'QUEUED to@direct.example msg-1'));
        $this->messageLog->expects(self::once())->method('recordSent')->with('msg-1', 'test-user', 'to@direct.example', null, 3, null);

        $result = $this->send(new PhiMailOutgoingMessage('to@direct.example', 'hello'));

        self::assertSame(PhiMailSender::SUCCESS, $result);
        self::assertSame(
            self::lines('AUTH test-user test-password', 'TO to@direct.example', 'TEXT 5') . 'hello' . self::lines('SET FINAL 0', 'SEND', 'OK'),
            $this->sentCommands(),
        );
        self::assertSame(
            [['sent', 'sender', 'Default', 1, 'QUEUED to@direct.example msg-1' . self::LF, null]],
            $this->auditRows,
        );
    }

    /**
     * Sends a document for a patient.
     */
    public function testSendsADocumentForAPatient(): void
    {
        $this->reply(self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'BEGIN', 'OK', 'OK', 'QUEUED to@direct.example msg-2'));
        $this->messageLog->expects(self::once())->method('recordSent')->with('msg-2', 'test-user', 'to@direct.example', 42, 3, null);

        $result = $this->send(new PhiMailOutgoingMessage(
            recipient: 'to@direct.example',
            text: 'see attached',
            attachment: new PhiMailOutgoingAttachment(PhiMailAttachmentType::Cda, 'CCD_Doe_Jane.xml', '<xml/>'),
            verifyFinalDelivery: true,
            patientId: 42,
        ));

        self::assertSame(PhiMailSender::SUCCESS, $result);
        self::assertSame(
            self::lines('AUTH test-user test-password', 'TO to@direct.example', 'TEXT 12') . 'see attached'
                . self::lines('ADD CDA 6 CCD_Doe_Jane.xml') . '<xml/>' . self::lines('SET FINAL 1', 'SEND', 'OK'),
            $this->sentCommands(),
        );
        self::assertSame([['sent', 'sender', 'Default', 1, 'QUEUED to@direct.example msg-2' . self::LF, 42]], $this->auditRows);
    }

    /**
     * Logs the referral a message carries and gives its id.
     */
    public function testLogsTheReferralAMessageCarriesAndGivesItsId(): void
    {
        $this->reply(self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'OK', 'QUEUED to@direct.example msg-3'));
        $this->messageLog->expects(self::once())->method('recordSent')->with('msg-3', 'test-user', 'to@direct.example', 42, 3, 7);

        $result = $this->sender()->deliver(
            new PhiMailOutgoingMessage(recipient: 'to@direct.example', text: 'hello', patientId: 42, referralId: 7),
            new PhiMailRequester('sender', 'Default', 3),
            failureEvent: 'failed',
            successEvent: 'sent',
        );

        self::assertTrue($result->wasSent());
        self::assertSame('msg-3', $result->messageId);
        self::assertSame(PhiMailSender::SUCCESS, $result->message);
    }

    /**
     * Once the server queued a message it has gone: a log that then fails makes it sent but unlogged,
     * not an error, so nobody sends it again.
     */
    public function testAQueuedMessageTheLogRefusesIsSentButUnlogged(): void
    {
        $this->reply(self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'OK', 'QUEUED to@direct.example msg-4'));
        $this->messageLog->method('recordSent')->willThrowException(new SqlQueryException(sqlStatement: 'INSERT INTO direct_message_log', message: 'database is down'));

        $result = $this->deliver(new PhiMailOutgoingMessage('to@direct.example', 'hello'));

        self::assertSame(['msg-4', PhiMailSender::SUCCESS, false], [$result->messageId, $result->message, $result->logged]);
    }

    /**
     * The same when it's the success audit, written before the log, that fails.
     */
    public function testAQueuedMessageTheAuditRefusesIsSentButUnlogged(): void
    {
        $this->reply(self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'OK', 'QUEUED to@direct.example msg-5'));
        $this->auditFailure = new \RuntimeException('audit table is gone');
        $this->messageLog->expects(self::never())->method('recordSent');

        $result = $this->deliver(new PhiMailOutgoingMessage('to@direct.example', 'hello'));

        self::assertSame(['msg-5', PhiMailSender::SUCCESS, false], [$result->messageId, $result->message, $result->logged]);
    }

    /**
     * An unsent message has no id and says why.
     */
    public function testAnUnsentMessageHasNoIdAndSaysWhy(): void
    {
        $connections = $this->createMock(PhiMailConnectionFactory::class);
        $connections->expects(self::never())->method('open');

        $result = $this->sender($connections, enabled: false)->deliver(
            new PhiMailOutgoingMessage('to@direct.example', 'hello'),
            new PhiMailRequester('sender', 'Default', 3),
            failureEvent: 'failed',
            successEvent: 'sent',
        );

        self::assertFalse($result->wasSent());
        self::assertNull($result->messageId);
        self::assertSame(self::CONFIG_ERROR . ' 1', $result->message);
    }

    public function testDisabledMessagingDoesNotConnect(): void
    {
        $connections = $this->createMock(PhiMailConnectionFactory::class);
        $connections->expects(self::never())->method('open');

        $result = $this->send(new PhiMailOutgoingMessage('to@direct.example', 'hello'), $this->sender($connections, enabled: false));

        self::assertSame(self::CONFIG_ERROR . ' 1', $result);
        self::assertSame([], $this->auditRows);
    }

    public function testConnectFailureReportsItsCode(): void
    {
        $connections = $this->createMock(PhiMailConnectionFactory::class);
        $connections->method('open')->willThrowException(
            new PhiMailConnectionException(PhiMailConnectionFailure::Unreachable, 111, 'Connection refused'),
        );

        $result = $this->send(new PhiMailOutgoingMessage('to@direct.example', 'hello'), $this->sender($connections));

        self::assertSame(self::CONFIG_ERROR . ' C4 111 (Server may be offline)', $result);
        self::assertSame([], $this->auditRows);
    }

    public function testBuildsFromGlobals(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $globals->set('fileroot', dirname(__DIR__, 5));
        $globals->set('phimail_enable', true);
        $globals->set('phimail_server_address', 'tcp://127.0.0.1:1');
        $crypto = $this->createStub(CryptoInterface::class);
        $crypto->method('decryptFromDatabase')->willReturn('test-password');

        $sender = PhiMailSender::fromGlobals($globals, $crypto, new NullLogger(), $this->createStub(EventAuditLogger::class));
        $result = $this->send(new PhiMailOutgoingMessage('to@direct.example', 'hello'), $sender);

        // Nothing listens on port 1, so the configured server refuses the connection.
        self::assertStringStartsWith(self::CONFIG_ERROR . ' C4 ', $result);
    }

    public function testServerErrorIsAuditedAsAFailure(): void
    {
        $this->reply(self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'OK', 'ERROR message rejected'));
        $this->messageLog->expects(self::never())->method('recordSent');

        $result = $this->send(new PhiMailOutgoingMessage('to@direct.example', 'hello'));

        self::assertSame('The message could not be sent at this time.', $result);
        self::assertSame([['failed', 'sender', 'Default', 0, 'ERROR message rejected' . self::LF, null]], $this->auditRows);
        $this->sentCommands();
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unexpectedReplyProvider(): array
    {
        return [
            'unknown reply' => ['BOGUS' . self::LF, 'UNEXPECTED RESPONSE: BOGUS' . self::LF],
            'QUEUED without a message ID' => ['QUEUED to@direct.example' . self::LF, 'UNEXPECTED RESPONSE: QUEUED to@direct.example' . self::LF],
            'connection closed' => ['', 'UNEXPECTED RESPONSE: '],
        ];
    }

    #[DataProvider('unexpectedReplyProvider')]
    public function testUnexpectedReplyIsAuditedAsAFailure(string $reply, string $auditText): void
    {
        $this->reply(self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'OK') . $reply);
        stream_socket_shutdown($this->server, STREAM_SHUT_WR);
        $this->messageLog->expects(self::never())->method('recordSent');

        $result = $this->send(new PhiMailOutgoingMessage('to@direct.example', 'hello'));

        self::assertSame('There was a problem sending the message.', $result);
        self::assertSame([['failed', 'sender', 'Default', 0, $auditText, null]], $this->auditRows);
        $this->sentCommands();
    }

    /**
     * Server replies that stop the send before SEND, and what the user sees.
     *
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedStepProvider(): array
    {
        return [
            'AUTH refused' => [self::lines('FAIL bad password'), self::CONFIG_ERROR . ' 4'],
            'AUTH unanswered' => ['', self::CONFIG_ERROR . ' 4'],
            'TO refused' => [self::lines('OK', 'FAIL not allowed'), 'Delivery is not allowed to the specified Direct Address: FAIL not allowed' . self::LF],
            'TO unanswered' => [self::lines('OK'), 'Delivery is not allowed to the specified Direct Address: '],
            'closed after TO' => [self::lines('OK', 'OK'), self::CONFIG_ERROR . ' 5'],
            'TEXT refused' => [self::lines('OK', 'OK', 'recipient info', 'FAIL'), self::CONFIG_ERROR . ' 5'],
            'TEXT unanswered' => [self::lines('OK', 'OK', 'recipient info'), self::CONFIG_ERROR . ' 5'],
            'text refused' => [self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'FAIL'), self::CONFIG_ERROR . ' 6'],
            'ADD refused' => [self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'FAIL'), self::CONFIG_ERROR . ' 7'],
            'attachment refused' => [self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'BEGIN', 'FAIL'), self::CONFIG_ERROR . ' 8'],
            'SET FINAL refused' => [
                self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'BEGIN', 'OK', 'FAIL unsupported'),
                'There was a problem in setting the server flag for receiving a message received disposition notification FAIL unsupported' . self::LF,
            ],
        ];
    }

    #[DataProvider('refusedStepProvider')]
    public function testRefusedStepStopsTheSend(string $replies, string $expected): void
    {
        $this->reply($replies);
        stream_socket_shutdown($this->server, STREAM_SHUT_WR);
        $this->messageLog->expects(self::never())->method('recordSent');

        $result = $this->send(new PhiMailOutgoingMessage(
            recipient: 'to@direct.example',
            text: 'hello',
            attachment: new PhiMailOutgoingAttachment(PhiMailAttachmentType::Raw, 'doc.pdf', '%PDF'),
        ));

        self::assertSame($expected, $result);
        self::assertSame([], $this->auditRows);
        self::assertStringNotContainsString('SEND' . self::LF, $this->sentCommands());
    }

    /**
     * @return array<string, array{string, string, string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function lineBreakProvider(): array
    {
        return [
            'username' => ["test-user\nSEND", 'test-password', 'to@direct.example', 'doc.pdf', self::CONFIG_ERROR . ' 4'],
            'password' => ['test-user', "test-password\r", 'to@direct.example', 'doc.pdf', self::CONFIG_ERROR . ' 4'],
            'recipient' => ['test-user', 'test-password', "to@direct.example\nSEND", 'doc.pdf', 'Delivery is not allowed to the specified Direct Address:'],
            'attachment name' => ['test-user', 'test-password', 'to@direct.example', "doc.pdf\nSEND", self::CONFIG_ERROR . ' 7'],
        ];
    }

    #[DataProvider('lineBreakProvider')]
    public function testLineBreakInACommandArgumentRefusesTheSend(
        string $username,
        string $password,
        string $recipient,
        string $fileName,
        string $expected,
    ): void {
        $connections = $this->createMock(PhiMailConnectionFactory::class);
        $connections->expects(self::never())->method('open');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with('phiMail send refused a command argument with a line break');

        $result = $this->send(
            new PhiMailOutgoingMessage($recipient, 'hello', new PhiMailOutgoingAttachment(PhiMailAttachmentType::Raw, $fileName, '%PDF')),
            $this->sender($connections, username: $username, password: $password, logger: $logger),
        );

        self::assertSame($expected, $result);
        self::assertSame([], $this->auditRows);
    }

    public function testDroppedConnectionIsLogged(): void
    {
        stream_socket_shutdown($this->server, STREAM_SHUT_WR);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with('phiMail connection failed during a send', self::arrayHasKey('exception'));

        $result = $this->send(new PhiMailOutgoingMessage('to@direct.example', 'hello'), $this->sender(logger: $logger));

        self::assertSame(self::CONFIG_ERROR . ' 4', $result);
        $this->sentCommands();
    }

    /**
     * A lost acknowledgment keeps the queued message.
     */
    public function testLostAcknowledgmentKeepsTheQueuedMessage(): void
    {
        HangUpAfterSendStream::$replies = self::lines('OK', 'OK', 'recipient info', 'BEGIN', 'OK', 'OK', 'QUEUED to@direct.example msg-3');
        HangUpAfterSendStream::$sent = '';
        stream_wrapper_register(HangUpAfterSendStream::PROTOCOL, HangUpAfterSendStream::class);
        try {
            $stream = fopen(HangUpAfterSendStream::PROTOCOL . '://server', 'r+');
            self::assertIsResource($stream);
            $this->connection = new PhiMailConnection($stream);
            $this->messageLog->expects(self::once())->method('recordSent')->with('msg-3', 'test-user', 'to@direct.example', null, 3);

            $result = $this->send(new PhiMailOutgoingMessage('to@direct.example', 'hello'));
        } finally {
            stream_wrapper_unregister(HangUpAfterSendStream::PROTOCOL);
        }

        self::assertSame(PhiMailSender::SUCCESS, $result);
        self::assertStringEndsWith('SEND' . self::LF . 'OK' . self::LF, HangUpAfterSendStream::$sent);
        self::assertSame([['sent', 'sender', 'Default', 1, 'QUEUED to@direct.example msg-3' . self::LF, null]], $this->auditRows);
    }

    /**
     * deliver() as the referral notice calls it.
     */
    private function deliver(PhiMailOutgoingMessage $message): PhiMailSendResult
    {
        return $this->sender()->deliver($message, new PhiMailRequester('sender', 'Default', 3), failureEvent: 'failed', successEvent: 'sent');
    }

    private function send(PhiMailOutgoingMessage $message, ?PhiMailSender $sender = null): string
    {
        return ($sender ?? $this->sender())->send(
            $message,
            new PhiMailRequester('sender', 'Default', 3),
            failureEvent: 'failed',
            successEvent: 'sent',
        );
    }

    /**
     * A sender on the test connection, recording what it audits.
     */
    private function sender(
        ?PhiMailConnectionFactory $connections = null,
        bool $enabled = true,
        string $username = 'test-user',
        string $password = 'test-password',
        LoggerInterface $logger = new NullLogger(),
    ): PhiMailSender
    {
        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger->method('newEvent')->willReturnCallback(
            function (string $event, ?string $user, ?string $group, int $success, string $comments, ?int $patientId): void {
                if ($this->auditFailure !== null) {
                    throw $this->auditFailure;
                }
                $this->auditRows[] = [$event, $user, $group, $success, $comments, $patientId];
            }
        );
        $connection = $this->connection;

        return new PhiMailSender(
            config: new PhiMailConfig(new PhiMailServer($enabled, 'tls://phimail.example:32541', true, null), $username, $password, 'admin'),
            connections: $connections ?? new class ($connection) implements PhiMailConnectionFactory {
                public function __construct(private readonly PhiMailConnection $connection)
                {
                }

                public function open(): PhiMailConnection
                {
                    return $this->connection;
                }
            },
            messageLog: $this->messageLog,
            auditLogger: $auditLogger,
            logger: $logger,
        );
    }

    private function reply(string $data): void
    {
        fwrite($this->server, $data);
    }

    /**
     * Everything the client sent, once it has closed the connection.
     */
    private function sentCommands(): string
    {
        $sent = stream_get_contents($this->server);
        self::assertIsString($sent);
        self::assertTrue(feof($this->server), 'the client left the connection open');
        return $sent;
    }

    private static function lines(string ...$lines): string
    {
        return implode(self::LF, $lines) . self::LF;
    }
}
