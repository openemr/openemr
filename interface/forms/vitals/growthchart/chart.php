<?php

// be careful with the relative paths here...

// Description
//  Script can create growth charts for following formats:
//    1)png image
//    2)pdf
//    3)html (css for printing)
//
// The png image and pdf require following files in current directory:
//  2-20yo_boys_BMI.png
//  2-20yo_girls_BMI.png
//  birth-36mos_boys_HC.png
//  birth-36mos_girls_HC.png
//
// The html (css for printing) require the following files in current directory
//  2-20yo_boys_BMI-1.png
//  2-20yo_boys_BMI-2.png
//  2-20yo_girls_BMI-1.png
//  2-20yo_girls_BMI-2.png
//  birth-24mos_boys_HC-1.png
//  birth-24mos_boys_HC-2.png
//  birth-24mos_girls_HC-1.png
//  birth-24mos_girls_HC-2.png
//  bluedot.gif
//  redbox.gif
//  reddot.gif
//  chart.php
//  page1.css
//  page2.css
//

include_once ("../../../../interface/globals.php");
include_once ($GLOBALS['fileroot']."/library/patient.inc");
$chartpath = $GLOBALS['fileroot']."/interface/forms/vitals/growthchart/";

$name = "";
$pid = $_GET['pid'];

if ($pid == "") {
    // no pid? no graph for you.
    echo "<p>" . xl('Missing PID.','','',' ') .  xl('Please close this window.') . "</p>";
    exit;
}

$patient_data = "";
if (isset($pid) && is_numeric($pid)) {
    $patient_data = getPatientData($pid, "fname, lname, sex, DATE_FORMAT(DOB,'%Y%m%d') as DOB");
    $nowAge = getPatientAge($patient_data['DOB']); 
    $dob = $patient_data['DOB'];
    $name = $patient_data['fname'] ." ".$patient_data['lname'];
}

// The first data point in the DATA set is significant. It tells date 
// of the currently viewed vitals by the user. We will use this
// date to define which chart is displayed on the screen
$charttype = "2-20"; // default the chart-type to ages 2-20
$datapoints = explode('~', $_GET['data']);
if (isset($datapoints) && $datapoints != "") {
    list($date, $height, $weight, $head_circ) = explode('-', $datapoints[0]);
    if ($date != "") { $charttype_date = $date; }
    $tmpAge = getPatientAgeInDays($patient_data['DOB'], $date);
    // use the birth-36 chart if the age-on-date-of-vitals is 25months or younger 
    if ($tmpAge < (365*2)+31 ) { $charttype = "birth"; }
}

//sort the datapoints
rsort($datapoints);

