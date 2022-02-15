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

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Patient\PatientCreatedEvent;
use OpenEMR\Events\Patient\PatientUpdatedEvent;
use OpenEMR\Validators\PatientValidator;
use OpenEMR\Validators\ProcessingResult;

class PatientService extends BaseService
{
    const TABLE_NAME = 'patient_data';

    /**
     * In the case where a patient doesn't have a picture uploaded,
     * this value will be returned so that the document controller
     * can return an empty response.
     */
    private $patient_picture_fallback_id = -1;

    private $patientValidator;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
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
        if (empty($data['pubpid'])) {
            $data['pubpid'] = $freshPid;
        }

        $query = $this->buildInsertColumns($data);
        $sql = " INSERT INTO patient_data SET ";
        $sql .= $query['set'];

        $results = sqlInsert(
            $sql,
            $query['bind']
        );
        $data['id'] = $results;

        // Tell subscribers that a new patient has been created
        $patientCreatedEvent = new PatientCreatedEvent($data);
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch(PatientCreatedEvent::EVENT_HANDLE, $patientCreatedEvent, 10);

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
        $table = PatientService::TABLE_NAME;
        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE $table SET ";
        $sql .= $query['set'];
        $sql .= " WHERE `pid` = ?";

        array_push($query['bind'], $data['pid']);
        $sqlResult = sqlStatement($sql, $query['bind']);

        if ($sqlResult) {
            // Tell subscribers that a new patient has been updated
            $patientUpdatedEvent = new PatientUpdatedEvent($dataBeforeUpdate, $data);
            $GLOBALS["kernel"]->getEventDispatcher()->dispatch(PatientUpdatedEvent::EVENT_HANDLE, $patientUpdatedEvent, 10);

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
            $GLOBALS["kernel"]->getEventDispatcher()->dispatch(PatientUpdatedEvent::EVENT_HANDLE, $patientUpdatedEvent, 10);
        }
        return $processingResult;
    }

    /**
     * Returns a list of patients matching optional search criteria.
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
        $sqlBindArray = array();

        //Converting _id to UUID byte
        if (isset($search['uuid'])) {
            $search['uuid'] = UuidRegistry::uuidToBytes($search['uuid']);
        }

        $sql = 'SELECT  id,
                        pid,
                        uuid,
                        pubpid,
                        title,
                        fname,
                        mname,
                        lname,
                        ss,
                        street,
                        postal_code,
                        city,
                        state,
                        county,
                        country_code,
                        drivers_license,
                        contact_relationship,
                        phone_contact,
                        phone_home,
                        phone_biz,
                        phone_cell,
                        email,
                        DOB,
                        sex,
                        race,
                        ethnicity,
                        status
                FROM patient_data';

        if (!empty($search)) {
            $sql .= ' WHERE ';
            $whereClauses = array();
            $wildcardFields = array('fname', 'mname', 'lname', 'street', 'city', 'state','postal_code','title');
            foreach ($search as $fieldName => $fieldValue) {
                // support wildcard match on specific fields
                if (in_array($fieldName, $wildcardFields)) {
                    array_push($whereClauses, $fieldName . ' LIKE ?');
                    array_push($sqlBindArray, '%' . $fieldValue . '%');
                } else {
                    // equality match
                    array_push($whereClauses, $fieldName . ' = ?');
                    array_push($sqlBindArray, $fieldValue);
                }
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
        }
        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $processingResult->addData($row);
        }

        return $processingResult;
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

        $sql = "SELECT  id,
                        pid,
                        uuid,
                        pubpid,
                        title,
                        fname,
                        mname,
                        lname,
                        ss,
                        street,
                        postal_code,
                        city,
                        state,
                        county,
                        country_code,
                        drivers_license,
                        contact_relationship,
                        phone_contact,
                        phone_home,
                        phone_biz,
                        phone_cell,
                        email,
                        DOB,
                        sex,
                        race,
                        ethnicity,
                        status
                FROM patient_data
                WHERE uuid = ?";

        $puuidBinary = UuidRegistry::uuidToBytes($puuidString);
        $sqlResult = sqlQuery($sql, [$puuidBinary]);

        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $processingResult->addData($sqlResult);
        return $processingResult;
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
     * @return false if nothing found otherwise return UUID
     */
    public function getUuid($pid)
    {
        return self::getUuidById($pid, self::TABLE_NAME, 'pid');
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
            $age_year = $yearnow - $dobyear;
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
}
