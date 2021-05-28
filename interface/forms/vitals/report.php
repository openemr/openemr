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
    $lengthInMetric = round(number_format($value * 2.54, 2), 1);
    return $value . ' in' . ' \ ' . $lengthInMetric . ' cm';
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

//  These are placeholders for when the CDC of WHO haas datasets for patients with DS.  We need raw data, not just the
    //datasheet.
//    //currently data is unavailable
//    if ($age_in_months < 24 && $hasDS) {
//        //grab the CDC info
//
//    }
//
//    //currently data is unavailable
//    if ($age_in_months >=24 && $hasDS) {
//        //grab the CDC info
//
//    }



    $report = $data;
    $htmlcss = "
    <style>
    #header_frag {
        display: grid;
        grid-template-columns:140px 120px;
        column-gap: 10px;
        row-gap: 5px;
        margin-top: 20px;
        margin-left: 10px;
        margin-right: 10px;
    }

    #measurement_frag {
        display: grid;
        grid-template-columns:120px 150px 100px 100px ;
        column-gap: 10px;
        row-gap: 5px;
        margin-top: 3px;
        margin-left: 10px;
        margin-right: 10px;
    }

    .measurement-2_frag {
        display: grid;
        grid-template-columns:120px 150px 120px 80px;
        column-gap: 10px;
        row-gap: 5px;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 20px;

    }

    .note_frag {
        display: grid;
        margin-top: 20px;
        margin-left: 10px;
        margin-right: 10px;

    }

    .note {
        text-align:left;
        font-size:10pt;

    }
    .label {
        text-align:left;
        grid-template-columns:120px repeat(2, 90px) 100px;
        grid-row:1;
        font-weight: bold;
        font-size:11pt;

    }

    .label-1 {
        text-align:left;
        font-weight: bold;
        font-size:10pt;

    }

    #label-wht4height {
        text-align:left;
        font-weight: bold;
        grid-column-start:1;
        grid-column-end:3;
    }

    .data-1 {
        text-align:left;
        font-size:10pt;
    }

    .data-note {
        text-align:left;
        font-size:10pt;
        font-style: oblique;
        margin-top: 20px;

    }

    .header-title {
        grid-column: 1;
        text-align:left;
        font-weight: bold;
        font-size:11pt;

    }

    .header-data {
        grid-column: 2;
        text-align:left;
        font-size:10pt;

    }

    .vf {
        display: grid;
        grid-template-columns: repeat(6, 140px);
        grid-auto-rows: minmax(100px, auto);
    }

</style>";

    $htmlMeasurements = "
    <div id='measurement_frag' ><!--outer div-->
    <div class = 'label'> " . xlt('Measurement') . "</div>
    <div class = 'label'> " . xlt('Value') . "</div>
    <div class = 'label'> " . xlt('WHO %tile') . "</div>
    <div class = 'label'> " . xlt('CEC $tile') . "</div>
