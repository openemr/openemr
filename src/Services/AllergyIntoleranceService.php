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
    private $allergyIntoleranceValidator;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::ALLERGY_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::ALLERGY_TABLE, self::PATIENT_TABLE, self::PRACTITIONER_TABLE,
            self::FACILITY_TABLE]);
        $this->allergyIntoleranceValidator = new AllergyIntoleranceValidator();
    }

    public function search($search, $isAndCondition = true)
    {
        // we inner join on lists itself so we can grab our uuids, we do this so we can search on each of the uuids
        // such as allergy_uuid, practitioner_uuid,organization_uuid, etc.  You can't use an 'AS' clause in a select
        // so we have to have actual column names in our WHERE clause.  To make that work in a searchable way we extend
        // out our queries into sub queries which through the power of index's & keys it is pretty highly optimized by
        // the database query engine.

        $sql = "SELECT lists.*,
        lists.pid AS patient_id,
        lists.title,
        lists.comments,
        practitioners.uuid as practitioner,
        practitioners.practitioner_npi,
        practitioners.practitioner_uuid,
        organizations.uuid as organization,
        organizations.organization_uuid,
        patient.puuid,
        patient.patient_uuid,
        allergy_ids.allergy_uuid,
        reaction.title as reaction_title,
        reaction.codes AS reaction_codes,
        verification.title as verification_title
    FROM (
            SELECT lists.*, lists.pid AS patient_id FROM lists
        ) lists
        INNER JOIN (
            SELECT lists.uuid AS allergy_uuid FROM lists
        ) allergy_ids ON lists.uuid = allergy_ids.allergy_uuid
        LEFT JOIN list_options as reaction ON (reaction.option_id = lists.reaction and reaction.list_id = 'reaction')
        LEFT JOIN list_options as verification ON verification.option_id = lists.verification
            and verification.list_id = 'allergyintolerance-verification'
        RIGHT JOIN (
            SELECT
                patient_data.uuid AS puuid
                ,patient_data.pid
                ,patient_data.uuid AS patient_uuid
            FROM patient_data
        ) patient ON patient.pid = lists.pid
        LEFT JOIN (
            select
            users.uuid
            ,users.uuid AS practitioner_uuid
            ,users.npi AS practitioner_npi
            ,users.username
            ,users.facility AS organization
            FROM users
        ) practitioners ON practitioners.username = lists.user
        LEFT JOIN (
            select
            facility.uuid
            ,facility.uuid AS organization_uuid
            ,facility.name
            FROM facility
        ) organizations ON organizations.name = practitioners.organization";

        // make sure we only search for allergy fields
        $search['type'] = new StringSearchField('type', ['allergy'], SearchModifier::EXACT);

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        // create temp allergy uuid array since sql returns duplicates if
        // allergies do not have a user associated
        $temp_uuid_array = [];
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['allergy_uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            $row['patient_uuid'] = UuidRegistry::uuidToString($row['patient_uuid']);
            $row['allergy_uuid'] = UuidRegistry::uuidToString($row['allergy_uuid']);
            $row['practitioner'] = $row['practitioner'] ?
                UuidRegistry::uuidToString($row['practitioner']) :
                $row['practitioner'];
            $row['organization'] = $row['organization'] ?
                UuidRegistry::uuidToString($row['organization']) :
                $row['organization'];
            if ($row['diagnosis'] != "") {
                $row['diagnosis'] = $this->addCoding($row['diagnosis']);
            }
            if (!empty($row['reaction']) && !empty($row['reaction_codes'])) {
                $row['reaction'] = $this->addCoding($row['reaction_codes']);
            }
            unset($row['allergy_uuid']);
            // only add to processing result if unique
            if (!in_array($row['uuid'], $temp_uuid_array)) {
                $processingResult->addData($row);
                array_push($temp_uuid_array, $row['uuid']);
            }
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
        // backwards compatible we let sub tables be referenced before,
        // we want those to go away as it's a leaky abstraction
        if (isset($search['lists.pid'])) {
            $search['patient_id'] = $search['lists.pid'];
            unset($search['lists.pid']);
        }
        if (isset($search['lists.id'])) {
            $search['allergy_uuid'] = $search['lists.id'];
            unset($search['lists.id']);
        }
        // Validating and Converting Patient UUID to PID
        if (isset($search['puuid'])) {
            $isValidPatient = $this->allergyIntoleranceValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $search['puuid'],
                true
            );
            if ($isValidPatient !== true) {
                return $isValidPatient;
            }
        }

        // Validating and Converting UUID to ID
        if (isset($search['allergy_uuid'])) {
            $isValidAllergy = $this->allergyIntoleranceValidator->validateId(
                'uuid',
                self::ALLERGY_TABLE,
                $search['allergy_uuid'],
                true
            );
            if ($isValidAllergy !== true) {
                return $isValidAllergy;
            }
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
        // override puuid with the token search field for binary search
        if (isset($search['puuid'])) {
            $newSearch['puuid'] = new TokenSearchField('puuid', $search['puuid'], true);
            unset($search['puuid']);
        }

        foreach ($search as $key => $value) {
            if (!$value instanceof ISearchField) {
                $newSearch[] = new StringSearchField($key, [$value], SearchModifier::EXACT);
            } else {
                $newSearch[$key] = $value;
            }
        }

        // override puuid, this replaces anything in search if it is already specified.
        if (isset($puuidBind)) {
            $newSearch['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        return $this->search($newSearch, $isAndCondition);
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
        if (isset($puuidBind)) {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }
        return $this->search($search);
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
