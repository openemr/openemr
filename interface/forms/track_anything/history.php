<?php
/**
* Encounter form to track any clinical parameter.
*
* Copyright (C) 2014 Joe Slam <joe@produnis.de>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package OpenEMR
* @author Joe Slam <joe@produnis.de>
* @link http://www.open-emr.org
*/

// initial stuff
//---------------
$sanitize_all_escapes  = true;
$fake_register_globals = false;
require_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
if(!$formid){ 
	$formid = $_POST['formid']; // call from track_anything encounter
	$fromencounter = 1;
	if(!$formid){ 
		$formid = $_GET['formid']; // call from demographic-widget "track_anything_fragement.php"
		$fromencounter = 0;
	}
}

if ($_POST['fromencounter'] != ''){
	$fromencounter = $_POST['fromencounter'];	
}

// get $_POSTed vars
//----------------------
$ASC_DESC = $_POST['ASC_DESC'];

if(!$ASC_DESC) {
	$ASC_DESC = "DESC"; # order DESC by default
}
//---------- end $_POSTed vars


// set up some vars
//-------------------
$items_c 		= 0; 		# (count how many items are tracked)
$items_n 		= array(); 	# (save items names)
$row_gl 		= 0; 		# (global count of data_rows)
$row_lc 		= 0;		# (local count of data_rows)
$hidden_loop 	= '';		# (collects all <input type='hidden'> entries )
$date_global 	= array();	# (collects items datetime for global rows)
$value_global 	= array(); 	# (collects items' values [global array])
$date_local 	= array(); 	# (collects items' datetime for local row)
$value_local 	= array(); 	# (collects item's values [local array])	
$save_item_flag = 0;		# flag to get item_names
$localplot 		= 0;		# flag if local plot-button is shown
$localplot_c	= array();  # dummy counter for localplot
$globalplot		= 0;		# flag if global plot-button is shown
$globalplot_c	= array();	# flag if global plot-button is shown
$track_count	= 0;		# counts tracks and generates div-ids
//-----------end setup vars
		


echo "<html><head>";
// Javascript support and Javascript-functions
//******* **********************************
?> 
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="style.css" type="text/css"> 
<script type="text/javascript" src="<?php echo $web_root; ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/openflashchart/js/json/json2.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/openflashchart/js/swfobject.js"></script>
<script type="text/javascript">
//-------------- checkboxes checked checker --------------------
// Pass the checkbox name to the function
function getCheckedBoxes(chkboxName) {
  var checkboxes = document.getElementsByName(chkboxName);
  var checkedValue = [];
  // loop over them all
  for (var i=0; i<checkboxes.length; i++) {
     // And stick the checked ones onto an array...
     if (checkboxes[i].checked) {
        checkedValue.push(checkboxes[i].value);
     }
  }
  return checkedValue; 
}
//---------------------------------------------------------------


// this is automatically called by swfobject.embedSWF()
//------------------------------------------------------
function open_flash_chart_data(){
	return JSON.stringify(data);
}
//------------------------------------------------------


// set up flashvars for ofc
var flashvars = {};
var data;

// plot the current graph
//------------------------------------------------------
function plot_graph(checkedBoxes, theitems, thetrack, thedates, thevalues, trackCount){
	top.restoreSession();
	return $.ajax({ url: '<?php echo $web_root; ?>/library/openflashchart/graph_track_anything.php',
		     type: 'POST',
		     data: { dates:  thedates, 
				     values: thevalues, 
				     items:  theitems, 
				     track:  thetrack, 
				     thecheckboxes: checkedBoxes
				   },
			 dataType: "json",  
			 success: function(returnData){
				 // ofc will look after a variable named "ofc"
				 // inside of the flashvar
				 // However, we need to set both
				 // data and flashvars.ofc 
				 data=returnData;
				 flashvars.ofc = returnData;
				 // call ofc with proper falshchart
					swfobject.embedSWF('<?php echo $web_root; ?>/library/openflashchart/open-flash-chart.swf', 
					"graph"+trackCount, "650", "200", "9.0.0","",flashvars);  
			},
			error: function (XMLHttpRequest, textStatus, errorThrown) {
				alert(XMLHttpRequest.responseText);
				//alert("XMLHttpRequest="+XMLHttpRequest.responseText+"\ntextStatus="+textStatus+"\nerrorThrown="+errorThrown);
			}
	
	}); // end ajax query	
}
//------------------------------------------------------
</script>
<?php  