<!--    Some practices find Zscore helpful, others not so much-->
    <div class = 'label zscore'>WHO Z-score</div>
  ";

    //start with the weight widget
    if (is_numeric($report['weight'])) {
        $weightInPounds = $report['weight'];
        $weightInMetric = number_format($weightInPounds * 0.45359237, 2);
        $display = $weightInPounds . " lb \\ " . $weightInMetric . " kg";
        $WHOweightPcentile = xlt($report["Weight %ile WHO"]) ?? 'x';
        $CDCweightPcentile = xlt($report["Weight %ile CDC"]) ?? 'x';
        $WHOweightZscore = xlt($report["Weight Zscore WHO"]) ?? 'x';
        $weight = "
        <div class='label-1' > " . xlt('Weight') . "</div >
        <div class='data-1' >$display</div >
        <div class='data-1' >$WHOweightPcentile</div >
        <div class='data-1' >$CDCweightPcentile</div>
        <div class='data-1 zscore' >$WHOweightZscore</div>
    "; //End of weight
    } else {
        $weight = '';
    }


    if (is_numeric($report['height'])) {
        $display = displayLengthString($report['height']);
        $WHOheightCentile = xlt($report['Height %ile WHO']) ?? 'x';
        $CDCheightCentile = xlt($report['Height %ile CDC']) ?? 'x';
        $WHOheightZscore = xlt($report['Height Zscore WHO']) ?? 'x';
        $height = "

        <div class='label-1' > " . xlt('Height') . "</div >
        <div class='data-1' >{$display}</div >
        <div class='data-1' >{$WHOheightCentile}</div >
        <div class='data-1' >{$CDCheightCentile}</div>
        <div class='data-1 zscore' >{$WHOheightZscore}</div>
        "; //End of height
    } else {
        $height = '';
    }

    if (is_numeric($report['head_circ'])) {
        $display = displayLengthString($report['head_circ']);
        $WHOhcCentile = xlt($report['Head Circ %ile WHO']) ?? 'x';
        $CDChcCentile = xlt($report['Head Circ %ile CDC']) ?? 'x';
        $WHOhcZscore = xlt($report['Head Circ Zscore WHO']) ?? 'x';

        $hc = "
        <div class='label-1' > " . xlt('Head Cir') . "</div >
        <div class='data-1' >{$display}</div >
        <div class='data-1' >{$WHOhcCentile}</div >
        <div class='data-1' >{$CDChcCentile}</div>
        <div class='data-1 zscore' >{$WHOhcZscore}</div>
        ";//end of head circumference
    } else {
        $hc = '';
    }

    if (is_numeric($report['BMI'])) {
        $display = xlt($report['BMI'])  . ' kg/	&#13217;';
        $WHObmiCentiles = xlt($report['BMI %ile WHO']) ?? 'x';
        $CDCbmiCentiles = xlt($report['BMI %ile CDC']) ?? 'x';
        $WHObmiZscore = xlt($report['BMI Zscore WHO']) ?? 'x';
        $bmi = "
        <div class='label-1' > " . xlt('BMI') . "</div >
        <div class='data-1' >{$display}</div >
        <div class='data-1' >{$WHObmiCentiles}</div >
        <div class='data-1' >{$CDCbmiCentiles}</div>
        <div class='data-1 zscore' >{$WHObmiZscore}</div>
        ";
    } else {
        $bmi = '';
    }

    if (isset($report['BMI_status'])) {
        $display = xlt($report['BMI_status']) ?? 'x';
        $bmiStatus = "
        <div class = 'label-1'> " . xlt('BMI Status') . "</div>
        <div class = 'data-1'>{$display}</div>
        ";
    } else {
        $bmiStatus = '';
    }

    $vitalsHeader = "
    <div class='measurement-2_frag' ><!--outer div-->
    <div class = 'label'><u> " . xlt('Vitals') . "</u></div>
    <div class = 'label'></div>
    <div class = 'label'></div>
    <div class = 'label'></div>
    ";

    if (is_numeric($report['temperature']) && $report['temperature'] > 0) {
        $display = xlt($report['temperature']) . '&#8457; \ ' .
            number_format((($report['temperature'] - 32) * 0.5556), 2) . '&#x2103;';
        $temperature = "
        <div class='label-1' > " . xlt('Temperature') . "</div>
        <div class='data-1' >{$display}</div>
        <div class='data-1' ></div>
        <div class='data-1'></div>



    ";
    } else {
        $temperature = '';
    }

    if (isset($report['pulse']) && $report['pulse'] > 0) {
        $display = xlt($report['pulse']) . xlt(" per min");
        $pulse = "
        <div class='label-1'> " . xlt('Pulse') . "</div >
        <div class='data-1'>{$display}</div >
        <div class='data-1'></div >
        <div class='data-1'></div>
        ";
    } else {
        $pulse = '';
    }

    if (isset($report['bps']) || isset($report['bpd'])) {
        if (isset($report['bps']) && isset($report['bpd'])) {
            $display = $report['bps'] . '/' . $report['bpd'];
        } else {
            $display = xlt("missing or incomplete data");
        }
        $bp = "

        <div class='label-1'> " . xlt('BP') . "</div >
        <div class='data-1'>{$display}</div >
        <div class='data-1'></div >
        <div class='data-1'></div>
        ";
    } else {
        $bp = '';
    }

    //O2 has its own frag because it's not really part of vitals.
    $o2Header = "
    <div class='measurement-2_frag' ><!--outer div-->";

    if (is_numeric($report['oxygen_saturation']) && $report['oxygen_saturation'] > 0) {
        $display = xlt($report['oxygen_saturation']) . "%";

        $o2sat = "
        <div class='label-1' > " . xlt('O2 Sat') . "</div >
            <div class='data-1' >{$display}</div>
            <div></div>
            <div></div>

         ";//end of $02Sat
    }

    //Here we group together the additional measurements that are added

