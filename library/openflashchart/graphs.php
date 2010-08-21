<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Flexible script for graphing entities in OpenEMR
//   Currently set up to set up vitals, and can be expanding
//   to graph other stuff

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once($GLOBALS['srcdir'] . "/openflashchart/php-ofc-library/open-flash-chart.php");
require_once($GLOBALS['srcdir'] . "/formdata.inc.php");

// Conversion functions/constants
function convertFtoC($a) {
  return ($a-32)*0.5556;  
}
function getLbstoKgMultiplier() {
  return 0.45359237;
}
function getIntoCmMultiplier() {
  return 2.54;   
}
function getIdealYSteps($a) {
  if ($a>1000) {
    return 200;
  }
  else if ($a>500) {
    return 100;  
  }
  else if ($a>100) {
    return 20;
  }
  else if ($a>50) {
    return 10;
  }
  else {
    return 5;
  }
}

// Collect passed variable(s)
$params = trim($_GET['params']);
$paramsArray = explode("___",$params);
$name = $paramsArray[0];
$table = $paramsArray[1];

//Make title (use case statement) and prepare other specific conversion/customizations
switch ($name) {
  case "weight":
    $titleGraph = xl("Weight")." (".xl("lbs").")";
    break;
  case "weight_metric":
    $titleGraph = xl("Weight")." (".xl("kg").")";
    $multiplier = getLbstoKgMultiplier();
    $name = "weight";
    break;
  case "height":
    $titleGraph = xl("Height")." (".xl("in").")";
    break;
  case "height_metric":
    $titleGraph = xl("Height")." (".xl("cm").")";
    $multiplier = getIntoCmMultiplier();
    $name = "height";
    break;
  case "bps":
    $titleGraph = xl("Blood Pressure")." (".xl("mm/hg").")";
    $titleGraphLine1 = xl("BP Systolic");
    $titleGraphLine2 = xl("BP Diastolic");
    break;
  case "bpd":
    $titleGraph = xl("Blood Pressure")." (".xl("mm/hg").")";
    $titleGraphLine1 = xl("BP Diastolic");
    $titleGraphLine2 = xl("BP Systolic");
    break;
  case "pulse":
    $titleGraph = xl("Pulse")." (".xl("per min").")";
    break;
  case "respiration":
    $titleGraph = xl("Respiration")." (".xl("per min").")";
    break;
  case "temperature":
    $titleGraph = xl("Temperature")." (".xl("F").")";
    break;
  case "temperature_metric":
    $titleGraph = xl("Temperature")." (".xl("C").")";
    $isConvertFtoC = 1;
    $name="temperature";
    break;
  case "oxygen_saturation":
    $titleGraph = xl("Oxygen Saturation")." (".xl("%").")";
    break;
  case "head_circ":
    $titleGraph = xl("Head Circumference")." (".xl("in").")";
    break;
  case "head_circ_metric":
    $titleGraph = xl("Head Circumference")." (".xl("cm").")";
    $multiplier = getIntoCmMultiplier();
    $name="head_circ";
    break;
  case "waist_circ":
    $titleGraph = xl("Waist Circumference")." (".xl("in").")";
    break;
  case "waist_circ_metric":
    $titleGraph = xl("Waist Circumference")." (".xl("cm").")";
    $multiplier = getIntoCmMultiplier();
    $name="waist_circ";
    break;
  case "BMI":
    $titleGraph = xl("BMI")." (".xl("kg/m^2").")";
    break;
  default:
    
}

// acl checks here
//  For now, only allow access for med aco.
if (!acl_check('patients', 'med')) {
  exit;
}

// Collect info
if ($table) {
  // Collect the pertinent info and ranges
  $values = SqlStatement("SELECT " . add_escape_custom($name) . ", " .
                         "UNIX_TIMESTAMP(date) as unix_date " .
                         "FROM " .add_escape_custom($table) . " " .
			 "WHERE pid=? ORDER BY date", array($pid) );
  $ranges = SqlQuery("SELECT MAX(CONVERT(" . add_escape_custom($name) . ",SIGNED)) AS " .
                   "max_" . add_escape_custom($name) . ", " .
                   "MAX(UNIX_TIMESTAMP(date)) as max_date, " .
                   "MIN(UNIX_TIMESTAMP(date)) as min_date  " .
                   "FROM " .add_escape_custom($table) . " " .
		   "WHERE pid=?", array($pid) );
}
else {
  exit;
}

// If less than 2 values, then exit
if (sqlNumRows($values) < 2) {
  exit;
}

