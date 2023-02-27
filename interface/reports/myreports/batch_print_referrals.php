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

use OpenEMR\Core\Header;

$form_from_date = $form_to_date = $reprint = '';

if(!isset($_POST['start'])) $_POST['start'] = '';
if(!isset($_POST['end'])) $_POST['end'] = '';
if(!isset($_POST['reprint'])) $_POST['reprint'] = '';
if(!isset($_POST['form_provider'])) $_POST['form_provider'] = '';
if(!isset($_POST['form_supervisor'])) $_POST['form_supervisor'] = '';
if(!isset($_POST['form_facility'])) $_POST['form_facility'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_name'])) $_POST['form_name'] = '';
if(empty($_POST['start']) || empty($_POST['end'])) {
  $form_from_date = date('Y-m-d', time());
  $form_to_date = date('Y-m-d', (time() + (24*60*60)));
} else {
  $form_from_date = $_POST['start'];
  $form_to_date = $_POST['end'];
}
$form_provider = $_POST['form_provider'];
$form_supervisor = $_POST['form_supervisor'];
$facility = $_POST['form_facility'];
$reprint = $_POST['reprint'];
$form_name= $_POST['form_name'];

$css_base = 'wmtprint.css';
$css_style = 'wmtprint.bkk.css';
$referral_list = getFormsByType('referral_form');
foreach($referral_list as $frm) {
 include_once('../../forms/'.$frm['form_name'].'/referral_report.php');
}
?>
<html>
<head>
<!-- title>&nbsp;</title -->
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<link rel="stylesheet" href="../../../library/wmt-v2/<?php echo $css_base; ?>" type="text/css">
<link rel="stylesheet" href="../../../library/wmt-v2/<?php echo $css_style; ?>" type="text/css">
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
  #referral_results {
    margin-top: -30px;
  }
}

