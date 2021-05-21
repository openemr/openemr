<?php

/**
 * ConditionService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ConditionValidator;
use OpenEMR\Validators\ProcessingResult;

class ConditionService extends BaseService
{
    private const CONDITION_TABLE = "lists";
    private const PATIENT_TABLE = "patient_data";
    private const ENCOUNTER_TABLE = "form_encounter";
    private $uuidRegistry;
    private $conditionValidator;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('lists');
        $this->uuidRegistry = new UuidRegistry(['table_name' => self::CONDITION_TABLE]);
        $this->uuidRegistry->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::ENCOUNTER_TABLE]))->createMissingUuids();
        $this->conditionValidator = new ConditionValidator();
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = "SELECT lists.*,
        lists.pid AS patient_id,
        lists.title,
        encounter.uuid as encounter_uuid,
        patient.puuid,
        patient.patient_uuid,
        condition_ids.condition_uuid,
        verification.title as verification_title
    FROM lists
        INNER JOIN (
            SELECT lists.uuid AS condition_uuid FROM lists
        ) condition_ids ON lists.uuid = condition_ids.condition_uuid
        LEFT JOIN list_options as verification ON verification.option_id = lists.verification and verification.list_id = 'condition-verification'
        RIGHT JOIN (
            SELECT
                patient_data.uuid AS puuid
                ,patient_data.pid
                ,patient_data.uuid AS patient_uuid
            FROM patient_data
        ) patient ON patient.pid = lists.pid
        LEFT JOIN issue_encounter as issue ON issue.list_id =lists.id
        LEFT JOIN form_encounter as encounter ON encounter.encounter =issue.encounter";

        $search['type'] = new StringSearchField('type', ['medical_problem'], SearchModifier::EXACT);
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);


        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }
        return $processingResult;
    }

    public function getUuidFields(): array
    {
        return ['condition_uuid', 'puuid', 'encounter_uuid', 'uuid', 'patient_uuid'];
    }

    public function createResultRecordFromDatabaseResult($row)
    {
        $row = parent::createResultRecordFromDatabaseResult($row);
        if ($row['diagnosis'] != "") {
            $row['diagnosis'] = $this->addCoding($row['diagnosis']);
        }
        return $row;
    }

    /**
     * Returns a list of condition matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true, $puuidBind = null)
    {
        $newSearch = [];
        foreach ($search as $key => $value) {
            if (!$value instanceof ISearchField) {
                $newSearch[] = new StringSearchField($key, [$value], SearchModifier::EXACT);
            } else {
                $newSearch[$key] = $value;
            }
        }

        // override puuid, this replaces anything in search if it is already specified.
        if (isset($puuidBind)) {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        return $this->search($search, $isAndCondition);
    }

    /**
     * Returns a single condition record by uuid.
     * @param $uuid - The condition uuid identifier in string format.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        $search['condition_uuid'] = new TokenSearchField('condition_uuid', $uuid, true);
        if (isset($puuidBind)) {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }
        return $this->search($search);
    }


    /**
     * Inserts a new condition record.
     *
     * @param $data The condition fields (array) to insert.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function insert($data)
    {
        $processingResult = $this->conditionValidator->validate(
            $data,
            ConditionValidator::DATABASE_INSERT_CONTEXT
        );

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $puuidBytes = UuidRegistry::uuidToBytes($data['puuid']);
        $data['pid'] = $this->getIdByUuid($puuidBytes, self::PATIENT_TABLE, "pid");
        $data['uuid'] = (new UuidRegistry(['table_name' => self::CONDITION_TABLE]))->createUuid();

        $query = $this->buildInsertColumns($data);
        $sql  = " INSERT INTO lists SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     type='medical_problem',";
        $sql .= $query['set'];
        $results = sqlInsert(
            $sql,
            $query['bind']
        );

        if ($results) {
            $processingResult->addData(array(
                'id' => $results,
                'uuid' => UuidRegistry::uuidToString($data['uuid'])
            ));
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }

    /**
     * Updates an existing condition record.
     *
     * @param $uuid - The condition uuid identifier in string format used for update.
     * @param $data - The updated condition data fields
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function update($uuid, $data)
    {
        if (empty($data)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages("Invalid Data");
            return $processingResult;
        }

        $data["uuid"] = $uuid;
        $processingResult = $this->conditionValidator->validate(
            $data,
            ConditionValidator::DATABASE_UPDATE_CONTEXT
        );
        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE lists SET ";
        $sql .= $query['set'];
        $sql .= " WHERE `uuid` = ?";
        $sql .= "       AND `type` = 'medical_problem'";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        array_push($query['bind'], $uuidBinary);
        $sqlResult = sqlStatement($sql, $query['bind']);

        if (!$sqlResult) {
            $processingResult->addErrorMessage("error processing SQL Update");
        } else {
            $processingResult = $this->getOne($uuid);
        }
        return $processingResult;
    }

    /**
     * Deletes an existing condition record.
     *
     * @param $puuid - The patient uuid identifier in string format used for update.
     * @param $uuid - The condition uuid identifier in string format used for update.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function delete($puuid, $uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = $this->conditionValidator->validateId("uuid", "lists", $uuid, true);
        $isPatientValid = $this->conditionValidator->validateId("uuid", "patient_data", $puuid, true);

        if ($isValid !== true || $isPatientValid !== true) {
            $validationMessages = [
                'UUID' => ["invalid or nonexisting value"]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $puuidBytes = UuidRegistry::uuidToBytes($puuid);
        $auuid = UuidRegistry::uuidToBytes($uuid);
        $pid = $this->getIdByUuid($puuidBytes, self::PATIENT_TABLE, "pid");
        $sql  = "DELETE FROM lists WHERE pid=? AND uuid=? AND type='medical_problem'";

        $results = sqlStatement($sql, array($pid, $auuid));

        if ($results) {
            $processingResult->addData(array(
                'uuid' => $uuid
            ));
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }
}