// determine the patient's age
// return - a floating point age in months or years
//        - a string in the formay '#y #m #d'
function get_age($dob, $date=null) {
    global $charttype;
	
    if ($date == null) {
        $daynow = date("d");
        $monthnow = date("m");
        $yearnow = date("Y");
    }
    else {
        $datenow=preg_replace("/-/", "", $date);
        $yearnow=substr($datenow,0,4);
        $monthnow=substr($datenow,4,2);
        $daynow=substr($datenow,6,2);
        $datenow=$yearnow.$monthnow.$daynow;
    }

    $dob=preg_replace("/-/", "", $dob);
    $dobyear=substr($dob,0,4);
    $dobmonth=substr($dob,4,2);
    $dobday=substr($dob,6,2);
    $dob=$dobyear.$dobmonth.$dobday;

    //to compensate for 30, 31, 28, 29 days/month
    $mo=$monthnow; //to avoid confusion with later calculation

    if ($mo==05 or $mo==07 or $mo==10 or $mo==12) {  //determined by monthnow-1 
        $nd=30; //nd = number of days in a month, if monthnow is 5, 7, 9, 12 then 
    }  // look at April, June, September, November for calculation.  These months only have 30 days.
    elseif ($mo==03) { // for march, look to the month of February for calculation, check for leap year
        $check_leap_Y=$yearnow/4; // To check if this is a leap year. 
        if (is_int($check_leap_Y)) {$nd=29;} //If it true then this is the leap year
        else {$nd=28;} //otherwise, it is not a leap year.
    }
    else {$nd=31;} // other months have 31 days

    $bdthisyear=$yearnow.$dobmonth.$dobday; //Date current year's birthday falls on
    if ($datenow < $bdthisyear) // if patient hasn't had birthday yet this year
    {
        $age_year = $yearnow - $dobyear - 1;
        if ($daynow < $dobday) {
            $months_since_birthday=12 - $dobmonth + $monthnow - 1;
            $days_since_dobday=$nd - $dobday + $daynow; //did not take into account for month with 31 days
        }
        else {
            $months_since_birthday=12 - $dobmonth + $monthnow;
            $days_since_dobday=$daynow - $dobday;
        }
    }
    else // if patient has had birthday this calandar year
    {
        $age_year = $yearnow - $dobyear;
        if ($daynow < $dobday) {
            $months_since_birthday=$monthnow - $dobmonth -1;
            $days_since_dobday=$nd - $dobday + $daynow;
        }
        else {
            $months_since_birthday=$monthnow - $dobmonth;
            $days_since_dobday=$daynow - $dobday;
        }	
    }
    
    $day_as_month_decimal = $days_since_dobday / 30;
    $months_since_birthday_float = $months_since_birthday + $day_as_month_decimal;
    $month_as_year_decimal = $months_since_birthday_float / 12;
    $age_float = $age_year + $month_as_year_decimal;

    if ($charttype == 'birth') {
    //if (($age_year < 2) or ($age_year == 2 and $months_since_birthday < 12)) {
        $age_in_months = $age_year * 12 + $months_since_birthday_float;
        $age = round($age_in_months,2);  //round the months to xx.xx 2 floating points
    }
    else if ($charttype == '2-20') {
        $age = round($age_float,2);
    }

    // round the years to 2 floating points
    $ageinYMD = $age_year."y ".$months_since_birthday."m ".$days_since_dobday."d";
    return compact('age','ageinYMD');
}

// convert to applicable weight units from the globals.php setting
function unitsWt($wt) {
    if (($GLOBALS['units_of_measurement'] == 2) || ($GLOBALS['units_of_measurement'] == 4)) {
        //convert to metric
	return (number_format(($wt*0.45359237),2,'.','').xl('kg','',' '));
    }
    else {
	//keep US units
        return $wt.xl('lb','',' ');
    }
}

// convert to applicable weight units from the globals.php setting
function unitsDist($dist) {
    if (($GLOBALS['units_of_measurement'] == 2) || ($GLOBALS['units_of_measurement'] == 4)) {
        //convert to metric
        return (number_format(($dist*2.54),2,'.','').xl('cm','',' '));
    }
    else {
        //keep US units
        return $dist.xl('in','',' ');
    }
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

    if (preg_match('/^male/i', $patient_data['sex'])) { 
        $chart = "birth-24mos_boys_HC.png";

        // added by BM for CSS html output
        $chartCss1 = "birth-24mos_boys_HC-1.png";
        $chartCss2 = "birth-24mos_boys_HC-2.png"; 
    }
    elseif (preg_match('/^female/i', $patient_data['sex'])) { 
        $chart = "birth-24mos_girls_HC.png";

        // added by BM for CSS html output
        $chartCss1 = "birth-24mos_girls_HC-1.png";
        $chartCss2 = "birth-24mos_girls_HC-2.png"; 
    }

    $ageOffset = 0;
    $heightOffset = 15; // Substract 15 because the graph starts at 15 inches
    $weightOffset = 3;  // graph starts at 0 lbs
    
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
}	
elseif ($charttype == "2-20") {
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
    }
    elseif (preg_match('/^female/i', $patient_data['sex'])) { 
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
}

