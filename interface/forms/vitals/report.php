<?php

/**
 * vitals report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
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
    $is_pediatric_patient = ($patient_age <= 20 || (preg_match('/month/', $patient_age)));

    $vitals = "";
    if ($data) {
        $vitals .= "<table><tr>";

        foreach ($data as $key => $value) {
            if (
                $key == "uuid" ||
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

            if ($key == 'inhaled_oxygen_concentration') {
                $value .= " %";
            }

            $key = ucwords(str_replace("_", " ", $key));

            //modified by BM 06-2009 for required translation
            if ($key == "Temp Method" || $key == "BMI Status") {
                if ($key == "BMI Status") {
                    if ($is_pediatric_patient) {
                        $value = "See Growth-Chart";
                    }
                }

                $vitals .= '<td><div class="bold" style="display:inline-block">' . xlt($key) . ': </div></td><td><div class="text" style="display:inline-block">' . xlt($value) . "</div></td>";
            } elseif ($key == "Bps") {
                $bps = $value;
                if (!empty($bpd)) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt('Blood Pressure') . ": </div></td><td><div class='text' style='display:inline-block'>" . text($bps) . "/" . text($bpd)  . "</div></td>";
                } else {
                    continue;
                }
            } elseif ($key == "Bpd") {
                $bpd = $value;
                if ($bps) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt('Blood Pressure') . ": </div></td><td><div class='text' style='display:inline-block'>" . text($bps) . "/" . text($bpd)  . "</div></td>";
                } else {
                    continue;
                }
            } elseif ($key == "Weight") {
                $value = floatval($value);
                $convValue = number_format($value * 0.45359237, 2);
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>";
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
                $value = floatval($value);
                $convValue = round(number_format($value * 2.54, 2), 1);
                // show appropriate units
                if ($GLOBALS['units_of_measurement'] == 2) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($convValue) . " " . xlt('cm') . " (" . text($value) . " " . xlt('in')  . ")</div></td>";
                } elseif ($GLOBALS['units_of_measurement'] == 3) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('in') . "</div></td>";
                } elseif ($GLOBALS['units_of_measurement'] == 4) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($convValue) . " " . xlt('cm') . "</div></td>";
                } else { // = 1 or not set
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('in') . " (" . text($convValue) . " " . xlt('cm')  . ")</div></td>";
                }
            } elseif ($key == "Temperature") {
                $value = floatval($value);
                $convValue = number_format((($value - 32) * 0.5556), 2);
                // show appropriate units
                if ($GLOBALS['units_of_measurement'] == 2) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($convValue) . " " . xlt('C') . " (" . text($value) . " " . xlt('F')  . ")</div></td>";
                } elseif ($GLOBALS['units_of_measurement'] == 3) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('F') . "</div></td>";
                } elseif ($GLOBALS['units_of_measurement'] == 4) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($convValue) . " " . xlt('C') . "</div></td>";
                } else { // = 1 or not set
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($value) . " " . xlt('F') . " (" . text($convValue) . " " . xlt('C')  . ")</div></td>";
                }
            } elseif ($key == "Pulse" || $key == "Respiration"  || $key == "Oxygen Saturation" || $key == "BMI" || $key == "Oxygen Flow Rate") {
                $value = floatval($value);
                $c_value = number_format($value, 0);
                if ($key == "Oxygen Saturation") {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('%') . "</div></td>";
                } elseif ($key == "Oxygen Flow Rate") {
                    $c_value = number_format($value, 2);
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('l/min') . "</div></td>";
                } elseif ($key == "BMI") {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('kg/m^2') . "</div></td>";
                } else { //pulse and respirations
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('per min') . "</div></td>";
                }
            } elseif ($key == "Ped Weight Height" || $key == 'Ped Bmi' || $key == 'Ped Head Circ') {
                $value = floatval($value);
                if ($is_pediatric_patient) {
                    $c_value = number_format($value, 0);
                    if ($key == "Ped Weight Height") {
                        $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt("Pediatric Height Weight Percentile") . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('%') . "</div></td>";
                    } elseif ($key == "Ped Bmi") {
                        $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt("Pediatric BMI Percentile") . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('%') . "</div></td>";
                    } elseif ($key == "Ped Head Circ") {
                        $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt("Pediatric Head Circumference Percentile") . ": </div></td><td><div class='text' style='display:inline-block'>" . text($c_value) . " " . xlt('%') . "</div></td>";
                    }
                }
            } else {
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($value) . "</div></td>";
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
