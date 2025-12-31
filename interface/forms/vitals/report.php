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
require_once($GLOBALS["srcdir"] . "/api.inc.php");
require_once($GLOBALS['fileroot'] . "/library/patient.inc.php");

use OpenEMR\Common\Utils\MeasurementUtils;

function vitals_report($pid, $encounter, $cols, $id, $print = true)
{
    $measurementUtils = new MeasurementUtils();
    $count = 0;
    $data = formFetch("form_vitals", $id);
    $patient_data = getPatientData($GLOBALS['pid']);
    $patient_age = getPatientAge($patient_data['DOB']);
    $is_pediatric_patient = ($patient_age <= 20 || (preg_match('/month/', (string) $patient_age)));

    $vitals = "";
    if ($data) {
        $vitals .= "<table><tr>";

        foreach ($data as $key => $value) {
            if (
                in_array($key, ["uuid", "id", "pid", "user", "groupname", "authorized", "activity", "date"]) || $value == "" ||
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
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($measurementUtils->formatWeight($value)) . "</div></td>";
            } elseif (in_array($key, ["Height", "Waist Circ", "Head Circ"])) {
                $value = floatval($value);
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($measurementUtils->formatLength($value)) . "</div></td>";
            } elseif ($key == "Temperature") {
                $value = floatval($value);
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($key) . ": </div></td><td><div class='text' style='display:inline-block'>" . text($measurementUtils->formatTemperature($value)) . "</div></td>";
            } elseif (in_array($key, ["Pulse", "Respiration", "Oxygen Saturation", "BMI", "Oxygen Flow Rate"])) {
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
            } elseif (in_array($key, ["Ped Weight Height", 'Ped Bmi', 'Ped Head Circ'])) {
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
