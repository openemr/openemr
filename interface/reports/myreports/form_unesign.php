<?php
// Copyright (C) 2019-2022 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report allows 'un-signing' of e-Signed encounters and/or forms.

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/registry.inc");
require_once("$srcdir/wmt-v2/wmtstandard.inc");

use OpenEMR\Core\Header;

$alertmsg = '';

$ORDERHASH = array(
  'patient' => 'lower(p.lname), lower(p.fname), fe.date',
  'pubpid'  => 'lower(p.pubpid), fe.date',
  'time'    => 'fe.date',
	'doctor'  => 'lower(ulast), lower(ufirst), fe.date'
);

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$form_from_date = fixDate(date('Y-m-d', $last_month));
$form_to_date = fixDate(date('Y-m-d'));
// echo "From ($form_from_date)  To [$form_to_date]<br>\n";
if(isset($_POST['form_from_date'])) {
	$tmp = DateToYYYYMMDD($_POST['form_from_date']);
	$form_from_date = fixDate($tmp, date('Y-m-d', $last_month));
}
if(isset($_POST['form_to_date'])) {
	$tmp = DateToYYYYMMDD($_POST['form_to_date']);
	$form_to_date = fixDate($tmp, date('Y-m-d'));
}
$form_provider = '';
$form_facility = '';
$form_name = '';
$form_status = '';
$form_orderby = 'pubpid';
$form_pid = '';
// print_r($_POST);
// echo "<br>";
if(isset($pid)) $form_pid = $pid;
if(isset($_REQUEST['form_orderby'])) $form_orderby = $_REQUEST['form_orderby'];
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_name'])) $form_name = $_POST['form_name'];
if(isset($_POST['form_status'])) $form_status = $_POST['form_status'];
if(isset($_POST['form_pid'])) $form_pid = $_POST['form_pid'];
$form_details   = TRUE;
// echo "Status ($form_status)<br>";

// HERE WE READ THE INPUTS FOR THE CHECKED BOXED AND UN-SIGN THEM
if(isset($_GET['approve'])) {
	$item = 1;
	while($item <= $_POST['item_total']) {
		if(!isset($_POST['approve_stat_'.$item])) 
								$_POST['approve_stat_'.$item] = '';
		if($_POST['approve_stat_'.$item]) {
			$frm_id = trim($_POST['approve_id_'.$item]);
			$frmdir = trim($_POST['form_formdir_'.$item]);

			$sql = "DELETE FROM esign_signatures WHERE id = ?";
			sqlStatement($sql, array($frm_id));
		}
		$item++;
	}
	$_POST['form_refresh'] = 'refresh';
}

$orderby = $ORDERHASH[$form_orderby];

$binds = array();
$query_from = $form_from_date . ' 00:00:00';
$query_to = $form_to_date ? $form_to_date : $form_from_date;
$query_to .= ' 23:59:59';
$query = 'SELECT ' .
  'es.*, f.formdir, f.form_name, f.deleted, f.form_id, ' .
  'fe.encounter, fe.date, fe.reason, ' .
	'u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle, '.
  'p.fname, p.mname, p.lname, p.pubpid, p.pid FROM ' .
	'esign_signatures AS es ' .
	'LEFT OUTER JOIN forms AS f ON (es.tid = IF(es.table = "forms", f.id, f.encounter)) ' .
	'LEFT JOIN form_encounter AS fe USING (encounter) '.
  'LEFT JOIN patient_data AS p ON f.pid = p.pid ' .
  'LEFT JOIN users AS u ON es.uid = u.id ' .
  'WHERE ' .
  'f.deleted != 1 AND fe.date >= ? AND fe.date <= ? ';
$binds[] = $query_from;
$binds[] = $query_to;

if ($form_facility) {
  $query .= 'AND fe.facility_id = ? ';
	$binds[] = $form_facility;
}
if ($form_provider !== '') {
  $query .= 'AND fe.provider_id = ? ';
	$binds[] = $form_provider;
}
if ($form_status !== '') {
  $query .= 'AND es.is_lock = ? ';
	$binds[] = $form_status;
}
if ($form_name) {
  $query .= 'AND f.formdir = ? ';
	$binds[] = $form_name;
}
if ($form_pid) {
  $query .= 'AND f.pid= ? ';
	$binds[] = $form_pid;
}
$query .= "ORDER BY $orderby";
// echo "Query: $query<br>";
// echo "Binds: ";
// print_r($binds);
// echo "<br>";