// If blood pressure, then collect the other reading to allow graphing both in same graph
$isBP = 0;
if ($name == "bps" || $name == "bpd") {
  // Set BP flag and collect other pressure reading
  $isBP = 1;
  if ($name == "bps") $name_alt = "bpd";
  if ($name == "bpd") $name_alt = "bps";
  // Collect the pertinent vitals and ranges.
  $values_alt = SqlStatement("SELECT " . add_escape_custom($name_alt) . ", " .
                             "UNIX_TIMESTAMP(date) as unix_date " .
                             "FROM " .add_escape_custom($table) . " " .
			     "WHERE pid=? ORDER BY date", array($pid) );
  $ranges_alt = SqlQuery("SELECT MAX(CONVERT(" . add_escape_custom($name_alt) . ",SIGNED)) AS " .
                         "max_" . add_escape_custom($name_alt) . ", " .
                         "MAX(UNIX_TIMESTAMP(date)) as max_date, " .
                         "MIN(UNIX_TIMESTAMP(date)) as min_date  " .
                         "FROM " .add_escape_custom($table) . " " .
			 "WHERE pid=?", array($pid) );
}

// Prepare look and feel of data points
$s = new scatter_line( '#DB1750', 2 );
$def = new hollow_dot();
$def->size(4)->halo_size(3)->tooltip('#val#<br>#date:Y-m-d H:i#');
$s->set_default_dot_style( $def );
if ($isBP) {
  //set up the other blood pressure line
  $s_alt = new scatter_line( '#0000FF', 2 );
  $s_alt->set_default_dot_style( $def );
}

// Prepare and insert data
$data = array();
while ($row = sqlFetchArray($values)) {
  if ($row["$name"]) {
    $x=$row['unix_date'];
    if ($multiplier) {
      // apply unit conversion multiplier
      $y=$row["$name"]*$multiplier;
    }
    else if ($isConvertFtoC ) {
      // apply temp F to C conversion
      $y=convertFtoC($row["$name"]);
    }
    else {
     // no conversion, so use raw value
     $y=$row["$name"];
    }
    $data[] = new scatter_value($x, $y);
  }
}
$s->set_values( $data );
if ($isBP) {
  //set up the other blood pressure line
  $data = array();
  while ($row = sqlFetchArray($values_alt)) {
    if ($row["$name_alt"]) {
      $x=$row['unix_date'];
    if ($multiplier) {
      // apply unit conversion multiplier
      $y=$row["$name_alt"]*$multiplier;
    }
    else if ($isConvertFtoC ) {
      // apply temp F to C conversion
      $y=convertFtoC($row["$name_alt"]);
    }
    else {
     // no conversion, so use raw value
     $y=$row["$name_alt"];
    }
      $data[] = new scatter_value($x, $y);
    }
  }
  $s_alt->set_values( $data );
}

// Prepare the x-axis
$x = new x_axis();
$x->set_range( $ranges['min_date'], $ranges['max_date']  );
// Calculate the steps and visible steps
$step=($ranges['max_date'] - $ranges['min_date'])/60;
$step_vis=2;
// do not allow steps to be less than 1 day
if ($step < 86400) {
  $step = 86400;
  $step_vis=1; 
}
$x->set_steps($step);
$labels = new x_axis_labels();
$labels->text('#date:Y-m-d#');
$labels->set_steps($step);
$labels->visible_steps($step_vis);
$labels->rotate(90);
$x->set_labels($labels);

// Prepare the y-axis
$y = new y_axis();
if ($name == "bpd") {
  // in this special case use the alternate ranges (the bps)
  if ($multiplier) {
    // apply unit conversion multiplier
    $maximum = $ranges_alt["max_"."$name_alt"]*$multiplier;
  }
  else if ($isConvertFtoC ) {
    // apply temp F to C conversion
    $maximum = convertFtoC( $ranges_alt["max_"."$name_alt"] );
  }
  else {
    // no conversion, so use raw value
    $maximum = $ranges_alt["max_"."$name_alt"];
  }
}
else {
  if ($multiplier) {
    // apply unit conversion multiplier
    $maximum = $ranges["max_"."$name"]*$multiplier;
  }
  else if ($isConvertFtoC ) {
    // apply temp F to C conversion
    $maximum = convertFtoC( $ranges["max_"."$name"] );
  }
  else {
    // no conversion, so use raw value
    $maximum = $ranges["max_"."$name"];
  }
}
// set the range and y-step
$y->set_range( 0 , $maximum );
$y->set_steps( getIdealYSteps( $maximum ) );

// Build and show the chart
$chart = new open_flash_chart();
$chart->set_title( new Title( $titleGraph ));
if ($isBP) {
  // Set up both bp lines
  $s -> set_key( $titleGraphLine1 , 10 );
  $chart->add_element( $s );
  $s_alt -> set_key( $titleGraphLine2 , 10 );
  $chart->add_element( $s_alt );
}
else {
  // Set up the line
  $chart->add_element( $s );   
}
$chart->set_x_axis( $x );
$chart->add_y_axis( $y );
    
//error_log("Chart: ".$chart->toPrettyString(),0);
    
echo $chart->toPrettyString();
    
?>
