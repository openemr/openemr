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

class PatientService extends BaseService
{
    /**
     * In the case where a patient doesn't have a picture uploaded,
     * this value will be returned so that the document controller
     * can return an empty response.
     */
    private $patient_picture_fallback_id = -1;

    private $validator;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('patient_data');
    }

    // make this a comprehensive validation
    public function validate($patient, $context, $id = null)
    {
        $this->validator = new Validator;
        if ($id) {
            $vPid = $this->validatePid($id);
            if ($vPid->isNotValid()) {
                return $vPid;
            }
        }

        $this->validator->context(
            'insert', function (Validator $context) {
                $context->required('fname', "First Name")->lengthBetween(2, 255);
                $context->required('lname', 'Last Name')->lengthBetween(2, 255);
                $context->required('sex', 'Gender')->lengthBetween(4, 30);
                $context->required('DOB', 'Date of Birth')->datetime('Y-m-d');
            }
        );

        $this->validator->context(
            'update', function (Validator $context) {
                $context->copyContext(
                    'insert', function ($rules) {
                        foreach ($rules as $key => $chain) {
                            $chain->required(false);
                        }
                    }
                );
            }
        );

        return $this->validator->validate($patient, $context);
    }

    public function validatePid($pid)
    {
        $this->validator->required('pid')->callback(
            function ($value) {
                return $this->verifyPid($value);
            }
        )->numeric();
        return $this->validator->validate(['pid' => $pid]);
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

        return $pid['pid'] === null ? 1 : $pid['pid'];
    }

    public function insert($data)
    {
        $validationResult = $this->validate($data, 'insert');
        if ($validationResult->isNotValid()) {
            return $validationResult;
        }
        $fresh_pid = $this->getFreshPid();
        $data['pid'] = $fresh_pid;
        $data['pubpid'] = $fresh_pid;
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
            return $fresh_pid;
        }

        return $results;
    }

    public function update($pid, $data)
    {
        $validationResult = $this->validate($data, 'update', $pid);
        if ($validationResult->isNotValid()) {
            return $validationResult;
        }
        $data['date'] = date("Y-m-d H:i:s");

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE patient_data SET ";
        $sql .= $query['set'];
        $sql .= " WHERE pid = ?";
        array_push($query['bind'], $pid);
        return sqlStatement(
            $sql,
            $query['bind']
        );
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

        return $results;
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

        return sqlQuery($sql, $this->pid);
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
