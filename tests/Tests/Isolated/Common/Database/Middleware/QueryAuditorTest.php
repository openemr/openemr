<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Database\Middleware;

use OpenEMR\Common\Database\Middleware\QueryAuditor;
use OpenEMR\Common\Logging\AuditLoggerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

class QueryAuditorTest extends TestCase
{
    #[DataProvider('outcomeProvider')]
    public function testStopQueryDelegatesToAuditLoggerWithOutcome(
        ?Throwable $exception,
        bool $expectedOutcome,
    ): void {
        $sql = 'SELECT * FROM patient_data WHERE pid = ?';
        $params = [42];

        $auditLogger = $this->createMock(AuditLoggerInterface::class);
        $auditLogger->expects(self::once())
            ->method('auditSQLEvent')
            ->with($sql, $expectedOutcome, $params);

        $auditor = new QueryAuditor($auditLogger);
        $auditor->startQuery($sql, $params);
        $auditor->stopQuery($exception);
    }

    public function testStopQueryPassesNullParamsThrough(): void
    {
        $sql = 'SELECT 1';

        $auditLogger = $this->createMock(AuditLoggerInterface::class);
        $auditLogger->expects(self::once())
            ->method('auditSQLEvent')
            ->with($sql, true, null);

        $auditor = new QueryAuditor($auditLogger);
        $auditor->startQuery($sql, null);
        $auditor->stopQuery(null);
    }

    public function testStopQueryWithoutStartQueryDoesNotAudit(): void
    {
        $auditLogger = $this->createMock(AuditLoggerInterface::class);
        $auditLogger->expects(self::never())
            ->method('auditSQLEvent');

        $auditor = new QueryAuditor($auditLogger);
        $auditor->stopQuery(null);
    }

    public function testStateResetsSoRepeatedStopQueryAuditsOnlyOnce(): void
    {
        $sql = 'UPDATE patient_data SET fname = ? WHERE pid = ?';
        $params = ['Jane', 7];

        $auditLogger = $this->createMock(AuditLoggerInterface::class);
        $auditLogger->expects(self::once())
            ->method('auditSQLEvent')
            ->with($sql, true, $params);

        $auditor = new QueryAuditor($auditLogger);
        $auditor->startQuery($sql, $params);
        $auditor->stopQuery(null);
        // A second stopQuery with no intervening startQuery must be a no-op
        // because the auditor cleared its recorded statement.
        $auditor->stopQuery(null);
    }

    public function testConnectAndDisconnectDoNotAudit(): void
    {
        $auditLogger = $this->createMock(AuditLoggerInterface::class);
        $auditLogger->expects(self::never())
            ->method('auditSQLEvent');

        $auditor = new QueryAuditor($auditLogger);
        $auditor->connect();
        $auditor->disconnect();
    }

    /**
     * @return array<string, array{?Throwable, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function outcomeProvider(): array
    {
        return [
            'successful query (no exception) audits as true' => [null, true],
            'failed query (with exception) audits as false' => [new RuntimeException('boom'), false],
        ];
    }
}
