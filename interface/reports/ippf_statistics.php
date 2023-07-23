<?php

/**
 * This module creates statistical reports related to family planning
 * and sexual and reproductive health.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Might want something different here.
//
if (!AclMain::aclCheckCore('acct', 'rep')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Report")]);
    exit;
}

$facilityService = new FacilityService();

$report_type = empty($_GET['t']) ? 'i' : $_GET['t'];

$from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : '0000-00-00';
$to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

$form_by       = $_POST['form_by'];     // this is a scalar
$form_show     = $_POST['form_show'];   // this is an array
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_sexes    = isset($_POST['form_sexes']) ? $_POST['form_sexes'] : '3';
$form_content  = isset($_POST['form_content']) ? $_POST['form_content'] : '1';
$form_output   = isset($_POST['form_output']) ? 0 + $_POST['form_output'] : 1;

if (empty($form_by)) {
    $form_by = '1';
}

if (empty($form_show)) {
    $form_show = array('1');
}

// One of these is chosen as the left column, or Y-axis, of the report.
//
if ($report_type == 'm') {
    $report_title = xl('Member Association Statistics Report');
    $arr_by = array(
    101 => xl('MA Category'),
    102 => xl('Specific Service'),
    // 6   => xl('Contraceptive Method'),
    // 104 => xl('Specific Contraceptive Service');
    17  => xl('Patient'),
    9   => xl('Internal Referrals'),
    10  => xl('External Referrals'),
    103 => xl('Referral Source'),
    2   => xl('Total'),
    );
    $arr_content = array(
    1 => xl('Services'),
    2 => xl('Unique Clients'),
    4 => xl('Unique New Clients'),
    // 5 => xl('Contraceptive Products'),
    );
    $arr_report = array(
    // Items are content|row|column|column|...
    /*****************************************************************
    '2|2|3|4|5|8|11' => xl('Client Profile - Unique Clients'),
    '4|2|3|4|5|8|11' => xl('Client Profile - New Clients'),
    *****************************************************************/
    );
} elseif ($report_type == 'g') {
    $report_title = xl('GCAC Statistics Report');
    $arr_by = array(
    13 => xl('Abortion-Related Categories'),
    1  => xl('Total SRH & Family Planning'),
    12 => xl('Pre-Abortion Counseling'),
    5  => xl('Abortion Method'), // includes surgical and drug-induced
    8  => xl('Post-Abortion Followup'),
    7  => xl('Post-Abortion Contraception'),
    11 => xl('Complications of Abortion'),
    10  => xl('External Referrals'),
    20  => xl('External Referral Followups'),
    );
    $arr_content = array(
    1 => xl('Services'),
    2 => xl('Unique Clients'),
    4 => xl('Unique New Clients'),
    );
    $arr_report = array(
    /*****************************************************************
    '1|11|13' => xl('Complications by Service Provider'),
    *****************************************************************/
    );
} else {
    $report_title = xl('IPPF Statistics Report');
    $arr_by = array(
    3  => xl('General Service Category'),
    4  => xl('Specific Service'),
    104 => xl('Specific Contraceptive Service'),
    6  => xl('Contraceptive Method'),
    9   => xl('Internal Referrals'),
    10  => xl('External Referrals'),
    );
    $arr_content = array(
    1 => xl('Services'),
    3 => xl('New Acceptors'),
    5 => xl('Contraceptive Products'),
    );
    $arr_report = array(
    );
}

// This will become the array of reportable values.
$areport = array();

// This accumulates the bottom line totals.
$atotals = array();

$arr_show   = array(
  '.total' => array('title' => 'Total'),
  '.age2'  => array('title' => 'Age Category (2)'),
  '.age9'  => array('title' => 'Age Category (9)'),
); // info about selectable columns

$arr_titles = array(); // will contain column headers

// Query layout_options table to generate the $arr_show table.
// Table key is the field ID.
$lres = sqlStatement("SELECT field_id, title, data_type, list_id, description " .
  "FROM layout_options WHERE " .
  "form_id = 'DEM' AND uor > 0 AND field_id NOT LIKE 'em%' " .
  "ORDER BY group_name, seq, title");
while ($lrow = sqlFetchArray($lres)) {
    $fid = $lrow['field_id'];
    if ($fid == 'fname' || $fid == 'mname' || $fid == 'lname') {
        continue;
    }

    $arr_show[$fid] = $lrow;
    $arr_titles[$fid] = array();
}

// Compute age in years given a DOB and "as of" date.
//
function getAge($dob, $asof = '')
{
    if (empty($asof)) {
        $asof = date('Y-m-d');
    }

    $a1 = explode('-', substr($dob, 0, 10));
    $a2 = explode('-', substr($asof, 0, 10));
    $age = $a2[0] - $a1[0];
    if ($a2[1] < $a1[1] || ($a2[1] == $a1[1] && $a2[2] < $a1[2])) {
        --$age;
    }

  // echo "<!-- $dob $asof $age -->\n"; // debugging
    return $age;
}

$cellcount = 0;

function genStartRow($att)
{
    global $cellcount, $form_output;
    if ($form_output != 3) {
        echo " <tr $att>\n";
    }

    $cellcount = 0;
}

function genEndRow()
{
    global $form_output;
    if ($form_output == 3) {
        echo "\n";
    } else {
        echo " </tr>\n";
    }
}

function getListTitle($list, $option)
{
    $row = sqlQuery("SELECT title FROM list_options WHERE " .
    "list_id = ? AND option_id = ?", array($list, $option));
    if (empty($row['title'])) {
        return $option;
    }

    return $row['title'];
}

// Usually this generates one cell, but allows for two or more.
//
function genAnyCell($data, $right = false, $class = '', $colspan = 1)
{
    global $cellcount, $form_output;
    if (!is_array($data)) {
        $data = array(0 => $data);
    }

    foreach ($data as $datum) {
        if ($form_output == 3) {
            if ($cellcount) {
                echo ',';
            }

            echo '"' . $datum . '"';
        } else {
            echo "  <td";
            if ($class) {
                echo " class='" . attr($class) . "'";
            }

            if ($colspan > 1) {
                echo " colspan='" . attr($colspan) . "' align='center'";
            } elseif ($right) {
                echo " align='right'";
            }

            echo ">" . text($datum) . "</td>\n";
        }

        ++$cellcount;
    }
}

function genHeadCell($data, $right = false, $colspan = 1)
{
    genAnyCell($data, $right, 'dehead', $colspan);
}

// Create an HTML table cell containing a numeric value, and track totals.
//
function genNumCell($num, $cnum)
{
    global $atotals, $form_output;
    $atotals[$cnum] += $num;
    if (empty($num) && $form_output != 3) {
        $num = '&nbsp;';
    }

    genAnyCell($num, true, 'detail');
}

