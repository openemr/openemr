<?php

/**
 * AppointmentService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use MongoDB\Driver\Query;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;

class AppointmentService extends BaseService
{
    const TABLE_NAME = "openemr_postcalendar_events";
    const PATIENT_TABLE = "patient_data";
    const PRACTITIONER_TABLE = "users";
    const FACILITY_TABLE = "facility";

    /**
     * @var EncounterService
     */
    private $encounterService;

    /**
     * @var PatientService
     */
    private $patientService;

  /**
   * Default constructor.
   */
    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
        UuidRegistry::createMissingUuidsForTables([self::TABLE_NAME, self::PATIENT_TABLE, self::PRACTITIONER_TABLE,
            self::FACILITY_TABLE]);
    }

    public function setEncounterService(EncounterService $service)
    {
        $this->encounterService = $service;
    }

    public function getEncounterService()
    {
        if (empty($this->encounterService)) {
            $this->encounterService = new EncounterService();
        }
        return $this->encounterService;
    }

    public function setPatientService(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function getPatientService()
    {
        if (empty($this->patientService)) {
            $this->patientService = new PatientService();
        }
        return $this->patientService;
    }

    public function getUuidFields(): array
    {
        return ['puuid', 'pce_aid_uuid', 'pc_uuid'];
    }

    public function validate($appointment)
    {
        $validator = new Validator();

        $validator->required('pc_catid')->numeric();
        $validator->required('pc_title')->lengthBetween(2, 150);
        $validator->required('pc_duration')->numeric();
        $validator->required('pc_hometext')->string();
        $validator->required('pc_apptstatus')->string();
        $validator->required('pc_eventDate')->datetime('Y-m-d');
        $validator->required('pc_startTime')->length(5); // HH:MM is 5 chars
        $validator->required('pc_facility')->numeric();
        $validator->required('pc_billing_location')->numeric();
        $validator->optional('pc_aid')->numeric()
            ->callback(function ($value, $data) {
                $id = QueryUtils::fetchSingleValue('Select id FROM users WHERE id = ? ', 'id', [$value]);
                if (empty($id)) {
                    throw new InvalidValueException('pc_aid must be for a valid user', 'pc_aid');
                }
                return true;
            });
        $validator->optional('pid')->callback(function ($value, $data) {
            $id = QueryUtils::fetchSingleValue('Select id FROM patient_data WHERE pid = ? ', 'id', [$value]);
            if (empty($id)) {
                throw new InvalidValueException('pid must be for a valid patient', 'pid');
            }
            return true;
        });

        return $validator->validate($appointment);
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = "SELECT pce.pc_eid,
                       pce.pc_uuid,
                       pd.puuid,
                       pd.fname,
                       pd.lname,
                       pd.DOB,
                       pd.pid,
                       providers.uuid AS pce_aid_uuid,
                       providers.npi AS pce_aid_npi,
                       pce.pc_aid,
                       pce.pc_apptstatus,
                       pce.pc_eventDate,
                       pce.pc_startTime,
                       pce.pc_endTime,
              	       pce.pc_facility,
                       pce.pc_billing_location,
                       pce.pc_catid,
                       pce.pc_pid,
                       f1.name as facility_name,
                       f2.name as billing_location_name
                       FROM (
                             SELECT 
                               pc_eid,
                               uuid AS pc_uuid, -- we do this because our uuid registry requires the field to be named this way
                               pc_aid,
                               pc_apptstatus,
                               pc_eventDate,
                               pc_startTime,
                               pc_endTime,
                               pc_facility,
                               pc_billing_location,
                               pc_catid,
                               pc_pid
                            FROM 
                                 openemr_postcalendar_events
                       ) pce
                       LEFT JOIN facility as f1 ON pce.pc_facility = f1.id
                       LEFT JOIN facility as f2 ON pce.pc_billing_location = f2.id
                       LEFT JOIN (
                           select uuid AS puuid
                           ,fname
                           ,lname
                           ,DOB
                           ,pid
                           FROM 
                                patient_data
                      ) pd ON pd.pid = pce.pc_pid
                       LEFT JOIN users as providers ON pce.pc_aid = providers.id";

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $processingResult->addData($this->createResultRecordFromDatabaseResult($row));
        }

        return $processingResult;
    }

    public function getAppointmentsForPatient($pid)
    {
        $sqlBindArray = array();

        $sql = "SELECT pce.pc_eid,
                       pce.uuid AS pc_uuid,
                       pd.fname,
                       pd.lname,
                       pd.DOB,
                       pd.pid,
                       pd.uuid AS puuid,
                       providers.uuid AS pce_aid_uuid,
                       providers.npi AS pce_aid_npi,
                       pce.pc_aid,
                       pce.pc_apptstatus,
                       pce.pc_eventDate,
                       pce.pc_startTime,
                       pce.pc_endTime,
              	       pce.pc_facility,
                       pce.pc_billing_location,
                       pce.pc_catid,
                       pce.pc_pid,
                       f1.name as facility_name,
                       f2.name as billing_location_name
                       FROM openemr_postcalendar_events as pce
                       LEFT JOIN facility as f1 ON pce.pc_facility = f1.id
                       LEFT JOIN facility as f2 ON pce.pc_billing_location = f2.id
                       LEFT JOIN patient_data as pd ON pd.pid = pce.pc_pid
                       LEFT JOIN users as providers ON pce.pc_aid = providers.id";

        if ($pid) {
            $sql .= " WHERE pd.pid = ?";
            array_push($sqlBindArray, $pid);
        }

        $records = QueryUtils::fetchRecords($sql, $sqlBindArray);
        $finalRecords = [];
        if (!empty($records)) {
            foreach ($records as $record) {
                $finalRecords[] = $this->createResultRecordFromDatabaseResult($record);
            }
        }
        return $finalRecords;
    }

    public function getAppointment($eid)
    {
        $sql = "SELECT pce.pc_eid,
                       pce.uuid AS pc_uuid,
                       pd.fname,
                       pd.lname,
                       pd.DOB,
                       pd.pid,
                       pd.uuid AS puuid,
                       providers.uuid AS pce_aid_uuid,
                       providers.npi AS pce_aid_npi,
                       pce.pc_aid,
                       pce.pc_apptstatus,
                       pce.pc_eventDate,
                       pce.pc_startTime,
                       pce.pc_endTime,
                       pce.pc_duration,
              	       pce.pc_facility,
                       pce.pc_billing_location,
                       pce.pc_catid,
                       pce.pc_room,
                       pce.pc_pid,
                       f1.name as facility_name,
                       f2.name as billing_location_name
                       FROM openemr_postcalendar_events as pce
                       LEFT JOIN facility as f1 ON pce.pc_facility = f1.id
                       LEFT JOIN facility as f2 ON pce.pc_billing_location = f2.id
                       LEFT JOIN patient_data as pd ON pd.pid = pce.pc_pid
                       LEFT JOIN users as providers ON pce.pc_aid = providers.id
                       WHERE pce.pc_eid = ?";

        $records = QueryUtils::fetchRecords($sql, [$eid]);
        $finalRecords = [];
        if (!empty($records)) {
            foreach ($records as $record) {
                $finalRecords[] = $this->createResultRecordFromDatabaseResult($record);
            }
        }
        return $finalRecords;
    }

    public function insert($pid, $data)
    {
        $startTime = date("H:i:s", strtotime($data['pc_startTime']));
        // TODO: Why are we adding strings with numbers?  How is this even working
        $endTime = $startTime . $data['pc_duration'];
        $uuid = (new UuidRegistry())->createUuid();

        $sql  = " INSERT INTO openemr_postcalendar_events SET";
        $sql .= "     uuid=?";
        $sql .= "     pc_pid=?,";
        $sql .= "     pc_catid=?,";
        $sql .= "     pc_title=?,";
        $sql .= "     pc_duration=?,";
        $sql .= "     pc_hometext=?,";
        $sql .= "     pc_eventDate=?,";
        $sql .= "     pc_apptstatus=?,";
        $sql .= "     pc_startTime=?,";
        $sql .= "     pc_endTime=?,";
        $sql .= "     pc_facility=?,";
        $sql .= "     pc_billing_location=?,";
        $sql .= "     pc_informant=1,";
        $sql .= "     pc_eventstatus=1,";
        $sql .= "     pc_sharing=1,";
        $sql .= "     pc_aid=?";

        $results = sqlInsert(
            $sql,
            array(
                $uuid,
                $pid,
                $data["pc_catid"],
                $data["pc_title"],
                $data["pc_duration"],
                $data["pc_hometext"],
                $data["pc_eventDate"],
                $data['pc_apptstatus'],
                $startTime,
                $endTime,
                $data["pc_facility"],
                $data["pc_billing_location"],
                $data["pc_aid"] ?? null
            )
        );

        return $results;
    }

    public function delete($eid)
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?", $eid);
        return ['message' => 'record deleted'];
    }

    /**
     * Returns a list of categories
     * @return array
     */
    public function getCalendarCategories()
    {
        $sql = "SELECT pc_catid, pc_constant_id, pc_catname, pc_cattype,aco_spec FROM openemr_postcalendar_categories "
        . " WHERE pc_active = 1 ORDER BY pc_seq";
        return QueryUtils::fetchRecords($sql);
    }

    /**
     * check to see if a status code exist as a check in
     * @param $option
     * @return bool
     */
    public function isCheckInStatus($option)
    {
        $row = sqlQuery("SELECT toggle_setting_1 FROM list_options WHERE " .
            "list_id = 'apptstat' AND option_id = ? AND activity = 1", array($option));
        if (empty($row['toggle_setting_1'])) {
            return(false);
        }

        return(true);
    }

    /**
     * check to see if a status code exist as a check out
     * @param $option
     * @return bool
     */
    public function isCheckOutStatus($option)
    {
        $row = sqlQuery("SELECT toggle_setting_2 FROM list_options WHERE " .
            "list_id = 'apptstat' AND option_id = ? AND activity = 1", array($option));
        if (empty($row['toggle_setting_2'])) {
            return(false);
        }

        return(true);
    }

    /**
     * Returns a list of appointment statuses (also used with encounters).
     * @return array
     */
    public function getAppointmentStatuses()
    {
        $listService = new ListService();
        $options = $listService->getOptionsByListName('apptstat', ['activity' => 1]);
        return $options;
    }

    /**
     * Checks to see if the passed in status is a valid appointment status for calendar appointments.
     * @param $status_option_id The status to check if its a valid appointment status
     * @return bool True if its valid, false otherwise
     */
    public function isValidAppointmentStatus($status_option_id)
    {
        $listService = new ListService();
        $option = $listService->getListOption('apptstat', $status_option_id);
        if (!empty($option)) {
            return true;
        }
        return false;
    }

    /**
     * Updates the status for an appointment.  TODO: should be refactored at some point to update the entire record
     * @param $eid number The id of the appointment event
     * @param $status string The status the appointment event should be set to.
     * @param $user number The user performing the update
     * @param $encounter number The encounter of the appointment
     */
    public function updateAppointmentStatus($eid, $status, $user, $encounter = '')
    {
        $appt = $this->getAppointment($eid);
        if (empty($appt)) {
            throw new \InvalidArgumentException("Appointment does not exist for eid " . $eid);
        } else {
            // TODO: Not sure why getAppointment returns an array of records instead of a single record
            $appt = $appt[0];
        }

        $sql = "UPDATE " . self::TABLE_NAME . " SET pc_apptstatus = ? WHERE pc_eid = ? ";
        $binds = [$status, $eid];

        if (!empty($appt['pid'])) {
            $trackerService = new PatientTrackerService();
            $trackerService->manage_tracker_status($appt['pc_eventDate'], $appt['pc_startTime'], $eid, $appt['pid'], $user, $status, $appt['pc_room'], $encounter);
        } else {
            $this->getLogger()->error("AppointmentService->updateAppointmentStatus() failed to update manage_tracker_status"
            . " as patient pid was empty", ['pc_eid' => $eid, 'status' => $status, 'user' => $user, 'encounter' => $encounter]);
        }
        return QueryUtils::sqlStatementThrowException($sql, $binds);
    }

    /**
     * @param $eid
     * @param $pid
     * @return array The most recent encounter for a given appointment
     */
    public function getEncounterForAppointment($pc_eid, $pid)
    {
        $appointment = $this->getAppointment($pc_eid)[0];
        $date = $appointment['pc_eventDate'];
        // we grab the most recent encounter for today's date for the given patient
        $encounterService = $this->getEncounterService();
        $dateField = new DateSearchField('date', ['eq' . $date], DateSearchField::DATE_TYPE_DATE);
        $pidField = new TokenSearchField('pid', [new TokenSearchValue($pid)]);
        // returns the most recent encounter for the given appointment..
        // TODO: @adunsulag we should look at in the future of making an actual join table between encounters and appointments...
        // this fuzzy match by date seems like it will have major problems for both inpatient settings as well as any kind
        // of emergency care (patient sees doctor, patient does telehealth visit during the night due to crisis situation).
        $encounterResult = $encounterService->search(['date' => $dateField, 'pid' => $pidField], true, null, ['limit' => 1]);
        if ($encounterResult->hasData()) {
            $result = $encounterResult->getData();
            return array_pop($result);
        }
        return null;
    }

    public function createEncounterForAppointment($eid)
    {
        $appointment = $this->getAppointment($eid)[0];
        $patientService = $this->getPatientService();
        $patientUuid = UuidRegistry::uuidToString($patientService->getUuid($appointment['pid']));

        $userService = new UserService();
        $user = $userService->getUser($appointment['pc_aid']);
        $authGroup = UserService::getAuthGroupForUser($user['username']);

        $pos_code = QueryUtils::fetchSingleValue(
            "SELECT pos_code FROM facility WHERE id = ?",
            'pos_code',
            $appointment['pc_facility']
        );

        $data = [
            'pc_catid' => $appointment['pc_catid']
            // TODO: where would we get this information if it wasn't defaulted to ambulatory?  Should this be a globals setting?
            // this is imitating the work from encounter_events.inc.php::todaysEncounterCheck
            ,'class_code' => EncounterService::DEFAULT_CLASS_CODE
            ,'puuid' => $patientUuid
            ,'pid' => $appointment['pid']
            ,'provider_id' => $user['id']
            ,'reason' => $appointment['pc_hometext'] ?? xl('Please indicate visit reason')
            ,'facility_id' => $appointment['pc_facility']
            ,'billing_facility' => $appointment['pc_billing_location']
            ,'pos_code' => $pos_code
            ,'user' => $user['username']
            ,'group' => $authGroup
        ];

        $encounterService = $this->getEncounterService();
        $result = $encounterService->insertEncounter($patientUuid, $data);
        if ($result->hasData()) {
            $result = $result->getData();
            return $result[0]['encounter'];
        }
        return null;
    }

    /**
     * Returns the calendar category record from a supplied category id
     * @return array
     */
    public function getOneCalendarCategory($cat_id)
    {
        $sql = "SELECT * FROM openemr_postcalendar_categories WHERE pc_catid = ?";
        return QueryUtils::fetchRecords($sql, [$cat_id]);
    }
}
