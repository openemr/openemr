<?php
// Copyright (C) 2016-2019 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

use OpenEMR\Core\Header;

$alertmsg = '';
set_time_limit(0);

$ORDERHASH = array(
  'pid'     => 'pid',
  'patient' => 'lower(p.lname), lower(p.fname)',
  'pubpid'  => 'lower(p.pubpid)',
  'date'    => 'd.date'
);

$form_orderby = 'pid';
if(isset($_REQUEST['form_orderby'])) {
	$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'pid';
}
$orderby = $ORDERHASH[$form_orderby];
$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$form_from_date = fixDate($last_month, date('Y-m-d'));
$form_to_date = date('Y-m-d');
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
} else $_POST['form_from_date'] = DateToYYYYMMDD($form_from_date);
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
} else $_POST['form_to_date'] = $form_to_date;

?>
<html>
<head>
<title><?php echo xlt('Missing Documents'); ?></title>
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

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Missing Documents'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='missing_documents.php'>

<div id="report_parameters">
<table>
 <tr>
  <td width='600px'>
    <div style='float:left'>

      <table class='text'>
        <tr>
           <td class='label'><?php echo xlt('From'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
           <td class='label'><?php echo xlt('To'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
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
   <a href="nojs.php" onclick="return dosort('pid')"
   <?php if ($form_orderby == "user") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Provider'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('pubpid')"
   <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Patient ID'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Patient'); ?></a>
  </th>
  <th><?php echo xlt('Category'); ?></th>
  <th>
   <a href="nojs.php" onclick="return dosort('date')"
   <?php if ($form_orderby == "date") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Date'); ?></a>
  </th>
  <th><?php echo xlt('Document'); ?></th>
 </thead>
 <tbody>
<?php
$bgcolor = '#FFFFDD';
$cnt = 0;
$start_id = 0;
$res = sqlQuery('SELECT MAX(`id`) AS max from `documents` WHERE 1');
$max_id = $res{'max'};

if(!$_POST['form_refresh']) $start_id = $max_id + 1;

while($start_id <= $max_id) {

	$binds = array();
	$query = 'SELECT d.*, p.`pid`, p.`pubpid`, p.`lname`, p.`fname`, ' .
		'p.`mname`, p.`DOB`, c.`name` ' .
		'FROM `documents` AS d ' .
		'LEFT JOIN `patient_data` AS p ON (d.`foreign_id` = p.`pid`) ' .
		'LEFT JOIN `categories_to_documents` AS dc ON (d.`id` = dc.`document_id`) '.
		'LEFT JOIN `categories` AS c ON (dc.`category_id` = c.`id`) ' .
		'WHERE `type` = "file_url" AND d.`id` > ?';
	$binds[] = $start_id;
	if ($form_to_date) {
  	$query .= ' AND d.`date` >= ? AND d.`date` <= ? ';
		$binds[] = $form_from_date . ' 00:00:00';
		$binds[] = $form_to_date . ' 23:23:59';
	} else {
  	$query .= ' AND d.`date` >= ? AND d.`date` <= ? ';
		$binds[] = $form_from_date . ' 00:00:00';
		$binds[] = $form_from_date . ' 23:59:59';
	}
	$query .= "ORDER BY $orderby LIMIT 5000";
	
 	// echo "Query: $query<br>\n";
 	// echo "Binds: ";
 	// print_r($binds);
 	// echo "<br>\n";

	$res = sqlStatement($query, $binds);
	$new_id = $start_id;
	while ($row = sqlFetchArray($res)) {
		$new_id = $row{'id'};
		$document = explode('/', $row{'url'});
		$document_name = array_pop($document);
		$file = substr($row{'url'}, 7);
		$exists = file_exists($file);
		if($exists) continue;
		$bgcolor = ($bgcolor == '#FFDDDD') ? '#FFFFDD' : '#FFDDDD';
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td><a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');">
		<?php echo $row{'pid'}; ?>&nbsp;</a></td>
  <td><a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');">
		<?php echo text($row{'pubpid'}); ?>&nbsp;</a></td>
	<td><a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');">
		<?php echo text($row{'lname'} . ' ' . $row{'fname'}); ?>&nbsp;</a></td>
  <td><?php echo text($row{'name'}); ?>&nbsp;</td>
  <td><?php echo text(oeFormatShortDate($row{'date'})); ?>&nbsp;</td>
  <td><?php echo text($document_name); ?>&nbsp;</td>
 </tr>
<?php
		$cnt++;
 	}
	if($start_id == $new_id) {
		$start_id = $start_id + 5000;
	} else $start_id = $new_id;
}
?>
</tbody>
</table>
<?php if(!$cnt && $_POST['form_refresh']) { ?>
	<br><span class="bold">&nbsp;&nbsp;*&nbsp;<?php echo xlt('No Missing Documents Found For Those Dates'); ?></span><br>
<?php } ?>
</div>  <!-- end encresults -->

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script type='text/javascript'>
Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});

<?php if ($alertmsg) echo " alert('$alertmsg');\n"; ?>

</script>

</html>