$res=array();
if(isset($_GET['mode']) || isset($_GET['approve']) || 
	isset($_POST['form_orderby'])) $res = sqlStatement($query, $binds);
$item = 0;

// echo "Status ($form_status)<br>";
?>
<html>
<head>
<title><?php echo xlt('Un-sign Forms'); ?></title>

<?php Header::setupHeader(['datetime-picker', 'report-helper', 'dialog', 'opener']); ?>

<!-- link rel=stylesheet href="<?php echo $css_header;?>" type="text/css" -->
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
<?php
$js_location = $GLOBALS['webroot'] . '/library/js';
if($v_major > 4) $js_location = $GLOBALS['assets_static_relative'];
?>
<?php if($v_major < 5) { ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/overlib_mini.js"></script>
<script type="text/javascript" src="<?php echo $js_location; ?>/jquery.1.7.2.min.js"></script>
<script type="text/javascript" src="<?php echo $js_location; ?>/jquery-ui.js"></script>
<?php } ?>

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
	
	response=confirm("Unlock All Checked Forms?\n\nAre you sure you wabt to do this?");
	if(response == false) return false;
	document.forms[0].action='form_unesign.php?approve=yes';
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
<?php if($v_major < 5 || ($v_minor || $v_patch)) { ?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php } ?>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Unlock Approved Forms'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='form_unesign.php?mode=search'>

<div id="report_parameters">
<table>
 <tr>
  <td>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td><b><?php echo xlt('Facility'); ?>: </b></td>
          <td>
	    <?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?></td>
          <td><b><?php echo xlt('Provider'); ?>: </b></td>
          <td><?php
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND (specialty LIKE '%Provider%' OR ".
								"specialty LIKE '%Supervisor%') ".
								"ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_provider' id='form_provider' class='form-control'>\n";
              echo "    <option value=''>-- " . xl('All') . " --</option>\n";
              echo "    <option value='0'";
							if($form_provider === '0') echo " selected";
							echo ">-- " . xl('None Assigned') . " --</option>\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_provider) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
              }
              echo "   </select>\n";
              ?></td>
           	<td><b><?php echo xlt('From'); ?>: </b></td>
           	<td>
             <input type='entry' name='form_from_date' id="form_from_date" size='16' class="datepicker form-control" value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
         </tr>
         <tr>
          <td><b><?php echo xlt('Form Name'); ?>: </b></td>
          <td><?php
              echo "   <select name='form_name' id='form_name' class='form-control'>\n";
              echo "    <option value=''>-- " . xl('All') . " --</option>\n";
							$sel_forms = getRegistered();
							foreach($sel_forms as $frm) {
								echo "		<option value='".$frm['directory']."'";
								if($frm['directory'] == $form_name) echo " selected";
								if($frm['nickname']) {
									echo ">".text($frm['nickname'])."</option>\n";
								} else {
									echo ">".text($frm['name'])."</option>\n";
								}
							}
              echo "   </select>\n";
              ?></td>
          <td><b><?php echo xlt('Status'); ?>: </b></td>
<?php // echo "Status ($form_status)<br>"; ?>
          <td><?php
            echo "<select name='form_status' id='form_status' class='form-control'>\n";
            echo "<option value=''";
						echo ($form_status === '') ? ' selected' : '';
						echo ">-- " . xlt('All') . " --</option>\n";
            echo "<option value='1'";
						echo ($form_status === '1') ? ' selected' : '';
						echo ">" . xlt('Locked') . "</option>\n";
            echo "<option value='0'";
						echo ($form_status === '0') ? ' selected' : '';
						echo ">" . xlt('Unlocked') . "</option>\n";
            echo "</select>\n";
           ?></td>
