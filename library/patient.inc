<?php

/**
 * patient.inc includes functions for manipulating patient information.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018-2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2021-2022 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\SocialHistoryService;

require_once(dirname(__FILE__) . "/dupscore.inc.php");

global $facilityService;
$facilityService = new FacilityService();

// These are for sports team use:
$PLAYER_FITNESSES = array(
  xl('Full Play'),
  xl('Full Training'),
  xl('Restricted Training'),
  xl('Injured Out'),
  xl('Rehabilitation'),
  xl('Illness'),
  xl('International Duty')
);
$PLAYER_FITCOLORS = array('#6677ff', '#00cc00', '#ffff00', '#ff3333', '#ff8800', '#ffeecc', '#ffccaa');

// Hard-coding this array because its values and meanings are fixed by the 837p
// standard and we don't want people messing with them.
global $policy_types;
$policy_types = array(
  ''   => xl('N/A'),
  '12' => xl('Working Aged Beneficiary or Spouse with Employer Group Health Plan'),
  '13' => xl('End-Stage Renal Disease Beneficiary in MCP with Employer`s Group Plan'),
  '14' => xl('No-fault Insurance including Auto is Primary'),
  '15' => xl('Worker`s Compensation'),
  '16' => xl('Public Health Service (PHS) or Other Federal Agency'),
  '41' => xl('Black Lung'),
  '42' => xl('Veteran`s Administration'),
  '43' => xl('Disabled Beneficiary Under Age 65 with Large Group Health Plan (LGHP)'),
  '47' => xl('Other Liability Insurance is Primary'),
);

/**
 * Get a patient's demographic data.
 *
 * @param int    $pid   The PID of the patient
 * @param string $given an optional subsection of the patient's demographic
 *                      data to retrieve.
 * @return array The requested subsection of a patient's demographic data.
 *               If no subsection was given, returns everything, with the
 *               date of birth as the last field.
 */
// To prevent sql injection on this function, if a variable is used for $given parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getPatientData($pid, $given = "*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS")
{
    $sql = "select $given from patient_data where pid=? order by date DESC limit 0,1";
    return sqlQuery($sql, array($pid));
}

function getInsuranceProvider($ins_id)
{

    $sql = "select name from insurance_companies where id=?";
    $row = sqlQuery($sql, array($ins_id));
    return $row['name'] ?? '';
}

function getInsuranceProviders()
{
    $returnval = array();

    if (true) {
        $sql = "select name, id from insurance_companies where inactive != 1 order by name, id";
        $rez = sqlStatement($sql);
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $returnval[$row['id']] = $row['name'];
        }
    } else { // Please leave this here. I have a user who wants to see zip codes and PO
        // box numbers listed along with the insurance company names, as many companies
        // have different billing addresses for different plans.  -- Rod Roark
        $sql = "select insurance_companies.name, insurance_companies.id, " .
          "addresses.zip, addresses.line1 " .
          "from insurance_companies, addresses " .
          "where addresses.foreign_id = insurance_companies.id " .
          "order by insurance_companies.name, addresses.zip";

        $rez = sqlStatement($sql);

        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            preg_match("/\d+/", $row['line1'], $matches);
            $returnval[$row['id']] = $row['name'] . " (" . $row['zip'] .
              "," . $matches[0] . ")";
        }
    }

    return $returnval;
}

function getInsuranceProvidersExtra()
{
    $returnval = array();
    // add a global and if for where to allow inactive inscompanies

    $sql = "SELECT insurance_companies.name, insurance_companies.id, insurance_companies.cms_id,
            addresses.line1, addresses.line2, addresses.city, addresses.state, addresses.zip
            FROM insurance_companies, addresses
            WHERE addresses.foreign_id = insurance_companies.id
            AND insurance_companies.inactive != 1
            ORDER BY insurance_companies.name, addresses.zip";

    $rez = sqlStatement($sql);

    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        switch ($GLOBALS['insurance_information']) {
            case $GLOBALS['insurance_information'] = '0':
                $returnval[$row['id']] = $row['name'];
                break;
            case $GLOBALS['insurance_information'] = '1':
                $returnval[$row['id']] = $row['name'] . " (" . $row['line1'] . ", " . $row['line2'] . ")";
                break;
            case $GLOBALS['insurance_information'] = '2':
                $returnval[$row['id']] = $row['name'] . " (" . $row['line1'] . ", " . $row['line2'] . ", " . $row['zip'] . ")";
                break;
            case $GLOBALS['insurance_information'] = '3':
                $returnval[$row['id']] = $row['name'] . " (" . $row['line1'] . ", " . $row['line2'] . ", " . $row['state'] . ")";
                break;
            case $GLOBALS['insurance_information'] = '4':
                $returnval[$row['id']] = $row['name'] . " (" . $row['line1'] . ", " . $row['line2'] . ", " . $row['state'] .
                    ", " . $row['zip'] . ")";
                break;
            case $GLOBALS['insurance_information'] = '5':
                $returnval[$row['id']] = $row['name'] . " (" . $row['line1'] . ", " . $row['line2'] . ", " . $row['city'] .
                    ", " . $row['state'] . ", " . $row['zip'] . ")";
                break;
            case $GLOBALS['insurance_information'] = '6':
                $returnval[$row['id']] = $row['name'] . " (" . $row['line1'] . ", " . $row['line2'] . ", " . $row['city'] .
                    ", " . $row['state'] . ", " . $row['zip'] . ", " . $row['cms_id'] . ")";
                break;
            case $GLOBALS['insurance_information'] = '7':
                preg_match("/\d+/", $row['line1'], $matches);
                $returnval[$row['id']] = $row['name'] . " (" . $row['zip'] .
                    "," . $matches[0] . ")";
                break;
        }
    }

    return $returnval;
}

// ----------------------------------------------------------------------------
// Get one facility row.  If the ID is not specified, then get either the
// "main" (billing) facility, or the default facility of the currently
// logged-in user.  This was created to support genFacilityTitle() but
// may find additional uses.
//
function getFacility($facid = 0)
{
    global $facilityService;

    $facility = null;

    if ($facid > 0) {
        return $facilityService->getById($facid);
    }

    if ($GLOBALS['login_into_facility']) {
        //facility is saved in sessions
        $facility  = $facilityService->getById($_SESSION['facilityId']);
    } else {
        if ($facid == 0) {
            $facility = $facilityService->getPrimaryBillingLocation();
        } else {
            $facility = $facilityService->getFacilityForUser($_SESSION['authUserID']);
        }
    }

    return $facility;
}

