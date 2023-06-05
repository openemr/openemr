<?php

/**
 * vitals growthchart chart.php
 *
 * Description
 *  Script can create growth charts for following formats:
 *    1)png image
 *    2)pdf
 *    3)html (css for printing)
 *
 * The png image and pdf require following files in current directory:
 *  2-20yo_boys_BMI.png
 *  2-20yo_girls_BMI.png
 *  birth-24mos_boys_HC.png
 *  birth-24mos_girls_HC.png
 *
 * The html (css for printing) require the following files in current directory
 *  2-20yo_boys_BMI-1.png
 *  2-20yo_boys_BMI-2.png
 *  2-20yo_girls_BMI-1.png
 *  2-20yo_girls_BMI-2.png
 *  birth-24mos_boys_HC-1.png
 *  birth-24mos_boys_HC-2.png
 *  birth-24mos_girls_HC-1.png
 *  birth-24mos_girls_HC-2.png
 *  bluedot.gif
 *  redbox.gif
 *  reddot.gif
 *  chart.php
 *  page1.css
 *  page2.css
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../../interface/globals.php");
require_once($GLOBALS['fileroot'] . "/library/patient.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\VitalsService;


if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$chartpath = $GLOBALS['fileroot'] . "/interface/forms/vitals/growthchart/";
$name = "";
$pid = $_GET['pid'];

if ($pid == "") {
    // no pid? no graph for you.
    echo "<p>" . xlt('Missing PID.') . ' ' .  xlt('Please close this window.') . "</p>";
    exit;
}

$vitalsService = new VitalsService();

$isMetric = ((($GLOBALS['units_of_measurement'] == 2) || ($GLOBALS['units_of_measurement'] == 4)) ? true : false);

$patient_data = "";
if (isset($pid) && is_numeric($pid)) {
    $patient_data = getPatientData($pid, "fname, lname, sex, DATE_FORMAT(DOB,'%Y%m%d') as DOB");
    $nowAge = getPatientAge($patient_data['DOB']);
    $dob = $patient_data['DOB'];
    $name = $patient_data['fname'] . " " . $patient_data['lname'];
}

// The first data point in the DATA set is significant. It tells the date
// of the currently viewed vitals by the user. We will use this
// date to define which chart is displayed on the screen
$charttype = "2-20"; // default the chart-type to ages 2-20
$datapoints = $vitalsService->getVitalsHistoryForPatient($pid, true);
$first_datapoint = $datapoints[0];
if (!empty($first_datapoint)) {
    $date = str_replace('-', '', substr($first_datapoint['date'], 0, 10));
    $height = (($isMetric) ? convertHeightToUs($first_datapoint['height']) : $first_datapoint['height']);
    $weight = (($isMetric) ? convertWeightToUS($first_datapoint['weight']) : $first_datapoint['weight']);
    $head_circ = $first_datapoint['head_circ'];
    if ($date != "") {
        $charttype_date = $date;
    }

    $tmpAge = getPatientAgeInDays($patient_data['DOB'], $date);
    // use the birth-24 chart if the age-on-date-of-vitals is 24months or younger
    if ($tmpAge < (365 * 2)) {
        $charttype = "birth";
    }
}

if (isset($_GET['chart_type'])) {
    $charttype = $_GET['chart_type'];
}

//sort the datapoints
rsort($datapoints);


// convert to applicable weight units from Config Locale
function unitsWt($wt)
{
    global $isMetric;
    if ($isMetric) {
        //convert to metric
        return (number_format(($wt * 0.45359237), 2, '.', '') . xl('kg', '', ' '));
    } else {
    //keep US
        return number_format($wt, 2) . xl('lb', '', ' ');
    }
}

// convert to applicable length units from Config Locale
function unitsDist($dist)
{
    global $isMetric;
    if ($isMetric) {
        //convert to metric
        return (number_format(($dist * 2.54), 2, '.', '') . xl('cm', '', ' '));
    } else {
        //keep US
        return number_format($dist, 2)  . xl('in', '', ' ');
    }
}

// convert vitals service data to US values for graphing
function convertHeightToUs($height)
{
    return $height * 0.393701;
}

function convertWeightToUs($weight)
{
    return $weight * 2.20462262185;
}
/******************************/
/******************************/
/******************************/


