<?php

/**
 * Vitals report generation.
 *
 * Generates an HTML representation of patient vitals data.
 *
 * @package   OpenEMR
 * @category  Forms
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright 2018 Brady Miller
 * @copyright 2021 Sherwin Gaddis
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @link      http://www.open-emr.org
 */

// @version PHP 7.4+ (Example - adjust if needed based on project requirements)

require_once __DIR__ . "/../../globals.php";
require_once $GLOBALS["srcdir"] . "/api.inc.php";
require_once $GLOBALS['fileroot'] . "/library/patient.inc.php";

/**
 * Formats weight in pounds to US standard display (lbs or lbs/oz).
 *
 * @param float $pounds Weight in pounds.
 * @param int   $mode   Display mode: 1 for lbs only, other for lbs and oz. Default is 1.
 *
 * @return string Formatted weight string.
 */
function US_weight($pounds, $mode = 1)
{
    if ($mode == 1) {
        return $pounds . " " . xl('lb');
    } else {
        $pounds_int = floor($pounds);
        $ounces = round(($pounds - $pounds_int) * 16);
        return $pounds_int . " " . xl('lb') . " " . $ounces . " " . xl('oz');
    }
}

/**
 * Fetches and formats vitals data for display.
 *
 * Retrieves vitals form data and generates an HTML table snippet.
 * Note: PHPCS flags the function name 'vitals_report' based on potential
 * naming convention rules (e.g., PEAR). Renaming was avoided to prevent
 * breaking existing calls, assuming snake_case might be acceptable in this context.
 *
 * @param int      $pid       Patient ID.
 * @param int      $encounter Encounter ID.
 * @param int      $cols      Number of columns for display layout.
 * @param int      $id        Form ID for formFetch.
 * @param bool     $print     Whether to echo the result (true) or return it (false). Default true.
 *
 * @return string|null HTML string if $print is false, null otherwise (echos output).
 */
