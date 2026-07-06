<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

interface AuditLoggerInterface
{
    public function auditSQLEvent(string $statement, bool $outcome, ?array $binds = null);
}
