<?php

/**
 * library of functions useful for searching and updating fee sheet related
 * information
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('fee_sheet_classes.php');
require_once("$srcdir/../custom/code_types.inc.php");
require_once("$srcdir/../library/lists.inc");
require_once("code_check.php");

/**
 * update issues from list of diagnosis
 *
 * This function checks to see if a given list of diagnoses is already
 * associated with the given encounter.  Each diagnoses which has a
 * corresponding entry in the problem list, but isn't already associated gets
 * flagged. (update to issue_encounter table)
 *
 * If the $create parameter is true, any diagnosis which doesn't have a
 * corresponding problem list entry has one created.
 *
 * @param int   $pid        the ID of the patient
 * @param int   $encounter  the encounter ID
 * @param array $diags      a list of diagnoses
 * @param bool  $create     if set issue not already in the patient problem list will be created
 */
function update_issues($pid, $encounter, $diags)
{
    $list_touched = false;  // flag to determine if we have actually affected the medical_problem list.
    $sqlEncounterDate = ('select date FROM form_encounter where encounter=?');
    $res = sqlQuery($sqlEncounterDate, array($encounter));
    $target_date = $res['date'];
    $lists_params = array();
    $encounter_params = array();
    $sqlUpdateIssueDescription = "UPDATE lists SET title=?, modifydate=NOW() WHERE id=? AND TITLE!=?";

    $sqlFindProblem = "SELECT id, title FROM lists WHERE ";
    $sqlFindProblem .= " ( (`begdate` IS NULL) OR (`begdate` IS NOT NULL AND `begdate`<=?) ) AND " ;
    array_push($lists_params, $target_date);
    $sqlFindProblem .= " ( (`enddate` IS NULL) OR (`enddate` IS NOT NULL AND `enddate`>=?) ) ";
    array_push($lists_params, $target_date);
    $sqlFindProblem .= "  AND pid=? AND diagnosis like ? ";
    array_push($lists_params, $pid);
    array_push($lists_params, "");

    $idx_diagnosis = count($lists_params) - 1;

    $sqlFindIssueEncounter = "SELECT encounter FROM issue_encounter WHERE pid=? AND encounter=? AND list_id=?";
    array_push($encounter_params, $pid, $encounter);
    array_push($encounter_params, "");

    $sqlCreateIssueEncounter = " INSERT into issue_encounter(pid,list_id,encounter)values (?,?,?) ";

    $sqlCreateProblem = " INSERT into lists(date,begdate,type,occurrence,classification,pid,diagnosis,title,modifydate) values(?,?,'medical_problem',0,0,?,?,?,NOW())";
    $idx_list_id = count($encounter_params) - 1;
    foreach ($diags as $diags) {
        // ensure that a problem is allowed to be created from the diagnostic element
        if ($diags->allowed_to_create_problem_from_diagnosis != "TRUE") {
            continue;
        }

        $diagnosis_key = $diags->code_type . ":" . $diags->code;
        $list_id = null;
        if ($diags->db_id != null) {
            // If we got a the id for the problem passed in, then use it.
            $list_id = $diags->db_id;
        } else {
            // If not, then search the database for it.
            $lists_params[$idx_diagnosis] = '%' . $diagnosis_key . '%';
            $res = sqlStatement($sqlFindProblem, $lists_params);
            if (sqlNumRows($res) > 0) {
                $list_id = $res->fields['id'];
            }
        }

        if (!($list_id == null)) {
            // We found a problem corresponding to this diagnosis
            $encounter_params[$idx_list_id] = $list_id;
            $issue_encounter = sqlStatement($sqlFindIssueEncounter, $encounter_params);
            if (sqlNumRows($issue_encounter) == 0) {
                // An issue encounter entry didn't exist, so create it
                sqlStatement($sqlCreateIssueEncounter, array($pid,$list_id,$encounter));
            }

            // Check the description in the problem
            sqlStatement($sqlUpdateIssueDescription, array($diags->description,$list_id,$diags->description));
            $list_touched = true;  // Since there is already medical_problem listed, then the list has been touched in the past, so make sure it's flagged correctly
        } else {
            // No Problem found for this diagnosis
            if ($diags->create_problem) { // TODO: per entry create
            // If the create flag is set, then create an entry for this diagnosis.
                sqlStatement($sqlCreateProblem, array($target_date,$target_date,$pid,$diagnosis_key,$diags->description));
                $newProblem = sqlStatement($sqlFindProblem, $lists_params); // requerying the database for the newly created ID, instead of using the sqlInsert return value for backwards compatbility with 4.1.0 and earlier insert ID bug.
                if (sqlNumRows($newProblem) > 0) {
                    $list_id = $newProblem->fields['id'];
                    if ($list_id > 0) {
                        sqlStatement($sqlCreateIssueEncounter, array($pid,$list_id,$encounter));
                    }
                }

                $list_touched = true; // Since we are creating a new problem, the list has been touched
            }
        }
    }

    if ($list_touched) {
        // If the list was touched at any point by this code, then flag it in the DB.
        setListTouch($pid, 'medical_problem');
    }
}

