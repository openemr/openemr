<?php

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

interface AuditLoggerInterface
{
    /**
     * @param ?mixed[] $binds
     */
    public function auditSQLEvent(string $statement, bool $outcome, ?array $binds = null): void;
}