//#########################################################
// Here starts webpage-output
//-------------------------------

echo "</head><body class='body_top'>";

echo "<div id='track_anything'>";
// Choose output mode (order ASC vs order DESC)
//---------------------------------------------
echo "<form method='post' action='history.php' onsubmit='return top.restoreSession()'>"; 
echo "<table><tr>";
echo "<td class='menu'><input type='radio' name='ASC_DESC' ";
if($ASC_DESC == 'ASC'){ echo "checked='checked' "; }
echo " value='ASC'> " . xlt('ASC') . " &nbsp;";
echo "<input type='radio' name='ASC_DESC' ";
if($ASC_DESC != 'ASC'){ echo "checked='checked' ";}
echo " value='DESC'> " . xlt('DESC');
echo "</td>";
echo "<td class='menu'><input class='graph_button' type='submit' name='submit' value='" . xla('Order Tracks') . "' /></td>";
echo "<input type='hidden' name='formid' value='" . attr($formid) . "'>";
echo "<input type='hidden' name='fromencounter' value='" . attr($fromencounter) . "'>";
//--------/end Choose output ASC/DESC

// go to encounter or go to demographics
//---------------------------------------------
if($fromencounter == 1) {
	echo "<td>&nbsp;&nbsp;&nbsp;<a class='css_button' href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl' onclick='top.restoreSession()'><span>".xlt('Back to encounter')."</span></a></td>";
	}
if($fromencounter == 0) {
	echo "<td>&nbsp;&nbsp;&nbsp;<a href='../../patient_file/summary/demographics.php' ";
    if (!$GLOBALS['concurrent_layout']){ echo "target='Main'"; }
    echo " class='css_button' onclick='top.restoreSession()'>";
    echo "<span>" . xlt('Back to Patient') . "</span></a></td>";
	}
//---------------------------------------------

echo "</tr></table>";
echo "</form>";
echo "<hr>";


// get name and id of selected track
$spell  = "SELECT form_track_anything.procedure_type_id AS the_id, form_track_anything_type.name AS the_name ";
$spell .= "FROM form_track_anything "; 
$spell .= "INNER JOIN form_track_anything_type ON form_track_anything.procedure_type_id = form_track_anything_type.track_anything_type_id ";
$spell .= "WHERE id = ? AND form_track_anything_type.active = 1";
//---
$myrow = sqlQuery($spell, array($formid));
	$the_procedure = $myrow["the_id"];
	$the_procedure_name = $myrow["the_name"];


//echo "<div>";

// print out track report
//###########################
echo "<h3>" . xlt('Track Report') . "</h3>";
echo "<table id='track_anything' border=0>";
echo "<tr><td> " . xlt('Track') . ": </td><td>" . text($the_procedure_name) . "</td></tr>";
echo "</table>";
echo "<hr>";