// Translate an IPPF code to the corresponding descriptive name of its
// contraceptive method, or to an empty string if none applies.
//
function getContraceptiveMethod($code)
{
    $key = '';
    if (preg_match('/^111101/', $code)) {
        $key = xl('Pills');
    } elseif (preg_match('/^11111[1-9]/', $code)) {
        $key = xl('Injectables');
    } elseif (preg_match('/^11112[1-9]/', $code)) {
        $key = xl('Implants');
    } elseif (preg_match('/^111132/', $code)) {
        $key = xl('Patch');
    } elseif (preg_match('/^111133/', $code)) {
        $key = xl('Vaginal Ring');
    } elseif (preg_match('/^112141/', $code)) {
        $key = xl('Male Condoms');
    } elseif (preg_match('/^112142/', $code)) {
        $key = xl('Female Condoms');
    } elseif (preg_match('/^11215[1-9]/', $code)) {
        $key = xl('Diaphragms/Caps');
    } elseif (preg_match('/^11216[1-9]/', $code)) {
        $key = xl('Spermicides');
    } elseif (preg_match('/^11317[1-9]/', $code)) {
        $key = xl('IUD');
    } elseif (preg_match('/^145212/', $code)) {
        $key = xl('Emergency Contraception');
    } elseif (preg_match('/^121181.13/', $code)) {
        $key = xl('Female VSC');
    } elseif (preg_match('/^122182.13/', $code)) {
        $key = xl('Male VSC');
    } elseif (preg_match('/^131191.10/', $code)) {
        $key = xl('Awareness-Based');
    }

    return $key;
}

// Helper function to find a contraception-related IPPF code from
// the related_code element of the given array.
//
function getRelatedContraceptiveCode($row)
{
    if (!empty($row['related_code'])) {
        $relcodes = explode(';', $row['related_code']);
        foreach ($relcodes as $codestring) {
            if ($codestring === '') {
                continue;
            }

            list($codetype, $code) = explode(':', $codestring);
            if ($codetype !== 'IPPF') {
                continue;
            }

            // Check if the related code concerns contraception.
            $tmp = getContraceptiveMethod($code);
            if (!empty($tmp)) {
                return $code;
            }
        }
    }

    return '';
}

// Helper function to find an abortion-method IPPF code from
// the related_code element of the given array.
//
function getRelatedAbortionMethod($row)
{
    if (!empty($row['related_code'])) {
        $relcodes = explode(';', $row['related_code']);
        foreach ($relcodes as $codestring) {
            if ($codestring === '') {
                continue;
            }

            list($codetype, $code) = explode(':', $codestring);
            if ($codetype !== 'IPPF') {
                continue;
            }

            // Check if the related code concerns contraception.
            $tmp = getAbortionMethod($code);
            if (!empty($tmp)) {
                return $code;
            }
        }
    }

    return '';
}

// Translate an IPPF code to the corresponding descriptive name of its
// abortion method, or to an empty string if none applies.
//
function getAbortionMethod($code)
{
    $key = '';
    if (preg_match('/^25222[34]/', $code)) {
        if (preg_match('/^2522231/', $code)) {
            $key = xl('D&C');
        } elseif (preg_match('/^2522232/', $code)) {
            $key = xl('D&E');
        } elseif (preg_match('/^2522233/', $code)) {
            $key = xl('MVA');
        } elseif (preg_match('/^252224/', $code)) {
            $key = xl('Medical');
        } else {
            $key = xl('Other Surgical');
        }
    }

    return $key;
}

/*********************************************************************
// Helper function to look up the GCAC issue associated with a visit.
// Ideally this is the one and only GCAC issue linked to the encounter.
// However if there are multiple such issues, or if only unlinked issues
// are found, then we pick the one with its start date closest to the
// encounter date.
//
function getGcacData($row, $what, $morejoins="") {
  $patient_id = $row['pid'];
  $encounter_id = $row['encounter'];
  $encdate = substr($row['encdate'], 0, 10);
  $query = "SELECT $what " .
    "FROM lists AS l " .
    "JOIN lists_ippf_gcac AS lg ON l.type = 'ippf_gcac' AND lg.id = l.id " .
    "LEFT JOIN issue_encounter AS ie ON ie.pid = '$patient_id' AND " .
    "ie.encounter = '$encounter_id' AND ie.list_id = l.id " .
    "$morejoins " .
    "WHERE l.pid = '$patient_id' AND " .
    "l.activity = 1 AND l.type = 'ippf_gcac' " .
    "ORDER BY ie.pid DESC, ABS(DATEDIFF(l.begdate, '$encdate')) ASC " .
    "LIMIT 1";
  // Note that reverse-ordering by ie.pid is a trick for sorting
  // issues linked to the encounter (non-null values) first.
  return sqlQuery($query);
}

// Get the "client status" field from the related GCAC issue.
//
function getGcacClientStatus($row) {
  $irow = getGcacData($row, "lo.title", "LEFT JOIN list_options AS lo ON " .
    "lo.list_id = 'clientstatus' AND lo.option_id = lg.client_status");
  if (empty($irow['title'])) {
    $key = xl('Indeterminate');
  }
  else {
    // The client status description should be just fine for this.
    $key = $irow['title'];
  }
  return $key;
}
*********************************************************************/

// Determine if a recent gcac service was performed.
//
function hadRecentAbService($pid, $encdate)
{
    $query = "SELECT COUNT(*) AS count " .
    "FROM form_encounter AS fe, billing AS b, codes AS c WHERE " .
    "fe.pid = ? AND " .
    "fe.date <= ? AND " .
    "DATE_ADD(fe.date, INTERVAL 14 DAY) > ? AND " .
    "b.pid = fe.pid AND " .
    "b.encounter = fe.encounter AND " .
    "b.activity = 1 AND " .
    "b.code_type = 'MA' AND " .
    "c.code_type = '12' AND " .
    "c.code = b.code AND c.modifier = b.modifier AND " .
    "( c.related_code LIKE '%IPPF:252223%' OR c.related_code LIKE '%IPPF:252224%' )";
    $tmp = sqlQuery($query, array($pid, $encdate, $encdate));
    return !empty($tmp['count']);
}