$name_x = 650;
$name_y = 50;
$name_x1 = 1650;
$name_y1 = 60;

$ageOffset = 0;
$heightOffset = 0;
$weightOffset = 0;

if ($charttype == 'birth') {
    // Use birth to 24 months chart

    $dot_x = 190;         //months starts here (pixel)
    $delta_x = 26.13;     //pixels per month  - length
    $dot_y1 = 768;        //height starts here - at 15 inches
    $delta_y1 = 24.92;    //pixels per inch  - height
    $dot_y2 = 1170;       //weight starts here - at 3 lbs
    $delta_y2 = 22.16;    //pixels per pound - weight

    $HC_dot_x = 1180;     //months starts here for Head circumference chart
    $HC_delta_x = 26.04;  //pixels per month for Head circumference chart
    $HC_dot_y =  764;     //Head circumference starts here - at 11 inches
    $HC_delta_y = 60.00;  //calculated pixels per inch for head circumference

    $WT_y = 1127; //start here to draw wt and height graph at bottom of Head circumference chart
    $WT_delta_y = 12.96;
    $HT_x = 1187; //start here to draw wt and height graph at bottom of Head circumference chart
    $HT_delta_x = 24.32;

    if (preg_match('/^male/i', $patient_data['sex'])) {
        $chart = "birth-24mos_boys_HC.png";

        // added by BM for CSS html output
        $chartCss1 = "birth-24mos_boys_HC-1.png";
        $chartCss2 = "birth-24mos_boys_HC-2.png";
    } elseif (preg_match('/^female/i', $patient_data['sex'])) {
        $chart = "birth-24mos_girls_HC.png";

        // added by BM for CSS html output
        $chartCss1 = "birth-24mos_girls_HC-1.png";
        $chartCss2 = "birth-24mos_girls_HC-2.png";
    }

    $ageOffset = 0;
    $heightOffset = 15; // Substract 15 because the graph starts at 15 inches
    $weightOffset = 3;  // graph starts at 3 lbs
    $WToffset = 0; //for wt and ht table at bottom half of HC graph
    $HToffset = 18; // starting inch for wt and ht table at bottom half of HC graph

    // pixel positions and offsets for data table
    $datatable_x = 370;
    $datatable_age_offset = 75;
    $datatable_weight_offset = 145;
    $datatable_height_offset = 220;
    $datatable_hc_offset = 300;
    $datatable_y = 1052;
    $datatable_y_increment = 17;

    // pixel positions and offsets for head-circ data table
    $datatable2_x = 1360;
    $datatable2_age_offset = 75;
    $datatable2_weight_offset = 145;
    $datatable2_height_offset = 210;
    $datatable2_hc_offset = 290;
    $datatable2_y = 1098;
    $datatable2_y_increment = 18;
} elseif ($charttype == "2-20") {
    // current patient age between 2 and 20 years

    $dot_x = 177;
    $delta_x = 35.17;
    $dot_y1 = 945;
    $delta_y1 = 16.74;
    $dot_y2 = 1176; //at 14 lbs,  1157 at 20 lbs
    $delta_y2 = 3.01;

    $bmi_dot_x = 1135;
    $bmi_delta_x = 39.89;
    $bmi_dot_y = 1130;
    $bmi_delta_y = 37.15;

    if (preg_match('/^male/i', $patient_data['sex'])) {
        $chart = "2-20yo_boys_BMI.png";

        // added by BM for CSS html output
        $chartCss1 = "2-20yo_boys_BMI-1.png";
        $chartCss2 = "2-20yo_boys_BMI-2.png";
    } elseif (preg_match('/^female/i', $patient_data['sex'])) {
        $chart = "2-20yo_girls_BMI.png";

        // added by BM for CSS html output
        $chartCss1 = "2-20yo_girls_BMI-1.png";
        $chartCss2 = "2-20yo_girls_BMI-2.png";
    }

    $ageOffset = 2;
    $heightOffset = 30;
    $weightOffset = 14;

    // pixel positions and offsets data table
    $datatable_x = 96;
    $datatable_age_offset = 84;
    $datatable_weight_offset = 180;
    $datatable_height_offset = 270;
    $datatable_bmi_offset = 360;
    $datatable_y = 188;
    $datatable_y_increment = 18;

    // pixel positions and offsets for BMI data table
    $datatable2_x = 1071;
    $datatable2_age_offset = 73;
    $datatable2_weight_offset = 145;
    $datatable2_height_offset = 215;
    $datatable2_bmi_offset = 310;
    $datatable2_y = 152;
    $datatable2_y_increment = 17;
} else {
    // bad age data? no graph for you.
    echo "<p>" . xlt('Age data is out of range.') . "</p>";
    exit;
}

