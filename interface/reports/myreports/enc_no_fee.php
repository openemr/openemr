<?php
// Copyright (C) 2016-2019 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report monitors fee sheet statuses

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/billing.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'user'  => 'fe.provider_id, visit_dt',
  'patient' => 'lower(p.lname), lower(p.fname), visit_dt',
  'pubpid'  => 'lower(p.pubpid), visit_dt',
  'time'    => 'visit_dt',
);

$use_definable = FALSE;
$fres = sqlStatement('SHOW TABLES LIKE "%form_definable_fee%"');
$frow = sqlFetchArray($fres);
if($frow) $use_definable = TRUE;

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$last_year  = mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$next_month = mktime(0,0,0,date('m')+1,date('d'),date('Y'));
$next_year  = mktime(0,0,0,date('m'),date('d'),date('Y')+2);
$tomorrow   = mktime(0,0,0,date('m'),date('d')+1,date('Y'));
$form_from_date = date('Y-m-d');
$form_to_date = date('Y-m-d');
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
} else $_POST['form_from_date'] = DateToYYYYMMDD($_POST['form_from_date']);
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
} else $_POST['form_to_date'] = DateToYYYYMMDD($_POST['form_to_date']);
$form_user = '';
$form_no_pmts = false;
$form_no_chgs = true;
if(isset($_POST['form_refresh'])) $form_no_chgs = false;
if(isset($_POST['form_user'])) $form_user = $_POST['form_user'];
if(isset($_POST['form_no_pmts'])) $form_no_pmts = true;
if(isset($_POST['form_no_chgs'])) $form_no_chgs = true;

$form_orderby = 'user';
if(isset($_REQUEST['form_orderby'])) {
	$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'user';
}
$orderby = $ORDERHASH[$form_orderby];
$exclude = LoadList('Exclude_Enc_No_Fee_Categories','active');

$binds = array();
$query = 'SELECT f.deleted, ' .
	'fe.provider_id, fe.supervisor_id, SUBSTRING(fe.date,1,10) AS visit_dt, ' .
	'fe.encounter, fe.pc_catid, ' .
	'b.code_type, b.code, ' .
	'p.fname, p.mname, p.lname, p.pubpid, p.pid, p.DOB, ' .
	'u.lname AS ulast, u.fname AS ufirst ' .
	'FROM forms AS f ' .
	'LEFT JOIN form_encounter AS fe USING (encounter) ' .
	'LEFT JOIN billing AS b ON ( (b.encounter = fe.encounter) ' ;
$fee_filter = '';
foreach($code_types as $type => $vals) {
	if($vals['fee']) {
		if(!$fee_filter) {
			$fee_filter = 'AND (';
		} else {
			$fee_filter .= ' OR ';
		}
		$fee_filter .= 'b.code_type = ?';
		$binds[] = $type;
	}
}	
if($fee_filter) $fee_filter .= ') ';	
$query .= $fee_filter . ') ';
$query .= 'LEFT JOIN patient_data AS p ON (f.pid = p.pid) ' .
	'LEFT JOIN users AS u ON (u.id = fe.provider_id) ' .
  'WHERE f.deleted = 0 AND f.formdir = "newpatient" ';
if($form_no_chgs) $query .= 'AND b.code_type IS NULL ' ;
$enc_filter = '';
foreach($exclude as $e) {
	if(!$enc_filter) {
		$enc_filter = 'AND (';
	} else {
		$enc_filter .= ' AND ';
	}
	$enc_filter .= 'fe.pc_catid != ?';
	$binds[] = $e['option_id'];
}
if($enc_filter) $enc_filter .= ') ';
$query .= $enc_filter;
if ($form_to_date) {
  $query .= 'AND fe.date >= ? AND fe.date <= ? ';
	$binds[] = $form_from_date . ' 00:00:00';
	$binds[] = $form_to_date . ' 23:23:59';
} else {
  $query .= 'AND fe.date >= ? AND fe.date <= ? ';
	$binds[] = $form_from_date . ' 00:00:00';
	$binds[] = $form_from_date . ' 23:59:59';
}
if ($form_user) {
 	$query .= 'AND fe.provider_id = ? ';
	$binds[] = $form_user;
}
$query .= "ORDER BY $orderby";

 // echo "Query: $query<br>\n";
 // echo "Binds: ";
 // print_r($binds);
 // echo "<br>\n";
