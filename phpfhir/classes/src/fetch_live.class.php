<?php
/**
 * FetchLiveData class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

namespace oeFHIR;

require_once(dirname(__file__) . "/../../../custom/code_types.inc.php");

class FetchLiveData
{
    /**
     * Function oeQuery
     * All DB Transactions take place
     *
     * @param String $sql SQL Query Statement
     * @param array $params SQL Parameters
     * @param boolean $log Logging True / False
     * @param boolean $error Error Display True / False
     * @return   records   Array
     */
    public function oeQuery($sql, $params = [], $log = false, $error = true)
    {
        $return = false;
        $result = false;

        try {
            $return = sqlStatement($sql, $params);
            $result = true;
        } catch (Exception $e) {
            if ($error) {
            }
        }

        return $return;
    }

    /*
     * Fetch the current demographics data of a patient from patient_data table
     *
     * @param    pid       Integer   Patient ID
     * @return   records   Array     current patient data
     */
    public function getDemographicsCurrent($pid)
    {

        $query = "SELECT *
                   FROM patient_data
                   WHERE pid = ?";
        $result = $this->oeQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records[0];
    }

    /*
     * Fetch the current Problems of a patient from lists table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of problems
     */
    public function getProblems($pid)
    {

        $query = "SELECT *
                   FROM lists
                   WHERE pid = ? AND TYPE = 'medical_problem'";
        $result = $this->oeQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Allergies of a patient from lists table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of allergies
     */
    public function getAllergies($pid)
    {

        $query = "SELECT *
                   FROM lists
                   WHERE pid = ? AND TYPE = 'allergy'";
        $result = $this->oeQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Medications of a patient from prescriptions table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of medications
     */
    public function getMedications($pid)
    {

        $query = "SELECT *
                   FROM prescriptions
                   WHERE patient_id = ?";
        $result = $this->oeQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Immunizations of a patient from immunizations table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of immunizations
     */
    public function getImmunizations($pid)
    {

        $query = "SELECT im.*, cd.code_text, DATE(administered_date) AS administered_date,
            DATE_FORMAT(administered_date,'%m/%d/%Y') AS administered_formatted, lo.title AS route_of_administration,
            u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.streetb, u.city, u.state, u.zip, u.phonew1,
            f.name, f.phone, lo.notes AS route_code
            FROM immunizations AS im
            LEFT JOIN codes AS cd ON cd.code = im.cvx_code
            JOIN code_types AS ctype ON ctype.ct_key = 'CVX' AND ctype.ct_id=cd.code_type
            LEFT JOIN list_options AS lo ON lo.list_id = 'drug_route' AND lo.option_id = im.route
            LEFT JOIN users AS u ON u.id = im.administered_by_id
            LEFT JOIN facility AS f ON f.id = u.facility_id
            WHERE im.patient_id=?";
        $result = $this->oeQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the currect Lab Results of a patient
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of lab results
     */
    public function getLabResults($pid)
    {

        $query = "SELECT CONCAT_WS('',po.procedure_order_id,poc.`procedure_order_seq`) AS tcode,
                          prs.result AS result_value,
                          prs.units, prs.range,
                          poc.procedure_name AS order_title,
                          prs.result_code AS result_code,
                          prs.result_text AS result_desc,
                          po.date_ordered,
                          prs.date AS result_time,
                          prs.abnormal AS abnormal_flag,
                          prs.procedure_result_id AS result_id
                   FROM procedure_order AS po
                   JOIN procedure_order_code AS poc ON poc.`procedure_order_id`=po.`procedure_order_id`
                   JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id
                        AND pr.`procedure_order_seq`=poc.`procedure_order_seq`
                   JOIN procedure_result AS prs ON prs.procedure_report_id = pr.procedure_report_id
                   WHERE po.patient_id = ? AND prs.result NOT IN ('DNR','TNP')";
        $result = $this->oeQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    public function getUser($id)
    {

        $query = "SELECT *
                   FROM users
                   WHERE id = ?";
        $result = $this->oeQuery($query, array($id));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records[0];
    }

    /*
     * Fetch the current Vitals of a patient from form_vitals table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of vitals
     */
    public function getVitals($pid)
    {

        $query = "SELECT *
                   FROM form_vitals
                   WHERE pid = ? AND activity=?";
        $result = $this->oeQuery($query, array($pid, 1));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the social history of a patient from history_data table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       history data
     */
    public function getSocialHistory($pid)
    {

        $query = "SELECT *
                   FROM history_data
                   WHERE pid=?
                   ORDER BY id DESC LIMIT 1";
        $result = $this->oeQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
      * Fetch the list of encounter Id's of a patient from form_encounter table
      *
      * @param    pid       Integer     patient id
      * @return   records   Array       encounter data
      */
    public function getEncounterIdList($pid)
    {

        $query = "SELECT form_encounter.encounter,form_encounter.reason
                   FROM form_encounter
                   WHERE pid = ?";

        $result = $this->oeQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the encounter data of a patient from form_encounter table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       encounter data
     */
    public function getEncounterData($pid, $eid = '')
    {

        $query = "SELECT form_encounter.*,u.lname AS provider_name
                   FROM form_encounter
                   LEFT JOIN users AS u
                   ON form_encounter.provider_id=u.id
                   WHERE pid = ?";
        $data = array($pid);
        if ($eid) {
            $query .= " && encounter=?";
            $data = array($pid, $eid);
        }
        $result = $this->oeQuery($query, $data);
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }
        if ($eid) {
            $records = $records[0];
        }
        return $records;
    }

    /*
     * Fetch the billing data of a patient from billing table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       billing data
     */
    public function getProcedure($pid)
    {

        $query = "SELECT *
                 FROM billing
                 WHERE pid=? AND code_type=?";
        $result = $this->oeQuery($query, array($pid, 'CPT4'));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Care Plan of a patient from form_care_plan table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of Care Plans
     */
    public function getCarePlan($pid)
    {

        $query = "SELECT *
                   FROM form_care_plan
                   WHERE pid = ? AND activity=?";
        $result = $this->oeQuery($query, array($pid, 1));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

    /*
     * Fetch the current Functional Cognitive Status of a patient from form_functional_cognitive_status table
     *
     * @param    pid       Integer     patient id
     * @return   records   Array       list of Functional Cognitive Status
     */
    public function getFunctionalCognitiveStatus($pid)
    {

        $query = "SELECT *
                   FROM form_functional_cognitive_status
                   WHERE pid = ? AND activity=?";
        $result = $this->oeQuery($query, array($pid, 1));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }
}
