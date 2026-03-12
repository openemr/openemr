<?php

/**
 * Tests for LogTablesSink
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use Doctrine\DBAL\Connection;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\LogTablesSink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogTablesSink::class)]
class LogTablesSinkTest extends TestCase
{
    private Connection&MockObject $connection;
    private CryptoInterface&MockObject $crypto;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->crypto = $this->createMock(CryptoInterface::class);
    }

    private function createEvent(?array $api = null): Event
    {
        return new Event(
            current_datetime: '2026-03-11 12:00:00',
            event: 'test-event',
            category: 'test-category',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Test comment',
            user_notes: 'User notes',
            patientId: 123,
            success: 1,
            SSL_CLIENT_S_DN_CN: 'cert-user',
            logFrom: 'open-emr',
            menuItemId: null,
            ccdaDocId: null,
            api: $api,
        );
    }

    public function testRecordInsertsIntoLogTable(): void
    {
        $event = $this->createEvent();

        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data): int {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    $this->assertSame('log', $table);
                    $this->assertSame('2026-03-11 12:00:00', $data['date']);
                    $this->assertSame('test-event', $data['event']);
                    $this->assertSame('test-category', $data['category']);
                    $this->assertSame('testuser', $data['user']);
                    $this->assertSame('testgroup', $data['groupname']);
                    $this->assertSame(base64_encode('Test comment'), $data['comments']);
                    $this->assertSame('User notes', $data['user_notes']);
                    $this->assertSame(123, $data['patient_id']);
                    $this->assertSame(1, $data['success']);
                    $this->assertSame('cert-user', $data['crt_user']);
                    $this->assertSame('open-emr', $data['log_from']);
                } elseif ($callCount === 2) {
                    $this->assertSame('log_comment_encrypt', $table);
                }

                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('42');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $result = $sink->record($event);
        $this->assertTrue($result);
    }

    public function testRecordInsertsIntoLogCommentEncryptWithCorrectChecksum(): void
    {
        $event = $this->createEvent();

        $capturedLogCommentData = null;

        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogCommentData): int {
                if ($table === 'log_comment_encrypt') {
                    $capturedLogCommentData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('99');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        $this->assertNotNull($capturedLogCommentData);
        $this->assertSame('99', $capturedLogCommentData['log_id']);
        $this->assertSame('No', $capturedLogCommentData['encrypt']);
        $this->assertSame('', $capturedLogCommentData['checksum_api']);
        $this->assertSame('4', $capturedLogCommentData['version']);
        // Checksum should be a sha3-512 hash (128 hex chars)
        $this->assertSame(128, strlen((string) $capturedLogCommentData['checksum']));
    }

    public function testRecordEncryptsCommentsWhenEnabled(): void
    {
        $event = $this->createEvent();

        $this->crypto->expects($this->once())
            ->method('encryptStandard')
            ->with('Test comment')
            ->willReturn('encrypted-comment');

        $capturedLogData = null;
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogData): int {
                if ($table === 'log') {
                    $capturedLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: true,
        );

        $sink->record($event);

        $this->assertSame('encrypted-comment', $capturedLogData['comments']);
    }

    public function testRecordBase64EncodesCommentsWhenNotEncrypting(): void
    {
        $event = $this->createEvent();

        $this->crypto->expects($this->never())->method('encryptStandard');

        $capturedLogData = null;
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogData): int {
                if ($table === 'log') {
                    $capturedLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        $this->assertSame(base64_encode('Test comment'), $capturedLogData['comments']);
    }

    public function testRecordSetsEncryptFlagToYesWhenEncrypting(): void
    {
        $event = $this->createEvent();

        $this->crypto->method('encryptStandard')->willReturn('encrypted');

        $capturedLogCommentData = null;
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogCommentData): int {
                if ($table === 'log_comment_encrypt') {
                    $capturedLogCommentData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: true,
        );

        $sink->record($event);

        $this->assertSame('Yes', $capturedLogCommentData['encrypt']);
    }

    public function testRecordSetsEncryptFlagToNoWhenNotEncrypting(): void
    {
        $event = $this->createEvent();

        $capturedLogCommentData = null;
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogCommentData): int {
                if ($table === 'log_comment_encrypt') {
                    $capturedLogCommentData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        $this->assertSame('No', $capturedLogCommentData['encrypt']);
    }

    public function testRecordHandlesNullUserAndGroup(): void
    {
        $event = new Event(
            current_datetime: '2026-03-11 12:00:00',
            event: 'test-event',
            category: 'test-category',
            user: null,
            group: null,
            comments: 'Test comment',
            user_notes: '',
            patientId: null,
            success: 1,
            SSL_CLIENT_S_DN_CN: '',
            logFrom: 'open-emr',
            menuItemId: null,
            ccdaDocId: null,
            api: null,
        );

        $capturedLogData = null;
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogData): int {
                if ($table === 'log') {
                    $capturedLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        // Null user/group should be converted to empty string
        $this->assertSame('', $capturedLogData['user']);
        $this->assertSame('', $capturedLogData['groupname']);
    }

    public function testRecordHandlesNullPatientId(): void
    {
        $event = new Event(
            current_datetime: '2026-03-11 12:00:00',
            event: 'test-event',
            category: null,
            user: 'user',
            group: 'group',
            comments: 'comment',
            user_notes: '',
            patientId: null,
            success: 1,
            SSL_CLIENT_S_DN_CN: '',
            logFrom: 'open-emr',
            menuItemId: null,
            ccdaDocId: null,
            api: null,
        );

        $capturedLogData = null;
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogData): int {
                if ($table === 'log') {
                    $capturedLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        $this->assertNull($capturedLogData['patient_id']);
        $this->assertNull($capturedLogData['category']);
    }

    private function createApiData(): array
    {
        return [
            'user_id' => 1,
            'patient_id' => 123,
            'method' => 'GET',
            'request' => '/api/patient',
            'request_url' => 'https://example.com/api/patient',
            'request_body' => '{"foo":"bar"}',
            'response' => '{"status":"ok"}',
        ];
    }

    public function testRecordInsertsIntoApiLogWhenApiDataPresent(): void
    {
        $event = $this->createEvent($this->createApiData());

        $insertedTables = [];
        $this->connection->expects($this->exactly(3))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$insertedTables): int {
                $insertedTables[] = $table;
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('42');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        $this->assertSame(['log', 'log_comment_encrypt', 'api_log'], $insertedTables);
    }

    public function testRecordDoesNotInsertIntoApiLogWhenNoApiData(): void
    {
        $event = $this->createEvent(null);

        $insertedTables = [];
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$insertedTables): int {
                $insertedTables[] = $table;
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        $this->assertSame(['log', 'log_comment_encrypt'], $insertedTables);
    }

    public function testRecordApiLogContainsCorrectData(): void
    {
        $apiData = $this->createApiData();
        $event = $this->createEvent($apiData);

        $capturedApiLogData = null;
        $this->connection->expects($this->exactly(3))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedApiLogData): int {
                if ($table === 'api_log') {
                    $capturedApiLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('55');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        $this->assertNotNull($capturedApiLogData);
        $this->assertSame('55', $capturedApiLogData['log_id']);
        $this->assertSame(1, $capturedApiLogData['user_id']);
        $this->assertSame(123, $capturedApiLogData['patient_id']);
        $this->assertSame('GET', $capturedApiLogData['method']);
        $this->assertSame('/api/patient', $capturedApiLogData['request']);
        $this->assertSame('https://example.com/api/patient', $capturedApiLogData['request_url']);
        $this->assertSame('{"foo":"bar"}', $capturedApiLogData['request_body']);
        $this->assertSame('{"status":"ok"}', $capturedApiLogData['response']);
        $this->assertSame('2026-03-11 12:00:00', $capturedApiLogData['created_time']);
        // IP address comes from collectIpAddresses()
        $this->assertArrayHasKey('ip_address', $capturedApiLogData);
    }

    public function testRecordEncryptsApiFieldsWhenEnabled(): void
    {
        $apiData = $this->createApiData();
        $event = $this->createEvent($apiData);

        $this->crypto->expects($this->exactly(4))
            ->method('encryptStandard')
            ->willReturnCallback(fn(string $value) => "encrypted:$value");

        $capturedApiLogData = null;
        $this->connection->expects($this->exactly(3))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedApiLogData): int {
                if ($table === 'api_log') {
                    $capturedApiLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: true,
        );

        $sink->record($event);

        $this->assertSame('encrypted:https://example.com/api/patient', $capturedApiLogData['request_url']);
        $this->assertSame('encrypted:{"foo":"bar"}', $capturedApiLogData['request_body']);
        $this->assertSame('encrypted:{"status":"ok"}', $capturedApiLogData['response']);
    }

    public function testRecordDoesNotEncryptEmptyApiFields(): void
    {
        $apiData = [
            'user_id' => 1,
            'patient_id' => 123,
            'method' => 'GET',
            'request' => '/api/patient',
            'request_url' => '',
            'request_body' => '',
            'response' => '',
        ];
        $event = $this->createEvent($apiData);

        // Only comments should be encrypted, not the empty API fields
        $this->crypto->expects($this->once())
            ->method('encryptStandard')
            ->with('Test comment')
            ->willReturn('encrypted-comment');

        $capturedApiLogData = null;
        $this->connection->expects($this->exactly(3))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedApiLogData): int {
                if ($table === 'api_log') {
                    $capturedApiLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: true,
        );

        $sink->record($event);

        $this->assertSame('', $capturedApiLogData['request_url']);
        $this->assertSame('', $capturedApiLogData['request_body']);
        $this->assertSame('', $capturedApiLogData['response']);
    }

    public function testRecordApiChecksumIsPopulatedWhenApiDataPresent(): void
    {
        $event = $this->createEvent($this->createApiData());

        $capturedLogCommentData = null;
        $this->connection->expects($this->exactly(3))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogCommentData): int {
                if ($table === 'log_comment_encrypt') {
                    $capturedLogCommentData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(
            conn: $this->connection,
            crypto: $this->crypto,
            shouldEncrypt: false,
        );

        $sink->record($event);

        // API checksum should be a sha3-512 hash (128 hex chars)
        $this->assertSame(128, strlen((string) $capturedLogCommentData['checksum_api']));
    }
}
