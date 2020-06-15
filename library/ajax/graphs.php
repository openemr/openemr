<?php

/**
 * Flexible script for graphing entities in OpenEMR.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// Collect passed variable(s)
//  $table is the sql table (or form name if LBF)
//  $name identifies the desired data item
//  $title is used as the title of the graph
$table = trim($_POST['table']);
$name = trim($_POST['name']);
$title = trim($_POST['title']);

$is_lbf = substr($table, 0, 3) === 'LBF';

// acl checks here
//  For now, only allow access for med aco.
//  This can be expanded depending on which table is accessed.
if (!AclMain::aclCheckCore('patients', 'med')) {
    exit;
}

// Conversion functions/constants
function convertFtoC($a)
{
    return ($a - 32) * 0.5556;
}
function getLbstoKgMultiplier()
{
    return 0.45359237;
}
function getIntoCmMultiplier()
{
    return 2.54;
}
function getIdealYSteps($a)
{
    if ($a > 1000) {
        return 200;
    } elseif ($a > 500) {
        return 100;
    } elseif ($a > 100) {
        return 20;
    } elseif ($a > 50) {
        return 10;
    } else {
        return 5;
    }
}

function graphsGetValues($name)
{
    global $is_lbf, $pid, $table;
    if ($is_lbf) {
        // Like below, but for LBF data.
        $values = sqlStatement(
            "SELECT " .
            "ld.field_value AS " . add_escape_custom($name) . ", " .
            "f.date " .
            "FROM forms AS f, lbf_data AS ld WHERE " .
            "f.pid = ? AND " .
            "f.formdir = ? AND " .
            "f.deleted = 0 AND " .
            "ld.form_id = f.form_id AND " .
            "ld.field_id = ? AND " .
            "ld.field_value != '0' " .
            "ORDER BY f.date",
            array($pid, $table, $name)
        );
    } else {
        // Collect the pertinent info and ranges
        //  (Note am skipping values of zero, this could be made to be
        //   optional in the future when using lab values)
        $values = SqlStatement("SELECT " .
            escape_sql_column_name($name, array($table)) . ", " .
        "date " .
        "FROM " . escape_table_name($table) . " " .
        "WHERE " . escape_sql_column_name($name, array($table)) . " != 0 " .
        "AND pid = ? ORDER BY date", array($pid));
    }

    return $values;
}

//Customizations (such as titles and conversions)
if ($is_lbf) {
    $titleGraph = $title;
    if ($name == 'bp_systolic' || $name == 'bp_diastolic') {
        $titleGraph = xl("Blood Pressure") . " (" . xl("mmHg") . ")";
        $titleGraphLine1 = xl("BP Systolic");
        $titleGraphLine2 = xl("BP Diastolic");
    }
} else {
    switch ($name) {
        case "weight":
             $titleGraph = $title . " (" . xl("lbs") . ")";
            break;
        case "weight_metric":
             $titleGraph = $title . " (" . xl("kg") . ")";
             $multiplier = getLbstoKgMultiplier();
             $name = "weight";
            break;
        case "height":
             $titleGraph = $title . " (" . xl("in") . ")";
            break;
        case "height_metric":
             $titleGraph = $title . " (" . xl("cm") . ")";
             $multiplier = getIntoCmMultiplier();
             $name = "height";
            break;
        case "bps":
             $titleGraph = xl("Blood Pressure") . " (" . xl("mmHg") . ")";
             $titleGraphLine1 = xl("BP Systolic");
             $titleGraphLine2 = xl("BP Diastolic");
            break;
        case "bpd":
             $titleGraph = xl("Blood Pressure") . " (" . xl("mmHg") . ")";
             $titleGraphLine1 = xl("BP Diastolic");
             $titleGraphLine2 = xl("BP Systolic");
            break;
        case "pulse":
             $titleGraph = $title . " (" . xl("per min") . ")";
            break;
        case "respiration":
             $titleGraph = $title . " (" . xl("per min") . ")";
            break;
        case "temperature":
             $titleGraph = $title . " (" . xl("F") . ")";
            break;
        case "temperature_metric":
             $titleGraph = $title . " (" . xl("C") . ")";
             $isConvertFtoC = 1;
             $name = "temperature";
            break;
        case "oxygen_saturation":
             $titleGraph = $title . " (" . xl("%") . ")";
            break;
        case "head_circ":
             $titleGraph = $title . " (" . xl("in") . ")";
            break;
        case "head_circ_metric":
             $titleGraph = $title . " (" . xl("cm") . ")";
             $multiplier = getIntoCmMultiplier();
             $name = "head_circ";
            break;
        case "waist_circ":
             $titleGraph = $title . " (" . xl("in") . ")";
            break;
        case "waist_circ_metric":
             $titleGraph = $title . " (" . xl("cm") . ")";
             $multiplier = getIntoCmMultiplier();
             $name = "waist_circ";
            break;
        case "BMI":
             $titleGraph = $title . " (" . xl("kg/m^2") . ")";
            break;
        default:
             $titleGraph = $title;
    }
}

// Collect info
if ($table) {
  // Like below, but for LBF data.
    $values = graphsGetValues($name);
} else {
    exit;
}

// If less than 2 values, then exit
if (sqlNumRows($values) < 2) {
      exit;
}

// If blood pressure, then collect the other reading to allow graphing both in same graph
$isBP = 0;
if ($is_lbf) {
    if ($name == "bp_systolic" || $name == "bp_diastolic") {
        // Set BP flag and collect other pressure reading
        $isBP = 1;
        if ($name == "bp_systolic") {
            $name_alt = "bp_diastolic";
        } else {
            $name_alt = "bp_systolic";
        }

        // Collect the pertinent vitals and ranges.
        $values_alt = graphsGetValues($name_alt);
    }
} else {
    if ($name == "bps" || $name == "bpd") {
        // Set BP flag and collect other pressure reading
        $isBP = 1;
        if ($name == "bps") {
            $name_alt = "bpd";
        }

        if ($name == "bpd") {
            $name_alt = "bps";
        }

        // Collect the pertinent vitals and ranges.
        $values_alt = graphsGetValues($name_alt);
    }
}

// Prepare data
$data = array();
while ($row = sqlFetchArray($values)) {
    if ($row["$name"]) {
        $x = $row['date'];
        if ($multiplier) {
            // apply unit conversion multiplier
            $y = $row["$name"] * $multiplier;
        } elseif ($isConvertFtoC) {
            // apply temp F to C conversion
            $y = convertFtoC($row["$name"]);
        } else {
           // no conversion, so use raw value
            $y = $row["$name"];
        }

        $data[$x][$name] = $y;
    }
}

if ($isBP) {
  //set up the other blood pressure line
    while ($row = sqlFetchArray($values_alt)) {
        if ($row["$name_alt"]) {
            $x = $row['date'];
            if ($multiplier) {
                // apply unit conversion multiplier
                $y = $row["$name_alt"] * $multiplier;
            } elseif ($isConvertFtoC) {
                // apply temp F to C conversion
                $y = convertFtoC($row["$name_alt"]);
            } else {
               // no conversion, so use raw value
                $y = $row["$name_alt"];
            }

            $data[$x][$name_alt] = $y;
        }
    }
}

// Prepare label
$data_final = "";
if ($isBP) {
    $data_final .= xl('Date') . "\t" . $titleGraphLine1 . "\t" . $titleGraphLine2 . "\n";
} else {
    $data_final .= xl('Date') . "\t" . $titleGraph . "\n";
}

// Prepare data
foreach ($data as $date => $value) {
    if ($isBP) {
        $data_final .= $date . "\t" . $value[$name] . "\t" . $value[$name_alt] . "\n";
    } else {
        $data_final .= $date . "\t" . $value[$name] . "\n";
    }
}

// Build and send back the json
$graph_build = array();
$graph_build['data_final'] = $data_final;
$graph_build['title'] = $titleGraph;
// Note need to also use " when building the $data_final rather
// than ' , or else JSON_UNESCAPED_SLASHES doesn't work and \n and
// \t get escaped.
echo json_encode($graph_build, JSON_UNESCAPED_SLASHES);