// Generate a report title including report name and facility name, address
// and phone.
//
function genFacilityTitle($repname = '', $facid = 0, $logo = "")
{
    $s = '';
    $s .= "<table class='ftitletable' width='100%'>\n";
    $s .= " <tr>\n";
    if (empty($logo)) {
        $s .= "  <td align='left' class='ftitlecell1'>" . text($repname) . "</td>\n";
    } else {
        $s .= "  <td align='left' class='ftitlecell1'><img class='h-auto' style='max-height:8%;' src='" . attr($logo) . "' /></td>\n";
        $s .= "  <td align='left' class='ftitlecellm'><h2>" . text($repname) . "</h2></td>\n";
    }
    $s .= "  <td align='right' class='ftitlecell2'>\n";
    $r = getFacility($facid);
    if (!empty($r)) {
        $s .= "<b>" . text($r['name'] ?? '') . "</b>\n";
        if (!empty($r['street'])) {
            $s .= "<br />" . text($r['street']) . "\n";
        }

        if (!empty($r['city']) || !empty($r['state']) || !empty($r['postal_code'])) {
            $s .= "<br />";
            if ($r['city']) {
                $s .= text($r['city']);
            }

            if ($r['state']) {
                if ($r['city']) {
                    $s .= ", \n";
                }

                $s .= text($r['state']);
            }

            if ($r['postal_code']) {
                $s .= " " . text($r['postal_code']);
            }

            $s .= "\n";
        }

        if (!empty($r['country_code'])) {
            $s .= "<br />" . text($r['country_code']) . "\n";
        }

        if (preg_match('/[1-9]/', ($r['phone'] ?? ''))) {
            $s .= "<br />" . text($r['phone']) . "\n";
        }
    }

    $s .= "  </td>\n";
    $s .= " </tr>\n";
    $s .= "</table>\n";
    return $s;
}

/**
GET FACILITIES

returns all facilities or just the id for the first one
(FACILITY FILTERING (lemonsoftware))

@param string - if 'first' return first facility ordered by id
@return array | int for 'first' case
*/
function getFacilities($first = '')
{
    global $facilityService;

    $fres = $facilityService->getAllFacility();

    if ($first == 'first') {
        return $fres[0]['id'];
    } else {
        return $fres;
    }
}

//(CHEMED) facility filter
function getProviderInfo($providerID = "%", $providers_only = true, $facility = '')
{
    $param1 = "";
    if ($providers_only === 'any') {
        $param1 = " AND authorized = 1 AND active = 1 ";
    } elseif ($providers_only) {
        $param1 = " AND authorized = 1 AND calendar = 1 ";
    }

    //--------------------------------
    //(CHEMED) facility filter
    $param2 = "";
    if ($facility) {
        if ($GLOBALS['restrict_user_facility']) {
            $param2 = " AND (facility_id = '" . add_escape_custom($facility) . "' OR  '" . add_escape_custom($facility) . "' IN (select facility_id from users_facility where tablename = 'users' and table_id = id))";
        } else {
            $param2 = " AND facility_id = '" . add_escape_custom($facility) . "' ";
        }
    }

    //--------------------------------

    $command = "=";
    if ($providerID == "%") {
        $command = "like";
    }

// removing active from query since is checked above with $providers_only argument
    $query = "select distinct id, username, lname, fname, authorized, info, facility, suffix " .
        "from users where username != '' and id $command '" .
        add_escape_custom($providerID) . "' " . $param1 . $param2;
    // sort by last name -- JRM June 2008
    $query .= " ORDER BY lname, fname ";
    $rez = sqlStatement($query);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    //if only one result returned take the key/value pairs in array [0] and merge them down into
    // the base array so that $resultval[0]['key'] is also accessible from $resultval['key']

    if ($iter == 1) {
        $akeys = array_keys($returnval[0]);
        foreach ($akeys as $key) {
            $returnval[0][$key] = $returnval[0][$key];
        }
    }

    return ($returnval ?? null);
}

function getProviderName($providerID, $provider_only = 'any')
{
    $pi = getProviderInfo($providerID, $provider_only);
    if (!empty($pi[0]["lname"]) && (strlen($pi[0]["lname"]) > 0)) {
        if (!empty($pi[0]["suffix"]) && (strlen($pi[0]["suffix"]) > 0)) {
            $pi[0]["lname"] .= ", " . $pi[0]["suffix"];
        }

        return $pi[0]['fname'] . " " . $pi[0]['lname'];
    }

    return "";
}

function getProviderId($providerName)
{
    $query = "select id from users where username = ?";
    $rez = sqlStatement($query, array($providerName));
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    return $returnval;
}

// To prevent sql injection on this function, if a variable is used for $given parameter, then
// it needs to be escaped via whitelisting prior to using this function; see lines 2020-2121 of
// library/clinical_rules.php script for example of this.
function getHistoryData($pid, $given = "*", $dateStart = '', $dateEnd = '')
{
    $where = '';
    if ($given == 'tobacco') {
        $where = 'tobacco is not null and';
    }

    if ($dateStart && $dateEnd) {
        $res = sqlQuery("select $given from history_data where $where pid = ? and date >= ? and date <= ? order by date DESC, id DESC limit 0,1", array($pid,$dateStart,$dateEnd));
    } elseif ($dateStart && !$dateEnd) {
        $res = sqlQuery("select $given from history_data where $where pid = ? and date >= ? order by date DESC, id DESC limit 0,1", array($pid,$dateStart));
    } elseif (!$dateStart && $dateEnd) {
        $res = sqlQuery("select $given from history_data where $where pid = ? and date <= ? order by date DESC, id DESC limit 0,1", array($pid,$dateEnd));
    } else {
        $res = sqlQuery("select $given from history_data where $where pid = ? order by date DESC, id DESC limit 0,1", array($pid));
    }

    return $res;
}

// function getInsuranceData($pid, $type = "primary", $given = "insd.*, DATE_FORMAT(subscriber_DOB,'%m/%d/%Y') as subscriber_DOB, ic.name as provider_name")
// To prevent sql injection on this function, if a variable is used for $given parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getInsuranceData($pid, $type = "primary", $given = "insd.*, ic.name as provider_name")
{
    $sql = "select $given from insurance_data as insd " .
    "left join insurance_companies as ic on ic.id = insd.provider " .
    "where pid = ? and type = ? order by date DESC limit 1";
    return sqlQuery($sql, array($pid, $type));
}

// To prevent sql injection on this function, if a variable is used for $given parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getInsuranceDataByDate(
    $pid,
    $date,
    $type,
    $given = "insd.*, DATE_FORMAT(subscriber_DOB,'%m/%d/%Y') as subscriber_DOB, ic.name as provider_name"
) {
 // this must take the date in the following manner: YYYY-MM-DD
  // this function recalls the insurance value that was most recently enterred from the
  // given date. it will call up most recent records up to and on the date given,
  // but not records enterred after the given date
    $sql = "select $given from insurance_data as insd " .
    "left join insurance_companies as ic on ic.id = provider " .
    "where pid = ? and (date_format(date,'%Y-%m-%d') <= ? OR date IS NULL) and " .
    "type=? order by date DESC limit 1";
    return sqlQuery($sql, array($pid,$date,$type));
}

// To prevent sql injection on this function, if a variable is used for $given parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getEmployerData($pid, $given = "*")
{
    $sql = "select $given from employer_data where pid=? order by date DESC limit 0,1";
    return sqlQuery($sql, array($pid));
}

