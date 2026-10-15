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
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchConfigClauseBuilder;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchQueryConfig;
use OpenEMR\Validators\ProcessingResult;

class TaskService extends BaseService
{
    public const TABLE_NAME = 'task';

    /** Each task with its patient's uuid, so a search can name the patient by uuid. */
    private const SELECT = "SELECT task.id, task.uuid, task.status, task.status_reason, task.business_status,
            task.intent, task.priority, task.code, task.description, task.patient_id, patients.uuid AS puuid,
            task.focus_type, task.focus_uuid, task.owner_type, task.owner_uuid,
            task.requester_type, task.requester_uuid, task.part_of_uuid,
            task.authored_on, task.last_modified, task.created_by, task.updated_by
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