@media screen {
	.title {
		visibility: visible;
	}
	#referral_description {
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
<script type="text/javascript">
var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';
</script>
</head>

<body class="body_top">
<span class='title'><?php xl('Referrals','e'); ?> - <?php xl('Batch Print Referral Letters','e'); ?></span>

<div id="report_parameters">
<form method="post" id='theform' action="batch_print_referrals.php">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='80%;'>
	<div style='float:left'>

	<table class='text'>
		<tr>
		</tr>
			<td class='label'><?php xl('Start Date','e'); ?>:</td>
			<td><input type='text' name='start' id="form_from_date" size='10' value='<?php echo $form_from_date?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'><?php xl('End Date','e'); ?>:</td>
			<td> <input type='text' name='end' id="form_to_date" size='10' value='<?php echo $form_to_date?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'> <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
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
							"AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
							"UPPER(specialty) LIKE '%SUPERVISOR%') ".
							"AND calendar=1 ".
							"ORDER BY lname, fname";
      $ures = sqlStatement($query);

      echo "   <select name='form_provider'>\n";
      echo "    <option value=''";
			if($form_provider === '') { echo " selected"; }
			echo ">-- " . xl('All') . " --</option>\n";
		// THIS SHOULD NOT EVEN BE POSSIBLE
      echo "    <option value='0'";
			if($form_provider === '0') { echo " selected"; }
			echo ">-- " . xl('None Assigned') . " --</option>\n";

      while ($urow = sqlFetchArray($ures)) {
        $provid = $urow['id'];
        echo "    <option value='$provid'";
        if ($provid == $form_provider) echo " selected";
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


<div id="referral_results">

<?php
$form_orderby='pid';
$query = "SELECT forms.id AS master_id, " .
  "forms.formdir, forms.form_name, forms.deleted, forms.form_id, " .
  "form_encounter.encounter, form_encounter.date, form_encounter.reason, " .
	"form_encounter.provider_id, form_encounter.supervisor_id, ".
	"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle, ".
  "patient_data.fname, patient_data.mname, patient_data.lname, " .
  "patient_data.pubpid, patient_data.pid, patient_data.DOB FROM forms " .
	"LEFT JOIN form_encounter USING (encounter) ".
  "LEFT JOIN patient_data ON forms.pid = patient_data.pid " .
  "LEFT JOIN users AS u ON form_encounter.provider_id = u.id " .
  "WHERE " .
  "forms.deleted != '1' AND ";
$first = true;
if(count($referral_list) > 0) {
	$query .= "( ";	
	foreach($referral_list as $frm) {
		if(!$first) $query .= "OR ";
		$query .= "forms.formdir = '".$frm['form_name']."' ";
		$first = false;
	}
}
if(!$first) $query .= ") ";
if ($form_to_date) {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_from_date 23:59:59' ";
}
if ($facility) {
  $query .= "AND form_encounter.facility_id = '$facility' ";
}
if ($form_provider !== '') {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}
if ($form_supervisor !== '') {
  $query .= "AND form_encounter.supervisor_id = '$form_supervisor' ";
}
$query .= "ORDER BY $form_orderby";

$res = array();
if($_POST['form_refresh']) $res = sqlStatementNoLog($query);

$included_forms = array();
$first = true;
if($_POST['form_refresh']) {
	while($result = sqlFetchArray($res)) {
		$frow = sqlQuery('SELECT form_complete, referral_printed FROM form_'.
				$result{'formdir'}.' WHERE id=?', array($result{'form_id'}));
		if(!isset($frow{'form_complete'})) $frow{'form_complete'} = '';
		if(strtolower($frow{'form_complete'}) != 'a') continue;
		if(!$reprint && $frow{'referral_printed'}) continue;
		if(!FormInRepository($result{'pid'}, $result{'encounter'}, $result{'form_id'}, 'form_'.$result{'formdir'}.'_referral')) continue;
		echo "<div class='no_print' style='width: 100%;'>\n";
		echo "<input name='tmp_letter_".$result{'master_id'}."' id='tmp_letter_".
			$result{'master_id'}."' type='checkbox' value='form_".$result{'formdir'}.
			":".$result{'form_id'}."' onchange='togglePrint(\"".
			$result{'master_id'}."\");' checked='checked' />&nbsp;";	
		echo "<label class='bold' for='tmp_letter_",$result{'master_id'};
		echo "'>Include This Letter";
		echo "</label>\n";
		if($first) {
			echo '<div style="float: right; padding-right: 12px;">';
			echo '<a href="javascript:;" class="link_submit" onclick="CheckAll();">Check All</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll();">Uncheck All</a></div>';
			echo "\n";
		}
		echo "</div>\n";
		if($first) echo "<span style='height: 3px;'></span><br>\n";
		$first = false;
		echo "<div class='pagebreak' id='referral_letter_".$result{'master_id'}."'>\n";
		$this_report = $result{'formdir'} . '_print_referral';
		$this_report($result{'pid'}, $result{'form_id'}, $result{'encounter'});
		echo "</div>\n";
		echo "<div class='no_print' style='padding: 4px;'>&nbsp;</div>\n";
	}
	if($first) {
		echo '<br>';
		echo '<div class="no_print">No referral letters were found for those parameters</div>';
	} else {
		echo '<div class="no_print"><a href="javascript:;" class="link_submit" onclick="CheckAll();">Check All</a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll();">Uncheck All</a><br></div>';
		echo "\n";
	}
}
?>

</div>
</form>
</body>

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

function togglePrint(id) {
	if(document.getElementById('tmp_letter_'+id).checked == true) {
		document.getElementById('referral_letter_'+id).className = "pagebreak";
	} else {
		document.getElementById('referral_letter_'+id).className = "pagebreak no_print";
	}
}

function markPrinted() {
	var output = 'error';
	var f = document.forms[0].elements;
	var id_array = [];
	for(tmp=0; tmp<f.length; tmp++) {
		if(f[tmp].name.indexOf('tmp_letter_') != -1) {	
			if(f[tmp].checked == true) {
				id_array.push(f[tmp].value);
			}
		}
	}
	if(!id_array.length) {
		alert('No Forms are Selected, Nothing will Print');
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/mark_referrals.ajax.php",
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
		if(f[tmp].name.indexOf('tmp_letter_') != -1) {	
			var id = f[tmp].name.substr(11);
			f[tmp].checked = true;		
			document.getElementById("referral_letter_"+id).className = "pagebreak";
		}
	}
}

function UncheckAll() {
	var f = document.forms[0].elements;
	for(tmp=0; tmp<f.length; tmp++) {
		if(f[tmp].name.indexOf('tmp_letter_') != -1) {	
			var id = f[tmp].name.substr(11);
			f[tmp].checked = false;		
			document.getElementById("referral_letter_"+id).className = "pagebreak no_print";
		}
	}
}

</script>
</html>