// Get the "client status" as descriptive text.
//
function getGcacClientStatus($row)
{
    $pid = $row['pid'];
    $encdate = $row['encdate'];

    if (hadRecentAbService($pid, $encdate)) {
        return xl('MA Client Accepting Abortion');
    }

  // Check for a GCAC visit form.
  // This will the most recent GCAC visit form for visits within
  // the past 2 weeks, although there really should be such a form
  // attached to the visit associated with $row.
    $query = "SELECT lo.title " .
    "FROM forms AS f, form_encounter AS fe, lbf_data AS d, list_options AS lo " .
    "WHERE f.pid = ? AND " .
    "f.formdir = 'LBFgcac' AND " .
    "f.deleted = 0 AND " .
    "fe.pid = f.pid AND fe.encounter = f.encounter AND " .
    "fe.date <= ? AND " .
    "DATE_ADD(fe.date, INTERVAL 14 DAY) > ? AND " .
    "d.form_id = f.form_id AND " .
    "d.field_id = 'client_status' AND " .
    "lo.list_id = 'clientstatus' AND " .
    "lo.option_id = d.field_value " .
    "ORDER BY d.form_id DESC LIMIT 1";
    $irow = sqlQuery($query, array($pid, $encdate, $encdate));
    if (!empty($irow['title'])) {
        return $irow['title'];
    }

  // Check for a referred abortion.
  /*
  $query = "SELECT COUNT(*) AS count " .
    "FROM transactions AS t, codes AS c WHERE " .
    "t.title = 'Referral' AND " .
    "t.refer_date IS NOT NULL AND " .
    "t.refer_date <= '$encdate' AND " .
    "DATE_ADD(t.refer_date, INTERVAL 14 DAY) > '$encdate' AND " .
    "t.refer_related_code LIKE 'REF:%' AND " .
    "c.code_type = '16' AND " .
    "c.code = SUBSTRING(t.refer_related_code, 5) AND " .
    "( c.related_code LIKE '%IPPF:252223%' OR c.related_code LIKE '%IPPF:252224%' )";
  */
    $query = "SELECT COUNT(*) AS count " .
    "FROM transactions AS t " .
    "LEFT JOIN codes AS c ON t.refer_related_code LIKE 'REF:%' AND " .
    "c.code_type = '16' AND " .
    "c.code = SUBSTRING(t.refer_related_code, 5) " .
    "WHERE " .
    "t.title = 'Referral' AND " .
    "t.refer_date IS NOT NULL AND " .
    "t.refer_date <= ? AND " .
    "DATE_ADD(t.refer_date, INTERVAL 14 DAY) > ? AND " .
    "( t.refer_related_code LIKE '%IPPF:252223%' OR " .
    "t.refer_related_code LIKE '%IPPF:252224%' OR " .
    "( c.related_code IS NOT NULL AND " .
    "( c.related_code LIKE '%IPPF:252223%' OR " .
    "c.related_code LIKE '%IPPF:252224%' )))";

    $tmp = sqlQuery($query, array($encdate, $encdate));
    if (!empty($tmp['count'])) {
        return xl('Outbound Referral');
    }

    return xl('Indeterminate');
}

// Helper function called after the reporting key is determined for a row.
//
function loadColumnData($key, $row, $quantity = 1)
{
    global $areport, $arr_titles, $form_content, $from_date, $to_date, $arr_show;

  // If first instance of this key, initialize its arrays.
    if (empty($areport[$key])) {
        $areport[$key] = array();
        $areport[$key]['.prp'] = 0;       // previous pid
        $areport[$key]['.wom'] = 0;       // number of services for women
        $areport[$key]['.men'] = 0;       // number of services for men
        $areport[$key]['.age2'] = array(0,0);               // age array
        $areport[$key]['.age9'] = array(0,0,0,0,0,0,0,0,0); // age array
        foreach ($arr_show as $askey => $dummy) {
            if (substr($askey, 0, 1) == '.') {
                continue;
            }

            $areport[$key][$askey] = array();
        }
    }

  // Skip this key if we are counting unique patients and the key
  // has already seen this patient.
    if ($form_content == '2' && $row['pid'] == $areport[$key]['.prp']) {
        return;
    }

  // If we are counting new acceptors, then require a unique patient
  // whose contraceptive start date is within the reporting period.
    if ($form_content == '3') {
        // if ($row['pid'] == $areport[$key]['prp']) return;
        if ($row['pid'] == $areport[$key]['.prp']) {
            return;
        }

        // Check contraceptive start date.
        if (
            !$row['contrastart'] || $row['contrastart'] < $from_date ||
            $row['contrastart'] > $to_date
        ) {
            return;
        }
    }

  // If we are counting new clients, then require a unique patient
  // whose registration date is within the reporting period.
    if ($form_content == '4') {
        if ($row['pid'] == $areport[$key]['.prp']) {
            return;
        }

        // Check registration date.
        if (
            !$row['regdate'] || $row['regdate'] < $from_date ||
            $row['regdate'] > $to_date
        ) {
            return;
        }
    }

  // Flag this patient as having been encountered for this report row.
  // $areport[$key]['prp'] = $row['pid'];
    $areport[$key]['.prp'] = $row['pid'];

  // Increment the correct sex category.
    if (strcasecmp($row['sex'], 'Male') == 0) {
        $areport[$key]['.men'] += $quantity;
    } else {
        $areport[$key]['.wom'] += $quantity;
    }

  // Increment the correct age categories.
    $age = getAge(fixDate($row['DOB']), $row['encdate']);
    $i = min(intval(($age - 5) / 5), 8);
    if ($age < 11) {
        $i = 0;
    }

    $areport[$key]['.age9'][$i] += $quantity;
    $i = $age < 25 ? 0 : 1;
    $areport[$key]['.age2'][$i] += $quantity;

    foreach ($arr_show as $askey => $dummy) {
        if (substr($askey, 0, 1) == '.') {
            continue;
        }

        $status = empty($row[$askey]) ? 'Unspecified' : $row[$askey];
        $areport[$key][$askey][$status] += $quantity;
        $arr_titles[$askey][$status] += $quantity;
    }
}

