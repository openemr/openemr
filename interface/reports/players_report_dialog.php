<?php
// Copyright (C) 2009-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//////////////////////////////////////////////////////////////////////
// This dialog works with a specified player on a specified date,
// and is used to:
//
// o change fitness level
// o change per-event minutes of participation and absence reason
// o add/modify the associated pt note
// o link an issue to the player's fitness for that day
//////////////////////////////////////////////////////////////////////

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/calendar_events.inc.php");

// Temporary variable while new logic is being tested.
// True means that missing days in the daily_fitness table default to
// the previous entry's values, if there is one.
// Otherwise the default fitness level (Fully Fit) is used.
$PROPLOGIC = true;

$plid = $_REQUEST['plid'] + 0; // pid
$ymd = $_REQUEST['date'];
if (empty($ymd)) die("Internal error: date parameter is missing");
$date = substr($ymd,0,4) . '-' . substr($ymd,4,2) . '-' . substr($ymd,6,2);

$form_fitness = formData('form_fitness');
$form_issue   = formData('form_issue') + 0;
$form_to      = formData('form_to');

$form_note = empty($_POST['form_note']) ? '' : $_POST['form_note'];
if (get_magic_quotes_gpc()) $form_note = stripslashes($form_note);

$form_am = empty($_POST['form_am']) ? '' : $_POST['form_am'];
if (get_magic_quotes_gpc()) $form_am = stripslashes($form_am);

$form_pm = empty($_POST['form_pm']) ? '' : $_POST['form_pm'];
if (get_magic_quotes_gpc()) $form_pm = stripslashes($form_pm);

function gen_list_options($list_id, $default='') {
  $res = sqlStatement("SELECT * FROM list_options WHERE " .
    "list_id = '$list_id' ORDER BY seq");
  while ($row = sqlFetchArray($res)) {
    $key = $row['option_id'];
    echo "    <option value='$key'";
    if ($key == $default) echo " selected";
    echo ">" . $row['title'] . "</option>\n";
  }
}

$alertmsg = ''; // anything here pops up in an alert box

// Get player info.
$patrow = sqlQuery("SELECT " .
  "fname, mname, lname, pubpid, squad " .
  "FROM patient_data " .
  "WHERE pid = '$plid' LIMIT 1");
$squad = $patrow['squad'];

if ($PROPLOGIC) {
  // For a given date, fitness info is the last on or before that date,
  // or if there is none then the defaults apply.
  $dfrow = sqlQuery("SELECT " .
    "df.*, lf.option_id AS lf_id, lf.title AS lf_title " .
    "FROM daily_fitness AS df " .
    "LEFT JOIN list_options AS lf ON lf.list_id = 'fitness' AND lf.option_id = df.fitness " .
    "WHERE df.pid = '$plid' AND df.date <= '$date' " .
    "ORDER BY df.date DESC LIMIT 1");
}
else {
  $dfrow = sqlQuery("SELECT " .
    "df.*, lf.option_id AS lf_id, lf.title AS lf_title " .
    "FROM daily_fitness AS df " .
    "LEFT JOIN list_options AS lf ON lf.list_id = 'fitness' AND lf.option_id = df.fitness " .
    "WHERE df.pid = '$plid' AND df.date = '$date'");
}

if (empty($dfrow)) {
  $dfrow = array(
    'pid'      => '0',
    'date'     => '',
    'fitness'  => '1',
    'lf_title' => 'FF',
    'issue_id' => '0',
  );
}

// This gets the events for the player's squad for this date,
// and the player-specific data (if any) for each such event.
$eres = getSquadEvents($date, $squad, $plid);

// Get the roster note, if any, for this player and date.
$nrow = sqlQuery("SELECT id, body, assigned_to FROM pnotes WHERE " .
  "pid = '$plid' AND LEFT(date,10) = '$date' AND title LIKE 'Roster' AND " .
  "deleted = 0 ORDER BY date LIMIT 1");
$noteid = empty($nrow) ? '0' : $nrow['id'];

// If the Save button was clicked...
if ($_POST['form_save']) {

  // Update daily_fitness.
  if ($dfrow['date'] == $date) {
    sqlStatement("UPDATE daily_fitness SET " .
      "fitness = '$form_fitness', " .
      "am = '$form_am', " .
      "pm = '$form_pm', " .
      "issue_id = '$form_issue'" .
      "WHERE pid = '$plid' AND date = '$date'");
  }
  else {
    sqlStatement("INSERT INTO daily_fitness SET " .
      "pid = '$plid', " .
      "date = '$date', " .
      "fitness = '$form_fitness', " .
      "am = '$form_am', " .
      "pm = '$form_pm', " .
      "issue_id = '$form_issue'");
  }

  // Update player_events.
  while ($erow = sqlFetchArray($eres)) {
    if (!eventMatchesDay($erow, $date)) continue;
    $eid = 0 + $erow['pc_eid'];
    $duration = (int) ($erow['pc_duration'] / 60);
    $form_mins = formData("form_mins_$eid") + 0;
    $form_fitrel = empty($_POST["form_fitrel_$eid"]) ? 0 : 1;
    sqlStatement("DELETE FROM player_event WHERE pid = '$plid' AND " .
      "date = '$date' AND pc_eid = '$eid'");
    if ($form_mins < $duration) {
      sqlStatement("INSERT INTO player_event SET " .
        "pid = '$plid', " .
        "date = '$date', " .
        "pc_eid = '$eid', " .
        "minutes = '$form_mins', " .
        "fitness_related = '$form_fitrel'");
    }
  }

  // Add or append to the roster note.
  if ($form_note !== '') {
    if ($noteid) {
      updatePnote($noteid, $form_note, 'Roster', $form_to);
    }
    else {
      addPnote($plid, $form_note, $userauthorized, '1', 'Roster', $form_to,
        "$date 00:00:00");
    }
  }

  // Close this window and refresh the roster display.
  echo "<html>\n<body>\n<script language='JavaScript'>\n";
  if ($alertmsg) echo " alert('$alertmsg');\n";
  echo " if (!opener.closed && opener.refreshme) opener.refreshme();\n";
  echo " window.close();\n";
  echo "</script>\n</body>\n</html>\n";
  exit();
}
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<title><?php echo htmlspecialchars(xl('Record of Fitness')); ?></title>