/**
 * create diagnosis entries in the billing table
 *
 * this function checks for billing table entries corresponding to the given
 * list of diagnoses.  It creates an entry for any diagnosis not already listed
 * on the fee sheet for the given encounter.
 *
 * @param int   $req_pid             the ID of the patient
 * @param int   $req_encounter       the encounter ID
 * @param array $diags               a list of diagnoses
 */
function create_diags($req_pid, $req_encounter, $diags)
{
    $authorized = 1;// Need to fix this. hard coded for now
    $provid = 0;
    $rowParams = "(NOW(), ?, ?, ?, ?," . // date, encounter, code_type,code, code_text
            " ?, ?, ?, ?," . // pid, authorized, user, groupname
            "1, 0, ?," .    // activity, billed, provider_id
            " '', NULL, '0.00', '', '', '')"; // modifier, units,fee,ndc_info,justify,notecodes
    $sqlCreateDiag = "insert into billing (date, encounter, code_type, code, code_text, " .
    "pid, authorized, user, groupname, activity, billed, provider_id, " .
    "modifier, units, fee, ndc_info, justify, notecodes) values ";
    $sqlCreateDiag .= $rowParams;

    $sqlUpdateDescription = "UPDATE billing SET code_text=? WHERE id=?";
    $findRow = " SELECT id,code_text FROM billing where activity=1 AND encounter=? AND pid=? and code_type=? and code=?";
    foreach ($diags as $diag) {
        $find_params = array($req_encounter,$req_pid,$diag->getCode_type(),$diag->getCode());
        $search = sqlStatement($findRow, $find_params);
        $count = sqlNumRows($search);
        if ($count == 0) {
            $bound_params = array();
            array_push($bound_params, $req_encounter);
            $diag->addArrayParams($bound_params);
            array_push($bound_params, $req_pid, $authorized, $_SESSION['authUserID'], $_SESSION['authProvider'], $provid);
            $res = sqlInsert($sqlCreateDiag, $bound_params);
        } else {
            // update the code_text;
            $billing_entry = sqlFetchArray($search);
            $code_text = $billing_entry['code_text'];
            if ($code_text != $diag->description) {
                sqlStatement($sqlUpdateDescription, array($diag->description,$billing_entry['id']));
            }
        }
    }
}

/**
 * create procedure entries in the billing table
 *
 * this function checks for billing table entries corresponding to the given
 * list of procedures.  It creates an entry for any procedure not already listed
 * on the fee sheet for the given encounter
 *
 * @param int   $req_pid             the ID of the patient
 * @param int   $req_encounter       the encounter ID
 * @param array $procs               a list of procedures
 */
function create_procs($req_pid, $req_encounter, $procs)
{
    $authorized = 1;// Need to fix this. hard coded for now
    $provid = 0;
    $sql = "insert into billing (" .
            "date,      encounter,  code_type,  code," .
            "code_text, pid,        authorized, user," .
            "groupname, activity,   billed,     provider_id, " .
            "modifier,  units,      fee,        ndc_info, " .
            "justify,   notecodes" .
            ") values ";
    $param = "(NOW(),?,?,?," . // date, encounter, code_type, code
            "?,?,?,?," .     // code_text,pid,authorized,user
            "?,1,0,?," .     // groupname,activity,billed,provider_id
            "?,?,?,''," .     // modifier, units, fee, ndc_info
            "?,'')";        // justify, notecodes
    foreach ($procs as $proc) {
        $insert_params = array();
        array_push($insert_params, $req_encounter);
        $proc->addArrayParams($insert_params);
        array_push($insert_params, $req_pid, $authorized, $_SESSION['authUserID'], $_SESSION['authProvider'], $provid);
        $proc->addProcParameters($insert_params);
        sqlStatement($sql . $param, $insert_params);
    }
}

/**
 * retrieve the diagnoses from the given patient's problem list
 *
 * All the problems are included, but problems which have been
 * flagged as part of the given encounter are marked as such and
 * also sorted earlier in the list.
 *
 * @param int   $pid             the ID of the patient
 * @param int   $encounter       the encounter ID
 * @return array - returns an array of the diagnoses
 */
