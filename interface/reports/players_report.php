<?php
// Copyright (C) 2005-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report lists all players/patients by name within squad.
// It is applicable only for sports teams.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/calendar_events.inc.php");

// Temporary variable while new logic is being tested.
// True means that missing days in the daily_fitness table default to
// the previous entry's values, if there is one.
// Otherwise the default fitness level (Fully Fit) is used.
$PROPLOGIC = true;

$squads = acl_get_squads();
$auth_notes_a  = acl_check('encounters', 'notes_a');

$alertmsg = ''; // not used yet but maybe later

$form_date = fixDate($_POST['form_date'], date('Y-m-d'));

// $now = time();
$now = mktime(0, 0, 0, substr($form_date, 5, 2),
  substr($form_date, 8, 2), substr($form_date, 0, 4));

// Get attributes of the default fitless level.
$fdefault = sqlQuery("SELECT * FROM list_options WHERE " .
  "list_id = 'fitness' ORDER BY is_default DESC, seq ASC LIMIT 1");

$query = "SELECT pid, squad, fitness, lname, fname FROM patient_data";
$res = sqlStatement($query);

// Sort the patients in squad priority order.
function patient_compare($a, $b) {
  global $squads;
  if ($squads[$a['squad']][3] == $squads[$b['squad']][3]) {
    if ($a['lname'] == $b['lname']) {
      return ($a['fname'] < $b['fname']) ? -1 : 1;
    }
    return ($a['lname'] < $b['lname']) ? -1 : 1;
  }
  // The squads are different so compare their order attributes,
  // or unassigned squads sort last.
  if (! $squads[$a['squad']][3]) return 1;
  if (! $squads[$b['squad']][3]) return -1;
  return ($squads[$a['squad']][2] < $squads[$b['squad']][2]) ? -1 : 1;
}
//
$ordres = array();
if ($res) {
  while ($row = sqlFetchArray($res)) $ordres[] = $row;
  usort($ordres, "patient_compare");
}
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

<style type="text/css">
</style>

<?php if (empty($_GET['embed'])) { ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/topdialog.js"></script>
<?php } ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajtooltip.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>

<script language="JavaScript">
<?php if (empty($_GET['embed'])) require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 var mypcc = '<?php  echo $GLOBALS['phone_country_code'] ?>';

 function gopid(pid) {
<?php
$maintop = $_GET['embed'] ? "top" : "opener.top";
echo "  $maintop.restoreSession();\n";
if ($GLOBALS['concurrent_layout']) {
  echo "  $maintop.RTop.location = '../patient_file/summary/demographics.php?set_pid=' + pid;\n";
  // echo "  $maintop.left_nav.forceDual();\n"; // Decided not to do this.
} else {
  echo "  $maintop.location = '../patient_file/patient_file.php?set_pid=' + pid;\n";
}
if (empty($_GET['embed'])) echo "  window.close();\n";
?>
 }

 // Process click to pop up the dialog window.
 function rosdlgclick(pid, date) {
  cascwin('players_report_dialog.php?plid=' + pid + '&date=' + date,
   '_blank', 850, 550, "resizable=1,scrollbars=1");
 }

 function mov1(elem, plid) {
  ttMouseOver(elem, "players_report_ajax.php?plid=" + plid);
 }

 function mov2(elem, plid, date) {
  ttMouseOver(elem, "players_report_ajax.php?plid=" + plid + "&date=" + date);
 }

 function refreshme() {
  // location.reload();
  document.forms[0].submit();
 }

</script>

<title><?php  xl('Weekly Exposures','e'); ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<form method='post' action='players_report.php<?php if (!empty($_GET['embed'])) echo "?embed=1"; ?>'>