// get all track data, sort it by date $ASC_DESC, print it out grouped by encounter
$spell  = "SELECT form_track_anything.id, forms.encounter, form_track_anything_results.track_timestamp AS sortdate ";
$spell .= "FROM form_track_anything ";
$spell .= "JOIN forms ON form_track_anything.id = forms.form_id ";
$spell .= "JOIN form_track_anything_results ON form_track_anything.id = form_track_anything_results.track_anything_id ";
$spell .= "WHERE form_track_anything.procedure_type_id = ?  ";
$spell .= "AND forms.formdir = 'track_anything' AND forms.pid = ? ";
$spell .= "GROUP BY id ";
$spell .= "ORDER BY sortdate " . escape_sort_order($ASC_DESC);
//---
$query = sqlStatement($spell,array($the_procedure,$pid));
while($myrow = sqlFetchArray($query)){
	$the_track = $myrow["id"];
	$the_encounter = $myrow["encounter"];
	$track_count++;
	
	// reset local arrays;
	$date_local 	= array(); 	# (collects items' datetime for local row)
	$value_local 	= array(); 	# (collects item's values [local array])
	$localplot_c 	= array(); // counter to decide if graph-button is shown
	$shownameflag 	= 0; // show table-head	?
	$localplot	  	= 0; // show graph-button?	
	$col 			= 0; // how many Items per row	
	$row_lc 		= 0; // local row counter
	//--- end reset local arrays
	
	
	// get every single tracks
	echo "<div id='graph" . attr($track_count) . "'> </div><br>"; // here goes the graph
	echo "<small>[" . xlt('Data from') . " ";
	echo "<a href='../../patient_file/encounter/encounter_top.php?set_encounter=" . attr($the_encounter) . "' target='RBot'>" . xlt('encounter') . " #" . text($the_encounter) . "</a>]";
	echo "</small>";
	echo "<table border='1'>";
	$spell2  = "SELECT DISTINCT track_timestamp ";
	$spell2 .= "FROM form_track_anything_results ";
	$spell2 .= "WHERE track_anything_id = ? "; 
	$spell2 .= "ORDER BY track_timestamp " . escape_sort_order($ASC_DESC);
	$query2 = sqlStatement($spell2, array($the_track));
	while($myrow2 = sqlFetchArray($query2)){ 
		$thistime = $myrow2['track_timestamp'];
		$shownameflag++;
		
		// get data of this specific track
		$spell3  = "SELECT form_track_anything_results.itemid, form_track_anything_results.result, form_track_anything_type.name AS the_name ";
		$spell3 .= "FROM form_track_anything_results ";
		$spell3 .= "INNER JOIN form_track_anything_type ON form_track_anything_results.itemid = form_track_anything_type.track_anything_type_id ";
		$spell3 .= "WHERE track_anything_id = ? AND track_timestamp = ? AND form_track_anything_type.active = 1 ";
		$spell3 .= "ORDER BY form_track_anything_results.track_timestamp " . escape_sort_order($ASC_DESC) . ", ";
		$spell3 .= " form_track_anything_type.position ASC, the_name ASC ";
		$query3  = sqlStatement($spell3, array($the_track, $thistime));
		
		// print local <table>-heads
		// ----------------------------
		if ($shownameflag==1){
			echo "<tr><th class='time'>" . xlt('Time') . "</th>";
			while($myrow3 = sqlFetchArray($query3)){
				echo "<th class='item'>&nbsp;" . text($myrow3['the_name']) . "&nbsp;</th>";	//  

				if($save_item_flag == 0) {
					$items_n[$items_c] = $myrow3['the_name']; // save item names
					$items_c++; // count number of items
				}	
				$col++;
			}
			$save_item_flag++;
			echo "</tr>";		
		}
		//-----/end print local table head
		
		// data-rows
		echo "<tr><td class='time'>&nbsp;" . text($thistime) . "</td>";				
		$col_i = 0; // how many columns
		$date_global[$row_gl] = $thistime; // save datetime into global array
		$date_local[$row_lc]  = $thistime; // save datetime into local array

		$query3  = sqlStatement($spell3, array($the_track, $thistime));
		while($myrow3 = sqlFetchArray($query3)){
			echo "<td class='item'>&nbsp;" . text($myrow3['result']) . "&nbsp;</td>";
			if (is_numeric($myrow3['result'])) {
					$value_global[$col_i][$row_gl] = $myrow3['result']; // save value into global array 
					$value_local[$col_i][$row_lc]  = $myrow3['result']; // save value into local array 
			}
			$col_i++;
		} 
		echo "</tr>";
		$row++;
		$row_gl++;
		$row_lc++;
	}

	// check for each column if there is any numeric data
	// and show checkbox if so...
	//----------------------------------------------------
	echo "<tr>";
	echo "<td class='check'>" . xlt('Check items to graph') . " </td>"; // 
	for ($col_i = 0; $col_i < $col; $col_i++){
		echo "<td class='check'>";
		for ($row_b=0; $row_b <$row_lc; $row_b++) {
			if(is_numeric($value_local[$col_i][$row_b])){ 
				$localplot_c[$col_i]++; // count more than 1 to show graph-button
				$globalplot_c[$col_i]++;
			}
		}
		
		// show graph-checkbox only if we have more than 1 valid data
		if ($localplot_c[$col_i] > 1 || $globalplot_c[$col_i] > 1){ 
			echo "<input type='checkbox' name='check_col" . attr($track_count) . "' value='" . attr($col_i) . "'>";
			if ($localplot_c[$col_i] > 1) {
				$localplot++;
			}
			$globalplot++;
		}
		echo "</td>";
	}	


	echo "</tr>";
	echo "</tr></table>";
	echo "<table>";
	echo "<tr>";
	echo "<td class='check'>" . xlt('With checked items plot') . ":</td>"; // 
	echo "<td class='check'>";
	if ($localplot > 0){ 
		echo "<input type='button' class='graph_button'  onclick='get_my_graph" . attr($track_count) . "(\"local\")' name='' value='" . xla('encounter data') . "'>";
	}
	if ($localplot > 0 && $globalplot > 0){
			echo "<br>";
	}
	if ($globalplot > 0){ 
		echo "<input type='button' class='graph_button'  onclick='get_my_graph" . attr($track_count) . "(\"global\")' name='' value='" . xla('data of all encounters so far') . "'>";
	}
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "<br><hr>";
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// onClick create graph javascript method
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
?>
<script type="text/javascript">	
function get_my_graph<?php echo attr($track_count); ?>(where){
	top.restoreSession();
	if(where=="local"){
		//alert("local");
		var thedates = JSON.stringify(<?php echo json_encode($date_local); ?>);
		var thevalues =  JSON.stringify(<?php echo json_encode($value_local); ?>);
	}
	if(where=="global"){
		//alert("global");
		var thedates = JSON.stringify(<?php echo json_encode($date_global); ?>);
		var thevalues =  JSON.stringify(<?php echo json_encode($value_global); ?>);
	}

	var checkedBoxes = JSON.stringify(getCheckedBoxes("check_col<?php echo attr($track_count) ?>"));
	var theitems = JSON.stringify(<?php echo json_encode($items_n); ?>);
	var thetrack = JSON.stringify(<?php echo json_encode($the_procedure_name); ?>);
	plot_graph(checkedBoxes, theitems, thetrack, thedates, thevalues, <?php echo attr($track_count); ?>);
}
</script>
<?php
// ~~~~~~~~~~~~~~~~~ / end javascript method ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
} // end while get all trackdata


