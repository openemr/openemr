<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
include_once('../../../library/patient.inc');
include_once('../../../library/options.inc.php');
include_once('../../../library/wmt-v2/wmtprint.inc');
include_once('../../../library/wmt-v2/wmtstandard.inc');
include_once('../../../library/wmt-v2/wmtpatient.class.php');
include_once('../../../library/wmt-v2/printfacility.class.php');
include_once('../../../library/wmt-v2/printappt.class.php');
include_once('../../../library/wmt-v2/printvisit.class.php');
include_once('../../../library/wmt-v2/list_tools.inc');
require_once("../../forms/definable_fee/batch_rpt.php");

use OpenEMR\Core\Header;

$provider = $startdate = $enddate = $reprint = '';
global $css_tweak;

if(!isset($_POST['start'])) $_POST['start'] = '';
if(!isset($_POST['end'])) $_POST['end'] = '';
if(!isset($_POST['reprint'])) $_POST['reprint'] = '';
if(!isset($_POST['form_provider'])) $_POST['form_provider'] = '';
if(!isset($_POST['form_facility'])) $_POST['form_facility'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(empty($_POST['start']) || empty($_POST['end'])) {
  $startdate = date('Y-m-d', time());
  $enddate = date('Y-m-d', (time() + (24*60*60)));
} else {
  $startdate = $_POST['start'];
  $enddate = $_POST['end'];
}
$provider = $_POST['form_provider'];
$facility = $_POST['form_facility'];
$reprint = $_POST['reprint'];

// CALL WITH THE PID TO PRINT ON DEMAND FROM THE SUMMARY SCREEN
if(!isset($_REQUEST['pid'])) {
	$_REQUEST['pid'] = '';
} else {
	$_POST['form_refresh'] = true;
	$_POST['start'] = $startdate;
	$_POST['end'] = $startdate;
}

$css_tweak = strtolower(checkSettingMode('wmt::css_print_override','','definable_fee'));
$css_base = 'wmtprint.css';
$css_style = 'wmtprint.bkk.css';
if($css_tweak) $css_base = "wmtprint.$css_tweak.css";
if($css_tweak) $css_style = "wmtprint.$css_tweak.bkk.css";
?>
<html>

<head>
<!-- title>&nbsp;</title -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="../../../library/wmt-v2/<?php echo $css_base; ?>" type="text/css">
<link rel="stylesheet" href="../../../library/wmt-v2/<?php echo $css_style; ?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<style>

<?php if($css_tweak == 'small') { ?>
@media print {
	td {
		font-size: 10pt;
	}
}
<?php } ?>

@media print {
	.title {
		display: none;
	}
  .pagebreak {
    page-break-after: always;
    border: none;
		padding: none;
		margin: none;
    display: block;
  }
	.no_print {
		display: none;
	}
	.include_checks {
		display: none;
	}
	#report_parameters {
		display: none;
	}
  #superbill_results {
    margin-top: -30px;
  }
}

@media screen {
	.title {
		visibility: visible;
	}
	#superbill_description {
		visibility: visible;
	}
  .pagebreak {
    width: 98%;
    border: 2px dashed black;
		padding: 5px;
  }
	.no_print {
		display: block;
	}
	.include_checks {
		visibility: visible;
	}
	#report_parameters {
		visibility: visible;
	}
}

td.tightFit {
	padding-left: 2px;
	padding-right: 2px;
	padding-top: 1px;
	padding-bottom: 1px;
}
</style>
<script type="text/javascript">
var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';
</script>
</head>

<body class="body_top">
<span class='title'><?php xl('Reports','e'); ?> - <?php xl('Fee Sheets by Appointments','e'); ?></span>