/******************************/
// Section for the CSS HTML table
//  this will bypass gd and pdf requirements
//  to allow internationalization and flexibility
//  with fonts

$cssWidth = 524;
$cssHeight = 668;

function cssHeader()
{
    global $cssWidth, $cssHeight;

    ?>
    <html>
    <head>
    <link rel="stylesheet" title="page1" href="page1.css">
    <link rel="stylesheet" title="page2" href="page2.css">
    <style>
        html {
            padding: 0;
            margin: 0;
        }
        body {
            font-family: sans-serif;
            font-weight: normal;
            font-size: 10pt;
            background: white;
            color: red;
            margin: 0;
            padding: 0;
        }
        div {
            padding: 0;
            margin: 0;
        }
        div.paddingdiv {
            width: <?php echo attr($cssWidth); ?>pt;
            height: <?php echo attr($cssHeight); ?>pt;
            page-break-after: always;
        }
        div.label_custom {
            font-size: 7pt;
        }
    div.DoNotPrint {
        position: absolute;
        top: 2pt;
        left: 200pt;
    }
        img {
            margin: 0;
            padding: 0;
        }
        img.background {
            width: <?php echo attr($cssWidth); ?>pt;
            height: <?php echo attr($cssHeight); ?>pt;
        }
        @media print {
        div.DoNotPrint {
            display: none;
        }
    }
    </style>
    <script>
        function FormSetup()    {
        changeStyle('page1')
    }
    function changeStyle(css_title) {
        var i, link_tag ;
        for (i = 0, link_tag = document.getElementsByTagName("link") ; i < link_tag.length ; i++ ) {
            if ((link_tag[i].rel.indexOf( "stylesheet" ) != -1) && link_tag[i].title) {
            link_tag[i].disabled = true ;
                if (link_tag[i].title == css_title) {
                    link_tag[i].disabled = false ;
                }
            }
        }
    }
  function pagePrint(title) {
    changeStyle(title);
    var win = top.printLogPrint ? top : opener.top;
    win.printLogPrint(window);
  }
    </script>
    <title><?php echo xlt('Growth Chart'); ?></title>
    </head>
    <body Onload="FormSetup()">
    <div class="DoNotPrint">
        <table>
            <tr>
                <td>
                    <input type="button" value="<?php echo xla('View Page 1'); ?>" onclick="javascript:changeStyle('page1')" class="button">
                    <input type="button" value="<?php echo xla('View Page 2'); ?>" onclick="javascript:changeStyle('page2')" class="button">
                    <input type="button" value="<?php echo xla('Print Page 1'); ?>" onclick="javascript:pagePrint('page1')" class="button">
                    <input type="button" value="<?php echo xla('Print Page 2'); ?>" onclick="javascript:pagePrint('page2')" class="button">
                </td>
            </tr>
        </table>
    </div>
    <?php
}

function cssFooter()
{
    ?>
    </body>
    </html>
    <?php
}

function cssPage($image1, $image2)
{
    ?>
    <div class='paddingdiv' id='page1'>
        <img class='background' src='<?php echo $image1; ?>' />
    </div>
    <div class='paddingdiv' id='page2'>
    <img class='background' src='<?php echo $image2; ?>' />
    </div>
    <?php
}

