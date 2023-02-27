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
require_once("$srcdir/patient.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/wmt-v2/wmtstandard.inc");

use OpenEMR\Core\Header;

$date_mode = checkSettingMode('wmt::rto_monitor_date_mode');
if(!$date_mode) $date_mode = 'rto_target_date';

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'user'  => 'lower(rto.rto_resp_user), rto.rto_target_date',
  'patient' => 'lower(pd.lname), lower(pd.fname), rto.rto_target_date',
  'pubpid'  => 'lower(pd.pubpid), rto.rto_target_date',
  'due'    => 'rto.rto_target_date',
  'create'    => 'rto.rto_date',
  'ordered'  => 'lower(rto.rto_ordered_by), rto.rto_target_date',
);

$last_month = mktime(0,0,0, date('m')-1, date('d'),  date('Y'));
$last_year  = mktime(0,0,0, date('m'),   date('d'),  date('Y')-1);
$next_month = mktime(0,0,0, date('m')+1, date('d'),  date('Y'));
$next_year  = mktime(0,0,0, date('m'),   date('d'),  date('Y')+2);
$tomorrow   = mktime(0,0,0, date('m'),   date('d')+1, date('Y'));
$form_from_date = date('Y-m-d', $last_year);
$form_to_date   = date('Y-m-d', $next_year);
if(isset($_POST['form_from_date'])) 
    $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
if(isset($_POST['form_to_date']))
    $form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_user = $_SESSION['authUser'];
if(isset($GLOBALS['wmt::task_monitor_user'])) 
    $form_user = $GLOBALS['wmt::task_monitor_user'];
$form_status = 'p';
$form_action= '';
$form_ordered_by = '';
$form_show_comments = '';
$form_date_mode = $date_mode;
$_pass = 0;
if(isset($_POST['pass'])) $_pass = $_POST['pass'];
if(isset($_POST['form_date_mode'])) $form_date_mode = $_POST['form_date_mode'];
if(!isset($_POST['form_no_date'])) {
  $form_no_date = ($_pass) ? false : true;
} else $form_no_date = true;
if(!isset($_POST['form_show_comments'])) {
  $form_show_comments = ($_pass) ? false : true;
} else $form_show_comments = true;
if(isset($_POST['form_user'])) $form_user = $_POST['form_user'];
if(isset($_POST['form_status'])) $form_status = $_POST['form_status'];
if(isset($_POST['form_action'])) $form_action= $_POST['form_action'];
if(isset($_POST['form_ordered_by'])) $form_ordered_by=$_POST['form_ordered_by'];
if(!isset($GLOBALS['wmt::task_monitor_refresh'])) 
    $GLOBALS['wmt::task_monitor_refresh'] = 120;

$use_msg_group = FALSE;
$tst = sqlQuery('SHOW TABLES LIKE "msg_group_link"');
if($tst) $use_msg_group = TRUE;

$form_orderby = 'due';
if(isset($_REQUEST['form_orderby'])) $form_orderby = 
    $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'due';
if($date_mode == 'rto_date') {
  $form_orderby = 'create';
  if(isset($_REQUEST['form_orderby'])) $form_orderby = 
    $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'create';
}
$orderby = $ORDERHASH[$form_orderby];

$binds = array();
$query = "SELECT " .
  'rto.rto_ordered_by AS requester, rto.pid, rto.date, rto.id, rto_status, ' . 
  'rto_resp_user, rto_target_date, rto_action, rto.rto_notes, rto.rto_date, ' .
  'pd.fname, pd.mname, pd.lname, pd.pubpid ' .
  'FROM form_rto AS rto ' .
  'LEFT JOIN patient_data AS pd USING (pid) ' .
  'RIGHT JOIN list_options AS lo ON (rto_status = lo.option_id AND ' .
  'lo.list_id = "RTO_Status" AND lo.activity > 0) ' .
  'WHERE rto.pid != "" ';
