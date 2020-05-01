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

use Particle\Validator\Validator;
use OpenEMR\Validators\PatientValidator;
use OpenEMR\Validators\ProcessingResult;

class PatientService extends BaseService
{
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
        parent::__construct('patient_data');
        $this->patientValidator = new PatientValidator();
    }
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    public function getPid()
    {
        return $this->pid;
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
     * @return array(isValid => true|false, data => )
     */
    public function insert($data)
    {
        $processingResult = $this->patientValidator->validate($data, PatientValidator::DATABASE_INSERT_CONTEXT);

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $freshPid = $this->getFreshPid();
        $data['pid'] = $freshPid;
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
            $processingResult->setData($freshPid);
            return $processingResult;
        } else {
            $processingResult->addProcessingError("error processing SQL Insert");
        }

        return $processingResult;
    }

    /**
     * Updates an existing patient record.
     *
     * @param $pid - The patient identifier (PID) used for update.
     * @param $data - The updated patient data fields
     * @return
     */
    public function update($pid, $data)
    {
        $data["pid"] = $pid;
        $processingResult = $this->patientValidator->validate($data, PatientValidator::DATABASE_UPDATE_CONTEXT);
        if (!$processingResult->isValid()) {
            return $processingResult;
        }
        $data['date'] = date("Y-m-d H:i:s");

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE patient_data SET ";
        $sql .= $query['set'];
        $sql .= " WHERE pid = ?";
        
        array_push($query['bind'], $pid);
        $sqlResult = sqlStatement($sql, $query['bind']);

        if (!$sqlResult) {
            $processingResult->addErrorMessage("error processing SQL Update");
        }

        return $processingResult;
    }

    /**
     * Returns a list of patients matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @return patient records matching criteria.
     */
    public function getAll($search = array())
    {
        $sqlBindArray = array();

        $sql = 'SELECT  id,
                        pid,
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

            foreach ($search as $fieldName => $fieldValue) {
                // support wildcard match on specific fields
                if (in_array($fieldName, array('fname', 'lname', 'street'))) {
                    array_push($whereClauses, $fieldName . ' LIKE ?');
                    array_push($sqlBindArray, '%' . $fieldValue . '%');
                } else {
                    // equality match
                    array_push($whereClauses, $fieldName . ' = ?');
                    array_push($sqlBindArray, $fieldValue);
                }
            }
            $sql .= implode(" AND ", $whereClauses);
        }

        $statementResults = sqlStatement($sql, $sqlBindArray);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        $processingResult = new ProcessingResult();
        $processingResult->setData($results);
        return $processingResult;
    }

    /**
     * Returns a single patient record by patient id.
     */
    public function getOne()
    {
        $sql = "SELECT  id,
                        pid,
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
                WHERE pid = ?";

        $sqlResult = sqlQuery($sql, $this->pid);
        $processingResult = new ProcessingResult();
        $processingResult->setData($sqlResult);
        return $processingResult;
    }

    /**
     * @return number
     */
    public function getPatientPictureDocumentId()
    {
        $sql = "SELECT doc.id AS id
                 FROM documents doc
                 JOIN categories_to_documents cate_to_doc
                   ON doc.id = cate_to_doc.document_id
                 JOIN categories cate
                   ON cate.id = cate_to_doc.category_id
                WHERE cate.name LIKE ? and doc.foreign_id = ?";

        $result = sqlQuery($sql, array($GLOBALS['patient_photo_category_name'], $this->pid));

        if (empty($result) || empty($result['id'])) {
            return $this->patient_picture_fallback_id;
        }

        return $result['id'];
    }
}
