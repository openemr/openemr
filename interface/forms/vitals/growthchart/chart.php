<?php

// be careful with the relative paths here...

include_once ("../../../../interface/globals.php");
include_once ($GLOBALS['fileroot']."/library/patient.inc");
$chartpath = $GLOBALS['fileroot']."/interface/forms/vitals/growthchart/";

$name = "";
$pid = $_GET['pid'];

if ($pid == "") {
    // no pid? no graph for you.
    echo "<p>Missing PID. Please close this window.</p>";
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

/******************************/
/******************************/
/******************************/

$name_x = 650;
$name_y = 60;
$name_x1 = 1650;
$name_y1 = 60;

$ageOffset = 0;
$heightOffset = 0;
$weightOffset = 0;

if ($charttype == 'birth') {
    // Use birth to 36 months chart 

    $dot_x = 180;         //months starts here (pixel)
    $delta_x = 17.36;     //pixels per month  - length
    $dot_y1 = 825;        //height starts here - at 15 inches
    $delta_y1 = 24.89;    //pixels per inch  - height
    $dot_y2 = 1155;       //weight starts here - at 4 lbs
    $delta_y2 = 22.09;    //pixels per pound - weight
	
    $HC_dot_x = 1180;     //months starts here for Head circumference chart
    $HC_delta_x = 17.39;  //pixels per month for Head circumference chart
    $HC_dot_y =  764;     //Head circumference starts here - at 11 inches
    $HC_delta_y = 60.00;  //calculated pixels per inch for head circumference

    if (preg_match('/^male/i', $patient_data['sex'])) { 
        $chart = "birth-36mos_boys_HC.png"; 
    }
    elseif (preg_match('/^female/i', $patient_data['sex'])) { 
        $chart = "birth-36mos_girls_HC.png"; 
    }

    $ageOffset = 0;
    $heightOffset = 15; // Substract 15 because the graph starts at 15 inches
    $weightOffset = 4;  // graph starts at 4 lbs
    
    // pixel positions and offsets for data table
    $datatable_x = 357;
    $datatable_age_offset = 75;
    $datatable_weight_offset = 160;
    $datatable_height_offset = 240;
    $datatable_hc_offset = 320;
    $datatable_y = 1021;
    $datatable_y_increment = 17;
    
    // pixel positions and offsets for head-circ data table
    $datatable2_x = 1375;
    $datatable2_age_offset = 75;
    $datatable2_weight_offset = 160;
    $datatable2_height_offset = 225;
    $datatable2_hc_offset = 310;
    $datatable2_y = 1092;
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
    }
    elseif (preg_match('/^female/i', $patient_data['sex'])) { 
        $chart = "2-20yo_girls_BMI.png";
    }

    $ageOffset = 2;
    $heightOffset = 30;
    $weightOffset = 14;
   
    // pixel positions and offsets data table
    $datatable_x = 96;
    $datatable_age_offset = 84;
    $datatable_weight_offset = 200;
    $datatable_height_offset = 290;
    $datatable_bmi_offset = 360;
    $datatable_y = 188;
    $datatable_y_increment = 18;
    
    // pixel positions and offsets for BMI data table
    $datatable2_x = 1071;
    $datatable2_age_offset = 73;
    $datatable2_weight_offset = 165;
    $datatable2_height_offset = 230;
    $datatable2_bmi_offset = 310;
    $datatable2_y = 152;
    $datatable2_y_increment = 17;
}

else {
    // bad age data? no graph for you.
    echo "<p>Age data is out of range. </p>";
    exit;
}

// create the graph 
$im     = imagecreatefrompng($chartpath.$chart);
$color1 = imagecolorallocate($im, 0, 0, 255); //blue - color scheme imagecolorallocate($im, Red, Green, Blue)
$color  = imagecolorallocate($im, 255, 51, 51); //red

// draw the patient's name 
imagestring($im, 12, $name_x, $name_y, $name, $color);
imagestring($im, 12, $name_x1, $name_y1, $name, $color);

// counter to limit the number of data points plotted
$count = 0;
		
// sort and plot the data points 
sort($datapoints);
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
    
    //birth to 36 mos chart has 9 rows to fill.
    if ($count < 9 && $charttype == "birth") {
        imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $color);
        imagestring($im, 2, ($datatable_x+$datatable_age_offset), $datatable_y, $ageinYMD, $color);
        imagestring($im, 2, ($datatable_x+$datatable_weight_offset), $datatable_y, $weight, $color);
        imagestring($im, 2, ($datatable_x+$datatable_height_offset), $datatable_y, $height, $color);
        imagestring($im, 2, ($datatable_x+$datatable_hc_offset), $datatable_y, $head_circ, $color);
        $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
    }

    // 2 to 20 year-old chart has 7 rows to fill.
    if ($count < 7  && $charttype == "2-20") {
        imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $color);
        imagestring($im, 2, ($datatable_x+$datatable_age_offset), $datatable_y, $ageinYMD, $color);
        imagestring($im, 2, ($datatable_x+$datatable_weight_offset), $datatable_y, $weight, $color);
        imagestring($im, 2, ($datatable_x+$datatable_height_offset), $datatable_y, $height, $color);
        imagestring($im, 2, ($datatable_x+$datatable_bmi_offset), $datatable_y, substr($bmi,0,5), $color);
        $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"
    }

    // Head Circumference chart has 5 rows to fill in
    if ($count < 5 && $charttype == "birth") {
        imagestring($im, 2, $datatable2_x, $datatable2_y, $datestr, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_age_offset), $datatable2_y, $ageinYMD, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_weight_offset), $datatable2_y, $weight, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_height_offset), $datatable2_y, $height, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_hc_offset), $datatable2_y, $head_circ, $color);
        $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"			
    }

    // BMI chart has 14 rows to fill in.
    if ($count < 14 && $charttype == "2-20") {
        imagestring($im, 2, $datatable2_x, $datatable2_y, $datestr, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_age_offset), $datatable2_y, $ageinYMD, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_weight_offset), $datatable2_y, $weight, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_height_offset), $datatable2_y, $height, $color);
        imagestring($im, 2, ($datatable2_x+$datatable2_bmi_offset), $datatable2_y, substr($bmi,0,5), $color);
        $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
    }

    $count++;	
}
   
header("Content-type: image/png"); 
imagepng($im);
imagedestroy($im);
?>
