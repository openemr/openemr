<?php

/**
 * Isolated tests for the phiMail protocol connection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\PhiMailBlobException;
use OpenEMR\Common\DirectMessaging\PhiMailConnection;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionException;
use OpenEMR\Common\DirectMessaging\PhiMailConnectionFailure;
use OpenEMR\Common\DirectMessaging\PhiMailServer;
use OpenEMR\Common\DirectMessaging\PhiMailStreamException;
use OpenEMR\Common\DirectMessaging\PhiMailUnexpectedResponseException;
use OpenEMR\Services\SoftwareVersion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

final class PhiMailConnectionTest extends TestCase
{
    private PhiMailConnection $connection;

    /** @var resource the server's end of the socket pair */
    private $server;

    private string $tempDir;

    protected function setUp(): void
    {
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pair === false) {
            // @codeCoverageIgnoreStart
            // Defensive — socket pairs are always available on the platforms CI runs.
            self::fail('could not create a socket pair');
            // @codeCoverageIgnoreEnd
        }
        [$client, $this->server] = $pair;
        $this->connection = new PhiMailConnection($client);

        $tempDir = sys_get_temp_dir() . '/phimail-connection-test-' . uniqid('', true);
        (new Filesystem())->mkdir($tempDir);
        // PHP's tempnam() resolves symlinks in the directory, such as macOS's /var.
        $this->tempDir = realpath($tempDir) ?: $tempDir;
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tempDir);
    }

    public function testRejectsAValueThatIsNotAStream(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PhiMailConnection('not a stream');
    }

    public function testHandshakeSendsTheClientVersion(): void
    {
        $this->reply('OK');

        $this->connection->handshake(self::version());

        self::assertSame(
            'INFO VER OEMR 8.1.0 1.3.2 ' . PHP_VERSION . "\n",
            fgets($this->server),
        );
    }

    public function testRejectedHandshakeClosesTheConnection(): void
    {
        $this->reply('FAIL');

        try {
            $this->connection->handshake(self::version());
            self::fail('handshake() accepted a FAIL reply.');
        } catch (PhiMailConnectionException $e) {
            self::assertSame(PhiMailConnectionFailure::HandshakeRejected, $e->failure);
            self::assertInstanceOf(PhiMailUnexpectedResponseException::class, $e->getPrevious());
        }

        fgets($this->server);
        self::assertFalse(fgets($this->server), 'the client end is still open');
    }

    public function testExpectOkAcceptsOk(): void
    {
        $this->reply('OK');

        $this->connection->expectOk("CHECK\n");

        self::assertSame("CHECK\n", fgets($this->server));
    }

    public function testExpectOkReportsAnyOtherReply(): void
    {
        $this->reply('FAIL bad credentials');

        try {
            $this->connection->expectOk("AUTH user pass\n");
            self::fail('expectOk() accepted a FAIL reply.');
        } catch (PhiMailUnexpectedResponseException $e) {
            self::assertSame("FAIL bad credentials\n", $e->response);
        }
    }

    public function testReadLineReturnsTheLineWithItsLineFeed(): void
    {
        $this->reply('NONE');

        self::assertSame("NONE\n", $this->connection->readLine());
    }

    public function testReadLineThrowsWhenTheServerHasHungUp(): void
    {
        fclose($this->server);

        $this->expectException(PhiMailStreamException::class);
        $this->connection->readLine();
    }

    public function testWriteThrowsWhenTheServerHasHungUp(): void
    {
        fclose($this->server);

        $this->expectException(PhiMailStreamException::class);
        $this->connection->write("CHECK\n");
    }

    public function testReadBlobDownloadsExactlyTheRequestedBytes(): void
    {
        $body = str_repeat('0123456789', 250);
        fwrite($this->server, $body . "2\n");

        // A permissive umask must not leave the download readable by others.
        $umask = umask(0);
        try {
            $path = $this->connection->readBlob(strlen($body), $this->tempDir);
        } finally {
            umask($umask);
        }

        // PHP's tempnam() keeps only three characters of the prefix on Windows, so check the directory.
        self::assertSame($this->tempDir, dirname($path));
        self::assertSame($body, (new Filesystem())->readFile($path));
        self::assertSame(0600, fileperms($path) & 0777, 'the download is readable by others');
        self::assertSame("2\n", $this->connection->readLine(), 'readBlob() read past the blob');
    }

    public function testReadBlobOfZeroBytesCreatesAnEmptyFile(): void
    {
        $path = $this->connection->readBlob(0, $this->tempDir);

        self::assertSame('', (new Filesystem())->readFile($path));
    }

    public function testReadBlobFailsWithoutTheTempDir(): void
    {
        $missing = $this->tempDir . '/missing';

        $this->expectException(PhiMailBlobException::class);
        $this->expectExceptionMessage('M13 temp dir does not exist: ' . $missing);
        $this->connection->readBlob(1, $missing);
    }

    public function testReadBlobFailsWhenItCannotCreateTheTempFile(): void
    {
        // A regular file passes the existence check but cannot hold a new file, so
        // PHP's tempnam() creates one in the system temp dir instead.
        $notADir = $this->tempDir . '/file';
        (new Filesystem())->touch($notADir);
        $filesystem = new RecordingFilesystem();
        $connection = new PhiMailConnection(fopen('php://memory', 'r'), $filesystem);

        try {
            $connection->readBlob(1, $notADir);
            self::fail('readBlob() returned a file outside the temp dir.');
        } catch (PhiMailBlobException $e) {
            self::assertSame('M14 failed creating a temp file in: ' . $notADir, $e->getMessage());
        }

        self::assertCount(1, $filesystem->tempFiles);
        self::assertFileDoesNotExist($filesystem->tempFiles[0], 'the fallback temp file was left behind');
    }

    public function testReadBlobFailsAndCleansUpWhenTheStreamCannotBeRead(): void
    {
        // A write-only file stream is open and not at EOF, but fread() fails.
        $source = $this->tempDir . '/source';
        $connection = new PhiMailConnection(fopen($source, 'w'));

        try {
            $connection->readBlob(10, $this->tempDir);
            self::fail('readBlob() returned from a failed read.');
        } catch (PhiMailBlobException $e) {
            self::assertSame('M15 failure reading blob chunk after 0 bytes', $e->getMessage());
        }

        self::assertSame([$source], glob($this->tempDir . '/*'), 'the partial download was left behind');
    }

    public function testReadBlobFailsAndCleansUpWhenTheFileCannotBeWritten(): void
    {
        $this->reply('data');
        FailingFileStreamWrapper::register();

        try {
            $this->connection->readBlob(5, FailingFileStreamWrapper::SCHEME . '://downloads');
            self::fail('readBlob() returned from a failed write.');
        } catch (PhiMailBlobException $e) {
            self::assertSame('M15 failure writing blob chunk after 0 bytes', $e->getMessage());
            self::assertSame([], FailingFileStreamWrapper::$files, 'the partial download was left behind');
        } finally {
            FailingFileStreamWrapper::unregister();
        }
    }

    public function testReadBlobFailsAndCleansUpWhenTheTempFileCannotBeOpened(): void
    {
        FailingFileStreamWrapper::register();
        FailingFileStreamWrapper::$failOpenForWrite = true;

        try {
            $this->connection->readBlob(1, FailingFileStreamWrapper::SCHEME . '://downloads');
            self::fail('readBlob() returned without a temp file.');
        } catch (PhiMailBlobException $e) {
            self::assertStringStartsWith('M14 failed opening temp file: ' . FailingFileStreamWrapper::SCHEME . '://downloads/direct-', $e->getMessage());
            self::assertSame([], FailingFileStreamWrapper::$files, 'the empty temp file was left behind');
        } finally {
            FailingFileStreamWrapper::unregister();
        }
    }

    public function testOpenRefusesWhenDisabled(): void
    {
        $this->assertOpenFails(new PhiMailServer(false, 'https://127.0.0.1:1', false, null), PhiMailConnectionFailure::Disabled);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidAddressProvider(): array
    {
        return [
            'no scheme' => ['phimail.example.com'],
            'unsupported scheme' => ['ftp://phimail.example.com:21'],
            'malformed' => ['http://:80'],
        ];
    }

    #[DataProvider('invalidAddressProvider')]
    public function testOpenRejectsAnInvalidAddress(string $address): void
    {
        $this->assertOpenFails(new PhiMailServer(true, $address, false, null), PhiMailConnectionFailure::InvalidAddress);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unreachableAddressProvider(): array
    {
        // Nothing listens on port 1, so every attempt is refused.
        return [
            'plain TCP' => ['tcp://127.0.0.1:1'],
            'HTTPS' => ['https://127.0.0.1:1'],
            'TLS as configured' => ['tls://127.0.0.1:1'],
        ];
    }

    #[DataProvider('unreachableAddressProvider')]
    public function testOpenReportsTheSocketErrorWhenUnreachable(string $address): void
    {
        $e = $this->assertOpenFails(new PhiMailServer(true, $address, true, __FILE__), PhiMailConnectionFailure::Unreachable);

        self::assertNotSame(0, $e->socketErrorCode);
        self::assertNotSame('', $e->socketErrorMessage);
    }

    private function assertOpenFails(PhiMailServer $server, PhiMailConnectionFailure $failure): PhiMailConnectionException
    {
        try {
            PhiMailConnection::open($server, self::version(), new NullLogger());
        } catch (PhiMailConnectionException $e) {
            self::assertSame($failure, $e->failure);
            return $e;
        }
        self::fail('open() succeeded.');
    }

    private function reply(string $line): void
    {
        fwrite($this->server, $line . "\n");
    }

    private static function version(): SoftwareVersion
    {
        return new SoftwareVersion(8, 1, 0, '', 0, 0, 0);
    }
}