<?php // echo "Status ($form_status)<br>"; ?>
           <td><b><?php echo xlt('To'); ?>: </b></td>
           <td>
             <input type='entry' name='form_to_date' id="form_to_date" size='16' class="form-control datepicker" value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>' />
         </tr>
					<tr>
						<td><b><?php echo xlt('Patient ID'); ?>: </b></td>
						<td><input name="form_pid" id="form_pid" type="entry" class="form-control" value="<?php echo $form_pid; ?>" /></td>
					</tr>
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:10px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'><span><?php echo xlt('Submit'); ?></span></a>
          </div>
        </td>
      </tr>
			<tr>
				<td>
          <div style='margin-left:10px'>
            <?php if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) { ?>
            <a href='#' class='css_button' onclick='window.print()'><span><?php echo xlt('Print'); ?></span></a>
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
	<th><?php echo xlt('Unlock'); ?></th>
  <th>
		<a href="nojs.php" onclick="return dosort('doctor');"
		<?php if($form_orderby == 'doctor') echo "style='color:#00CC00'"; ?>><?php echo xlt('Provider'); ?></a>
  </th>
  <th>
		<a href="nojs.php" onclick="return dosort('time');"
		<?php if($form_orderby == 'time') echo "style='color:#00CC00'"; ?>><?php echo xlt('Date'); ?></a>
  </th>
  <th>
		<a href="nojs.php" onclick="return dosort('patient');"
		<?php if($form_orderby == 'patient') echo "style='color:#00CC00'"; ?>><?php echo xlt('Patient'); ?></a>
  </th>
  <th>
		<a href="nojs.php" onclick="return dosort('pubpid');"
		<?php if($form_orderby == 'pubpid') echo "style='color:#00CC00'"; ?>><?php echo xlt('ID'); ?></a>
  </th>
  <th><?php echo xlt('Status'); ?></th>
  <th><?php echo xlt('Encounter'); ?></th>
  <th><?php echo xlt('Form'); ?></th>
<?php } else { ?>
  <th><?php echo xlt('Provider'); ?></td>
  <th><?php echo xlt('Encounters'); ?></td>
<?php } ?>
 </thead>
 <tbody>
<?php
if($res) {
  $lastdocname = "";
  $doc_encounters = 0;
  while ($row = sqlFetchArray($res)) {
    $errmsg  = "";
		$item++;
?>
 <tr>
	<td>
		<input name="approve_stat_<?php echo $item; ?>" id="approve_stat_<?php echo $item; ?>" type="checkbox" value="1" <?php echo $row{'is_lock'} ? '' : 'disabled="disabled"'; ?>/>
		<input name="approve_id_<?php echo $item; ?>" id="approve_id_<?php echo $item; ?>" type="hidden" value="<?php echo $row{'id'}; ?>" />
		<input name="approve_form_<?php echo $item; ?>" id="approve_form_<?php echo $item; ?>" type="hidden" value="<?php echo $row{'formdir'}; ?>" />
	</td>
  <td><?php echo text($row['ulast'].', '.$row['ufirst']); ?>&nbsp; </td>
  <td><?php echo text(oeFormatShortDate(substr($row['date'], 0, 10))); ?>&nbsp; </td>
  <td><?php echo text($row['lname'].', '.$row['fname'].' '.$row['mname']); ?>&nbsp; </td>
  <td><?php echo $row['pubpid']; ?>&nbsp; </td>
  <td><?php echo $row['is_lock'] ? xlt('Locked') : xlt('Unlocked') ; ?>&nbsp; </td>
  <td><?php echo text($row['encounter']); ?>&nbsp; </td>
  <td><?php echo ($row['form_name'] == 'New Patient Encounter') ? 'Encounter' : $row['form_name']; ?>&nbsp; <input name="encounter_<?php echo $item; ?>" id="encounter_<?php echo $item; ?>" type="hidden" value="<?php echo $row['encounter']; ?>" /> </td>
 </tr>
<?php
    $lastdocname = $row['ulast'].', '.$row['ufirst'];
  }
}
?>
<tr>
	<td><img class="selectallarrow" width="32" height="20" alt="With Selected:" src="../../pic/arrow_ltr.png"></td>
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

<script type='text/javascript'>
<?php if($v_major > 4 && ($v_minor || $v_patch)) { ?>
$('.datepicker').datetimepicker({
  <?php $datetimepicker_timepicker = false; ?>
  <?php $datetimepicker_showseconds = false; ?>
  <?php $datetimepicker_formatInput = true; ?>
  <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
  <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
});
<?php } else { ?>
Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
<?php } ?>

<?php if ($alertmsg) echo " alert('$alertmsg');\n"; ?>

</script>

</html>