else {
    // bad age data? no graph for you.
    echo "<p>" . xl('Age data is out of range.') . "</p>";
    exit;
}

/******************************/
// Section for the CSS HTML table
//  this will bypass gd and pdf requirements
//  to allow internationalization and flexibility
//  with fonts

$cssWidth = 524;
$cssHeight = 668;

function cssHeader() {
    global $cssWidth, $cssHeight;

    ?>
    <html>
    <head>
    <link rel="stylesheet" type="text/css" title="page1" href="page1.css">
    <link rel="stylesheet" type="text/css" title="page2" href="page2.css">
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
            width: <?php echo $cssWidth; ?>pt;
            height: <?php echo $cssHeight; ?>pt;
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
            width: <?php echo $cssWidth; ?>pt;
            height: <?php echo $cssHeight; ?>pt;
        }
        @media print {
	    div.DoNotPrint {
	        display: none;
	    }	
	}	
    </style>
    <SCRIPT LANGUAGE="JavaScript">  
        function FormSetup()	{
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
	    window.print();
        }	
    </SCRIPT>
    <title><?php xl('Growth Chart','e'); ?></title>
    </head>
    <body Onload="FormSetup()">
    <div class="DoNotPrint">
        <table>
            <tr>
                <td>
                    <input type="button" value="<?php xl('View Page 1','e'); ?>" onclick="javascript:changeStyle('page1')" class="button">
                    <input type="button" value="<?php xl('View Page 2','e'); ?>" onclick="javascript:changeStyle('page2')" class="button">
                    <input type="button" value="<?php xl('Print Page 1','e'); ?>" onclick="javascript:pagePrint('page1')" class="button">
                    <input type="button" value="<?php xl('Print Page 2','e'); ?>" onclick="javascript:pagePrint('page2')" class="button">
                </td>
            </tr>
        </table>
    </div>    
    <?php
}

function cssFooter() {
    ?>
    </body>
    </html>
    <?php
}