// Generate a consistent header and footer, used for printed patient reports
function genPatientHeaderFooter($pid, $DOS = null)
{
    $patient_dob = getPatientData($pid, "DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
    $patient_name = getPatientName($pid);

    // Header
    $s = '<htmlpageheader name="PageHeader1"><div style="text-align: right; font-weight: bold;">';
    $s .= text($patient_name) . '&emsp;DOB: ' . text($patient_dob['DOB_TS']);
    if ($DOS) {
        $s .= '&emsp;DOS: ' . text($DOS);
    }
    $s .= '</div></htmlpageheader>';

    // Footer
    $s .= '<htmlpagefooter name="PageFooter1"><div style="text-align: right; font-weight: bold;">';
    $s .= '<div style="float: right; width:33%; text-align: left;">' . oeFormatDateTime(date("Y-m-d H:i:s")) . '</div>';
    $s .= '<div style="float: right; width:33%; text-align: center;">{PAGENO}/{nbpg}</div>';
    $s .= '<div style="float: right; width:33%; text-align: right;">' . text($patient_name) . '</div>';
    $s .= '</div></htmlpagefooter>';

    // Set the header and footer in the current document
    $s .= '<sethtmlpageheader name="PageHeader1" page="ALL" value="ON" show-this-page="1" />';
    $s .= '<sethtmlpagefooter name="PageFooter1" page="ALL" value="ON" />';

    return $s;
}

function _set_patient_inc_count($limit, $count, $where, $whereBindArray = array())
{
  // When the limit is exceeded, find out what the unlimited count would be.
    $GLOBALS['PATIENT_INC_COUNT'] = $count;
  // if ($limit != "all" && $GLOBALS['PATIENT_INC_COUNT'] >= $limit) {
    if ($limit != "all") {
        $tmp = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE $where", $whereBindArray);
        $GLOBALS['PATIENT_INC_COUNT'] = $tmp['count'];
    }
}

/**
 * Allow the last name to be followed by a comma and some part of a first name(can
 *   also place middle name after the first name with a space separating them)
 * Allows comma alone followed by some part of a first name(can also place middle name
 *   after the first name with a space separating them).
 * Allows comma alone preceded by some part of a last name.
 * If no comma or space, then will search both last name and first name.
 * If the first letter of either name is capital, searches for name starting
 *   with given substring (the expected behavior). If it is lower case, it
 *   searches for the substring anywhere in the name. This applies to either
 *   last name, first name, and middle name.
 * Also allows first name followed by middle and/or last name when separated by spaces.
 * @param string $term
 * @param string $given
 * @param string $orderby
 * @param string $limit
 * @param string $start
 * @return array
 */
// To prevent sql injection on this function, if a variable is used for $given OR $orderby parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getPatientLnames($term = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{
    $names = getPatientNameSplit($term);

    foreach ($names as $key => $val) {
        if (!empty($val)) {
            if ((strlen($val) > 1) && ($names[$key][0] != strtoupper($names[$key][0]))) {
                $names[$key] = '%' . $val . '%';
            } else {
                $names[$key] = $val . '%';
            }
        }
    }

    // Debugging section below
    //if(array_key_exists('first',$names)) {
    //    error_log("first name search term :".$names['first']);
    //}
    //if(array_key_exists('middle',$names)) {
    //    error_log("middle name search term :".$names['middle']);
    //}
    //if(array_key_exists('last',$names)) {
    //    error_log("last name search term :".$names['last']);
    //}
    // Debugging section above

    $sqlBindArray = array();
    if (array_key_exists('last', $names) && $names['last'] == '') {
        // Do not search last name
        $where = "fname LIKE ? ";
        array_push($sqlBindArray, $names['first']);
        if ($names['middle'] != '') {
            $where .= "AND mname LIKE ? ";
            array_push($sqlBindArray, $names['middle']);
        }
    } elseif (array_key_exists('first', $names) && $names['first'] == '') {
        // Do not search first name or middle name
        $where = "lname LIKE ? ";
        array_push($sqlBindArray, $names['last']);
    } elseif (empty($names['first']) && !empty($names['last'])) {
        // Search both first name and last name with same term
        $names['first'] = $names['last'];
        $where = "lname LIKE ? OR fname LIKE ? ";
        array_push($sqlBindArray, $names['last'], $names['first']);
    } elseif ($names['middle'] != '') {
        $where = "lname LIKE ? AND fname LIKE ? AND mname LIKE ? ";
        array_push($sqlBindArray, $names['last'], $names['first'], $names['middle']);
    } else {
        $where = "lname LIKE ? AND fname LIKE ? ";
        array_push($sqlBindArray, $names['last'], $names['first']);
    }

    if (!empty($GLOBALS['pt_restrict_field'])) {
        if ($_SESSION["authUser"] != 'admin' || $GLOBALS['pt_restrict_admin']) {
            $where .= " AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
            array_push($sqlBindArray, $_SESSION["authUser"]);
        }
    }

    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql, $sqlBindArray);

    $returnval = array();
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}
/**
 * Accept a string used by a search function expected to find a patient name,
 * then split up the string if a comma or space exists. Return an array having
 * from 1 to 3 elements, named first, middle, and last.
 * See above getPatientLnames() function for details on how the splitting occurs.
 * @param string $term
 * @return array
 */
function getPatientNameSplit($term)
{
    $term = trim($term);
    if (strpos($term, ',') !== false) {
        $names = explode(',', $term);
        $n['last'] = $names[0];
        if (strpos(trim($names[1]), ' ') !== false) {
            list($n['first'], $n['middle']) = explode(' ', trim($names[1]));
        } else {
            $n['first'] = $names[1];
        }
    } elseif (strpos($term, ' ') !== false) {
        $names = explode(' ', $term);
        if (count($names) == 1) {
            $n['last'] = $names[0];
        } elseif (count($names) == 3) {
            $n['first'] = $names[0];
            $n['middle'] = $names[1];
            $n['last'] = $names[2];
        } else {
            // This will handle first and last name or first followed by
            // multiple names only using just the last of the names in the list.
            $n['first'] = $names[0];
            $n['last'] = end($names);
        }
    } else {
        $n['last'] = $term;
        if (empty($n['last'])) {
            $n['last'] = '%';
        }
    }

    // Trim whitespace off the names before returning
    foreach ($n as $key => $val) {
        $n[$key] = trim($val);
    }

    return $n; // associative array containing names
}

// To prevent sql injection on this function, if a variable is used for $given OR $orderby parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getPatientId($pid = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{

    $sqlBindArray = array();
    $where = "pubpid LIKE ? ";
    array_push($sqlBindArray, $pid . "%");
    if (!empty($GLOBALS['pt_restrict_field']) && $GLOBALS['pt_restrict_by_id']) {
        if ($_SESSION["authUser"] != 'admin' || $GLOBALS['pt_restrict_admin']) {
            $where .= "AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                    " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                    add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
            array_push($sqlBindArray, $_SESSION["authUser"]);
        }
    }

    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

// To prevent sql injection on this function, if a variable is used for $given OR $orderby parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getByPatientDemographics($searchTerm = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{
    $layoutCols = sqlStatement(
        "SELECT field_id FROM layout_options WHERE form_id = 'DEM' AND field_id not like ? AND uor !=0",
        array('em\_%')
    );

    $sqlBindArray = array();
    $where = "";
    for ($iter = 0; $row = sqlFetchArray($layoutCols); $iter++) {
        if ($iter > 0) {
            $where .= " or ";
        }

        $where .= " " . add_escape_custom($row["field_id"]) . " like ? ";
        array_push($sqlBindArray, "%" . $searchTerm . "%");
    }

    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

