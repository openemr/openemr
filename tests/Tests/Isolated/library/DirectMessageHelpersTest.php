<?php

/**
 * Isolated tests for the legacy phiMail protocol helpers that ccr/transmitCCD.php calls.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Filesystem\Filesystem;

#[BackupGlobals(true)]
final class DirectMessageHelpersTest extends TestCase
{
    /** @var resource */
    private $client;

    /** @var resource */
    private $server;

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
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pair === false) {
            // @codeCoverageIgnoreStart
            // Defensive — socket pairs are always available on the platforms CI runs.
            self::fail('could not create a socket pair');
            // @codeCoverageIgnoreEnd
        }
        [$this->client, $this->server] = $pair;

        $audit = $this->createMock(EventAuditLogger::class);
        $audit->method('newEvent')->willReturnCallback(
            function (string $event, string $user, int $group, int $success, string $comments): void {
                $this->auditRows[] = [$success, $comments];
            }
        );
        self::setAuditLoggerInstances([EventAuditLogger::class => $audit]);
    }

    protected function tearDown(): void
    {
        self::setAuditLoggerInstances([]);
    }

    public function testWriteExpectOkReturnsTrueOnOk(): void
    {
        fwrite($this->server, "OK\n");

        self::assertTrue(phimail_write_expect_OK($this->client, "TO someone@direct.example\n"));
        self::assertSame("TO someone@direct.example\n", fgets($this->server));
    }

    public function testWriteExpectOkReturnsAnyOtherReply(): void
    {
        fwrite($this->server, "FAIL not allowed\n");

        self::assertSame("FAIL not allowed\n", phimail_write_expect_OK($this->client, "TO someone@direct.example\n"));
    }

    public function testWriteExpectOkReturnsFalseWhenTheServerHasHungUp(): void
    {
        fclose($this->server);

        self::assertFalse(phimail_write_expect_OK($this->client, "SEND\n"));
    }

    public function testWriteIgnoresAHungUpServer(): void
    {
        fclose($this->server);

        phimail_write($this->client, "SEND\n");

        self::assertFalse(fgets($this->client));
    }

    public function testCloseClosesTheStream(): void
    {
        phimail_close($this->client);

        self::assertFalse(fgets($this->server));
    }

    public function testReadBlobTakesTheRawLengthLine(): void
    {
        $tempDir = sys_get_temp_dir() . '/phimail-helpers-test-' . uniqid('', true);
        $fs = new Filesystem();
        $fs->mkdir($tempDir);
        OEGlobalsBag::getInstance()->set('temporary_files_dir', $tempDir);
        fwrite($this->server, 'hello');

        try {
            $path = phimail_read_blob($this->client, "5\n");
            self::assertIsString($path);
            self::assertSame('hello', $fs->readFile($path));
        } finally {
            $fs->remove($tempDir);
        }
    }

    public function testReadBlobAuditsAFailureAndReturnsFalse(): void
    {
        $missing = sys_get_temp_dir() . '/phimail-helpers-test-missing-' . uniqid('', true);
        OEGlobalsBag::getInstance()->set('temporary_files_dir', $missing);

        self::assertFalse(phimail_read_blob($this->client, "1\n"));
        self::assertSame([[0, 'M13 temp dir does not exist: ' . $missing]], $this->auditRows);
    }

    public function testReadBlobRejectsAMalformedLength(): void
    {
        self::assertFalse(phimail_read_blob($this->client, false));
        self::assertSame([[0, 'M17 invalid blob length']], $this->auditRows);
    }

    /**
     * @param array<class-string, EventAuditLogger> $instances
     */
    private static function setAuditLoggerInstances(array $instances): void
    {
        (new ReflectionProperty(EventAuditLogger::class, 'instances'))->setValue(null, $instances);
    }
}
