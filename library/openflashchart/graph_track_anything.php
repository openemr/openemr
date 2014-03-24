<?php
// Copyright (C) 2010 Brady Miller <brady@sparmy.com>
// Modified 2011 Rod Roark <rod@sunsetsystems.com>
// Modified 2014 Joe Slam <joe@produnis.de>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Flexible script for graphing entities in OpenEMR
//

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once($GLOBALS['srcdir'] . "/openflashchart/php-ofc-library/open-flash-chart.php");
require_once($GLOBALS['srcdir'] . "/formdata.inc.php");

// get $_POSTed data
//+++++++++++++++++++++++
$titleGraph   	  = json_decode($_POST['track'],TRUE);
$the_date_array   = json_decode($_POST['dates'],TRUE);
$the_value_array  = json_decode($_POST['values'],TRUE);
$the_item_names   = json_decode($_POST['items'],TRUE);
$the_checked_cols = json_decode($_POST['thecheckboxes'],TRUE);
// ++++++/end get POSTed data
$laenge = count($the_date_array);

// set up colors for lines to plot
$line_colors[]	= "#a40000";
$line_colors[]	= "#5c3566";
$line_colors[]	= "#204a87";
$line_colors[]	= "#4e9a06";
$line_colors[]	= "#babdb6";
$line_colors[]	= "#0000FF";
$line_colors[]	= "#DB1750";




// check if something was sent
// and quit if not
//-------------------------------
if ($the_checked_cols == NULL) {
	// nothing to plot
	echo "No item checked,\n";
	echo "nothing to plot."; // DEBUG ONLY! COMMENT ME OUT!
	exit;
	}
// end check if NULL data




// get ideal y-axis-steps	
if(!function_exists('getIdealYSteps')) {
	function getIdealYSteps($a) {
		if ($a>1000) {
			return 400;
		} else if ($a>500) {
			return 200;  
		} else if ($a>100) {
			return 40;
		} else if ($a>50) {
			return 20;
		} else {
			return 5;
		}	
	} // end function getIdeal...
} // end if function_exist




// Prepare look and feel of data points
$def = new hollow_dot();
$def->size(4)->halo_size(3)->tooltip('#val#<br>#date:Y-m-d H:i#');


// Build and show the chart
$chart = new open_flash_chart();
$chart->set_title( new Title( $titleGraph ));


// do this for each checked data-column
//-----------------------------------
//#############################################################
foreach($the_checked_cols as $col) {

	// reset loop-arrays
	$the_values = array();
	$the_dates = array();
	$the_data = array();

	
	// skip NULL or not-numeric entries
	// check if values are numeric
	// and change date into UNIX-format
	for ($i = 0; $i < $laenge; $i++){
		if( is_numeric($the_value_array[$col][$i]) ) {
			// is numeric
			$the_values[] = $the_value_array[$col][$i];
			$the_dates[]  = strtotime($the_date_array[$i]); // convert to UNIX-format
			$the_data[] = new scatter_value(strtotime($the_date_array[$i]), $the_value_array[$col][$i]);  // save into array for plotting
		} else {
			// is NOT numeric, do nothing
		}
	}
	// -----------------------------------------------------------
	// all graph-data are now in array $the_data
	// UNIX times (for x-axis-range) are in array $the_dates
	// -----------------------------------------------------------

	
	$s_{$col} = new scatter_line( $line_colors[$col] , 2 );
	$s_{$col}->set_default_dot_style( $def );

	// Prepare and insert data
	$s_{$col}->set_values( $the_data );
	$s_{$col}-> set_key( $the_item_names[$col] , 10 );
	$chart->add_element( $s_{$col} ); 
} // end foreach data-column-------------------------------------
//###############################################################



// get ranges
//--------------------------------------
// dates (for x-axis)
$the_sort = $the_dates;# // UNIX.time
sort($the_sort);
$lowest = $the_sort[0];
rsort($the_sort);
$highest = $the_sort[0];

// get maximum value (for y-axis)
$the_sort = $the_value_array;
foreach($the_checked_cols as $col) {
	rsort($the_sort[$col]);
	$maxima[] = $the_sort[$col][0];
}
rsort($maxima);
$maximum = $maxima[0];
//-----/end get ranges  -----------------


// Prepare the x-axis
$x = new x_axis();
$x->set_range( $lowest, $highest  );

// Calculate the steps and visible steps
$step= ($highest - $lowest)/60;
$step_vis=2;

// do not allow steps to be less than 30 minutes
	if ($step < 26400) { # 86400
			$step = 26400;
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
$y = new y_axis(); // $maximum is already set above

// set the range and y-step
$y->set_range( 0 , $maximum + getIdealYSteps( $maximum ) );
$y->set_steps( getIdealYSteps( $maximum ) );

#	$chart->add_element( $s );   
$chart->set_x_axis( $x );
$chart->add_y_axis( $y );
 

// echo a pretty ofc-string anyway
echo $chart->toPrettyString();  
?>