// To prevent sql injection on this function, if a variable is used for $given OR $orderby parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getByPatientDemographicsFilter(
    $searchFields,
    $searchTerm = "%",
    $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS",
    $orderby = "lname ASC, fname ASC",
    $limit = "all",
    $start = "0",
    $search_service_code = ''
) {

    $layoutCols = explode('~', $searchFields);
    $sqlBindArray = array();
    $where = "";
    $i = 0;
    foreach ($layoutCols as $val) {
        if (empty($val)) {
            continue;
        }

        if ($i > 0) {
            $where .= " or ";
        }

        if ($val == 'pid') {
            $where .= " " . escape_sql_column_name($val, ['patient_data']) . " = ? ";
                array_push($sqlBindArray, $searchTerm);
        } else {
            $where .= " " . escape_sql_column_name($val, ['patient_data']) . " like ? ";
                array_push($sqlBindArray, $searchTerm . "%");
        }

        $i++;
    }

  // If no search terms, ensure valid syntax.
    if ($i == 0) {
        $where = "1 = 1";
    }

  // If a non-empty service code was given, then restrict to patients who
  // have been provided that service.  Since the code is used in a LIKE
  // clause, % and _ wildcards are supported.
    if ($search_service_code) {
        $where = "( $where ) AND " .
        "( SELECT COUNT(*) FROM billing AS b WHERE " .
        "b.pid = patient_data.pid AND " .
        "b.activity = 1 AND " .
        "b.code_type != 'COPAY' AND " .
        "b.code LIKE ? " .
        ") > 0";
        array_push($sqlBindArray, $search_service_code);
    }

    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

// return a collection of Patient PIDs
// new arg style by JRM March 2008
// orig function getPatientPID($pid = "%", $given = "pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS", $orderby = "lname ASC, fname ASC", $limit="all", $start="0")
// To prevent sql injection on this function, if a variable is used for $given OR $orderby parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getPatientPID($args)
{
    $pid = "%";
    $given = "pid, id, lname, fname, mname, suffix, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS";
    $orderby = "lname ASC, fname ASC";
    $limit = "all";
    $start = "0";

    // alter default values if defined in the passed in args
    if (isset($args['pid'])) {
        $pid = $args['pid'];
    }

    if (isset($args['given'])) {
        $given = $args['given'];
    }

    if (isset($args['orderby'])) {
        $orderby = $args['orderby'];
    }

    if (isset($args['limit'])) {
        $limit = $args['limit'];
    }

    if (isset($args['start'])) {
        $start = $args['start'];
    }

    $command = "=";
    if ($pid == -1) {
        $pid = "%";
    } elseif (empty($pid)) {
        $pid = "NULL";
    }

    if (strstr($pid, "%")) {
        $command = "like";
    }

    $sql = "select $given from patient_data where pid $command '" . add_escape_custom($pid) . "' order by $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    return $returnval;
}

/* return a patient's name in the format LAST [SUFFIX], FIRST [MIDDLE] */
function getPatientName($pid)
{
    if (empty($pid)) {
        return "";
    }

    $patientData = getPatientPID(array("pid" => $pid));
    if (empty($patientData[0]['lname'])) {
        return "";
    }

    $patientName = $patientData[0]['lname'];
    $patientName .= $patientData[0]['suffix'] ? " " . $patientData[0]['suffix'] . ", " : ", ";
    $patientName .= $patientData[0]['fname'];
    $patientName .= empty($patientData[0]['mname']) ? "" : " " . $patientData[0]['mname'];
    return $patientName;
}

/**
 * Get a patient's first name, middle name, last name and suffix if applicable.
 *
 * Returns a properly formatted, complete name when applicable. Example name
 * would be "John B Doe Jr". No additional punctuation is added. Spaces are
 * correctly omitted if the middle name of suffix does not apply.
 *
 * @var $pid int The Patient ID
 * @returns string The Full Name
 */
function getPatientFullNameAsString($pid): string
{
    if (empty($pid)) {
        return '';
    }
    $ptData = getPatientPID(["pid" => $pid]);
    $pt = $ptData[0];

    if (empty($pt['lname'])) {
        return "";
    }

    $name = $pt['fname'];

    if ($pt['mname']) {
        $name .= " {$pt['mname']}";
    }

    $name .= " {$pt['lname']}";

    if ($pt['suffix']) {
        $name .= " {$pt['suffix']}";
    }

    return $name;
}

/* return a patient's name in the format FIRST LAST */
function getPatientNameFirstLast($pid)
{
    if (empty($pid)) {
        return "";
    }

    $patientData = getPatientPID(array("pid" => $pid));
    if (empty($patientData[0]['lname'])) {
        return "";
    }

    $patientName =  $patientData[0]['fname'] . " " . $patientData[0]['lname'];
    return $patientName;
}

/* find patient data by DOB */
// To prevent sql injection on this function, if a variable is used for $given OR $orderby parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getPatientDOB($DOB = "%", $given = "pid, id, lname, fname, mname", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{
    $sqlBindArray = array();
    $where = "DOB like ? ";
    array_push($sqlBindArray, $DOB . "%");
    if (!empty($GLOBALS['pt_restrict_field'])) {
        if ($_SESSION["authUser"] != 'admin' || $GLOBALS['pt_restrict_admin']) {
            $where .= "AND ( patient_data." . add_escape_custom($GLOBALS['pt_restrict_field']) .
                    " = ( SELECT facility_id FROM users WHERE username = ?) OR patient_data." .
                    add_escape_custom($GLOBALS['pt_restrict_field']) . " = '' ) ";
            array_push($sqlBindArray, $_SESSION["authUser"]);
        }
    }

    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";

    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

/* find patient data by SSN */
// To prevent sql injection on this function, if a variable is used for $given OR $orderby parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getPatientSSN($ss = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{
    $sqlBindArray = array();
    $where = "ss LIKE ?";
    array_push($sqlBindArray, $ss . "%");
    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

//(CHEMED) Search by phone number
// To prevent sql injection on this function, if a variable is used for $given OR $orderby parameter, then
// it needs to be escaped via whitelisting prior to using this function.
function getPatientPhone($phone = "%", $given = "pid, id, lname, fname, mname, providerID", $orderby = "lname ASC, fname ASC", $limit = "all", $start = "0")
{
    $phone = preg_replace("/[[:punct:]]/", "", $phone);
    $sqlBindArray = array();
    $where = "REPLACE(REPLACE(phone_home, '-', ''), ' ', '') REGEXP ?";
    array_push($sqlBindArray, $phone);
    $sql = "SELECT $given FROM patient_data WHERE $where ORDER BY $orderby";
    if ($limit != "all") {
        $sql .= " limit " . escape_limit($start) . ", " . escape_limit($limit);
    }

    $rez = sqlStatement($sql, $sqlBindArray);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $returnval[$iter] = $row;
    }

    _set_patient_inc_count($limit, count($returnval), $where, $sqlBindArray);
    return $returnval;
}

