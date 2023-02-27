<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows past encounters with filtering and sorting.

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/billing.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/formhunt.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/approve.inc');
$hold_approve=true;
set_time_limit(0);

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'patient' => 'lower(p.lname), lower(p.fname), fe.date',
  'pubpid'  => 'lower(p.pubpid), fe.date',
  'time'    => 'fe.date',
	'doctor'  => 'lower(ulast), lower(ufirst), fe.date'
);
$pop_forms = getFormsByType(array('pop_form'));
$archive_list = getFormsByType(array('archive_form'));
$referral_list = getFormsByType(array('referral_form'));
$lock_list = getFormsByType(array('lock_form'));
$custom_forms = getCustomForms();
$approve_dt_catalog = array();
$pop_used = checkSettingMode('wmt::form_popup');
$client_id = '';
if(isset($GLOBALS['wmt::client_id'])) $client_id = $GLOBALS['wmt::client_id'];
if($client_id == '') $client_id = checkSettingMode('wmt::client_id');
if(!isset($GLOBALS['wmt::enc_link_fee'])) $GLOBALS['wmt::enc_link_fee'] = '';
if(!isset($GLOBALS['wmt::enc_direct_to_fee'])) $GLOBALS['wmt::enc_direct_to_fee'] = '';

foreach($archive_list as $form) {
	// Build a catalog of the field list for each form
	if($form['form_name'] != 'mc_wellsub') {
		$flds = sqlListFields('form_'.$form['form_name']);
	} else {
		unset($flds);
		$flds = array();
	}
	if(in_array('approved_dt', $flds)) $approve_dt_catalog[] = $form['form_name'];
	
	if($form['form_name']=='wu_foot_exam' || $form['form_name']=='diabetes_self'
		|| $form['form_name'] == 'abc_SHarvey') {
		include_once("../../forms/".$form['form_name']."/archive.php");
	} else if($form['form_name'] == '') {
		// Never mind these
	} else {
		include_once("../../forms/".$form['form_name']."/report.php");
	}
}

foreach($lock_list as $form) {
	$flds = sqlListFields('form_'.$form['form_name']);
	if(in_array('approved_dt', $flds)) $approve_dt_catalog[]= $form['form_name'];
}

foreach($referral_list as $form) {
	if($form['form_name'] == '') {
		// Never mind these
	} else {
		include_once("../../forms/".$form['form_name']."/referral.php");
	}
}

$last_month = mktime(0,0,0,date('m'),date('d')-2,date('Y'));
$form_from_date= date('Y-m-d', $last_month);
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
$form_provider = '';
$form_supervisor= '';
$form_facility = '';
$form_name = '';
$form_status = 'c';
$approved_by = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_supervisor'])) $form_supervisor = $_POST['form_supervisor'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_name'])) $form_name = $_POST['form_name'];
if(isset($_POST['form_status'])) $form_status= $_POST['form_status'];
if(isset($_POST['approved_by'])) $approved_by = $_POST['approved_by'];
$form_details = 1;