<style type="text/css">
 body    { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 // Process click on issue title.
 function newissue() {
  dlgopen('../patient_file/summary/add_edit_issue.php?thispid=<?php echo $plid; ?>', '_blank', 800, 600);
  return false;
 }

 // callback from add_edit_issue.php:
 function refreshIssue(issue, title) {
  var s = document.forms[0].form_issue;
  s.options[s.options.length] = new Option(title, issue, true, true);
 }

</script>

</head>

<body class="body_top" onunload='imclosing()'>

<form method='post' action='players_report_dialog.php?<?php echo "plid=$plid" . "&date=$ymd"; ?>'
 onsubmit='return top.restoreSession()'>
<input type='hidden' name='form_pid' value='<?php echo $pid ?>' />

<center>

<table border='0' cellspacing='8'>
 <tr>
  <td>
   <b><?php xl('Fitness Level','e'); ?></b>
  </td>
  <td>
   <select name='form_fitness'
    title='<?php xl('Fitness level for this player on this day','e'); ?>'>
<?php gen_list_options('fitness', $dfrow['fitness']); ?>
   </select>
  </td>
 </tr>

 <tr>
  <td>
   <b><?php xl('Related Issue','e'); ?></b>
  </td>
  <td>
   <select name='form_issue'
    title='<?php xl('Select the issue primarily responsible for any missed events on this day','e'); ?>'>
    <option value='0'>Unassigned</option>
<?php
$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
  "pid = $plid AND enddate IS NULL " .
  "ORDER BY type, begdate");
while ($irow = sqlFetchArray($ires)) {
  $list_id = $irow['id'];
  $tcode = $irow['type'];
  if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];
  echo "    <option value='$list_id'";
  if ($list_id == $dfrow['issue_id']) echo " selected";
  echo ">$tcode: " . $irow['begdate'] . " " .
    htmlspecialchars(substr($irow['title'], 0, 40)) . "</option>\n";
}
?>
   </select>
   &nbsp;
   <input type='button' value='<?php echo htmlspecialchars(xl('Add Issue')); ?>'
    onclick='newissue()' /> &nbsp;
  </td>
 </tr>

<?php
while ($erow = sqlFetchArray($eres)) {

  echo "<!--\n";
  print_r($erow); // debugging
  echo "-->\n";

  if (!eventMatchesDay($erow, $date)) continue;

  echo "<!-- The above matches -->\n"; // debugging

  $eid = 0 + $erow['pc_eid'];
  if (empty($erow['pid'])) {
    // No player_event data so set defaults.
    $minutes = (int) ($erow['pc_duration'] / 60);
    $fitness_related = ($dfrow['fitness'] == 1) ? 0 : 1;
  }
  else {
    $minutes = 0 + $erow['minutes'];
    $fitness_related = empty($erow['fitness_related']) ? 0 : 1;
  }
  echo " <tr>\n";
  echo "  <td><b>" . substr($erow['pc_startTime'], 0, 5) . " " .
    $erow['pc_hometext'] . "</b></td>\n";
  echo "  <td>" . xl('Minutes') . ": " .
    "<input type='text' name='form_mins_$eid' size='3' value='$minutes' />" .
    "&nbsp;<input type='checkbox' name='form_fitrel_$eid'";
  if ($fitness_related) echo " checked";
  echo " />" . xl('Injury/illness-related') . "</td>\n";
  echo " </tr>\n";
}
?>

 <tr>
  <td>
   <b><?php xl('Note','e'); ?></b>
  </td>
  <td>
<?php
// Get the set of local users.
$ures = sqlStatement("SELECT username, fname, lname FROM users " .
 "WHERE username != '' AND active = 1 AND " .
 "( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
 "ORDER BY lname, fname");
// Show existing note, if any, and then a textarea for adding more.
if ($noteid) {
  echo "   <div class='text' " .
    "style='background-color:white;color:gray;border:1px solid #999;padding: 5px;'>" .
    nl2br(htmlentities($nrow['body'])) . "</div>\n";
}
?>
   <textarea name='form_note' id='note' rows='4' cols='80'></textarea>
   <br />
   <b><?php xl('To','e'); ?>:</b>
   <select name='form_to'>
    <option value=''>** <?php xl('Close','e'); ?> **</option>
<?php
// The "To" list of users.
while ($urow = sqlFetchArray($ures)) {
  echo "    <option value='" . $urow['username'] . "'";
  if ($urow['username'] == $nrow['assigned_to']) echo " selected";
  echo ">" . $urow['lname'];
  if ($urow['fname']) echo ", " . $urow['fname'];
  echo "</option>\n";
}
?>
   </select>
  </td>
 </tr>

 <tr>
  <td>
   <b><?php xl('AM Program','e'); ?></b>
  </td>
  <td>
   <textarea name='form_am' rows='3' cols='80'><?php echo $dfrow['am']; ?></textarea>
  </td>
 </tr>

 <tr>
  <td>
   <b><?php xl('PM Program','e'); ?></b>
  </td>
  <td>
   <textarea name='form_pm' rows='3' cols='80'><?php echo $dfrow['pm']; ?></textarea>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' /> &nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />

</center>
</form>
</body>
</html>
