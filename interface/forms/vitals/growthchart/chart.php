<?php

// be careful with the relative paths here...

include_once ("../../../../interface/globals.php");
include_once ($GLOBALS['fileroot']."/library/patient.inc");
$chartpath = $GLOBALS['fileroot']."/interface/forms/vitals/growthchart/";

$name = "";
$pid = $_GET['pid'];
if (isset($pid) && is_numeric($pid)) {
    $patient_data = getPatientData($pid, "fname, lname, sex, DATE_FORMAT(DOB,'%Y%m%d') as DOB");
    $nowAge = getPatientAge($patient_data['DOB']);
    $name = $patient_data['fname'] ." ".$patient_data['lname'];
}

$name_x = 650;
$name_y = 60;
$name_x1 = 1650;
$name_y1 = 60;

$ageOffset = 0;
$heightOffset = 0;
$weightOffset = 0;

if ($pid == "") {
    // no pid? no graph for you.
    echo "<p>Missing PID. Please close this window.</p>";
    exit;
}
elseif (preg_match('/month/', $nowAge)) {
    // current patient age <= 24 months

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

    if (preg_match('/^male/i', $patient_data['sex'])) { $chart = "birth-36mos_boys_HC.png"; }
    elseif (preg_match('/^female/i', $patient_data['sex'])) { $chart = "birth-36mos_girls_HC.png"; }

    $ageOffset = 0;
    $heightOffset = 15; // Substract 15 because the graph starts at 15 inches
    $weightOffset = 4;  // graph starts at 4 lbs
    
    // pixel positions and offsets for data table
    $datatable_x = 357;
    $datatable_age_offset = 90;
    $datatable_weight_offset = 160;
    $datatable_height_offset = 240;
    $datatable_hc_offset = 320;
    $datatable_y = 1021;
    $datatable_y_increment = 17;
    
    // pixel positions and offsets for head-circ data table
    $datatable2_x = 1375;
    $datatable2_age_offset = 90;
    $datatable2_weight_offset = 140;
    $datatable2_height_offset = 210;
    $datatable2_hc_offset = 280;
    $datatable2_y = 1092;
    $datatable2_y_increment = 18;
}	
elseif ($nowAge >=2 && $nowAge <= 20) {
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

    if (preg_match('/^male/i', $patient_data['sex'])) { $chart = "2-20yo_boys_BMI.png"; }
    elseif (preg_match('/^female/i', $patient_data['sex'])) { $chart = "2-20yo_girls_BMI.png"; }

    $ageOffset = 2;
    $heightOffset = 30;
    $weightOffset = 14;
   
    // pixel positions and offsets data table
    $datatable_x = 96;
    $datatable_age_offset = 90;
    $datatable_weight_offset = 180;
    $datatable_height_offset = 270;
    $datatable_bmi_offset = 360;
    $datatable_y = 188;
    $datatable_y_increment = 18;
    
    // pixel positions and offsets for BMI data table
    $datatable2_x = 1071;
    $datatable2_age_offset = 90;
    $datatable2_weight_offset = 140;
    $datatable2_height_offset = 210;
    $datatable2_bmi_offset = 280;
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

// plot the data points 
foreach (explode('~', $_GET['data']) as $data) {
    list($date, $height, $weight, $head_circ) = explode('-', $data);
    if ($date == "") { continue; }

    // determine age of person at the time of the data-sample
    if (preg_match('/month/', $nowAge)) {
        $age = getPatientAge($patient_data['DOB'], $date);
        $age = preg_replace("/\D/", "", $age); // remove non-digit characters
    }
    else { $age = getPatientAge($patient_data['DOB'], $date); }

    // calculate the x-axis (Age) value
    $x = $dot_x + $delta_x * ($age - $ageOffset);

    // Draw Height dot
    $y1 = $dot_y1 - $delta_y1 * ($height - $heightOffset);
    imagefilledellipse($im, $x, $y1, 10, 10, $color);
  
    // Draw Weight bullseye
    $y2 = $dot_y2 - $delta_y2 * ($weight - $weightOffset);
    imageellipse($im, $x, $y2, 12, 12, $color); // outter ring
    imagefilledellipse($im, $x, $y2, 5, 5, $color); //center dot

    // Draw BMI
    $bmi = $weight/$height/$height*703;
    $bmi_x = $bmi_dot_x + $bmi_delta_x * ($age - 2);
    $bmi_y = $bmi_dot_y - $bmi_delta_y * ($bmi - 10);
    imagefilledellipse($im, $bmi_x, $bmi_y, 10, 10, $color1);
  
    // Draw Head circumference
    $HC_x = $HC_dot_x + $HC_delta_x * $age; 
    $HC_y = $HC_dot_y - $HC_delta_y * ($head_circ - 11);
    imagefilledellipse($im, $HC_x, $HC_y, 10, 10, $color1);

    $datestr = substr($date,0,4)."/".substr($date,4,2)."/".substr($date,6,2);

    // fill in data table
    imagestring($im, 2, $datatable_x, $datatable_y, $datestr, $color);
    imagestring($im, 2, ($datatable_x+$datatable_age_offset), $datatable_y, $age, $color);
    imagestring($im, 2, ($datatable_x+$datatable_weight_offset), $datatable_y, $weight, $color);
    imagestring($im, 2, ($datatable_x+$datatable_height_offset), $datatable_y, $height, $color);
    if (preg_match('/month/', $nowAge))
        imagestring($im, 2, ($datatable_x+$datatable_hc_offset), $datatable_y, $head_circ, $color);
    else
        imagestring($im, 2, ($datatable_x+$datatable_bmi_offset), $datatable_y, substr($bmi,0,5), $color);
    $datatable_y = $datatable_y + $datatable_y_increment; // increment the datatable "row pointer"

    // fill in the bmi/hc data table
    imagestring($im, 2, $datatable2_x, $datatable2_y, $datestr, $color);
    imagestring($im, 2, ($datatable2_x+$datatable2_age_offset), $datatable2_y, $age, $color);
    imagestring($im, 2, ($datatable2_x+$datatable2_weight_offset), $datatable2_y, $weight, $color);
    imagestring($im, 2, ($datatable2_x+$datatable2_height_offset), $datatable2_y, $height, $color);
    if (preg_match('/month/', $nowAge))
        imagestring($im, 2, ($datatable2_x+$datatable2_hc_offset), $datatable2_y, $head_circ, $color);
    else
        imagestring($im, 2, ($datatable2_x+$datatable2_bmi_offset), $datatable2_y, substr($bmi,0,5), $color);
    $datatable2_y = $datatable2_y + $datatable2_y_increment; // increment the datatable2 "row pointer"
}
   
header("Content-type: image/png"); 
imagepng($im);
imagedestroy($im);
?>
