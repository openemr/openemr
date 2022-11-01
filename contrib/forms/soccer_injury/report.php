<?php

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc.php");

$si_report_cols  = 2;
$si_report_colno = 0;

// Helper function used by soccer_injury_report().
// Writes a title/value pair to a table cell.
//
function si_report_item($title, $value)
{
    global $si_report_cols, $si_report_colno;
    if (!$value) {
        return;
    }

    if (++$si_report_colno > $si_report_cols) {
        $si_report_colno = 1;
        echo " </tr>\n <tr>\n";
    }

    echo "  <td valign='top'><span class='bold'>" . text($title) . ": </span>" .
    "<span class='text'>" . text($value) . " &nbsp;</span></td>\n";
}

// This function is invoked from printPatientForms in report.inc.php
// when viewing a "comprehensive patient report".  Also from
// interface/patient_file/encounter/forms.php.
//
function soccer_injury_report($pid, $encounter, $cols, $id)
{
    global $si_report_cols;

    $arr_gameplay = array(
    '1' => '1st Quarter',
    '2' => '2nd Quarter',
    '3' => '3rd Quarter',
    '4' => '4th Quarter',
    '5' => 'Warm Up',
    '6' => 'Extra Time',
    '7' => 'Cool Down',
    '11' => 'Training Warm Up',
    '12' => 'Training Session',
    '13' => 'Training Cool Down',
    );

    $arr_activity = array(
    '1' => 'Tackling',
    '2' => 'Tackled',
    '3' => 'Collision',
    '4' => 'Kicked',
    '5' => 'Use of Elbow',
    '6' => 'Hit by Ball',
    '7' => 'Other:',
    '11' => 'Passing',
    '12' => 'Shooting',
    '13' => 'Running',
    '14' => 'Dribbling',
    '15' => 'Heading',
    '16' => 'Jumping',
    '17' => 'Landing',
    '18' => 'Fall',
    '19' => 'Stretching',
    '20' => 'Twist/Turning',
    '21' => 'Throwing',
    '22' => 'Diving',
    '23' => 'Other NC:',
    );

    $arr_surface = array(
    '1' => 'Pitch'      ,
    '2' => 'Training'   ,
    '3' => 'Artificial' ,
    '4' => 'All Weather',
    '5' => 'Indoor'     ,
    '6' => 'Gym'        ,
    '7' => 'Other'      ,
    );

    $arr_position = array(
    '1' => 'Defender'        ,
    '2' => 'Midfield Offense',
    '3' => 'Midfield Defense',
    '4' => 'Wing Back'       ,
    '5' => 'Forward'         ,
    '6' => 'Striker'         ,
    '7' => 'Goal Keeper'     ,
    '8' => 'Starting Lineup' ,
    '9' => 'Substitute'      ,
    );

    $arr_footwear = array(
    '1' => 'Molded Cleat'     ,
    '2' => 'Detachable Cleats',
    '3' => 'Indoor Shoes'     ,
    '4' => 'Turf Shoes'       ,
    );

    $arr_equip = array(
    '1' => 'Shin Pads'      ,
    '2' => 'Gloves'         ,
    '3' => 'Ankle Strapping',
    '4' => 'Knee Strapping' ,
    '5' => 'Bracing'        ,
    '6' => 'Synthetic Cast' ,
    );

    $arr_side = array(
    '1' => 'Left'          ,
    '2' => 'Right'         ,
    '3' => 'Bilateral'     ,
    '4' => 'Not Applicable',
    );

    $arr_removed = array(
    '1' => 'Immediately',
    '2' => 'Later'      ,
    '3' => 'Not at All' ,
    );

    $arr_treat = array(
    '1' => 'Hospital A&amp;E Dept',
    '2' => 'General Practitioner' ,
    '3' => 'Physiotherapist'      ,
    '4' => 'Nurse'                ,
    '5' => 'Hospital Specialist'  ,
    '6' => 'Osteopath'            ,
    '7' => 'Chiropractor'         ,
    '8' => 'Sports Massage Th'    ,
    '9' => 'Sports Physician'     ,
    );

 /****
 $row = sqlQuery ("SELECT forms.date AS occdate, si.* " .
  "FROM forms, form_soccer_injury AS si WHERE " .
  "forms.formdir = 'soccer_injury' AND forms.form_id = '$id' AND " .
  "si.id = '$id' AND si.activity = '1'");
 ****/

    $row = sqlQuery("SELECT form_encounter.onset_date AS occdate, si.* " .
    "FROM forms, form_encounter, form_soccer_injury AS si WHERE " .
    "forms.formdir = 'soccer_injury' AND " .
    "forms.form_id = ? AND " .
    "si.id = ? AND si.activity = '1' AND " .
    "form_encounter.encounter = forms.encounter AND " .
    "form_encounter.pid = forms.pid", array($id));

    if (!$row) {
        return;
    }

    $si_report_cols = $cols;

    echo "<table cellpadding='0' cellspacing='0'>\n";
    echo " <tr>\n";

    si_report_item("Occurred", substr($row['occdate'], 0, 10) . " " . substr($row['siinjtime'], 0, 5));
    si_report_item("During", $arr_gameplay[$row['sigametime']]);
    si_report_item("Mechanism", $arr_activity[$row['simechanism']] . ' ' . $row['simech_other']);
    si_report_item("Surface", $arr_surface[$row['sisurface']]);
    si_report_item("Position", $arr_position[$row['siposition']]);
    si_report_item("Footwear", $arr_footwear[$row['sifootwear']]);
    foreach ($arr_equip as $key => $value) {
        if ($row["siequip_$key"]) {
            si_report_item("Equipment", $value);
        }
    }

    si_report_item("Side", $arr_side[$row['siside']]);
    si_report_item("Removed", $arr_removed[$row['siremoved']]);
    foreach ($arr_treat as $key => $value) {
        if ($row["sitreat_$key"]) {
            si_report_item("Treatment", $value);
        }
    }

    if ($row["sitreat_other"]) {
        si_report_item("Treatment", $row["sitreat_other"]);
    }

    si_report_item("To Return", $row['sinoreturn'] ? "No" : "Yes");

    echo " </tr>\n";
    echo "</table>\n";
}
