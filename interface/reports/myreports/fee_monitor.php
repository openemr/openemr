<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report monitors fee sheet statuses

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/wmt-v2/wmtstandard.inc");

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'user'  => 'fe.provider_id, fee.form_dt, oe.pc_startTime',
  'patient' => 'lower(p.lname), lower(p.fname), fee.form_dt',
  'pubpid'  => 'lower(p.pubpid), fee.form_dt',
  'time'    => 'fe.form_dt, oe.pc_startTime',
);

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$last_year = mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$next_month = mktime(0,0,0,date('m')+1,date('d'),date('Y'));
$next_year = mktime(0,0,0,date('m'),date('d'),date('Y')+2);
$tomorrow = mktime(0,0,0,date('m'),date('d')+1,date('Y'));
$form_from_date = date('Y-m-d');
$form_to_date = date('Y-m-d');
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
$form_user = array($_SESSION['authUserID']);
$user_test = checkSettingMode('wmt::fee_monitor_doc');
if($user_test) $form_user = explode('|', $user_test);
$form_status = 'i';
$_pass = 0;
if(isset($_POST['form_user'])) $form_user = $_POST['form_user'];
if(isset($_POST['form_status'])) $form_status = $_POST['form_status'];
if(isset($_POST['pass'])) $_pass = $_POST['pass'];
if(!isset($GLOBALS['wmt::fee_monitor_refresh'])) $GLOBALS['wmt::fee_monitor_refresh'] = 120;

$form_orderby = 'user';
if(isset($_REQUEST['form_orderby'])) {
	$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'user';
}
$orderby = $ORDERHASH[$form_orderby];
// echo "Selected Items: ",count($form_user),"<br>\n";
// print_r($form_user);
// echo "<br>\n";

$binds = array();
$query = 'SELECT ' .
	'fe.provider_id, fe.supervisor_id, SUBSTRING(fe.date,1,10) AS visit_dt, ' .
	'fe.encounter, f.formdir, f.form_id, ' .
	'p.fname, p.mname, p.lname, p.pubpid, p.pid, p.DOB, ' .
	'u.lname AS ulast, u.fname AS ufirst, ' .
	'oe.pc_catid, oe.pc_startTime, oe.pc_eventDate, ' .
	'oe.pc_apptstatus, oe.pc_aid, ' .
	'fee.form_complete, fee.form_dt FROM forms AS f ' .
  'LEFT JOIN form_encounter AS fe USING (encounter) ' .
  'LEFT JOIN form_definable_fee AS fee ON (f.form_id = fee.id) '.
	'LEFT JOIN patient_data AS p ON (f.pid = p.pid) ' .
	'LEFT JOIN openemr_postcalendar_events AS oe ON ((f.pid = oe.pc_pid) AND '.
	'(SUBSTRING(fe.date,1,10) = oe.pc_eventDate) AND ' .
	'(fe.pc_catid = oe.pc_catid)) LEFT JOIN users AS u ON (u.id = oe.pc_aid) ' .
  'WHERE f.deleted = 0 AND f.formdir = "definable_fee" ';
if ($form_to_date) {
  $query .= 'AND fe.date >= ? AND fe.date <= ? ';
	$binds[] = $form_from_date . ' 00:00:00';
	$binds[] = $form_to_date . ' 23:23:59';
} else {
  $query .= 'AND fe.date >= ? AND fe.date <= ? ';
	$binds[] = $form_from_date . ' 00:00:00';
	$binds[] = $form_from_date . ' 23:59:59';
}
$query .= 'AND (oe.pc_aid IS NULL OR oe.pc_aid = "" OR oe.pc_aid = 0';
$tmp = 1;
foreach($form_user as $dr) {
 	$query .= ' OR oe.pc_aid = ?';
	$binds[] = $dr;
	$tmp++;
}
$query .= ') ';

if ($form_status) {
  $query .= 'AND fee.form_complete = ? ';
	$binds[] = $form_status;
}
$query .= "ORDER BY $orderby";

// echo "Query: $query<br>\n";
// echo "Binds: ";
// print_r($binds);
// echo "<br>\n";
$res = sqlStatement($query, $binds);
// echo "Pass: $_pass<br>\n";
$_pass++;
?>
<html>
<head>
<title><?php xl('Fee Sheet Monitor','e'); ?></title>
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
<script type="text/javascript" src="../../../library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="../../../library/wmt-v2/report_tools.js"></script>

<script type="text/javascript">

function DelayedReload() {
	document.forms[0].submit();
	return true;
}

