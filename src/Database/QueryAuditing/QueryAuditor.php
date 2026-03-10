<?php

/**
 * SQL query auditor implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

use OpenEMR\Common\Logging\EventAuditLogger;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\SqlParser\Utils\Query;
use PhpMyAdmin\SqlParser\Utils\StatementType;

/**
 * Audits SQL queries for compliance logging.
 *
 * This class coordinates query parsing, filtering, classification, and
 * logging. It handles recursion prevention internally and supports
 * breakglass user logging.
 *
 * Refactored from EventAuditLogger::auditSQLEvent().
 */
final class QueryAuditor implements QueryAuditorInterface
{
    /**
     * Tables that should never be audited (would cause recursion).
     */
    private const SKIP_TABLES = [
        'log',
        'log_comment_encrypt',
        'api_log',
        'sequences',
    ];

    /**
     * Statement types that map to query operations.
     */
    private const QUERY_TYPE_MAP = [
        StatementType::Select->value => 'select',
        StatementType::Insert->value => 'insert',
        StatementType::Update->value => 'update',
        StatementType::Delete->value => 'delete',
        StatementType::Replace->value => 'replace',
    ];

    private bool $isAuditing = false;

    public function __construct(
        private readonly AuditSettingsInterface $settings,
        private readonly BreakglassCheckerInterface $breakglassChecker,
        private readonly QueryContextInterface $context,
        private readonly TableEventMap $tableEventMap,
        private readonly CategoryResolver $categoryResolver,
        private readonly EventAuditLogger $auditLogger,
    ) {
    }

    public function audit(string $sql, ?array $params, bool $success): void
    {
        // Recursion guard - set early to prevent any nested queries (like
        // breakglass checks) from triggering audit recursion
        if ($this->isAuditing) {
            return;
        }
        $this->isAuditing = true;

        try {
            $this->doAudit($sql, $params, $success);
        } finally {
            $this->isAuditing = false;
        }
    }

    private function doAudit(string $sql, ?array $params, bool $success): void
    {
        $user = $this->context->getUser();
        $isBreakglass = $this->shouldForceLog($user);

        // Check if auditing is enabled
        if (!$this->settings->isAuditingEnabled() && !$isBreakglass) {
            return;
        }

        // Parse the SQL
        $parser = new Parser($sql);
        $statement = $parser->statements[0] ?? null;

        if ($statement === null) {
            return;
        }

        // Get statement type and tables
        $flags = Query::getFlags($statement);
        $statementType = $flags->queryType;

        if ($statementType === null) {
            return;
        }

        $tables = Query::getTables($statement);

        // Skip queries on audit tables (recursion prevention)
        if ($this->shouldSkipTables($tables)) {
            return;
        }

        // Skip COUNT queries
        if ($statementType === StatementType::Select && $flags->isCount) {
            return;
        }

        $queryType = self::QUERY_TYPE_MAP[$statementType->value] ?? null;
        if ($queryType === null) {
            return;
        }

        // For SELECT queries, check if query logging is enabled
        if ($statementType === StatementType::Select) {
            if (!$this->settings->isQueryLoggingEnabled() && !$isBreakglass) {
                return;
            }
        }

        // Map tables to event type
        $eventType = $this->tableEventMap->getEventType($tables);

        // Skip SELECT on unknown tables
        if ($statementType === StatementType::Select && $eventType === AuditEventType::Other) {
            return;
        }

        // Check if this event type is enabled
        if (!$this->settings->isEventTypeEnabled($eventType) && !$isBreakglass) {
            return;
        }

        // Resolve category
        $primaryTable = $this->tableEventMap->getPrimaryTable($tables) ?? '';
        $category = $this->categoryResolver->resolve($eventType, $primaryTable, $sql);

        // Build the event string
        $event = $eventType->value . '-' . $queryType;

        // Format comments (SQL + params)
        $comments = $this->formatComments($sql, $params);

        // Get patient ID for patient-record events
        $patientId = null;
        if ($eventType === AuditEventType::PatientRecord) {
            $patientId = $this->context->getPatientId();
        }

        // Write the audit record
        $this->auditLogger->recordLogItem(
            success: $success ? 1 : 0,
            event: $event,
            user: $user ?? '',
            group: $this->context->getGroup() ?? '',
            comments: $comments,
            patientId: $patientId,
            category: $category?->value,
        );
    }

    /**
     * Check if any of the tables should skip auditing.
     *
     * @param string[] $tables
     */
    private function shouldSkipTables(array $tables): bool
    {
        foreach ($tables as $table) {
            $normalized = trim($table, '`"\'');
            if (in_array($normalized, self::SKIP_TABLES, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if logging should be forced for this user.
     */
    private function shouldForceLog(?string $user): bool
    {
        if ($user === null || $user === '') {
            return false;
        }

        if (!$this->settings->isBreakglassLoggingForced()) {
            return false;
        }

        return $this->breakglassChecker->isBreakglassUser($user);
    }

    /**
     * Format the SQL and parameters for logging.
     *
     * @param array<int|string, mixed>|null $params
     */
    private function formatComments(string $sql, ?array $params): string
    {
        if ($params === null || $params === []) {
            return $sql;
        }

        $formatted = [];
        foreach ($params as $value) {
            $stringValue = is_scalar($value) || $value === null
                ? (string) $value
                : gettype($value);
            $formatted[] = "'" . addslashes($stringValue) . "'";
        }

        return $sql . ' (' . implode(',', $formatted) . ')';
    }
}
