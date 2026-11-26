<?php

/**
 * Builds and installs the per-table change-capture triggers.
 *
 * One AFTER INSERT / UPDATE / DELETE trigger per watched table, each writing a
 * single row into changefeed_log. The bodies are single-statement INSERTs (no
 * BEGIN...END), so they need no DELIMITER handling and install as one statement
 * each - which is why they are issued here rather than through the SQL install
 * parser (which splits on ';').
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ChangeFeed;

use OpenEMR\Common\Database\QueryUtils;
use RuntimeException;

final class TriggerManager
{
    public const LOG_TABLE = 'changefeed_log';

    private const TRIGGER_PREFIX = 'cf_';

    /** @var array<string, string> SQL event => trigger-name suffix */
    private const EVENTS = [
        'INSERT' => 'ai',
        'UPDATE' => 'au',
        'DELETE' => 'ad',
    ];

    /** @var list<WatchedResource> */
    private array $watched;

    /**
     * @param list<WatchedResource>|null $watched
     */
    public function __construct(?array $watched = null)
    {
        $this->watched = $watched ?? self::defaultWatched();
    }

    /**
     * The clinical-core tables the feed watches out of the box. Both map 1:1 to
     * a FHIR resource and carry a uuid column; both are hard-delete tables (no
     * activity/soft-delete column), so no softDeleteColumn is set.
     *
     * @return list<WatchedResource>
     */
    public static function defaultWatched(): array
    {
        return [
            new WatchedResource('patient_data', 'Patient', 'pid', 'uuid'),
            new WatchedResource('form_encounter', 'Encounter', 'id', 'uuid'),
        ];
    }

    /**
     * @return list<WatchedResource>
     */
    public function watchedResources(): array
    {
        return $this->watched;
    }

    /**
     * Install every trigger (dropping any pre-existing one first so re-enabling
     * the module is idempotent).
     */
    public function install(): void
    {
        $this->assertLogTableExists();
        foreach ($this->statements() as $statement) {
            QueryUtils::sqlStatementThrowException($statement);
        }
    }

    /**
     * Drop every trigger this module owns. Safe to run repeatedly.
     */
    public function uninstall(): void
    {
        foreach ($this->dropStatements() as $statement) {
            QueryUtils::sqlStatementThrowException($statement);
        }
    }

    /**
     * Ordered DROP-then-CREATE statements for every watched table. Pure - no
     * database access - so it can be asserted in a unit test.
     *
     * @return list<string>
     */
    public function statements(): array
    {
        $statements = [];
        foreach ($this->watched as $resource) {
            foreach (self::EVENTS as $event => $suffix) {
                $statements[] = $this->dropStatement($resource, $suffix);
                $statements[] = $this->createStatement($resource, $event, $suffix);
            }
        }

        return $statements;
    }

    /**
     * @return list<string>
     */
    public function dropStatements(): array
    {
        $statements = [];
        foreach ($this->watched as $resource) {
            foreach (self::EVENTS as $suffix) {
                $statements[] = $this->dropStatement($resource, $suffix);
            }
        }

        return $statements;
    }

    private function triggerName(WatchedResource $resource, string $suffix): string
    {
        return self::TRIGGER_PREFIX . $resource->table . '_' . $suffix;
    }

    private function dropStatement(WatchedResource $resource, string $suffix): string
    {
        return 'DROP TRIGGER IF EXISTS `' . $this->triggerName($resource, $suffix) . '`';
    }

    private function createStatement(WatchedResource $resource, string $event, string $suffix): string
    {
        $rowRef = $event === 'DELETE' ? 'OLD' : 'NEW';
        $name = $this->triggerName($resource, $suffix);
        $pkExpr = sprintf('CAST(%s.`%s` AS CHAR)', $rowRef, $resource->primaryKeyColumn);
        $uuidExpr = sprintf('LOWER(HEX(%s.`%s`))', $rowRef, $resource->uuidColumn);
        $opExpr = $this->operationExpression($resource, $event);

        return sprintf(
            'CREATE TRIGGER `%s` AFTER %s ON `%s` FOR EACH ROW '
            . 'INSERT INTO `%s` '
            . '(`resource_table`, `resource_type`, `row_pk`, `row_uuid`, `op`, `changed_at`) '
            . "VALUES ('%s', '%s', %s, %s, %s, NOW())",
            $name,
            $event,
            $resource->table,
            self::LOG_TABLE,
            $resource->table,
            $resource->fhirResourceType,
            $pkExpr,
            $uuidExpr,
            $opExpr
        );
    }

    /**
     * The op value written by a trigger. INSERT/DELETE are fixed; UPDATE is
     * normally 'update', but for a soft-delete table a transition of the
     * activity column to 0 is recorded as a delete.
     */
    private function operationExpression(WatchedResource $resource, string $event): string
    {
        if ($event === 'INSERT') {
            return "'" . ChangeOperation::Insert->value . "'";
        }
        if ($event === 'DELETE') {
            return "'" . ChangeOperation::Delete->value . "'";
        }

        // UPDATE
        if ($resource->softDeleteColumn === null) {
            return "'" . ChangeOperation::Update->value . "'";
        }

        return sprintf(
            "IF(NEW.`%s` = 0 AND (OLD.`%s` IS NULL OR OLD.`%s` <> 0), '%s', '%s')",
            $resource->softDeleteColumn,
            $resource->softDeleteColumn,
            $resource->softDeleteColumn,
            ChangeOperation::Delete->value,
            ChangeOperation::Update->value
        );
    }

    private function assertLogTableExists(): void
    {
        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS c FROM information_schema.tables '
            . 'WHERE table_schema = DATABASE() AND table_name = ?',
            'c',
            [self::LOG_TABLE]
        );

        if ((int) $count === 0) {
            throw new RuntimeException(
                'changefeed_log table is missing; install the Change Feed module before enabling it.'
            );
        }
    }
}