<table border='0' cellpadding='5' cellspacing='0' width='98%'>

 <tr>
  <td height="1" colspan="2">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td align='left'>
   <h2><?php  xl('Weekly Exposures','e'); ?></h2>
  </td>
  <td align='right'>
   <b><?php  echo date('l, F j, Y', $now) ?></b>&nbsp;
   <input type='text' name='form_date' id='form_date' size='10' value='<?php  echo date('Y-m-d', $now) ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a start date','e'); ?>'>
   &nbsp;
   <input type='submit' name='form_refresh' value='<?php  xl('Refresh','e'); ?>'>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="2">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   &nbsp;<?php  xl('Squad','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php  xl('Player','e'); ?>
  </td>

<?php
$time = $now;
for ($day = 0; $day < 8; ++$day) {
  echo "  <td class='dehead' colspan='2' align='center'>";
  echo date('D', $time);
  echo "</td>\n";
  $time += 60 * 60 * 24;
}
?>
 </tr>
<?php 
$lastsquad = '';
foreach ($ordres as $row) {
  $squadvalue = $row['squad'];
  $squadname = $squads[$squadvalue][3];
  if ($squadname) {
    if (! acl_check('squads', $squadvalue)) continue;
  } else {
    $squadname = "None";
  }
  $patient_id = $row['pid'];
?>
 <tr>
  <td class="detail">
   &nbsp;<?php  echo ($squadname == $lastsquad) ? "" : $squadname ?>
  </td>
  <td class="detail">
   &nbsp;<a href='javascript:gopid(<?php  echo $patient_id ?>)' style='color:#000000'><?php  echo $row['lname'] . ", " . $row['fname'] ?></a>
  </td>
<?php
  $time = $now;
  for ($day = 0; $day < 8; ++$day) {
    $date = date('Y-m-d', $time);
    $ymd = date('Ymd', $time);

    if ($PROPLOGIC) {
      // For a given date, fitness info is the last on or before that date,
      // or if there is none then the defaults apply.
      $dfrow = sqlQuery("SELECT df.*, lf.title AS lf_title, lf.mapping AS lf_mapping " .
        "FROM daily_fitness AS df " .
        "LEFT JOIN list_options AS lf ON lf.list_id = 'fitness' AND lf.option_id = df.fitness " .
        "WHERE df.pid = '$patient_id' AND df.date <= '$date' " .
        "ORDER BY df.date DESC LIMIT 1");
    }
    else {
      $dfrow = sqlQuery("SELECT df.*, lf.title AS lf_title, lf.mapping AS lf_mapping " .
        "FROM daily_fitness AS df " .
        "LEFT JOIN list_options AS lf ON lf.list_id = 'fitness' AND lf.option_id = df.fitness " .
        "WHERE df.pid = '$patient_id' AND df.date = '$date'");
    }

    if (empty($dfrow)) {
      $dfrow = array(
        'fitness' => $fdefault['option_id'],
        'lf_title' => $fdefault['title'],
        'lf_mapping' => $fdefault['mapping'],
        // 'am' => '',
        // 'pm' => '',
      );
    }

    $mapping = explode(':', $dfrow['lf_mapping']);
    $bgcolor = $mapping[0];

    // Compute percentage of participation.
    $eventmins = 0;
    $partmins = 0;
    $eres = getSquadEvents($date, $squadvalue, $patient_id);

    while ($erow = sqlFetchArray($eres)) {
      if (!eventMatchesDay($erow, $date)) continue;
      $duration = (int) ($erow['pc_duration'] / 60);
      $eventmins += $duration;
      if (empty($erow['pid']) || $erow['minutes'] > $duration) {
        $partmins += $duration;
      }
      else {
        $partmins += $erow['minutes'];
      }
    }

    echo "  <td class='detail' " .
      "bgcolor='$bgcolor' " .
      "onclick='rosdlgclick($patient_id,$ymd)' " .
      "onmouseover='mov1(this,$patient_id)' " .
      "onmouseout='ttMouseOut()' " .
      ">\n";
    if ($PROPLOGIC && (empty($dfrow['date']) || $dfrow['date'] != $date))
      echo '<i>' . $mapping[1] . '</i>';
    else
      echo $mapping[1];
    echo "  </td>\n";
    echo "  <td class='detail' align='right' " .
      "bgcolor='$bgcolor' " .
      "style='width:40pt;' " .
      "onclick='rosdlgclick($patient_id,$ymd)' " .
      "onmouseover='mov2(this,$patient_id,$ymd)' " .
      "onmouseout='ttMouseOut()' " .
      "nowrap>\n";
    if ($partmins < $eventmins) {
      echo ((int)($partmins * 100 / $eventmins)) . "%\n";
    }
    echo "  </td>\n";

    $time += 60 * 60 * 24;
  }
?>
 </tr>
<?php 
  $lastsquad = $squadname;
}
?>

</table>

</form>
</center>

<div id='tooltipdiv'
 style='position:absolute;width:500px;border:1px solid black;padding:2px;background-color:#ffffaa;visibility:hidden;z-index:1000;font-size:9pt;'
></div>

<script>
<?php 
if ($alertmsg) {
  echo " alert('$alertmsg');\n";
}
?>
</script>
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_date", ifFormat:"%Y-%m-%d", button:"img_date"});
</script>
</body>
</html>