// Convert a point from above settings for gd into a
// a format (pt) to use for css html document
//  return - Array(Xcoord,Ycoord,page)
function convertpoint($coord)
{
    global $cssWidth, $cssHeight;

    //manual offsets to help line up output
    $Xoffset = -3;
    $Yoffset = -2;

    //collect coordinates from input array
    $Xcoord = $coord[0];
    $Ycoord = $coord[1];

    //adjust with offsets
    $Xcoord = $Xcoord + $Xoffset;
    $Ycoord = $Ycoord + $Yoffset;


    if ($Xcoord > 1000) {
        //on second page so subtract 1000 from x
        $Xcoord = $Xcoord - 1000;
        $page = "page2";
    } else {
        $page = "page1";
    }

    //calculate conversion to pt (decrease number of decimals)
    $Xcoord = ($cssWidth * $Xcoord) / 1000;
    $Xcoord = number_format($Xcoord, 1, '.', '');
    $Ycoord = ($cssHeight * $Ycoord) / 1294;
    $Ycoord = number_format($Ycoord, 1, '.', '');

    //return point
    return(array($Xcoord,$Ycoord,$page));
}


if (($_GET['html'] ?? null) == 1) {
    //build the html css page if selected
    cssHeader();
    cssPage($chartCss1, $chartCss2);

    //output name
    $point = convertpoint(array($name_x, $name_y));
    echo("<div id='" . attr($point[2]) . "' class='name' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($name)  . "</div>\n");
    $point = convertpoint(array($name_x1,$name_y1));
    echo("<div id='" . attr($point[2]) . "' class='name' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($name)  . "</div>\n");

    // counter to limit the number of data points plotted
    $count = 0;

    // plot the data points
    foreach ($datapoints as $data) {
        if (!empty($data)) {
            $date = str_replace('-', '', substr($data['date'], 0, 10));
            // convert to US if metric locale
            $height = (($isMetric) ? convertHeightToUs($data['height']) : $data['height']);
            $weight = (($isMetric) ? convertWeightToUs($data['weight']) : $data['weight']);
            $head_circ = $data['head_circ'];

            if ($date == "") {
                continue;
            }

            // only plot if we have both weight and heights. Skip if either is 0.
            // Rational is only well visit will need both, sick visit only needs weight
            // for some clinic.
            if (empty($weight) || empty($height)) {
                continue;
            }

            // get age of patient at this data-point
            // to get data from function getPatientAgeYMD including $age,$age_in_months, $ageinYMD
            extract(getPatientAgeYMD($dob, $date));
            if ($charttype == 'birth') {
                // for birth style chart, we use the age in months
                $age = $age_in_months;
            }

            // exclude data points that do not belong on this chart
            // for example, a data point for a 18 month old can be excluded
            // from that patient's 2-20 yr chart
            $daysold = getPatientAgeInDays($dob, $date);
            if ($daysold >= (365 * 2) && $charttype == "birth") {
                continue;
            }

            if ($daysold <= (365 * 2) && $charttype == "2-20") {
                continue;
            }

            // calculate the x-axis (Age) value
            $x = $dot_x + $delta_x * ($age - $ageOffset);

            // Draw Height dot
            $y1 = $dot_y1 - $delta_y1 * ($height - $heightOffset);
            $point = convertpoint(array((int) $x,$y1));
            echo("<div id='" . attr($point[2]) . "' class='graphic' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'><img src='reddot.gif' /></div>\n");

            // Draw Weight bullseye
            $y2 = $dot_y2 - $delta_y2 * ($weight - $weightOffset);
            $point = convertpoint(array((int) $x,$y2));
            echo("<div id='" . attr($point[2]) . "' class='graphic' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'><img src='redbox.gif' /></div>\n");

            if ($charttype == "birth") {
                // Draw Head circumference
                $HC_x = $HC_dot_x + $HC_delta_x * $age;
                $HC_y = $HC_dot_y - $HC_delta_y * ($head_circ - 11);
                $point = convertpoint(array($HC_x,$HC_y));
                echo("<div id='" . attr($point[2]) . "' class='graphic' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'><img src='bluedot.gif' /></div>\n");
                // Draw Wt and Ht graph at the bottom half
                $WT = $WT_y - $WT_delta_y * ($weight - $WToffset);
                $HT = $HT_x + $HT_delta_x * ($height - $HToffset);
                $point = convertpoint(array($HT,$WT));
                echo("<div id='" . attr($point[2]) . "' class='graphic' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'><img src='reddot.gif' /></div>\n");
            } elseif ($charttype == "2-20") {
                // Draw BMI
                $bmi = $weight / $height / $height * 703;
                $bmi_x = $bmi_dot_x + $bmi_delta_x * ($age - 2);
                $bmi_y = $bmi_dot_y - $bmi_delta_y * ($bmi - 10);
                $point = convertpoint(array($bmi_x,$bmi_y));
                echo("<div id='" . attr($point[2]) . "' class='graphic' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'><img src='bluedot.gif' /></div>\n");
            }

            // fill in data tables

            $datestr = substr($date, 0, 4) . "/" . substr($date, 4, 2) . "/" . substr($date, 6, 2);

            //birth to 24 mos chart has 8 rows to fill.
            if ($count < 8 && $charttype == "birth") {
                $point = convertpoint(array($datatable_x,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($datestr) . "</div>\n");
                $point = convertpoint(array($datatable_x + $datatable_age_offset,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($ageinYMD) . "</div>\n");
                $point = convertpoint(array($datatable_x + $datatable_weight_offset,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsWt($weight)) . "</div>\n");
                $point = convertpoint(array($datatable_x + $datatable_height_offset,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsDist($height)) . "</div>\n");
                $point = convertpoint(array($datatable_x + $datatable_hc_offset,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsDist($head_circ)) . "</div>\n");
                $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
            }

            // 2 to 20 year-old chart has 7 rows to fill.
            if ($count < 7  && $charttype == "2-20") {
                $point = convertpoint(array($datatable_x,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($datestr) . "</div>\n");
                $point = convertpoint(array($datatable_x + $datatable_age_offset,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($ageinYMD) . "</div>\n");
                $point = convertpoint(array($datatable_x + $datatable_weight_offset,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsWt($weight)) . "</div>\n");
                $point = convertpoint(array($datatable_x + $datatable_height_offset,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsDist($height)) . "</div>\n");
                $point = convertpoint(array($datatable_x + $datatable_bmi_offset,$datatable_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(substr($bmi, 0, 5)) . "</div>\n");
                $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
            }

            // Head Circumference chart has 5 rows to fill in
            if ($count < 5 && $charttype == "birth") {
                $point = convertpoint(array($datatable2_x,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($datestr) . "</div>\n");
                $point = convertpoint(array($datatable2_x + $datatable2_age_offset,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($ageinYMD) . "</div>\n");
                $point = convertpoint(array($datatable2_x + $datatable2_weight_offset,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsWt($weight)) . "</div>\n");
                $point = convertpoint(array($datatable2_x + $datatable2_height_offset,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsDist($height)) . "</div>\n");
                $point = convertpoint(array($datatable2_x + $datatable2_hc_offset,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsDist($head_circ)) . "</div>\n");
                $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
            }

            // BMI chart has 14 rows to fill in.
            if ($count < 14 && $charttype == "2-20") {
                $point = convertpoint(array($datatable2_x,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($datestr) . "</div>\n");
                $point = convertpoint(array($datatable2_x + $datatable2_age_offset,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text($ageinYMD) . "</div>\n");
                $point = convertpoint(array($datatable2_x + $datatable2_weight_offset,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsWt($weight)) . "</div>\n");
                $point = convertpoint(array($datatable2_x + $datatable2_height_offset,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(unitsDist($height)) . "</div>\n");
                $point = convertpoint(array($datatable2_x + $datatable2_bmi_offset,$datatable2_y));
                echo("<div id='" . attr($point[2]) . "' class='label_custom' style='position: absolute; top: " . attr($point[1]) . "pt; left: " . attr($point[0]) . "pt;'>" . text(substr($bmi, 0, 5)) . "</div>\n");
                $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
            }
            $count++;
        }
    }
}

// Done creating CSS HTML output
/******************************/


// create the graph
$im     = imagecreatefrompng($chartpath . $chart);
$color1 = imagecolorallocate($im, 0, 0, 255); //blue - color scheme imagecolorallocate($im, Red, Green, Blue)
$color  = imagecolorallocate($im, 255, 51, 51); //red

// draw the patient's name
imagestring($im, 12, $name_x, $name_y, $name, $color);
imagestring($im, 12, $name_x1, $name_y1, $name, $color);

// counter to limit the number of data points plotted
$count = 0;

// plot the data points
foreach ($datapoints as $data) {
    if (!empty($data)) {
        $date = str_replace('-', '', substr($data['date'], 0, 10));
        // values can be US or metric thus convert to US for graphing
        $height = (($isMetric) ? convertHeightToUs($data['height']) : $data['height']);
        $weight = (($isMetric) ? convertWeightToUs($data['weight']) : $data['weight']);
        $head_circ = $data['head_circ'];

        if ($date == "") {
            continue;
        }

        // only plot if we have both weight and heights. Skip if either is 0.
        // Rational is only well visit will need both, sick visit only needs weight
        // for some clinic.
        if (empty($weight) || empty($height)) {
            continue;
        }

        // get age of patient at this data-point
        // to get data from function getPatientAgeYMD including $age, $ageinYMD, $age_in_months
        extract(getPatientAgeYMD($dob, $date));
        if ($charttype == 'birth') {
            // for birth style chart, we use the age in months
            $age = $age_in_months;
        }

        // exclude data points that do not belong on this chart
        // for example, a data point for a 18 month old can be excluded
        // from that patient's 2-20 yr chart
        $daysold = getPatientAgeInDays($dob, $date);
        if ($daysold > (365 * 2) && $charttype == "birth") {
            continue;
        }

        if ($daysold < (365 * 2) && $charttype == "2-20") {
            continue;
        }

        // calculate the x-axis (Age) value
        $x = $dot_x + $delta_x * ($age - $ageOffset);

        // Draw Height dot
        $y1 = $dot_y1 - $delta_y1 * ($height - $heightOffset);
        imagefilledellipse($im, (int) $x, (int) $y1, 10, 10, $color);

        // Draw Weight bullseye
        $y2 = $dot_y2 - $delta_y2 * ($weight - $weightOffset);
        imageellipse($im, (int) $x, (int) $y2, 12, 12, $color); // outter ring
        imagefilledellipse($im, (int) $x, (int) $y2, 5, 5, $color); //center dot

        if ($charttype == "birth") {
            // Draw Head circumference
            $HC_x = $HC_dot_x + $HC_delta_x * $age;
            $HC_y = $HC_dot_y - $HC_delta_y * (((int) $head_circ ?? null) - 11);
            imagefilledellipse($im, (int) $HC_x, (int) $HC_y, 10, 10, $color1);
            // Draw Wt and Ht graph at the bottom half
            $WT = $WT_y - $WT_delta_y * ($weight - $WToffset);
            $HT = $HT_x + $HT_delta_x * ($height - $HToffset);
            imagefilledellipse($im, (int) $HT, (int) $WT, 10, 10, $color);
        } elseif ($charttype == "2-20") {
            // Draw BMI
            $bmi = $weight / $height / $height * 703;
            $bmi_x = $bmi_dot_x + $bmi_delta_x * ($age - 2);
            $bmi_y = $bmi_dot_y - $bmi_delta_y * ($bmi - 10);
            imagefilledellipse($im, (int) $bmi_x, (int) $bmi_y, 10, 10, $color1);
        }

        // fill in data tables

        $datestr = substr($date, 0, 4) . "/" . substr($date, 4, 2) . "/" . substr($date, 6, 2);

        //birth to 24 mos chart has 8 rows to fill.
        if ($count < 8 && $charttype == "birth") {
            imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $color);
            imagestring($im, 2, ($datatable_x + $datatable_age_offset), $datatable_y, $ageinYMD, $color);
            imagestring($im, 2, ($datatable_x + $datatable_weight_offset), $datatable_y, unitsWt($weight), $color);
            imagestring($im, 2, ($datatable_x + $datatable_height_offset), $datatable_y, unitsDist($height), $color);
            imagestring($im, 2, ($datatable_x + $datatable_hc_offset), $datatable_y, unitsDist($head_circ), $color);
            $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
        }

        // 2 to 20 year-old chart has 7 rows to fill.
        if ($count < 7  && $charttype == "2-20") {
            imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $color);
            imagestring($im, 2, ($datatable_x + $datatable_age_offset), $datatable_y, $ageinYMD, $color);
            imagestring($im, 2, ($datatable_x + $datatable_weight_offset), $datatable_y, unitsWt($weight), $color);
            imagestring($im, 2, ($datatable_x + $datatable_height_offset), $datatable_y, unitsDist($height), $color);
            imagestring($im, 2, ($datatable_x + $datatable_bmi_offset), $datatable_y, substr($bmi, 0, 5), $color);
            $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
        }

        // Head Circumference chart has 5 rows to fill in
        if ($count < 5 && $charttype == "birth") {
            imagestring($im, 2, $datatable2_x, $datatable2_y, $datestr, $color);
            imagestring($im, 2, ($datatable2_x + $datatable2_age_offset), $datatable2_y, $ageinYMD, $color);
            imagestring($im, 2, ($datatable2_x + $datatable2_weight_offset), $datatable2_y, unitsWt($weight), $color);
            imagestring($im, 2, ($datatable2_x + $datatable2_height_offset), $datatable2_y, unitsDist($height), $color);
            imagestring($im, 2, ($datatable2_x + $datatable2_hc_offset), $datatable2_y, unitsDist($head_circ), $color);
            $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
        }

        // BMI chart has 14 rows to fill in.
        if ($count < 14 && $charttype == "2-20") {
            imagestring($im, 2, $datatable2_x, $datatable2_y, $datestr, $color);
            imagestring($im, 2, ($datatable2_x + $datatable2_age_offset), $datatable2_y, $ageinYMD, $color);
            imagestring($im, 2, ($datatable2_x + $datatable2_weight_offset), $datatable2_y, unitsWt($weight), $color);
            imagestring($im, 2, ($datatable2_x + $datatable2_height_offset), $datatable2_y, unitsDist($height), $color);
            imagestring($im, 2, ($datatable2_x + $datatable2_bmi_offset), $datatable2_y, substr($bmi, 0, 5), $color);
            $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
        }

        $count++;
    }
}

if (($_GET['pdf'] ?? null) == 1) {
    $pdf = new Cezpdf("LETTER");
    $pdf->ezSetMargins(0, 0, 0, 0);

    // we start with one large image, break it into two pages
    $page1 = imagecreate((imagesx($im) / 2), imagesy($im));
    $page2 = imagecreate((imagesx($im) / 2), imagesy($im));
    imagecopy($page1, $im, 0, 0, 0, 0, (imagesx($im) / 2), imagesy($im));
    imagecopy($page2, $im, 0, 0, (imagesx($im) / 2), 0, imagesx($im), imagesy($im));
    imagedestroy($im);

    // each page is built
    $tmpfilename = tempnam("/tmp", "oemr");
    imagepng($page1, $tmpfilename);
    imagedestroy($page1);
    $pdf->ezImage($tmpfilename);
    $pdf->ezNewPage();
    imagepng($page2, $tmpfilename);
    imagedestroy($page2);
    $pdf->ezImage($tmpfilename);

    // temporary file is removed
    unlink($tmpfilename);

    // output the PDF
    $pdf->ezStream();
} else {
    cssFooter();
}
?>