$res = sqlStatement($query, $binds);
?>
<html>
<head>
<title><?php xl('Encounters With No Fees','e'); ?></title>
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

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>

<script type="text/javascript">
<?php include($GLOBALS['srcdir'].'/wmt-v2/report_tools.inc.js'); ?>

function DelayedReload() {
	document.forms[0].submit();
	return true;
}

$(document).ready(function() {
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

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Encounters With No Fee Sheets','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='enc_no_fee.php'>

<div id="report_parameters">
<table>
 <tr>
  <td width='600px'>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
								"UPPER(specialty) LIKE '%SUPERVISOR%') ".
								"ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_user'>\n";
              echo "    <option value=''";
							if(!$form_user)  echo " selected"; 
							echo ">-- " . xl('All') . " --</option>\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_user) echo " selected";
                echo ">".$urow['lname'].", ".$urow['fname']."</option>\n";
              }
              echo "   </select>\n";
             ?></td>
           <td class='label'><?php xl('From','e'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
         </tr>
         <tr>
           <td colspan="5" class="text"><?php echo xl('Show Encounters With'); ?>&nbsp;&nbsp;<input name="form_no_pmts" id="form_no_pmts" type="checkbox" value="1" <?php echo $form_no_pmts ? 'checked="checked"' : ''; ?> /><label for="form_no_pmts"><?php echo xl('No Payments'); ?></label>
						&nbsp;&nbsp;&nbsp;
           <input name="form_no_chgs" id="form_no_chgs" type="checkbox" value="1" <?php echo $form_no_chgs ? 'checked="checked"' : ''; ?> /><label for="form_no_chgs"><?php echo xl('No Charges'); ?></label></td>
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
   <a href="nojs.php" onclick="return dosort('pubpid')"
   <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient ID','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?></a>
  </th>
  <th>
   <?php  xl('Fee Sheet','e'); ?>
  </th>
 </thead>
 <tbody>
<?php
$bgcolor = '#FFFFDD';
$cnt = 0;
$frow = array();
while ($row = sqlFetchArray($res)) {
	$bgcolor = ($bgcolor == '#FFDDDD') ? '#FFFFDD' : '#FFDDDD';
	if($use_definable) $frow = sqlQuery('SELECT * FROM forms WHERE ' .
		'encounter = ? AND formdir = ?', array($row{'encounter'},'definable_fee'));
	$pmts = sqlQuery('SELECT SUM(pay_amount) AS payments FROM ar_activity '.
		'WHERE pid = ? AND encounter = ?',array($row{'pid'}, $row{'encounter'}));
	if(!isset($pmts{'payments'})) $pmts{'payments'} = 0;
	if($form_no_pmts && $pmts{'payments'} != 0) continue;
	if(!isset($frow{'form_id'})) $frow{'form_id'} = '';
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td>
		<!-- a href="javascript:FormPop('<?php echo $row{'pid'}; ?>','<?php echo $frow{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', 'definable_fee');" -->
   <?php echo text($row{'ulast'} . ' ' . $row{'ufirst'}); ?>&nbsp;</a>
  </td>
  <td>
		<!-- a href="javascript:FormPop('<?php echo $row{'pid'}; ?>','<?php echo $frow{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', 'definable_fee');" -->
   <?php echo text(oeFormatShortDate($row{'visit_dt'})); ?>&nbsp;</a>
  </td>
  <td>
		<a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');">
   <?php echo $row{'pubpid'}; ?>&nbsp;</a>
  </td>
  <td>
		<a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');">
   <?php echo text($row{'lname'}); ?>, <?php echo text($row{'fname'}); ?>&nbsp;</a>
  </td>
  <td>
	<?php if($use_definable) { ?>
		<a href="javascript:FormPop('<?php echo $row{'pid'}; ?>','<?php echo $frow{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', 'definable_fee');">
   <?php echo ($frow{'form_id'}) ? xl('Edit Fee Sheet') : xl('Create Fee Sheet'); ?>&nbsp;</a>
	<?php } else {
		echo $frow{'code_type'} ? 'Fee Sheet Exists' : 'No Fee Sheet';
	} ?>
  </td>
 </tr>
<?php
	$cnt++;
 }
?>
</tbody>
</table>
<?php if(!$cnt) { ?>
	<br><span class="bold">&nbsp;&nbsp;*&nbsp;No Encounters With No Fees Were Found For Those Parameters</span><br>
<?php } ?>
</div>  <!-- end encresults -->

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script type='text/javascript'>
Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
