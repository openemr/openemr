<?php

/**
 * Isolated tests for phimail_check() failure reporting.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\DirectMessaging\PhiMailCheckException;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Process\Process;

#[BackupGlobals(true)]
final class DirectMessageCheckTest extends TestCase
{
    private const SERVER = __DIR__ . '/fixtures/phimail-server.php';

    /** @var list<array{int, string}> success flag and comments of each audit row */
    private array $auditRows = [];

    public static function setUpBeforeClass(): void
    {
        $projectDir = dirname(__DIR__, 4);
        OEGlobalsBag::getInstance()->set('fileroot', $projectDir);
        require_once $projectDir . '/library/translation.inc.php';
        require_once $projectDir . '/library/direct_message_check.inc.php';
    }

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $globals->set('disable_translation', true);
        $globals->set('phimail_enable', true);
        $globals->set('phimail_username', 'test-user');
        $globals->set('phimail_password', 'stored-password');

        $crypto = $this->createStub(CryptoInterface::class);
        $crypto->method('decryptFromDatabase')->willReturn('test-password');
        ServiceContainer::override(CryptoInterface::class, $crypto);

        $audit = $this->createMock(EventAuditLogger::class);
        $audit->method('newEvent')->willReturnCallback(
            function (string $event, string $user, int $group, int $success, string $comments): void {
                self::assertSame('direct-message-check', $event);
                self::assertSame('phimail-service', $user);
                $this->auditRows[] = [$success, $comments];
            }
        );
        self::setAuditLoggerInstances([EventAuditLogger::class => $audit]);
    }

    protected function tearDown(): void
    {
        self::setAuditLoggerInstances([]);
        ServiceContainer::reset();
    }

    public function testDisabledServiceFailsAfterAuditing(): void
    {
        OEGlobalsBag::getInstance()->set('phimail_enable', false);

        try {
            phimail_check();
            self::fail('phimail_check() returned normally from a failed connect.');
        } catch (PhiMailCheckException $e) {
            self::assertSame('could not connect to server C1', $e->getMessage());
        }

        self::assertSame([[0, 'could not connect to server C1']], $this->auditRows);
    }

    public function testUnreachableServerFailsAfterAuditing(): void
    {
        // Nothing listens on port 1, so every connect attempt is refused.
        OEGlobalsBag::getInstance()->set('phimail_server_address', 'https://127.0.0.1:1');

        try {
            phimail_check();
            self::fail('phimail_check() returned normally from an unreachable server.');
        } catch (PhiMailCheckException $e) {
            $reason = $e->getMessage();
        }

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

        try {
            phimail_check();
            self::fail('phimail_check() returned normally from a failed check.');
        } catch (PhiMailCheckException $e) {
            self::assertSame($reason, $e->getMessage());
        }

        self::assertSame([[0, $reason]], $this->auditRows);
        self::assertSame('closed', $this->serverResult($server));
    }

    public function testNoMessagesCompletesNormally(): void
    {
        $server = $this->startServer(['OK', 'OK', 'NONE']);

        phimail_check();

        self::assertSame([[1, 'message check completed']], $this->auditRows);
        self::assertSame('closed', $this->serverResult($server));
    }

    /**
     * @param list<string> $replies
     */
    private function startServer(array $replies): Process
    {
        $server = new Process([PHP_BINARY, self::SERVER, ...$replies]);
        $server->setTimeout(30);
        $server->start();
        $port = null;
        $server->waitUntil(function (string $type, string $output) use (&$port): bool {
            if (preg_match('/^port (\d+)$/m', $output, $m) === 1) {
                $port = $m[1];
            }
            return $port !== null;
        });
        self::assertIsString($port, 'fake phiMail server did not start: ' . $server->getErrorOutput());
        OEGlobalsBag::getInstance()->set('phimail_server_address', 'http://127.0.0.1:' . $port);
        return $server;
    }

    /**
     * Wait for the fake server to exit and return its last line: "closed" when
     * phimail_check() closed the connection.
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
