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
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
set_time_limit(0);

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'patient' => 'lower(patient_data.lname), lower(patient_data.fname), form_encounter.date',
  'pubpid'  => 'lower(patient_data.pubpid), form_encounter.date',
  'time'    => 'form_encounter.date',
	'doctor'  => 'lower(ulast), lower(ufirst), form_encounter.date'
);
$pop_forms = getFormsByType(array('pop_form'));
$referral_list = getFormsByType(array('referral_form'));
$pop_used = checkSettingMode('wmt::form_popup');

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
$include_all = '';
$popup = false;
if(isset($_POST['form_provider'])) $form_provider = strip_tags($_POST['form_provider']);
if(isset($_POST['form_supervisor'])) $form_supervisor = strip_tags($_POST['form_supervisor']);
if(isset($_POST['form_facility'])) $form_facility = strip_tags($_POST['form_facility']);
if(isset($_POST['form_name'])) $form_name = strip_tags($_POST['form_name']);
if(isset($_POST['include_all'])) $include_all = $_POST['include_all'];
if(isset($_GET['popup'])) $popup = $_GET['popup'];
$form_details = 1;

$orderby = $ORDERHASH['time'];
$form_orderby='time';

$query = "SELECT " .
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
if ($form_facility) {
  $query .= "AND form_encounter.facility_id = '$form_facility' ";
}
if ($form_provider !== '') {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}
if ($form_supervisor !== '') {
  $query .= "AND form_encounter.supervisor_id = '$form_supervisor' ";
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
<title><?php xl('Pending Referral Report','e'); ?></title>
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
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>

<script type="text/javascript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function refreshme() {
  document.forms[0].submit();
 }

// Kept for now for batch printing coming later
 function PrintSelected() {
	var cnt = document.forms[0].elements['item_total'].value;
	var tst = false;
	var print_id = '';
	var print_id_array = [];
	// print_form_ + tmp <- is the form directory
	for(tmp=1; tmp<=cnt; tmp++) {
		if(document.forms[0].elements['print_stat_'+tmp].checked==true) {
			print_id = document.forms[0].elements['print_stat_'+tmp].value	
			print_id += '~' + document.forms[0].elements['print_form_'+tmp].value	
			print_id_array.push(print_id);
			alert("This Item: "+print_id);
			tst=true;
		}
	}
	if(!tst) {
		alert("No Forms Are Selected....Nothing to do!");
		return false;
	}
	
	response=confirm("Print all Checked Forms?\n");
	if(response == false) return false;
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/reports/myreports/batch_print_referrals.php?print_id='+print_id_array, '_blank', 900, 900);
 }

 function CheckAll() {
	var cnt = document.forms[0].elements['item_total'].value;
	for(tmp=1; tmp<=cnt; tmp++) {
		if(document.forms[0].elements['print_stat_'+tmp].disabled!=true) {	
			document.forms[0].elements['print_stat_'+tmp].checked=true;		
		}
	}
 }

 function UncheckAll() {
	var cnt = document.forms[0].elements['item_total'].value;
	for(tmp=1; tmp<=cnt; tmp++) {
		document.forms[0].elements["print_stat_"+tmp].checked=false;		
	}
 }