if(isset($_GET['approve']) && $approved_by != '') {
	$item=1;
	$cnt=0;
	while($item <= $_POST['item_total']) {
		if(!isset($_POST['approve_stat_'.$item])) { $_POST['approve_stat_'.$item] = 0; }
		if($_POST['approve_stat_'.$item] == '1') {
			$frm_id = trim($_POST['approve_id_'.$item]);
			$frmdir = trim($_POST['approve_form_'.$item]);
			$frm_table = 'form_'.$frmdir;
			if($frmdir == 'mc_wellsub') $frm_table = 'form_mc_wellness';
			$sql = 'SELECT id, pid, date, approved_by';
			if(in_array($frmdir, $approve_dt_catalog)) $sql .= ', approved_dt';
			$sql .= " FROM $frm_table WHERE id=?";
			$test=sqlStatementNoLog($sql, array($frm_id));
			$row=sqlFetchArray($test);
			$orig_date = $row{'date'};
			if(!isset($row{'approved_dt'})) $row{'approved_dt'} = '';
			if($row{'id'} && $row{'id'} == $frm_id) {
				$sql = "UPDATE $frm_table SET form_complete='a'";
				if(!$hold_approve || ($row{'approved_by'} == '')) {
					$sql .= ", approved_by='$approved_by'";
				}
				// For the forms that have the most recent method
				if(in_array($frmdir, $approve_dt_catalog)) {
					if(!$hold_approve) {
						$sql .= ", approved_dt=NOW()";
					// Sometimes for re-archiving and as forms are brought up to 
					// current standards this date is empty - the date stamp on the 
					// form table is the closest estimate.
					} else if($hold_approve || ($row{'approved_dt'} == '')) {
						$sql .= ", approved_dt='$orig_date'";
					}
				}
				$sql .= " WHERE id='$frm_id'";
				sqlStatement($sql);
				// Here we have to handle the forms that are ready for going to 
				// the repository, as well as forms that use a different archive
				// function. 
				if($frmdir == 'wu_foot_exam' || $frmdir == 'diabetes_self'
						|| $frmdir == 'abc_SHarvey') {
					$tst=FormInRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm_table);
					if(!$tst) {
						ob_start();
						$rpt_function= $_POST['approve_form_'.$item].'_archive';
						$rpt_function($row{'pid'}, $_POST['encounter_'.$item], $frm_id);
						// Generate the html output
						$content=ob_get_contents();
						ob_end_clean();
						AddFormToRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm_table, $content);
					}
				} else if(SearchMultiArray($frmdir, $archive_list) !== false) {
					$tst=FormInRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm_table);
					if(!$tst) {
						ob_start();
						$rpt_function= $_POST['approve_form_'.$item].'_report';
						$rpt_function($row{'pid'}, $_POST['encounter_'.$item], "*", $frm_id, true);
						// Generate the html output
						$content=ob_get_contents();
						ob_end_clean();
						AddFormToRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm_table, $content);
					}
				}
				if(SearchMultiArray($frmdir, $referral_list) !== false) {
					$tst=FormInRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm_table.'_referral');
					if(!$tst) {
						ob_start();
						$rpt_function= $_POST['approve_form_'.$item].'_referral';
						$rpt_function($row{'pid'}, $_POST['encounter_'.$item], "*", $frm_id, true);
						// Generate the html output
						$content=ob_get_contents();
						ob_end_clean();
						AddFormToRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm_table.'_referral', $content);
					}
				}
				$cnt++;
			}
		}
		$item++;
	}
	$_POST['form_refresh']='refresh';
}

$orderby = $ORDERHASH['time'];
$form_orderby='time';

$query = "SELECT " .
  "forms.formdir, forms.form_name, forms.deleted, forms.form_id, " .
  "fe.encounter, fe.date, fe.reason, fe.provider_id, fe.supervisor_id, ".
	"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle, ".
	"pcp.lname AS pcplast, pcp.fname AS pcpfirst, pcp.mname AS pcpmiddle, ".
  "p.fname, p.mname, p.lname, p.DOB, p.pubpid, p.pid, p.providerID AS pcpID " .
	"FROM forms " .
	"LEFT JOIN form_encounter AS fe USING (encounter) ".
  "LEFT JOIN patient_data AS p ON forms.pid = p.pid " .
  "LEFT JOIN users AS u ON fe.provider_id = u.id " .
  "LEFT JOIN users AS pcp ON p.providerID = pcp.id " .
  "WHERE " .
  "forms.deleted != '1' AND ";
	$first = true;
	if($archive_list && (count($archive_list) > 0)) {
		$query .= "( ";	
		foreach($archive_list as $frm) {
			if(!$first) { $query .= "OR "; }
			$query .= "forms.formdir = '".$frm['form_name']."' ";
			$first = false;
		}
	}
	if($lock_list && (count($lock_list) > 0)) {
		if($first) { $query .= "( "; }
		foreach($lock_list as $frm) {
			if(!$first) { $query .= "OR "; }
			$query .= "forms.formdir = '".$frm['form_name']."' ";
			$first = false;
		}
	}
	if(!$first) { $query .= ") "; }
if ($form_to_date) {
  $query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_from_date 23:59:59' ";
}
if ($form_facility) {
  $query .= "AND fe.facility_id = '$form_facility' ";
}
if ($form_provider !== '') {
  $query .= "AND fe.provider_id = '$form_provider' ";
}
if ($form_supervisor !== '') {
	if($client_id == 'fl_pedi') {
  	$query .= "AND p.providerID = '$form_supervisor' ";
	} else {
  	$query .= "AND fe.supervisor_id = '$form_supervisor' ";
	}
}
if ($form_name) {
  $query .= "AND forms.formdir = '$form_name' ";
}
$query .= "ORDER BY $orderby";

