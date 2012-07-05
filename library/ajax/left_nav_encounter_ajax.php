<?php
// Copyright (C) 2011-2012 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This is an AJAX handler used either to open the New Encounter form or to
// create a new encounter dynamically.  In either case an issue is linked to
// the new encounter.  It is given an issue ID from which also serves to
// identify the patient, and if that patient is not the current patient then
// the current patient is switched to the new one.

$sanitize_all_escapes  = true;
$fake_register_globals = false;

require_once("../../interface/globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/encounter_events.inc.php");

$issue = $_GET['issue'];
$createvisit = !empty($_GET['createvisit']);
$today = date('Y-m-d');

$irow = sqlQuery("SELECT title, pid FROM lists WHERE id = ?", array($issue));

$thispid = $irow['pid'];
if (empty($thispid)) die("Error fetching issue $issue");

if ($createvisit) {
  // Create a new encounter and give it a title of the issue title.
  $thisenc = todaysEncounter($thispid, $irow['title']);

  // If the encounter is not already linked to the specified issue, link it.
  $tmp = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
    "pid = ? AND list_id = ? AND encounter = ?",
    array($thispid, $issue, $thisenc));
  if (empty($tmp['count'])) {
    sqlStatement("INSERT INTO issue_encounter " .
      "( pid, list_id, encounter ) VALUES ( ?, ?, ? )",
      array($thispid, $issue, $thisenc));
  }
?>

// alert('pid = <?php echo $pid; ?> thispid = <?php echo $thispid; ?>'); // debugging

top.restoreSession();
var enc = <?php echo $thisenc; ?>;
<?php
  // If there is a followup function to call, call it.
  $followup = $_REQUEST['followup'];
  if (!empty($followup)) {
    echo "$followup($thisenc)\n";
    exit;
  }
} // end if $createvisit

// If this is a new pid, switch to it. Cloned from demographics.php.
// Currently this will only happen from players_report.php, but we try to be general.
if ($pid != $thispid) {
  include_once("$srcdir/pid.inc");
  setpid($thispid);
  $prow = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
?>
// The JavaScript part of switching to the new pid. Cloned from demographics.php.
top.left_nav.setPatient(<?php echo "'" . htmlspecialchars(($prow['fname']) . " " . ($prow['lname']),ENT_QUOTES) .
  "'," . htmlspecialchars($pid,ENT_QUOTES) . ",'" . htmlspecialchars(($prow['pubpid']),ENT_QUOTES) .
  "','', ' " . htmlspecialchars(xl('DOB') . ": " . oeFormatShortDate($prow['DOB_YMD']) . " " .
  xl('Age') . ": " . getPatientAge($prow['DOB_YMD']), ENT_QUOTES) . "'"; ?>);
// TBD: ForceDual? Maybe load demographics.php into the top frame?
<?php
} // End of pid switch logic.

if ($createvisit) {
  // Write JavaScript to open the selected encounter as the active encounter.
  // Logic cloned from encounters.php.
  if ($GLOBALS['concurrent_layout']) {
?>
top.left_nav.setEncounter('<?php echo $today; ?>', enc, 'RBot');
top.left_nav.setRadio('RBot', 'enc');
top.left_nav.loadFrame2('enc2', 'RBot', 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
<?php
  }
  else {
?>
top.Title.location.href = '../encounter/encounter_title.php?set_encounter='   + enc;
top.Main.location.href  = '../encounter/patient_encounter.php?set_encounter=' + enc;
<?php
  }
}
else { // if not $createvisit
  if ($GLOBALS['concurrent_layout']) {
?>
top.left_nav.loadFrame2('nen1','RBot','forms/newpatient/new.php?autoloaded=1&calenc=&issue=<?php echo $issue; ?>');
<?php
  }
  else {
?>
  // This case should not happen.
<?php
  }
} // end not $createvisit
?>
