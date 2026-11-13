<?php

/**
 * Isolated tests for PhiMailReceiver::runBackgroundService() failure reporting.
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
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\DirectMessaging\PhiMailCheckException;
use OpenEMR\Common\DirectMessaging\PhiMailReceiver;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

#[BackupGlobals(true)]
final class PhiMailReceiverBackgroundServiceTest extends TestCase
{
    private const SERVER = __DIR__ . '/fixtures/phimail-server.php';

    /** @var list<array{int, string}> success flag and comments of each audit row */
    private array $auditRows = [];

    /** Thrown by the audit logger in place of writing a row, when set */
    private ?\Throwable $auditFailure = null;

    /** Where the receiver downloads message bodies and attachments */
    private string $tempDir;

    public static function setUpBeforeClass(): void
    {
        $projectDir = dirname(__DIR__, 5);
        OEGlobalsBag::getInstance()->set('fileroot', $projectDir);
        require_once $projectDir . '/library/translation.inc.php';
    }

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $globals->set('disable_translation', true);
        $globals->set('phimail_enable', true);
        $globals->set('phimail_username', 'test-user');
        $globals->set('phimail_password', 'stored-password');

        $this->tempDir = sys_get_temp_dir() . '/phimail-test-' . uniqid('', true);
        (new Filesystem())->mkdir($this->tempDir);
        $globals->set('temporary_files_dir', $this->tempDir);

        $crypto = $this->createStub(CryptoInterface::class);
        $crypto->method('decryptFromDatabase')->willReturn('test-password');
        ServiceContainer::override(CryptoInterface::class, $crypto);

        $audit = $this->createMock(EventAuditLogger::class);
        $audit->method('newEvent')->willReturnCallback(
            function (string $event, string $user, int $group, int $success, string $comments): void {
                self::assertSame('direct-message-check', $event);
                self::assertSame('phimail-service', $user);
                if ($this->auditFailure instanceof \Throwable) {
                    throw $this->auditFailure;
                }
                $this->auditRows[] = [$success, $comments];
            }
        );
        self::setAuditLoggerInstances([EventAuditLogger::class => $audit]);
    }

    protected function tearDown(): void
    {
        self::setAuditLoggerInstances([]);
        ServiceContainer::reset();
        (new Filesystem())->remove($this->tempDir);
    }

    public function testDisabledServiceFailsAfterAuditing(): void
    {
        OEGlobalsBag::getInstance()->set('phimail_enable', false);

        self::assertSame('could not connect to server C1', self::failedCheck()->getMessage());

        self::assertSame([[0, 'could not connect to server C1']], $this->auditRows);
    }

    public function testFailedAuditWriteKeepsTheCheckFailure(): void
    {
        OEGlobalsBag::getInstance()->set('phimail_enable', false);
        $this->auditFailure = new ConnectionException('audit database is down');

        $this->expectException(PhiMailCheckException::class);
        $this->expectExceptionMessage('could not connect to server C1');
        PhiMailReceiver::runBackgroundService();
    }

    public function testUnreachableServerFailsAfterAuditing(): void
    {
        // Nothing listens on port 1, so every connect attempt is refused.
        OEGlobalsBag::getInstance()->set('phimail_server_address', 'https://127.0.0.1:1');

        $reason = self::failedCheck()->getMessage();

        // The C4 detail carries the OS error number, which differs by platform.
        self::assertStringStartsWith('could not connect to server C4 ', $reason);
        self::assertSame([[0, $reason]], $this->auditRows);
    }

    /**
     * @return array<string, array{list<string>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function failingServerProvider(): array
    {
        return [
            'INFO rejected' => [['FAIL'], 'could not connect to server C5'],
            'AUTH rejected' => [['OK', 'FAIL bad credentials'], "authentication error FAIL bad credentials\n"],
            'unexpected CHECK reply' => [['OK', 'OK', 'BOGUS'], "M16 unexpected problem checking messages BOGUS\n"],
        ];
    }

    /**
     * @param list<string> $replies
     */
    #[DataProvider('failingServerProvider')]
    public function testServerFailureThrowsAfterAuditingAndClosesSocket(array $replies, string $reason): void
    {
        $server = $this->startServer($replies);

        self::assertSame($reason, self::failedCheck()->getMessage());

        self::assertSame([[0, $reason]], $this->auditRows);
        self::assertSame('closed', $this->serverResult($server));
    }

    public function testFailedMessageDownloadDeletesTheTempBody(): void
    {
        // The reply to SHOW 0: OK, headers, a blank line, the MIME type, the
        // body length, then the 5-byte body directly followed by an
        // attachment count of 2, which does not match the 1 that MAIL announced.
        $show = <<<'REPLY'
            OK
            Subject: test

            text/plain
            5
            hello2
            REPLY;
        $server = $this->startServer(['OK', 'OK', 'MAIL to@example.org from@example.org 1 msg-1', $show]);

        self::assertSame('M5 attachment mismatch', self::failedCheck()->getMessage());

        self::assertSame([[0, 'M5 attachment mismatch']], $this->auditRows);
        self::assertSame('closed', $this->serverResult($server));
        self::assertCount(0, (new Finder())->in($this->tempDir)->files(), 'downloaded body left in the temp directory');
    }

    public function testMalformedBodyLengthFailsWithoutATempFile(): void
    {
        $show = <<<'REPLY'
            OK
            Subject: test

            text/plain
            bogus
            REPLY;
        $server = $this->startServer(['OK', 'OK', 'MAIL to@example.org from@example.org 0 msg-1', $show]);

        self::assertSame('M4 read body failed', self::failedCheck()->getMessage());

        self::assertSame([[0, 'M17 invalid blob length'], [0, 'M4 read body failed']], $this->auditRows);
        self::assertSame('closed', $this->serverResult($server));
        self::assertCount(0, (new Finder())->in($this->tempDir)->files(), 'temp file created for a malformed length');
    }

    public function testFailedAuditWriteInAHelperKeepsTheCheckFailure(): void
    {
        $show = <<<'REPLY'
            OK
            Subject: test

            text/plain
            bogus
            REPLY;
        $server = $this->startServer(['OK', 'OK', 'MAIL to@example.org from@example.org 0 msg-1', $show]);
        $this->auditFailure = new ConnectionException('audit database is down');

        self::assertSame('M4 read body failed', self::failedCheck()->getMessage());

        self::assertSame('closed', $this->serverResult($server));
    }

    /**
     * A check that worked but could not write its success audit row must
     * surface the database error, so the run is not recorded as clean.
     */
    public function testFailedAuditWriteFailsAnOtherwiseSuccessfulCheck(): void
    {
        $server = $this->startServer(['OK', 'OK', 'NONE']);
        $auditFailure = new ConnectionException('audit database is down');
        $this->auditFailure = $auditFailure;

        $failure = null;
        try {
            PhiMailReceiver::runBackgroundService();
        } catch (ConnectionException $e) {
            $failure = $e;
        }
        self::assertSame($auditFailure, $failure, 'the check hid a failed audit write');

        self::assertSame('closed', $this->serverResult($server));
    }

    public function testNoMessagesCompletesNormally(): void
    {
        $server = $this->startServer(['OK', 'OK', 'NONE']);

        PhiMailReceiver::runBackgroundService();

        self::assertSame([[1, 'message check completed']], $this->auditRows);
        self::assertSame('closed', $this->serverResult($server));
    }

    /**
     * Run the check, which must fail, and return its failure. Catching here
     * rather than calling fail() after the call keeps every line reachable.
     */
    private static function failedCheck(): PhiMailCheckException
    {
        $failure = null;
        try {
            PhiMailReceiver::runBackgroundService();
        } catch (PhiMailCheckException $e) {
            $failure = $e;
        }
        self::assertInstanceOf(PhiMailCheckException::class, $failure, 'the check returned normally');
        return $failure;
    }

    /**
     * @param list<string> $replies
     */
    private function startServer(array $replies): Process
    {
        $server = new Process([PHP_BINARY, self::SERVER, ...$replies]);
        $server->setTimeout(60);
        $server->start();
        $port = null;
        // Match against all output so far, since a line can arrive split across chunks
        $findPort = function () use ($server, &$port): bool {
            if (preg_match('/^port (\d+)\R/m', $server->getOutput(), $m) === 1) {
                $port = $m[1];
            }
            return $port !== null;
        };
        // start() can read the port line before waitUntil() runs, and waitUntil()
        // does not replay output already read, so check what has arrived first
        if (!$findPort()) {
            $server->waitUntil($findPort);
        }
        self::assertIsString($port, 'fake phiMail server did not start: ' . $server->getErrorOutput());
        OEGlobalsBag::getInstance()->set('phimail_server_address', 'http://127.0.0.1:' . $port);
        return $server;
    }

    /**
     * Wait for the fake server to exit and return its last line: "closed" when
     * PhiMailReceiver::runBackgroundService() closed the connection.
     */
    private function serverResult(Process $server): string
    {
        $server->wait();
        self::assertSame(0, $server->getExitCode(), $server->getErrorOutput());
        $lines = explode(PHP_EOL, trim($server->getOutput()));
        return end($lines);
    }

    /**
     * @param array<class-string, EventAuditLogger> $instances
     */
    private static function setAuditLoggerInstances(array $instances): void
    {
        (new ReflectionProperty(EventAuditLogger::class, 'instances'))->setValue(null, $instances);
    }
}
