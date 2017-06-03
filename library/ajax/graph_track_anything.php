<?php
/**
 * Trending script for graphing objects in track anything module.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Joe Slam <joe@produnis.de>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2010-2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2014 Joe Slam <joe@produnis.de>
 */


require_once(dirname(__FILE__) . "/../../interface/globals.php");

// get $_POSTed data
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

// build labels
$graph_build = array();
$graph_build['data_final'] = xl('Date');
foreach($the_checked_cols as $col) {
    $graph_build['data_final'] .= "\t" . $the_item_names[$col];
}
$graph_build['data_final'] .= "\n";

// build data
foreach($the_checked_cols as $col) {

	// skip NULL or not-numeric entries
	// check if values are numeric
	for ($i = 0; $i < $laenge; $i++){
		if( is_numeric($the_value_array[$col][$i]) ) {
			// is numeric
			$graph_build['data_final'] .= $the_date_array[$i] . "\t" . $the_value_array[$col][$i] . "\n";
			$the_values[] = $the_value_array[$col][$i];
			$the_dates[]  = $the_date_array[$i];
		} else {
			// is NOT numeric, do nothing
		}
	}
}

// Build and send back the json
$graph_build = array();
$graph_build['data_final'] = $data_final;
$graph_build['title'] = $titleGraph;
// Note need to also use " when building the $data_final rather
// than ' , or else JSON_UNESCAPED_SLASHES doesn't work and \n and
// \t get escaped.
echo json_encode($graph_build,JSON_UNESCAPED_SLASHES);