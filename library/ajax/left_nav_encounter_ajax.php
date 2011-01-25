<?php
// Copyright (C) 2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

$sanitize_all_escapes  = true;
$fake_register_globals = false;

require_once("../../interface/globals.php");
require_once("$srcdir/forms.inc");

// Functions here were cloned from front_payment.php.  They should
// be consolidated into a shared module somewhere.

// Get the patient's encounter ID for today, if it exists.
// In the case of more than one encounter today, pick the last one.
//
function todaysEncounterIf($patient_id) {
  global $today;
  $tmprow = sqlQuery("SELECT encounter FROM form_encounter WHERE " .
    "pid = ? AND date = ? ORDER BY encounter DESC LIMIT 1",
    array($patient_id, "$today 00:00:00"));
  return empty($tmprow['encounter']) ? 0 : $tmprow['encounter'];
}

// Get the patient's encounter ID for today, creating it if there is none.
//
function todaysEncounter($patient_id, $reason='') {
  global $today, $userauthorized;

  if (empty($reason)) $reason = xl('Please indicate visit reason');
  $encounter = todaysEncounterIf($patient_id);
  if ($encounter) return $encounter;

  $tmprow = sqlQuery("SELECT username, facility, facility_id FROM users " .
    "WHERE id = ?", array($_SESSION["authUserID"]));
  $username = $tmprow['username'];
  $facility = $tmprow['facility'];
  $facility_id = $tmprow['facility_id'];
  $conn = $GLOBALS['adodb']['db'];
  $encounter = $conn->GenID("sequences");
  $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
  addForm($encounter, "New Patient Encounter",
    sqlInsert("INSERT INTO form_encounter SET date = ?, onset_date = ?, "  .
      "reason = ?, facility = ?, facility_id = ?, pid = ?, encounter = ?, " .
      "provider_id = ?",
      array($today, $today, $reason, $facility, $facility_id, $patient_id,
        $encounter, $provider_id)
    ),
    "newpatient", $patient_id, $userauthorized, "NOW()", $username
  );
  return $encounter;
}

$issue = $_GET['issue'];
$today = date('Y-m-d');

$irow = sqlQuery("SELECT title FROM lists WHERE id = ?", array($issue));

// Check if an encounter already exists for today.
// If yes, select the latest one.
// If not, create one and give it a title of the issue title.
$thisenc = todaysEncounter($pid, $irow['title']);

// If the encounter is not already linked to the specified issue, link it.
$tmp = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
  "pid = ? AND list_id = ? AND encounter = ?",
  array($pid, $issue, $thisenc));
if (empty($tmp['count'])) {
  sqlStatement("INSERT INTO issue_encounter " .
    "( pid, list_id, encounter ) VALUES ( ?, ?, ? )",
    array($pid, $issue, $thisenc));
}

// Write JavaScript to open the selected encounter as the active encounter.
// Logic cloned from encounters.php.
?>
top.restoreSession();
var enc = <?php echo $thisenc; ?>;
<?php if ($GLOBALS['concurrent_layout']) { ?>
setEncounter('<?php echo $today; ?>', enc, 'RBot');
setRadio('RBot', 'enc');
loadFrame2('enc2', 'RBot', 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
<?php } else { ?>
top.Title.location.href = '../encounter/encounter_title.php?set_encounter='   + enc;
top.Main.location.href  = '../encounter/patient_encounter.php?set_encounter=' + enc;
<?php } ?>

