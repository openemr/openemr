<?php

/**
 * Handles the growth chart plotting and percentiles, includes charts for patients with Down Syndrome
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com> <daniel@mi-squared.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@groowlingflea.com><daniel@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

include_once("../../../../interface/globals.php");
include_once($GLOBALS['fileroot'] . "/library/patient.inc");
use OpenEMR\Common\Csrf\CsrfUtils;

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

$patient_data = "";
if (isset($pid) && is_numeric($pid)) {
    $patient_data = getPatientData($pid, "fname, lname, sex, DATE_FORMAT(DOB,'%Y%m%d') as DOB");
    $nowAge = getPatientAge($patient_data['DOB']);
    $dob = $patient_data['DOB'];
    $name = $patient_data['fname'] . " " . $patient_data['lname'];
    $hasDS = hasDownSyndrome($pid);
}

// The first data point in the DATA set is significant. It tells date
// of the currently viewed vitals by the user. We will use this
// date to define which chart is displayed on the screen
$charttype = "2-20"; // default the chart-type to ages 2-20
$datapoints = explode('~', $_GET['data']);
if (isset($datapoints) && $datapoints != "") {
    list($date, $height, $weight, $head_circ) = explode('-', $datapoints[0]);
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


// convert to applicable weight units from the globals.php setting
function unitsWt($wt)
{
    if (($GLOBALS['units_of_measurement'] == 2) || ($GLOBALS['units_of_measurement'] == 4)) {
        //convert to metric
        return (number_format(($wt * 0.45359237), 2, '.', '') . xlt('kg', '', ' '));
    } else {
    //keep US units
        return $wt . xlt('lb', '', ' ');
    }
}

// convert to applicable weight units from the globals.php setting
function unitsDist($dist)
{
    if (($GLOBALS['units_of_measurement'] == 2) || ($GLOBALS['units_of_measurement'] == 4)) {
        //convert to metric
        return (number_format(($dist * 2.54), 2, '.', '') . xlt('cm', '', ' '));
    } else {
        //keep US units
        return $dist . xlt('in', '', ' ');
    }
}

/******************************/
/******************************/
/******************************/


$name_x = 837;
$dsname_x = 834; //***DS NAME POS X

$name_y = 65;
$dsname_y = 75; //***DS NAME POS Y

$name_x1 = 837;

$name_y1 = 72;

$ageOffset = 0;

$heightOffset = 0;
$weightOffset = 0;

