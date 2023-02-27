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
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/wmt-v2/formhunt.inc");
require_once("$srcdir/wmt-v2/wmtstandard.inc");
require_once("$srcdir/wmt-v2/approve.inc");

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
$archive_list = getFormsByType(array('archive_form'));
$lock_list = getFormsByType(array('lock_form'));
// foreach($archive_list as $frm) {
	// print_r($frm);
	// echo "<br>\n";
// }

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$form_from_date = fixDate(date('Y-m-d'), date('Y-m-d', $last_month));
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d', $last_month));
}
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
$form_provider = '';
$form_facility = '';
$form_name = '';
$form_status = 'a';
$form_orderby = 'pubpid';
$form_pid = '';
if(isset($pid)) $form_pid = $pid;
if(isset($_REQUEST['form_orderby'])) $form_orderby = $_REQUEST['form_orderby'];
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_name'])) $form_name = $_POST['form_name'];
if(isset($_POST['form_status'])) $form_status= $_POST['form_status'];
if(isset($_POST['form_pid'])) $form_pid= $_POST['form_pid'];
$form_details   = "1";

// Here We read the inputs for checked boxes and approve them
// echo "Get: ",$_GET['approve'],"<br/>\n";
// echo "Items: ",$_POST['item_total'],"<br/>\n";
if(isset($_GET['approve'])) {
	$item=1;
	$cnt=0;
	while($item <= $_POST['item_total']) {
		if(!isset($_POST['approve_stat_'.$item])) { $_POST['approve_stat_'.$item] = ''; }
		if($_POST['approve_stat_'.$item] == '1') {
			$frm_id=trim($_POST['approve_id_'.$item]);
			$frmdir=trim($_POST['approve_form_'.$item]);
			$frm='form_'.$frmdir;
			if($frmdir == 'mc_wellsub') {
				$frmdir = 'mc_wellness';
				$frm='form_mc_wellness';
			}
			$sql = "SELECT id, date, pid FROM $frm WHERE id='$frm_id'";
			$test=sqlStatement($sql);
			$row=sqlFetchArray($test);
			// echo "Returned Row: ",$row{'id'},"<br/>\n";
			if($row{'id'} && $row{'id'} == $frm_id) {
				$sql = "UPDATE $frm SET form_complete='c', approved_by='', ";
				$flds = sqlListFields($frm);
				if(in_array('referral_printed', $flds)) { 
					$sql .= "referral_printed=0, ";
				}
				if(in_array('referral_docid', $flds)) { 
					$sql .= "referral_docid=0, ";
				}
				$sql .= "date = NOW() WHERE id='$frm_id'";
				// echo "Update SQL: $sql<br>\n";
				$test=sqlInsert($sql);
				// Here we have to handle the forms that are ready for going to 
				// the repository, as well as forms that use a different archive
				// function. 
				if(SearchMultiArray($frmdir, $archive_list) !== false) {
					$tst=FormInRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm);
					if($tst) {
						DeleteFromRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm);
					}
					$tst=FormInRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm.'_referral');
					if($tst) {
						DeleteFromRepository($row{'pid'}, $_POST['encounter_'.$item], $frm_id, $frm.'_referral');
					}
				} else {
				}
				$cnt++;
			}
		}
		$item++;
	}
	$_POST['form_refresh']='refresh';
}

$orderby = $ORDERHASH[$form_orderby];

