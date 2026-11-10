<?php

/**
 * Isolated tests for the phiMail receive workflow, against a scripted server on a socket pair.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use Doctrine\DBAL\ConnectionException;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\DirectMessaging\DirectMessageLogEntry;
use OpenEMR\Common\DirectMessaging\DirectMessageLogRepository;
use OpenEMR\Common\DirectMessaging\DirectReplyTarget;
use OpenEMR\Common\DirectMessaging\PhiMailAuditLog;
use OpenEMR\Common\DirectMessaging\PhiMailCheckException;
use OpenEMR\Common\DirectMessaging\PhiMailConfig;
use OpenEMR\Common\DirectMessaging\PhiMailConnection;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionException;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionFactory;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionFailure;
use OpenEMR\Common\DirectMessaging\PhiMailDeliveryStatus;
use OpenEMR\Common\DirectMessaging\PhiMailDocumentException;
use OpenEMR\Common\DirectMessaging\PhiMailReceiver;
use OpenEMR\Common\DirectMessaging\PhiMailServer;
use OpenEMR\Common\DirectMessaging\PhiMailUserRepository;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[BackupGlobals(true)]
final class PhiMailReceiverTest extends TestCase
{
    private const LF = "\n";
    private const NOTIFY_USER = 'notify-user';
    private const SERVICE_USER_ID = 7;

    /** @var resource the server's end of the socket pair */
    private $server;

    private PhiMailConnection $connection;

    private string $tempDir;

    /** @var list<array{int, string, ?int}> success flag, comments, and patient of each audit row */
    private array $auditRows = [];

    /** Thrown by the audit logger in place of writing a row, when set */
    private ?\Throwable $auditFailure = null;

    private DirectMessageLogRepository&MockObject $messageLog;

    private PhiMailUserRepository&MockObject $users;

    private RecordingDocumentStore $documents;

    private RecordingNoteWriter $notes;

    private RecordingNotifier $notifier;

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

        $tempDir = sys_get_temp_dir() . '/phimail-receiver-test-' . uniqid('', true);
        (new Filesystem())->mkdir($tempDir);
        // PHP's tempnam() resolves symlinks in the directory, such as macOS's /var.
        $this->tempDir = realpath($tempDir) ?: $tempDir;

        $this->messageLog = $this->createMock(DirectMessageLogRepository::class);
        $this->users = $this->createMock(PhiMailUserRepository::class);
        $this->users->method('serviceUserId')->willReturn(self::SERVICE_USER_ID);
        $this->users->method('findUsername')->willReturnMap([[3, 'sender'], [null, null]]);
        $this->documents = new RecordingDocumentStore();
        $this->notes = new RecordingNoteWriter();
        $this->notifier = new RecordingNotifier();
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tempDir);
    }

    public function testNoneCompletesTheCheck(): void
    {
        $this->reply(self::lines('OK', 'NONE'));

        $this->receiver()->check();

        self::assertSame([[1, 'message check completed', 0]], $this->auditRows);
        self::assertSame(self::lines('AUTH test-user test-password', 'CHECK'), $this->sentCommands());
    }

    /**
     * @return array<string, array{PhiMailConnectionException, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function connectFailureProvider(): array
    {
        return [
            'disabled' => [new PhiMailConnectionException(PhiMailConnectionFailure::Disabled), 'could not connect to server C1'],
            'refused' => [
                new PhiMailConnectionException(PhiMailConnectionFailure::Unreachable, 111, 'Connection refused'),
                'could not connect to server C4 111 (Server may be offline)',
            ],
            'unreachable without a reason' => [
                new PhiMailConnectionException(PhiMailConnectionFailure::Unreachable, 0),
                'could not connect to server C4 0 (Connection error)',
            ],
            'unreachable with a reason' => [
                new PhiMailConnectionException(PhiMailConnectionFailure::Unreachable, 110, 'Connection timed out'),
                'could not connect to server C4 110 (Connection timed out)',
            ],
        ];
    }

    #[DataProvider('connectFailureProvider')]
    public function testConnectFailureFailsAfterAuditing(PhiMailConnectionException $failure, string $reason): void
    {
        $connections = new class ($failure) implements PhiMailConnectionFactory {
            public function __construct(private readonly PhiMailConnectionException $failure)
            {
            }

            public function open(): PhiMailConnection
            {
                throw $this->failure;
            }
        };

        $this->assertCheckFails($reason, $this->receiver($connections));

        self::assertSame([[0, $reason, 0]], $this->auditRows);
    }

    public function testAuthenticationFailureClosesTheConnection(): void
    {
        $this->reply(self::lines('FAIL bad credentials'));

        $this->assertCheckFails('authentication error FAIL bad credentials' . self::LF);

        self::assertSame(self::lines('AUTH test-user test-password'), $this->sentCommands());
    }

    public function testAuthenticationWithoutAReplyFails(): void
    {
        stream_socket_shutdown($this->server, STREAM_SHUT_WR);

        $this->assertCheckFails('authentication error ');
    }

    public function testCheckWithoutAReplyFails(): void
    {
        $this->reply(self::lines('OK'));
        stream_socket_shutdown($this->server, STREAM_SHUT_WR);

        $this->assertCheckFails('M16 unexpected problem checking messages ');
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unexpectedCheckReplyProvider(): array
    {
        return [
            'unknown keyword' => ['BOGUS'],
            'STATUS with an empty code' => ['STATUS msg-1  recipient rejected'],
            'MAIL with an empty sender' => ['MAIL to@direct.example  0 msg-4'],
            'MAIL with an extra field' => ['MAIL to@direct.example from@direct.example 0 msg-4 extra'],
        ];
    }

    #[DataProvider('unexpectedCheckReplyProvider')]
    public function testUnexpectedCheckReplyFails(string $reply): void
    {
        $this->reply(self::lines('OK', $reply));

        $this->assertCheckFails('M16 unexpected problem checking messages ' . $reply . self::LF);
        self::assertSame(self::lines('AUTH test-user test-password', 'CHECK'), $this->sentCommands());
    }

    public function testFailedAuditWriteFailsAnOtherwiseSuccessfulCheck(): void
    {
        $this->reply(self::lines('OK', 'NONE'));
        $auditFailure = new ConnectionException('audit database is down');
        $this->auditFailure = $auditFailure;

        try {
            $this->receiver()->check();
            self::fail('check() hid a failed audit write.');
        } catch (ConnectionException $e) {
            self::assertSame($auditFailure, $e);
        }

        self::assertSame(self::lines('AUTH test-user test-password', 'CHECK'), $this->sentCommands());
    }

    public function testFailedAuditWriteKeepsTheCheckFailure(): void
    {
        $this->reply(self::lines('OK', 'BOGUS'));
        $this->auditFailure = new ConnectionException('audit database is down');

        $this->assertCheckFails('M16 unexpected problem checking messages BOGUS' . self::LF);
    }

    public function testDispatchedStatusUpdatesTheLog(): void
    {
        $line = 'STATUS msg-1 dispatched';
        $this->reply(self::lines('OK', $line, 'OK', 'NONE'));
        $this->messageLog->method('findByMessageId')->with('msg-1')->willReturn(self::sentMessage());
        $this->messageLog->expects(self::once())->method('updateStatus')->with('msg-1', PhiMailDeliveryStatus::Dispatched, '');

        $this->receiver()->check();

        self::assertSame([[1, $line . self::LF, 42], [1, 'message check completed', 0]], $this->auditRows);
        self::assertSame([], $this->notifier->alerts);
        self::assertSame([], $this->notes->notes);
        self::assertSame(self::lines('AUTH test-user test-password', 'CHECK', 'OK', 'CHECK'), $this->sentCommands());
    }

    /**
     * @return array<string, array{string, PhiMailDeliveryStatus, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function undeliveredStatusProvider(): array
    {
        return [
            'failed' => [
                'STATUS msg-1 failed recipient rejected',
                PhiMailDeliveryStatus::Failed,
                'recipient rejected',
                'STATUS msg-1 failed recipient rejected',
            ],
            'unknown' => [
                'STATUS msg-1 delayed',
                PhiMailDeliveryStatus::Unknown,
                '',
                'UNKNOWN STATUS: STATUS msg-1 delayed',
            ],
        ];
    }

    #[DataProvider('undeliveredStatusProvider')]
    public function testUndeliveredStatusAlertsThePractice(string $line, PhiMailDeliveryStatus $status, string $info, string $serverMessage): void
    {
        $this->reply(self::lines('OK', $line, 'OK', 'NONE'));
        $this->messageLog->method('findByMessageId')->willReturn(self::sentMessage());
        $this->messageLog->expects(self::once())->method('updateStatus')->with('msg-1', $status, $info);

        $this->receiver()->check();

        $notice = self::lines(
            'Sent by: sender(3) on 2026-11-01 10:00:00',
            'Sent to: to@direct.example',
        ) . 'Server message: ' . $serverMessage . self::LF;
        self::assertSame([[0, $serverMessage . self::LF, 42], [1, 'message check completed', 0]], $this->auditRows);
        self::assertSame([['Direct Messaging Send Failure.', $notice]], $this->notifier->alerts);
        self::assertSame(
            [[
                'patientId' => 42,
                'text' => self::lines('FAILURE NOTICE: Direct Message Send Failed.', '') . $notice . self::LF,
                'assignedTo' => self::NOTIFY_USER,
            ]],
            $this->notes->notes,
        );
    }

    public function testUndeliveredStatusFromAnUnknownSender(): void
    {
        $this->reply(self::lines('OK', 'STATUS msg-1 failed', 'OK', 'NONE'));
        $this->messageLog->method('findByMessageId')->willReturn(new DirectMessageLogEntry(null, null, '2026-11-01 10:00:00', 'to@direct.example'));

        $this->receiver()->check();

        self::assertCount(1, $this->notifier->alerts);
        self::assertStringStartsWith('Sent by: unknown (see log)() on', $this->notifier->alerts[0][1]);
        self::assertSame(0, $this->notes->notes[0]['patientId']);
    }

    public function testUnmatchedStatusIsAcknowledged(): void
    {
        $line = 'STATUS msg-9 dispatched';
        $this->reply(self::lines('OK', $line, 'OK', 'NONE'));
        $this->messageLog->method('findByMessageId')->willReturn(null);
        $this->messageLog->expects(self::never())->method('updateStatus');

        $this->receiver()->check();

        self::assertSame([[0, 'NO MATCH: ' . $line . self::LF, 0], [1, 'message check completed', 0]], $this->auditRows);
        self::assertSame(self::lines('AUTH test-user test-password', 'CHECK', 'OK', 'CHECK'), $this->sentCommands());
    }

    public function testUnmatchedStatusAcknowledgmentFailure(): void
    {
        $this->reply(self::lines('OK', 'STATUS msg-9 dispatched', 'FAIL'));
        $this->messageLog->method('findByMessageId')->willReturn(null);

        $this->assertCheckFails('M1 status acknowledgment failed: FAIL' . self::LF);
    }

    public function testStatusAcknowledgmentFailure(): void
    {
        $this->reply(self::lines('OK', 'STATUS msg-1 dispatched', 'FAIL'));
        $this->messageLog->method('findByMessageId')->willReturn(self::sentMessage());

        $this->assertCheckFails('M2 status acknowledgment failed: FAIL' . self::LF);
    }

    public function testStatusLookupFailure(): void
    {
        $this->reply(self::lines('OK', 'STATUS msg-1 dispatched'));
        $this->messageLog->method('findByMessageId')->willThrowException(self::sqlFailure());

        $this->assertCheckFails('database problem');
    }

    public function testStatusUpdateFailure(): void
    {
        $this->reply(self::lines('OK', 'STATUS msg-1 dispatched'));
        $this->messageLog->method('findByMessageId')->willReturn(self::sentMessage());
        $this->messageLog->method('updateStatus')->willThrowException(self::sqlFailure());

        $this->assertCheckFails('database problem updating: msg-1');
    }

    public function testSenderLookupFailure(): void
    {
        $this->reply(self::lines('OK', 'STATUS msg-1 failed'));
        $this->messageLog->method('findByMessageId')->willReturn(self::sentMessage());
        $users = $this->createMock(PhiMailUserRepository::class);
        $users->method('findUsername')->willThrowException(self::sqlFailure());
        $this->users = $users;

        $this->assertCheckFails('database problem looking up sender of: msg-1');
    }

    public function testFailureNoticeNoteFailure(): void
    {
        $this->reply(self::lines('OK', 'STATUS msg-1 failed'));
        $this->messageLog->method('findByMessageId')->willReturn(self::sentMessage());
        $this->notes->failure = self::sqlFailure();

        $this->assertCheckFails('database problem adding failure notice for: msg-1');
    }

    public function testPlainTextMessageBecomesTheNote(): void
    {
        $mail = 'MAIL to@direct.example from@direct.example 0 msg-5';
        $this->reply(
            self::lines('OK', $mail, 'OK', 'Subject: hello', 'From: from@direct.example', '', 'Text/Plain; charset=utf-8', '5')
            . 'hello' . self::lines('0', 'OK', 'NONE'),
        );
        $this->messageLog->expects(self::once())->method('recordReceived')->with('msg-5', 'from@direct.example', 'to@direct.example', self::SERVICE_USER_ID);

        $this->receiver()->check();

        self::assertSame(
            [[
                'patientId' => 0,
                'text' => self::lines('Direct Message Received.', 'Subject: hello', 'From: from@direct.example', '') . 'hello',
                'assignedTo' => self::NOTIFY_USER,
            ]],
            $this->notes->notes,
        );
        self::assertSame([], $this->documents->stored);
        self::assertSame([[1, $mail . self::LF, 0], [1, 'message check completed', 0]], $this->auditRows);
        self::assertSame(self::lines('AUTH test-user test-password', 'CHECK', 'SHOW 0', 'DONE', 'CHECK'), $this->sentCommands());
        $this->assertTempDirIsEmpty();
    }

    public function testHtmlMessageIsConvertedToText(): void
    {
        $html = '<p>Hello<br>there &amp; you</p>';
        $this->reply(
            self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-5', 'OK', 'Subject: hello', '', 'text/html', (string) strlen($html))
            . $html . self::lines('0', 'OK', 'NONE'),
        );

        $this->receiver()->check();

        self::assertSame(self::lines('Direct Message Received.', 'Subject: hello', '') . 'Hello' . PHP_EOL . 'there & you', $this->notes->notes[0]['text']);
        $this->assertTempDirIsEmpty();
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function textTypeProvider(): array
    {
        return ['plain' => ['text/plain'], 'HTML' => ['text/html']];
    }

    #[DataProvider('textTypeProvider')]
    public function testEmptyTextMessageSaysSo(string $mimeType): void
    {
        $this->reply(self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-5', 'OK', 'Subject: hello', '', $mimeType, '0', '0', 'OK', 'NONE'));

        $this->receiver()->check();

        self::assertSame(
            self::lines('Direct Message Received.', 'Subject: hello', '') . 'Please note, this message was received empty and is not an error.',
            $this->notes->notes[0]['text'],
        );
    }

    /**
     * A message with attachments stores each as a document.
     */
    public function testMessageWithAttachmentsStoresEachAsADocument(): void
    {
        $mail = 'MAIL to@direct.example from@direct.example 3 msg-6';
        $this->reply(
            self::lines('OK', $mail, 'OK', 'Subject: records', '', 'application/pdf', '4') . '%PDF'
            . self::lines('3', 'report.xml', 'Text/XML; charset=utf-8', ' Summary of care ', '', 'application/pdf', 'Scan', 'empty.txt', 'text/plain', 'Nothing')
            . self::lines('OK', 'report.xml', 'text/xml', '6') . '<xml/>'
            . self::lines('OK', '', 'application/pdf', '3') . 'abc'
            . self::lines('OK', 'empty.txt', 'text/plain', '0')
            . self::lines('OK', 'NONE'),
        );

        $this->receiver()->check();

        self::assertCount(3, $this->documents->stored);
        [$body, $report, $scan] = $this->documents->stored;
        self::assertMatchesRegularExpression('/^dm-message-[0-9a-f]+\.pdf$/', $body['name']);
        self::assertSame(['application/pdf', '%PDF', self::SERVICE_USER_ID], [$body['mimeType'], $body['contents'], $body['ownerId']]);
        self::assertSame(['dm-report.xml', 'text/xml', '<xml/>'], [$report['name'], $report['mimeType'], $report['contents']]);
        // An attachment without a name gets a generated one, not the temp file's path.
        self::assertMatchesRegularExpression('/^dm-attachment-[0-9a-f]+\.pdf$/', $scan['name']);
        self::assertSame(['application/pdf', 'abc'], [$scan['mimeType'], $scan['contents']]);

        $expectedNote = self::lines(
            'Direct Message Received.',
            'Subject: records',
            '',
            'Message content is not plain text so it has been stored as a document.',
            '',
            'The following documents were attached to this Direct message:',
            'Document 101 ("file-101"; application/pdf; 4 bytes) Main message body',
            'Document 102 ("file-102"; text/xml; 6 bytes) Summary of care',
            'Document 103 ("file-103"; application/pdf; 3 bytes) Scan',
        ) . 'Zero length attachment (text/plain; 0 bytes - empty file received) Nothing';
        self::assertSame($expectedNote, $this->notes->notes[0]['text']);
        self::assertSame([[101, 501], [102, 501], [103, 501]], $this->notes->links);
        self::assertSame([[1, $mail . self::LF, 0], [1, 'message check completed', 0]], $this->auditRows);
        self::assertSame(
            self::lines('AUTH test-user test-password', 'CHECK', 'SHOW 0', 'SHOW 1', 'SHOW 2', 'SHOW 3', 'DONE', 'CHECK'),
            $this->sentCommands(),
        );
        $this->assertTempDirIsEmpty();
    }

    /**
     * A reply to a message sent with a referral is filed with it.
     */
    public function testAReplyToAMessageSentWithAReferralIsFiledWithIt(): void
    {
        $mail = 'MAIL to@direct.example from@direct.example 1 msg-7';
        $this->reply(
            self::lines(
                'OK',
                $mail,
                'OK',
                'Subject: Re: referral',
                'In-Reply-To: <sent-2@direct.example>',
                'References: <sent-1@direct.example>',
                ' <sent-2@direct.example>',
                '',
                'text/plain',
                '6',
            )
            . 'Seen.' . self::LF
            . self::lines('1', 'consult.xml', 'text/xml', 'Consult note')
            . self::lines('OK', 'consult.xml', 'text/xml', '6') . '<xml/>'
            . self::lines('OK', 'NONE'),
        );
        $this->messageLog->expects(self::once())->method('findReplyTarget')
            ->with(['sent-2@direct.example', 'sent-1@direct.example'], 'from@direct.example')
            ->willReturn(new DirectReplyTarget('sent-2@direct.example', 42, 7));
        $this->messageLog->expects(self::once())->method('recordReceived')
            ->with('msg-7', 'from@direct.example', 'to@direct.example', self::SERVICE_USER_ID, 42, 7, 501);

        $this->receiver()->check();

        self::assertSame([42], array_column($this->documents->stored, 'patientId'));
        self::assertSame(42, $this->notes->notes[0]['patientId']);
        self::assertStringStartsWith(
            'Direct Message Received. It answers a message sent with referral #7 for this patient.' . self::LF . 'Subject: Re: referral',
            $this->notes->notes[0]['text'],
        );
        self::assertSame([[101, 501]], $this->notes->links);
        self::assertSame([[1, $mail . self::LF, 42], [1, 'message check completed', 0]], $this->auditRows);
    }

    /**
     * A message that answers nothing sent stays unassigned.
     */
    public function testAMessageThatAnswersNothingSentStaysUnassigned(): void
    {
        $this->reply(
            self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-8', 'OK', 'In-Reply-To: <elsewhere@direct.example>', '', 'text/plain', '2')
            . 'hi' . self::lines('0', 'OK', 'NONE'),
        );
        $this->messageLog->method('findReplyTarget')->willReturn(null);
        $this->messageLog->expects(self::once())->method('recordReceived')
            ->with('msg-8', 'from@direct.example', 'to@direct.example', self::SERVICE_USER_ID, null, null, 501);

        $this->receiver()->check();

        self::assertSame(0, $this->notes->notes[0]['patientId']);
        self::assertStringStartsWith('Direct Message Received.' . self::LF, $this->notes->notes[0]['text']);
    }

    /**
     * Reply lookup failure.
     */
    public function testReplyLookupFailure(): void
    {
        $this->reply(self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-9', 'OK', 'Subject: hello', ''));
        $this->messageLog->method('findReplyTarget')->willThrowException(self::sqlFailure());

        $this->assertCheckFails('database problem matching reply: msg-9');
    }

    public function testEmptyNonTextBodyIsNotStored(): void
    {
        $this->reply(self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-7', 'OK', 'Subject: empty', '', 'application/pdf', '0', '0', 'OK', 'NONE'));

        $this->receiver()->check();

        self::assertSame([], $this->documents->stored);
        self::assertSame([], $this->notes->links);
        self::assertStringEndsWith(
            self::lines('', 'The following documents were attached to this Direct message:')
            . 'Zero length attachment (application/pdf; 0 bytes - empty file received) Main message body',
            $this->notes->notes[0]['text'],
        );
        $this->assertTempDirIsEmpty();
    }

    /**
     * Scripted replies after AUTH's OK, and the failure each one causes.
     *
     * @return array<string, array{string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function mailProtocolFailureProvider(): array
    {
        $mail = self::lines('MAIL to@direct.example from@direct.example 1 msg-8');
        $show0 = $mail . self::lines('OK', 'Subject: hello', '', 'text/plain', '5') . 'hello';
        $withInfo = $show0 . self::lines('1', 'scan.pdf', 'application/pdf', 'Scan');
        return [
            'SHOW 0 refused' => [$mail . self::lines('FAIL'), 'M3 SHOW 0 failed: FAIL' . self::LF, []],
            'headers cut off' => [$mail . self::lines('OK', 'Subject: hello'), 'M4 read headers failed', []],
            'MIME type missing' => [$mail . self::lines('OK', 'Subject: hello', ''), 'M4 read headers failed', []],
            'body length invalid' => [$mail . self::lines('OK', 'Subject: hello', '', 'text/plain', 'five'), 'M4 read body failed', ['M17 invalid blob length']],
            'body length missing' => [$mail . self::lines('OK', 'Subject: hello', '', 'text/plain'), 'M4 read body failed', ['M17 invalid blob length']],
            'attachment count missing' => [$show0, 'M5 attachment mismatch', []],
            'attachment count differs' => [$show0 . self::lines('2'), 'M5 attachment mismatch', []],
            'attachment info cut off' => [$show0 . self::lines('1', 'scan.pdf'), 'M6 read attachment 1 metadata failed', []],
            'SHOW 1 refused' => [$withInfo . self::lines('FAIL'), 'M8 SHOW 1 failed: FAIL' . self::LF, []],
            'repeated attachment header missing' => [$withInfo . self::lines('OK', 'scan.pdf'), 'M9 skip attachment 1 duplicate header lines failed', []],
            'attachment length invalid' => [
                $withInfo . self::lines('OK', 'scan.pdf', 'application/pdf', '-1'),
                'M10 read attachment 1 failed',
                ['M17 invalid blob length'],
            ],
        ];
    }

    /**
     * @param list<string> $earlierFailures audit rows written before the one that fails the check
     */
    #[DataProvider('mailProtocolFailureProvider')]
    public function testMailProtocolFailure(string $replies, string $reason, array $earlierFailures): void
    {
        $this->reply(self::lines('OK') . $replies);
        stream_socket_shutdown($this->server, STREAM_SHUT_WR);

        $this->assertCheckFails($reason);

        self::assertSame(
            [...array_map(static fn(string $text): array => [0, $text, 0], $earlierFailures), [0, $reason, 0]],
            $this->auditRows,
        );
        self::assertSame([], $this->notes->notes);
        $this->assertTempDirIsEmpty();
    }

    public function testMissingTempDirFailsTheDownload(): void
    {
        $this->reply(self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-8', 'OK', 'Subject: hello', '', 'text/plain', '5') . 'hello');
        $missing = $this->tempDir . '/missing';

        $this->assertCheckFails('M4 read body failed', $this->receiver(tempDir: $missing));

        self::assertSame([[0, 'M13 temp dir does not exist: ' . $missing, 0], [0, 'M4 read body failed', 0]], $this->auditRows);
    }

    public function testRefusedBodyDocument(): void
    {
        $this->reply($this->pdfMessage());
        $this->documents->refuse = true;

        $this->assertCheckFails('M7 store non-text body failed');

        self::assertSame([[0, 'M7 store non-text body failed', 0]], $this->auditRows);
        $this->assertTempDirIsEmpty();
    }

    public function testBodyDocumentStoreError(): void
    {
        $this->reply($this->pdfMessage());
        $this->documents->failure = new PhiMailDocumentException('upload failed');

        $this->assertCheckFails('M7 store non-text body failed');

        self::assertSame([[0, 'problem storing attachment in OpenEMR', 0], [0, 'M7 store non-text body failed', 0]], $this->auditRows);
        $this->assertTempDirIsEmpty();
    }

    public function testBodyDocumentWithoutAUrl(): void
    {
        $this->reply($this->pdfMessage());
        $this->documents->withoutUrl = true;

        $this->assertCheckFails('M7 store non-text body returned non-string url');
    }

    public function testRefusedAttachmentDocument(): void
    {
        $this->reply(
            self::lines('OK', 'MAIL to@direct.example from@direct.example 1 msg-8', 'OK', 'Subject: hello', '', 'text/plain', '5') . 'hello'
            . self::lines('1', 'scan.pdf', 'application/pdf', 'Scan', 'OK', 'scan.pdf', 'application/pdf', '3') . 'abc',
        );
        $this->documents->refuse = true;

        $this->assertCheckFails('M11 store attachment 1 failed');

        $this->assertTempDirIsEmpty();
    }

    public function testNoteFailureKeepsTheAcknowledgedBody(): void
    {
        $this->reply(self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-5', 'OK', 'Subject: hello', '', 'text/plain', '5') . 'hello' . self::lines('0', 'OK'));
        $this->notes->failure = self::sqlFailure();

        $this->assertCheckFails('database problem delivering received message: msg-5');

        // DONE told the server the message arrived, so its only copy stays on disk.
        $files = iterator_to_array((new Finder())->in($this->tempDir)->files(), false);
        self::assertCount(1, $files);
        self::assertSame('hello', $files[0]->getContents());
    }

    public function testUnreadableBodyFailsAndKeepsTheAcknowledgedBody(): void
    {
        $this->reply(self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-5', 'OK', 'Subject: hello', '', 'text/plain', '5') . 'hello' . self::lines('0', 'OK'));
        $filesystem = $this->createPartialMock(Filesystem::class, ['readFile']);
        $filesystem->method('readFile')->willThrowException(new IOException('unreadable'));

        $this->assertCheckFails('problem reading received message body: msg-5', $this->receiver(filesystem: $filesystem));

        self::assertSame([], $this->notes->notes);
        self::assertCount(1, (new Finder())->in($this->tempDir)->files());
    }

    public function testReceivedLogFailure(): void
    {
        $this->reply(self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-5', 'OK', 'Subject: hello', '', 'text/plain', '5') . 'hello' . self::lines('0', 'OK'));
        $this->messageLog->method('recordReceived')->willThrowException(self::sqlFailure());

        $this->assertCheckFails('database problem logging received message: msg-5');

        self::assertCount(1, $this->notes->notes);
    }

    public function testDoneFailureIsReportedAfterDelivery(): void
    {
        $mail = 'MAIL to@direct.example from@direct.example 0 msg-5';
        $this->reply(self::lines('OK', $mail, 'OK', 'Subject: hello', '', 'text/plain', '5') . 'hello' . self::lines('0', 'FAIL'));
        $this->messageLog->expects(self::once())->method('recordReceived');

        $this->assertCheckFails('M12 DONE failed: FAIL' . self::LF);

        self::assertCount(1, $this->notes->notes);
        self::assertSame([[1, $mail . self::LF, 0], [0, 'M12 DONE failed: FAIL' . self::LF, 0]], $this->auditRows);
    }

    private function receiver(?PhiMailConnectionFactory $connections = null, ?string $tempDir = null, Filesystem $filesystem = new Filesystem()): PhiMailReceiver
    {
        $auditLogger = $this->createMock(EventAuditLogger::class);
        $auditLogger->method('newEvent')->willReturnCallback(
            function (string $event, string $user, int $group, int $success, string $comments, ?int $patientId): void {
                self::assertSame('direct-message-check', $event);
                self::assertSame('phimail-service', $user);
                if ($this->auditFailure instanceof \Throwable) {
                    throw $this->auditFailure;
                }
                $this->auditRows[] = [$success, $comments, $patientId];
            }
        );
        $connection = $this->connection;

        return new PhiMailReceiver(
            config: new PhiMailConfig(new PhiMailServer(true, 'tls://phimail.example:32541', true, null), 'test-user', 'test-password', self::NOTIFY_USER),
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
            users: $this->users,
            documents: $this->documents,
            notes: $this->notes,
            notifier: $this->notifier,
            audit: new PhiMailAuditLog($auditLogger, new NullLogger()),
            logger: new NullLogger(),
            tempDir: $tempDir ?? $this->tempDir,
            filesystem: $filesystem,
        );
    }

    private function assertCheckFails(string $reason, ?PhiMailReceiver $receiver = null): void
    {
        try {
            ($receiver ?? $this->receiver())->check();
            self::fail('check() returned normally from a failed check.');
        } catch (PhiMailCheckException $e) {
            self::assertSame($reason, $e->getMessage());
        }
    }

    private function assertTempDirIsEmpty(): void
    {
        self::assertCount(0, (new Finder())->in($this->tempDir)->files(), 'downloaded content left in the temp directory');
    }

    /**
     * A message whose body is a 4-byte PDF, up to the server's reply to DONE.
     */
    private function pdfMessage(): string
    {
        return self::lines('OK', 'MAIL to@direct.example from@direct.example 0 msg-8', 'OK', 'Subject: hello', '', 'application/pdf', '4')
            . '%PDF' . self::lines('0', 'OK', 'NONE');
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

    private static function sentMessage(): DirectMessageLogEntry
    {
        return new DirectMessageLogEntry(42, 3, '2026-11-01 10:00:00', 'to@direct.example');
    }

    private static function sqlFailure(): SqlQueryException
    {
        return new SqlQueryException(sqlStatement: 'SELECT 1', message: 'database is down');
    }
}
