<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Database;

use Firehed\DbalLogger\DbalLogger;
use OpenEMR\Common\Logging\EventAuditLogger;
use Throwable;

class AuditLoggerMiddleware implements DbalLogger
{
    private ?string $sql = null;
    private ?array $params = null;

    public function __construct(private EventAuditLogger $eal)
    {
    }

    public function startQuery(string $sql, ?array $params = null, ?array $types = null): void
    {
        $this->sql = $sql;
        $this->params = $params;
    }

    public function stopQuery(?Throwable $exception): void
    {
        assert($this->sql !== null);

        $this->eal->auditSQLEvent(
            $this->sql,
            $exception !== null,
            $this->params,
        );

        // Reset for next query.
        $this->sql = null;
        $this->params = null;
    }

    public function connect(): void
    {
        // No-op.
    }

    public function disconnect(): void
    {
        // No-op.
    }
}