function cssPage($image1,$image2) {
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
function convertpoint($coord) {
    global $cssWidth, $cssHeight;

    //manual offsets to help line up output
    $Xoffset=-3;
    $Yoffset=-2;
    
    //collect coordinates from input array
    $Xcoord=$coord[0];
    $Ycoord=$coord[1];
    
    //adjust with offsets
    $Xcoord = $Xcoord + $Xoffset;
    $Ycoord = $Ycoord + $Yoffset;
    
    
    if ($Xcoord > 1000) {
        //on second page so subtract 1000 from x
	$Xcoord = $Xcoord - 1000;
	$page = "page2";
    }
    else {
        $page = "page1";	
    }
    
    //calculate conversion to pt (decrease number of decimals)
    $Xcoord = ($cssWidth*$Xcoord)/1000;
    $Xcoord = number_format($Xcoord,1,'.','');
    $Ycoord = ($cssHeight*$Ycoord)/1294;
    $Ycoord = number_format($Ycoord,1,'.','');
    
    //return point
    return(Array($Xcoord,$Ycoord,$page));
}


if ($_GET['html'] == 1) {
    //build the html css page if selected
    cssHeader();
    cssPage($chartCss1,$chartCss2);
    
    //output name
    $point = convertpoint(Array($name_x,$name_y));
    echo("<div id='" . $point[2]  . "' class='name' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $name  . "</div>\n");
    $point = convertpoint(Array($name_x1,$name_y1));
    echo("<div id='" . $point[2]  . "' class='name' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $name  . "</div>\n");

    // counter to limit the number of data points plotted
    $count = 0;

    // plot the data points
    foreach ($datapoints as $data) {
        list($date, $height, $weight, $head_circ) = explode('-', $data);
        if ($date == "") { continue; }

        // only plot if we have both weight and heights. Skip if either is 0.
        // Rational is only well visit will need both, sick visit only needs weight
        // for some clinic.
        if ($weight == 0 || $height == 0 ) { continue; }

        // get age of patient at this data-point
        // to get data from function get_age including $age, $ageinYMD
        extract(get_age($dob, $date));

        // exclude data points that do not belong on this chart
        // for example, a data point for a 18 month old can be excluded
        // from that patient's 2-20 yr chart
        $daysold = getPatientAgeInDays($dob, $date);
        if ($daysold > (365*3) && $charttype == "birth") { continue; }
        if ($daysold < (365*2) && $charttype == "2-20") { continue; }

        // calculate the x-axis (Age) value
        $x = $dot_x + $delta_x * ($age - $ageOffset);

        // Draw Height dot
        $y1 = $dot_y1 - $delta_y1 * ($height - $heightOffset);
        $point = convertpoint(Array($x,$y1));
        echo("<div id='" . $point[2]  . "' class='graphic' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'><img src='reddot.gif' /></div>\n");        

        // Draw Weight bullseye
        $y2 = $dot_y2 - $delta_y2 * ($weight - $weightOffset);
	$point = convertpoint(Array($x,$y2));
	echo("<div id='" . $point[2]  . "' class='graphic' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'><img src='redbox.gif' /></div>\n");

        if ($charttype == "birth") {
            // Draw Head circumference
            $HC_x = $HC_dot_x + $HC_delta_x * $age;
            $HC_y = $HC_dot_y - $HC_delta_y * ($head_circ - 11);
            $point = convertpoint(Array($HC_x,$HC_y));
	    echo("<div id='" . $point[2]  . "' class='graphic' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'><img src='bluedot.gif' /></div>\n");
        }
        else if ($charttype == "2-20") {
            // Draw BMI
            $bmi = $weight/$height/$height*703;
            $bmi_x = $bmi_dot_x + $bmi_delta_x * ($age - 2);
            $bmi_y = $bmi_dot_y - $bmi_delta_y * ($bmi - 10);
	    $point = convertpoint(Array($bmi_x,$bmi_y));
	    echo("<div id='" . $point[2]  . "' class='graphic' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'><img src='bluedot.gif' /></div>\n");
        }

        // fill in data tables

        $datestr = substr($date,0,4)."/".substr($date,4,2)."/".substr($date,6,2);

        //birth to 36 mos chart has 9 rows to fill.
        if ($count < 9 && $charttype == "birth") {
	    $point = convertpoint(Array($datatable_x,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $datestr . "</div>\n");
            $point = convertpoint(Array($datatable_x+$datatable_age_offset,$datatable_y));
            echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $ageinYMD . "</div>\n");
            $point = convertpoint(Array($datatable_x+$datatable_weight_offset,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsWt($weight) . "</div>\n");
            $point = convertpoint(Array($datatable_x+$datatable_height_offset,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsDist($height) . "</div>\n");
            $point = convertpoint(Array($datatable_x+$datatable_hc_offset,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsDist($head_circ) . "</div>\n");
            $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
        }

        // 2 to 20 year-old chart has 7 rows to fill.
        if ($count < 7  && $charttype == "2-20") {
	    $point = convertpoint(Array($datatable_x,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $datestr . "</div>\n");
	    $point = convertpoint(Array($datatable_x+$datatable_age_offset,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $ageinYMD . "</div>\n");
	    $point = convertpoint(Array($datatable_x+$datatable_weight_offset,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsWt($weight) . "</div>\n");
	    $point = convertpoint(Array($datatable_x+$datatable_height_offset,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsDist($height) . "</div>\n");
	    $point = convertpoint(Array($datatable_x+$datatable_bmi_offset,$datatable_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . substr($bmi,0,5) . "</div>\n");
            $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
        }

        // Head Circumference chart has 5 rows to fill in
        if ($count < 5 && $charttype == "birth") {
	    $point = convertpoint(Array($datatable2_x,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $datestr . "</div>\n");
	    $point = convertpoint(Array($datatable2_x+$datatable2_age_offset,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $ageinYMD . "</div>\n");
            $point = convertpoint(Array($datatable2_x+$datatable2_weight_offset,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsWt($weight) . "</div>\n");
            $point = convertpoint(Array($datatable2_x+$datatable2_height_offset,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsDist($height) . "</div>\n");
            $point = convertpoint(Array($datatable2_x+$datatable2_hc_offset,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsDist($head_circ) . "</div>\n");
            $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
        }

        // BMI chart has 14 rows to fill in.
        if ($count < 14 && $charttype == "2-20") {
	    $point = convertpoint(Array($datatable2_x,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $datestr . "</div>\n");
            $point = convertpoint(Array($datatable2_x+$datatable2_age_offset,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . $ageinYMD . "</div>\n");
	    $point = convertpoint(Array($datatable2_x+$datatable2_weight_offset,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsWt($weight) . "</div>\n");
	    $point = convertpoint(Array($datatable2_x+$datatable2_height_offset,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . unitsDist($height) . "</div>\n");
	    $point = convertpoint(Array($datatable2_x+$datatable2_bmi_offset,$datatable2_y));
	    echo("<div id='" . $point[2]  . "' class='label' style='position: absolute; top: " . $point[1]  . "pt; left: " . $point[0] . "pt;'>" . substr($bmi,0,5) . "</div>\n");
            $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
        }

        $count++;
    }

    cssFooter();
    exit;
}
// Done creating CSS HTML output
/******************************/


// create the graph 
$im     = imagecreatefrompng($chartpath.$chart);
$color1 = imagecolorallocate($im, 0, 0, 255); //blue - color scheme imagecolorallocate($im, Red, Green, Blue)
$color  = imagecolorallocate($im, 255, 51, 51); //red

// draw the patient's name 
imagestring($im, 12, $name_x, $name_y, $name, $color);
imagestring($im, 12, $name_x1, $name_y1, $name, $color);

// counter to limit the number of data points plotted
$count = 0;
		
// plot the data points 
foreach ($datapoints as $data) {
    list($date, $height, $weight, $head_circ) = explode('-', $data);
    if ($date == "") { continue; }

    // only plot if we have both weight and heights. Skip if either is 0.
    // Rational is only well visit will need both, sick visit only needs weight
    // for some clinic.
    if ($weight == 0 || $height == 0 ) { continue; }
    
    // get age of patient at this data-point
    // to get data from function get_age including $age, $ageinYMD
    extract(get_age($dob, $date)); 

    // exclude data points that do not belong on this chart
    // for example, a data point for a 18 month old can be excluded 
    // from that patient's 2-20 yr chart
    $daysold = getPatientAgeInDays($dob, $date);
    if ($daysold > (365*3) && $charttype == "birth") { continue; }
    if ($daysold < (365*2) && $charttype == "2-20") { continue; }

    // calculate the x-axis (Age) value
    $x = $dot_x + $delta_x * ($age - $ageOffset);
	
    // Draw Height dot
    $y1 = $dot_y1 - $delta_y1 * ($height - $heightOffset);
    imagefilledellipse($im, $x, $y1, 10, 10, $color);
	  
    // Draw Weight bullseye
    $y2 = $dot_y2 - $delta_y2 * ($weight - $weightOffset);
    imageellipse($im, $x, $y2, 12, 12, $color); // outter ring
    imagefilledellipse($im, $x, $y2, 5, 5, $color); //center dot
	  
    if ($charttype == "birth") {
        // Draw Head circumference
        $HC_x = $HC_dot_x + $HC_delta_x * $age; 
        $HC_y = $HC_dot_y - $HC_delta_y * ($head_circ - 11);
        imagefilledellipse($im, $HC_x, $HC_y, 10, 10, $color1);
    }
    else if ($charttype == "2-20") {
        // Draw BMI
        $bmi = $weight/$height/$height*703;
        $bmi_x = $bmi_dot_x + $bmi_delta_x * ($age - 2);
        $bmi_y = $bmi_dot_y - $bmi_delta_y * ($bmi - 10);
        imagefilledellipse($im, $bmi_x, $bmi_y, 10, 10, $color1);
    }
		
    // fill in data tables
    
    $datestr = substr($date,0,4)."/".substr($date,4,2)."/".substr($date,6,2);
    
    //birth to 24 mos chart has 8 rows to fill.
    if ($count < 8 && $charttype == "birth") {
        imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $color);
        imagestring($im, 2, ($datatable_x+$datatable_age_offset), $datatable_y, $ageinYMD, $color);
        imagestring($im, 2, ($datatable_x+$datatable_weight_offset), $datatable_y, unitsWt($weight), $color);
        imagestring($im, 2, ($datatable_x+$datatable_height_offset), $datatable_y, unitsDist($height), $color);
        imagestring($im, 2, ($datatable_x+$datatable_hc_offset), $datatable_y, unitsDist($head_circ), $color);
        $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
    }

    // 2 to 20 year-old chart has 7 rows to fill.
    if ($count < 7  && $charttype == "2-20") {
        imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $color);
        imagestring($im, 2, ($datatable_x+$datatable_age_offset), $datatable_y, $ageinYMD, $color);
        imagestring($im, 2, ($datatable_x+$datatable_weight_offset), $datatable_y, unitsWt($weight), $color);
        imagestring($im, 2, ($datatable_x+$datatable_height_offset), $datatable_y, unitsDist($height), $color);
        imagestring($im, 2, ($datatable_x+$datatable_bmi_offset), $datatable_y, substr($bmi,0,5), $color);
        $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
    }

    // Head Circumference chart has 5 rows to fill in
    if ($count < 5 && $charttype == "birth") {
        imagestring($im, 2, $datatable2_x, $datatable2_y, $datestr, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_age_offset), $datatable2_y, $ageinYMD, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_weight_offset), $datatable2_y, unitsWt($weight), $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_height_offset), $datatable2_y, unitsDist($height), $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_hc_offset), $datatable2_y, unitsDist($head_circ), $color);
        $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"			
    }

    // BMI chart has 14 rows to fill in.
    if ($count < 14 && $charttype == "2-20") {
        imagestring($im, 2, $datatable2_x, $datatable2_y, $datestr, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_age_offset), $datatable2_y, $ageinYMD, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_weight_offset), $datatable2_y, unitsWt($weight), $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_height_offset), $datatable2_y, unitsDist($height), $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_bmi_offset), $datatable2_y, substr($bmi,0,5), $color);
        $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
    }

    $count++;	
}

if ($_GET['pdf'] == 1) {
    require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
    $pdf =& new Cezpdf("LETTER");
    $pdf->ezSetMargins(0,0,0,0);

    // we start with one large image, break it into two pages
    $page1 = imagecreate((imagesx($im)/2),imagesy($im));
    $page2 = imagecreate((imagesx($im)/2),imagesy($im));
    imagecopy($page1, $im, 0,0, 0,0,(imagesx($im)/2),imagesy($im));
    imagecopy($page2, $im, 0,0, (imagesx($im)/2),0,imagesx($im),imagesy($im));
    imagedestroy($im);

    // each page is built
    $tmpfilename = tempnam("/tmp", "oemr");
    imagepng($page1,$tmpfilename);
    imagedestroy($page1);
    $pdf->ezImage($tmpfilename);
    $pdf->ezNewPage();
    imagepng($page2,$tmpfilename);
    imagedestroy($page2);
    $pdf->ezImage($tmpfilename);
    
    // temporary file is removed
    unlink($tmpfilename);

    // output the PDF
    $pdf->ezStream();
}
else {
    // older style chart that is simply a PNG image
    header("Content-type: image/png"); 
    imagepng($im);
    imagedestroy($im);
}
?>
