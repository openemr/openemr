<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");
include_once($GLOBALS['fileroot']."/library/patient.inc");


function US_weight($pounds, $mode = 1)
{

    if ($mode==1) {
        return $pounds . " " . xl('lb') ;
    } else {
        $pounds_int=floor($pounds);
        $ounces=round(($pounds-$pounds_int)*16);
        return $pounds_int . " " . xl('lb') . " " . $ounces . " " . xl('oz');
    }
}

function vitals_report($pid, $encounter, $cols, $id, $print = true)
{
    $count = 0;
    $data = formFetch("form_vitals", $id);
    if (!$data) {
        return '';
    }

    $patient_data = getPatientData($GLOBALS['pid']);
    $patient_age = getPatientAge($patient_data['DOB']);

    $vitals="";

    foreach ($data as $key => $value) {
        if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" ||
            $key == "authorized" || $key == "activity" || $key == "date" || $key == "bpd" ||
            $value == "" || $value == "0000-00-00 00:00:00" || $value == "0.0" ) {
            // skip certain data
            continue;
        }

        if ($value == "on") {
            $value = "yes";
        }

        $key = ucwords(str_replace("_", " ", $key));

        // Perform transforms for $key and/or $value as needed.  At end only $key will be translated.
        // Should consider use of switch rather than million ifs

        //modified by BM 06-2009 for required translation
        if ($key == "Temp Method" || $key == "BMI Status") {
            if ($key == "BMI Status") {
                if ($patient_age <= 20 || (preg_match('/month/', $patient_age))) {
                    $value = "See Growth-Chart";
                }
            }
            $value = xl($value);
        } elseif ($key == "Bps") {
            $value = sprintf('%s / %s', $data["bps"], $data["bpd"]);
            $key = 'Blood Pressure';
        } elseif ($key == "Weight") {
            $convValue = number_format($value*0.45359237, 2);
            // show appropriate units
            $mode=$GLOBALS['us_weight_format'];
            if ($GLOBALS['units_of_measurement'] == 2) {
                $value =  $convValue . " " . xl('kg') . " (" . US_weight($value, $mode) . ")";
            } elseif ($GLOBALS['units_of_measurement'] == 3) {
                $value =  US_weight($value, $mode) ;
            } elseif ($GLOBALS['units_of_measurement'] == 4) {
                $value = $convValue . " " . xl('kg') ;
            } else { // = 1 or not set
                $value = US_weight($value, $mode) . " (" . $convValue . " " . xl('kg')  . ")";
            }
        } elseif ($key == "Height" || $key == "Waist Circ"  || $key == "Head Circ") {
            $convValue = round(number_format($value*2.54, 2), 1);
            // show appropriate units
            if ($GLOBALS['units_of_measurement'] == 2) {
                $value = $convValue . " " . xl('cm') . " (" . $value . " " . xl('in')  . ")";
            } elseif ($GLOBALS['units_of_measurement'] == 3) {
                $value = $value . " " . xl('in');
            } elseif ($GLOBALS['units_of_measurement'] == 4) {
                $value = $convValue . " " . xl('cm');
            } else { // = 1 or not set
                $value = $value . " " . xl('in') . " (" . $convValue . " " . xl('cm')  . ")";
            }
        } elseif ($key == "Temperature") {
            $convValue = number_format((($value-32)*0.5556), 2);
            // show appropriate units
            if ($GLOBALS['units_of_measurement'] == 2) {
                $value = $convValue . " " . xl('C') . " (" . $value . " " . xl('F')  . ")";
            } elseif ($GLOBALS['units_of_measurement'] == 3) {
                $value = $value . " " . xl('F');
            } elseif ($GLOBALS['units_of_measurement'] == 4) {
                $value = $convValue . " " . xl('C');
            } else { // = 1 or not set
                $value = $value . " " . xl('F') . " (" . $convValue . " " . xl('C')  . ")";
            }
        } elseif ($key == "Pulse" || $key == "Respiration"  || $key == "Oxygen Saturation" || $key == "BMI") {
            $value = number_format($value, 0);
            if ($key == "Oxygen Saturation") {
                $value = $value . " " . xl('%');
            } elseif ($key == "BMI") {
                $value = $value . " " . xl('kg/m^2');
            } else { //pulse and respirations
                $value = $value . " " . xl('per min');
            }
        }

        $vitals .= sprintf('<div class="col-3"><small><strong>%s</strong></small>: %s</div>%s', xl($key), $value, PHP_EOL);
    }

    $vitals = sprintf('
        <div class="container">
            <div class="row">
                %s
            </div>
        </div>',
        $vitals);

    if ($print) {
        echo $vitals ;
    } else {
        return $vitals;
    }
}