if ($charttype == 'birth') {
    // Use birth to 24 months chart


    $dot_y2     = 1492;       //weight starts here - at 3 lbs
    $delta_y2   = 28.2;     //pixels per pound - weight

    $HC_dot_x   = 228;     //months start here for Head circumference chart
    $HC_delta_x = 33.2;  //pixels per month for Head circumference chart
    $HC_dot_y   = 939;     //Head circumference starts here - at 11 inches
    $HC_delta_y = 68.9;  //calculated pixels per inch for head circumference

    $WT_y       = 1436; //start here to draw wt and height graph at bottom of Head circumference chart
    $WT_delta_y = 16.527;
    $HT_x       = 229.68; //start here to draw wt and height graph at bottom of Head circumference chart
    $HT_delta_x = 31.13;

    if (preg_match('/^male/i', $patient_data['sex'])) {
        $dot_x      = 244.2;         //months start here (pixel)
        $delta_x    = 33.2;     //pixels per month  - length
        $dot_y1     = 979.55;        //height starts here - at 15 inches
        $delta_y1   = 31.7;    //pixels per inch  - height


        $chart  = "birth-24mos_boys_HC-1-WHO.png"; //Legnth vs Weight $chart)
        $chart2 = "birth-24mos_boys_HC-2-WHO.png"; //Head Circumference
        $ds_chart_hc    = "DS_Boys_HeadCirc_Birthto36mo.png";
        $ds_chart_len   = "DS_Boys_Length_Birthto36mo-1.png";
        $ds_chart_wght  = "DS_Boys_Weight_Birthto36mo-1.png";

        //Offsets and start points are different for boys and girls.
        //Using the same measurements for both genders creates off centered
        // charts, but only for DS charts.
        //

        $HC_dot_x_ds    = 196.4;     //months start here for Head circumference chart
        $HC_delta_x_ds  = 26.8;  //pixels per month for Head circumference chart
        $HC_dot_y_ds    =  1483.21;     //Head circumference starts here - at 34 cm
        $HC_delta_y_ds  = 85.58;  //calculated pixels per cm for head circumference
        $HC_offset_ds   = 34;

        //DS Boys birth to 32 m
        $LEN_dot_x_ds   = 196.63;
        $LEN_delta_x_ds = 26.965;
        $LEN_dot_y_ds   = 1485.31;
        $LEN_delta_y_ds = 25.7681;
        $LEN_offset     = 45;

        $WGHT_dot_x_ds      = 193.011;
        $WGHT_delta_x_ds    = 27.213;
        $WGHT_dot_y_ds      = 1464.54;
        $WGHT_delta_y_ds    = 83.975;
        $WGHT_offset        = 2;

        // pixel positions and offsets for data table
        $datatable_ds_hc_x = 457;
        $datatable_ds_hc_age_offset = 82;
        $datatable_ds_hc_weight_offset = 164;
        $datatable_ds_hc_height_offset = 276;
        $datatable_ds_hc_offset = 375;
        $datatable_ds_hc_y = 1315;
        $datatable_ds_hc_y_increment = 24;

        $datatable_ds_len_x = 457;
        $datatable_ds_len_age_offset = 82;
        $datatable_ds_len_weight_offset = 164;
        $datatable_ds_len_height_offset = 276;
        $datatable_ds_len_hc_offset = 375;
        $datatable_ds_len_y = 1317;
        $datatable_ds_len_y_increment = 24;

        $datatable_ds_wght_x = 457;
        $datatable_ds_wght_age_offset = 82;
        $datatable_ds_wght_weight_offset = 164;
        $datatable_ds_wght_height_offset = 276;
        $datatable_ds_wght_hc_offset = 375;
        $datatable_ds_wght_y = 1300;
        $datatable_ds_wght_y_increment = 23.4;

        // added by BM for CSS html output
        $chartCss1 = "birth-24mos_boys_HC-1.png";
        $chartCss2 = "birth-24mos_boys_HC-2.png";
    } elseif (preg_match('/^female/i', $patient_data['sex'])) {
        $dot_x      = 244.2;         //months start here (pixel)
        $delta_x    = 33.2;     //pixels per month  - length
        $dot_y1     = 953.6;        //height starts here ~14 inches for girls
        $delta_y1   = 31.7;    //pixels per inch  - height

        $dot_y2     = 1460;         //weight starts here - at 1.7 lbs for girls (length and weight)
        $WT_y       = 1436; //start here to draw wt and height graph at bottom of Head circumference chart


        $chart          = "birth-24mos_girls_HC-1-WHO.png";
        $chart2         = "birth-24mos_girls_HC-2-WHO.png";
        $ds_chart_hc    = "DS_Girls_HeadCircumference_Birthto36mo.png";
        $ds_chart_len   = "DS_Girls_Length_Birthto36mo-1.png";
        $ds_chart_wght  = "DS_Girls_Weight_Birthto36mo-1.png";

        //DS Girls, birth to 24 mo. Head Circumference by Age
        $HC_dot_x_ds    = 190;     //months start here for Head circumference chart
        $HC_delta_x_ds  = 27;  //pixels per month for Head circumference chart
        $HC_dot_y_ds    = 1480;     //Head circumference starts here - at 34 cm
        $HC_delta_y_ds  = 85.923;  //calculated pixels per cm for head circumference
        $HC_offset_ds   = 34;

        //DS Girls birth to 24 mo. Length by Age
        $LEN_dot_x_ds   = 197.266;
        $LEN_delta_x_ds = 27.238;
        $LEN_dot_y_ds   = 1455;
        $LEN_delta_y_ds = 28.785;
        $LEN_offset     =  49 ;

        //DS Girls Birth to 24, weight by age
        $WGHT_dot_x_ds = 189.9;
        $WGHT_delta_x_ds = 27.053;
        $WGHT_dot_y_ds = 1475.13;
        $WGHT_delta_y_ds = 85.78;
        $WGHT_offset =  2 ;

        // pixel positions and offsets for data table
        $datatable_ds_hc_x = 457;
        $datatable_ds_hc_age_offset = 82;
        $datatable_ds_hc_weight_offset = 164;
        $datatable_ds_hc_height_offset = 276;
        $datatable_ds_hc_offset = 375;
        $datatable_ds_hc_y = 1317;
        $datatable_ds_hc_y_increment = 24;

        $datatable_ds_len_x = 537;
        $datatable_ds_len_age_offset = 87;
        $datatable_ds_len_weight_offset = 162;
        $datatable_ds_len_height_offset = 248;
        $datatable_ds_len_hc_offset = 331;
        $datatable_ds_len_y = 1309;
        $datatable_ds_len_y_increment = 21;

        $datatable_ds_wght_x = 449;
        $datatable_ds_wght_age_offset = 94.4;
        $datatable_ds_wght_weight_offset = 178;
        $datatable_ds_wght_height_offset = 290.3;
        $datatable_ds_wght_hc_offset = 365;
        $datatable_ds_wght_y = 1310;
        $datatable_ds_wght_y_increment = 24;

        // added by BM for CSS html output
        $chartCss1 = "birth-24mos_girls_HC-1.png";
        $chartCss2 = "birth-24mos_girls_HC-2.png";
    }

    $ageOffset = 0;
    $heightOffset = 15; // Substract 15 because the graph starts at 15 inches for non-DS patients
    $weightOffset = 3;  // graph starts at 3 lbs
    $WToffset = 0; //for wt and ht table at bottom half of HC graph
    $HToffset = 18; // starting inch for wt and ht table at bottom half of HC graph

    // pixel positions and offsets for data table
    $datatable_x                = 476.83;
    $datatable_age_offset       = 94;
    $datatable_weight_offset    = 180;
    $datatable_height_offset    = 270;
    $datatable_bmi_offset       = 360;
    $datatable_hc_offset        = 360;
    $datatable_y                = 1335;
    $datatable_y_increment      = 22;

    // pixel positions and offsets for head-circ data table //***P2
    $datatable2_x               = 461;
    $datatable2_age_offset      = 111;
    $datatable2_weight_offset   = 199;
    $datatable2_height_offset   = 277;
    $datatable2_hc_offset        = 360;

    $datatable2_y = 1401;
    $datatable2_y_increment = 21;
} elseif ($charttype == "2-20") {
    // current patient age between 2 and 20 years

    $dot_x      = 227.362;
    $delta_x    = 44.69342;

    $dot_y1     = 1215; //@40KG
    $delta_y1   = 21.4;

    $dot_y2     = 1476; //1476 at 20 lbs
    $delta_y2   = 3.8;

    $bmi_dot_x      = 171.833;
    $bmi_delta_x    = 50.89;
    $bmi_dot_y      = 1440;
    $bmi_delta_y     = 47.3;

    if (preg_match('/^male/i', $patient_data['sex'])) {
        $chart = "2-20yo_boys_BMI-1-PDF.png";
        $chart2 = "2-20yo_boys_BMI-2-PDF.png";
        $ds_chart_hc    = "DS_Boys_HeadCirc_2-20years.png";
        $ds_chart_len   = "DS_Boys_Height_2-20years-1.png";
        $ds_chart_wght  = "DS_Boys_Weight_2-20years-1.png";

        //2-20 yo boys, HC, DS
        $HC_dot_x_ds    = 186.521;     //months start here for Head circumference chart
        $HC_delta_x_ds  = 53.7069;  //pixels per month for Head circumference chart
        $HC_dot_y_ds    =  1459.29;     //Head circumference starts here - at 34 cm
        $HC_delta_y_ds  = 90.905;  //calculated pixels per cm for head circumference
        $HC_offset_ds   = 43;

        //2-20 yo boys, height, DS
        $LEN_dot_x_ds   = 224.461;
        $LEN_delta_x_ds = 52.2622;
        $LEN_dot_y_ds   = 1480.97;
        $LEN_delta_y_ds = 12.86156;
        $LEN_offset     =  70;

        $WGHT_dot_x_ds      = 201.886;
        $WGHT_delta_x_ds    = 53.610;
        $WGHT_dot_y_ds      = 1473.84;
        $WGHT_delta_y_ds    = 13.442;
        $WGHT_offset        = 5;


        // pixel positions and offsets for data table
        $datatable_ds_hc_x              = 542;
        $datatable_ds_hc_age_offset     = 121;
        $datatable_ds_hc_weight_offset  = 248.825;
        $datatable_ds_hc_height_offset  = 376.021;
        $datatable_ds_hc_offset         = 488.979;
        $datatable_ds_hc_y              = 1325.66;
        $datatable_ds_hc_y_increment    = 24.825;

        $datatable_ds_len_x             = 592.376;
        $datatable_ds_len_age_offset    = 121.907;
        $datatable_ds_len_weight_offset = 240;
        $datatable_ds_len_height_offset = 353.080;
        $datatable_ds_len_bmi_offset    = 467.601;
        $datatable_ds_len_y             = 1340;
        $datatable_ds_len_y_increment   = 21.96;

        $datatable_ds_wght_x             = 200.212;
        $datatable_ds_wght_age_offset    = 122.449;
        $datatable_ds_wght_weight_offset = 243.226;
        $datatable_ds_wght_height_offset = 367.645;
        $datatable_ds_wght_bmi_offset    = 485.662;
        $datatable_ds_wght_y             = 244.219;
        $datatable_ds_wght_y_increment   = 22.506;

        // added by BM for CSS html output
        $chartCss1 = "2-20yo_boys_BMI-1.png";
        $chartCss2 = "2-20yo_boys_BMI-2.png";
    } elseif (preg_match('/^female/i', $patient_data['sex'])) {
        $chart = "2-20yo_girls_BMI-1-PDF.png";
        $chart2 = "2-20yo_girls_BMI-2-PDF.png";
        $ds_chart_hc    = "DS_Girls_HeadCircumference_2to20years-1.png";
        $ds_chart_len   = "DS_Girls_Height_2to20years-1.png";
        $ds_chart_wght  = "DS_Girls_Weight_2to20years-1.png";

        //female head circumference for 2-20 yo w/DS
        $HC_dot_x_ds    = 168.820;     //months start here for Head circumference chart
        $HC_delta_x_ds  = 56.757;     //pixels per month for Head circumference chart
        $HC_dot_y_ds    = 1474.28;     //Head circumference starts here - at 34 cm
        $HC_delta_y_ds  = 84.7;  //calculated pixels per cm for head circumference
        $HC_offset_ds   = 42;

        //female height for 2-20 yo w/DS
        $LEN_dot_x_ds   = 201;
        $LEN_delta_x_ds = 54.334;
        $LEN_dot_y_ds   = 1475.79;
        $LEN_delta_y_ds = 14.43;
        $LEN_offset     = 70;

        //female weight for 2-20 w/DS
        $WGHT_dot_x_ds      = 201;
        $WGHT_delta_x_ds    = 54.9615;
        $WGHT_dot_y_ds      = 1479.78;
        $WGHT_delta_y_ds    = 13.6496;
        $WGHT_offset        = 5;


        // pixel positions and offsets for data table
        $datatable_ds_hc_x              = 465.471;
        $datatable_ds_hc_age_offset     = 156;
        $datatable_ds_hc_weight_offset  = 296.175;
        $datatable_ds_hc_height_offset  = 440.7;
        $datatable_ds_hc_offset         = 591;
        $datatable_ds_hc_y              = 1317;
        $datatable_ds_hc_y_increment    = 25.9;

        $datatable_ds_len_x = 547;
        $datatable_ds_len_age_offset = 139.564;
        $datatable_ds_len_weight_offset = 260.404;
        $datatable_ds_len_height_offset = 389.306;
        $datatable_ds_len_bmi_offset = 526.264;
        $datatable_ds_len_y = 1318.69;
        $datatable_ds_len_y_increment = 24.381;

        $datatable_ds_wght_x = 209;
        $datatable_ds_wght_age_offset = 128;
        $datatable_ds_wght_weight_offset = 247;
        $datatable_ds_wght_height_offset = 367;
        $datatable_ds_wght_bmi_offset = 493;
        $datatable_ds_wght_y = 235.973;
        $datatable_ds_wght_y_increment = 21.3;
        // added by BM for CSS html output
        $chartCss1 = "2-20yo_girls_BMI-1.png";
        $chartCss2 = "2-20yo_girls_BMI-2.png";
    }

    $ageOffset = 2;
    $heightOffset = 30;
    $weightOffset = 20;

    // pixel positions and offsets data table
    $datatable_x                = 128.6;
    $datatable_age_offset       = 112;
    $datatable_weight_offset    = 224;
    $datatable_height_offset    = 321;
    $datatable_bmi_offset       = 429;
    $datatable_y                = 246;
    $datatable_y_increment      = 22;

    // pixel positions and offsets for BMI data table
    $datatable2_x                = 106;
    $datatable2_age_offset       = 85;
    $datatable2_weight_offset    = 174;
    $datatable2_height_offset    = 270;
    $datatable2_bmi_offset       = 351;
    $datatable2_y                = 189;
    $datatable2_y_increment      = 24;
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
        div.label {
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
        function FormSetup()
        {
            changeStyle('page1')
        }

        function changeStyle(css_title)
        {
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
//  return - array(Xcoord,Ycoord,page)
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
    return (array($Xcoord, $Ycoord, $page));
}


if ($_GET['html'] == 1) {
    //build the html css page if selected
    cssHeader();
    cssPage($chartCss1, $chartCss2);

    //output name
    $point = convertpoint(array($name_x, $name_y));
    echo("<div id='" . $point[2]  . "' class='name' style='position: absolute; top: " . $point[1]  . "pt; left: " .
        $point[0] . "pt;'>" . $name  . "</div>\n");

    $point = convertpoint(array($name_x1, $name_y1));
    echo("<div id='" . $point[2]  . "' class='name' style='position: absolute; top: " . $point[1]  . "pt; left: " .
        $point[0] . "pt;'>" . $name  . "</div>\n");

    // counter to limit the number of data points plotted
    $count = 0;

    // plot the data points
    foreach ($datapoints as $data) {
        list($date, $height, $weight, $head_circ) = explode('-', $data);
        if ($height == "" && $weight == "" && $head_circ == "") {
            continue;
        }

        // only plot if we have both weight and heights. Skip if either is 0.
        // Rational is only well visit will need both, sick visit only needs weight
        // for some clinic.
        // if ($weight == 0 || $height == 0 ) { continue; }
        if ($weight == 0) {
            $weight = "X";
        }
        if ($height == 0) {
            $height = "X";
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
        if (is_numeric($height)) {
            $y1 = $dot_y1 - $delta_y1 * ($height - $heightOffset);
            $point = convertpoint(array($x, $y1));
            echo("<div id='" . $point[2] . "' class='graphic' style='position: absolute; top: " .    $point[1] .

            "pt; left: " . $point[0] . "pt;'><img src='reddot.gif' /></div>\n");
        }
        // Draw Weight bullseye
        if (is_numeric($weight)) {
            $y2 = $dot_y2 - $delta_y2 * ($weight - $weightOffset);
            $point = convertpoint(array($x, $y2));
            echo("<div id='" . $point[2] . "' class='graphic' style='position: absolute; top: " .    $point[1] .

            "pt; left: " . $point[0] . "pt;'><img src='redbox.gif' /></div>\n");
        }

        if ($charttype == "birth") {
            // Draw Head circumference
            $HC_x = $HC_dot_x + $HC_delta_x * $age;
            $HC_y = $HC_dot_y - $HC_delta_y * ($head_circ - 11);
            $point = convertpoint(array($HC_x, $HC_y));
            echo("<div id='" . $point[2] . "' class='graphic' style='position: absolute; top: " .    $point[1] .

            "pt; left: " . $point[0] . "pt;'><img src='bluedot.gif' /></div>\n");

            // Draw Wt and Ht graph at the bottom half
            if (is_numeric($weight)) {
                $WT = $WT_y - $WT_delta_y * ($weight - $WToffset);
            }

            if (is_numeric($height)) {
                $HT = $HT_x + $HT_delta_x * ($height - $HToffset);
            }

            if (is_numeric($height) && is_numeric($weight)) {
                $point = convertpoint(array($HT, $WT));
                echo("<div id='" . $point[2] . "' class='graphic' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'><img src='reddot.gif' /></div>\n");
            }
        } elseif ($charttype == "2-20") {
            // Draw BMI
            if (is_numeric($height) && is_numeric($weight)) {
                $bmi = $weight / $height / $height * 703;
                $bmi_x = $bmi_dot_x + $bmi_delta_x * ($age - 2);
                $bmi_y = $bmi_dot_y - $bmi_delta_y * ($bmi - 10);
                $point = convertpoint(array($bmi_x, $bmi_y));
                echo("<div id='" . $point[2] . "' class='graphic' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'><img src='bluedot.gif' /></div>\n");
            }
        }

        // fill in data tables

        $datestr = substr($date, 0, 4) . "/" . substr($date, 4, 2) . "/" . substr($date, 6, 2);

        //birth to 24 mos chart has 8 rows to fill.
        if ($count < 8 && $charttype == "birth") {
            $point = convertpoint(array($datatable_x,$datatable_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  .
                "pt; left: " . $point[0] . "pt;'>" . $datestr . "</div>\n");

            $point = convertpoint(array($datatable_x + $datatable_age_offset,$datatable_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  .
                "pt; left: " . $point[0] . "pt;'>" . $ageinYMD . "</div>\n");

            if (is_numeric($weight)) {
                $point = convertpoint(array($datatable_x + $datatable_weight_offset,$datatable_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                 "pt; left: " . $point[0] . "pt;'>" . unitsWt($weight) . "</div>\n");
            }
            if (is_numeric($height)) {
                $point = convertpoint(array($datatable_x + $datatable_height_offset, $datatable_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . unitsDist($height) . "</div>\n");
            }

            $point = convertpoint(array($datatable_x + $datatable_hc_offset,$datatable_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsDist($head_circ) . "</div>\n");
                $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
        }

        // 2 to 20 year-old chart has 7 rows to fill.
        if ($count < 7  && $charttype == "2-20") {
            $point = convertpoint(array($datatable_x,$datatable_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $datestr . "</div>\n");

            $point = convertpoint(array($datatable_x + $datatable_age_offset,$datatable_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $ageinYMD . "</div>\n");

            if (is_numeric($weight)) {
                $point = convertpoint(array($datatable_x + $datatable_weight_offset, $datatable_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . unitsWt($weight) . "</div>\n");
            }

            if (is_numeric($height)) {
                $point = convertpoint(array($datatable_x + $datatable_height_offset, $datatable_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . unitsDist($height) . "</div>\n");
            }

            if (is_numeric($height) && is_numeric($weight)) {
                $point = convertpoint(array($datatable_x + $datatable_bmi_offset, $datatable_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . substr($bmi, 0, 5) . "</div>\n");
            }
                $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
        }

        // Head Circumference chart has 5 rows to fill in
        if ($count < 5 && $charttype == "birth") {
            $point = convertpoint(array($datatable2_x,$datatable2_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $datestr . "</div>\n");
            $point = convertpoint(array($datatable2_x + $datatable2_age_offset,$datatable2_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $ageinYMD . "</div>\n");

            if (is_numeric($weight)) {
                $point = convertpoint(array($datatable2_x + $datatable2_weight_offset, $datatable2_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . unitsWt($weight) . "</div>\n");
            }

            if (is_numeric($height)) {
                $point = convertpoint(array($datatable2_x + $datatable2_height_offset, $datatable2_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . unitsDist($height) . "</div>\n");
            }

            $point = convertpoint(array($datatable2_x + $datatable2_hc_offset,$datatable2_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsDist($head_circ) . "</div>\n");
            $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
        }

        // BMI chart has 14 rows to fill in.
        if ($count < 14 && $charttype == "2-20") {
            $point = convertpoint(array($datatable2_x,$datatable2_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $datestr . "</div>\n");

            $point = convertpoint(array($datatable2_x + $datatable2_age_offset,$datatable2_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $ageinYMD . "</div>\n");

            if (is_numeric($weight)) {
                $point = convertpoint(array($datatable2_x + $datatable2_weight_offset, $datatable2_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . unitsWt($weight) . "</div>\n");
            }

            if (is_numeric($height)) {
                $point = convertpoint(array($datatable2_x + $datatable2_height_offset, $datatable2_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . unitsDist($height) . "</div>\n");
            }

            if (is_numeric($height) && is_numeric($weight)) {
                $point = convertpoint(array($datatable2_x + $datatable2_bmi_offset, $datatable2_y));
                echo("<div id='" . $point[2] . "' class='label' style='position: absolute; top: " .   $point[1] .

                "pt; left: " . $point[0] . "pt;'>" . substr($bmi, 0, 5) . "</div>\n");
                $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
            }
        }

        $count++;
    }

    cssFooter();
    exit;
}

// Done creating CSS HTML output
/******************************/


// START PDF HERE - Growth charts for height (length) are different for boys and girls
$im          = imagecreatefrompng($chartpath . $chart);
$im2          = imagecreatefrompng($chartpath . $chart2);
$im_ds_hc    = imagecreatefrompng($chartpath . $ds_chart_hc);
$im_ds_len    = imagecreatefrompng($chartpath . $ds_chart_len);
$im_ds_wght    = imagecreatefrompng($chartpath . $ds_chart_wght);



$blue = imagecolorclosest($im_ds_hc, 24, 49, 194); //blue - color scheme imagecolorclosest($im, Red, Green, Blue)
$red  = imagecolorclosest($im, 255, 51, 51); //red




// draw the patient's name


//***DS
if (preg_match('/^male/i', $patient_data['sex'])) {
        imagestring($im, 12, $name_x, $name_y, $name, $blue);
        imagestring($im2, 12, $name_x1, $name_y1, $name, $blue);
        imagestring($im_ds_hc, 12, $dsname_x, $dsname_y, $name, $blue);
        imagestring($im_ds_len, 12, $dsname_x, $dsname_y, $name, $blue);
        imagestring($im_ds_wght, 12, $dsname_x, $dsname_y, $name, $blue);
        $ellipse = imagecolorclosest($im, 255, 51, 51); //re
} else {
        imagestring($im, 12, $name_x, $name_y, $name, $red);
        imagestring($im2, 12, $name_x1, $name_y1, $name, $red);
        imagestring($im_ds_hc, 12, $dsname_x, $dsname_y, $name, $red);
        imagestring($im_ds_len, 12, $dsname_x, $dsname_y, $name, $red);
        imagestring($im_ds_wght, 12, $dsname_x, $dsname_y, $name, $red);

        $ellipse = imagecolorclosest($im, 27, 124, 255);
}

    imagestring($im_ds_hc, 1, 0, 0, '.', $ellipse);
    imagestring($im_ds_wght, 1, 0, 0, '.', $ellipse);
    imagestring($im_ds_len, 1, 0, 0, '.', $ellipse);
    imagestring($im, 1, 0, 0, '.', $ellipse);
    imagestring($im2, 1, 0, 0, '.', $ellipse);


// counter to limit the number of data points plotted
$count = 0;

// plot the data points
foreach ($datapoints as $data) {
    list($date, $height, $weight, $head_circ) = explode('-', $data);
    if ($date == "") {
        continue;
    }
    if ($height == "" && $weight == "" && $head_circ == "") {
        continue;
    }
    // only plot if we have both weight and heights. Skip if either is 0.
    // Rational is only well visit will need both, sick visit only needs weight
    // for some clinic.
    //***SANTI ADD - we remove this because it was causing data errors.
    //if ($weight == 0 || $height == 0 ) { continue; }
    if ($weight == 0) {
        $weight = "X";
    }
    if ($height == 0) {
        $height = "X";
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
    if (is_numeric($height)) {
        $y1 = $dot_y1 - $delta_y1 * ($height - $heightOffset);
        imagefilledellipse($im, $x, $y1, 10, 10, $ellipse);
    }
    // Draw Weight bullseye //***P2
    if (is_numeric($weight)) {
        $y2 = $dot_y2 - $delta_y2 * ($weight - $weightOffset);
        imageellipse($im, $x, $y2, 12, 12, $ellipse); // outter ring
        imagefilledellipse($im, $x, $y2, 5, 5, $ellipse); //center dot
    }

    if ($charttype == "birth") {
        // Draw Head circumference
        $HC_x = $HC_dot_x + $HC_delta_x * $age;
        $HC_y = $HC_dot_y - $HC_delta_y * ($head_circ - 11);

        if (is_numeric($head_circ) && $head_circ > 0) {
            imagefilledellipse($im2, $HC_x, $HC_y, 10, 10, $ellipse);
        }
        // Draw Wt and Ht graph at the bottom half
        if (is_numeric($weight)) {
            $WT = $WT_y - $WT_delta_y * ($weight - $WToffset);
        }
        if (is_numeric($height)) {
            $HT = $HT_x + $HT_delta_x * ($height - $HToffset);
        }

        if (is_numeric($height) && is_numeric($weight)) {
            imagefilledellipse($im2, $HT, $WT, 10, 10, $ellipse);
        }

        if ($hasDS) {
            //Get the HC for patient with DS
            $HC_x_ds = $HC_dot_x_ds + $HC_delta_x_ds * $age;
            $LEN_x_ds = $LEN_dot_x_ds + $LEN_delta_x_ds * $age;
            $WGHT_x_ds = $WGHT_dot_x_ds + $WGHT_delta_x_ds * $age;

            //Datasheet is in cm , we get the number in inches
            $HC_y_ds = $HC_dot_y_ds - $HC_delta_y_ds * (2.54 * $head_circ - $HC_offset_ds);
            $LEN_y_ds = $LEN_dot_y_ds - $LEN_delta_y_ds * ((2.54 * $height - $LEN_offset));
            $WGHT_y_ds = $WGHT_dot_y_ds - $WGHT_delta_y_ds * ((.453592 * $weight - $WGHT_offset));

            imagefilledellipse($im_ds_hc, $HC_x_ds, $HC_y_ds, 10, 10, $ellipse);
            imagefilledellipse($im_ds_len, $LEN_x_ds, $LEN_y_ds, 10, 10, $ellipse);
            imagefilledellipse($im_ds_wght, $WGHT_x_ds, $WGHT_y_ds, 10, 10, $ellipse);
        }
    } elseif ($charttype == "2-20") {
        // Draw BMI
        if (is_numeric($weight) && is_numeric($height)) {
            $bmi = $weight / $height / $height * 703;
            $bmi_x = $bmi_dot_x + $bmi_delta_x * ($age - 2);
            $bmi_y = $bmi_dot_y - $bmi_delta_y * ($bmi - 10);
            imagefilledellipse($im2, $bmi_x, $bmi_y, 10, 10, $ellipse);
        }

        if ($hasDS) {
            //Get the HC for patient with DS, since we start at 2 we substract 2 from the age
            $HC_x_ds = $HC_dot_x_ds + $HC_delta_x_ds * ($age - 2);
            $LEN_x_ds = $LEN_dot_x_ds + $LEN_delta_x_ds * ($age - 2);
            $WGHT_x_ds = $WGHT_dot_x_ds + $WGHT_delta_x_ds * ($age - 2);

            //Datasheet is in cm , we get the number in inches
            $HC_y_ds = $HC_dot_y_ds - $HC_delta_y_ds * (2.54 * $head_circ - $HC_offset_ds);
            $LEN_y_ds = $LEN_dot_y_ds - $LEN_delta_y_ds * ((2.54 * $height - $LEN_offset));
            $WGHT_y_ds = $WGHT_dot_y_ds - $WGHT_delta_y_ds * ((.453592 * $weight - $WGHT_offset));

            imagefilledellipse($im_ds_hc, $HC_x_ds, $HC_y_ds, 10, 10, $ellipse);
            imagefilledellipse($im_ds_len, $LEN_x_ds, $LEN_y_ds, 10, 10, $ellipse);
            imagefilledellipse($im_ds_wght, $WGHT_x_ds, $WGHT_y_ds, 10, 10, $ellipse);
        }
    }
    // fill in data tables

    $datestr = substr($date, 0, 4) . "/" . substr($date, 4, 2) . "/" . substr($date, 6, 2);

    //birth to 24 mos chart has 8 rows to fill.
    if ($count < 8 && $charttype == "birth") {
        $test = imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $ellipse);
        $test = imagestring($im, 2, ($datatable_x + $datatable_age_offset), $datatable_y, $ageinYMD, $ellipse);
        if (is_numeric($weight)) {
            imagestring($im, 2, ($datatable_x + $datatable_weight_offset), $datatable_y, unitsWt($weight), $ellipse);
        }

        if (is_numeric($height)) {
            imagestring($im, 2, ($datatable_x + $datatable_height_offset), $datatable_y, unitsDist($height), $ellipse);
        }
        if (is_numeric($head_circ) && $head_circ > 0) {
            imagestring($im, 2, ($datatable_x + $datatable_hc_offset), $datatable_y, unitsDist($head_circ), $ellipse);
        }
        $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
    }

    // 2 to 20 year-old chart has 7 rows to fill.
    if ($count < 7  && $charttype == "2-20") {
        imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $ellipse);
        imagestring($im, 2, ($datatable_x + $datatable_age_offset), $datatable_y, $ageinYMD, $ellipse);
        if (is_numeric($weight)  && $weight > 0) {
            imagestring($im, 2, ($datatable_x + $datatable_weight_offset), $datatable_y, unitsWt($weight), $ellipse);
        }

        if (is_numeric($height)) {
            imagestring($im, 2, ($datatable_x + $datatable_height_offset), $datatable_y, unitsDist($height), $ellipse);
        }

        if (is_numeric($height) && is_numeric($weight)) {
            imagestring($im, 2, ($datatable_x + $datatable_bmi_offset), $datatable_y, substr($bmi, 0, 5), $ellipse);
        }

        $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
    }

    // Head Circumference chart has 5 rows to fill in
    if ($count < 5 && $charttype == "birth") {
        imagestring($im2, 2, $datatable2_x, $datatable2_y, $datestr, $ellipse);
        imagestring($im2, 2, ($datatable2_x + $datatable2_age_offset), $datatable2_y, $ageinYMD, $ellipse);
        if (is_numeric($weight)) {
            imagestring($im2, 2, ($datatable2_x + $datatable2_weight_offset), $datatable2_y, unitsWt($weight), $ellipse);
        }
        if (is_numeric($height)) {
            imagestring($im2, 2, ($datatable2_x + $datatable2_height_offset), $datatable2_y, unitsDist($height), $ellipse);
        }

        if (is_numeric($head_circ) && $head_circ > 0) {
            imagestring($im2, 2, ($datatable2_x + $datatable2_hc_offset), $datatable2_y, unitsDist($head_circ), $ellipse);
        }
        $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
    }

    // BMI chart has 14 rows to fill in.
    if ($count < 14 && $charttype == "2-20") {
        imagestring($im2, 2, $datatable2_x, $datatable2_y, $datestr, $ellipse);
        imagestring($im2, 2, ($datatable2_x + $datatable2_age_offset), $datatable2_y, $ageinYMD, $ellipse);
        if (is_numeric($weight)) {
            imagestring($im2, 2, ($datatable2_x + $datatable2_weight_offset), $datatable2_y, unitsWt($weight), $ellipse);
        }
        if (is_numeric($height)) {
            imagestring($im2, 2, ($datatable2_x + $datatable2_height_offset), $datatable2_y, unitsDist($height), $ellipse);
        }
        if (is_numeric($height) && is_numeric($weight)) {
            imagestring($im2, 2, ($datatable2_x + $datatable2_bmi_offset), $datatable2_y, substr($bmi, 0, 5), $ellipse);
        }
        $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
    }
    //***Down Syndrome Starts Here
    if ($count < 7 && $charttype == "birth"  && $hasDS) {
        //Head Circumference
        imagestring($im_ds_hc, 2, $datatable_ds_hc_x, $datatable_ds_hc_y, $datestr, $ellipse);
        imagestring($im_ds_len, 2, $datatable_ds_len_x, $datatable_ds_len_y, $datestr, $ellipse);
        imagestring($im_ds_wght, 2, $datatable_ds_wght_x, $datatable_ds_wght_y, $datestr, $ellipse);

        imagestring($im_ds_hc, 2, ($datatable_ds_hc_x + $datatable_ds_hc_age_offset), $datatable_ds_hc_y, $ageinYMD, $ellipse);
        imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_age_offset), $datatable_ds_len_y, $ageinYMD, $ellipse);
        imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_age_offset), $datatable_ds_wght_y, $ageinYMD, $ellipse);

        if (is_numeric($weight) && $weight > 0) {
            imagestring($im_ds_hc, 2, ($datatable_ds_hc_x + $datatable_ds_hc_weight_offset), $datatable_ds_hc_y, unitsWt($weight), $ellipse);
            imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_weight_offset), $datatable_ds_len_y, unitsWt($weight), $ellipse);
            imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_weight_offset), $datatable_ds_wght_y, unitsWt($weight), $ellipse);
        }
        //height
        if (is_numeric($height) && $height > 0) {
            imagestring($im_ds_hc, 2, ($datatable_ds_hc_x + $datatable_ds_hc_height_offset), $datatable_ds_hc_y, unitsDist($height), $ellipse);
            imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_height_offset), $datatable_ds_len_y, unitsDist($height), $ellipse);
            imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_height_offset), $datatable_ds_wght_y, unitsDist($height), $ellipse);
        }

        if (is_numeric($bmi) && $bmi > 0) {
            imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_bmi_offset), $datatable_ds_len_y, substr($bmi, 0, 5), $ellipse);
            imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_bmi_offset), $datatable_ds_wght_y, substr($bmi, 0, 5), $ellipse);
        }

        if (is_numeric($head_circ) && $head_circ > 0) {
            imagestring($im_ds_hc, 2, ($datatable_ds_hc_x + $datatable_ds_hc_offset), $datatable_ds_hc_y, unitsDist($head_circ), $ellipse);
            imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_hc_offset), $datatable_ds_len_y, unitsDist($head_circ), $ellipse);
            imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_hc_offset), $datatable_ds_wght_y, unitsDist($head_circ), $ellipse);
        }


        $datatable_ds_hc_y = $datatable_ds_hc_y + $datatable_ds_hc_y_increment; // increment the datatable2 "row pointer"
        $datatable_ds_len_y = $datatable_ds_len_y + $datatable_ds_len_y_increment; // increment the datatable2 "row pointer"
        $datatable_ds_wght_y = $datatable_ds_wght_y + $datatable_ds_wght_y_increment; // increment the datatable2 "row pointer"
    }

    if ($count < 6 &&  $charttype == "2-20"   && $hasDS) {
        //Head Circumference
        imagestring($im_ds_hc, 2, $datatable_ds_hc_x, $datatable_ds_hc_y, $datestr, $ellipse);
        imagestring($im_ds_len, 2, $datatable_ds_len_x, $datatable_ds_len_y, $datestr, $ellipse);
        imagestring($im_ds_wght, 2, $datatable_ds_wght_x, $datatable_ds_wght_y, $datestr, $ellipse);

        imagestring($im_ds_hc, 2, ($datatable_ds_hc_x + $datatable_ds_hc_age_offset), $datatable_ds_hc_y, $ageinYMD, $ellipse);
        imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_age_offset), $datatable_ds_len_y, $ageinYMD, $ellipse);
        imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_age_offset), $datatable_ds_wght_y, $ageinYMD, $ellipse);

        if (is_numeric($weight) && $weight > 0) {
            imagestring($im_ds_hc, 2, ($datatable_ds_hc_x + $datatable_ds_hc_weight_offset), $datatable_ds_hc_y, unitsWt($weight), $ellipse);
            imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_weight_offset), $datatable_ds_len_y, unitsWt($weight), $ellipse);
            imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_weight_offset), $datatable_ds_wght_y, unitsWt($weight), $ellipse);
        }
        //height
        if (is_numeric($height) && $height > 0) {
            imagestring($im_ds_hc, 2, ($datatable_ds_hc_x + $datatable_ds_hc_height_offset), $datatable_ds_hc_y, unitsDist($height), $ellipse);
            imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_height_offset), $datatable_ds_len_y, unitsDist($height), $ellipse);
            imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_height_offset), $datatable_ds_wght_y, unitsDist($height), $ellipse);
        }

        if (is_numeric($head_circ) && $head_circ > 0) {
            imagestring($im_ds_hc, 2, ($datatable_ds_hc_x + $datatable_ds_hc_offset), $datatable_ds_hc_y, unitsDist($head_circ), $ellipse);
        }

        if (is_numeric($bmi) && $bmi > 0) {
            imagestring($im_ds_len, 2, ($datatable_ds_len_x + $datatable_ds_len_bmi_offset), $datatable_ds_len_y, substr($bmi, 0, 5), $ellipse);
            imagestring($im_ds_wght, 2, ($datatable_ds_wght_x + $datatable_ds_wght_bmi_offset), $datatable_ds_wght_y, substr($bmi, 0, 5), $ellipse);
        }




        $datatable_ds_hc_y = $datatable_ds_hc_y + $datatable_ds_hc_y_increment; // increment the datatable2 "row pointer"
        $datatable_ds_len_y = $datatable_ds_len_y + $datatable_ds_len_y_increment; // increment the datatable2 "row pointer"
        $datatable_ds_wght_y = $datatable_ds_wght_y + $datatable_ds_wght_y_increment; // increment the datatable2 "row pointer"
    }

    $count++;
}