//######################################################### 
// This is the End
echo "<p>" . xlt('End of report') . ".</p>";

echo "<hr>";

// Choose output mode (order ASC vs order DESC)
//---------------------------------------------
echo "<form method='post' action='history.php' onsubmit='return top.restoreSession()'>"; 
echo "<table><tr>";
echo "<td class='menu'><input type='radio' name='ASC_DESC' ";
if($ASC_DESC == 'ASC'){ echo "checked='checked' "; }
echo " value='ASC'> " . xlt('ASC') . " &nbsp;";
echo "<input type='radio' name='ASC_DESC' ";
if($ASC_DESC != 'ASC'){ echo "checked='checked' ";}
echo " value='DESC'> " . xlt('DESC');
echo "</td>";
echo "<td class='menu'><input class='graph_button' type='submit' name='submit' value='" . xlt('Order Tracks') . "' /></td>";
echo "<input type='hidden' name='formid' value='" . attr($formid) . "'>";
echo "<input type='hidden' name='fromencounter' value='" . attr($fromencounter) . "'>";
//--------/end Choose output ASC/DESC


// go to encounter or go to demographics
//---------------------------------------------
if($fromencounter == 1) {
	echo "<td>&nbsp;&nbsp;&nbsp;<a class='css_button' href='".$GLOBALS['webroot'] . "/interface/patient_file/encounter/$returnurl' onclick='top.restoreSession()'><span>".xlt('Back to encounter')."</span></a></td>";
	}
if($fromencounter == 0) {
	echo "<td>&nbsp;&nbsp;&nbsp;<a href='../../patient_file/summary/demographics.php' ";
    if (!$GLOBALS['concurrent_layout']){ echo "target='Main'"; }
    echo " class='css_button' onclick='top.restoreSession()'>";
    echo "<span>" . htmlspecialchars(xl('Back to Patient'),ENT_NOQUOTES) . "</span></a></td>";
	}
//---------------------------------------------
echo "</tr></table>";
echo "</form>";
echo "</div>";
//------------ that's it, bye
formFooter();
?>