// This is called for each IPPF service code that is selected.
//
function process_ippf_code($row, $code, $quantity = 1)
{
    global $areport, $arr_titles, $form_by, $form_content;

    $key = 'Unspecified';

  // SRH including Family Planning
  //
    if ($form_by === '1') {
        if (preg_match('/^1/', $code)) {
            $key = xl('SRH - Family Planning');
        } elseif (preg_match('/^2/', $code)) {
            $key = xl('SRH Non Family Planning');
        } else {
            if ($form_content != 5) {
                return;
            }
        }
    } elseif ($form_by === '3') { // General Service Category
        if (preg_match('/^1/', $code)) {
            $key = xl('SRH - Family Planning');
        } elseif (preg_match('/^2/', $code)) {
            $key = xl('SRH Non Family Planning');
        } elseif (preg_match('/^3/', $code)) {
            $key = xl('Non-SRH Medical');
        } elseif (preg_match('/^4/', $code)) {
            $key = xl('Non-SRH Non-Medical');
        } else {
            $key = xl('Invalid Service Codes');
        }
    } elseif ($form_by === '13') { // Abortion-Related Category
        if (preg_match('/^252221/', $code)) {
            $key = xl('Pre-Abortion Counseling');
        } elseif (preg_match('/^252222/', $code)) {
            $key = xl('Pre-Abortion Consultation');
        } elseif (preg_match('/^252223/', $code)) {
            $key = xl('Induced Abortion');
        } elseif (preg_match('/^252224/', $code)) {
            $key = xl('Medical Abortion');
        } elseif (preg_match('/^252225/', $code)) {
            $key = xl('Incomplete Abortion Treatment');
        } elseif (preg_match('/^252226/', $code)) {
            $key = xl('Post-Abortion Care');
        } elseif (preg_match('/^252227/', $code)) {
            $key = xl('Post-Abortion Counseling');
        } elseif (preg_match('/^25222/', $code)) {
            $key = xl('Other/Generic Abortion-Related');
        } else {
            if ($form_content != 5) {
                return;
            }
        }
    } elseif ($form_by === '4') { // Specific Services. One row for each IPPF code.
        $key = $code;
    } elseif ($form_by === '104') { // Specific Contraceptive Services. One row for each IPPF code.
        if ($form_content != 5) {
            // Skip codes not for contraceptive services.
            $tmp = getContraceptiveMethod($code);
            if (empty($tmp)) {
                return;
            }
        }

        $key = $code;
    } elseif ($form_by === '5') { // Abortion Method.
        $key = getAbortionMethod($code);
        if (empty($key)) {
            if ($form_content != 5) {
                return;
            }

            $key = 'Unspecified';
        }
    } elseif ($form_by === '6') { // Contraceptive Method.
        $key = getContraceptiveMethod($code);
        if (empty($key)) {
            if ($form_content != 5) {
                return;
            }

            $key = 'Unspecified';
        }

        /*******************************************************************
        // Contraceptive method for new contraceptive adoption following abortion.
        // Get it from the IPPF code if an abortion issue is linked to the visit.
        // Note we are handling this during processing of services rather than
        // by enumerating issues, because we need the service date.
        //
        else if ($form_by === '7') {
        $key = getContraceptiveMethod($code);
        if (empty($key)) return;
        $patient_id = $row['pid'];
        $encounter_id = $row['encounter'];
        $query = "SELECT COUNT(*) AS count " .
        "FROM lists AS l " .
        "JOIN issue_encounter AS ie ON ie.pid = '$patient_id' AND " .
        "ie.encounter = '$encounter_id' AND ie.list_id = l.id " .
        "WHERE l.pid = '$patient_id' AND " .
        "l.activity = 1 AND l.type = 'ippf_gcac'";
        // echo "<!-- $key: $query -->\n"; // debugging
        $irow = sqlQuery($query);
        if (empty($irow['count'])) return;
        }
         *******************************************************************/

        // Contraceptive method for new contraceptive adoption following abortion.
        // Get it from the IPPF code if there is a suitable recent GCAC form.
        //
    } elseif ($form_by === '7') {
        $key = getContraceptiveMethod($code);
        if (empty($key)) {
            return;
        }

        $patient_id = $row['pid'];
        $encdate = $row['encdate'];
        // Skip this if no recent gcac service nor gcac form with acceptance.
        if (!hadRecentAbService($patient_id, $encdate)) {
            $query = "SELECT COUNT(*) AS count " .
            "FROM forms AS f, form_encounter AS fe, lbf_data AS d " .
            "WHERE f.pid = ? AND " .
            "f.formdir = 'LBFgcac' AND " .
            "f.deleted = 0 AND " .
            "fe.pid = f.pid AND fe.encounter = f.encounter AND " .
            "fe.date <= ? AND " .
            "DATE_ADD(fe.date, INTERVAL 14 DAY) > ? AND " .
            "d.form_id = f.form_id AND " .
            "d.field_id = 'client_status' AND " .
            "( d.field_value = 'maaa' OR d.field_value = 'refout' )";
            $irow = sqlQuery($query, array($patient_id, $encdate, $encdate));
            if (empty($irow['count'])) {
                return;
            }
        }
    } elseif ($form_by === '8') { // Post-Abortion Care and Followup by Source.
        // Requirements just call for counting sessions, but this way the columns
        // can be anything - age category, religion, whatever.
        if (preg_match('/^25222[567]/', $code)) { // care, followup and incomplete abortion treatment
            $key = getGcacClientStatus($row);
        } else {
            return;
        }

        /*******************************************************************
        // Complications of abortion by abortion method and complication type.
        // These may be noted either during recovery or during a followup visit.
        // Again, driven by services in order to report by service date.
        // Note: If there are multiple complications, they will all be reported.
        //
        else if ($form_by === '11') {
        $compl_type = '';
        if (preg_match('/^25222[345]/', $code)) { // all abortions including incomplete
        $compl_type = 'rec_compl';
        }
        else if (preg_match('/^25222[67]/', $code)) { // all post-abortion care and followup
        $compl_type = 'fol_compl';
        }
        else {
        return;
        }
        $irow = getGcacData($row, "lg.$compl_type, lo.title",
        "LEFT JOIN list_options AS lo ON lo.list_id = 'in_ab_proc' AND " .
        "lo.option_id = lg.in_ab_proc");
        if (empty($irow)) return; // this should not happen
        if (empty($irow[$compl_type])) return; // ok, no complications
        // We have one or more complications.
        $abtype = empty($irow['title']) ? xl('Indeterminate') : $irow['title'];
        $acompl = explode('|', $irow[$compl_type]);
        foreach ($acompl as $compl) {
        $crow = sqlQuery("SELECT title FROM list_options WHERE " .
        "list_id = 'complication' AND option_id = '$compl'");
        $key = "$abtype / " . $crow['title'];
        loadColumnData($key, $row);
        }
        return; // because loadColumnData() is already done.
        }
         *******************************************************************/

        // Pre-Abortion Counseling.  Three possible situations:
        //   Provided abortion in the MA clinics
        //   Referred to other service providers (govt,private clinics)
        //   Decided not to have the abortion
        //
    } elseif ($form_by === '12') {
        if (preg_match('/^252221/', $code)) { // all pre-abortion counseling
            $key = getGcacClientStatus($row);
        } else {
            return;
        }
    } elseif ($form_by === '17') { // Patient Name.
        $key = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
    } else {
        return; // no match, so do nothing
    }

  // OK we now have the reporting key for this issue.
    loadColumnData($key, $row, $quantity);
} // end function process_ippf_code()

// This is called for each MA service code that is selected.
//
function process_ma_code($row)
{
    global $form_by, $arr_content, $form_content;

    $key = 'Unspecified';

  // One row for each service category.
  //
    if ($form_by === '101') {
        if (!empty($row['lo_title'])) {
            $key = xl($row['lo_title']);
        }
    } elseif ($form_by === '102') { // Specific Services. One row for each MA code.
        $key = $row['code'];
    } elseif ($form_by === '103') { // One row for each referral source.
        $key = $row['referral_source'];
    } elseif ($form_by === '2') { // Just one row.
        $key = $arr_content[$form_content];
    } else {
        return;
    }

    loadColumnData($key, $row);
}