function vitals_report($pid, $encounter, $cols, $id, $print = true)
{
    $count = 0;
    $data = formFetch("form_vitals", $id);
    if (!$data) {
        // No data found, return empty string or null depending on $print
        return $print ? null : '';
    }

    $patient_data = getPatientData($GLOBALS['pid']);
    $patient_age = getPatientAge($patient_data['DOB'] ?? null); // Use null coalesce for safety
    // Check if age string contains 'month' or if numeric age is <= 20
    $is_pediatric_patient = (is_numeric($patient_age) && $patient_age <= 20) ||
                            (strpos($patient_age, 'month') !== false);

    $vitals = "<table><tr>";
    $bps = null; // Initialize BPS/BPD for proper BP pair handling
    $bpd = null;

    foreach ($data as $key => $value) {
        // Skip metadata or empty values
        if (in_array($key, ["uuid", "id", "pid", "user", "groupname", "authorized", "activity", "last_updated"]) ||
            $value === "" || $value === "0000-00-00 00:00:00" || $value === "0.0") {
            continue;
        }

        // Handle date separately
        if ($key == "date") {
            $formatted_date = date("m/d/Y g:i A", strtotime($value));
            $vitals .= "<td><div class='font-weight-bold d-inline-block'>"
                . xlt("Date") . ": </div></td><td><div class='text' style='display:inline-block'>"
                . text($formatted_date) . "</div></td>";
            $count++;
            if ($count == $cols) {
                $count = 0;
                $vitals .= "</tr><tr>\n";
            }
            continue; // Move to next item after handling date
        }

        // Normalize 'on' checkbox value
        if ($value == "on") {
            $value = "yes";
        }

        // Add units for specific fields
        if ($key == 'inhaled_oxygen_concentration') {
            $value .= " %";
        }

        // Format the display key
        $displayKey = ucwords(str_replace("_", " ", $key));

        // --- Start of specific key handling ---

        // Handle translatable text values
        if ($displayKey == "Temp Method" || $displayKey == "BMI Status") {
            if ($displayKey == "BMI Status" && $is_pediatric_patient) {
                // For pediatric patients, BMI Status might link to growth charts
                $value = "See Growth-Chart";
            }
            $vitals .= '<td><div class="bold" style="display:inline-block">'
                . xlt($displayKey) . ': </div></td><td><div class="text" style="display:inline-block">'
                . xlt($value) . "</div></td>";
        // Handle Blood Pressure (Systolic)
        } elseif ($displayKey == "Bps") {
            $bps = $value; // Store BPS
            // Only output BP if BPD is also present
            if (!empty($bpd)) {
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>"
                    . xlt('Blood Pressure') . ": </div></td><td><div class='text' style='display:inline-block'>"
                    . text($bps) . "/" . text($bpd) . "</div></td>";
                $bps = $bpd = null; // Reset after printing pair
            } else {
                // BPS found, but BPD not yet, skip output for now
                continue;
            }
        // Handle Blood Pressure (Diastolic)
        } elseif ($displayKey == "Bpd") {
            $bpd = $value; // Store BPD
             // Only output BP if BPS is also present
            if (!empty($bps)) {
                $vitals .= "<td><div class='font-weight-bold d-inline-block'>"
                    . xlt('Blood Pressure') . ": </div></td><td><div class='text' style='display:inline-block'>"
                    . text($bps) . "/" . text($bpd) . "</div></td>";
                $bps = $bpd = null; // Reset after printing pair
            } else {
                 // BPD found, but BPS not yet, skip output for now
                continue;
            }
        // Handle Weight with unit conversion
        } elseif ($displayKey == "Weight") {
            $value = floatval($value);
            $convValue = number_format($value * 0.45359237, 2);
            $mode = $GLOBALS['us_weight_format'] ?? 1; // Default mode if not set
            $units_pref = $GLOBALS['units_of_measurement'] ?? 1; // Default if not set

            $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($displayKey)
                . ": </div></td><td><div class='text' style='display:inline-block'>";
            if ($units_pref == 2) { // Metric primary, US secondary
                $vitals .= text($convValue) . " " . xlt('kg')
                    . " (" . text(US_weight($value, $mode)) . ")";
            } elseif ($units_pref == 3) { // US only
                $vitals .= text(US_weight($value, $mode));
            } elseif ($units_pref == 4) { // Metric only
                $vitals .= text($convValue) . " " . xlt('kg');
            } else { // Default: US primary, Metric secondary
                $vitals .= text(US_weight($value, $mode)) . " (" . text($convValue) . " "
                    . xlt('kg') . ")";
            }
            $vitals .= "</div></td>";
        // Handle Height, Waist, Head Circumference with unit conversion
        } elseif (in_array($displayKey, ["Height", "Waist Circ", "Head Circ"])) {
            $value = floatval($value);
            $convValue = number_format(round($value * 2.54, 1), 2);
            $units_pref = $GLOBALS['units_of_measurement'] ?? 1;

            $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($displayKey)
                . ": </div></td><td><div class='text' style='display:inline-block'>";
            if ($units_pref == 2) { // Metric primary, US secondary
                $vitals .= text($convValue) . " " . xlt('cm') . " (" . text($value) . " "
                    . xlt('in') . ")";
            } elseif ($units_pref == 3) { // US only
                $vitals .= text($value) . " " . xlt('in');
            } elseif ($units_pref == 4) { // Metric only
                $vitals .= text($convValue) . " " . xlt('cm');
            } else { // Default: US primary, Metric secondary
                $vitals .= text($value) . " " . xlt('in') . " (" . text($convValue) . " "
                    . xlt('cm') . ")";
            }
            $vitals .= "</div></td>";
        // Handle Temperature with unit conversion
        } elseif ($displayKey == "Temperature") {
            $value = floatval($value);
            $convValue = number_format((($value - 32) * 0.5556), 2);
            $units_pref = $GLOBALS['units_of_measurement'] ?? 1;

            $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($displayKey)
                . ": </div></td><td><div class='text' style='display:inline-block'>";
            if ($units_pref == 2) { // Metric primary, US secondary
                $vitals .= text($convValue) . " " . xlt('C') . " (" . text($value) . " "
                    . xlt('F') . ")";
            } elseif ($units_pref == 3) { // US only
                $vitals .= text($value) . " " . xlt('F');
            } elseif ($units_pref == 4) { // Metric only
                $vitals .= text($convValue) . " " . xlt('C');
            } else { // Default: US primary, Metric secondary
                $vitals .= text($value) . " " . xlt('F') . " (" . text($convValue) . " "
                    . xlt('C') . ")";
            }
            $vitals .= "</div></td>";
        // Handle Pulse, Respiration, O2 Sat, BMI, O2 Flow
        } elseif (in_array($displayKey, ["Pulse", "Respiration", "Oxygen Saturation", "BMI", "Oxygen Flow Rate"])) {
            $value = floatval($value);
            $c_value = number_format($value, 0); // Default formatting
            $unit = '';
            $label = xlt($displayKey);

            if ($displayKey == "Oxygen Saturation") {
                $unit = xlt('%');
            } elseif ($displayKey == "Oxygen Flow Rate") {
                $c_value = number_format($value, 2); // Needs more precision
                $unit = xlt('l/min');
            } elseif ($displayKey == "BMI") {
                $unit = xlt('kg/m^2');
            } elseif ($displayKey == "Pulse" || $displayKey == "Respiration") {
                $unit = xlt('per min');
            }

            $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . $label
                . ": </div></td><td><div class='text' style='display:inline-block'>"
                . text($c_value) . ($unit ? " " . $unit : "") . "</div></td>";
        // Handle Pediatric Percentiles
        } elseif (in_array($key, ['ped_weight_height_percentile', 'ped_bmi_percentile', 'ped_head_circ_percentile'])) {
            // Use original key here for mapping
            if ($is_pediatric_patient) {
                $value = floatval($value);
                $c_value = number_format($value, 0);
                $label = '';
                if ($key == "ped_weight_height_percentile") {
                    $label = xlt("Pediatric Height Weight Percentile");
                } elseif ($key == "ped_bmi_percentile") {
                    $label = xlt("Pediatric BMI Percentile");
                } elseif ($key == "ped_head_circ_percentile") {
                    $label = xlt("Pediatric Head Circumference Percentile");
                }

                if ($label) {
                    $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . $label
                        . ": </div></td><td><div class='text' style='display:inline-block'>"
                        . text($c_value) . " " . xlt('%') . "</div></td>";
                } else {
                    // Don't output if label couldn't be determined (shouldn't happen with in_array)
                    continue;
                }
            } else {
                // Don't show pediatric fields for non-pediatric patients
                continue;
            }
        // Default handler for other keys
        } else {
            $vitals .= "<td><div class='font-weight-bold d-inline-block'>" . xlt($displayKey)
                . ": </div></td><td><div class='text' style='display:inline-block'>"
                . text($value) . "</div></td>";
        }

        // --- End of specific key handling ---

        $count++;
        // Check if we need to wrap to the next row
        if ($count == $cols) {
            $count = 0;
            $vitals .= "</tr><tr>\n";
        }
    } // End foreach loop

    // Close the table properly, handling potentially empty last row
    if ($count == 0 && substr($vitals, -5) === "<tr>\n") {
        // Remove empty trailing row start
        $vitals = substr($vitals, 0, -5);
    } else {
        // Close the last row if it wasn't closed by reaching $cols
        $vitals .= "</tr>";
    }
    $vitals .= "</table>";

    // Output or return based on $print flag
    if ($print) {
        echo $vitals;
        return null; // Explicitly return null when printing
    } else {
        return $vitals;
    }
}
