<?php

/**
 * Task records: the storage behind the FHIR Task resource (#14521; D9 and C7
 * of the questionnaire-driven workflows epic, #13266). A task tracks work on
 * something else, its focus: a referral being worked, or a questionnaire
 * assigned to a patient.
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
            task.intent, task.priority, task.code, task.description, task.patient_id, patients.uuid AS puuid,
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
            foreach ($records as $row) {
                $record = [];
                foreach ($row as $column => $value) {
                    if (is_string($column)) {
                        $record[$column] = $value;
                    }
                }
                $processingResult->addData($this->createResultRecordFromDatabaseResult($record));
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
     * Saves a new task and returns its uuid.
     *
     * @param array<string, string|null> $fields the task's columns, uuids as strings
     */
    public function insert(array $fields, int $patientId, ?int $userId): string
    {
        $uuid = (new UuidRegistry(['table_name' => self::TABLE_NAME]))->createUuid();
        $columns = self::columns($fields);
        $sql = 'INSERT INTO task (uuid, patient_id, created_by, updated_by, version'
            . implode('', array_map(static fn(string $c): string => ", `$c`", array_keys($columns)))
            . ') VALUES (?, ?, ?, ?, 1' . str_repeat(', ?', count($columns)) . ')';
        QueryUtils::sqlInsert($sql, array_merge([$uuid, $patientId, $userId, $userId], array_values($columns)));
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * Updates a task and raises its version, only while its version is still the one expected
     * (If-Match) when one is given. False when no row matched: the task is gone, or changed since.
     *
     * @param array<string, string|null> $fields the task's columns, uuids as strings
     */
    public function update(string $uuid, array $fields, ?int $userId, ?int $expectedVersion = null): bool
    {
        $columns = self::columns($fields);
        $sql = 'UPDATE task SET updated_by = ?, version = version + 1'
            . implode('', array_map(static fn(string $c): string => ", `$c` = ?", array_keys($columns)))
            . ' WHERE uuid = ?' . ($expectedVersion === null ? '' : ' AND version = ?');
        $binds = array_merge([$userId], array_values($columns), [UuidRegistry::uuidToBytes($uuid)]);
        if ($expectedVersion !== null) {
            $binds[] = $expectedVersion;
        }
        QueryUtils::sqlStatementThrowException($sql, $binds);
        return QueryUtils::affectedRows() > 0;
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
        $writable = ['status', 'status_reason', 'business_status', 'intent', 'priority', 'code', 'description',
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
