<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once('../../globals.php');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/options.inc.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtprint.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printfacility.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printappt.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/printvisit.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
require_once($GLOBALS['incdir'].'/forms/food_handler/batch_rpt.php');

use OpenEMR\Core\Header;

$ORDERHASH = array(
  'patient' => 'lower(p.lname), lower(p.fname), fe.date',
  'pubpid'  => 'lower(p.pubpid), fe.date',
  'time'    => 'fe.date',
	'doctor'  => 'lower(ulast), lower(ufirst), fe.date'
);

if(!isset($_POST['form_orderby'])) $_POST['form_orderby'] = 'time';
$orderby = $ORDERHASH[$_POST['form_orderby']];

if(!isset($_POST['reprint'])) $_POST['reprint'] = '';
if(!isset($_POST['form_provider'])) $_POST['form_provider'] = '';
if(!isset($_POST['form_facility'])) $_POST['form_facility'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';


$last_month = mktime(0,0,0,date('m'),date('d')-2,date('Y'));
$form_from_date= date('Y-m-d', $last_month);
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
} 
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}

$provider = $_POST['form_provider'];
$facility = $_POST['form_facility'];
$reprint = $_POST['reprint'];

// CALL WITH THE PID TO PRINT ON DEMAND FROM THE SUMMARY SCREEN
if(!isset($_REQUEST['pid'])) {
	$_REQUEST['pid'] = '';
} else {
	$_POST['form_refresh'] = true;
	$_POST['form_from_date'] = $form_from_date;
	$_POST['form_to_date'] = $form_to_date;
}

?>
<html>

<head>
<title>Food Handler Certificates</title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtprint.css" type="text/css">

<style>

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
  #certificate_results {
    margin-top: -30px;
  }
	.wmtPrnLabel {
	  font-size: 14px;
	}
	.wmtPrnBody{
	  font-size: 14px;
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

</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.5.js"></script>
<script type="text/javascript">
var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';
</script>
</head>

<body class="body_top">
<span class='title'><?php xl('Reports','e'); ?> - <?php xl('Food Handler Certificates','e'); ?></span>

<div id="report_parameters">
<form method="post" id='theform' action="food_certificates.php">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='pid' id='pid' value='<?php echo $_REQUEST['pid']; ?>'/>
<table>
 <tr>
  <td width='80%;'>
	<div style='float:left'>

	<table class='text'>
		<tr>
		</tr>
			<td class='text'><?php xl('Start Date','e'); ?>:</td>
			<td><input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='text'><?php xl('End Date','e'); ?>:</td>
			<td> <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'> <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  <input type="checkbox" name="reprint" id="form_reprint" value="1" <?php echo $reprint ? 'checked' : ''; ?> />
			<label class="text" for="form_reprint">&nbsp;Reprint Existing Forms?</label></td>
		<tr>
			<td class="text"><?php xl('Provider','e'); ?>:</td>
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
			if($provider === '') echo " selected";
			echo ">-- " . xl('All') . " --</option>\n";

      echo "    <option value='0'";
			if($provider === '0') echo " selected";
			echo ">-- " . xl('None Assigned') . " --</option>\n";

      while ($urow = sqlFetchArray($ures)) {
        $provid = $urow['id'];
        echo "    <option value='$provid'";
        if ($provid == $provider) echo " selected";
        echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
      }
      echo "   </select>\n";
      ?></td>
			<td class='text'><?php xl('Facility','e'); ?>:</td>
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
					<a href="javascript:;" class="css_button" onclick="if(markedToPrint()) { window.print(); }"><span><?php xl('Print','e'); ?></span></a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->


<div id="certificate_results">

<?php
if( !(empty($_POST['form_from_date']) || empty($_POST['form_to_date']))) {

	$from = $form_from_date . ' 00:00:00';
	$to   = $form_to_date   . ' 23:59:59';
	$binds = array();
	$query = "SELECT " .
  "forms.formdir, forms.form_name, forms.deleted, forms.form_id, forms.pid, " .
  "fe.encounter, fe.date, fe.reason, fe.provider_id, fe.supervisor_id, ".
	"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle, ".
	"pcp.lname AS pcplast, pcp.fname AS pcpfirst, pcp.mname AS pcpmiddle, ".
  "p.fname, p.mname, p.lname, p.DOB, p.pubpid, p.pid, p.providerID AS pcpID, " .
	"fh.referral AS printed, a_result, b_result " .
	"FROM forms " .
	"LEFT JOIN form_encounter AS fe USING (encounter) ".
  "LEFT JOIN patient_data AS p ON forms.pid = p.pid " .
  "LEFT JOIN users AS u ON fe.provider_id = u.id " .
  "LEFT JOIN users AS pcp ON p.providerID = pcp.id " .
  "LEFT JOIN form_food_handler AS fh ON forms.form_id = fh.id " .
  "WHERE " .
  "forms.deleted != '1' AND forms.formdir = 'food_handler' " . 
	"AND (a_result IS NOT NULL AND a_result != '') " . 
	"AND (b_result IS NOT NULL AND b_result != '') " .
  "AND fe.date >= ? AND fe.date <= ? ";
	$binds[] = $from;
	$binds[] = $to;
	if ($form_facility) {
  	$query .= "AND fe.facility_id = ? ";
		$binds[] = $form_facility;
	}
	if ($form_provider !== '') {
  	$query .= "AND fe.provider_id = ? ";
		$binds[] = $form_provider;
	}
	$query .= "ORDER BY $orderby";

	$res = sqlStatementNoLog($query, $binds);
	$included_forms = array();
	$first = true;
  while($result = sqlFetchArray($res)) {
		if(!$reprint && $result{'printed'}) continue;
		echo "<div class='no_print' style='width: 100%;'>\n";
		echo "<input name='tmp_sheet_".$result{'form_id'}."' id='tmp_sheet_".
			$result{'form_id'}."' type='checkbox' value='".$result{'form_id'}."' onchange='togglePrint(\"".
			$result{'form_id'}."\");' checked='checked' />&nbsp;";	
		echo "<label class='bold' for='tmp_sheet_",$result{'form_id'};
		echo "'>Include This Certification";
		echo "</label>\n";
		if($first) {
			echo '<div style="float: right; padding-right: 12px;">';
			echo '<a href="javascript:;" class="link_submit" onclick="CheckAll(\'tmp_sheet_\');">Check All</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll(\'tmp_sheet_\');">Uncheck All</a></div>';
			echo "\n";
		}
		echo "</div>\n";
		if($first) echo "<br>\n";
		$first = false;
		echo "<div class='pagebreak' id='certificate_".$result{'form_id'}."'>\n";
		food_handler_form_print($result{'pid'}, $result{'form_id'}, $result{'encounter'});
		echo "</div>\n";
		echo "<div class='no_print' style='padding: 4px;'>&nbsp;</div>\n";
  }
	if($first) {
		echo '<br>';
		echo '<div class="no_print">No certifiacates were found for those parameters</div>';
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

<script type="text/javascript">
<?php include($GLOBALS['srcdir'].'/wmt-v2/ajax/init_ajax.inc.js'); ?>

Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

function togglePrint(eid) {
	if(document.getElementById('tmp_sheet_'+eid).checked == true) {
		document.getElementById('certificate_'+eid).className = "pagebreak";
	} else {
		document.getElementById('certificate_'+eid).className = "pagebreak no_print";
	}
}

function markedToPrint() {
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
		alert('No Certificates are Selected, Nothing will Print');
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ajax/mark_certificates.ajax.php",
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

</script>
</html>