$query = "SELECT " .
  "forms.formdir, forms.form_name, forms.deleted, forms.form_id, " .
  "form_encounter.encounter, form_encounter.date, form_encounter.reason, " .
	"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle, ".
  "patient_data.fname, patient_data.mname, patient_data.lname, " .
  "patient_data.pubpid FROM forms " .
	"LEFT JOIN form_encounter USING (encounter) ".
  "LEFT JOIN patient_data ON forms.pid = patient_data.pid " .
  "LEFT JOIN users AS u ON form_encounter.provider_id = u.id " .
  "WHERE " .
  "forms.deleted != '1' AND ";
	$first = true;
	if($archive_list && (count($archive_list) > 0)) {
		foreach($archive_list as $frm) {
			if($first) { $query .= "( "; }
			if(!$first) { $query .= "OR "; }
			$query .= "forms.formdir = '".$frm['form_name']."' ";
			$first = false;
		}
	}
	if($lock_list && (count($lock_list) > 0)) {
		foreach($lock_list as $frm) {
			if($first) { $query .= "( "; }
			if(!$first) { $query .= "OR "; }
			$query .= "forms.formdir = '".$frm['form_name']."' ";
			$first = false;
		}
	}
	if(!$first) { $query .= ") "; }
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
if ($form_name) {
  $query .= "AND forms.formdir = '$form_name' ";
}
if ($form_pid) {
  $query .= "AND forms.pid= '$form_pid' ";
}
$query .= "ORDER BY $orderby";

$res=array();
if(isset($_GET['mode']) || isset($_GET['approve']) || isset($_POST['form_orderby'])) {
	$res = sqlStatement($query);
}
$item=0;

?>
<html>
<head>
<title><?php xl('Unlock Forms/Remove From Archive','e'); ?></title>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

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

<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script LANGUAGE="JavaScript">

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

 function ApproveSelected() {
	var cnt = document.forms[0].elements['item_total'].value;
	var tst = false;
	for(tmp=1; tmp<=cnt; tmp++) {
		if(document.forms[0].elements['approve_stat_'+tmp].checked==true) tst=true;
	}
	if(!tst) {
		alert("No Forms Are Selected....Nothing to do!");
		return false;
	}
	
	response=confirm("Unlock All Checked Forms?\n\nAre you sure you are ready to do this?");
	if(response == false) return false;
	document.forms[0].action='form_unlock.php?approve=yes';
  document.forms[0].submit();
 }

 function CheckAll() {
	var cnt = document.forms[0].elements['item_total'].value;
	for(tmp=1; tmp<=cnt; tmp++) {
		if(document.forms[0].elements['approve_stat_'+tmp].disabled!=true) {	
			document.forms[0].elements['approve_stat_'+tmp].checked=true;		
		}
	}
 }

 function UncheckAll() {
	var cnt = document.forms[0].elements['item_total'].value;
	for(tmp=1; tmp<=cnt; tmp++) {
		document.forms[0].elements["approve_stat_"+tmp].checked=false;		
	}
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
 var target = '../../../custom/pin_check_popup.php?username='+srch;
 dlgopen(target, '_blank', 300, 200);
}

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Unlock Approved Forms','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='form_unlock.php?mode=search'>