if ($form_to_date) {
  $query .= "AND (($form_date_mode >= ? AND $form_date_mode <= ?) ";
  $binds[] = $form_from_date;
  $binds[] = $form_to_date;
} else {
  $query .= "AND (($form_date_mode >= ? AND $form_date_mode <= ?) ";
  $binds[] = $form_from_date;
  $binds[] = $form_from_date;
}
if($form_no_date) {
  $query .= "OR ($form_date_mode IS NULL OR $form_date_mode = '') ";
}
$query .= ') ';
if ($form_ordered_by) {
  $query .= 'AND form_rto.rto_ordered_by = ? ';
  $binds[] = $form_ordered_by;
}
if ($form_user) {
  $binds[] = $form_user;
  $query .= "AND ( rto_resp_user = ? ";
  $sql = 'SELECT users.username, msg_group_link.* FROM users ';
  if($use_msg_group) {
    $sql .= 'RIGHT JOIN msg_group_link ON (users.id = msg_group_link.user_id) '.
        'WHERE users.username=?';
    $grps = sqlStatement($sql, array($form_user));
    while($grp = sqlFetchArray($grps)) {
      $query .= ' OR rto_resp_user = ?';
      $binds[] = 'GRP:' . $grp['group_id'];
    }
  }
  $query .= ') ';
}
if ($form_status) {
  $query .= 'AND rto.rto_status= ? ';
  $binds[] = $form_status;
}
if ($form_action) {
  $query .= 'AND rto.rto_action= ? ';
  $binds[] = $form_action;
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
<title><?php echo text(xl('Task Monitor')); ?></title>

<?php Header::setupHeader(['jquery', 'datetime-picker']); ?>

<link rel=stylesheet href="<?php //echo $css_header;?>" type="text/css">
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
if($v_major < 5 && (!$v_minor && !$v_patch)) {
?>
<?php } ?>

<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'];?>/library/wmt/wmt.default.css" type="text/css">
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>

<script type="text/javascript">

function DelayedReload() {
  document.forms[0].submit();
  return true;
}

$(document).ready(function() {
  setTimeout("DelayedReload()", <?php echo ($GLOBALS['wmt::task_monitor_refresh'] * 1000); ?>);
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

function PopRTO(pid, id) {
  wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto/new.php?pop=yes&pid='+pid+'&id='+id, '_blank', 1200, 400);
}

</script>

</head>
<body class="body_top" style="background-color: #F4F4F4;">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo text(xl('Monitor')); ?> - <?php echo text(xl('Tasks/Orders')); ?> <?php echo text(xl('for')); ?> <?php echo text(UserNameFromName($form_user)); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='rto_monitor.php'>

<div id="report_parameters">
<table>
 <tr>
  <td width='650px'>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label'><?php echo text(xl('Status')); ?>: </td>
          <td><?php
              $query = "SELECT option_id, title FROM list_options WHERE ".
                "list_id = 'RTO_Status' ORDER BY seq";
              $ures = sqlStatement($query);

              echo "   <select name='form_status' class='form-control'>\n";
              echo "    <option value=''>-- " . xl('All') . " --\n";

              while ($urow = sqlFetchArray($ures)) {
                $statid = $urow['option_id'];
                echo "    <option value='$statid'";
                if ($statid == $form_status) echo " selected";
                echo ">" . $urow['title'] . "\n";
              }
              echo "   </select>\n";
              ?></td>
           <td class='label'><?php echo text(xl('From')); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' class='datepicker form-control' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <!-- <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php //xl('Click here to choose a date','e'); ?>'> --></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' class='datepicker form-control' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <!-- <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php //xl('Click here to choose a date','e'); ?>'>--></td>
         </tr>
         <tr>
           <td colspan="2">
             <input type='checkbox' name='form_no_date' id="form_no_date" value='1' <?php echo $form_no_date ? "checked='checked' " : ""; ?> />
             <label for="form_no_date" class='label'>Include tasks with no target date?</label></td>
            <td colspan="2">Use Date&nbsp;<input name="form_date_mode" id="rto_date" type="radio" <?php echo $form_date_mode == 'rto_date' ? 'checked' : ''; ?> value="rto_date" /><label for="rto_date">&nbsp;Created</label>&nbsp;&nbsp;or&nbsp;
            <input name="form_date_mode" id="rto_target_date" type="radio" <?php echo $form_date_mode == 'rto_target_date' ? 'checked' : ''; ?> value="rto_target_date" /><label for="rto_target_date">&nbsp;Due</label></td>
           <td colspan="2">
             <input type='checkbox' name='form_show_comments' id="form_show_comments" value='1' <?php echo $form_show_comments? "checked='checked' " : ""; ?> />
             <label for="form_show_comments" class='label'>Show comments?</label></td>
         </tr>
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:15px'>
            <a href='#' class='btn btn-primary css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'><span><?php xl('Submit','e'); ?></span></a>
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
<table class="table">

 <thead class="thead-light">
  <th>
   <a href="nojs.php" onclick="return dosort('create')"
   <?php if ($form_orderby == "create") echo " style=\"color:#00cc00\"" ?>><?php  xl('Create Date','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('due')"
   <?php if ($form_orderby == "due") echo " style=\"color:#00cc00\"" ?>><?php  xl('Due Date','e'); ?></a>
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
   <?php  xl('Order Type','e'); ?>
  </th>
  <th>
   <?php  xl('Last Encounter','e'); ?>
  </th>
 </thead>
 <tbody>
<?php
if ($res) {
  $sql = 'SELECT * FROM form_encounter WHERE pid=? ORDER BY date DESC LIMIT 1';
  $doc_encounters = 0;
  while ($row = sqlFetchArray($res)) {
    $action=ListLook($row['rto_action'],'RTO_Action');
    $pubpid= $row['pid'];
    $id= $row['id'];
    $errmsg  = "";
    $enc = sqlQuery($sql, array($row{'pid'}));
    $dolv = '--';
    if($enc{'date'}) $dolv = oeFormatShortDate(substr($enc{'date'},0,10));
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td>
    <a href="javascript:PopRTO('<?php echo $pubpid; ?>','<?php echo $id; ?>');">
   <?php echo oeFormatShortDate(substr($row['rto_date'], 0, 10)) ?>&nbsp;</a>
  </td>
  <td>
    <a href="javascript:PopRTO('<?php echo $pubpid; ?>','<?php echo $id; ?>');">
   <?php echo oeFormatShortDate(substr($row['rto_target_date'], 0, 10)) ?>&nbsp;</a>
  </td>
  <td>
    <a href="javascript:PopRTO('<?php echo $pubpid; ?>','<?php echo $id; ?>');">
   <?php echo $row['lname']; ?>&nbsp;</a>
  </td>
  <td>
    <a href="javascript:PopRTO('<?php echo $pubpid; ?>','<?php echo $id; ?>');">
   <?php echo $row['pubpid']; ?>&nbsp;</a>
  </td>
  <td>
    <a href="javascript:PopRTO('<?php echo $pubpid; ?>','<?php echo $id; ?>');">
   <?php echo $action; ?>&nbsp;</a>
  </td>
  <td>
   <?php echo $dolv; ?>&nbsp;</a>
  </td>
 </tr>
  <?php if($form_show_comments && $row{'rto_notes'}) { ?>
 <tr>
  <td>&nbsp;</td>
  <td colspan="4"><?php echo htmlspecialchars($row{'rto_notes'},ENT_QUOTES); ?></td>
 </tr>
  <?php } ?>
<?php
  }
}
?>
</tbody>
</table>
</div>
<?php } else { ?>
<div class='text'>
  <?php echo text(xl('Please input search criteria above, and click Submit to view results')); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='pass' id='pass' value='<?php echo $_pass; ?>'/>

</form>
</body>

<script type='text/javascript'>
//Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
//Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

<?php if ($alertmsg) echo " alert('$alertmsg');\n"; ?>

</script>

</html>