$(document).ready(function() {
	setTimeout("DelayedReload()", <?php echo ($GLOBALS['wmt::fee_monitor_refresh'] * 1000); ?>);
});

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

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Monitor','e'); ?> - <?php xl('Outstanding Fee Sheets','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='fee_monitor.php'>

<div id="report_parameters">
<table>
 <tr>
  <td width='600px'>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label'><?php xl('Status','e'); ?>: </td>
          <td><?php
              $query = "SELECT option_id, title FROM list_options WHERE ".
                "list_id = 'Form_Status' ORDER BY seq";
              $ures = sqlStatement($query);

              echo "   <select name='form_status'>\n";
              echo "    <option value=''>-- " . xl('All') . " --\n";

              while ($urow = sqlFetchArray($ures)) {
                echo '    <option value="'.$urow['option_id'].'"';
                if ($urow['option_id'] == $form_status) echo " selected";
                echo ">" . $urow['title'] . "\n";
              }
              echo "   </select>\n";
              ?></td>
          <td class='label'><?php xl('Provider','e'); ?>: </td>
          <td rowspan="4"><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND calendar = 1 ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_user[]' multiple size='5'>\n";
              // echo "    <option value=''";
							// if($form_user == '')  echo " selected"; 
							// echo ">-- " . xl('All') . " --</option>\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if (in_array($provid, $form_user)) echo " selected";
                echo ">".$urow['lname'].", ".$urow['fname']."</option>\n";
              }
              echo "   </select>\n";
             ?></td>
				</tr>
				<tr>
           <td class='label'><?php xl('From','e'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
				</tr>
				<tr>
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
          <div style='margin-left:15px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'><span><?php xl('Submit','e'); ?></span></a>
          </div>
        </td>
      </tr>
     </table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<div id="report_results">
<table>

 <thead>
  <th>
   <a href="nojs.php" onclick="return dosort('user')"
   <?php if ($form_orderby == "user") echo " style=\"color:#00cc00\"" ?>><?php  xl('Provider','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('time')"
   <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Date','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('time')"
   <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Time','e'); ?></a>
  </th>
  <th>
   <?php  xl('Appt Status','e'); ?>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('pubpid')"
   <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  xl('ID','e'); ?></a>
  </th>
  <th>
   <?php  xl('Form Status','e'); ?>
  </th>
 </thead>
 <tbody>
<?php
if ($res) {
	$bgcolor = '#F8F8F8';
	$cnt = 0;
  while ($row = sqlFetchArray($res)) {
		$bgcolor = ($bgcolor == '#E6E6E6') ? '#F8F8F8' : '#E6E6E6';
		if(!$row{'ulast'}) $row{'ulast'} = 'Appt/Enc MisMatch';
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td>
		<a href="javascript:FormPop('<?php echo $row{'pid'}; ?>','<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', 'definable_fee', '<?php echo $GLOBALS['webroot']; ?>');">
   <?php echo $row{'ulast'}; ?>&nbsp;</a>
  </td>
  <td>
		<a href="javascript:FormPop('<?php echo $row{'pid'}; ?>','<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', 'definable_fee', '<?php echo $GLOBALS['webroot']; ?>');">
   <?php echo $row{'form_dt'}; ?>&nbsp;</a>
  </td>
  <td>
		<a href="javascript:FormPop('<?php echo $row{'pid'}; ?>','<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', 'definable_fee', '<?php echo $GLOBALS['webroot']; ?>');">
   <?php echo $row{'pc_startTime'}; ?>&nbsp;</a>
  </td>
  <td>
		<a href="javascript:FormPop('<?php echo $row{'pid'}; ?>','<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', 'definable_fee', '<?php echo $GLOBALS['webroot']; ?>');">
   <?php echo ListLook($row{'pc_apptstatus'},'apptstat'); ?>&nbsp;</a>
  </td>
  <td>
		<a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');">
   <?php echo $row{'lname'}; ?>, <?php echo $row{'fname'}; ?>&nbsp;</a>
  </td>
  <td>
		<a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');">
   <?php echo $row{'pid'}; ?>&nbsp;</a>
  </td>
  <td>
		<a href="javascript:FormPop('<?php echo $row{'pid'}; ?>','<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', 'definable_fee', '<?php echo $GLOBALS['webroot']; ?>');">
   <?php echo ListLook($row{'form_complete'},'Form_Status',''); ?>&nbsp;</a>
  </td>
 </tr>
<?php
		$cnt++;
  }
}
?>
</tbody>
</table>
</div>  <!-- end encresults -->

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='pass' id='pass' value='<?php echo $_pass; ?>'/>

</form>
</body>

<script type='text/javascript'>
Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