$res=array();
if(isset($_GET['mode']) || isset($_GET['approve'])) {
	$res = sqlStatement($query);
}
$item=0;

?>
<html>
<head>
<title><?php xl('Approve Forms','e'); ?></title>
<link rel=stylesheet href="<?php echo $GLOBALS['css_header'];?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
    #report_pin_inputs {
        visibility: hidden;
        display: none;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

div.notification {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 0.8em;
  font-weight: bold;
	text-align: center;
	position: fixed;
  background: #E2A76F;
	border: solid 1px black;
	border-radius: 10px;
	width: 220px;
	z-index: 3000;
	cursor: progress;
	box-shadow: 8px 8px 5px #888888;
}

</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/report_tools.js"></script>

<script type="text/javascript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function HideDiv(target)
{
	var div = document.getElementById(target);
	div.style.display = 'none';
	return true;
}

function DelayedHideDiv()
{
	var target = 'pat-change-notification';
	if(arguments.length > 0) target = arguments[0];
	var pause = 3000;
	if(arguments.length > 1) pause = arguments[1];
	var d = document.getElementById(target);
	if(d) {
		d.style.display = 'block';
		window.setTimeout("HideDiv('"+target+"')", pause);
	}
	return true;
}

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function refreshme() {
  document.forms[0].submit();
 }

 function ApproveSelected() {
	if(document.forms[0].elements['approved_by'].selectedIndex=='0') {
		alert("No Doctor Selected As Approving");
		return false;
	}
	if(document.forms[0].elements['pin_verified'].value!='true') {
		alert("PIN Not Verified...Try Again");
		return false;
	}
	var cnt = document.forms[0].elements['item_total'].value;
	var tst = false;
	for(tmp=1; tmp<=cnt; tmp++) {
		if(document.forms[0].elements['approve_stat_'+tmp].checked==true) tst=true;
	}
	if(!tst) {
		alert("No Forms Are Selected....Nothing to do!");
		return false;
	}
	
	response=confirm("Approve all Checked Forms?\nApproving the forms will render them completely un-editable and is not reversible.\n\nAre you sure you are ready to do this?");
	if(response == false) return false;
	document.forms[0].action='form_approve.php?approve=yes';
  document.forms[0].submit();
 }

function set_pin(valid)
{
 var numargs = arguments.length;
 if (valid) {
	document.forms[0].elements['pin_verified'].value='true';
	return true;
 } else {
	document.forms[0].elements['pin_verified'].value='';
	return false;
 }
}

// This invokes the find-code popup.
function get_pin()
{
 document.forms[0].elements['pin_verified'].value='';
 var srch = document.forms[0].elements['approved_by'].value;
 if(srch == '') {
	alert("No User Selected!!");
  return false;
 }
 var target = '../../../custom/pin_check_popup.php?username='+srch;
 wmtOpen(target, '_blank', 400, 200);
}

function ApprovePop(pid, id, enc, form)
{
	var warn_msg = '';
	if(pid == '' || pid == 0) warn_msg = 'Patient ID is NOT set - ';
	if(id == '' || id == 0) warn_msg = 'Form ID is NOT set - ';
	if(enc == '' || enc == 0) warn_msg = 'Encounter is NOT set - ';
	if(form == '' || form == 0) warn_msg = 'Form Directory is NOT set - ';
	if(warn_msg != '') {
		alert(warn_msg + 'Not Able to Pop Open this Form');
		return false;
	}
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/'+form+'/view.php?mode=update&pid='+pid+'&id='+id+'&enc='+enc, '_blank', 'max', 'max');
}