<div id="report_parameters">
<table>
 <tr>
  <td>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label'><?php xl('Facility','e'); ?>: </td>
          <td>
	    <?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?></td>
          <td class='label'><?php xl('Provider','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND (specialty LIKE '%Provider%' OR ".
								"specialty LIKE '%Supervisor%') ".
								"ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_provider'>\n";
              echo "    <option value=''>-- " . xl('All') . " --</option>\n";
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
           	<td class='label'><?php xl('From','e'); ?>: </td>
           	<td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
         </tr>
         <tr>
          <td class='label'><?php xl('Form Name','e'); ?>: </td>
          <td><?php
              echo "   <select name='form_name'>\n";
              echo "    <option value=''>-- " . xl('All') . " --</option>\n";
							$sel_forms = getFormsByType(array('archive_form', 'lock_form'));
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
          <td class='label'><?php xl('Status','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of form statuses.
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
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
         </tr>
					<tr>
						<td class="label"><?php xl('Patient ID:','e'); ?></td>
						<td class="input"><input name="form_pid" id="form_pid" type="text" value="<?php echo $form_pid; ?>" /></td>
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
            <?php if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) { ?>
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
 if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) {
?>
<div id="report_results">
<table>

 <thead>
<?php if ($form_details) { ?>
	<th>
		<?php xl('Unlock','e'); ?>
	</th>
  <th>
		<a href="nojs.php" onclick="return dosort('doctor');"
		<?php if($form_orderby == 'doctor') echo "style='color:#00CC00'"; ?>> <?php xl('Provider','e'); ?> </a>
  </th>
  <th>
		<a href="nojs.php" onclick="return dosort('time');"
		<?php if($form_orderby == 'time') echo "style='color:#00CC00'"; ?>> <?php xl('Date','e'); ?> </a>
  </th>
  <th>
		<a href="nojs.php" onclick="return dosort('patient');"
		<?php if($form_orderby == 'patient') echo "style='color:#00CC00'"; ?>> <?php xl('Patient','e'); ?> </a>
  </th>
  <th>
		<a href="nojs.php" onclick="return dosort('pubpid');"
		<?php if($form_orderby == 'pubpid') echo "style='color:#00CC00'"; ?>> <?php xl('ID','e'); ?> </a>
  </th>
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
    // FIX - These fields should be set in the master query now
    $this_id= $row['form_id'];
    $this_form= 'form_'.$row['formdir'];
		if($row['formdir'] == 'mc_wellsub') { $this_form = 'form_mc_wellness'; }
    $sql = "SELECT form_complete FROM $this_form WHERE $this_form.id='$this_id'";
    $fdata = sqlStatement($sql);
    $farray = sqlFetchArray($fdata);
    $fstatus = array_shift($farray);
    if (($fstatus != $form_status) && ($form_status != '')) continue;

		$item++;
?>
 <tr>
	<td>
		<input name="approve_stat_<?php echo $item; ?>" id="approve_stat_<?php echo $item; ?>" type="checkbox" value="1" <?php echo ((($fstatus == 'c') || ($fstatus == 'i'))?'disabled="disabled"':''); ?>/>
		<input name="approve_id_<?php echo $item; ?>" id="approve_id_<?php echo $item; ?>" type="hidden" value="<?php echo $row['form_id']; ?>" />
		<input name="approve_form_<?php echo $item; ?>" id="approve_form_<?php echo $item; ?>" type="hidden" value="<?php echo $row['formdir']; ?>" />
	</td>
  <td>
   <?php echo $row['ulast'].', '.$row['ufirst']; ?>&nbsp;
  </td>
  <td>
   <?php echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>&nbsp;
  </td>
  <td>
   <?php echo $row['lname'].', '.$row['fname'].' '.$row['mname']; ?>&nbsp;
  </td>
  <td>
   <?php echo $row['pubpid']; ?>&nbsp;
  </td>
  <td>
   <?php echo ListLook($fstatus,'Form_Status'); ?>&nbsp;
  </td>
  <td>
   <?php echo substr($row['reason'],0,50); ?>&nbsp;
  </td>
  <td>
   <?php echo $row['form_name']; ?>&nbsp;
	 <input name="encounter_<?php echo $item; ?>" id="encounter_<?php echo $item; ?>" type="hidden" value="<?php echo $row['encounter']; ?>" />
  </td>
 </tr>
<?php
    $lastdocname = $row['ulast'].', '.$row['ufirst'];
  }
}
?>
<tr>
	<td><img class="selectallarrow" width="32" height="20" alt="With Selected:" src="../../../phpmyadmin/themes/original/img/arrow_ltr.png"></td>
	<td colspan="2"><a href="javascript:;" class="link_submit" onclick="CheckAll();"><?php xl('Check All','e'); ?></a>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="javascript:;" class="link_submit" onclick="UncheckAll();"><span><?php xl('Uncheck All','e'); ?></span></a></td>
	<td colspan="4">&nbsp;</td>
	<td><a href='javascript:;' class='css_button' onclick='ApproveSelected();'><span><?php xl('Unlock Forms','e'); ?></span></a></td>
</tr>

</tbody>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type="hidden" name="form_refresh" id="form_refresh" value=""/>
<input name="item_total" id="item_total" type="hidden" value="<?php echo $item; ?>" />
<input name="pin_verified" id="pin_verified" type="hidden" value="1" />

</form>
</body>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