if ($_GET['pdf'] == 1) {
    $pdf = new Cezpdf("LETTER");
    $pdf->ezSetMargins(0, 0, 0, 0);

    // we start with one large image, break it into two pages
    $page1 = imagecreate((imagesx($im)), imagesy($im));

    $page2 = imagecreate((imagesx($im2)), imagesy($im2));

    if ($hasDS) {
        $page3 = imagecreate(imagesx($im_ds_hc), imagesy($im_ds_hc));
        $page4 = imagecreate(imagesx($im_ds_len), imagesy($im_ds_len));
        $page5 = imagecreate(imagesx($im_ds_wght), imagesy($im_ds_wght));
    }

    imagecopy($page1, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
    imagecopy($page2, $im2, 0, 0, 0, 0, imagesx($im2), imagesy($im2));

    if ($hasDS) {
        imagecopy($page3, $im_ds_hc, 0, 0, 0, 0, imagesx($im_ds_hc), imagesy($im_ds_hc));
        imagecopy($page4, $im_ds_len, 0, 0, 0, 0, imagesx($im_ds_len), imagesy($im_ds_len));
        imagecopy($page5, $im_ds_wght, 0, 0, 0, 0, imagesx($im_ds_wght), imagesy($im_ds_wght));
    }

    imagedestroy($im);
    imagedestroy($im2);

    if ($hasDS) {
        imagedestroy($im_ds_hc);
        imagedestroy($im_ds_len);
        imagedestroy($im_ds_wght);
    }

    // each page is built
    $tmpfilename = tempnam("/tmp", "oemr");


    if ($hasDS) {
        imagepng($page3, $tmpfilename, 9);
        imagedestroy($page3);
        $pdf->ezImage($tmpfilename);

        $pdf->ezNewPage();
        imagepng($page4, $tmpfilename, 9);
        imagedestroy($page4);
        $pdf->ezImage($tmpfilename);

        $pdf->ezNewPage();
        imagepng($page5, $tmpfilename, 9);
        imagedestroy($page5);
        $pdf->ezImage($tmpfilename);
        $pdf->ezNewPage();
    }

    imagepng($page1, $tmpfilename, 9);
    imagedestroy($page1);
    $pdf->ezImage($tmpfilename);

    $pdf->ezNewPage();
    imagepng($page2, $tmpfilename, 9);
    imagedestroy($page2);
    $pdf->ezImage($tmpfilename);

    // temporary file is removed
    unlink($tmpfilename);

    // output the PDF
    $pdf->ezStream();
} else {
    // older style chart that is simply a PNG image
    header("Content-type: image/png");
    imagepng($im);
    imagedestroy($im);
    imagepng($im2);
    imagedestroy($im2);
}
?>