function goForm(name, pid, pubpid, str_dob, encdate, enc, fname, fid) {
  toencounter(pid, pubpid, name, enc, encdate, str_dob);
	var href = 'forms/'+fname+'/new.php?mode=update&enc='+enc+'&pid='+pid;
  if(fid) href += '&id='+fid;
  // alert('Here in the form: '+href);
  // DelayedHideDiv('pat-change-notification',3000);
  // DelayedHideDiv('enc-change-notification',3000);
	window.setTimeout("window.opener.loadFrame('enc2', 'RBot', '"+href+"')",4000);
}

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<div id="pat-change-notification" class="notification" style="left: 45%; top: 40%; z-index: 850; display: none;">Loading That Patient....</div>
<div id="enc-change-notification" class="notification" style="left: 45%; top: 40%; z-index: 750; display: none;">Loading That Encounter....</div>
<span class='title'><?php xl('Report','e'); ?> - <?php xl('Form Approval','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='form_approve.php?mode=search'>

<div id="report_parameters">
<table>
 <tr>
  <td>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label'><?php xl('Facility','e'); ?>: </td>
          <td style='width: 18%;'>
	    <?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?></td>
          <td class='label'><?php xl('Provider','e'); ?>: </td>
          <td style='width: 18%;'><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
								"UPPER(specialty) LIKE '%SUPERVISOR%') ".
								"ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_provider'>\n";
              echo "    <option value=''";
							if($form_provider == '') { echo " selected"; }
							echo ">-- " . xl('All') . " --</option>\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_provider) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
              }
              echo "   </select>\n";
             ?></td>
           	<td class='label'><?php xl('From','e'); ?>: </td>
           	<td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
          	<td class='label'><?php xl('Status','e'); ?>: </td>
          	<td><?php
              	$query = "SELECT option_id, title FROM list_options WHERE ".
                	"list_id = 'Form_Status' ORDER BY seq";
              	$ures = sqlStatement($query);
	
              	echo "   <select name='form_status'>\n";
              	echo "    <option value=''>-- " . xl('All') . " --</option>\n";
	
              	while ($urow = sqlFetchArray($ures)) {
                	$statid = $urow{'option_id'};
                	echo "    <option value='$statid'";
                	if ($statid == $form_status) echo " selected";
                	echo ">" . $urow{'title'} . "</option>\n";
              	}
              	echo "   </select>\n";
              ?></td>
         </tr>
         <tr>
          <td class='label'><?php xl('Form Name','e'); ?>: </td>
          <td><?php
							$sel_forms = getFormsByType(array('archive_form', 'lock_form'));
              echo "   <select name='form_name'>\n";
              echo "    <option value=''>-- " . xl('All') . " --</option>\n";
							foreach($sel_forms as $frm) {
								echo "		<option value='".$frm['form_name']."'";
								if($frm['form_name'] == $form_name) { echo " selected"; }
								if($frm['nickname']) {
									echo ">".$frm['nickname']."</option>\n";
								} else {
									echo ">".$frm['name']."</option>\n";
								}
							}
              echo "   </select>\n";
             ?></td>
          <td class='label'><?php echo $client_id == 'fl_pedi' ? 'Dr Following Patient' : 'Supervisor'; ?>: </td>
          <td><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' AND ".
								"(UPPER(specialty) LIKE '%SUPERVISOR%' OR UPPER(specialty) ".
								"LIKE '%PROVIDER%') ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_supervisor'>\n";
              echo "    <option value=''";
							if($form_supervisor == '') { echo " selected"; }
							echo ">-- " . xl('All') . " --</option>\n";
              echo "    <option value='0'";
							if($form_supervisor== '0') { echo " selected"; }
							echo ">-- " . xl('None Assigned') . " --</option>\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_supervisor) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
              }
              echo "   </select>\n";
             ?></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
         </tr>
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:10px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'><span><?php xl('Submit','e'); ?></span></a>
          </div>
        </td>
      </tr>
			<tr>
				<td>
          <div style='margin-left:10px'>
            <?php if (isset($_POST['form_refresh'])) { ?>
            <a href='#' class='css_button' onclick='window.print()'><span><?php xl('Print','e'); ?></span></a>
            <?php } else { echo "&nbsp;"; } ?>
					</div>
				</td>
			</tr>
     </table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php
 if (isset($_POST['form_refresh'])) {
?>
<div id="report_results">
<table>

 <thead>
<?php if ($form_details) { ?>
	<th>
		<?php xl('Approve','e'); ?>
	</th>
  <th>
		<?php xl('Provider','e'); ?>
  </th>
  <th>
		<?php xl('Date','e'); ?>
  </th>
  <th>
		<?php xl('Patient','e'); ?>
  </th>
  <th>
		<?php xl('ID','e'); ?>
  </th>
	<?php if($client_id == 'fl_pedi') { ?>
  <th>
		<?php xl('Dr Following','e'); ?>
  </th>
	<?php } ?>
  <th>
   <?php  xl('Status','e'); ?>
  </th>
  <th>
   <?php  xl('Encounter','e'); ?>
  </th>
  <th>
   <?php  xl('Form','e'); ?>
  </th>
<?php } else { ?>
  <th><?php  xl('Provider','e'); ?></td>
  <th><?php  xl('Encounters','e'); ?></td>
<?php } ?>
 </thead>
 <tbody>
<?php
if ($res) {
  $lastdocname = "";
  $doc_encounters = 0;
  while ($row = sqlFetchArray($res)) {
    $errmsg  = "";
    $this_id= $row['form_id'];
    $this_form= 'form_'.$row['formdir'];
		if($this_form == 'form_mc_wellsub') $this_form = 'form_mc_wellness';
  	$sql = "SELECT form_complete, form_priority FROM $this_form WHERE id=?";
    $farray = sqlQuery($sql, array($this_id));
    $fstatus = strtolower($farray['form_complete']);
    $fbill= strtolower($farray['form_priority']);
    if (($fstatus != $form_status) && ($form_status != '')) continue;

		$dob = oeFormatShortDate($row{'DOB'});
		$enc_date = oeFormatShortDate(substr($row{'date'}, 0, 10));
		if(!$row{'reason'}) $row{'reason'} = '[Go To Encounter]';
		$item++;
?>
 <tr>
	<td>
		<input name="approve_stat_<?php echo $item; ?>" id="approve_stat_<?php echo $item; ?>" type="checkbox" value="1" <?php echo (((($fstatus == 'a') || ($fstatus == 'i')) || (($row['formdir'] == 'mbj_fee') && ($fbill == 'u')))?'disabled="disabled"':''); ?>/>
		<input name="approve_id_<?php echo $item; ?>" id="approve_id_<?php echo $item; ?>" type="hidden" value="<?php echo $row['form_id']; ?>" />
		<input name="approve_form_<?php echo $item; ?>" id="approve_form_<?php echo $item; ?>" type="hidden" value="<?php echo $row['formdir']; ?>" />
	</td>
  <td>
   <?php echo $row{'ulast'}.', '.$row{'ufirst'}; ?>&nbsp;
  </td>
  <td>
   <?php echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>&nbsp;
  </td>
  <td><a href="javascript: goPid('<?php echo $row{'pid'}; ?>');">
   <?php echo $row{'lname'}.', '.$row{'fname'}.' '.$row{'mname'}; ?>&nbsp;</a>
  </td>
  <td>
   <?php echo $row{'pubpid'}; ?>&nbsp;
  </td>
	<?php if($client_id == 'fl_pedi') { ?>
  <td>
   <?php echo $row{'pcplast'} != '' ? $row['pcplast'].', '.$row['pcpfirst'] : 'Not On File'; ?>&nbsp;
  </td>
	<?php } ?>
  <td>
   <?php echo ListLook($fstatus,'Form_Status'); ?>&nbsp;
  </td>
  <td>
	<?php if($GLOBALS['wmt::enc_link_fee']) { ?>
		<a href='javascript:;' onclick="toencounter('<?php echo $row['pid']; ?>','<?php echo $row['pubpid']; ?>','<?php echo htmlspecialchars($row['fname'],ENT_QUOTES) . ' ' . htmlspecialchars($row['lname'],ENT_QUOTES); ?>','<?php echo $row['encounter']; ?>','<?php echo $enc_date; ?>', '<?php echo $dob; ?>'); ApprovePop('<?php echo $row['pid']; ?>', '', '<?php echo $row['encounter']; ?>', '<?php echo $GLOBALS['wmt::enc_link_fee']; ?>');" >
	<?php } else if($GLOBALS['wmt::enc_direct_to_fee']) { ?>
		<a href='javascript:;' onclick="goForm('<?php echo htmlspecialchars($row{'fname'} . ' ' . $row{'lname'},ENT_QUOTES,'',FALSE); ?>', '<?php echo $row['pid']; ?>', '<?php echo $row{'pubpid'}; ?>', '<?php echo $dob; ?>', '<?php echo $enc_date; ?>', '<?php echo $row{'encounter'}; ?>', '<?php echo 'fee_sheet'; ?>', '');" >
  <?php } else { ?>
		<a href='javascript:;' onclick="toencounter('<?php echo $row['pid']; ?>','<?php echo $row['pubpid']; ?>','<?php echo htmlspecialchars($row['fname'],ENT_QUOTES) . ' ' . htmlspecialchars($row['lname'],ENT_QUOTES); ?>','<?php echo $row['encounter']; ?>','<?php echo $enc_date; ?>', '<?php echo $dob; ?>');" >
	<?php } ?>
   <?php echo substr($row{'reason'},0,50); ?>&nbsp;</a>
  </td>
  <td>
	<!-- THIS IS THE EXCEPTION TO PUSH OLD FORMS IN THE BACKGROUND -->
	<?php if((SearchMultiArray($row['formdir'], $pop_forms) === false) || !$pop_used) { ?>
		<a href='javascript:;' onclick="goForm('<?php echo htmlspecialchars($row{'fname'} . ' ' . $row{'lname'},ENT_QUOTES,'',FALSE); ?>', '<?php echo $row['pid']; ?>', '<?php echo $row{'pubpid'}; ?>', '<?php echo $dob; ?>', '<?php echo $enc_date; ?>', '<?php echo $row{'encounter'}; ?>', '<?php echo $row{'formdir'}; ?>', '<?php echo $row{'form_id'}; ?>');" >
	<?php } else { 
		echo "<a href='javascript:;' onclick=\"ApprovePop('".$row['pid']."', '".$row['form_id']."', '".$row['encounter']."', '".$row['formdir']."');\">\n";
	 } ?>
   <?php echo $row['form_name']; ?>&nbsp;</a>
	 <input name="encounter_<?php echo $item; ?>" id="encounter_<?php echo $item; ?>" type="hidden" value="<?php echo $row['encounter']; ?>" />
  </td>
 </tr>
<?php
    $lastdocname = $row['ulast'].', '.$row['ufirst'];
  }
}
echo "</table>\n";
?>
<div id="report_pin_inputs">
<table>
<tr>
	<td><img class="selectallarrow" width="32" height="20" alt="With Selected:" src="../../../phpmyadmin/themes/original/img/arrow_ltr.png"></td>
	<td colspan="2"><a href="javascript:;" class="link_submit" onclick="CheckAll('approve_stat_');"><?php xl('Check All','e'); ?></a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll('approve_stat_');"><span><?php xl('Uncheck All','e'); ?></span></a></td>
	<td colspan="3"><?php xl('Approving Provider','e'); ?>&nbsp;&nbsp;<?php
		// Build a drop-down list of providers.
		$query = "SELECT id, username, lname, fname, setting_value FROM users LEFT JOIN user_settings on id=setting_user WHERE authorized = 1 AND setting_label='wmt::approve_allow' AND setting_value='1' ORDER BY lname, fname";
		$ures = sqlStatement($query);

		echo "   <select name='approved_by' id='approved_by' onChange='get_pin()'>\n";
		echo "    <option value=''>-- " . xl('None') . " --\n";

		while ($urow = sqlFetchArray($ures)) {
			$provid = $urow['username'];
			echo "    <option value='$provid'";
			if ($urow{'id'} == $form_supervisor) echo " selected";
			echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
		}
		echo "   </select>\n";
	?>
	&nbsp;</td>
	<td><a href='javascript:;' class='css_button' onclick='get_pin();'><span><?php xl('Verify PIN','e'); ?></span></a></td>
	<td><a href='javascript:;' class='css_button' onclick='ApproveSelected();'><span><?php xl('Approve Forms','e'); ?></span></a></td>
</tr>

</tbody>
</table>
</div>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" tabindex="-1" value="<?php echo $form_orderby ?>" />
<input type="hidden" name="form_refresh" id="form_refresh" tabindex="-1" value=""/>
<input name="item_total" id="item_total" type="hidden" tabindex="-1" value="<?php echo $item; ?>" />
<input name="pin_verified" id="pin_verified" type="hidden" tabindex="-1" value="" />

</form>
</body>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtpopup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php include_once($GLOBALS['srcdir'].'/restoreSession.php'); ?>
<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
