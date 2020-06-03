<?php

/**
 * section_fetch.class.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//namespace OnsitePortal;

require_once(dirname(__file__) . "/../../custom/code_types.inc.php");


require_once(dirname(__file__) . '/appsql.class.php');
class FetchSection
{

/*
 * Fetch the current demographics data of a patient from patient_data table
 *
 * @param    pid       Integer   Patient ID
 * @return   records   Array     current patient data
 */
    public function getDemographicsCurrent($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM patient_data
                   WHERE pid = ?";
        $result = $appTable->zQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

/*
 * Fetch the current Problems of a patient from lists table
 *
 * @param    pid       Integer     patient id
 * @return   records   Array       list of problems
 */
    public function getProblems($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM lists
                   WHERE pid = ? AND TYPE = 'medical_problem'";
        $result = $appTable->zQuery($query, array($pid));
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
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM lists
                   WHERE pid = ? AND TYPE = 'allergy'";
        $result = $appTable->zQuery($query, array($pid));
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
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM prescriptions
                   WHERE patient_id = ?";
        $result = $appTable->zQuery($query, array($pid));
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
    public static function getImmunizations($pid)
    {
        $appTable = new ApplicationTable();
        $query         = "SELECT im.*, cd.code_text, DATE(administered_date) AS administered_date,
            DATE_FORMAT(administered_date,'%m/%d/%Y') AS administered_formatted, lo.title as route_of_administration,
            u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.streetb, u.city, u.state, u.zip, u.phonew1,
            f.name, f.phone, lo.notes as route_code
            FROM immunizations AS im
            LEFT JOIN codes AS cd ON cd.code = im.cvx_code
            JOIN code_types AS ctype ON ctype.ct_key = 'CVX' AND ctype.ct_id=cd.code_type
            LEFT JOIN list_options AS lo ON lo.list_id = 'drug_route' AND lo.option_id = im.route
            LEFT JOIN users AS u ON u.id = im.administered_by_id
            LEFT JOIN facility AS f ON f.id = u.facility_id
            WHERE im.patient_id=?";
        $result = $appTable->zQuery($query, array($pid));
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
        $appTable = new ApplicationTable();
        $query = "SELECT CONCAT_WS('',po.procedure_order_id,poc.`procedure_order_seq`) AS tcode,
                          prs.result AS result_value,
                          prs.units, prs.range,
                          poc.procedure_name AS order_title,
                          prs.result_code as result_code,
                          prs.result_text as result_desc,
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
        $result = $appTable->zQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }

/*
 * Fetch the current Vitals of a patient from form_vitals table
 *
 * @param    pid       Integer     patient id
 * @return   records   Array       list of vitals
 */
    public function getVitals($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM form_vitals
                   WHERE pid = ? AND activity=?";
        $result = $appTable->zQuery($query, array($pid, 1));
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
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM history_data
                   WHERE pid=?
                   ORDER BY id DESC LIMIT 1";
        $result = $appTable->zQuery($query, array($pid));
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
    public function getEncounterData($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT form_encounter.*,u.fname AS provider_name
                   FROM form_encounter
                   LEFT JOIN users AS u
                   ON form_encounter.provider_id=u.id
                   WHERE pid = ?";
        $result = $appTable->zQuery($query, array($pid));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
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
        $appTable = new ApplicationTable();
        $query = "SELECT *
                 FROM billing
                 WHERE pid=? AND code_type=?";
        $result = $appTable->zQuery($query, array($pid, 'CPT4'));
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
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM form_care_plan
                   WHERE pid = ? AND activity=?";
        $result = $appTable->zQuery($query, array($pid, 1));
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
        $appTable = new ApplicationTable();
        $query = "SELECT *
                   FROM form_functional_cognitive_status
                   WHERE pid = ? AND activity=?";
        $result = $appTable->zQuery($query, array($pid, 1));
        $records = array();
        foreach ($result as $row) {
            $records[] = $row;
        }

        return $records;
    }
}
