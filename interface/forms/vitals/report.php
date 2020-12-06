<?php

/**
 * vitals report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");
require_once($GLOBALS['fileroot'] . "/library/patient.inc");

function US_weight($pounds, $mode = 1)
{

    if ($mode == 1) {
        return $pounds . " " . xl('lb') ;
    } else {
        $pounds_int = floor($pounds);
        $ounces = round(($pounds - $pounds_int) * 16);
        return $pounds_int . " " . xl('lb') . " " . $ounces . " " . xl('oz');
    }
}

function vitals_report($pid, $encounter, $cols, $id, $print = true)
{
    $count = 0;
    $data = formFetch("form_vitals", $id);
    $patient_data = getPatientData($GLOBALS['pid']);
    $patient_age = getPatientAge($patient_data['DOB']);

    $vitals = "";
    if ($data) {
        $vitals .= "<table><tr>";

        foreach ($data as $key => $value) {
            if (
                $key == "id" || $key == "pid" ||
                $key == "user" || $key == "groupname" ||
                $key == "authorized" || $key == "activity" ||
                $key == "date" || $value == "" ||
                $value == "0000-00-00 00:00:00" || $value == "0.0"
            ) {
                // skip certain data
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));

            //modified by BM 06-2009 for required translation
            if ($key == "Temp Method" || $key == "BMI Status") {
                if ($key == "BMI Status") {
                    if ($patient_age <= 20 || (preg_match('/month/', $patient_age))) {
                        $value = "See Growth-Chart";
                    }
                }

                $vitals .= '<td><div class="bold" style="display:inline-block">' . xlt($key) . ': </div><div class="text" style="display:inline-block">' . xlt($value) . "</div></td>";
            } elseif ($key == "Bps") {
                $bps = $value;
                if (!empty($bpd)) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt('Blood Pressure') . ": </div><div class='text' style='display:inline-block'>" . text($bps) . "/" . text($bpd)  . "</div></td>";
                } else {
                    continue;
                }
            } elseif ($key == "Bpd") {
                $bpd = $value;
                if ($bps) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt('Blood Pressure') . ": </div><div class='text' style='display:inline-block'>" . text($bps) . "/" . text($bpd)  . "</div></td>";
                } else {
                    continue;
                }
            } elseif ($key == "Weight") {
                $convValue = number_format($value * 0.45359237, 2);
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>";
                // show appropriate units
                $mode = $GLOBALS['us_weight_format'];
                if ($GLOBALS['units_of_measurement'] == 2) {
                    $vitals .=  text($convValue) . " " . xlt('kg') . " (" . text(US_weight($value, $mode)) . ")";
                } elseif ($GLOBALS['units_of_measurement'] == 3) {
                    $vitals .=  text(US_weight($value, $mode));
                } elseif ($GLOBALS['units_of_measurement'] == 4) {
                    $vitals .= text($convValue) . " " . xlt('kg');
                } else { // = 1 or not set
                    $vitals .= text(US_weight($value, $mode)) . " (" . text($convValue) . " " . xlt('kg')  . ")";
                }

                $vitals .= "</div></td>";
            } elseif ($key == "Height" || $key == "Waist Circ"  || $key == "Head Circ") {
                $convValue = round(number_format($value * 2.54, 2), 1);
                // show appropriate units
                if ($GLOBALS['units_of_measurement'] == 2) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($convValue) . " " . xlt('cm') . " (" . text($value) . " " . xlt('in')  . ")</div></td>";
                } elseif ($GLOBALS['units_of_measurement'] == 3) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('in') . "</div></td>";
                } elseif ($GLOBALS['units_of_measurement'] == 4) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($convValue) . " " . xlt('cm') . "</div></td>";
                } else { // = 1 or not set
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('in') . " (" . text($convValue) . " " . xlt('cm')  . ")</div></td>";
                }
            } elseif ($key == "Temperature") {
                $convValue = number_format((($value - 32) * 0.5556), 2);
                // show appropriate units
                if ($GLOBALS['units_of_measurement'] == 2) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($convValue) . " " . xlt('C') . " (" . text($value) . " " . xlt('F')  . ")</div></td>";
                } elseif ($GLOBALS['units_of_measurement'] == 3) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('F') . "</div></td>";
                } elseif ($GLOBALS['units_of_measurement'] == 4) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($convValue) . " " . xlt('C') . "</div></td>";
                } else { // = 1 or not set
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('F') . " (" . text($convValue) . " " . xlt('C')  . ")</div></td>";
                }
            } elseif ($key == "Pulse" || $key == "Respiration"  || $key == "Oxygen Saturation" || $key == "BMI") {
                $value = number_format($value, 0);
                if ($key == "Oxygen Saturation") {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('%') . "</div></td>";
                } elseif ($key == "BMI") {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('kg/m^2') . "</div></td>";
                } else { //pulse and respirations
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('per min') . "</div></td>";
                }
            } else {
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div><div class='text' style='display:inline-block'>" . text($value) . "</div></td>";
            }

            $count++;

            if ($count == $cols) {
                $count = 0;
                $vitals .= "</tr><tr>\n";
            }
        }

        $vitals .= "</tr></table>";
    }

    if ($print) {
        echo $vitals ;
    } else {
        return $vitals;
    }
}
