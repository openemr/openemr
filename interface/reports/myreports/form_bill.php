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
$bill_forms = getFormsByType(array('bill_form'));
$pop_used = checkSettingMode('wmt::form_popup');
$last_month = mktime(0,0,0,date('m'),date('d')-2,date('Y'));
$form_from_date= date('Y-m-d', $last_month);
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(isset($_POST['form_from_date']))
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
if(isset($_POST['form_to_date']))
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_provider = '';
$form_supervisor = '';
$form_facility = '';
$form_name = '';
$form_status = 'c';
$form_bill = 'u';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_supervisor'])) $form_supervisor = $_POST['form_supervisor'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_name'])) $form_name = $_POST['form_name'];
if(isset($_POST['form_status'])) $form_status= $_POST['form_status'];
if(isset($_POST['form_bill'])) $form_bill= $_POST['form_bill'];
$form_details   = "1";

if(isset($_GET['bill'])) {
	$item=1;
	$cnt=0;
	while($item <= $_POST['item_total']) {
		if($_POST['bill_stat_'.$item] == '1') {
			$frm_id=trim($_POST['bill_id_'.$item]);
			$frm='form_'.trim($_POST['bill_form_'.$item]);
			$sql = "SELECT id, date, pid FROM $frm WHERE id='$frm_id'";
			$test=sqlStatement($sql);
			$row=sqlFetchArray($test);
			// echo "Returned Row: ",$row{'id'},"<br/>\n";
			if($row{'id'} && $row{'id'} == $frm_id) {
				$sql = "UPDATE $frm SET form_priority='b', ".
							"date = NOW() WHERE id='$frm_id'";
				$test=sqlInsert($sql);
				$cnt++;
			}
		}
		$item++;
	}
	$_POST['form_refresh']='refresh';
}

$orderby = $ORDERHASH['time'];
$form_orderby = 'time';

$from_dt_bind = $form_from_date . ' 00:00:00';
$to_dt_bind = $form_from_date . ' 23:59:59';
if($form_to_date) $to_dt_bind = $form_to_date . ' 23:59:59';
$binds = array();
$query = 'SELECT ' .
  'forms.formdir, forms.form_name, forms.deleted, forms.form_id, ' .
  'form_encounter.encounter, form_encounter.date, form_encounter.reason, ' .
	'u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle, '.
  'patient_data.fname, patient_data.mname, patient_data.lname, ' .
  'patient_data.pubpid, patient_data.pid FROM forms ' .
	'LEFT JOIN form_encounter USING (encounter) '.
  'LEFT JOIN patient_data ON forms.pid = patient_data.pid ' .
  'LEFT JOIN users AS u ON form_encounter.provider_id = u.id ' .
  'WHERE forms.deleted != 1 ';
$first = true;
if($bill_forms && (count($bill_forms) > 0)) {
	$query .= 'AND (';	
	foreach($bill_forms as $frm) {
		if(!$first) $query .= 'OR '; 
		$query .= 'forms.formdir = ? ';
		$binds[] = $frm['form_name'];
		$first = false;
	}
	$query .= ') ';
}
$query .= 'AND form_encounter.date >= ? AND form_encounter.date <= ? ';
$binds[] = $from_dt_bind;
$binds[] = $to_dt_bind;

if ($form_facility) {
  $query .= 'AND form_encounter.facility_id = ? ';
	$binds[] = $form_facility;
}
if ($form_provider !== '') {
  $query .= 'AND form_encounter.provider_id = ? ';
	$binds[] = $form_provider;
}
if ($form_supervisor !== '') {
  $query .= 'AND form_encounter.supervisor_id = ? ';
	$binds[] = $form_supervisor;
}
if ($form_name) {
  $query .= 'AND forms.formdir = ? ';
	$binds[] = $form_name;
}
$query .= "ORDER BY $orderby";

$res=array();
if(isset($_GET['mode']) || isset($_GET['bill'])) {
	$res = sqlStatement($query, $binds);
}
$item = 0;

