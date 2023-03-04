<?php

/**
 * Patient Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\ORDataObject\Address;
use OpenEMR\Common\ORDataObject\ContactAddress;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Patient\BeforePatientCreatedEvent;
use OpenEMR\Events\Patient\BeforePatientUpdatedEvent;
use OpenEMR\Events\Patient\PatientCreatedEvent;
use OpenEMR\Events\Patient\PatientUpdatedEvent;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\PatientValidator;
use OpenEMR\Validators\ProcessingResult;

class PatientService extends BaseService
{
    public const TABLE_NAME = 'patient_data';
    private const PATIENT_HISTORY_TABLE = "patient_history";

    /**
     * In the case where a patient doesn't have a picture uploaded,
     * this value will be returned so that the document controller
     * can return an empty response.
     */
    private $patient_picture_fallback_id = -1;

    /**
     * @var PatientValidator
     */
    private $patientValidator;

    /**
     * Key of translated suffix values that can be in a patient's name.
     * @var array|null
     */
    private $patientSuffixKeys = null;

    /**
     * Default constructor.
     */
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
        $this->patientValidator = new PatientValidator();
    }

    /**
     * TODO: This should go in the ChartTrackerService and doesn't have to be static.
     *
     * @param  $pid unique patient id
     * @return recordset
     */
    public static function getChartTrackerInformationActivity($pid)
    {
        $sql = "SELECT ct.ct_when,
                   ct.ct_userid,
                   ct.ct_location,
                   u.username,
                   u.fname,
                   u.mname,
                   u.lname
            FROM chart_tracker AS ct
            LEFT OUTER JOIN users AS u ON u.id = ct.ct_userid
            WHERE ct.ct_pid = ?
            ORDER BY ct.ct_when DESC";
        return sqlStatement($sql, array($pid));
    }

    /**
     * TODO: This should go in the ChartTrackerService and doesn't have to be static.
     *
     * @return recordset
     */
    public static function getChartTrackerInformation()
    {
        $sql = "SELECT ct.ct_when,
                   u.username,
                   u.fname AS ufname,
                   u.mname AS umname,
                   u.lname AS ulname,
                   p.pubpid,
                   p.fname,
                   p.mname,
                   p.lname
            FROM chart_tracker AS ct
            JOIN cttemp ON cttemp.ct_pid = ct.ct_pid AND cttemp.ct_when = ct.ct_when
            LEFT OUTER JOIN users AS u ON u.id = ct.ct_userid
            LEFT OUTER JOIN patient_data AS p ON p.pid = ct.ct_pid
            WHERE ct.ct_userid != 0
            ORDER BY p.pubpid";
        return sqlStatement($sql);
    }

    public function getFreshPid()
    {
        $pid = sqlQuery("SELECT MAX(pid)+1 AS pid FROM patient_data");
        return $pid['pid'] === null ? 1 : intval($pid['pid']);
    }

    /**
     * Insert a patient record into the database
     *
     * returns the newly-created patient data array, or false in the case of
     * an error with the sql insert
     *
     * @param $data
     * @return false|int
     */
    public function databaseInsert($data)
    {
        $freshPid = $this->getFreshPid();
        $data['pid'] = $freshPid;
        $data['uuid'] = (new UuidRegistry(['table_name' => 'patient_data']))->createUuid();

        // The 'date' is the updated-date, and 'regdate' is the created-date
        // so set both to the current datetime.
        $data['date'] = date("Y-m-d H:i:s");
        $data['regdate'] = date("Y-m-d H:i:s");
        // we should never be null here but for legacy reasons we are going to default to this
        $createdBy = $_SESSION['authUserID'] ?? null; // we don't let anyone else but the current user be the createdBy
        $data['created_by'] = $createdBy;
        $data['updated_by'] = $createdBy; // for an insert this is the same
        if (empty($data['pubpid'])) {
            $data['pubpid'] = $freshPid;
        }

        // Before a patient is inserted, fire the "before patient created" event so listeners can do extra processing
        $beforePatientCreatedEvent = new BeforePatientCreatedEvent($data);
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch($beforePatientCreatedEvent, BeforePatientCreatedEvent::EVENT_HANDLE, 10);
        $data = $beforePatientCreatedEvent->getPatientData();

        $query = $this->buildInsertColumns($data);
        $sql = " INSERT INTO patient_data SET ";
        $sql .= $query['set'];

        $results = sqlInsert($sql, $query['bind']);
        $data['id'] = $results;

        // Tell subscribers that a new patient has been created
        $patientCreatedEvent = new PatientCreatedEvent($data);
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch($patientCreatedEvent, PatientCreatedEvent::EVENT_HANDLE, 10);

        // If we have a result-set from our insert, return the PID,
        // otherwise return false
        if ($results) {
            return $data;
        } else {
            return false;
        }
    }

    /**
     * Inserts a new patient record.
     *
     * @param $data The patient fields (array) to insert.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function insert($data)
    {
        $processingResult = $this->patientValidator->validate($data, PatientValidator::DATABASE_INSERT_CONTEXT);

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $data = $this->databaseInsert($data);

        if (false !== $data['pid']) {
            $processingResult->addData(array(
                'pid' => $data['pid'],
                'uuid' => UuidRegistry::uuidToString($data['uuid'])
            ));
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }

    /**
     * Do a database update using the pid from the input
     * array
     *
     * Return the data that was updated into the database,
     * or false if there was an error with the update
     *
     * @param array $data
     * @return mixed
     */
    public function databaseUpdate($data)
    {
        // Get the data before update to send to the event listener
        $dataBeforeUpdate = $this->findByPid($data['pid']);

        // The `date` column is treated as an updated_date
        $data['date'] = date("Y-m-d H:i:s");
        // we should never be null here but for legacy reasons we are going to default to this
        $updatedBy = $_SESSION['authUserID'] ?? null; // we don't let anyone else but the current user be the updatedBy
        $data['updated_by'] = $updatedBy; // for an insert this is the same
        $table = PatientService::TABLE_NAME;

        // Fire the "before patient updated" event so listeners can do extra processing before data is updated
        $beforePatientUpdatedEvent = new BeforePatientUpdatedEvent($data);
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch($beforePatientUpdatedEvent, BeforePatientUpdatedEvent::EVENT_HANDLE, 10);
        $data = $beforePatientUpdatedEvent->getPatientData();

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE $table SET ";
        $sql .= $query['set'];
        $sql .= " WHERE `pid` = ?";

        array_push($query['bind'], $data['pid']);
        $sqlResult = sqlStatement($sql, $query['bind']);

        if (
            $dataBeforeUpdate['care_team_provider'] != ($data['care_team_provider'] ?? '')
            || ($dataBeforeUpdate['care_team_facility'] ?? '') != ($data['care_team_facility'] ?? '')
        ) {
            // need to save off our care team
            $this->saveCareTeamHistory($data, $dataBeforeUpdate['care_team_provider'], $dataBeforeUpdate['care_team_facility']);
        }

        if ($sqlResult) {
            // Tell subscribers that a new patient has been updated
            $patientUpdatedEvent = new PatientUpdatedEvent($dataBeforeUpdate, $data);
            $GLOBALS["kernel"]->getEventDispatcher()->dispatch($patientUpdatedEvent, PatientUpdatedEvent::EVENT_HANDLE, 10);

            return $data;
        } else {
            return false;
        }
    }

    /**
     * Updates an existing patient record.
     *
     * @param $puuidString - The patient uuid identifier in string format used for update.
     * @param $data - The updated patient data fields
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function update($puuidString, $data)
    {
        $data["uuid"] = $puuidString;
        $processingResult = $this->patientValidator->validate($data, PatientValidator::DATABASE_UPDATE_CONTEXT);
        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        // Get the data before update to send to the event listener
        $dataBeforeUpdate = $this->getOne($puuidString);

        // The `date` column is treated as an updated_date
        $data['date'] = date("Y-m-d H:i:s");

        // Fire the "before patient updated" event so listeners can do extra processing before data is updated
        $beforePatientUpdatedEvent = new BeforePatientUpdatedEvent($data);
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch($beforePatientUpdatedEvent, BeforePatientUpdatedEvent::EVENT_HANDLE, 10);
        $data = $beforePatientUpdatedEvent->getPatientData();

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE patient_data SET ";
        $sql .= $query['set'];
        $sql .= " WHERE `uuid` = ?";

        $puuidBinary = UuidRegistry::uuidToBytes($puuidString);
        array_push($query['bind'], $puuidBinary);
        $sqlResult = sqlStatement($sql, $query['bind']);

        if (!$sqlResult) {
            $processingResult->addErrorMessage("error processing SQL Update");
        } else {
            $processingResult = $this->getOne($puuidString);
            // Tell subscribers that a new patient has been updated
            // We have to do this here and in the databaseUpdate() because this lookup is
            // by uuid where the databseUpdate updates by pid.
            $patientUpdatedEvent = new PatientUpdatedEvent($dataBeforeUpdate, $processingResult->getData());
            $GLOBALS["kernel"]->getEventDispatcher()->dispatch($patientUpdatedEvent, PatientUpdatedEvent::EVENT_HANDLE, 10);
        }
        return $processingResult;
    }

    protected function createResultRecordFromDatabaseResult($record)
    {
        if (!empty($record['uuid'])) {
            $record['uuid'] = UuidRegistry::uuidToString($record['uuid']);
        }
        if (!empty($record['patient_history_uuid'])) {
            $record['patient_history_uuid'] = UuidRegistry::uuidToString($record['patient_history_uuid']);
        }

        return $record;
    }


    /**
     * Returns a list of patients matching optional search criteria.
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
        $querySearch = [];
        if (!empty($search)) {
            if (isset($puuidBind)) {
                $querySearch['uuid'] = new TokenSearchField('uuid', $puuidBind);
            } elseif (isset($search['uuid'])) {
                $querySearch['uuid'] = new TokenSearchField('uuid', $search['uuid']);
            }
            $wildcardFields = array('fname', 'mname', 'lname', 'street', 'city', 'state','postal_code','title'
            , 'contact_address_line1', 'contact_address_city', 'contact_address_state','contact_address_postalcode');
            foreach ($wildcardFields as $field) {
                if (isset($search[$field])) {
                    $querySearch[$field] = new StringSearchField($field, $search[$field], SearchModifier::CONTAINS, $isAndCondition);
                }
            }
            // for backwards compatability, we will make sure we do exact matches on the keys using string comparisons if no object is used
            foreach ($search as $field => $key) {
                if (!isset($querySearch[$field]) && !($key instanceof ISearchField)) {
                    $querySearch[$field] = new StringSearchField($field, $search[$field], SearchModifier::EXACT, $isAndCondition);
                }
            }
        }
        return $this->search($querySearch, $isAndCondition);
    }

    public function search($search, $isAndCondition = true)
    {
        // we run two queries in this search.  The first query is to grab all of the uuids of the patients that match
        // the search.  Because we are joining several tables with a 1:m relationship on several tables (previous name,
        // patient history, address) we have to grab all of our patients uuids and then run our query AGAIN without any
        // search filters so that we can make sure to grab the ENTIRE patient record (all of their names, addresses, etc).
        $sqlSelectIds = "SELECT DISTINCT patient_data.uuid ";
        $sqlSelectData = "SELECT
                    patient_data.*
                    ,patient_history_uuid
                    ,patient_history_type_key
                    ,patient_history_created_by
                    ,previous_name_first
                    ,previous_name_prefix
                    ,previous_name_first
                    ,previous_name_middle
                    ,previous_name_last
                    ,previous_name_suffix
                    ,previous_name_enddate
                    ,patient_additional_addresses.*
        ";
        $sql = "
                FROM patient_data
                LEFT JOIN (
                    SELECT
                    pid AS patient_history_pid
                    ,history_type_key AS patient_history_type_key
                    ,created_by AS patient_history_created_by
                    ,previous_name_prefix
                    ,previous_name_first
                    ,previous_name_middle
                    ,previous_name_last
                    ,previous_name_suffix
                    ,previous_name_enddate
                    ,`date` AS previous_creation_date
                    ,uuid AS patient_history_uuid
                    FROM patient_history
                ) patient_history ON patient_data.pid = patient_history.patient_history_pid
                LEFT JOIN (
                    SELECT  
                        contact.id AS contact_address_contact_id
                        ,contact.foreign_id AS contact_address_patient_id
                        ,contact_address.`id` AS contact_address_id
                        ,contact_address.`priority` AS contact_address_priority
                        ,contact_address.`type` AS contact_address_type
                        ,contact_address.`use` AS contact_address_use
                        ,contact_address.`is_primary` AS contact_address_is_primary
                        ,contact_address.`created_date` AS contact_address_created_date
                        ,contact_address.`period_start` AS contact_address_period_start
                        ,contact_address.`period_end` AS contact_address_period_end
                        ,addresses.id AS contact_address_address_id
                        ,addresses.`line1` AS contact_address_line1
                        ,addresses.`line2` AS contact_address_line2
                        ,addresses.`city` AS contact_address_city
                        ,addresses.`district` AS contact_address_district
                        ,addresses.`state` AS contact_address_state
                        ,addresses.`zip` AS contact_address_postal_code
                        ,addresses.`country` AS contact_address_country
                    FROM contact
                    INNER JOIN contact_address ON contact.id = contact_address.contact_id
                    INNER JOIN addresses ON contact_address.address_id = addresses.id
                    WHERE `contact_address`.`status`='A' AND contact.foreign_table_name='patient_data'
                ) patient_additional_addresses ON patient_data.pid = patient_additional_addresses.contact_address_patient_id";
        $whereUuidClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sqlUuids = $sqlSelectIds . $sql . $whereUuidClause->getFragment();
        $uuidResults = QueryUtils::fetchTableColumn($sqlUuids, 'uuid', $whereUuidClause->getBoundValues());

        if (!empty($uuidResults)) {
            // now we are going to run through this again and grab all of our data w only the uuid search as our filter
            // this makes sure we grab the entire patient record and associated data
            $whereClause = " WHERE patient_data.uuid IN (" . implode(",", array_map(function ($uuid) {
                return "?";
            }, $uuidResults)) . ")";
            $statementResults = QueryUtils::sqlStatementThrowException($sqlSelectData . $sql . $whereClause, $uuidResults);
            $processingResult = $this->hydrateSearchResultsFromQueryResource($statementResults);
            return $processingResult;
        } else {
            return new ProcessingResult();
        }
    }

    private function hydrateSearchResultsFromQueryResource($queryResource)
    {
        $processingResult = new ProcessingResult();
        $patientsByUuid = [];
        $alreadySeenPatientHistoryUuids = [];
        $alreadySeenContactAddressIds = [];
        $patientFields = array_combine($this->getFields(), $this->getFields());
        $previousNameColumns = ['previous_name_prefix', 'previous_name_first', 'previous_name_middle'
            , 'previous_name_last', 'previous_name_suffix', 'previous_name_enddate'];
        $previousNamesFields = array_combine($previousNameColumns, $previousNameColumns);
        $patientOrderedList = [];
        while ($row = sqlFetchArray($queryResource)) {
                $record = $this->createResultRecordFromDatabaseResult($row);
            $patientUuid = $record['uuid'];
            if (!isset($patientsByUuid[$patientUuid])) {
                $patient = array_intersect_key($record, $patientFields);
                $patient['suffix'] = $this->parseSuffixForPatientRecord($patient);
                $patient['previous_names'] = [];
                $patientOrderedList[] = $patientUuid;
            } else {
                $patient = $patientsByUuid[$patientUuid];
            }
            // we only want to populate our patient history records if we haven't seen this uuid before and we are working
            // with a name history record...
            if (
                !empty($record['patient_history_type_key'])
                && empty($alreadySeenPatientHistoryUuids[$record['patient_history_uuid']])
                && $record['patient_history_type_key'] == 'name_history'
            ) {
                $alreadySeenPatientHistoryUuids[$record['patient_history_uuid']] = $record['patient_history_uuid'];
                    $previousName = array_intersect_key($record, $previousNamesFields);
                    $previousName['formatted_name'] = $this->formatPreviousName($previousName);
                    $patient['previous_names'][] = $previousName;
            }
            if (empty($patient['addresses'])) {
                $patient['addresses'] = [$this->hydratedPatientInitialAddressInformation($patient)];
            }

            // now we are going to keep track of our address information
            if (!empty($record['contact_address_id']) && empty($alreadySeenContactAddressIds[$record['contact_address_id']])) {
                $patient['addresses'][] = $this->hydratePatientAdditionalAddressInformation($record);
            }

            // now let's grab our history
            $patientsByUuid[$patientUuid] = $patient;
        }
        foreach ($patientOrderedList as $uuid) {
            $patient = $patientsByUuid[$uuid];
            $processingResult->addData($patient);
        }
        return $processingResult;
    }

    private function hydratePatientAdditionalAddressInformation(&$record)
    {
        $address = [
            'id' => $record['contact_address_address_id'] ?? null
            ,'contact_id' => $record['contact_address_contact_id'] ?? null
            ,'contact_address_id' => $record['contact_address_id'] ?? null
            ,'period_start' => $record['contact_address_period_start'] ?? date("Y-m-d 00:00:00")
            ,'period_end' => $record['contact_address_period_end'] ?? null
            ,'type' => $record['contact_address_type'] ?? ContactAddress::DEFAULT_TYPE
            ,'use' => $record['contact_address_use'] ?? ContactAddress::DEFAULT_USE
            ,'priority' => $record['contact_address_address_priority'] ?? 0
            ,'line1' => $record['contact_address_line1'] ?? ''
            ,'line2' => $record['contact_address_line2'] ?? ''
            ,'city' => $record['contact_address_city'] ?? ''
            ,'district' => $record['contact_address_district'] ?? ''
            ,'state' => $record['contact_address_state'] ?? ''
            ,'postal_code' => $record['contact_address_postal_code'] ?? ''
            ,'country_code' => $record['contact_address_country'] ?? ''
        ];
        return $address;
    }

    private function hydratedPatientInitialAddressInformation(&$patient)
    {
        // we need to setup our initial address from the patient records if we have one
        $address = [
            'id' => null
            ,'contact_id' => null
            ,'contact_address_id' => null
            ,'period_start' => $patient['date'] // we go off the patient date as for when the address was here as we don't have any other good date to go off of.
            ,'period_end' => null
            ,'type' => ContactAddress::DEFAULT_TYPE
            ,'use' => ContactAddress::DEFAULT_USE
            ,'priority' => 0
            ,'line1' => $patient['street'] ?? ''
            ,'line2' => $patient['street_line_2'] ?? ''
            ,'city' => $patient['city'] ?? ''
            ,'district' => $patient['county'] ?? ''
            ,'state' => $patient['state'] ?? ''
            ,'postal_code' => $patient['postal_code'] ?? ''
            ,'country_code' => $patient['country_code'] ?? ''
        ];
        return $address;
    }

    /**
     * Returns a single patient record by patient id.
     * @param $puuidString - The patient uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($puuidString)
    {
        $processingResult = new ProcessingResult();

        $isValid = $this->patientValidator->isExistingUuid($puuidString);

        if (!$isValid) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $puuidString]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        return $this->search(['uuid' => new TokenSearchField('uuid', [$puuidString], true)]);
    }

    /**
     * Given a pid, find the patient record
     *
     * @param $pid
     */
    public function findByPid($pid)
    {
        $table = PatientService::TABLE_NAME;
        $patientRow = self::selectHelper("SELECT * FROM `$table`", [
            'where' => 'WHERE pid = ?',
            'limit' => 1,
            'data' => [$pid]
        ]);

        return $patientRow;
    }

    /**
     * @return number
     */
    public function getPatientPictureDocumentId($pid)
    {
        $sql = "SELECT doc.id AS id
                 FROM documents doc
                 JOIN categories_to_documents cate_to_doc
                   ON doc.id = cate_to_doc.document_id
                 JOIN categories cate
                   ON cate.id = cate_to_doc.category_id
                WHERE cate.name LIKE ? and doc.foreign_id = ?";

        $result = sqlQuery($sql, array($GLOBALS['patient_photo_category_name'], $pid));

        if (empty($result) || empty($result['id'])) {
            return $this->patient_picture_fallback_id;
        }

        return $result['id'];
    }

    /**
     * Fetch UUID for the patient id
     *
     * @param string $id                - ID of Patient
     * @return false if nothing found otherwise return UUID (in binary form)
     */
    public function getUuid($pid)
    {
        return self::getUuidById($pid, self::TABLE_NAME, 'pid');
    }

    public function getPidByUuid($uuid)
    {
        return self::getIdByUuid($uuid, self::TABLE_NAME, 'pid');
    }

    private function saveCareTeamHistory($patientData, $oldProviders, $oldFacilities)
    {
        $careTeamService = new CareTeamService();
        $careTeamService->createCareTeamHistory($patientData['pid'], $oldProviders, $oldFacilities);
    }

    public function getPatientNameHistory($pid)
    {
        $sql = "SELECT pid,
            id,
            previous_name_prefix,
            previous_name_first,
            previous_name_middle,
            previous_name_last,
            previous_name_suffix,
            previous_name_enddate
            FROM patient_history
            WHERE pid = ? AND history_type_key = ?";
        $results =  QueryUtils::sqlStatementThrowException($sql, array($pid, 'name_history'));
        $rows = [];
        while ($row = sqlFetchArray($results)) {
            $row['formatted_name'] = $this->formatPreviousName($row);
            $rows[] = $row;
        }

        return $rows;
    }

    public function deletePatientNameHistoryById($id)
    {
        $sql = "DELETE FROM patient_history WHERE id = ?";
        return sqlQuery($sql, array($id));
    }

    public function getPatientNameHistoryById($pid, $id)
    {
        $sql = "SELECT pid,
            id,
            previous_name_prefix,
            previous_name_first,
            previous_name_middle,
            previous_name_last,
            previous_name_suffix,
            previous_name_enddate
            FROM patient_history
            WHERE pid = ? AND id = ? AND history_type_key = ?";
        $result =  sqlQuery($sql, array($pid, $id, 'name_history'));
        $result['formatted_name'] = $this->formatPreviousName($result);

        return $result;
    }

    /**
     * Create a previous patient name history
     * Updates not allowed for this history feature.
     *
     * @param string $pid patient internal id
     * @param array $record array values to insert
     * @return int | false new id or false if name already exist
     */
    public function createPatientNameHistory($pid, $record)
    {
        // we should never be null here but for legacy reasons we are going to default to this
        $createdBy = $_SESSION['authUserID'] ?? null; // we don't let anyone else but the current user be the createdBy

        $insertData = [
            'pid' => $pid,
            'history_type_key' => 'name_history',
            'previous_name_prefix' => $record['previous_name_prefix'],
            'previous_name_first' => $record['previous_name_first'],
            'previous_name_middle' => $record['previous_name_middle'],
            'previous_name_last' => $record['previous_name_last'],
            'previous_name_suffix' => $record['previous_name_suffix'],
            'previous_name_enddate' => $record['previous_name_enddate']
        ];
        $sql = "SELECT pid FROM " . self::PATIENT_HISTORY_TABLE . " WHERE
            pid = ? AND
            history_type_key = ? AND
            previous_name_prefix = ? AND
            previous_name_first = ? AND
            previous_name_middle = ? AND
            previous_name_last = ? AND
            previous_name_suffix = ? AND
            previous_name_enddate = ?
        ";
        $go_flag = QueryUtils::fetchSingleValue($sql, 'pid', $insertData);
        // return false which calling routine should understand as existing name record
        if (!empty($go_flag)) {
            return false;
        }
        // finish up the insert
        $insertData['created_by'] = $createdBy;
        $insertData['uuid'] = UuidRegistry::getRegistryForTable(self::PATIENT_HISTORY_TABLE)->createUuid();
        $insert = $this->buildInsertColumns($insertData);
        $sql = "INSERT INTO " . self::PATIENT_HISTORY_TABLE . " SET " . $insert['set'];

        return QueryUtils::sqlInsert($sql, $insert['bind']);
    }

    public function formatPreviousName($item)
    {
        if (
            $item['previous_name_enddate'] === '0000-00-00'
            || $item['previous_name_enddate'] === '00/00/0000'
        ) {
            $item['previous_name_enddate'] = '';
        }
        $item['previous_name_enddate'] = oeFormatShortDate($item['previous_name_enddate']);
        $name = ($item['previous_name_prefix'] ? $item['previous_name_prefix'] . " " : "") .
            $item['previous_name_first'] .
            ($item['previous_name_middle'] ? " " . $item['previous_name_middle'] . " " : " ") .
            $item['previous_name_last'] .
            ($item['previous_name_suffix'] ? " " . $item['previous_name_suffix'] : "") .
            ($item['previous_name_enddate'] ? " " . $item['previous_name_enddate'] : "");

        return text($name);
    }

    /**
     * Returns a string to be used to display a patient's age
     *
     * @param type $dobYMD
     * @param type $asOfYMD
     * @return string suitable for displaying patient's age based on preferences
     */
    public function getPatientAgeDisplay($dobYMD, $asOfYMD = null)
    {
        if ($GLOBALS['age_display_format'] == '1') {
            $ageYMD = $this->getPatientAgeYMD($dobYMD, $asOfYMD);
            if (isset($GLOBALS['age_display_limit']) && $ageYMD['age'] <= $GLOBALS['age_display_limit']) {
                return $ageYMD['ageinYMD'];
            } else {
                return $this->getPatientAge($dobYMD, $asOfYMD);
            }
        } else {
            return $this->getPatientAge($dobYMD, $asOfYMD);
        }
    }

    // Returns Age
    //   in months if < 2 years old
    //   in years  if > 2 years old
    // given YYYYMMDD from MySQL DATE_FORMAT(DOB,'%Y%m%d')
    // (optional) nowYMD is a date in YYYYMMDD format
    public function getPatientAge($dobYMD, $nowYMD = null)
    {
        if (empty($dobYMD)) {
            return '';
        }
        // strip any dashes from the DOB
        $dobYMD = preg_replace("/-/", "", $dobYMD);
        $dobDay = substr($dobYMD, 6, 2);
        $dobMonth = substr($dobYMD, 4, 2);
        $dobYear = (int) substr($dobYMD, 0, 4);

        // set the 'now' date values
        if ($nowYMD == null) {
            $nowDay = date("d");
            $nowMonth = date("m");
            $nowYear = date("Y");
        } else {
            $nowDay = substr($nowYMD, 6, 2);
            $nowMonth = substr($nowYMD, 4, 2);
            $nowYear = substr($nowYMD, 0, 4);
        }

        $dayDiff = $nowDay - $dobDay;
        $monthDiff = $nowMonth - $dobMonth;
        $yearDiff = $nowYear - $dobYear;

        $ageInMonths = (($nowYear * 12) + $nowMonth) - (($dobYear * 12) + $dobMonth);

        // We want the age in FULL months, so if the current date is less than the numerical day of birth, subtract a month
        if ($dayDiff < 0) {
            $ageInMonths -= 1;
        }

        if ($ageInMonths > 24) {
            $age = $yearDiff;
            if (($monthDiff == 0) && ($dayDiff < 0)) {
                $age -= 1;
            } elseif ($monthDiff < 0) {
                $age -= 1;
            }
        } else {
            $age = "$ageInMonths " . xl('month');
        }

        return $age;
    }


    /**
     *
     * @param type $dob
     * @param type $date
     * @return array containing
     *      age - decimal age in years
     *      age_in_months - decimal age in months
     *      ageinYMD - formatted string #y #m #d
     */
    public function getPatientAgeYMD($dob, $date = null)
    {
        if ($date == null) {
            $daynow = date("d");
            $monthnow = date("m");
            $yearnow = date("Y");
            $datenow = $yearnow . $monthnow . $daynow;
        } else {
            $datenow = preg_replace("/-/", "", $date);
            $yearnow = substr($datenow, 0, 4);
            $monthnow = substr($datenow, 4, 2);
            $daynow = substr($datenow, 6, 2);
            $datenow = $yearnow . $monthnow . $daynow;
        }

        $dob = preg_replace("/-/", "", $dob);
        $dobyear = substr($dob, 0, 4);
        $dobmonth = substr($dob, 4, 2);
        $dobday = substr($dob, 6, 2);
        $dob = $dobyear . $dobmonth . $dobday;

        //to compensate for 30, 31, 28, 29 days/month
        $mo = $monthnow; //to avoid confusion with later calculation

        if ($mo == 05 or $mo == 07 or $mo == 10 or $mo == 12) {  // determined by monthnow-1
            $nd = 30; // nd = number of days in a month, if monthnow is 5, 7, 9, 12 then
        } elseif ($mo == 03) { // look at April, June, September, November for calculation.  These months only have 30 days.
            // for march, look to the month of February for calculation, check for leap year
            $check_leap_Y = $yearnow / 4; // To check if this is a leap year.
            if (is_int($check_leap_Y)) { // If it true then this is the leap year
                $nd = 29;
            } else { // otherwise, it is not a leap year.
                $nd = 28;
            }
        } else { // other months have 31 days
            $nd = 31;
        }

        $bdthisyear = $yearnow . $dobmonth . $dobday; //Date current year's birthday falls on
        if ($datenow < $bdthisyear) { // if patient hasn't had birthday yet this year
            $age_year = $yearnow - $dobyear - 1;
            if ($daynow < $dobday) {
                $months_since_birthday = 12 - $dobmonth + $monthnow - 1;
                $days_since_dobday = $nd - $dobday + $daynow; //did not take into account for month with 31 days
            } else {
                $months_since_birthday = 12 - $dobmonth + $monthnow;
                $days_since_dobday = $daynow - $dobday;
            }
        } else // if patient has had birthday this calandar year
        {
            $age_year = (int) $yearnow - (int) $dobyear;
            if ($daynow < $dobday) {
                $months_since_birthday = $monthnow - $dobmonth - 1;
                $days_since_dobday = $nd - $dobday + $daynow;
            } else {
                $months_since_birthday = $monthnow - $dobmonth;
                $days_since_dobday = $daynow - $dobday;
            }
        }

        $day_as_month_decimal = $days_since_dobday / 30;
        $months_since_birthday_float = $months_since_birthday + $day_as_month_decimal;
        $month_as_year_decimal = $months_since_birthday_float / 12;
        $age_float = $age_year + $month_as_year_decimal;

        $age_in_months = $age_year * 12 + $months_since_birthday_float;
        $age_in_months = round($age_in_months, 2);  //round the months to xx.xx 2 floating points
        $age = round($age_float, 2);

        // round the years to 2 floating points
        $ageinYMD = $age_year . "y " . $months_since_birthday . "m " . $days_since_dobday . "d";
        return compact('age', 'age_in_months', 'ageinYMD');
    }

    private function parseSuffixForPatientRecord($patientRecord)
    {
        // if we have a suffix populated (that wasn't entered into last name) let's use that.
        if (!empty($patientRecord['suffix'])) {
            return $patientRecord['suffix'];
        }
        // parse suffix from last name. saves messing with LBF
        $suffixes = $this->getPatientSuffixKeys();
        $suffix = null;
        foreach ($suffixes as $s) {
            if (stripos($patientRecord['lname'], $s) !== false) {
                $suffix = $s;
                $result['lname'] = trim(str_replace($s, '', $patientRecord['lname']));
                break;
            }
        }
        return $suffix;
    }

    private function getPatientSuffixKeys()
    {
        if (!isset($this->patientSuffixKeys)) {
            $this->patientSuffixKeys = array(xl('Jr.'), ' ' . xl('Jr'), xl('Sr.'), ' ' . xl('Sr'), xl('II{{patient suffix}}'), xl('III{{patient suffix}}'), xl('IV{{patient suffix}}'));
        }
        return $this->patientSuffixKeys;
    }
}