function issue_diagnoses($pid, $encounter)
{
    $retval = array();
    $parameters = array($encounter,$pid);
    $sql = "SELECT l.diagnosis as diagnosis,l.title as title, NOT ISNULL(ie.encounter) as selected, l.id " .
          " FROM lists as l" .
          " LEFT JOIN issue_encounter as ie ON ie.list_id=l.id AND ie.encounter=?" .
          " WHERE l.type='medical_problem'" .
          " AND l.pid=?" . // 1st parameter pid
          " AND ( (l.begdate IS NULL) OR (l.begdate IS NOT NULL AND l.begdate<=NOW()) ) AND " .
          " ( ( l.enddate IS NULL) OR (l.enddate IS NOT NULL AND l.enddate>=NOW()) ) " .
          " ORDER BY ie.encounter DESC,l.id";
    $results = sqlStatement($sql, $parameters);
    while ($res = sqlFetchArray($results)) {
        $title = $res['title'];
        $db_id = $res['id'];
        $codes = explode(";", $res['diagnosis']);
        foreach ($codes as $code_key) {
            $diagnosis = explode(":", $code_key);
            $code = $diagnosis[1] ?? '';
            $code_type = $diagnosis[0];
            $new_info = new code_info($code, $code_type, $title, $res['selected'] != 0);

            //ensure that a diagnostic element is allowed to be created from a problem element
            if ($new_info->allowed_to_create_diagnosis_from_problem != "TRUE") {
                continue;
            }

            $new_info->db_id = $db_id;
            $retval[] = $new_info;
        }
    }

    return $retval;
}

/**
 * retrieve the most common diagnoses
 *
 * queries the billing table for the most frequently used diagnosis codes.
 *
 * @param int   $limit               the max number of rows to return
 * @return array - returns an array of the diagnoses
 */


function common_diagnoses($limit = 10)
{
    $retval = array();
    $parameters = array($limit);
    $sql = "SELECT code_type, code, code_text,count(code) as num " .
         " FROM billing WHERE code_type in (" . diag_code_types('keylist', true) . ")" .  // include all code types
         " GROUP BY code_type,code,code_text ORDER BY num desc LIMIT ?";
    $results = sqlStatement($sql, $parameters);
    while ($res = sqlFetchArray($results)) {
        $title = $res['code_text'];
        $code = $res['code'];
        $code_type = $res['code_type'];
        $retval[] = new code_info($code, $code_type, $title, 0);
    }

    return $retval;
}

/**
 * retrieve the entries for the specified encounter's fee sheet
 *
 *
 * @param int   $pid             the ID of the patient
 * @param int   $encounter       the encounter ID
 * @param array &$diagnoses      return by reference of all the diagnoses
 * @param array &$procedures     return by reference of all the procedures
 *
 */
function fee_sheet_items($pid, $encounter, &$diagnoses, &$procedures)
{
    $param = array($encounter);
    $sql = "SELECT code,code_type,code_text,fee,modifier,justify,units,ct_diag,ct_fee,ct_mod "
          . " FROM billing, code_types as ct "
          . " WHERE encounter=? AND billing.activity>0 AND ct.ct_key=billing.code_type "
          . " ORDER BY id";
    $results = sqlStatement($sql, $param);
    while ($res = sqlFetchArray($results)) {
        $code = $res['code'];
        $code_type = $res['code_type'];
        $code_text = $res['code_text'];
        if ($res['ct_diag'] == '1') {
            $diagnoses[] = new code_info($code, $code_type, $code_text);
        } elseif ($res['ct_fee'] == 1) {
            $fee = $res['fee'];
            $justify = $res['justify'];
            $modifiers = $res['modifier'];
            $units = $res['units'];
            $selected = true;
            $mod_size = $res['ct_mod'];
            $procedures[] = new procedure($code, $code_type, $code_text, $fee, $justify, $modifiers, $units, $mod_size, $selected);
        }
    }
}


/**
 * retrieve the details of the specified patient's encounters, except for the
 * current (specified by $encounter)
 *
 * @param int   $pid             the ID of the patient
 * @param int   $encounter       the encounter ID
 */
function select_encounters($pid, $encounter)
{
    $retval = array();
    $parameters = array($pid,$encounter);
    $sql = "SELECT DATE(date) as date,encounter " .
         " FROM form_encounter " .
         " WHERE pid=? and encounter!=? " .
         " ORDER BY date DESC";
    $results = sqlStatement($sql, $parameters);
    while ($res = sqlFetchArray($results)) {
        $retval[] = new encounter_info($res['encounter'], $res['date']);
    }

    return $retval;
}

/**
 * Update the justify field for the given billing entry
 *
 *
 *
 * @param int   $pid                the ID of the patient
 * @param int   $enc                the encounter ID
 * @param array $diags              the list of justification codes
 * @param int   $billing_id         the identifier in the billing table of the
 *                                  row to update
 */
function update_justify($pid, $enc, $diags, $billing_id)
{
    $justify = "";
    foreach ($diags as $diag) {
        $justify .= $diag->getKey() . ":";
    }

    $sqlUpdate = " UPDATE billing SET justify=? "
              . " WHERE id=?";
    $params = array($justify,$billing_id);
    sqlStatement($sqlUpdate, $params);
}