?>
<html>
<head>
<title><?php xl('Approve Forms','e'); ?></title>
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
<script type="text/javascript" src="../../../library/wmt-v2/wmtpopup.js"></script>

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

 function BillSelected() {
	var cnt = document.forms[0].elements['item_total'].value;
	var tst = false;
	for(tmp=1; tmp<=cnt; tmp++) {
		if(document.forms[0].elements['bill_stat_'+tmp].checked==true) tst=true;
	}
	if(!tst) {
		alert("No Forms Are Selected....Nothing to do!");
		return false;
	}
	
	response=confirm("Mark All Selected Forms as Billed?");
	if(response == false) return false;
	document.forms[0].action='form_bill.php?bill=yes';
  document.forms[0].submit();
 }

 function CheckAll() {
	var cnt = document.forms[0].elements['item_total'].value;
	for(tmp=1; tmp<=cnt; tmp++) {
		if(document.forms[0].elements['bill_stat_'+tmp].disabled!=true) {	
			document.forms[0].elements['bill_stat_'+tmp].checked=true;		
		}
	}
 }

 function UncheckAll() {
	var cnt = document.forms[0].elements['item_total'].value;
	for(tmp=1; tmp<=cnt; tmp++) {
		document.forms[0].elements["bill_stat_"+tmp].checked=false;		
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
 var srch = document.forms[0].elements['billed_by'].value;
 if(srch == '') {
	alert("No User Selected!!");
  return false;
 }
 var target = '../../../custom/pin_check_popup.php?username='+srch;
 dlgopen(target, '_blank', 300, 200);
}

function ApprovePop(pid, id, enc, form)
{
	var warn_msg = '';
	if(pid == '' || pid == 0) warn_msg = 'Patient ID is NOT set - ';
	if(id == '' || id == 0) warn_msg = 'Form ID is NOT set - ';
	if(enc == '' || enc == 0) warn_msg = 'Encounter is NOT set - ';
	if(form == '' || form == 0) warn_msg = 'Form Directory is NOT set- ';
	if(warn_msg != '') {
		alert(warn_msg + 'Not Able to Pop Open this Form');
		return false;
	}
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/'+form+'/view.php?mode=update&pid='+pid+'&id='+id+'&enc='+enc, '_blank', 900, 900, 1);
}

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Billed/Unbilled Report','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='form_bill.php?mode=search'>

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
          <td><select name="form_provider" id="form_provider">
					<?php $filter = "AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
								"UPPER(specialty) LIKE '%SUPERVISOR%') ";
						UserSelect($form_provider, false, $filter, array('0' => 'None Assigned'), '-- ALL --');

          ?></select></td>
          <td class='label'><?php xl('From','e'); ?>: </td>
          <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
          <td class='label'><?php xl('Status','e'); ?>: </td>
          <td><?php
            // Build a drop-down list of form statuses.
            $query = "SELECT option_id, title FROM list_options WHERE ".
            "list_id = 'Form_Bill' ORDER BY seq";
            $ures = sqlStatement($query);

          	echo "   <select name='form_bill'>\n";
           	echo "    <option value=''>-- " . xl('All') . " --</option>\n";
	
          	while ($urow = sqlFetchArray($ures)) {
            	$statid = $urow{'option_id'};
             	echo "    <option value='$statid'";
             	if ($statid == $form_bill) echo " selected";
             	echo ">" . $urow{'title'} . "</option>\n";
           	}
           	echo "   </select>\n";
         ?></td>
         </tr>
         <tr>
          <td class='label'><?php xl('Form Name','e'); ?>: </td>
          <td><?php
              echo "   <select name='form_name'>\n";
              echo "    <option value=''>-- " . xl('All') . " --</option>\n";
							foreach($bill_forms as $frm) {
								if($frm['form_name'] != '') {
									echo "		<option value='".$frm['form_name']."'";
									if($frm['form_name'] == $form_name) { echo " selected"; }
									if($frm['nickname']) {
										echo ">".$frm['nickname']."</option>\n";
									} else {
										echo ">".$frm['name']."</option>\n";
									}
								}
							}
              echo "   </select>\n";
             ?></td>
          <td class='label'><?php xl('Supervisor','e'); ?>: </td>
          <td><select name='form_supervisor' id="form_supervisor">
					<?php
					$filter = "AND UPPER(specialty) LIKE '%SUPERVISOR%' ";
					UserSelect($form_supervisor, false, $filter, array('0' => 'None Assigned'), '-- ALL --');
					?>
          </select></td>
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
 if ($_POST['form_refresh']) {
?>
<div id="report_results">
<table>

 <thead>
<?php if ($form_details) { ?>
	<th>
		<?php xl('Bill','e'); ?>
	</th>
  <th>
		<?php xl('Bill Status','e'); ?>
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
  <th>
   <?php  xl('Form Status','e'); ?>
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
    $sql = "SELECT form_priority, form_complete FROM $this_form WHERE `id`= ?";
    $farray = sqlQuery($sql, array($this_id));
    $fbill = strtolower($farray{'form_priority'});
    $fstatus = strtolower($farray{'form_complete'});
    if (($fbill != $form_bill) && ($form_bill != '')) continue;
		$item++;
?>
 <tr>
	<td>
		<input name="bill_stat_<?php echo $item; ?>" id="bill_stat_<?php echo $item; ?>" type="checkbox" value="1" <?php echo ((($fbill == 'b') || ($fstatus == 'a'))?'disabled="disabled"':''); ?>/>
		<input name="bill_id_<?php echo $item; ?>" id="bill_id_<?php echo $item; ?>" type="hidden" value="<?php echo $row['form_id']; ?>" />
		<input name="bill_form_<?php echo $item; ?>" id="bill_form_<?php echo $item; ?>" type="hidden" value="<?php echo $row['formdir']; ?>" />
	</td>
  <td>
   <?php echo ListLook($fbill,'Form_Bill'); ?>&nbsp;
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
	<?php if((SearchMultiArray($row['formdir'], $pop_forms) !== false) && $pop_used) {
		echo "<a href='javascript:;' onclick=\"ApprovePop('".$row['pid']."', '".$row['form_id']."', '".$row['encounter']."', '".$row['formdir']."');\">\n";
	} ?>
   <?php echo $row['form_name']; ?>&nbsp;
	<?php if((SearchMultiArray($row['formdir'], $pop_forms) !== false) && $pop_used) { ?>
			</a>
		<?php } ?>
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
	<td colspan="2"><a href='javascript:;' class='css_button' onclick='BillSelected();'><span><?php xl('Mark As Billed','e'); ?></span></a></td>
</tr>

</tbody>
</table>
</div>  <!-- end report results -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type="hidden" name="form_refresh" id="form_refresh" value=""/>
<input name="item_total" id="item_total" type="hidden" value="<?php echo $item; ?>" />

</form>
</body>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
