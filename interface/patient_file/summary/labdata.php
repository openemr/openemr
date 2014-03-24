<?php
/**
* How to present clinical parameter.
*
* Copyright (C) 2014 Joe Slam <trackanything@produnis.de>
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
* @author Joe Slam <trackanything@produnis.de>
* @link http://www.open-emr.org
* ---------------------------------------------------------------------------------
*
* this script needs $pid to run...
* 
* if you copy this file to another place,
* make sure you set $path_to_this_script
* to the propper path...


* Prepare your data:
* this script expects propper 'result_code' entries 
* in table 'procedure_results'. If your data miss 
* 'result_code' entries, you won't see anything, 
* so make sure they are there.
* [additionally, the script will also look for 'units', 
* 'range' and 'code_text'. If these data are not available, 
* the script will run anyway...]
* 
* the script will list all available patient's 'result_codes' 
* from table 'procedure_results'. Check those you wish to view. 
* If you see nothing to select, then
*    a) there is actually no lab data of this patient available
*    b) the lab data are missing 'result_code'-entries in table 'procedure_results'
* 

*/
// Some initial api-inputs
$sanitize_all_escapes  = true;
$fake_register_globals = false;
require_once("../../globals.php");
require_once("../../../library/options.inc.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

// Set the path to this script
$path_to_this_script = $rootdir . "/patient_file/summary/labdata.php";


// is this the printable HTML-option?
$printable = $_POST['print'];


// main db-spell
//----------------------------------------
$main_spell  = "SELECT procedure_result.procedure_result_id, procedure_result.result, procedure_result.result_text,  procedure_result.result_code, procedure_result.units, procedure_result.abnormal, procedure_result.range, ";
$main_spell .= "procedure_report.date_collected, procedure_report.review_status, ";
$main_spell .= "procedure_order.encounter_id ";
$main_spell .= "FROM procedure_result ";
$main_spell .= "JOIN procedure_report ";
$main_spell .= "	ON procedure_result.procedure_report_id = procedure_report.procedure_report_id ";
$main_spell .= "JOIN procedure_order ";
$main_spell .= "	ON procedure_report.procedure_order_id = procedure_order.procedure_order_id ";
$main_spell .= "WHERE procedure_result.result_code = ? "; // '?'
$main_spell .= "AND procedure_order.patient_id = ? ";
$main_spell .= "AND procedure_result.result IS NOT NULL ";
$main_spell .= "AND procedure_result.result != ''";
$main_spell .= "ORDER BY procedure_report.date_collected DESC ";
//----------------------------------------

// some styles and javascripts
// ####################################################
echo "<html><head>";
?> 
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css"> 
<link rel="stylesheet" href="<?php echo $web_root; ?>/interface/themes/labdata.css" type="text/css"> 
<script type="text/javascript" src="<?php echo $web_root; ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/openflashchart/js/json/json2.js"></script>
<script type="text/javascript" src="<?php echo $web_root; ?>/library/openflashchart/js/swfobject.js"></script>
<script type="text/javascript" language="JavaScript">
function checkAll(bx) {
    for (var tbls=document.getElementsByTagName("table"), i=tbls.length; i--; )
      for (var bxs=tbls[i].getElementsByTagName("input"), j=bxs.length; j--; )
         if (bxs[j].type=="checkbox")
            bxs[j].checked = bx.checked;
}

// this is automatically called by swfobject.embedSWF()
//------------------------------------------------------
function open_flash_chart_data(){
	return JSON.stringify(data);
}
//------------------------------------------------------


// set up flashvars for ofc
var flashvars = {};
var data;

</script>
<?php ##############################################################################
echo "</head><body class='body_top'>";
echo "<div id='labdata'>";
echo "<h2>" . xlt('Labs') . "</h2>";
echo "<span class='text'>";
// some patient data...
$spell  = "SELECT * ";
$spell .= "FROM patient_data "; 
$spell .= "WHERE id = ?";
//---
$myrow = sqlQuery($spell,array($pid));
	$lastname = $myrow["lname"];
	$firstname  = $myrow["fname"];
	$DOB  = $myrow["DOB"];



if($printable) {
	echo "<table border=0>";
	echo "<tr><td>" . xlt('Patient') . ": </td><td><b>" . text($lastname) . ", " . text($firstname) . "</b></td></tr>";
	echo "<tr><td>" . xlt('Patient ID') . ": </td><td>" . text($pid) . "</td></tr>";
	echo "<tr><td>" . xlt('Date of birth') . ": </td><td>" . text($DOB) . "</td></tr>";
	echo "<tr><td>" . xlt('Access date') . ": </td><td>" . text(date('Y-m-d - H:i:s')) . "</td></tr>";
	echo "</table>";
	}


echo "<div id='reports_list'>";
if(!$printable){
	echo "<form method='post' action='" . $path_to_this_script . "' onsubmit='return top.restoreSession()'>"; 
	// What items are there for patient $pid?
	// -----------------------------------------------
	$value_list = array();
	$value_select = $_POST['value_code']; // what items are checkedboxed?
	$tab = 0;
	echo xlt('Select items') . ": ";
	echo "<table border='1'>";
	echo "<tr><td>";
	
	$spell  = "SELECT DISTINCT procedure_result.result_code AS value_code ";
	$spell .= "FROM procedure_result ";
	$spell .= "JOIN procedure_report ";
	$spell .= "	ON procedure_result.procedure_report_id = procedure_report.procedure_report_id ";
	$spell .= "JOIN procedure_order ";
	$spell .= "	ON procedure_report.procedure_order_id = procedure_order.procedure_order_id ";
	$spell .= "WHERE procedure_order.patient_id = ? ";
	$spell .= "AND procedure_result.result IS NOT NULL ";
	$spell .= "AND procedure_result.result != ''";
	$spell .= "ORDER BY procedure_result.result_code ASC ";
	$query  = sqlStatement($spell,array($pid));
	

	// Select which items to view...
	$i = 0;
	while($myrow = sqlFetchArray($query)){

		echo "<input type='checkbox' name='value_code[]' value=" . attr($myrow['value_code']) . " ";
		if($value_select){
			if (in_array($myrow['value_code'], $value_select)){ echo "checked='checked' ";}
		}
		echo " /> " . text($myrow['value_code']) . "<br />";
		$value_list[$i][value_code] = $myrow['value_code'];
		$i++;	
		$tab++;
		if($tab == 10) {
			echo "</td><td>";
			$tab=0;
		}	
	}
	echo "</tr>";
	echo "</table>";
	echo "</div>";
	
	?><input type='checkbox' onclick="checkAll(this)" /> <?php echo xlt('Toggle All') . "<br/>";
	echo "<table><tr>";
	// Choose output mode [list vs. matrix]
	echo "<td>" . xlt('Select output') . ":</td>";
	echo "<td><input type='radio' name='mode' ";
	$mode = $_POST['mode'];
	if($mode == 'list'){ echo "checked='checked' ";}
	echo " value='list'> " . xlt('List') . "<br>";
	
	echo "<input type='radio' name='mode' ";
	if($mode != 'list'){ echo "checked='checked' ";}
	echo " value='matrix'> " . xlt('Matrix') . "<br>";

	echo "<td></td></td>";
	echo "</tr><tr>";
	echo "<td>";

    echo "<a href='../summary/demographics.php' ";
    if (!$GLOBALS['concurrent_layout']){ echo "target='Main'"; }
    echo " class='css_button' onclick='top.restoreSession()'>";
    echo "<span>" . xlt('Back to Patient') . "</span></a>";

	echo "</td>";
	echo "<td><input type='submit' name='submit' value='" . xla('Submit') . "' /></td>";
	echo "</tr></table>";
	echo "</form>";

} // end "if printable"
	echo "<br><br><hr><br>";
	
// print results of patient's items
//-------------------------------------------
$mode = $_POST['mode'];
$value_select = $_POST['value_code'];
// are some Items selected?
if($value_select){

	// print in List-Mode
	if($mode=='list'){

		$i = 0;
		$item_graph = 0;
		$rowspan = count($value_select);
		echo "<table border='1' cellspacing='3'>";
		echo "<tr>";
		#echo "<th class='list'>Item</td>";
		echo "<th class='list'>" . xlt('Name') . "</th> ";
		echo "<th class='list'>&nbsp;" . xlt('Result') . "&nbsp;</th> ";
		echo "<th class='list'>" . xlt('Range') . "</th> ";
		echo "<th class='list'>" . xlt('Units') . "</th> ";
		echo "<th class='list'>" . xlt('Date') . "</th> ";
		echo "<th class='list'>" . xlt('Review') . "</th> ";
		echo "<th class='list'>" . xlt('Enc') . "</th> ";
		#echo "<th class='list'>resultID</th> ";
		echo "</tr>";
		// get complete data of each item
		foreach($value_select as $this_value){
			// set a plot-spacer
			echo "<tr><td colspan='7'><div id='graph_item_" . attr($item_graph) . "'</td></tr>";
			$value_count = 0;
			$value_array = array(); // reset local array
			$date_array  = array();//  reset local array

			// get data from db
			$spell  = $main_spell;
			$query  = sqlStatement($spell,array($this_value,$pid));	
			while($myrow = sqlFetchArray($query)){
				
				$value_array[0][$value_count] 	= $myrow['result'];
				$date_array[$value_count]	= $myrow['date_collected'];
				$the_item =	$myrow['result_text'];
				echo "<tr>";
				echo "<td class='list_item'>" . text($myrow['result_text']) . "</td>";


				if($myrow['abnormal'] == 'No' || $myrow['abnormal'] == 'no'  || $myrow['abnormal'] == '' || $myrow['abnormal'] == NULL ) {
					echo "<td class='list_result'>&nbsp;&nbsp;&nbsp;" . text($myrow['result']) . "&nbsp;&nbsp;</td>";
				} else {
					echo "<td class='list_result_abnorm'>&nbsp;" ;
					if($myrow['abnormal'] == 'high') {
						echo "+ ";
					} elseif ($myrow['abnormal'] == 'low') {
						echo "- ";
					} else {
						echo "&nbsp;&nbsp;";
					}
					echo text($myrow['result']) . "&nbsp;&nbsp;</td>";
				}
				echo "<td class='list_item'>" . text($myrow['range']) 		. "</td>";
				
				echo "<td class='list_item'>" . generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'),$myrow['units']) . "</td>";
				echo "<td class='list_log'>"  . text($myrow['date_collected']) . "</td>";
				echo "<td class='list_log'>"  . text($myrow['review_status']) . "</td>";
				echo "<td class='list_log'>";
				if(!$printable){
					echo "<a href='../../patient_file/encounter/encounter_top.php?set_encounter=". attr($myrow['encounter_id']) . "' target='RBot'>";
					echo text($myrow['encounter_id']);
					echo "</a>";
				} else {
					echo text($myrow['encounter_id']);
				}
				echo "</td>";
				echo "</tr>";
				$value_count++;
			}
			
			if($value_count > 1 && !$printable) {
				echo "<tr><td colspan='7' align='center'>";
				echo "<input type='button' class='graph_button'  onclick='get_my_graph" . attr($item_graph) . "()' name='' value='" . xla('Plot item') . " \"" . attr($the_item) . "\"'>";
				echo "</td></tr>";
			}
			?>
			<script type="text/javascript">
			// prepare to plot the stuff	
			top.restoreSession();
			function get_my_graph<?php echo attr($item_graph) ?>(){
				var thedates = JSON.stringify(<?php echo json_encode($date_array); ?>);
				var thevalues =  JSON.stringify(<?php echo json_encode($value_array); ?>);
				var theitem = JSON.stringify(<?php echo json_encode(array($the_item)); ?>);
				var thetitle = JSON.stringify(<?php echo json_encode($the_item); ?>);
				var checkboxfake = JSON.stringify(<?php echo json_encode(array(0)); ?>);
				
				$.ajax({ url: '<?php echo $web_root; ?>/library/openflashchart/graph_track_anything.php',
						type: 'POST',
						data: { dates:  thedates, 
								values: thevalues, 
								track:  thetitle,
								items:  theitem,
								thecheckboxes: checkboxfake
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
							"graph_item_<?php echo $item_graph ?>", "650", "200", "9.0.0","",flashvars);  
						},
							error: function (XMLHttpRequest, textStatus, errorThrown) {
							alert(XMLHttpRequest.responseText);
						}	
				}); // end ajax query	
			}
			//------------------------------------------------------------------------
			</script>
			<?php
			echo "<tr><td colspan='9'  class='list_spacer'><hr></td></tr>";
			$item_graph++;
		}
		echo "</table><br>";
	
	}// end if mode = list
	
	//##########################################################################################################################
	if($mode=='matrix'){
	
		$value_matrix = array();
		$datelist = array();
		$i = 0;
		// get all data of patient's items
		foreach($value_select as $this_value){
				
			$spell  = $main_spell;
			$query  = sqlStatement($spell,array($this_value,$pid));	
	
			while($myrow = sqlFetchArray($query)){
				$value_matrix[$i][procedure_result_id] 	= $myrow['procedure_result_id'];
				$value_matrix[$i][result_code] 			= $myrow['result_code'];
				$value_matrix[$i][result_text] 			= $myrow['result_text'];
				$value_matrix[$i][result] 				= $myrow['result'];
				$value_matrix[$i][units] 				= generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'),$myrow['units']) ;
				$value_matrix[$i][range] 				= $myrow['range'];
				$value_matrix[$i][abnormal] 			= $myrow['abnormal'];
				$value_matrix[$i][review_status] 		= $myrow['review_status'];
				$value_matrix[$i][encounter_id] 		= $myrow['encounter_id'];
				$value_matrix[$i][date_collected] 		= $myrow['date_collected'];
				$datelist[] 							= $myrow['date_collected'];	
				$i++;
			}
		}
	
		// get unique datetime
		$datelist = array_unique($datelist);
	
		// sort datetime DESC
		rsort($datelist);
	
		// sort item-data
		foreach($value_matrix as $key => $row) {
			$result_code[$key] = $row['result_code'];
			$date_collected[$key] = $row['date_collected'];
			}
		array_multisort(array_map('strtolower',$result_code), SORT_ASC, $date_collected, SORT_DESC, $value_matrix);
	
		$cellcount = count($datelist);
		$itemcount = count($value_matrix);
	
		// print matrix
		echo "<table border='1' cellpadding='2'>"; 
		echo "<tr>";
		#echo "<th class='matrix'>Item</th>";
		echo "<th class='matrix'>" . xlt('Name') . "</th>";
		echo "<th class='matrix'>" . xlt('Range') . "</th>";
		echo "<th class='matrix'>" . xlt('Unit') . "</th>";
		echo "<th class='matrix_spacer'>|</td>";
		foreach($datelist as $this_date){
			echo "<th width='30' class='matrix_time'>" . text($this_date) . "</th>";	
		}
		echo "</tr>";
	
		$i=0;
		$a=TRUE;
		while($a==TRUE){
			echo "<tr>";
			#echo "<td class='matrix_item'>" . $value_matrix[$i]['result_code'] . "</td>";
			echo "<td class='matrix_item'>" . text($value_matrix[$i]['result_text']) . "</td>";
			echo "<td class='matrix_item'>" . text($value_matrix[$i]['range']) . "</td>";
			echo "<td class='matrix_item'>" . text($value_matrix[$i]['units']) . "</td>";
			echo "<td class='matrix_spacer'> | </td>";
			
			$z=0;
			while($z < $cellcount){
			
				if($value_matrix[$i]['date_collected'] == $datelist[$z]){
					if($value_matrix[$i]['result'] == NULL){
						echo "<td class='matrix_result'> </td>";
					} else {

						if($value_matrix[$i]['abnormal'] == 'No' || $value_matrix[$i]['abnormal'] == 'no'  || $value_matrix[$i]['abnormal'] == '' || $value_matrix[$i]['abnormal'] == NULL ) {
							echo "<td class='matrix_result'>&nbsp;&nbsp;&nbsp;" . text($value_matrix[$i]['result']) . "&nbsp;&nbsp;</td>";	
																	
						} else {
							echo "<td class='matrix_result_abnorm'>&nbsp;&nbsp;" ;
							if($value_matrix[$i]['abnormal'] == 'high') {
								echo "+ ";
							} elseif ($value_matrix[$i]['abnormal'] == 'low') {
								echo "- ";
							} 
							echo text($value_matrix[$i]['result']) . "&nbsp;&nbsp;</td>";

						}
	
					}
					$j = $i;
					$i++;	
					
					if($value_matrix[$i]['result_code'] != $value_matrix[$j]['result_code']){
						$z = $cellcount;
					}			
				} else {
					echo "<td class='matrix_result'>&nbsp;</td>";
				}		
				$z++;
			}
			if( $i == $itemcount){$a=FALSE;}
		}
	
		echo "</table>";
	
	}// end if mode = matrix
} else { // end of "are items selected"
	echo "<p>" . xlt('No parameters selected') . ".</p>"; 
	$nothing = TRUE;
	}


if(!$printable){
	if(!$nothing){
		echo "<p>";
		echo "<form method='post' action='" . $path_to_this_script . "' target='_new' onsubmit='return top.restoreSession()'>";
		echo "<input type='hidden' name='mode' value='". attr($mode) . "'>";	
		foreach($_POST['value_code'] as $this_valuecode) {
			echo "<input type='hidden' name='value_code[]' value='". attr($this_valuecode) . "'>";
		}	
		echo "<input type='submit' name='print' value='" . xla('Print') . "' />";
		echo "</form>";
  	    echo "<br><a href='../summary/demographics.php' ";
		if (!$GLOBALS['concurrent_layout']){ echo "target='Main'"; }
		echo " class='css_button' onclick='top.restoreSession()'>";
		echo "<span>" . xlt('Back to Patient') . "</span></a>";
	}
 

} else {
	echo "<p>" . xlt('End of report') . ".</p>";
}
echo "</span>";
echo "<br><br>";
echo "</div>";
echo "</body></html>";
?>
