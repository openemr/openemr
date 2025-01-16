<?php

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\TherapyGroupsValidator;

class TherapyGroupsService extends BaseService
{
    public const TABLE_NAME = 'therapy_groups';

    private TherapyGroupsValidator $therapyGroupValidator;

    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);

        $this->therapyGroupValidator = new TherapyGroupsValidator();
    }

    public function insert($data): ProcessingResult
    {
        $processingResult = $this->therapyGroupValidator->validate($data, TherapyGroupsValidator::DATABASE_INSERT_CONTEXT);

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        // DB-Query here
        $data = [
            'group_name' => $data['group_name'],
            'group_start_date' => $data['group_start_date'] ?? date('Y-m-d H:i:s'),
            'group_type' => $data['group_type'],
            'group_participation' => $data['group_participation'],
            'group_status' => $data['group_status'],
        ];

        $query = $this->buildInsertColumns($data);

        $results = sqlInsert("INSERT INTO " . $this::TABLE_NAME . " SET " . $query['set'], $query['bind']);

        if (!$results) {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        $processingResult->addData([
            'group_id' => $results,
        ]);

        return $processingResult;
    }

    public function getAll($search = [], $isAndCondition = true)
    {
        $newSearch = [];

        foreach ($search as $key => $value) {
            if (!$value instanceof ISearchField) {
                $newSearch[$key] = new StringSearchField($key, [$value], SearchModifier::EXACT);
            } else {
                $newSearch[$key] = $value;
            }
        }

        return $this->search($newSearch, $isAndCondition);
    }

    public function getAllForPatient(string $puuid)
    {
        $processingResult = new ProcessingResult();

        $puuidBytes = UuidRegistry::uuidToBytes($puuid);

        $sql = <<<SQL
SELECT tg.*
FROM therapy_groups tg
INNER JOIN therapy_groups_participants tgp ON tg.group_id = tgp.group_id
INNER JOIN patient_data pd on pd.pid = tgp.pid
WHERE pd.uuid = ?
SQL;

        $results = sqlStatement($sql,[$puuidBytes]);
        while ($row = sqlFetchArray($results)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }

        return $processingResult;
    }

    public function search($search = array(), $isAndCondition = true)
    {
        $sql = "SELECT * FROM " . $this::TABLE_NAME;
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }
        return $processingResult;
    }

    public function addPatient(string $puuid, string $id, array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        $data = [
            'pid' => $this->getIdByUuid(UuidRegistry::uuidToBytes($puuid), 'patient_data', "pid"),
            'group_id' => $id,
        ];

        $query = $this->buildInsertColumns($data);

        $sql = "INSERT INTO therapy_groups_participants SET ";
        $sql .= "pid = ?, group_id = ?, group_patient_status = ?";

        $results = sqlInsert($sql, [
            $data['pid'],
            $data['group_id'],
            $data['group_patient_status'] ?? 10,
        ]);

        if ($results) {
            $processingResult->addData([
                'id' => $results,
            ]);
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }

    public function removePatient(string $puuid, string $id): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        $data = [
            'pid' => $this->getIdByUuid(UuidRegistry::uuidToBytes($puuid), 'patient_data', "pid"),
            'group_id' => $id,
        ];

        $query = $this->buildInsertColumns($data);

        $sql = "DELETE FROM therapy_groups_participants WHERE ";
        $sql .= "pid = ? AND group_id = ?";

        $results = sqlStatement($sql, [
            $data['pid'],
            $data['group_id'],
        ]);

        if ($results) {
            $processingResult->addData([
                'id' => $results,
            ]);
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }
}
