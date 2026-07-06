<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Database\Middleware;

use Doctrine\DBAL\ParameterType;
use Firehed\DbalLogger\DbalLogger;
use OpenEMR\Common\Logging\AuditLoggerInterface;
use Throwable;

/**
 * DBAL middleware logger that delegates to EventAuditLogger for SQL auditing.
 *
 * This bridges the DBAL middleware system to the existing OpenEMR audit
 * infrastructure. EventAuditLogger reads session state (authUser, authProvider,
 * pid) at query time via its internally-held session reference.
 */
final class QueryAuditor implements DbalLogger
{
    private ?string $currentSql = null;

    /** @var array<int|string, mixed>|null */
    private ?array $currentParams = null;

    public function __construct(
        private readonly AuditLoggerInterface $auditLogger,
    ) {

    }
    /**
     * @param array<int|string, mixed>|null $params
     * @param array<int|string, ParameterType>|null $types
     */
    public function startQuery(string $sql, ?array $params = null, ?array $types = null): void
    {
        $this->currentSql = $sql;
        $this->currentParams = $params;
    }

    public function stopQuery(?Throwable $exception): void
    {
        if ($this->currentSql === null) {
            return;
        }

        $outcome = $exception === null;

        // Delegate to the existing EventAuditLogger infrastructure.
        $this->auditLogger->auditSQLEvent(
            $this->currentSql,
            $outcome,
            $this->currentParams,
        );

        $this->currentSql = null;
        $this->currentParams = null;
    }

    public function connect(): void
    {
        // No-op for now
    }

    public function disconnect(): void
    {
        // No-op for now
    }
}
