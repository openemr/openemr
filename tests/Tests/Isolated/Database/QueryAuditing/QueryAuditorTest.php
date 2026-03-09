<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Database\QueryAuditing;

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Database\QueryAuditing\AuditEventType;
use OpenEMR\Database\QueryAuditing\AuditSettingsInterface;
use OpenEMR\Database\QueryAuditing\BreakglassCheckerInterface;
use OpenEMR\Database\QueryAuditing\CategoryResolver;
use OpenEMR\Database\QueryAuditing\QueryAuditor;
use OpenEMR\Database\QueryAuditing\QueryContextInterface;
use OpenEMR\Database\QueryAuditing\TableEventMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueryAuditor::class)]
final class QueryAuditorTest extends TestCase
{
    private AuditSettingsInterface&MockObject $settings;
    private BreakglassCheckerInterface&MockObject $breakglassChecker;
    private QueryContextInterface&MockObject $context;
    private EventAuditLogger&MockObject $auditLogger;
    private QueryAuditor $auditor;

    protected function setUp(): void
    {
        $this->settings = $this->createMock(AuditSettingsInterface::class);
        $this->breakglassChecker = $this->createMock(BreakglassCheckerInterface::class);
        $this->context = $this->createMock(QueryContextInterface::class);
        $this->auditLogger = $this->createMock(EventAuditLogger::class);

        $this->auditor = new QueryAuditor(
            $this->settings,
            $this->breakglassChecker,
            $this->context,
            new TableEventMap(),
            new CategoryResolver(),
            $this->auditLogger,
        );
    }

    public function testAuditSkipsWhenDisabledAndNotBreakglass(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(false);
        $this->settings->method('isBreakglassLoggingForced')->willReturn(false);
        $this->context->method('getUser')->willReturn('testuser');

        $this->auditLogger->expects(self::never())->method('recordLogItem');

        $this->auditor->audit('SELECT * FROM patient_data', null, true);
    }

    public function testAuditLogsWhenEnabled(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->settings->method('isQueryLoggingEnabled')->willReturn(true);
        $this->settings->method('isEventTypeEnabled')->willReturn(true);
        $this->context->method('getUser')->willReturn('testuser');
        $this->context->method('getGroup')->willReturn('admin');
        $this->context->method('getPatientId')->willReturn(123);

        $this->auditLogger->expects(self::once())
            ->method('recordLogItem')
            ->with(
                success: 1,
                event: 'patient-record-select',
                user: 'testuser',
                group: 'admin',
                comments: 'SELECT * FROM patient_data',
                patientId: 123,
                category: 'Patient Demographics',
            );

        $this->auditor->audit('SELECT * FROM patient_data', null, true);
    }

    public function testAuditSkipsLogTable(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->context->method('getUser')->willReturn('testuser');

        $this->auditLogger->expects(self::never())->method('recordLogItem');

        $this->auditor->audit('INSERT INTO log (event) VALUES (?)', ['test'], true);
    }

    public function testAuditSkipsSequencesTable(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->context->method('getUser')->willReturn('testuser');

        $this->auditLogger->expects(self::never())->method('recordLogItem');

        $this->auditor->audit('UPDATE sequences SET id = id + 1', null, true);
    }

    public function testAuditSkipsSelectOnUnknownTable(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->settings->method('isQueryLoggingEnabled')->willReturn(true);
        $this->context->method('getUser')->willReturn('testuser');

        $this->auditLogger->expects(self::never())->method('recordLogItem');

        $this->auditor->audit('SELECT * FROM some_unknown_table', null, true);
    }

    public function testAuditLogsInsertOnUnknownTable(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->settings->method('isEventTypeEnabled')->willReturn(true);
        $this->context->method('getUser')->willReturn('testuser');
        $this->context->method('getGroup')->willReturn('admin');
        $this->context->method('getPatientId')->willReturn(null);

        $this->auditLogger->expects(self::once())
            ->method('recordLogItem')
            ->with(
                success: 1,
                event: 'other-insert',
                user: 'testuser',
                group: 'admin',
                comments: "INSERT INTO custom_table (col) VALUES ('val')",
                patientId: null,
                category: null,
            );

        $this->auditor->audit("INSERT INTO custom_table (col) VALUES ('val')", null, true);
    }

    public function testAuditSkipsSelectWhenQueryLoggingDisabled(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->settings->method('isQueryLoggingEnabled')->willReturn(false);
        $this->settings->method('isBreakglassLoggingForced')->willReturn(false);
        $this->context->method('getUser')->willReturn('testuser');

        $this->auditLogger->expects(self::never())->method('recordLogItem');

        $this->auditor->audit('SELECT * FROM patient_data', null, true);
    }

    public function testAuditLogsBreakglassUserEvenWhenDisabled(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(false);
        $this->settings->method('isBreakglassLoggingForced')->willReturn(true);
        $this->settings->method('isQueryLoggingEnabled')->willReturn(false);
        $this->settings->method('isEventTypeEnabled')->willReturn(false);
        $this->breakglassChecker->method('isBreakglassUser')->willReturn(true);
        $this->context->method('getUser')->willReturn('emergency_user');
        $this->context->method('getGroup')->willReturn('admin');
        $this->context->method('getPatientId')->willReturn(456);

        $this->auditLogger->expects(self::once())
            ->method('recordLogItem')
            ->with(
                success: 1,
                event: 'patient-record-select',
                user: 'emergency_user',
                group: 'admin',
                comments: 'SELECT * FROM patient_data',
                patientId: 456,
                category: 'Patient Demographics',
            );

        $this->auditor->audit('SELECT * FROM patient_data', null, true);
    }

    public function testAuditFormatsParameters(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->settings->method('isEventTypeEnabled')->willReturn(true);
        $this->context->method('getUser')->willReturn('testuser');
        $this->context->method('getGroup')->willReturn('admin');
        $this->context->method('getPatientId')->willReturn(123);

        $this->auditLogger->expects(self::once())
            ->method('recordLogItem')
            ->with(
                success: 1,
                event: 'patient-record-update',
                user: 'testuser',
                group: 'admin',
                comments: "UPDATE patient_data SET fname = ? WHERE id = ? ('John','123')",
                patientId: 123,
                category: 'Patient Demographics',
            );

        $this->auditor->audit(
            'UPDATE patient_data SET fname = ? WHERE id = ?',
            ['John', '123'],
            true,
        );
    }

    public function testAuditRecordsFailure(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->settings->method('isEventTypeEnabled')->willReturn(true);
        $this->context->method('getUser')->willReturn('testuser');
        $this->context->method('getGroup')->willReturn('admin');
        $this->context->method('getPatientId')->willReturn(null);

        $this->auditLogger->expects(self::once())
            ->method('recordLogItem')
            ->with(
                success: 0,
                event: 'patient-record-insert',
                user: 'testuser',
                group: 'admin',
                comments: self::anything(),
                patientId: null,
                category: 'Patient Demographics',
            );

        $this->auditor->audit(
            'INSERT INTO patient_data (fname) VALUES (?)',
            ['John'],
            false,
        );
    }

    public function testAuditSkipsCountQueries(): void
    {
        $this->settings->method('isAuditingEnabled')->willReturn(true);
        $this->settings->method('isQueryLoggingEnabled')->willReturn(true);
        $this->context->method('getUser')->willReturn('testuser');

        $this->auditLogger->expects(self::never())->method('recordLogItem');

        $this->auditor->audit('SELECT COUNT(*) FROM patient_data', null, true);
    }
}
