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

        $freshPid = $this->getFreshPid();
        $data['pid'] = $freshPid;
        $data['uuid'] = (new UuidRegistry(['table_name' => 'patient_data']))->createUuid();
        $data['date'] = date("Y-m-d H:i:s");
        $data['regdate'] = date("Y-m-d H:i:s");

        $query = $this->buildInsertColumns($data);
        $sql = " INSERT INTO patient_data SET ";
        $sql .= $query['set'];

        $results = sqlInsert(
            $sql,
            $query['bind']
        );

        if ($results) {
            $processingResult->addData(array(
                'pid' => $freshPid,
                'uuid' => UuidRegistry::uuidToString($data['uuid'])
            ));
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
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
}