function LBFgcac_query($pid, $encounter, $name)
{
    $query = "SELECT d.form_id, d.field_value " .
    "FROM forms AS f, form_encounter AS fe, lbf_data AS d " .
    "WHERE f.pid = ? AND " .
    "f.encounter = ? AND " .
    "f.formdir = 'LBFgcac' AND " .
    "f.deleted = 0 AND " .
    "fe.pid = f.pid AND fe.encounter = f.encounter AND " .
    "d.form_id = f.form_id AND " .
    "d.field_id = ?";
    return sqlStatement($query, array($pid, $encounter, $name));
}

function LBFgcac_title($form_id, $field_id, $list_id)
{
    $query = "SELECT lo.title " .
    "FROM lbf_data AS d, list_options AS lo WHERE " .
    "d.form_id = ? AND " .
    "d.field_id = ? AND " .
    "lo.list_id = ? AND " .
    "lo.option_id = d.field_value " .
    "LIMIT 1";
    $row = sqlQuery($query, array($form_id, $field_id, $list_id));
    return empty($row['title']) ? '' : $row['title'];
}

// This is called for each encounter that is selected.
//
function process_visit($row)
{
    global $form_by;

    if ($form_by !== '7' && $form_by !== '11') {
        return;
    }

  // New contraceptive method following abortion.  These should only be
  // present for inbound referrals.
  //
    if ($form_by === '7') {
        // We think this case goes away, but not sure yet.
        /*****************************************************************
      $dres = LBFgcac_query($row['pid'], $row['encounter'], 'contrameth');
      while ($drow = sqlFetchArray($dres)) {
        $a = explode('|', $drow['field_value']);
        foreach ($a as $methid) {
        if (empty($methid)) continue;
        $crow = sqlQuery("SELECT title FROM list_options WHERE " .
          "list_id = 'contrameth' AND option_id = '$methid'");
        $key = $crow['title'];
        if (empty($key)) $key = xl('Indeterminate');
        loadColumnData($key, $row);
        }
      }
        *****************************************************************/
    } elseif ($form_by === '11') { // Complications of abortion by abortion method and complication type.
        // These may be noted either during recovery or during a followup visit.
        // Note: If there are multiple complications, they will all be reported.
        $dres = LBFgcac_query($row['pid'], $row['encounter'], 'complications');
        while ($drow = sqlFetchArray($dres)) {
            $a = explode('|', $drow['field_value']);
            foreach ($a as $complid) {
                if (empty($complid)) {
                    continue;
                }

                $crow = sqlQuery("SELECT title FROM list_options WHERE " .
                "list_id = 'complication' AND option_id = ?", array($complid));
                $abtype = LBFgcac_title($drow['form_id'], 'in_ab_proc', 'in_ab_proc');
                if (empty($abtype)) {
                    $abtype = xl('Indeterminate');
                }

                $key = "$abtype / " . $crow['title'];
                loadColumnData($key, $row);
            }
        }
    }

  // loadColumnData() already done as needed.
}

/*********************************************************************
// This is called for each issue that is selected.
//
function process_issue($row) {
  global $form_by;

  $key = 'Unspecified';

  // Pre-Abortion Counseling.  Three possible rows:
  //   Provided abortion in the MA clinics
  //   Referred to other service providers (govt,private clinics)
  //   Decided not to have the abortion
  //
  if ($form_by === '12') {

    // TBD: Assign one of the 3 keys, or just return.

  }

  // Others TBD

  else {
    return;
  }

  // TBD: Load column data from the issue.
  // loadColumnData($key, $row);
}
*********************************************************************/

// This is called for each selected referral.
// Row keys are the first specified MA code, if any.
//
function process_referral($row)
{
    global $form_by;
    $key = 'Unspecified';

  // For followups we care about the actual service provided, otherwise
  // the requested service.
    $related_code = $form_by === '20' ?
    $row['reply_related_code'] : $row['refer_related_code'];

    if (!empty($related_code)) {
        $relcodes = explode(';', $related_code);
        foreach ($relcodes as $codestring) {
            if ($codestring === '') {
                continue;
            }

            list($codetype, $code) = explode(':', $codestring);

            if ($codetype == 'REF') {
                // This is the expected case; a direct IPPF code is obsolete.
                $rrow = sqlQuery("SELECT related_code FROM codes WHERE " .
                "code_type = '16' AND code = ? AND active = 1 " .
                "ORDER BY id LIMIT 1", array($code));
                if (!empty($rrow['related_code'])) {
                        list($codetype, $code) = explode(':', $rrow['related_code']);
                }
            }

            if ($codetype !== 'IPPF') {
                continue;
            }

            if ($form_by === '1') {
                if (preg_match('/^[12]/', $code)) {
                    $key = xl('SRH Referrals');
                    loadColumnData($key, $row);
                    break;
                }
            } else { // $form_by is 9 (internal) or 10 or 20 (external) referrals
                $key = $code;
                break;
            }
        } // end foreach
    }

    if ($form_by !== '1') {
        loadColumnData($key, $row);
    }
}

function uses_description($form_by)
{
    return ($form_by === '4'  || $form_by === '102' || $form_by === '9' ||
    $form_by === '10' || $form_by === '20' || $form_by === '104');
}

  // If we are doing the CSV export then generate the needed HTTP headers.
  // Otherwise generate HTML.
  //