//----------------------input functions
function newPatientData(
    $db_id = "",
    $title = "",
    $fname = "",
    $lname = "",
    $mname = "",
    $sex = "",
    $DOB = "",
    $street = "",
    $postal_code = "",
    $city = "",
    $state = "",
    $country_code = "",
    $ss = "",
    $occupation = "",
    $phone_home = "",
    $phone_biz = "",
    $phone_contact = "",
    $status = "",
    $contact_relationship = "",
    $referrer = "",
    $referrerID = "",
    $email = "",
    $language = "",
    $ethnoracial = "",
    $interpretter = "",
    $migrantseasonal = "",
    $family_size = "",
    $monthly_income = "",
    $homeless = "",
    $financial_review = "",
    $pubpid = "",
    $pid = "MAX(pid)+1",
    $providerID = "",
    $genericname1 = "",
    $genericval1 = "",
    $genericname2 = "",
    $genericval2 = "",
    $billing_note = "",
    $phone_cell = "",
    $hipaa_mail = "",
    $hipaa_voice = "",
    $squad = 0,
    $pharmacy_id = 0,
    $drivers_license = "",
    $hipaa_notice = "",
    $hipaa_message = "",
    $regdate = ""
) {

    $fitness = 0;
    $referral_source = '';
    if ($pid) {
        $rez = sqlQuery("select id, fitness, referral_source from patient_data where pid = ?", array($pid));
        // Check for brain damage:
        if ($db_id != $rez['id']) {
            $errmsg = "Internal error: Attempt to change patient_data.id from '" .
              text($rez['id']) . "' to '" . text($db_id) . "' for pid '" . text($pid) . "'";
            die($errmsg);
        }

        $fitness = $rez['fitness'];
        $referral_source = $rez['referral_source'];
    }

    // Get the default price level.
    $lrow = sqlQuery("SELECT option_id FROM list_options WHERE " .
      "list_id = 'pricelevel' AND activity = 1 ORDER BY is_default DESC, seq ASC LIMIT 1");
    $pricelevel = empty($lrow['option_id']) ? '' : $lrow['option_id'];

    $query = ("replace into patient_data set
        id='" . add_escape_custom($db_id) . "',
        title='" . add_escape_custom($title) . "',
        fname='" . add_escape_custom($fname) . "',
        lname='" . add_escape_custom($lname) . "',
        mname='" . add_escape_custom($mname) . "',
        sex='" . add_escape_custom($sex) . "',
        DOB='" . add_escape_custom($DOB) . "',
        street='" . add_escape_custom($street) . "',
        postal_code='" . add_escape_custom($postal_code) . "',
        city='" . add_escape_custom($city) . "',
        state='" . add_escape_custom($state) . "',
        country_code='" . add_escape_custom($country_code) . "',
        drivers_license='" . add_escape_custom($drivers_license) . "',
        ss='" . add_escape_custom($ss) . "',
        occupation='" . add_escape_custom($occupation) . "',
        phone_home='" . add_escape_custom($phone_home) . "',
        phone_biz='" . add_escape_custom($phone_biz) . "',
        phone_contact='" . add_escape_custom($phone_contact) . "',
        status='" . add_escape_custom($status) . "',
        contact_relationship='" . add_escape_custom($contact_relationship) . "',
        referrer='" . add_escape_custom($referrer) . "',
        referrerID='" . add_escape_custom($referrerID) . "',
        email='" . add_escape_custom($email) . "',
        language='" . add_escape_custom($language) . "',
        ethnoracial='" . add_escape_custom($ethnoracial) . "',
        interpretter='" . add_escape_custom($interpretter) . "',
        migrantseasonal='" . add_escape_custom($migrantseasonal) . "',
        family_size='" . add_escape_custom($family_size) . "',
        monthly_income='" . add_escape_custom($monthly_income) . "',
        homeless='" . add_escape_custom($homeless) . "',
        financial_review='" . add_escape_custom($financial_review) . "',
        pubpid='" . add_escape_custom($pubpid) . "',
        pid= '" . add_escape_custom($pid) . "',
        providerID = '" . add_escape_custom($providerID) . "',
        genericname1 = '" . add_escape_custom($genericname1) . "',
        genericval1 = '" . add_escape_custom($genericval1) . "',
        genericname2 = '" . add_escape_custom($genericname2) . "',
        genericval2 = '" . add_escape_custom($genericval2) . "',
        billing_note= '" . add_escape_custom($billing_note) . "',
        phone_cell = '" . add_escape_custom($phone_cell) . "',
        pharmacy_id = '" . add_escape_custom($pharmacy_id) . "',
        hipaa_mail = '" . add_escape_custom($hipaa_mail) . "',
        hipaa_voice = '" . add_escape_custom($hipaa_voice) . "',
        hipaa_notice = '" . add_escape_custom($hipaa_notice) . "',
        hipaa_message = '" . add_escape_custom($hipaa_message) . "',
        squad = '" . add_escape_custom($squad) . "',
        fitness='" . add_escape_custom($fitness) . "',
        referral_source='" . add_escape_custom($referral_source) . "',
        regdate='" . add_escape_custom($regdate) . "',
        pricelevel='" . add_escape_custom($pricelevel) . "',
        date=NOW()");

    $id = sqlInsert($query);

    if (!$db_id) {
      // find the last inserted id for new patient case
        $db_id = $id;
    }

    $foo = sqlQuery("select `pid`, `uuid` from `patient_data` where `id` = ? order by `date` limit 0,1", array($id));

    // set uuid if not set yet (if this was an insert and not an update)
    if (empty($foo['uuid'])) {
        $uuid = (new UuidRegistry(['table_name' => 'patient_data']))->createUuid();
        sqlStatementNoLog("UPDATE `patient_data` SET `uuid` = ? WHERE `id` = ?", [$uuid, $id]);
    }

    return $foo['pid'];
}

// Supported input date formats are:
//   mm/dd/yyyy
//   mm/dd/yy   (assumes 20yy for yy < 10, else 19yy)
//   yyyy/mm/dd
//   also mm-dd-yyyy, etc. and mm.dd.yyyy, etc.
//
function fixDate($date, $default = "0000-00-00")
{
    $fixed_date = $default;
    $date = trim($date);
    if (preg_match("'^[0-9]{1,4}[/.-][0-9]{1,2}[/.-][0-9]{1,4}$'", $date)) {
        $dmy = preg_split("'[/.-]'", $date);
        if ($dmy[0] > 99) {
            $fixed_date = sprintf("%04u-%02u-%02u", $dmy[0], $dmy[1], $dmy[2]);
        } else {
            if ($dmy[0] != 0 || $dmy[1] != 0 || $dmy[2] != 0) {
                if ($dmy[2] < 1000) {
                    $dmy[2] += 1900;
                }

                if ($dmy[2] < 1910) {
                    $dmy[2] += 100;
                }
            }
            // Determine if MDY date format is used, preferring Date Display Format from
            // global settings if it's not YMD, otherwise guessing from country code.
            $using_mdy = empty($GLOBALS['date_display_format']) ?
                ($GLOBALS['phone_country_code'] == 1) : ($GLOBALS['date_display_format'] == 1);
            if ($using_mdy) {
                $fixed_date = sprintf("%04u-%02u-%02u", $dmy[2], $dmy[0], $dmy[1]);
            } else {
                $fixed_date = sprintf("%04u-%02u-%02u", $dmy[2], $dmy[1], $dmy[0]);
            }
        }
    }

    return $fixed_date;
}

function pdValueOrNull($key, $value)
{
    if (
        ($key == 'DOB' || $key == 'regdate' || $key == 'contrastart' ||
        substr($key, 0, 8) == 'userdate' || $key == 'deceased_date') &&
        (empty($value) || $value == '0000-00-00')
    ) {
        return "NULL";
    } else {
        return "'" . add_escape_custom($value) . "'";
    }
}

/**
 * Create or update patient data from an array.
 *
 * This is a wrapper function for the PatientService which is now the single point
 * of patient creation and update.
 *
 * If successful, returns the pid of the patient
 *
 * @param $pid
 * @param $new
 * @param false $create
 * @return mixed
 */
function updatePatientData($pid, $new, $create = false)
{
    // Create instance of patient service
    $patientService = new PatientService();
    if (
        $create === true ||
        $pid === null
    ) {
        $result = $patientService->databaseInsert($new);
        updateDupScore($result['pid']);
    } else {
        $new['pid'] = $pid;
        $result = $patientService->databaseUpdate($new);
    }

    // From the returned patient data array
    // retrieve the data and return the pid
    $pid = $result['pid'];

    return $pid;
}

function newEmployerData(
    $pid,
    $name = "",
    $street = "",
    $postal_code = "",
    $city = "",
    $state = "",
    $country = ""
) {

    return sqlInsert("insert into employer_data set
        name='" . add_escape_custom($name) . "',
        street='" . add_escape_custom($street) . "',
        postal_code='" . add_escape_custom($postal_code) . "',
        city='" . add_escape_custom($city) . "',
        state='" . add_escape_custom($state) . "',
        country='" . add_escape_custom($country) . "',
        pid='" . add_escape_custom($pid) . "',
        date=NOW()
        ");
}

// Create or update employer data from an array.
//
function updateEmployerData($pid, $new, $create = false)
{
    // used to hard code colnames array('name','street','city','state','postal_code','country');
    // but now adapted for layout based
    $colnames = array();
    foreach ($new as $key => $value) {
        $colnames[] = $key;
    }

    if ($create) {
        $set = "pid = '" . add_escape_custom($pid) . "', date = NOW()";
        foreach ($colnames as $key) {
            $value = isset($new[$key]) ? $new[$key] : '';
            $set .= ", `$key` = '" . add_escape_custom($value) . "'";
        }

        return sqlInsert("INSERT INTO employer_data SET $set");
    } else {
        $set = '';
        $old = getEmployerData($pid);
        $modified = false;
        foreach ($colnames as $key) {
            $value = empty($old[$key]) ? '' : $old[$key];
            if (isset($new[$key]) && strcmp($new[$key], $value) != 0) {
                $value = $new[$key];
                $modified = true;
            }

            $set .= "`$key` = '" . add_escape_custom($value) . "', ";
        }

        if ($modified) {
            $set .= "pid = '" . add_escape_custom($pid) . "', date = NOW()";
            return sqlInsert("INSERT INTO employer_data SET $set");
        }

        return ($old['id'] ?? '');
    }
}

// This updates or adds the given insurance data info, while retaining any
// previously added insurance_data rows that should be preserved.
// This does not directly support the maintenance of non-current insurance.
//
function newInsuranceData(
    $pid,
    $type = "",
    $provider = "",
    $policy_number = "",
    $group_number = "",
    $plan_name = "",
    $subscriber_lname = "",
    $subscriber_mname = "",
    $subscriber_fname = "",
    $subscriber_relationship = "",
    $subscriber_ss = "",
    $subscriber_DOB = null,
    $subscriber_street = "",
    $subscriber_postal_code = "",
    $subscriber_city = "",
    $subscriber_state = "",
    $subscriber_country = "",
    $subscriber_phone = "",
    $subscriber_employer = "",
    $subscriber_employer_street = "",
    $subscriber_employer_city = "",
    $subscriber_employer_postal_code = "",
    $subscriber_employer_state = "",
    $subscriber_employer_country = "",
    $copay = "",
    $subscriber_sex = "",
    $effective_date = null,
    $accept_assignment = "TRUE",
    $policy_type = ""
) {

    if (strlen($type) <= 0) {
        return false;
    }

    if (is_null($accept_assignment)) {
        $accept_assignment = "TRUE";
    }
    if (is_null($policy_type)) {
        $policy_type = "";
    }

    // If empty dates were passed, then null.
    if (empty($effective_date)) {
        $effective_date = null;
    }
    if (empty($subscriber_DOB)) {
        $subscriber_DOB = null;
    }

    $idres = sqlStatement("SELECT * FROM insurance_data WHERE " .
    "pid = ? AND type = ? ORDER BY date DESC", array($pid,$type));
    $idrow = sqlFetchArray($idres);

    // Replace the most recent entry in any of the following cases:
    // * Its effective date is >= this effective date.
    // * It is the first entry and it has no (insurance) provider.
    // * There is no encounter that is earlier than the new effective date but
    //   on or after the old effective date.
    // Otherwise insert a new entry.

    $replace = false;
    if ($idrow) {
        // convert date from null to "0000-00-00" for below strcmp and query
        $temp_idrow_date = (!empty($idrow['date'])) ? $idrow['date'] : "0000-00-00";
        $temp_effective_date = (!empty($effective_date)) ? $effective_date : "0000-00-00";
        if (strcmp($temp_idrow_date, $temp_effective_date) > 0) {
            $replace = true;
        } else {
            if (!$idrow['provider'] && !sqlFetchArray($idres)) {
                $replace = true;
            } else {
                $ferow = sqlQuery("SELECT count(*) AS count FROM form_encounter " .
                "WHERE pid = ? AND date < ? AND " .
                "date >= ?", array($pid, $temp_effective_date . " 00:00:00", $temp_idrow_date . " 00:00:00"));
                if ($ferow['count'] == 0) {
                    $replace = true;
                }
            }
        }
    }

    if ($replace) {
        // TBD: This is a bit dangerous in that a typo in entering the effective
        // date can wipe out previous insurance history.  So we want some data
        // entry validation somewhere.
        if ($effective_date === null) {
            sqlStatement("DELETE FROM insurance_data WHERE " .
                "pid = ? AND type = ? AND " .
                "id != ?", array($pid, $type, $idrow['id']));
        } else {
            sqlStatement("DELETE FROM insurance_data WHERE " .
                "pid = ? AND type = ? AND date >= ? AND " .
                "id != ?", array($pid, $type, $effective_date, $idrow['id']));
        }

        $data = array();
        $data['type'] = $type;
        $data['provider'] = $provider;
        $data['policy_number'] = $policy_number;
        $data['group_number'] = $group_number;
        $data['plan_name'] = $plan_name;
        $data['subscriber_lname'] = $subscriber_lname;
        $data['subscriber_mname'] = $subscriber_mname;
        $data['subscriber_fname'] = $subscriber_fname;
        $data['subscriber_relationship'] = $subscriber_relationship;
        $data['subscriber_ss'] = $subscriber_ss;
        $data['subscriber_DOB'] = $subscriber_DOB;
        $data['subscriber_street'] = $subscriber_street;
        $data['subscriber_postal_code'] = $subscriber_postal_code;
        $data['subscriber_city'] = $subscriber_city;
        $data['subscriber_state'] = $subscriber_state;
        $data['subscriber_country'] = $subscriber_country;
        $data['subscriber_phone'] = $subscriber_phone;
        $data['subscriber_employer'] = $subscriber_employer;
        $data['subscriber_employer_city'] = $subscriber_employer_city;
        $data['subscriber_employer_street'] = $subscriber_employer_street;
        $data['subscriber_employer_postal_code'] = $subscriber_employer_postal_code;
        $data['subscriber_employer_state'] = $subscriber_employer_state;
        $data['subscriber_employer_country'] = $subscriber_employer_country;
        $data['copay'] = $copay;
        $data['subscriber_sex'] = $subscriber_sex;
        $data['pid'] = $pid;
        $data['date'] = $effective_date;
        $data['accept_assignment'] = $accept_assignment;
        $data['policy_type'] = $policy_type;
        updateInsuranceData($idrow['id'], $data);
        return $idrow['id'];
    } else {
        return sqlInsert(
            "INSERT INTO `insurance_data` SET `type` = ?,
            `provider` = ?,
            `policy_number` = ?,
            `group_number` = ?,
            `plan_name` = ?,
            `subscriber_lname` = ?,
            `subscriber_mname` = ?,
            `subscriber_fname` = ?,
            `subscriber_relationship` = ?,
            `subscriber_ss` = ?,
            `subscriber_DOB` = ?,
            `subscriber_street` = ?,
            `subscriber_postal_code` = ?,
            `subscriber_city` = ?,
            `subscriber_state` = ?,
            `subscriber_country` = ?,
            `subscriber_phone` = ?,
            `subscriber_employer` = ?,
            `subscriber_employer_city` = ?,
            `subscriber_employer_street` = ?,
            `subscriber_employer_postal_code` = ?,
            `subscriber_employer_state` = ?,
            `subscriber_employer_country` = ?,
            `copay` = ?,
            `subscriber_sex` = ?,
            `pid` = ?,
            `date` = ?,
            `accept_assignment` = ?,
            `policy_type` = ?",
            [
                $type,
                $provider,
                $policy_number,
                $group_number,
                $plan_name,
                $subscriber_lname,
                $subscriber_mname,
                $subscriber_fname,
                $subscriber_relationship,
                $subscriber_ss,
                $subscriber_DOB,
                $subscriber_street,
                $subscriber_postal_code,
                $subscriber_city,
                $subscriber_state,
                $subscriber_country,
                $subscriber_phone,
                $subscriber_employer,
                $subscriber_employer_city,
                $subscriber_employer_street,
                $subscriber_employer_postal_code,
                $subscriber_employer_state,
                $subscriber_employer_country,
                $copay,
                $subscriber_sex,
                $pid,
                $effective_date,
                $accept_assignment,
                $policy_type
            ]
        );
    }
}

// This is used internally only.
function updateInsuranceData($id, $new)
{
    $fields = sqlListFields("insurance_data");
    $use = array();

    foreach ($new as $key => $value) {
        if (in_array($key, $fields)) {
            $use[$key] = $value;
        }
    }

    $sqlBindArray = [];
    $sql = "UPDATE insurance_data SET ";
    foreach ($use as $key => $value) {
        $sql .= "`" . $key . "` = ?, ";
        array_push($sqlBindArray, $value);
    }

    $sql = substr($sql, 0, -2) . " WHERE id = ?";
    array_push($sqlBindArray, $id);

    sqlStatement($sql, $sqlBindArray);
}

function newHistoryData($pid, $new = false)
{
    $socialHistoryService = new SocialHistoryService();

    $insertionRecord = $new;
    if (!is_array(($insertionRecord))) {
        $insertionRecord = [
            'pid' => $pid
        ];
    }
    $socialHistoryService->create($insertionRecord);
}

function updateHistoryData($pid, $new)
{
    $socialHistoryService = new SocialHistoryService();
    return $socialHistoryService->updateHistoryDataForPatientPid($pid, $new);
}

// Returns Age
//   in months if < 2 years old
//   in years  if > 2 years old
// given YYYYMMDD from MySQL DATE_FORMAT(DOB,'%Y%m%d')
// (optional) nowYMD is a date in YYYYMMDD format
function getPatientAge($dobYMD, $nowYMD = null)
{
    $patientService = new PatientService();
    return $patientService->getPatientAge($dobYMD, $nowYMD);
}

/**
 * Wrapper to make sure the clinical rules dates formats corresponds to the
 * format expected by getPatientAgeYMD
 *
 * @param  string  $dob     date of birth
 * @param  string  $target  date to calculate age on
 * @return array containing
 *      age - decimal age in years
 *      age_in_months - decimal age in months
 *      ageinYMD - formatted string #y #m #d */
function parseAgeInfo($dob, $target)
{
    // Prepare dob (expected in order Y M D, remove whatever delimiters might be there
    $dateDOB = preg_replace("/[-\s\/]/", "", $dob);
    ;
    // Prepare target (Y-M-D H:M:S)
    $dateTarget = preg_replace("/[-\s\/]/", "", $target);

    return getPatientAgeYMD($dateDOB, $dateTarget);
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
function getPatientAgeYMD($dob, $date = null)
{
    $service = new PatientService();
    return $service->getPatientAgeYMD($dob, $date);
}

// Returns Age in days
//   in months if < 2 years old
//   in years  if > 2 years old
// given YYYYMMDD from MySQL DATE_FORMAT(DOB,'%Y%m%d')
// (optional) nowYMD is a date in YYYYMMDD format
function getPatientAgeInDays($dobYMD, $nowYMD = null)
{
    $age = -1;

    // strip any dashes from the DOB
    $dobYMD = preg_replace("/-/", "", $dobYMD);
    $dobDay = substr($dobYMD, 6, 2);
    $dobMonth = substr($dobYMD, 4, 2);
    $dobYear = substr($dobYMD, 0, 4);

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

    // do the date math
    $dobtime = strtotime($dobYear . "-" . $dobMonth . "-" . $dobDay);
    $nowtime = strtotime($nowYear . "-" . $nowMonth . "-" . $nowDay);
    $timediff = $nowtime - $dobtime;
    $age = $timediff / 86400; // 24 hours * 3600 seconds/hour  = 86400 seconds

    return $age;
}
/**
 * Returns a string to be used to display a patient's age
 *
 * @param type $dobYMD
 * @param type $asOfYMD
 * @return string suitable for displaying patient's age based on preferences
 */
function getPatientAgeDisplay($dobYMD, $asOfYMD = null)
{
    $service = new PatientService();
    return $service->getPatientAgeDisplay($dobYMD, $asOfYMD);
}
function dateToDB($date)
{
    $date = substr($date, 6, 4) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2);
    return $date;
}

/**
 * Get up to 3 insurances (primary, secondary, tertiary) that are effective
 * for the given patient on the given date.
 *
 * @param int     The PID of the patient.
 * @param string  Date in yyyy-mm-dd format.
 * @return array  Array of 0-3 insurance_data rows.
 */
function getEffectiveInsurances($patient_id, $encdate)
{
    $insarr = array();
    foreach (array('primary','secondary','tertiary') as $instype) {
        $tmp = sqlQuery(
            "SELECT * FROM insurance_data " .
            "WHERE pid = ? AND type = ? " .
            "AND (date <= ? OR date IS NULL) ORDER BY date DESC LIMIT 1",
            array($patient_id, $instype, $encdate)
        );
        if (empty($tmp['provider'])) {
            break;
        }

        $insarr[] = $tmp;
    }

    return $insarr;
}

/**
 * Get all requisition insurance companies
 *
 *
 */

function getAllinsurances($pid)
{
    $insarr = array();
    $sql = "SELECT a.type, a.provider, a.plan_name, a.policy_number, a.group_number,
           a.subscriber_lname, a.subscriber_fname, a.subscriber_relationship, a.subscriber_employer,
		   b.name, c.line1, c.line2, c.city, c.state, c.zip
           FROM `insurance_data` AS a
           RIGHT JOIN insurance_companies AS b
           ON a.provider = b.id
           RIGHT JOIN addresses AS c
           ON a.provider = c.foreign_id
           WHERE a.pid = ? ";
    $inco = sqlStatement($sql, array($pid));

    while ($icl = sqlFetchArray($inco)) {
        $insarr[] = $icl;
    }
    return $insarr;
}

/**
 * Get the patient's balance due. Normally this excludes amounts that are out
 * to insurance.  If you want to include what insurance owes, set the second
 * parameter to true.
 *
 * @param int     The PID of the patient.
 * @param boolean Indicates if amounts owed by insurance are to be included.
 * @param int     Optional encounter id. If value is passed, will fetch only bills from specified encounter.
 * @return number The balance.
 */
function get_patient_balance($pid, $with_insurance = false, $eid = false)
{
    $balance = 0;
    $bindarray = array($pid);
    $sqlstatement = "SELECT date, encounter, last_level_billed, " .
      "last_level_closed, stmt_count " .
      "FROM form_encounter WHERE pid = ?";
    if ($eid) {
        $sqlstatement .= " AND encounter = ?";
        array_push($bindarray, $eid);
    }
    $feres = sqlStatement($sqlstatement, $bindarray);
    while ($ferow = sqlFetchArray($feres)) {
        $encounter = $ferow['encounter'];
        $dos = substr($ferow['date'], 0, 10);
        $insarr = getEffectiveInsurances($pid, $dos);
        $inscount = count($insarr);
        if (!$with_insurance && $ferow['last_level_closed'] < $inscount && $ferow['stmt_count'] == 0) {
            // It's out to insurance so only the co-pay might be due.
            $brow = sqlQuery(
                "SELECT SUM(fee) AS amount FROM billing WHERE " .
                "pid = ? AND encounter = ? AND " .
                "code_type = 'copay' AND activity = 1",
                array($pid, $encounter)
            );
            $drow = sqlQuery(
                "SELECT SUM(pay_amount) AS payments " .
                "FROM ar_activity WHERE " .
                "deleted IS NULL AND pid = ? AND encounter = ? AND payer_type = 0",
                array($pid, $encounter)
            );
            // going to comment this out for now since computing future copays doesn't
            // equate to cash in hand, which shows in the Billing widget in dashboard 4-23-21
            // $copay = !empty($insarr[0]['copay']) ? $insarr[0]['copay'] * 1 : 0;
            $copay = 0;

            $amt = !empty($brow['amount']) ? $brow['amount'] * 1 : 0;
            $pay = !empty($drow['payments']) ? $drow['payments'] * 1 : 0;
            $ptbal = $copay + $amt - $pay;
            if ($ptbal) { // @TODO check if we want to show patient payment credits.
                $balance += $ptbal;
            }
        } else {
            // Including insurance or not out to insurance, everything is due.
            $brow = sqlQuery("SELECT SUM(fee) AS amount FROM billing WHERE " .
            "pid = ? AND encounter = ? AND " .
            "activity = 1", array($pid, $encounter));
            $drow = sqlQuery("SELECT SUM(pay_amount) AS payments, " .
              "SUM(adj_amount) AS adjustments FROM ar_activity WHERE " .
              "deleted IS NULL AND pid = ? AND encounter = ?", array($pid, $encounter));
            $srow = sqlQuery("SELECT SUM(fee) AS amount FROM drug_sales WHERE " .
              "pid = ? AND encounter = ?", array($pid, $encounter));
            $balance += $brow['amount'] + $srow['amount']
              - $drow['payments'] - $drow['adjustments'];
        }
    }

    return sprintf('%01.2f', $balance);
}

function get_patient_balance_excluding($pid, $excluded = -1)
{
    // We join form_encounter here to make sure we only count amounts for
    // encounters that exist.  We've had some trouble before with encounters
    // that were deleted but leaving line items in the database.
    $brow = sqlQuery(
        "SELECT SUM(b.fee) AS amount " .
        "FROM billing AS b, form_encounter AS fe WHERE " .
        "b.pid = ? AND b.encounter != 0 AND b.encounter != ? AND b.activity = 1 AND " .
        "fe.pid = b.pid AND fe.encounter = b.encounter",
        array($pid, $excluded)
    );
    $srow = sqlQuery(
        "SELECT SUM(s.fee) AS amount " .
        "FROM drug_sales AS s, form_encounter AS fe WHERE " .
        "s.pid = ? AND s.encounter != 0 AND s.encounter != ? AND " .
        "fe.pid = s.pid AND fe.encounter = s.encounter",
        array($pid, $excluded)
    );
    $drow = sqlQuery(
        "SELECT SUM(a.pay_amount) AS payments, " .
        "SUM(a.adj_amount) AS adjustments " .
        "FROM ar_activity AS a, form_encounter AS fe WHERE " .
        "a.deleted IS NULL AND a.pid = ? AND a.encounter != 0 AND a.encounter != ? AND " .
        "fe.pid = a.pid AND fe.encounter = a.encounter",
        array($pid, $excluded)
    );
    return sprintf(
        '%01.2f',
        $brow['amount'] + $srow['amount'] - $drow['payments'] - $drow['adjustments']
    );
}

// Function to check if patient is deceased.
//  Param:
//    $pid  - patient id
//    $date - date checking if deceased (will default to current date if blank)
//  Return:
//    If deceased, then will return the number of
//      days that patient has been deceased and the deceased date.
//    If not deceased, then will return false.
function is_patient_deceased($pid, $date = '')
{

  // Set date to current if not set
    $date = (!empty($date)) ? $date : date('Y-m-d H:i:s');

  // Query for deceased status (if person is deceased gets days_deceased and date_deceased)
    $results = sqlQuery("SELECT DATEDIFF(?,`deceased_date`) AS `days_deceased`, `deceased_date` AS `date_deceased` " .
                      "FROM `patient_data` " .
                      "WHERE `pid` = ? AND " .
                      dateEmptySql('deceased_date', true, true) .
                      "AND `deceased_date` <= ?", array($date,$pid,$date));

    if (empty($results)) {
        // Patient is alive, so return false
        return false;
    } else {
        // Patient is dead, so return the number of days patient has been deceased.
        //  Don't let it be zero days or else will confuse calls to this function.
        if ($results['days_deceased'] === 0) {
            $results['days_deceased'] = 1;
        }

        return $results;
    }
}

// This computes, sets and returns the dup score for the given patient.
//
function updateDupScore($pid)
{
    $row = sqlQuery(
        "SELECT MAX(" . getDupScoreSQL() . ") AS dupscore " .
        "FROM patient_data AS p1, patient_data AS p2 WHERE " .
        "p1.pid = ? AND p2.pid < p1.pid",
        array($pid)
    );
    $dupscore = empty($row['dupscore']) ? 0 : $row['dupscore'];
    sqlStatement(
        "UPDATE patient_data SET dupscore = ? WHERE pid = ?",
        array($dupscore, $pid)
    );
    return $dupscore;
}