function ReferralPop(pid, id, enc, form)
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
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/'+form+'/print_referral.php?mode=update&pid='+pid+'&id='+id+'&enc='+enc, '_blank', 'max', 'max');
}

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Pending Referrals','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='referral_rpt.php?mode=search'>

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
							// THIS SHOULD NOT EVEN BE POSSIBLE
              // echo "    <option value='0'";
							// if($form_provider == '0') { echo " selected"; }
							// echo ">-- " . xl('None Assigned') . " --</option>\n";

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
						<td class="label"><label for="include_all">Include Already Printed?</label></td>
						<td><input name="include_all" id="include_all" type="checkbox" value="1" <?php echo ($include_all == '1') ? 'checked="checked"' : ''; ?> /></td>
         </tr>
         <tr>
          <td class='label'><?php xl('Form Name','e'); ?>: </td>
          <td><?php
              echo "   <select name='form_name'>\n";
              echo "    <option value=''>-- " . xl('All') . " --</option>\n";
							foreach($referral_list as $frm) {
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
          <td class='label'><?php xl('Supervisor','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of supervisors.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' AND ".
								"UPPER(specialty) LIKE '%SUPERVISOR%' ORDER BY lname, fname";
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
			<!-- tr>
				<td>
          <div style='margin-left:10px'>
            <?php if (isset($_POST['form_refresh'])) { ?>
            <a href="#" class="css_button" onclick="PrintSelected();"><span><?php xl('Print Forms','e'); ?></span></a>
            <?php } else { echo "&nbsp;"; } ?>
					</div>
				</td>
			</tr -->
     </table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php
 if (isset($_POST['form_refresh'])) {
?>
<div id="report_results" style="border: solid 1px black;">
<table>

 <thead>
<?php if ($form_details) { ?>
	<!-- th>
		<?php xl('Print','e'); ?>
	</th -->
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
  <th>
   <?php  xl('Printed','e'); ?>
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
$bgcolor = '#FFFFDD';
if ($res) {
  $lastdocname = "";
  $doc_encounters = 0;
  while ($row = sqlFetchArray($res)) {
    $errmsg  = "";
    $this_form= 'form_'.$row{'formdir'};
		if($this_form == 'form_mc_wellsub') $this_form = 'form_mc_wellness';
    $sql = "SELECT form_complete, referral_printed FROM $this_form WHERE id=?";
    $farray = sqlQuery($sql, array($row{'form_id'}));
    $fstatus = strtolower($farray['form_complete']);
		if($fstatus != 'a') continue;
		if(!$include_all && ($farray{'referral_printed'} == 1)) continue;
		// Now - is there a referral in the repository available?
		if(!FormInRepository($row{'pid'}, $row{'encounter'}, $row{'form_id'}, $this_form.'_referral')) continue;	

		$item++;
		$bgcolor = ($bgcolor == '#FFFFDD') ? '#FFDDDD' : '#FFFFDD';
?>
 <tr style="background-color: <?php echo $bgcolor; ?>;">
	<!-- td>
		<input name="print_stat_<?php echo $item; ?>" id="print_stat_<?php echo $item; ?>" type="checkbox" value="<?php echo $row{'form_id'}; ?>" />
		<input name="print_form_<?php echo $item; ?>" id="print_form_<?php echo $item; ?>" type="hidden" value="<?php echo $row['formdir']; ?>" />
	</td -->
  <td>
  <?php echo $row{'ulast'}.', '.$row{'ufirst'}; ?>&nbsp;
  </td>
  <td>
   <?php echo oeFormatShortDate(substr($row{'date'}, 0, 10)) ?>&nbsp;
  </td>
  <td><a href="javascript:;" onclick="opener.top.frames['RTop'].location='../../patient_file/summary/demographics.php?set_pid='+<?php echo $row{'pid'}; ?>; opener.top.restoreSession();"><?php echo $row{'lname'}.', '.$row{'fname'}.' '.$row{'mname'}; ?>&nbsp;</a>
  </td>
  <td>
   <?php echo $row{'pubpid'}; ?>&nbsp;
  </td>
  <td>
   <?php echo ($farray{'referral_printed'}) ? 'Yes' : 'No'; ?>&nbsp;
  </td>
  <td>
   <?php echo substr($row['reason'],0,50); ?>&nbsp;
  </td>
  <td>
		<a href="javascript:;" onclick="ReferralPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', '<?php echo $row{'formdir'}; ?>');"><?php echo $row['form_name']; ?>&nbsp;</a>
	 <input name="encounter_<?php echo $item; ?>" id="encounter_<?php echo $item; ?>" type="hidden" value="<?php echo $row['encounter']; ?>" />
  </td>
 </tr>
<?php
    $lastdocname = $row{'ulast'}.', '.$row{'ufirst'};
  }
}
?>
<!-- tr>
	<td><img class="selectallarrow" width="32" height="20" alt="With Selected:" src="../../../phpmyadmin/themes/original/img/arrow_ltr.png"></td>
	<td colspan="2"><a href="javascript:;" class="link_submit" onclick="CheckAll();"><?php xl('Check All','e'); ?></a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll();"><span><?php xl('Uncheck All','e'); ?></span></a></td>
	<td><a href='#' class='css_button' onclick='PrintSelected();'><span><?php xl('Print Forms','e'); ?></span></a></td>
</tr -->

</tbody>
</table>
</div>  <!-- end referral reports -->
<div class='text'>
 	<?php echo xl('Click on the form name to print the attached referral.','e' ); ?>
</div>
<div class='text'>
 	<?php echo xl('Click on the patient name to view the patient in the main window.','e' ); ?>
</div>
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" tabindex="-1" value="<?php echo $form_orderby ?>" />
<input type="hidden" name="form_refresh" id="form_refresh" tabindex="-1" value=""/>
<input name="item_total" id="item_total" type="hidden" tabindex="-1" value="<?php echo $item; ?>" />

</form>
</body>

<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/wmt/wmtpopup.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
