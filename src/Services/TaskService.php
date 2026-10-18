<?php

/**
 * Task records: the storage behind the FHIR Task resource (#14521; D9 and C7
 * of the questionnaire-driven workflows epic, #13266). A task tracks work on
 * something else, its focus: a referral being worked, or a questionnaire
 * assigned to a patient. Its outputs, in task_output, are what the work
 * produced: the filled-in questionnaire, a result, a service delivered.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryPagination;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchConfigClauseBuilder;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchQueryConfig;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class TaskService extends BaseService
{
    public const TABLE_NAME = 'task';

    /** Each task with its patient's uuid, so a search can name the patient by uuid. */
    private const SELECT = "SELECT task.id, task.uuid, task.status, task.status_reason, task.business_status,
            task.intent, task.priority, task.code, task.code_system, task.description, task.patient_id, patients.uuid AS puuid,
            task.focus_type, task.focus_uuid, task.owner_type, task.owner_uuid,
            task.requester_type, task.requester_uuid, task.part_of_uuid,
            task.authored_on, task.last_modified, task.created_by, task.updated_by, task.version
        FROM task
        LEFT JOIN patient_data AS patients ON patients.pid = task.patient_id";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * @return string[]
     */
    public function getUuidFields(): array
    {
        return ['uuid', 'puuid', 'focus_uuid', 'owner_uuid', 'requester_uuid', 'part_of_uuid'];
    }

    /**
     * @param array<string, ISearchField> $search
     * @param mixed $isAndCondition
     * @param ?SearchQueryConfig $config paging (_count, _offset); with _count the query reads one row past the
     *                                   page so the result knows whether there is more
     */
    public function search(array $search, $isAndCondition = true, ?SearchQueryConfig $config = null): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $pagination = $config?->getPagination();
        if ($pagination !== null) {
            $processingResult->setPagination($pagination);
        }
        try {
            $where = FhirSearchWhereClauseBuilder::build($search, $isAndCondition !== false);
            $records = QueryUtils::fetchRecords(
                'SELECT tasks.* FROM (' . self::SELECT . ') AS tasks ' . $where->getFragment() . ' ORDER BY tasks.id '
                    . self::pagingClause($pagination),
                $where->getBoundValues()
            );
            $outputs = self::outputsOf(array_map(static fn(array $row): mixed => $row['id'] ?? null, $records));
            foreach ($records as $row) {
                $record = [];
                foreach ($row as $column => $value) {
                    if (is_string($column)) {
                        $record[$column] = $value;
                    }
                }
                $record = $this->createResultRecordFromDatabaseResult($record);
                $id = $row['id'] ?? null;
                $record['outputs'] = is_numeric($id) ? ($outputs[(int)$id] ?? []) : [];
                $processingResult->addData($record);
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $processingResult;
    }

    /**
     * One task as search() returns it, by uuid, or null.
     *
     * @return ?array<string, mixed>
     */
    public function getOne(string $uuid): ?array
    {
        $records = $this->search(['_id' => new TokenSearchField('uuid', [$uuid], true)])->getData();
        $record = is_array($records) ? ($records[0] ?? null) : null;
        return is_array($record) ? self::stringKeyed($record) : null;
    }

    /**
     * What a write needs to know about a stored task: its patient, status, parent and version.
     * Null for no such task, including an id that isn't a uuid at all.
     *
     * @return ?array{id: int, patient_id: ?int, status: string, part_of_uuid: ?string, version: int}
     */
    public function getForWrite(string $uuid): ?array
    {
        if (!UuidRegistry::isValidStringUUID($uuid)) {
            return null;
        }
        $row = QueryUtils::querySingleRow(
            'SELECT id, patient_id, status, part_of_uuid, version FROM task WHERE uuid = ?',
            [UuidRegistry::uuidToBytes($uuid)]
        );
        if (!is_array($row) || !is_numeric($row['id'] ?? null)) {
            return null;
        }
        $parent = $row['part_of_uuid'] ?? null;
        return [
            'id' => (int)$row['id'],
            'patient_id' => is_numeric($row['patient_id'] ?? null) ? (int)$row['patient_id'] : null,
            'status' => is_string($row['status'] ?? null) ? $row['status'] : '',
            'part_of_uuid' => is_string($parent) && $parent !== '' ? UuidRegistry::uuidToString($parent) : null,
            'version' => is_numeric($row['version'] ?? null) ? (int)$row['version'] : 1,
        ];
    }

    /**
     * Saves a new task with its outputs and returns its uuid.
     *
     * @param array<string, string|null> $fields the task's columns, uuids as strings
     * @param list<array<string, string|null>> $outputs task_output rows in order, value_uuid as a string
     */
    public function insert(array $fields, int $patientId, ?int $userId, array $outputs = []): string
    {
        $uuid = (new UuidRegistry(['table_name' => self::TABLE_NAME]))->createUuid();
        $columns = self::columns($fields);
        $sql = 'INSERT INTO task (uuid, patient_id, created_by, updated_by, version'
            . implode('', array_map(static fn(string $c): string => ", `$c`", array_keys($columns)))
            . ') VALUES (?, ?, ?, ?, 1' . str_repeat(', ?', count($columns)) . ')';
        QueryUtils::inTransaction(static function () use ($sql, $uuid, $patientId, $userId, $columns, $outputs): void {
            $taskId = QueryUtils::sqlInsert($sql, array_merge([$uuid, $patientId, $userId, $userId], array_values($columns)));
            self::insertOutputs($taskId, $outputs);
        });
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * Updates a task and raises its version, only while its version is still the one expected
     * (If-Match) when one is given. False when no row matched: the task is gone, or changed since.
     * Outputs, when given, replace the task's own in the same transaction; null leaves them.
     *
     * @param array<string, string|null> $fields the task's columns, uuids as strings
     * @param ?list<array<string, string|null>> $outputs task_output rows in order, value_uuid as a string
     */
    public function update(string $uuid, array $fields, ?int $userId, ?int $expectedVersion = null, ?array $outputs = null): bool
    {
        $columns = self::columns($fields);
        $sql = 'UPDATE task SET updated_by = ?, version = version + 1'
            . implode('', array_map(static fn(string $c): string => ", `$c` = ?", array_keys($columns)))
            . ' WHERE uuid = ?' . ($expectedVersion === null ? '' : ' AND version = ?');
        $binds = array_merge([$userId], array_values($columns), [UuidRegistry::uuidToBytes($uuid)]);
        if ($expectedVersion !== null) {
            $binds[] = $expectedVersion;
        }
        return QueryUtils::inTransaction(static function () use ($sql, $binds, $uuid, $outputs): bool {
            QueryUtils::sqlStatementThrowException($sql, $binds);
            // The UPDATE holds the task's row until the transaction ends, so a rival update waits
            // and then finds the version moved on, rather than mixing its outputs with these.
            if (QueryUtils::affectedRows() < 1) {
                return false;
            }
            if ($outputs !== null) {
                $taskId = QueryUtils::fetchSingleValue('SELECT id FROM task WHERE uuid = ?', 'id', [UuidRegistry::uuidToBytes($uuid)]);
                if (!is_numeric($taskId)) {
                    throw new \RuntimeException('The task just updated has no id: ' . $uuid);
                }
                QueryUtils::sqlStatementThrowException('DELETE FROM task_output WHERE task_id = ?', [$taskId]);
                self::insertOutputs((int)$taskId, $outputs);
            }
            return true;
        });
    }

    /**
     * @param list<array<string, string|null>> $outputs
     */
    private static function insertOutputs(int $taskId, array $outputs): void
    {
        foreach ($outputs as $seq => $output) {
            $valueUuid = $output['value_uuid'] ?? null;
            QueryUtils::sqlInsert(
                'INSERT INTO task_output (task_id, seq, type_system, type_code, type_text, value_type, value_uuid) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $taskId,
                    $seq,
                    $output['type_system'] ?? null,
                    $output['type_code'] ?? null,
                    $output['type_text'] ?? null,
                    $output['value_type'] ?? '',
                    is_string($valueUuid) ? UuidRegistry::uuidToBytes($valueUuid) : '',
                ]
            );
        }
    }

    /**
     * The outputs of these tasks in order, by task id, value_uuid as a string.
     *
     * @param array<mixed> $taskIds
     * @return array<int, list<array<string, string|null>>>
     */
    private static function outputsOf(array $taskIds): array
    {
        $ids = array_values(array_unique(array_map(intval(...), array_filter($taskIds, is_numeric(...)))));
        if ($ids === []) {
            return [];
        }
        $rows = QueryUtils::fetchRecords(
            'SELECT task_id, type_system, type_code, type_text, value_type, value_uuid FROM task_output WHERE task_id IN ('
                . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY task_id, seq, id',
            $ids
        );
        $outputs = [];
        foreach ($rows as $row) {
            $taskId = $row['task_id'] ?? null;
            $valueUuid = $row['value_uuid'] ?? null;
            if (!is_numeric($taskId) || !is_string($valueUuid)) {
                continue;
            }
            $output = ['value_uuid' => UuidRegistry::uuidToString($valueUuid)];
            foreach (['type_system', 'type_code', 'type_text', 'value_type'] as $column) {
                $value = $row[$column] ?? null;
                $output[$column] = is_string($value) ? $value : null;
            }
            $outputs[(int)$taskId][] = $output;
        }
        return $outputs;
    }

    /**
     * The writable columns, uuid columns as bytes. A write names only these, so a field that
     * isn't one of them can't reach the SQL.
     *
     * @param array<string, string|null> $fields
     * @return array<string, string|null>
     */
    private static function columns(array $fields): array
    {
        $writable = ['status', 'status_reason', 'business_status', 'intent', 'priority', 'code', 'code_system', 'description',
            'focus_type', 'focus_uuid', 'owner_type', 'owner_uuid', 'requester_type', 'requester_uuid', 'part_of_uuid', 'authored_on'];
        $columns = [];
        foreach ($writable as $column) {
            if (array_key_exists($column, $fields)) {
                $value = $fields[$column];
                $columns[$column] = $value !== null && str_ends_with($column, '_uuid') ? UuidRegistry::uuidToBytes($value) : $value;
            }
        }
        return $columns;
    }

    /**
     * @param array<mixed> $record
     * @return array<string, mixed>
     */
    private static function stringKeyed(array $record): array
    {
        $out = [];
        foreach ($record as $key => $value) {
            if (is_string($key)) {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * The page as SQL: SearchConfigClauseBuilder's LIMIT and OFFSET for _count (with or without _offset).
     * It writes nothing when there is no limit, so _offset alone gets its own clause here; MySQL takes
     * an OFFSET only after a LIMIT, hence the largest one.
     */
    private static function pagingClause(?QueryPagination $pagination): string
    {
        $clause = SearchConfigClauseBuilder::buildQueryPaginationClause($pagination);
        $offset = $pagination?->getCurrentOffsetId();
        if ($clause === '' && is_numeric($offset) && (int)$offset > 0) {
            $clause = 'LIMIT 18446744073709551615 OFFSET ' . (int)$offset;
        }
        return $clause;
    }
}
