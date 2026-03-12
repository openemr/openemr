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
use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\LogTablesSink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type ApiData from Event
 */
#[CoversClass(LogTablesSink::class)]
class LogTablesSinkTest extends TestCase
{
    private Connection&MockObject $connection;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
    }

    /**
     * @param ?ApiData $api
     */
    private function createEvent(bool $isEncrypted = false, ?array $api = null): Event
    {
        return new Event(
            isEncrypted: $isEncrypted,
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

        $callCount = 0;
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$callCount): int {
                $callCount++;

                if ($callCount === 1) {
                    self::assertSame('log', $table);
                    self::assertSame('2026-03-11 12:00:00', $data['date']);
                    self::assertSame('test-event', $data['event']);
                    self::assertSame('test-category', $data['category']);
                    self::assertSame('testuser', $data['user']);
                    self::assertSame('testgroup', $data['groupname']);
                    self::assertSame('Test comment', $data['comments']);
                    self::assertSame('User notes', $data['user_notes']);
                    self::assertSame(123, $data['patient_id']);
                    self::assertSame(1, $data['success']);
                    self::assertSame('cert-user', $data['crt_user']);
                    self::assertSame('open-emr', $data['log_from']);
                } elseif ($callCount === 2) {
                    self::assertSame('log_comment_encrypt', $table);
                }

                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('42');

        $sink = new LogTablesSink(conn: $this->connection);

        $result = $sink->record($event);
        self::assertTrue($result);
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

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        self::assertIsArray($capturedLogCommentData);
        self::assertSame('99', $capturedLogCommentData['log_id']);
        self::assertSame('No', $capturedLogCommentData['encrypt']);
        self::assertSame('', $capturedLogCommentData['checksum_api']);
        self::assertSame('4', $capturedLogCommentData['version']);
        // Checksum should be a sha3-512 hash (128 hex chars)
        self::assertIsString($capturedLogCommentData['checksum']);
        self::assertSame(128, strlen($capturedLogCommentData['checksum']));
    }

    public function testRecordSetsEncryptFlagToYesWhenEventIsEncrypted(): void
    {
        $event = $this->createEvent(isEncrypted: true);

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

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        self::assertIsArray($capturedLogCommentData);
        self::assertSame('Yes', $capturedLogCommentData['encrypt']);
    }

    public function testRecordSetsEncryptFlagToNoWhenEventIsNotEncrypted(): void
    {
        $event = $this->createEvent(isEncrypted: false);

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

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        self::assertIsArray($capturedLogCommentData);
        self::assertSame('No', $capturedLogCommentData['encrypt']);
    }

    public function testRecordHandlesNullUserAndGroup(): void
    {
        $event = new Event(
            isEncrypted: false,
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

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        // Null user/group should be converted to empty string
        self::assertIsArray($capturedLogData);
        self::assertSame('', $capturedLogData['user']);
        self::assertSame('', $capturedLogData['groupname']);
    }

    public function testRecordHandlesNullPatientId(): void
    {
        $event = new Event(
            isEncrypted: false,
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

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        self::assertIsArray($capturedLogData);
        self::assertNull($capturedLogData['patient_id']);
        self::assertNull($capturedLogData['category']);
    }

    /**
     * @return ApiData
     */
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
        $event = $this->createEvent(api: $this->createApiData());

        $insertedTables = [];
        $this->connection->expects($this->exactly(3))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$insertedTables): int {
                $insertedTables[] = $table;
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('42');

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        self::assertSame(['log', 'log_comment_encrypt', 'api_log'], $insertedTables);
    }

    public function testRecordDoesNotInsertIntoApiLogWhenNoApiData(): void
    {
        $event = $this->createEvent(api: null);

        $insertedTables = [];
        $this->connection->expects($this->exactly(2))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$insertedTables): int {
                $insertedTables[] = $table;
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('1');

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        self::assertSame(['log', 'log_comment_encrypt'], $insertedTables);
    }

    public function testRecordApiLogContainsCorrectData(): void
    {
        $apiData = $this->createApiData();
        $event = $this->createEvent(api: $apiData);

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

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        self::assertNotNull($capturedApiLogData);
        self::assertSame('55', $capturedApiLogData['log_id']);
        self::assertSame(1, $capturedApiLogData['user_id']);
        self::assertSame(123, $capturedApiLogData['patient_id']);
        self::assertSame('GET', $capturedApiLogData['method']);
        self::assertSame('/api/patient', $capturedApiLogData['request']);
        self::assertSame('https://example.com/api/patient', $capturedApiLogData['request_url']);
        self::assertSame('{"foo":"bar"}', $capturedApiLogData['request_body']);
        self::assertSame('{"status":"ok"}', $capturedApiLogData['response']);
        self::assertSame('2026-03-11 12:00:00', $capturedApiLogData['created_time']);
        // IP address comes from collectIpAddresses()
        self::assertArrayHasKey('ip_address', $capturedApiLogData);
    }

    public function testRecordApiChecksumIsPopulatedWhenApiDataPresent(): void
    {
        $event = $this->createEvent(api: $this->createApiData());

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

        $sink = new LogTablesSink(conn: $this->connection);

        $sink->record($event);

        // API checksum should be a sha3-512 hash (128 hex chars)
        self::assertIsArray($capturedLogCommentData);
        self::assertIsString($capturedLogCommentData['checksum_api']);
        self::assertSame(128, strlen($capturedLogCommentData['checksum_api']));
    }
}