//    $additionalFields = array();
//    //Since groups of measurements are usually expected to be grouped
//    foreach ($data as $key => $value) {
//        if (
//            $key == "id" || $key == "pid" ||
//            $key == "user" || $key == "groupname" ||
//            $key == "authorized" || $key == "activity" ||
//            $key == "date" || $value == "" ||
//            $value == "0000-00-00 00:00:00" || $value == "0.0" ||
//            $key == "weight" || $key == "height" || $key == "temperature" ||
//            $key == "BMI_status" || $key == "head_circ" ||
//            $key == "Age: " || $key == "Age in Days: " || $key == "BMI" ||
//            $key ==  "Height %ile WHO" || $key == "Weight %ile WHO" ||
//            $key == "Weight Zscore WHO" || $key == "Weight for Height %ile WHO" || $key == "Head Circ %ile WHO" ||
//            $key == "Head Circ Zscore WHO" || $key == "BMI %ile WHO" || $key == "BMI Zscore WHO" ||
//            $key == "Height %ile CDC" || $key == "Weight %ile CDC" || $key == "Weight for Height %ile CDC"
//        ) {
//            // skip certain data
//            continue;
//
//        } else {
//            $additionalFields[$key] = $value;
//
//        }
//    }




    $noteHeader = "
    <div class='note_frag' >";
    $display = xlt($report['note']);
    $note = "
        <div class='label-1'> " . xlt('Notes') . "</div>
        <div class='data-1'>{$display}</div>";
    if ($age_in_months < 24 && !$hasDS) {
        $note .= "<div  class='data-note'> " .
            xlt("The CDC recommends using datasets from the WHO for children under 24 months") . "</div>";
    }

    if ($hasDS) {
        $note .= "<div  class='data-note'> " .
            xlt("For patients with DS, please refer to the growth charts provided by the CDC.  Datasets
                        that have exact percentiles are not available from either WHO or CDC.") . "</div>";
    }


    if ($age_in_months >= 24 && $age_in_months < 60) {
        $note .= "<div  class='data-note'>" . xlt("OpenEMR provides percentile data from both CDC and the WHO
                    for children between 2 years old and 5 years old.  Growth charts from the CDC are only available. ")
                    . "</div> <div class = 'data-note'>" . xlt("The WHO uses increments of days for extended growth
                    charts, the CDC uses increments of 6 months. This accounts for the difference between %tiles") .
                    "</div>";
    }

    $dateOfVitals = substr($report['date'], 0, 10);
    $ageinYMD = xlt($report['Age: ']);
    $user = xlt($report['user']);
    $detailHeader = "
    <div id='header_frag'>";
    $details = "
        <div class='header-title'>" . xlt("Date Taken: ") . "</div><div class='header-data'>{$dateOfVitals}</div>
        <div class='header-title'>" . xlt("Age Taken: ") .  "</div>  <div class='header-data'>{$ageinYMD}</div>
        <div class='header-title'>" . xlt("Taken by: ") .   "</div>  <div class='header-data'>{$user}</div>
        </div>

    ";


    //Display of vitals.
    //As long as each header has a div to close it, the programmer can change the order of things with ease.
    $html = $htmlcss . $htmlMeasurements; //Measurement header
    $html .= $weight ?? '';
    $html .= $height ?? '';
    $html .= $hc ?? '';
    $html .= $bmi ?? '';
    $html .= $bmiStatus ?? '';
    $html .= "</div>"; //last div for measurements

    //Vitals - temperature, pulse, blood pressure.
    if ($temperature !== "" || $pulse !== "" || $bp !== "") {
        $html .= $vitalsHeader;
        $html .= $temperature ?? '';
        $html .= $pulse ?? '';
        $html .= $bp ?? '';
        $html .= "</div>";//last div for Vitals
    }

    //O2 saturation.  Not technically a vital so separated in the layout display
    $html .= $o2Header;
    $html .= $o2sat ?? '';
    $html .= "</div>"; //last div for 02.

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

    if ($print) {
        echo $html;
    } else {
        return $html;
    }
    return true;
}
