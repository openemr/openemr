<?php

/**
 * vitals report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Daniel Pflieger <daniel@mi-squared.com> <daniel@growlingflea.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2022 Daniel Pflieger <daniel@mi-squared.com> <daniel@growlingflea.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");
require_once($GLOBALS['fileroot'] . "/library/patient.inc");
require_once($GLOBALS['fileroot'] . "/library/classes/GrowthCharts.class.php");

function US_weight($pounds, $mode = 1): string
{

    if ($mode == 1) {
        return $pounds . " " . xl('lb') ;
    } else {
        $pounds_int = floor($pounds);
        $ounces = round(($pounds - $pounds_int) * 16);
        return $pounds_int . " " . xl('lb') . " " . $ounces . " " . xl('oz');
    }
}

function displayLengthString($value): string
{
    $convValue = round(number_format($value * 2.54, 2), 1);
    if ($GLOBALS['units_of_measurement'] == 2) {
        return $convValue . " " . xl('cm') . " (" . $value . " " . xl('in')  . ")";
    } elseif ($GLOBALS['units_of_measurement'] == 3) {
        return $value . " " . xl('in') ;
    } elseif ($GLOBALS['units_of_measurement'] == 4) {
        return $convValue . " " . xl('cm');
    } else { // = 1 or not set
        return $value . " " . xl('in') . " (" . $convValue . " " . xl('cm')  . ")";
    }
}

function vitals_report($pid, $encounter, $cols, $id, $print = true)
{

    $data = formFetch("form_vitals", $id);
    $patient_data = getPatientData($GLOBALS['pid']);

    //***Vitals Add
    $ageYMD = getPatientAgeYMD($patient_data['DOB'], $data['date']);
    $ageDays = ceil(getPatientAgeInDays($patient_data['DOB'], $data['date']));
    $age_in_months = $ageYMD['age_in_months'];

    //Check if patient has Down Syndrome.  If so, we use the CDC charts since WHO does not have
    //data available.
    $hasDS = hasDownSyndrome($data['pid']);

    //else print years and months
    $data["Age: "] = $ageYMD['ageinYMD'];
    $data["Age in Days: "] = $ageDays;
    $data["BMI"] = number_format(($data['weight'] / 2.204) / pow(($data['height'] * 2.54 * .01), 2), 2) ;
    if ($ageDays <= 1856 && !$hasDS) {
        $who_data = GrowthCharts::getWhoStats(
            $ageDays,
            $patient_data['sex'],
            $data['weight'] / 2.204,
            $data['height'] * 2.54,
            $data['head_circ'] * 2.54
        );
        $data["Height %ile WHO"] = number_format($who_data['height_pct'], 2) ?? "-";
        $data["Height Zscore WHO"] = number_format($who_data['height_Z']['Zscore'], 2) ?? "-";
        $data["Weight %ile WHO"] = number_format($who_data['weight_pct'], 2) ?? "-";
        $data["Weight Zscore WHO"] = number_format($who_data['weight_Z']['Zscore'], 2) ?? "-";
        $data["Weight for Height %ile WHO"] = number_format($who_data['weight_height_pct'], 2) ?? "-";
        $data["Head Circ %ile WHO"] = number_format($who_data['head_pct'], 2) ?? "-";
        $data["Head Circ Zscore WHO"] = number_format($who_data['head_Z']['Zscore'], 2) ?? "-";
        $data["BMI %ile WHO"] = number_format($who_data['bmi_pct'], 2) ?? "-";
        $data["BMI Zscore WHO"] = number_format($who_data['bmi_Z']['Zscore'], 2) ?? "-";
        $data["BMI Status"] = $who_data['bmi_status'];
    }

    if (($age_in_months >= 23.5) && ($age_in_months < 240.5) && !$hasDS) {
        $cdc_data = GrowthCharts::getCdcStats(
            $age_in_months,
            $patient_data['sex'],
            $data['weight'] / 2.204,
            $data['height'] * 2.54,
            $data['BMI']
        );
        $data["Height %ile CDC"] = number_format($cdc_data['height_pct'], 1 ?? 'x');
        $data["Weight %ile CDC"] = number_format($cdc_data['weight_pct'], 1) ?? 'x';
        $data["Weight for Height %ile CDC"] = number_format($cdc_data['weight_height_pct'], 1) ?? 'x';
    }

    $report = $data;


    //Vitals Data


    //Measurements data, use end users preferences to display data
    if (is_numeric($report['weight']) && $report['weight'] > 0) {
        $convValue = number_format($report['weight'] * 0.45359237, 2);
        $mode = $GLOBALS['us_weight_format'];
        if ($GLOBALS['units_of_measurement'] == 2) {
            $weightDisplay = $convValue . " " . xl('kg') . " (" . US_weight($report['weight'], $mode) . ")";
        } elseif ($GLOBALS['units_of_measurement'] == 3) {
            $weightDisplay = US_weight($report['weight'], $mode);
        } elseif ($GLOBALS['units_of_measurement'] == 4) {
            $weightDisplay = $convValue . " " . xl('kg');
        } else { // = 1 or not set
            $weightDisplay = US_weight($report['weight'], $mode) . " (" . $convValue . " " . xl('kg') . ")";
        }
    } else {
        $weightDisplay = 'NM';
    }
    $WHOweightCentile = xlt($report["Weight %ile WHO"]) ?? 'x';
    $CDCweightCentile = xlt($report["Weight %ile CDC"]) ?? 'x';
    $WHOweightZscore = xlt($report["Weight Zscore WHO"]) ?? 'x';


    if (is_numeric($report['height']) && $report['height'] > 0) {
        $heightDisplay = displayLengthString($report['height']);

      //End of height
    } else {
        $heightDisplay = 'NM';
    }
    $WHOheightCentile = xlt($report['Height %ile WHO']) ?? 'x';
    $CDCheightCentile = xlt($report['Height %ile CDC']) ?? 'x';
    $WHOheightZscore = xlt($report['Height Zscore WHO']) ?? 'x';

    //Head Circumference
    if (is_numeric($report['head_circ']) && $report['head_circ'] > 0) {
        $hcDisplay = displayLengthString($report['head_circ']);
    } else {
        $hcDisplay = 'NM';
    }
    $WHOhcCentile = xlt($report['Head Circ %ile WHO']) ?? 'x';
    $CDChcCentile = xlt($report['Head Circ %ile CDC']) ?? 'x';
    $WHOhcZscore = xlt($report['Head Circ Zscore WHO']) ?? 'x';

    //BMI
    if (is_numeric($report['BMI'])) {
        $bmiDisplay = xlt($report['BMI'])  . ' kg/m^2';
        if ($age_in_months > 240.5) {
            $bmiStatus = GrowthCharts::bmiAdultStatus($report['BMI']);
        } else {
            $bmiStatus = xlt($report['BMI Status']) ?? 'x';
        }
    } else {
        $bmiDisplay = 'NM';
    }
    $WHObmiCentiles = xlt($report['BMI %ile WHO']) ?? 'x';
    $CDCbmiCentiles = xlt($report['BMI %ile CDC']) ?? 'x';
    $WHObmiZscore = xlt($report['BMI Zscore WHO']) ?? 'x';



    //todo: temp location
    if (is_numeric($report['temperature']) && $report['temperature'] > 0) {
        $tempDisplay = xlt($report['temperature']) . '&#8457; \ ' .
            number_format((($report['temperature'] - 32) * 0.5556), 2) . '&#x2103;';
        $tempMethod = $report['temp_method'] ?? 'n/a';
    } else {
        $tempDisplay = 'NM';
    }

    if (isset($report['pulse']) && $report['pulse'] > 0) {
        $pulseDisplay = xlt($report['pulse']) . xlt(" per min");
    } else {
        $pulseDisplay = 'NM';
    }

    if (isset($report['bps']) || isset($report['bpd'])) {
        if (isset($report['bps']) && isset($report['bpd'])) {
            $bpdisplay = $report['bps'] . '/' . $report['bpd'];
        } else {
            $bpdisplay = xlt("missing or incomplete data");
        }
    } else {
        $bpdisplay = 'NM';
    }

    $htmlcss = "
    <style>
    .details_frag {
        display: grid;
        grid-template-columns:repeat(5, 80px) 140px;
        column-gap: 1px;
        margin-top: 10px;
        margin-left: 10px;
        margin-right: 10px;
    }

    .measurement_frag {
        display: grid;
        grid-template-columns:110px 105px 95px 95px 95px;;
        column-gap: 1px;
        row-gap: 5px;
        margin-bottom: 5px;
        margin-left: 10px;
        margin-right: 10px;
    }

    .data_frag {
        display: grid;
        grid-template-columns:110px 105px 95px 95px 95px;;
        column-gap: 1px;
        row-gap: 5px;
        margin-left: 10px;
        margin-right: 10px;
    }

    .vitals_frag {
        display: grid;
        grid-template-columns:110px 105px 95px 95px 95px;;;
        column-gap: 1px;
        row-gap: 5px;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 5px;
    }

    .measurements_frag {
        display: grid;
        grid-template-columns:110px 105px 95px 95px 95px;;;
        column-gap: 1px;
        row-gap: 5px;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 10px;
    }

    .note_frag {
        display: grid;
        margin-top: 10px;
        margin-left: 10px;
        margin-right: 10px;

    }

    .note {
        text-align:left;
        font-size:8pt;

    }
    .label-vital-report {
        text-align:left;
        grid-template-columns:120px repeat(2, 90px) 100px;
        grid-row:1;
        font-weight: bold;
        font-size:10pt;

    }
      .data-label {
        text-align:left;
        grid-template-columns:120px repeat(2, 90px) 100px;
        grid-row:1;
        font-weight: bold;
        font-size:9pt;

    }

    .label-1 {
        text-align:left;
        font-weight: bold;
        font-size:8pt;

    }

    #label-wht4height {
        text-align:left;
        font-weight: bold;
        grid-column-start:1;
        grid-column-end:3;
    }

    .data-1 {
        text-align:left;
        font-size:8pt;
    }

    .data-note {
        text-align:left;
        font-size:8pt;
        font-style: oblique;
        margin-top: 10px;

    }

    .vital-frag-header-title {
        text-align:left;
        font-weight: bold;
        font-size:9pt;
    }
      .vital-frag-header-data {
        text-align:left;
        font-size:9pt;
    }
    .header-data {
        text-align:left;
        font-size:8pt;

    }

    .vf {
        display: grid;
        grid-template-columns: repeat(6, 140px);
        grid-auto-rows: minmax(100px, auto);
    }

</style>";

    //Other vitals such as 02 sat, Repiration, Waist Circumference,

    //Vitals Header (underlinded "Vitals")
    $vitalsHeader = "
    <div class='vitals_frag' ><!--outer div-->
    <div class = 'label-vital-report'><u> " . xlt('Vitals') . "</u></div>
    <div class = 'label-vital-report'> " . xlt('Temperature') . "</div>
    <div class = 'label-vital-report'> " . xlt('Pulse') . "</div>
    <div class = 'label-vital-report'> " . xlt('BP') . "</div>
    </div>
    ";

    $vitalsData = "
    <div class='data_frag' >
    <div class = 'data-label'>Reading</div>
    <div class = 'data-1'> " . $tempDisplay . "</div>
    <div class = 'data-1'> " . $pulseDisplay . "</div>
    <div class = 'data-1'> " . $bpdisplay . "</div>
    <div class = 'data-1'></div>
    </div>

    ";


    //Measurement Header (underlined "Measurement")
    $measurementsHeader = "
    <div class='measurements_frag' ><!--outer div-->
    <div class = 'label-vital-report'><u> " . xlt('Measurements') . "</u></div>
    <div class = 'label-vital-report'> " . xlt('Weight') . "</div>
    <div class = 'label-vital-report'> " . xlt('Height') . "</div>
    <div class = 'label-vital-report peds' hidden> " . xlt('Head Cir') . "</div >
    <div class = 'label-vital-report'> " . xlt('BMI') . "</div>
    </div>
    ";

    //we hide measurements related to CDC and WHO unless the patient is under 20 years old
    $measurementsData = "
    <div class='data_frag' ><!--outer div-->
    <div class = 'data-label'>Measurement</div>
    <div id = 'weight' class = 'data-1'> " . xlt($weightDisplay) . "</div>
    <div id = 'height' class = 'data-1'> " . xlt($heightDisplay) . "</div>
    <div id = 'hc' class = 'data-1 peds' hidden> " . xlt($hcDisplay) . "</div >
    <div id = 'bmi' class = 'data-1'> " . xlt($bmiDisplay) . "</div>
    </div>

     <div class='data_frag peds' hidden><!--outer div-->
    <div class = 'data-label'>WHO %tile</div>
    <div id= 'weightWHOcentile' class = 'data-1 peds' hidden> " . xlt($WHOweightCentile) . "</div >
    <div id= 'heightWHOcentile' class = 'data-1 peds' hidden> " . xlt($WHOheightCentile) . "</div >
    <div id= 'hcWHOcentile'     class = 'data-1 peds' hidden> " . xlt($WHOhcCentile) . "</div >
    <div id= 'bmiWHOcentile'    class = 'data-1 peds' hidden> " . xlt($WHObmiCentiles) . "</div >
    </div>

     <div class='data_frag' ><!--outer div-->
    <div class = 'data-label peds' hidden>CDC %tile</div>
    <div id= 'weightCDCcentile' class = 'data-1 peds' hidden> " . xlt($CDCweightCentile) . "</div >
    <div id= 'heightCDCcentile' class = 'data-1 peds' hidden> " . xlt($CDCheightCentile) . "</div >
    <div id= 'hcCDCcentile'     class = 'data-1 peds' hidden> " . xlt($CDChcCentile) . "</div >
    <div id= 'bmiCDCcentile'    class = 'data-1 peds' hidden> " . xlt($CDCbmiCentiles) . "</div >
    </div>

    <div class='data_frag' ><!--outer div-->
    <div class = 'data-label'>Status</div>
    <div class = 'data-1'></div>
    <div class = 'data-1'></div>
    <div class = 'data-1 peds' hidden></div>
    <div class = 'data-1'> " . xlt($bmiStatus) . "</div>
    </div>
    ";

    //We need to grab the other measurements, planning for new ones to be added in the future.
    //At this time we are looking for wasitcircum, o2 sat, respiration

    $otherMeasurements = "
      <div class='measurements_frag' ><!--outer div-->
    <div class = 'label-vital-report'><u> " . xlt('Others') . "</u></div>
    <div class = 'label-vital-report'></div>
    <div class = 'label-vital-report'></div>
    <div class = 'label-vital-report'></div>
    </div>
    ";

    //here we grab all the measurements that we have not added.
    foreach ($report as $item => $obv) {
        if (
            $item == "id" ||
            $item == "uuid" ||
            $item == "date" ||
            $item == "pid" ||
            $item == "user" ||
            $item == "groupname" ||
            $item == "authorized" ||
            $item == "activity" ||
            $item == "bps" ||
            $item == "bpd" ||
            $item == "weight" ||
            $item == "height" ||
            $item == "temperature" ||
            $item == "temp_method" ||
            $item == "note" ||
            str_contains($item, "BMI") ||
            $item == "head_circ" ||
            str_contains($item, "%ile") ||
            str_contains($item, "Zscore") ||
            str_contains($item, "pulse") ||
            str_contains($item, "external_id") ||
            str_contains($item, "Age in Days") ||
            str_contains($item, "Age:") ||
            $obv == 0.0
        ) {
            continue;
        }
        if ($item == 'inhaled_oxygen_concentration') {
            $obv .= " %";
        }
        $item = str_replace('oxygen_saturation', 'O2 Sat', $item);
        $item = str_replace('_', ' ', $item);
        $otherMeasurements .= "
      <div class='data_frag' ><!--outer div-->
        <div class = 'data-label'> " . xlt($item) . "</div>
        <div class = 'data-1'> " . xlt($obv) . "</div>
        <div class = 'data-1'></div>
        <div class = 'data-1'></div>
      </div>
    ";
    }



    $noteHeader = "
    <div class='note_frag' >";
    $display = xlt($report['note']);
    $note = "
        <div class='label-vital-report'><u> " . xlt('Notes') . "</u></div>
        <div class='data-1'>" . xlt($display);
    if ($age_in_months < 24 && !$hasDS) {
        $note .= xlt("The CDC recommends using datasets from the WHO for children under 24 months") . "</div>";
    }

    if ($hasDS) {
        $note .= xlt("For patients with DS, please refer to the growth charts provided by the CDC.  Datasets
                        that have exact percentiles are not available from either WHO or CDC.");
    }


    if ($age_in_months >= 24 && $age_in_months < 60) {
        $note .=  xlt("OpenEMR provides percentile data from both CDC and the WHO
                    for children between 2 years old and 5 years old.  Growth charts from the CDC are only available,
                    but not raw data regarding percentiles. ")
            .  xlt("The WHO uses increments of days for extended growth
                    charts, the CDC uses increments of 6 months. This accounts for the difference between %tiles");
    }
    $note .= "</div>";
    $dateOfVitals = substr($report['date'], 0, 10);
    $ageinYMD = xlt($report['Age: ']);
    $user = xlt($report['user']);
    $detailHeader = "
    <div class='details_frag'>";
    $details = "
        <div class='vital-frag-header-title'>" . xlt("Date Taken: ") . "</div><div class='vital-frag-header-data'>" . xlt($dateOfVitals) . "</div>
        <div class='vital-frag-header-title'>" . xlt("Age Taken: ") .  "</div><div class='vital-frag-header-data'>" . xlt($ageinYMD) . "</div>
        <div class='vital-frag-header-title'>" . xlt("Taken by: ") .   "</div><div class='vital-frag-header-data'>" . xlt($user) . "</div>
    </div>

    ";



    //Display of vitals.
    //As long as each header has a div to close it, the programmer can change the order of things with ease.
    $html = $htmlcss;//CSS must always come first.
    $html .= $vitalsHeader . $vitalsData . $measurementsHeader . $measurementsData . $otherMeasurements;
    //Notes:  displays the note specified when recording vitals, explains cdc and who display
    //for patients under 2 CDC recommends using WHO data, but WHO provides info for patients up to 5.
    //The WHO does not have data for patients with DS, but the CDC provides charts, not data.
    $html .= $noteHeader;
    $html .= $note;
    $html .= "</div>"; //last div for note


    //we place any new measurements here.

    // Date, age, and user displayed.  This is especially helpful when entering historic data in a single encounter
    $html .= $detailHeader;
    $html .= $details;
    $html .= "</div>"; // last div for details.

    //If the patient is over 20 years old we hide the measurements that refer to CDC or WHO.  We also do not
    //need measurements like head circumference.
    if ($age_in_months <= 240.5) {
        $html = str_replace("peds' hidden>", "peds'>", $html);
    }



    if ($print) {
        echo $html;
    } else {
        return $html;
    }
    return true;
}
