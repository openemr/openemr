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
use OpenEMR\Common\Logging\Audit\AuditChain;
use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\LogTablesSink;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type ApiData from Event
 */
class LogTablesSinkTest extends TestCase
{
    private const string CHAIN_KEY = 'audit-chain-test-key';

    private Connection&MockObject $connection;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
    }

    /**
     * @param ?ApiData $api
     */
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
            logFrom: 'open-emr',
            menuItemId: null,
            ccdaDocId: null,
            api: $api,
        );
    }

    /**
     * Narrow a captured insert value (mixed) to its string form for
     * checksum recomputation, mirroring how PHP string concatenation
     * treats the raw values inside LogTablesSink.
     *
     * @param array<array-key, mixed> $data
     */
    private static function field(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        return is_scalar($value) ? (string)$value : '';
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
                    self::assertSame('', $data['crt_user'], 'crt_user is vestigial and always empty');
                    self::assertSame('open-emr', $data['log_from']);
                } elseif ($callCount === 2) {
                    self::assertSame('log_comment_encrypt', $table);
                }

                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('42');

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();
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

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

        self::assertIsArray($capturedLogCommentData);
        self::assertSame('99', $capturedLogCommentData['log_id']);
        self::assertSame('No', $capturedLogCommentData['encrypt']);
        self::assertSame('', $capturedLogCommentData['checksum_api']);
        self::assertSame('5', $capturedLogCommentData['version']);
        // Checksum should be a sha3-512 hash (128 hex chars)
        self::assertIsString($capturedLogCommentData['checksum']);
        self::assertSame(128, strlen($capturedLogCommentData['checksum']));
        self::assertSame(
            AuditChain::link(AuditChain::genesis(), '99', $capturedLogCommentData['checksum'], self::CHAIN_KEY),
            $capturedLogCommentData['chain'],
        );
    }

    public function testRecordSetsEncryptFlagToNo(): void
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

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

        self::assertIsArray($capturedLogCommentData);
        self::assertSame('No', $capturedLogCommentData['encrypt']);
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

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

        // Null user/group should remain null
        self::assertIsArray($capturedLogData);
        self::assertNull($capturedLogData['user']);
        self::assertNull($capturedLogData['groupname']);
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

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

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
            'client_id' => 'test-client-id',
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

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

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

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

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

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

        self::assertNotNull($capturedApiLogData);
        self::assertSame('55', $capturedApiLogData['log_id']);
        self::assertSame(1, $capturedApiLogData['user_id']);
        self::assertSame('test-client-id', $capturedApiLogData['client_id']);
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

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

        // API checksum should be a sha3-512 hash (128 hex chars)
        self::assertIsArray($capturedLogCommentData);
        self::assertIsString($capturedLogCommentData['checksum_api']);
        self::assertSame(128, strlen($capturedLogCommentData['checksum_api']));
    }

    /**
     * The stored api checksum must be reproducible by the exact field
     * concatenation the audit log tamper report uses
     * (interface/reports/audit_log_tamper_report.php). If the sink's field
     * order and the tamper report's recompute ever diverge, every new
     * api_log row will be reported as tampered.
     */
    public function testRecordApiChecksumMatchesTamperReportRecompute(): void
    {
        $event = $this->createEvent(api: $this->createApiData());

        $capturedLogCommentData = null;
        $capturedApiLogData = null;
        $this->connection->expects($this->exactly(3))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogCommentData, &$capturedApiLogData): int {
                if ($table === 'log_comment_encrypt') {
                    $capturedLogCommentData = $data;
                }
                if ($table === 'api_log') {
                    $capturedApiLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('55');

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

        self::assertIsArray($capturedLogCommentData);
        self::assertIsArray($capturedApiLogData);

        // mirror the tamper report's recompute, field by field
        $recompute = hash(
            'sha3-512',
            self::field($capturedApiLogData, 'log_id')
            . self::field($capturedApiLogData, 'user_id')
            . self::field($capturedApiLogData, 'client_id')
            . self::field($capturedApiLogData, 'patient_id')
            . self::field($capturedApiLogData, 'ip_address')
            . self::field($capturedApiLogData, 'method')
            . self::field($capturedApiLogData, 'request')
            . self::field($capturedApiLogData, 'request_url')
            . self::field($capturedApiLogData, 'request_body')
            . self::field($capturedApiLogData, 'response')
            . self::field($capturedApiLogData, 'created_time')
        );

        self::assertSame($recompute, $capturedLogCommentData['checksum_api']);
    }

    /**
     * Rows logged by callers that do not provide client_id (the state of the
     * world before the column existed, and any older recordLogItem callers)
     * must produce a checksum identical to the legacy formula that omitted
     * client_id entirely -- the empty-string fallback contributes nothing to
     * the hash. This is what keeps pre-upgrade audit rows verifiable in the
     * tamper report.
     */
    public function testRecordApiChecksumWithoutClientIdMatchesLegacyFormula(): void
    {
        $apiData = $this->createApiData();
        unset($apiData['client_id']);
        $event = $this->createEvent(api: $apiData);

        $capturedLogCommentData = null;
        $capturedApiLogData = null;
        $this->connection->expects($this->exactly(3))
            ->method('insert')
            ->willReturnCallback(function (string $table, array $data) use (&$capturedLogCommentData, &$capturedApiLogData): int {
                if ($table === 'log_comment_encrypt') {
                    $capturedLogCommentData = $data;
                }
                if ($table === 'api_log') {
                    $capturedApiLogData = $data;
                }
                return 1;
            });

        $this->connection->method('lastInsertId')->willReturn('55');

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);

        $sink->record($event);
        $sink->flush();

        self::assertIsArray($capturedLogCommentData);
        self::assertIsArray($capturedApiLogData);
        self::assertSame('', $capturedApiLogData['client_id'], 'missing client_id must be stored as empty string');

        // the legacy checksum formula, exactly as it existed before client_id
        $legacyRecompute = hash(
            'sha3-512',
            self::field($capturedApiLogData, 'log_id')
            . self::field($capturedApiLogData, 'user_id')
            . self::field($capturedApiLogData, 'patient_id')
            . self::field($capturedApiLogData, 'ip_address')
            . self::field($capturedApiLogData, 'method')
            . self::field($capturedApiLogData, 'request')
            . self::field($capturedApiLogData, 'request_url')
            . self::field($capturedApiLogData, 'request_body')
            . self::field($capturedApiLogData, 'response')
            . self::field($capturedApiLogData, 'created_time')
        );

        self::assertSame($legacyRecompute, $capturedLogCommentData['checksum_api']);
    }

    public function testARequestWritesItsRowsInOneTransaction(): void
    {
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit');
        $this->connection->method('lastInsertId')->willReturnOnConsecutiveCalls('10', '11');
        $this->connection->method('fetchAssociative')->willReturn(['chain' => \OpenEMR\Common\Logging\Audit\AuditChain::genesis()]);
        $checksums = [];
        $chains = [];
        $this->connection->method('insert')->willReturnCallback(
            function (string $table, array $data) use (&$checksums, &$chains): int {
                if ($table === 'log_comment_encrypt') {
                    $checksums[] = $data['checksum'];
                    $chains[] = $data['chain'];
                }
                return 1;
            }
        );

        $anchorUpdates = 0;
        $this->connection->method('executeStatement')->willReturnCallback(
            function (string $sql) use (&$anchorUpdates): int {
                if (str_contains($sql, 'UPDATE log_chain_anchor')) {
                    $anchorUpdates++;
                }

                return 1;
            }
        );

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);
        $sink->record($this->createEvent());
        $sink->record($this->createEvent());
        $sink->flush();

        self::assertSame(1, $anchorUpdates);
        self::assertCount(2, $checksums);
        self::assertIsString($checksums[0]);
        self::assertIsString($checksums[1]);
        self::assertIsString($chains[0]);
        self::assertCount(2, $chains);
        self::assertSame(
            \OpenEMR\Common\Logging\Audit\AuditChain::link(\OpenEMR\Common\Logging\Audit\AuditChain::genesis(), '10', $checksums[0], self::CHAIN_KEY),
            $chains[0],
        );
        self::assertSame(
            \OpenEMR\Common\Logging\Audit\AuditChain::link($chains[0], '11', $checksums[1], self::CHAIN_KEY),
            $chains[1],
        );
    }


    public function testAFailedFlushKeepsTheBufferedRow(): void
    {
        $event = $this->createEvent();
        $driver = \Doctrine\DBAL\Driver\PDO\Exception::new(new \PDOException('missing', 1146));
        $this->connection->expects($this->exactly(2))
            ->method('beginTransaction')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \Doctrine\DBAL\Exception\ConnectionException($driver, null)),
                true,
            );
        $this->connection->expects($this->exactly(2))->method('insert')->willReturn(1);
        $this->connection->method('lastInsertId')->willReturn('7');
        $this->connection->method('fetchAssociative')->willReturn(['chain' => AuditChain::genesis()]);
        $this->connection->method('isTransactionActive')->willReturn(false);

        $sink = new LogTablesSink(conn: $this->connection, chainKey: self::CHAIN_KEY);
        $sink->record($event);
        try {
            $sink->flush();
            self::fail('A missing audit table must not look like a successful write');
        } catch (\RuntimeException) {
        }
        $sink->flush();
    }
}
