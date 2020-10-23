<?php

/**
 * AllergyIntoleranceService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\AllergyIntoleranceValidator;
use OpenEMR\Validators\ProcessingResult;

class AllergyIntoleranceService extends BaseService
{
    private const ALLERGY_TABLE = "lists";
    private const PATIENT_TABLE = "patient_data";
    private const PRACTITIONER_TABLE = "users";
    private const FACILITY_TABLE = "facility";
    private $uuidRegistry;
    private $allergyIntoleranceValidator;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('lists');
        $this->uuidRegistry = new UuidRegistry(['table_name' => self::ALLERGY_TABLE]);
        $this->uuidRegistry->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PRACTITIONER_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::FACILITY_TABLE]))->createMissingUuids();
        $this->allergyIntoleranceValidator = new AllergyIntoleranceValidator();
    }

    /**
     * Returns a list of allergyIntolerance matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true)
    {

        // Validating and Converting Patient UUID to PID
        if (isset($search['lists.pid'])) {
            $isValidPatient = $this->allergyIntoleranceValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $search['lists.pid'],
                true
            );
            if ($isValidPatient !== true) {
                return $isValidPatient;
            }
            $puuidBytes = UuidRegistry::uuidToBytes($search['lists.pid']);
            $search['lists.pid'] = $this->getIdByUuid($puuidBytes, self::PATIENT_TABLE, "pid");
        }

        // Validating and Converting UUID to ID
        if (isset($search['lists.id'])) {
            $isValidAllergy = $this->allergyIntoleranceValidator->validateId(
                'uuid',
                self::ALLERGY_TABLE,
                $search['lists.id'],
                true
            );
            if ($isValidAllergy !== true) {
                return $isValidAllergy;
            }
            $uuidBytes = UuidRegistry::uuidToBytes($search['lists.id']);
            $search['lists.id'] = $this->getIdByUuid($uuidBytes, self::ALLERGY_TABLE, "id");
        }

        $sqlBindArray = array();
        $sql = "SELECT lists.*,
        users.uuid as practitioner,
        facility.uuid as organization,
        patient.uuid as puuid,
        reaction.title as reaction_title,
        verification.title as verification_title
    FROM lists
        LEFT JOIN list_options as reaction ON (reaction.option_id = lists.reaction and reaction.list_id = 'reaction')
        LEFT JOIN list_options as verification ON verification.option_id = lists.verification and verification.list_id = 'allergyintolerance-verification'
        RIGHT JOIN patient_data as patient ON patient.pid = lists.pid
        LEFT JOIN users as users ON users.username = lists.user
        LEFT JOIN facility as facility ON facility.name = users.facility
    WHERE type = 'allergy'";

        if (!empty($search)) {
            $sql .= ' AND ';
            $whereClauses = array();
            foreach ($search as $fieldName => $fieldValue) {
                array_push($whereClauses, $fieldName . ' = ?');
                array_push($sqlBindArray, $fieldValue);
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
        }
        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            $row['practitioner'] = $row['practitioner'] ?
                UuidRegistry::uuidToString($row['practitioner']) :
                $row['practitioner'];
            $row['organization'] = $row['organization'] ?
                UuidRegistry::uuidToString($row['organization']) :
            $row['organization'];
            if ($row['diagnosis'] != "") {
                $row['diagnosis'] = $this->addCoding($row['diagnosis']);
            }
            $processingResult->addData($row);
        }
        return $processingResult;
    }

    /**
     * Returns a single allergyIntolerance record by uuid.
     * @param $uuid - The allergyIntolerance uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = $this->allergyIntoleranceValidator->validateId("uuid", "lists", $uuid, true);

        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $sql = "SELECT lists.*,
        users.uuid as practitioner,
        facility.uuid as organization,
        patient.uuid as puuid,
        reaction.title as reaction_title,
        verification.title as verification_title
    FROM lists
        LEFT JOIN list_options as reaction ON (reaction.option_id = lists.reaction and reaction.list_id = 'reaction')
        LEFT JOIN list_options as verification ON verification.option_id = lists.verification and verification.list_id = 'allergyintolerance-verification'
        RIGHT JOIN patient_data as patient ON patient.pid = lists.pid
        LEFT JOIN users as users ON users.username = lists.user
        LEFT JOIN facility as facility ON facility.name = users.facility
    WHERE type = 'allergy' AND lists.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $sqlResult['practitioner'] = $sqlResult['practitioner'] ?
            UuidRegistry::uuidToString($sqlResult['practitioner']) :
            $sqlResult['practitioner'];
        $sqlResult['organization'] = $sqlResult['organization'] ?
            UuidRegistry::uuidToString($sqlResult['organization']) :
        $sqlResult['organization'];
        if ($sqlResult['diagnosis'] != "") {
            $row['diagnosis'] = $this->addCoding($sqlResult['diagnosis']);
        }
        $processingResult->addData($sqlResult);
        return $processingResult;
    }

    /**
     * Inserts a new allergy record.
     *
     * @param $data The allergy fields (array) to insert.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function insert($data)
    {
        $processingResult = $this->allergyIntoleranceValidator->validate(
            $data,
            AllergyIntoleranceValidator::DATABASE_INSERT_CONTEXT
        );

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $puuidBytes = UuidRegistry::uuidToBytes($data['puuid']);
        $data['pid'] = $this->getIdByUuid($puuidBytes, self::PATIENT_TABLE, "pid");
        $data['uuid'] = (new UuidRegistry(['table_name' => self::ALLERGY_TABLE]))->createUuid();

        $query = $this->buildInsertColumns($data);
        $sql  = " INSERT INTO lists SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     type='allergy',";
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
     * Updates an existing allergy record.
     *
     * @param $uuid - The allergy uuid identifier in string format used for update.
     * @param $data - The updated allergy data fields
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
        $processingResult = $this->allergyIntoleranceValidator->validate(
            $data,
            AllergyIntoleranceValidator::DATABASE_UPDATE_CONTEXT
        );
        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE lists SET ";
        $sql .= $query['set'];
        $sql .= " WHERE `uuid` = ?";
        $sql .= "       AND `type` = 'allergy'";

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
     * Deletes an existing allergy record.
     *
     * @param $puuid - The patient uuid identifier in string format used for update.
     * @param $uuid - The allergy uuid identifier in string format used for update.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function delete($puuid, $uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = $this->allergyIntoleranceValidator->validateId("uuid", "lists", $uuid, true);
        $isPatientValid = $this->allergyIntoleranceValidator->validateId("uuid", "patient_data", $puuid, true);

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
        $sql  = "DELETE FROM lists WHERE pid=? AND uuid=? AND type='allergy'";

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