if ($form_output == 3) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=service_statistics_report.csv");
    header("Content-Description: File Transfer");
} else {
    ?>
<html>
<head>
<title><?php echo text($report_title); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

<style>
body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
.dehead    { color:var(--black); font-family:sans-serif; font-size:10pt; font-weight:bold }
.detail    { color:var(--black); font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

<script>

// Begin experimental code

function selectByValue(sel, val) {
for (var i = 0; i < sel.options.length; ++i) {
 if (sel.options[i].value == val) sel.options[i].selected = true;
}
}

function selreport() {
var f = document.forms[0];
var isdis = 'visible';
var s = f.form_report;
var v = (s.selectedIndex < 0) ? '' : s.options[s.selectedIndex].value;
if (v.length > 0) {
 isdis = 'hidden';
 var a = v.split("|");
 f.form_content.selectedIndex = -1;
 f.form_by.selectedIndex = -1;
 f['form_show[]'].selectedIndex = -1;
 selectByValue(f.form_content, a[0]);
 selectByValue(f.form_by, a[1]);
 for (var i = 2; i < a.length; ++i) {
  selectByValue(f['form_show[]'], a[i]);
 }
}
f.form_by.style.visibility = isdis;
f.form_content.style.visibility = isdis;
f['form_show[]'].style.visibility = isdis;
}

// End experimental code

$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><?php echo $report_title; ?></h2>

<form name='theform' method='post' action='ippf_statistics.php?t=<?php echo attr_url($report_type); ?>' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<table border='0' cellspacing='5' cellpadding='1'>

<!-- Begin experimental code -->
<tr<?php echo (empty($arr_report)) ? " style='display:none'" : ""; ?>>
<td valign='top' class='dehead' nowrap>
    <?php echo xlt('Report'); ?>:
</td>
<td valign='top' class='detail' colspan='3'>
 <select name='form_report' title='Predefined reports' onchange='selreport()'>
    <?php
    echo "    <option value=''>" . xlt('Custom') . "</option>\n";
    foreach ($arr_report as $key => $value) {
        echo "    <option value='" . attr($key) . "'";
        if ($key == $form_report) {
            echo " selected";
        }

        echo ">" . text($value) . "</option>\n";
    }
    ?>
 </select>
</td>
<td valign='top' class='detail'>
 &nbsp;
</td>
</tr>
<!-- End experimental code -->

<tr>
<td valign='top' class='dehead' nowrap>
    <?php echo xlt('Rows'); ?>:
</td>
<td valign='top' class='detail'>
 <select name='form_by' title='Left column of report'>
    <?php
    foreach ($arr_by as $key => $value) {
        echo "    <option value='" . attr($key) . "'";
        if ($key == $form_by) {
            echo " selected";
        }

        echo ">" . text($value) . "</option>\n";
    }
    ?>
 </select>
</td>
<td valign='top' class='dehead' nowrap>
    <?php echo xlt('Content'); ?>:
</td>
<td valign='top' class='detail'>
<select name='form_content' title='<?php echo xla('What is to be counted?'); ?>'>
    <?php
    foreach ($arr_content as $key => $value) {
        echo "    <option value='" . attr($key) . "'";
        if ($key == $form_content) {
            echo " selected";
        }

        echo ">" . text($value) . "</option>\n";
    }
    ?>
 </select>
</td>
<td valign='top' class='detail'>
 &nbsp;
</td>
</tr>
<tr>
<td valign='top' class='dehead' nowrap>
    <?php echo xlt('Columns'); ?>:
</td>
<td valign='top' class='detail'>
 <select name='form_show[]' size='4' multiple
title='<?php echo xla('Hold down Ctrl to select multiple items'); ?>'>
    <?php
    foreach ($arr_show as $key => $value) {
        $title = $value['title'];
        if (empty($title) || $key == 'title') {
            $title = $value['description'];
        }

        echo "    <option value='" . attr($key) . "'";
        if (is_array($form_show) && in_array($key, $form_show)) {
            echo " selected";
        }

        echo ">" . text($title) . "</option>\n";
    }
    ?>
 </select>
</td>
<td valign='top' class='dehead' nowrap>
    <?php echo xlt('Filters'); ?>:
</td>
<td colspan='2' class='detail' style='border-style:solid;border-width:1px;border-color:#cccccc'>
 <table>
  <tr>
   <td valign='top' class='detail' nowrap>
    <?php echo xlt('Sex'); ?>:
   </td>
   <td class='detail' valign='top'>
  <select name='form_sexes' title='<?php echo xla('To filter by sex'); ?>'>
    <?php
    foreach (array(3 => xl('Men and Women'), 1 => xl('Women Only'), 2 => xl('Men Only')) as $key => $value) {
        echo "       <option value='" . attr($key) . "'";
        if ($key == $form_sexes) {
            echo " selected";
        }

        echo ">" . text($value) . "</option>\n";
    }
    ?>
    </select>
   </td>
  </tr>
  <tr>
   <td valign='top' class='detail' nowrap>
    <?php echo xlt('Facility'); ?>:
   </td>
   <td valign='top' class='detail'>
    <?php
// Build a drop-down list of facilities.
//
    $fres = $facilityService->getAllFacility();
    echo "      <select name='form_facility'>\n";
    echo "       <option value=''>-- All Facilities --\n";
    foreach ($fres as $frow) {
        $facid = $frow['id'];
        echo "       <option value='" . attr($facid) . "'";
        if ($facid == $_POST['form_facility']) {
            echo " selected";
        }

        echo ">" . text($frow['name']) . "\n";
    }

    echo "      </select>\n";
    ?>
   </td>
  </tr>
  <tr>
   <td colspan='2' class='detail' nowrap>
    <?php echo xlt('From'); ?>
  <input type='text' class='datepicker' name='form_from_date' id='form_from_date' size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
    <?php echo xlt('To{{Range}}'); ?>
  <input type='text' class='datepicker' name='form_to_date' id='form_to_date' size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
   </td>
  </tr>
 </table>
</td>
</tr>
<tr>
<td valign='top' class='dehead' nowrap>
    <?php echo xlt('To{{Destination}}'); ?>:
</td>
<td colspan='3' valign='top' class='detail' nowrap>
    <?php
    foreach (array(1 => 'Screen', 2 => 'Printer', 3 => 'Export File') as $key => $value) {
        echo "   <input type='radio' name='form_output' value='" . attr($key) . "'";
        if ($key == $form_output) {
            echo ' checked';
        }

        echo " />" . text($value) . " &nbsp;";
    }
    ?>
</td>
<td align='right' valign='top' class='detail' nowrap>
<input type='submit' name='form_submit' value='<?php echo xla('Submit'); ?>'
title='<?php echo xla('Click to generate the report'); ?>' />
</td>
</tr>
<tr>
<td colspan='5' height="1">
</td>
</tr>
</table>
    <?php
} // end not export

if ($_POST['form_submit']) {
    $pd_fields = '';
    foreach ($arr_show as $askey => $asval) {
        if (substr($askey, 0, 1) == '.') {
            continue;
        }

        if (
            $askey == 'regdate' || $askey == 'sex' || $askey == 'DOB' ||
            $askey == 'lname' || $askey == 'fname' || $askey == 'mname' ||
            $askey == 'contrastart' || $askey == 'referral_source'
        ) {
            continue;
        }

        $pd_fields .= ', pd.' . escape_sql_column_name($askey, array('patient_data'));
    }

    $sexcond = '';
    if ($form_sexes == '1') {
        $sexcond = "AND pd.sex NOT LIKE 'Male' ";
    } elseif ($form_sexes == '2') {
        $sexcond = "AND pd.sex LIKE 'Male' ";
    }

    $sqlBindArray = array();

    // In the case where content is contraceptive product sales, we
    // scan product sales at the top level because it is important to
    // account for each of them only once.  For each sale we determine
    // the one and only IPPF code representing the primary related
    // contraceptive service, and that might be either a service in
    // the Tally Sheet or the IPPF code attached to the product.
    //
    if ($form_content == 5) { // sales of contraceptive products
        $query = "SELECT " .
        "ds.pid, ds.encounter, ds.sale_date, ds.quantity, " .
        "d.cyp_factor, d.related_code, " .
        "pd.regdate, pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
        "pd.contrastart, pd.referral_source$pd_fields, " .
        "fe.date AS encdate, fe.provider_id " .
        "FROM drug_sales AS ds " .
        "JOIN drugs AS d ON d.drug_id = ds.drug_id " .
        "JOIN patient_data AS pd ON pd.pid = ds.pid $sexcond" .
        "LEFT JOIN form_encounter AS fe ON fe.pid = ds.pid AND fe.encounter = ds.encounter " .
        "WHERE ds.sale_date >= ? AND " .
        "ds.sale_date <= ? AND " .
        "ds.pid > 0 AND ds.quantity != 0";
        array_push($sqlBindArray, $from_date, $to_date);

        if ($form_facility) {
            $query .= " AND fe.facility_id = ?";
            array_push($sqlBindArray, $form_facility);
        }

        $query .= " ORDER BY ds.pid, ds.encounter, ds.drug_id";
        $res = sqlStatement($query, $sqlBindArray);

        while ($row = sqlFetchArray($res)) {
            $desired = false;
            $prodcode = '';
            if ($row['cyp_factor'] > 0) {
                $desired = true;
            }

            $tmp = getRelatedContraceptiveCode($row);
            if (!empty($tmp)) {
                $desired = true;
                $prodcode = $tmp;
            }

            if (!$desired) {
                continue; // skip if not a contraceptive product
            }

            // If there is a visit and it has a contraceptive service use that, else $prodcode.
            if (!empty($row['encounter'])) {
                $query = "SELECT " .
                "b.code_type, b.code, c.related_code " .
                "FROM billing AS b " .
                "LEFT OUTER JOIN codes AS c ON c.code_type = '12' AND " .
                "c.code = b.code AND c.modifier = b.modifier " .
                "WHERE b.pid = ? AND " .
                "b.encounter = ? AND " .
                "b.activity = 1 AND b.code_type = 'MA' " .
                "ORDER BY b.code";
                $bres = sqlStatement($query, array((0 + $row['pid']), (0 + $row['encounter'])));
                while ($brow = sqlFetchArray($bres)) {
                    $tmp = getRelatedContraceptiveCode($brow);
                    if (!empty($tmp)) {
                        $prodcode = $tmp;
                        break;
                    }
                }
            }

            // At this point $prodcode is the desired IPPF code, or empty if none.
            process_ippf_code($row, $prodcode, $row['quantity']);
        }
    }

    // Get referrals and related patient data.
    if ($form_content != 5 && ($form_by === '9' || $form_by === '10' || $form_by === '20' || $form_by === '1')) {
        $exttest = "t.refer_external = '1'";
        $datefld = "t.refer_date";

        if ($form_by === '9') {
            $exttest = "t.refer_external = '0'";
        } elseif ($form_by === '20') {
            $datefld = "t.reply_date";
        }

        $query = "SELECT " .
        "t.pid, t.refer_related_code, t.reply_related_code, " .
        "pd.regdate, pd.referral_source, " .
        "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
        "pd.contrastart$pd_fields " .
        "FROM transactions AS t " .
        "JOIN patient_data AS pd ON pd.pid = t.pid $sexcond" .
        "WHERE t.title = 'Referral' AND $datefld IS NOT NULL AND " .
        "$datefld >= ? AND $datefld <= ? AND $exttest " .
        "ORDER BY t.pid, t.id";
        $res = sqlStatement($query, array($from_date, $to_date));
        while ($row = sqlFetchArray($res)) {
            process_referral($row);
        }
    }

    /*****************************************************************
    else if ($form_by === '12') {
    // We are reporting on a date range, and assume the applicable date is
    // the issue start date which is presumably also the date of pre-
    // abortion counseling.  The issue end date and the surgery date are
    // not of interest here.
    $query = "SELECT " .
      "l.type, l.begdate, l.pid, " .
      "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, pd.userlist5, " .
      "pd.country_code, pd.status, pd.state, pd.occupation, " .
      "lg.client_status, lg.ab_location " .
      "FROM lists AS l " .
      "JOIN patient_data AS pd ON pd.pid = l.pid $sexcond" .
      "LEFT OUTER JOIN lists_ippf_gcac AS lg ON l.type = 'ippf_gcac' AND lg.id = l.id " .
      // "LEFT OUTER JOIN lists_ippf_con  AS lc ON l.type = 'contraceptive' AND lc.id = l.id " .
      "WHERE l.begdate >= '$from_date' AND l.begdate <= '$to_date' AND " .
      "l.activity = 1 AND l.type = 'ippf_gcac' " .
      "ORDER BY l.pid, l.id";
    $res = sqlStatement($query);
    while ($row = sqlFetchArray($res)) {
      process_issue($row);
    }
    }
    *****************************************************************/

    // else {

    /*****************************************************************
    if ($form_by === '104' || $form_by === '105') {
    $query = "SELECT " .
      "d.name, d.related_code, ds.pid, ds.quantity, " .
      "pd.regdate, pd.referral_source, " .
      "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
      "pd.contrastart$pd_fields " .
      "FROM drug_sales AS ds " .
      "JOIN drugs AS d ON d.drug_id = ds.drug_id " .
      "JOIN patient_data AS pd ON pd.pid = ds.pid $sexcond" .
      "WHERE ds.sale_date IS NOT NULL AND ds.pid != 0 AND " .
      "ds.sale_date >= '$from_date' AND ds.sale_date <= '$to_date' " .
      "ORDER BY ds.pid, ds.sale_id";
    $res = sqlStatement($query);
    while ($row = sqlFetchArray($res)) {
      $key = "(Unspecified)";
      if (!empty($row['related_code'])) {
        $relcodes = explode(';', $row['related_code']);
        foreach ($relcodes as $codestring) {
          if ($codestring === '') continue;
          list($codetype, $code) = explode(':', $codestring);
          if ($codetype !== 'IPPF') continue;
          $key = getContraceptiveMethod($code);
          if (!empty($key)) break;
          $key = "(No Method)";
        }
      }
      if ($form_by === '104') $key .= " / " . $row['name'];
      loadColumnData($key, $row, $row['quantity']);
    }
    }

    if ($form_by !== '9' && $form_by !== '10' && $form_by !== '20' &&
    $form_by !== '104' && $form_by !== '105')
    *****************************************************************/

    if ($form_content != 5 && $form_by !== '9' && $form_by !== '10' && $form_by !== '20') {
        $sqlBindArray = array();

        // This gets us all MA codes, with encounter and patient
        // info attached and grouped by patient and encounter.
        $query = "SELECT " .
        "fe.pid, fe.encounter, fe.date AS encdate, pd.regdate, " .
        "f.user AS provider, " .
        "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
        "pd.contrastart, pd.referral_source$pd_fields, " .
        "b.code_type, b.code, c.related_code, lo.title AS lo_title " .
        "FROM form_encounter AS fe " .
        "JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND " .
        "f.formdir = 'newpatient' AND f.form_id = fe.id AND f.deleted = 0 " .
        "JOIN patient_data AS pd ON pd.pid = fe.pid $sexcond" .
        "LEFT OUTER JOIN billing AS b ON " .
        "b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1 " .
        "AND b.code_type = 'MA' " .
        "LEFT OUTER JOIN codes AS c ON b.code_type = 'MA' AND c.code_type = '12' AND " .
        "c.code = b.code AND c.modifier = b.modifier " .
        "LEFT OUTER JOIN list_options AS lo ON " .
        "lo.list_id = 'superbill' AND lo.option_id = c.superbill " .
        "WHERE fe.date >= ? AND " .
        "fe.date <= ? ";
        array_push($sqlBindArray, $from_date . ' 00:00:00', $to_date . ' 23:59:59');

        if ($form_facility) {
            $query .= "AND fe.facility_id = '$form_facility' ";
            array_push($sqlBindArray, $form_facility);
        }

        $query .= "ORDER BY fe.pid, fe.encounter, b.code";
        $res = sqlStatement($query, $sqlBindArray);

        $prev_encounter = 0;

        while ($row = sqlFetchArray($res)) {
            if ($row['encounter'] != $prev_encounter) {
                $prev_encounter = $row['encounter'];
                process_visit($row);
            }

            if ($row['code_type'] === 'MA') {
                process_ma_code($row);
                if (!empty($row['related_code'])) {
                    $relcodes = explode(';', $row['related_code']);
                    foreach ($relcodes as $codestring) {
                        if ($codestring === '') {
                            continue;
                        }

                        list($codetype, $code) = explode(':', $codestring);
                        if ($codetype !== 'IPPF') {
                            continue;
                        }

                        process_ippf_code($row, $code);
                    }
                }
            }
        } // end while
    } // end if

    // Sort everything by key for reporting.
    ksort($areport);
    foreach ($arr_titles as $atkey => $dummy) {
        ksort($arr_titles[$atkey]);
    }

    if ($form_output != 3) {
        echo "<table border='0' cellpadding='1' cellspacing='2' width='98%'>\n";
    } // end not csv export

    // Generate first column headings line, with category titles.
    //
    genStartRow("bgcolor='#dddddd'");
    // If the key is an MA or IPPF code, then add a column for its description.
    if (uses_description($form_by)) {
        genHeadCell(array('', ''));
    } else {
        genHeadCell('');
    }

    // Generate headings for values to be shown.
    foreach ($form_show as $value) {
        if ($value == '.total') { // Total Services
            genHeadCell('');
        } elseif ($value == '.age2') { // Age
            genHeadCell($arr_show[$value]['title'], false, 2);
        } elseif ($value == '.age9') { // Age
            genHeadCell($arr_show[$value]['title'], false, 9);
        } elseif ($arr_show[$value]['list_id']) {
            genHeadCell($arr_show[$value]['title'], false, count($arr_titles[$value]));
        } elseif (!empty($arr_titles[$value])) {
            genHeadCell($arr_show[$value]['title'], false, count($arr_titles[$value]));
        }
    }

    if ($form_output != 3) {
        genHeadCell('');
    }

    genEndRow();

    // Generate second column headings line, with individual titles.
    //
    genStartRow("bgcolor='#dddddd'");
    // If the key is an MA or IPPF code, then add a column for its description.
    if (uses_description($form_by)) {
        genHeadCell(array($arr_by[$form_by], xl('Description')));
    } else {
        genHeadCell($arr_by[$form_by]);
    }

    // Generate headings for values to be shown.
    foreach ($form_show as $value) {
        if ($value == '.total') { // Total Services
            genHeadCell(xl('Total'));
        } elseif ($value == '.age2') { // Age
            genHeadCell(xl('0-24'), true);
            genHeadCell(xl('25+'), true);
        } elseif ($value == '.age9') { // Age
            genHeadCell(xl('0-10'), true);
            genHeadCell(xl('11-14'), true);
            genHeadCell(xl('15-19'), true);
            genHeadCell(xl('20-24'), true);
            genHeadCell(xl('25-29'), true);
            genHeadCell(xl('30-34'), true);
            genHeadCell(xl('35-39'), true);
            genHeadCell(xl('40-44'), true);
            genHeadCell(xl('45+'), true);
        } elseif ($arr_show[$value]['list_id']) {
            foreach ($arr_titles[$value] as $key => $dummy) {
                genHeadCell(getListTitle($arr_show[$value]['list_id'], $key), true);
            }
        } elseif (!empty($arr_titles[$value])) {
            foreach ($arr_titles[$value] as $key => $dummy) {
                genHeadCell($key, true);
            }
        }
    }

    if ($form_output != 3) {
        genHeadCell(xl('Total'), true);
    }

    genEndRow();

    $encount = 0;

    foreach ($areport as $key => $varr) {
        $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";

        $dispkey = $key;

      // If the key is an MA or IPPF code, then add a column for its description.
        if (uses_description($form_by)) {
            $dispkey = array($key, '');
            $type = $form_by === '102' ? 12 : 11; // MA or IPPF
            $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
            "code_type = ? AND code = ? ORDER BY id LIMIT 1", array($type, $key));
            if (!empty($crow['code_text'])) {
                $dispkey[1] = $crow['code_text'];
            }
        }

        genStartRow("bgcolor='$bgcolor'");

        genAnyCell($dispkey, false, 'detail');

      // This is the column index for accumulating column totals.
        $cnum = 0;
        $totalsvcs = $areport[$key]['.wom'] + $areport[$key]['.men'];

      // Generate data for this row.
        foreach ($form_show as $value) {
            // if ($value == '1') { // Total Services
            if ($value == '.total') { // Total Services
                genNumCell($totalsvcs, $cnum++);
            } elseif ($value == '.age2') { // Age
                for ($i = 0; $i < 2; ++$i) {
                    genNumCell($areport[$key]['.age2'][$i], $cnum++);
                }
            } elseif ($value == '.age9') { // Age
                for ($i = 0; $i < 9; ++$i) {
                    genNumCell($areport[$key]['.age9'][$i], $cnum++);
                }
            } elseif (!empty($arr_titles[$value])) {
                foreach ($arr_titles[$value] as $title => $dummy) {
                    genNumCell($areport[$key][$value][$title], $cnum++);
                }
            }
        }

      // Write the Total column data.
        if ($form_output != 3) {
            $atotals[$cnum] += $totalsvcs;
            genAnyCell($totalsvcs, true, 'dehead');
        }

        genEndRow();
    } // end foreach

    if ($form_output != 3) {
      // Generate the line of totals.
        genStartRow("bgcolor='#dddddd'");

      // If the key is an MA or IPPF code, then add a column for its description.
        if (uses_description($form_by)) {
            genHeadCell(array(xl('Totals'), ''));
        } else {
            genHeadCell(xl('Totals'));
        }

        for ($cnum = 0; $cnum < count($atotals); ++$cnum) {
            genHeadCell($atotals[$cnum], true);
        }

        genEndRow();
      // End of table.
        echo "</table>\n";
    }
} // end of if refresh or export

if ($form_output != 3) {
    ?>
</form>
</center>

<script>
selreport();
    <?php if ($form_output == 2) { ?>
 var win = top.printLogPrint ? top : opener.top;
 win.printLogPrint(window);
<?php } ?>
</script>

</body>
</html>
    <?php
} // end not export
?>