<div id="report_parameters">
<form method="post" id='theform' action="appt_fee_sheets.php">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='pid' id='pid' value='<?php echo $_REQUEST['pid']; ?>'/>
<table>
 <tr>
  <td width='80%;'>
	<div style='float:left'>

	<table class='text'>
		<tr>
		</tr>
			<td class='label'><?php xl('Start Date','e'); ?>:</td>
			<td><input type='text' name='start' id="form_from_date" size='10' value='<?php echo $startdate ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'><?php xl('End Date','e'); ?>:</td>
			<td> <input type='text' name='end' id="form_to_date" size='10' value='<?php echo $enddate ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'> <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="checkbox" name="reprint" id="form_reprint" value="1" <?php echo $reprint ? 'checked' : ''; ?> />
			<label for="form_reprint">&nbsp;Reprint Existing Forms?</label></td>
		<tr>
			<td class="label"><?php xl('Provider','e'); ?>:</td>
			<td>
			<?php
      // Build a drop-down list of providers.
      $query = "SELECT id, username, lname, fname FROM users " .
							"WHERE authorized=1 AND username!='' AND active='1' ".
							// "AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
							// "UPPER(specialty) LIKE '%SUPERVISOR%') ".
							"AND calendar=1 ".
							"ORDER BY lname, fname";
      $ures = sqlStatement($query);

      echo "   <select name='form_provider'>\n";
      echo "    <option value=''";
			if($provider === '') { echo " selected"; }
			echo ">-- " . xl('All') . " --</option>\n";
		// THIS SHOULD NOT EVEN BE POSSIBLE
      echo "    <option value='0'";
			if($provider === '0') { echo " selected"; }
			echo ">-- " . xl('None Assigned') . " --</option>\n";

      while ($urow = sqlFetchArray($ures)) {
        $provid = $urow['id'];
        echo "    <option value='$provid'";
        if ($provid == $provider) echo " selected";
        echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
      }
      echo "   </select>\n";
      ?></td>
			<td class='label'><?php xl('Facility','e'); ?>:</td>
			<td colspan="3"><?php dropdown_facility(strip_escape_custom($facility), 'form_facility'); ?>
			</td>
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'><span><?php xl('Submit','e'); ?></span></a>

					<?php if ($_POST['form_refresh']) { ?>
					<a href="javascript:;" class="css_button" onclick="if(markPrinted()) { window.print(); }"><span><?php xl('Print','e'); ?></span></a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->


<div id="superbill_results">

<?php
if( !(empty($_POST['start']) || empty($_POST['end']))) {

	$binds = array($startdate, $enddate);
  $sql = "SELECT pe.*, pf.*, pc.pc_catname FROM openemr_postcalendar_events ".
		"AS pe LEFT JOIN openemr_postcalendar_categories AS pc USING (pc_catid) ".
		"LEFT JOIN openemr_postcalendar_fee_sheets AS pf ON (pc_pid = pf_pid AND ".
		"pc_aid = pf_aid AND pc_eventDate = pf_eventDate AND ".
		"pc_startTime = pf_startTime) WHERE " .
    "pc_apptstatus != 'x' AND pc_apptstatus != '%' AND " .
    "pc_eventDate BETWEEN CAST(? AS DATE) AND CAST(? AS DATE) ";
	if($provider !== '') {
		$binds[] = $provider;
		$sql .= "AND pc_aid = ? ";
	}
	if($facility !== '') {
		$binds[] = $facility;
		$sql .= "AND pc_facility = ? ";
	}
	if($_REQUEST['pid'] !== '') {
		$binds[] = $_REQUEST['pid'];
		$sql .= "AND pc_pid = ? ";
	} else {
  	$sql .= "AND pc_pid != '' ";
	}
  $sql .= "ORDER BY pc_eventDate, pc_startTime";
	$res = sqlStatementNoLog($sql, $binds);
	$included_forms = array();
	$first = true;
  while($result = sqlFetchArray($res)) {
		if(!$reprint && $result{'pf_printed'}) continue;
		echo "<div class='no_print' style='width: 100%;'>\n";
		echo "<input name='tmp_sheet_".$result{'pc_eid'}."' id='tmp_sheet_".
			$result{'pc_eid'}."' type='checkbox' value='".$result{'pc_eid'}."' onchange='togglePrint(\"".
			$result{'pc_eid'}."\");' checked='checked' />&nbsp;";	
		echo "<label class='bold' for='tmp_sheet_",$result{'pc_eid'};
		echo "'>Include This Fee Sheet";
		echo "</label>\n";
		if($first) {
			echo '<div style="float: right; padding-right: 12px;">';
			echo '<a href="javascript:;" class="link_submit" onclick="CheckAll();">Check All</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll();">Uncheck All</a></div>';
			echo "\n";
		}
		echo "</div>\n";
		if($first) echo "<br>\n";
		$first = false;
		echo "<div class='pagebreak' id='superbill_sheet_".$result{'pc_eid'}."'>\n";
		definable_fee_form_print($result{'pc_pid'}, $result{'pc_eid'}, $result{'pc_aid'}, $result{'pc_facility'}, $result{'pc_catname'}, $result{'pc_eventDate'},substr($result{'pc_startTime'},0,5));
		echo "</div>\n";
		echo "<div class='no_print' style='padding: 4px;'>&nbsp;</div>\n";
  }
	if($first) {
		echo '<br>';
		echo '<div class="no_print">No fee sheets were found for those paramters</div>';
	} else {
		echo '<div class="no_print"><a href="javascript:;" class="link_submit" onclick="CheckAll();">Check All</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll();">Uncheck All</a><br></div>';
		echo "\n";
	}
}
?>
	</div>

</div>
</form>
</body>

<!-- stuff for the popup calendar -->
<script type="text/javascript">

// define ajax error handler
$(function() {
	$.ajaxSetup({
		error: function(jqXHR, exception) {
			if (jqXHR.status === 0) {
				alert('Not connect to network.');
			} else if (jqXHR.status == 404) {
				alert('Requested page not found. [404]');
			} else if (jqXHR.status == 500) {
				alert('Internal Server Error [500].');
			} else if (exception === 'parsererror') {
				alert('Requested JSON parse failed.');
			} else if (exception === 'timeout') {
				alert('Time out error.');
			} else if (exception === 'abort') {
				alert('Ajax request aborted.');
			} else {
				alert('Uncaught Error.\n' + jqXHR.responseText);
			}
		}
	});

	return false;
});

Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

function togglePrint(eid) {
	if(document.getElementById('tmp_sheet_'+eid).checked == true) {
		document.getElementById('superbill_sheet_'+eid).className = "pagebreak";
	} else {
		document.getElementById('superbill_sheet_'+eid).className = "pagebreak no_print";
	}
}

function markPrinted() {
	var output = 'error';
	var f = document.forms[0].elements;
	var id_array = [];
	for(var i=0; i<f.length; i++) {
		if(f[i].name.indexOf('tmp_sheet_') != -1) {
			if(f[i].checked == true) {
				id_array.push(f[i].value);
			}
		}
	}
	if(!id_array.length) {
		alert('No Fee Sheets are Selected, Nothing will Print');
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/mark_fee_sheets.ajax.php",
		data: {
			id: id_array
		},
		success: function(result) {
			if(result['error']) {
				output = false;
				alert('There was a problem marking the sheets as printed\n'+result['error']);
			} else {
				output = result;
			}
		},
		async: true 
	});
	return output;
}

function CheckAll() {
	var f = document.forms[0].elements;
	for(tmp=0; tmp<f.length; tmp++) {
		if(f[tmp].name.indexOf('tmp_sheet_') != -1) {	
			var eid = f[tmp].name.substr(10);
			f[tmp].checked = true;		
			document.getElementById("superbill_sheet_"+eid).className = "pagebreak";
		}
	}
}

function UncheckAll() {
	var f = document.forms[0].elements;
	for(tmp=0; tmp<f.length; tmp++) {
		if(f[tmp].name.indexOf('tmp_sheet_') != -1) {	
			var eid = f[tmp].name.substr(10);
			f[tmp].checked = false;		
			document.getElementById("superbill_sheet_"+eid).className = "pagebreak no_print";
		}
	}
}

</script>
</html>
