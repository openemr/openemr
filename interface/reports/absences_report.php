<?php 
// Copyright (C) 2007, 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This module is for team sports use and reports on absences by
// injury type (diagnosis) for a given time period.

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/acl.inc");
require_once("../../custom/code_types.inc.php");

// Might want something different here.
//
// if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$from_date = fixDate($_POST['form_from_date']);
$to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_by   = $_POST['form_by'];
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Absences by Diagnosis','e'); ?></title>
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script language="JavaScript">
 var mypcc = '<?php  echo $GLOBALS['phone_country_code'] ?>';
</script>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<center>

<h2><?php  xl('Days and Games Missed','e'); ?></h2>

<form name='theform' method='post' action='absences_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
  <?php  xl('By:','e'); ?>
   <input type='radio' name='form_by' value='d'
    <?php  echo ($form_by == 'p') ? '' : 'checked' ?> /><?php  xl('Diagnosis','e'); ?>&nbsp;
   <input type='radio' name='form_by' value='p'
    <?php  echo ($form_by == 'p') ? 'checked' : '' ?> /><?php  xl('Player','e'); ?> &nbsp;
   <?php  xl('From:','e'); ?>
   <input type='text' name='form_from_date' size='10' value='<?php  echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;<?php  xl('To:','e'); ?>
   <input type='text' name='form_to_date' size='10' value='<?php  echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;
   <input type='submit' name='form_refresh' value='<?php  xl('Refresh','e'); ?>'>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
<?php   if ($form_by == 'p') { ?>
  <td class="dehead">
   <?php  xl('Name','e'); ?>
  </td>
<?php  } else { ?>
  <td class="dehead">
   <?php  xl('Code','e'); ?>
  </td>
  <td class="dehead">
   <?php  xl('Description','e'); ?>
  </td>
<?php  } ?>
  <td class='dehead' align='right'>
   <?php  xl('Issues','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <?php  xl('Days','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <?php  xl('Games','e'); ?>
  </td>
 </tr>
<?php 
 if ($_POST['form_refresh']) {
  $form_doctor = $_POST['form_doctor'];
  $from_date = fixDate($_POST['form_from_date']);
  $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

  $query = "SELECT lists.diagnosis, lists.pid, lists.type, " .
    "lists.extrainfo AS gmissed, " .
    "lists.begdate, lists.enddate, lists.returndate, " .
    "pd.lname, pd.fname, pd.mname " .
    "FROM lists " .
    "JOIN patient_data AS pd ON pd.pid = lists.pid " .
    "WHERE ( lists.returndate IS NULL OR lists.returndate >= '$from_date' ) AND " .
    "( lists.begdate IS NULL OR lists.begdate <= '$to_date' )" .
    "ORDER BY pd.lname, pd.fname, pd.mname, lists.pid, lists.begdate";
  $res = sqlStatement($query);

  $areport = array();
  $last_listid  = 0;
  $last_pid     = 0;
  $last_endsecs = 0;

  while ($row = sqlFetchArray($res)) {
    $thispid = $row['pid'];
    // Compute days missed.  Force non-overlap of multiple issues for the
    // same player.  This logic assumes sorting on begdate within pid.
    $begsecs = $row['begdate'] ? strtotime($row['begdate']) : 0;
    $endsecs = $row['returndate'] ? strtotime($row['returndate']) : time();
    if ($thispid == $last_pid) {
      if ($begsecs < $last_endsecs) {
        $begsecs = $last_endsecs;
      }
    }
    else {
      $last_pid = $thispid;
      $last_endsecs = 0;
      $ptname = trim($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']);
    }
    $daysmissed = 0;
    if ($row['begdate']) {
      if ($begsecs > $endsecs) $begsecs = $endsecs;
      if ($last_endsecs < $endsecs) $last_endsecs = $endsecs;
      $daysmissed = round(($endsecs - $begsecs) / (60 * 60 * 24));
    }
    if ($form_by == 'p') {
      $key = $ptname;
    } else {
      $key = $row['diagnosis'];
    }
    if (empty($areport[$key])) {
      $areport[$key] = array();
      $areport[$key]['count']   = 0;
      $areport[$key]['dmissed'] = 0;
      $areport[$key]['gmissed'] = 0;
    }
    $areport[$key]['count']   += 1;
    $areport[$key]['dmissed'] += $daysmissed;
    $areport[$key]['gmissed'] += $row['gmissed'];
  }

  ksort($areport);

  foreach ($areport as $key => $row) {
?>

 <tr>
<?php if ($form_by == 'p') { ?>
  <td class='detail'>
   <?php echo $key ?>
  </td>
<?php } else { ?>
  <td class='detail'>
   <?php echo $key ?>
  </td>
  <td class='detail'>
   <?php echo empty($key) ? xl('No Diagnosis') : lookup_code_descriptions($key) ?>
  </td>
<?php  } ?>
  <td class='detail' align='right'>
   <?php echo $row['count'] ?>
  </td>
  <td class='detail' align='right'>
   <?php echo $row['dmissed'] ?>
  </td>
  <td class='detail' align='right'>
   <?php echo $row['gmissed'] ?>
  </td>
 </tr>
<?php 
  }
 }
?>

</table>
</form>
</center>
</body>
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</html>
