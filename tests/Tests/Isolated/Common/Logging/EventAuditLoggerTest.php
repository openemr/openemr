<?php

/**
 * Isolated tests for EventAuditLogger sink dispatch
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging;

use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Logging\Audit\Event;
use OpenEMR\Common\Logging\Audit\SinkInterface;
use OpenEMR\Common\Logging\AuditConfig;
use OpenEMR\Common\Logging\BreakglassCheckerInterface;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\EventCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EventAuditLoggerTest extends TestCase
{
    private SessionInterface&MockObject $session;
    private AuditConfig $config;
    private BreakglassCheckerInterface&MockObject $breakglassChecker;
    private ClockInterface $clock;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->config = new AuditConfig(
            enabled: true,
            forceBreakglass: false,
            queryEvents: true,
            httpRequestEvents: true,
            enabledEventTypes: [],
        );
        $this->breakglassChecker = $this->createMock(BreakglassCheckerInterface::class);
        $this->clock = new FrozenClock(new \DateTimeImmutable('2026-01-15 10:30:00'));
    }

    public function testRecordLogItemPassesCorrectEventData(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame('patient-record-select', $event->event);
                self::assertSame('drsmith', $event->user);
                self::assertSame('physicians', $event->group);
                self::assertSame(12345, $event->patientId);
                self::assertSame(1, $event->success);
                self::assertSame('patient-record', $event->category);
                return true;
            }));

        $logger = new EventAuditLogger(
            sink: $sink,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'patient-record-select',
            user: 'drsmith',
            group: 'physicians',
            comments: 'Viewed patient record',
            patientId: 12345,
            category: 'patient-record',
        );
    }

    public function testRecordLogItemBase64EncodesComments(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertSame(base64_encode('Plain text'), $event->comments);
                return true;
            }));

        $logger = new EventAuditLogger(
            sink: $sink,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->recordLogItem(
            success: 1,
            event: 'login',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Plain text',
        );
    }

    public function testRecordLogItemConvertsNullPatientIdString(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record')
            ->with(self::callback(function (Event $event): bool {
                self::assertNull($event->patientId);
                return true;
            }));

        $logger = new EventAuditLogger(
            sink: $sink,
            session: $this->session,
            config: $this->config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        // Legacy code sometimes passes "NULL" as a string
        $logger->recordLogItem(
            success: 1,
            event: 'test-event',
            user: 'testuser',
            group: 'testgroup',
            comments: 'Test comments',
            patientId: 'NULL',
        );
    }

    /**
     * @return array<string, array{
     *   sql: string,
     *   enabled: bool,
     *   forceBreakglass: bool,
     *   isBreakglassUser: bool,
     *   expectLog: bool,
     * }>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function breakglassOverrideProvider(): array
    {
        return [
            'breakglass active: logs despite disabled config' => [
                'sql' => 'SELECT * FROM patient_data WHERE pid = 1',
                'enabled' => false,
                'forceBreakglass' => true,
                'isBreakglassUser' => true,
                'expectLog' => true,
            ],
            'breakglass config off: skips even if user in group' => [
                'sql' => 'SELECT * FROM patient_data WHERE pid = 1',
                'enabled' => false,
                'forceBreakglass' => false,
                'isBreakglassUser' => true,
                'expectLog' => false,
            ],
            'user not in breakglass group: skips even if config on' => [
                'sql' => 'SELECT * FROM patient_data WHERE pid = 1',
                'enabled' => false,
                'forceBreakglass' => true,
                'isBreakglassUser' => false,
                'expectLog' => false,
            ],
            'breakglass does not override select from unknown table' => [
                'sql' => 'SELECT * FROM some_unknown_table',
                'enabled' => false,
                'forceBreakglass' => true,
                'isBreakglassUser' => true,
                'expectLog' => false,
            ],
        ];
    }

    #[DataProvider('breakglassOverrideProvider')]
    public function testAuditSQLEventBreakglassOverride(
        string $sql,
        bool $enabled,
        bool $forceBreakglass,
        bool $isBreakglassUser,
        bool $expectLog,
    ): void {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($expectLog ? $this->once() : $this->never())
            ->method('record');

        $this->session->method('get')
            ->willReturnCallback(fn (string $key): ?string => match ($key) {
                'authUser' => 'testuser',
                'authProvider' => 'default',
                default => null,
            });

        $this->breakglassChecker->method('isBreakglassUser')
            ->willReturn($isBreakglassUser);

        $config = new AuditConfig(
            enabled: $enabled,
            forceBreakglass: $forceBreakglass,
            queryEvents: true,
            httpRequestEvents: false,
            enabledEventTypes: [EventCategory::PatientRecord, EventCategory::Other],
        );

        $logger = new EventAuditLogger(
            sink: $sink,
            session: $this->session,
            config: $config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->auditSQLEvent($sql, true);
    }

    /**
     * Statements emitted by the two database layers to control transactions.
     *
     * ADODB emits BEGIN/COMMIT/ROLLBACK; the DBAL logging middleware emits
     * START TRANSACTION/COMMIT/ROLLBACK plus SAVEPOINT statements for nested
     * transactions. Legacy call sites additionally toggle autocommit directly.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function transactionControlStatementProvider(): array
    {
        return [
            'adodb begin' => ['BEGIN'],
            'dbal start transaction' => ['START TRANSACTION'],
            'commit' => ['COMMIT'],
            'rollback' => ['ROLLBACK'],
            'dbal savepoint' => ['SAVEPOINT DOCTRINE_1'],
            'dbal release savepoint' => ['RELEASE SAVEPOINT DOCTRINE_1'],
            'dbal rollback to savepoint' => ['ROLLBACK TO SAVEPOINT DOCTRINE_1'],
            'autocommit off' => ['SET autocommit=0'],
            'autocommit on' => ['SET autocommit=1'],
            'autocommit with spaces' => ['SET AUTOCOMMIT = 1'],
            'trailing semicolon' => ['START TRANSACTION;'],
            'lowercase' => ['start transaction'],
            'leading whitespace' => ["  COMMIT\n"],
        ];
    }

    #[DataProvider('transactionControlStatementProvider')]
    public function testAuditSQLEventSkipsTransactionControl(string $sql): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->never())
            ->method('record');

        $this->session->method('get')
            ->willReturnCallback(fn (string $key): ?string => match ($key) {
                'authUser' => 'testuser',
                'authProvider' => 'default',
                default => null,
            });

        // Maximally permissive config plus breakglass, so nothing but the
        // transaction-control check itself can be responsible for the skip.
        $this->breakglassChecker->method('isBreakglassUser')
            ->willReturn(true);

        $config = new AuditConfig(
            enabled: true,
            forceBreakglass: true,
            queryEvents: true,
            httpRequestEvents: true,
            enabledEventTypes: EventCategory::cases(),
        );

        $logger = new EventAuditLogger(
            sink: $sink,
            session: $this->session,
            config: $config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->auditSQLEvent($sql, true);
    }

    /**
     * Guards the provider above against becoming vacuous: a statement that
     * merely starts with a transaction-control keyword must still be audited.
     */
    public function testAuditSQLEventStillLogsStatementsWithTransactionKeywordPrefix(): void
    {
        $sink = $this->createMock(SinkInterface::class);
        $sink->expects($this->once())
            ->method('record');

        $this->session->method('get')
            ->willReturnCallback(fn (string $key): ?string => match ($key) {
                'authUser' => 'testuser',
                'authProvider' => 'default',
                default => null,
            });

        $config = new AuditConfig(
            enabled: true,
            forceBreakglass: false,
            queryEvents: true,
            httpRequestEvents: true,
            enabledEventTypes: EventCategory::cases(),
        );

        $logger = new EventAuditLogger(
            sink: $sink,
            session: $this->session,
            config: $config,
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );

        $logger->auditSQLEvent('UPDATE patient_data SET committed = 1 WHERE pid = 1', true);
    }

    public function testPatientRecordEventUsesSessionPatientByDefault(): void
    {
        $events = [];
        $logger = $this->patientRecordLogger($events, '7');

        $logger->auditSQLEvent('SELECT * FROM patient_data WHERE pid = ?', true, [42]);

        self::assertCount(1, $events);
        self::assertSame(7, $events[0]->patientId);
    }

    public function testPatientRecordEventUsesRequestPatientOverSession(): void
    {
        $events = [];
        $logger = $this->patientRecordLogger($events, '7');

        $logger->setRequestPatientId(42);
        $logger->auditSQLEvent('SELECT * FROM patient_data WHERE pid = ?', true, [42]);

        self::assertCount(1, $events);
        self::assertSame(42, $events[0]->patientId);
    }

    public function testClearingRequestPatientFallsBackToSession(): void
    {
        $events = [];
        $logger = $this->patientRecordLogger($events, 7);

        $logger->setRequestPatientId(42);
        $logger->setRequestPatientId(null);
        $logger->auditSQLEvent('SELECT * FROM patient_data WHERE pid = ?', true, [42]);

        self::assertCount(1, $events);
        self::assertSame(7, $events[0]->patientId);
    }

    public function testNonPatientRecordEventIgnoresRequestPatient(): void
    {
        $events = [];
        $logger = $this->patientRecordLogger($events, '7');

        $logger->setRequestPatientId(42);
        $logger->auditSQLEvent('UPDATE users SET active = ? WHERE id = ?', true, [1, 3]);

        self::assertCount(1, $events);
        self::assertSame(0, $events[0]->patientId);
    }

    #[DataProvider('unusableSessionPidProvider')]
    public function testPatientRecordEventRecordsZeroWithoutUsableSessionPatient(mixed $sessionPid): void
    {
        $events = [];
        $logger = $this->patientRecordLogger($events, $sessionPid);

        $logger->auditSQLEvent('SELECT * FROM patient_data WHERE pid = ?', true, [42]);

        self::assertCount(1, $events);
        self::assertSame(0, $events[0]->patientId);
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unusableSessionPidProvider(): array
    {
        return [
            'unset' => [null],
            'empty string' => [''],
            'non-numeric string' => ['abc'],
            'negative int' => [-1],
        ];
    }

    #[DataProvider('nonPositivePidProvider')]
    public function testSetRequestPatientIdRejectsNonPositive(int $pid): void
    {
        $events = [];
        $logger = $this->patientRecordLogger($events, null);

        $this->expectException(\InvalidArgumentException::class);
        $logger->setRequestPatientId($pid);
    }

    /**
     * @return array<string, array{int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonPositivePidProvider(): array
    {
        return [
            'zero' => [0],
            'negative' => [-1],
        ];
    }

    /**
     * A logger with patient-record and security-administration events on that
     * appends every recorded event to $events.
     *
     * @param list<Event> $events
     */
    private function patientRecordLogger(array &$events, mixed $sessionPid): EventAuditLogger
    {
        $this->session->method('get')->willReturnCallback(
            fn (string $name): mixed => $name === 'pid' ? $sessionPid : null
        );
        $sink = $this->createMock(SinkInterface::class);
        $sink->method('record')->willReturnCallback(
            function (Event $event) use (&$events): void {
                $events[] = $event;
            }
        );

        return new EventAuditLogger(
            sink: $sink,
            session: $this->session,
            config: new AuditConfig(
                enabled: true,
                forceBreakglass: false,
                queryEvents: true,
                httpRequestEvents: false,
                enabledEventTypes: [EventCategory::PatientRecord, EventCategory::SecurityAdministration],
            ),
            breakglassChecker: $this->breakglassChecker,
            clock: $this->clock,
        );
    }
}
