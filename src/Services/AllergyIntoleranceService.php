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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
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

    public function search($search, $isAndCondition = true)
    {
        // we inner join on lists itself so we can grab our allergy_uuid, we do this so we can search on allergy_uuid
        // in our where clause and allow our search to use that correctly
        $sql = "SELECT lists.*,
        lists.pid AS patient_id,
        users.uuid as practitioner,
        facility.uuid as organization,
        patient.uuid as puuid,
        allergy_ids.allergy_uuid,
        reaction.title as reaction_title,
        verification.title as verification_title
    FROM lists
        INNER JOIN (
            SELECT lists.uuid AS allergy_uuid FROM lists
        ) allergy_ids ON lists.uuid = allergy_ids.allergy_uuid
        LEFT JOIN list_options as reaction ON (reaction.option_id = lists.reaction and reaction.list_id = 'reaction')
        LEFT JOIN list_options as verification ON verification.option_id = lists.verification and verification.list_id = 'allergyintolerance-verification'
        RIGHT JOIN patient_data as patient ON patient.pid = lists.pid
        LEFT JOIN users as users ON users.username = lists.user
        LEFT JOIN facility as facility ON facility.name = users.facility";
//    WHERE type = 'allergy'";


        // make sure we only search for allergy fields
        $search['type'] = new StringSearchField('type', ['allergy'], SearchModifier::EXACT);
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
//
//        if (!empty($search)) {
//            $sql .= ' AND ';
//            if (!empty($puuidBind)) {
//                // code to support patient binding
//                $sql .= '(';
//            }
//            $whereClauses = array();
//            foreach ($search as $fieldName => $fieldValue) {
//                array_push($whereClauses, $fieldName . ' = ?');
//                array_push($sqlBindArray, $fieldValue);
//            }
//            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
//            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
//            if (!empty($puuidBind)) {
//                // code to support patient binding
//                $sql .= ") AND `patient`.`uuid` = ?";
//                $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
//            }
//        } elseif (!empty($puuidBind)) {
//            // code to support patient binding
//            $sql .= " AND `patient`.`uuid` = ?";
//            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
//        }

        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['allergy_uuid']);
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
     * Returns a list of allergyIntolerance matching optional search criteria.
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
        // backwards compatible let's change these over
        if (isset($search['lists.pid'])) {
            $search['puuid'] = $search['lists.pid'];
        }
        if (isset($search['lists.id'])) {
            $search['allergy_uuid'] = $search['lists.id'];
        }
        // Validating and Converting Patient UUID to PID
        if (isset($search['lists.pid'])) {
            $isValidPatient = $this->allergyIntoleranceValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $search['puuid'],
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
                $search['allergy_uuid'],
                true
            );
            if ($isValidAllergy !== true) {
                return $isValidAllergy;
            }
            $uuidBytes = UuidRegistry::uuidToBytes($search['lists.id']);
            $search['lists.id'] = $this->getIdByUuid($uuidBytes, self::ALLERGY_TABLE, "id");
        }

        if (!empty($puuidBind)) {
            // code to support patient binding
            $isValidPatient = $this->allergyIntoleranceValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $puuidBind,
                true
            );
            if ($isValidPatient !== true) {
                return $isValidPatient;
            }
        }
        $newSearch = [];
        foreach ($search as $key => $value) {
            if (!$value instanceof ISearchField) {
                $newSearch[] = new StringSearchField($key, [$value], SearchModifier::EXACT);
            }
            else {
                $newSearch[$key] = $value;
            }
        }

        // override our type and our puuid
        if (isset($puuidBind) && !isset($search['puuid']))
        {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        return $this->search($search, $isAndCondition);
    }

    /**
     * Returns a single allergyIntolerance record by uuid.
     * @param $uuid - The allergyIntolerance uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        $search['allergy_uuid'] = new TokenSearchField('allergy_uuid', $uuid, true);
        if (isset($puuidBind))
        {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }
        return $this->search($search);
//        $processingResult = new ProcessingResult();
//
//        $isValid = $this->allergyIntoleranceValidator->validateId("uuid", "lists", $uuid, true);
//        if ($isValid !== true) {
//            $validationMessages = [
//                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
//            ];
//            $processingResult->setValidationMessages($validationMessages);
//            return $processingResult;
//        }
//
//        if (!empty($puuidBind)) {
//            $isValid = $this->allergyIntoleranceValidator->validateId("uuid", "patient_data", $puuidBind, true);
//            if ($isValid !== true) {
//                $validationMessages = [
//                    'puuid' => ["invalid or nonexisting value" => " value " . $puuidBind]
//                ];
//                $processingResult->setValidationMessages($validationMessages);
//                return $processingResult;
//            }
//        }
//
//        $sql = "SELECT lists.*,
//        users.uuid as practitioner,
//        facility.uuid as organization,
//        patient.uuid as puuid,
//        reaction.title as reaction_title,
//        verification.title as verification_title
//    FROM lists
//        LEFT JOIN list_options as reaction ON (reaction.option_id = lists.reaction and reaction.list_id = 'reaction')
//        LEFT JOIN list_options as verification ON verification.option_id = lists.verification and verification.list_id = 'allergyintolerance-verification'
//        RIGHT JOIN patient_data as patient ON patient.pid = lists.pid
//        LEFT JOIN users as users ON users.username = lists.user
//        LEFT JOIN facility as facility ON facility.name = users.facility
//    WHERE type = 'allergy' AND lists.uuid = ?";
//
//        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
//        $sqlBindArray = [$uuidBinary];
//
//        if (!empty($puuidBind)) {
//            $sql .= " AND `patient`.`uuid` = ?";
//            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
//        }
//
//        $sqlResult = sqlQuery($sql, $sqlBindArray);
//        if (!empty($sqlResult)) {
//            $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
//            $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
//            $sqlResult['practitioner'] = $sqlResult['practitioner'] ?
//                UuidRegistry::uuidToString($sqlResult['practitioner']) :
//                $sqlResult['practitioner'];
//            $sqlResult['organization'] = $sqlResult['organization'] ?
//                UuidRegistry::uuidToString($sqlResult['organization']) :
//                $sqlResult['organization'];
//            if ($sqlResult['diagnosis'] != "") {
//                $row['diagnosis'] = $this->addCoding($sqlResult['diagnosis']);
//            }
//            $processingResult->addData($sqlResult);
//        }
//        return $processingResult;
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
