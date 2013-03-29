<?php
/**
* This script merges two patient charts into a single patient chart.
* It is to correct the error of creating a duplicate patient.
*
* Copyright (C) 2013 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

set_time_limit(0);

$sanitize_all_escapes  = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/log.inc");

// Set this to true for production use. If false you will get a "dry run" with no updates.
$PRODUCTION = true;

if (!acl_check('admin', 'super')) die(xlt('Not authorized'));
?>
<html>

<head>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<title><?php echo xlt('Merge Patients'); ?></title>

<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';

var el_pt_name;
var el_pt_id;

// This is for callback by the find-patient popup.
function setpatient(pid, lname, fname, dob) {
 el_pt_name.value = lname + ', ' + fname + ' (' + pid + ')';
 el_pt_id.value = pid;
}

// This invokes the find-patient popup.
function sel_patient(ename, epid) {
 el_pt_name = ename;
 el_pt_id = epid;
 dlgopen('../main/calendar/find_patient_popup.php', '_blank', 500, 400);
}

</script>

</head>

<body class="body_top">

<center><h2><?php echo xlt('Merge Patients') ?></h2></center>

<?php

function deleteRows($tblname, $colname, $source_pid) {
  global $PRODUCTION;
  $crow = sqlQuery("SELECT COUNT(*) AS count FROM `$tblname` WHERE `$colname` = $source_pid");
  $count = $crow['count'];
  if ($count) {
    $sql = "DELETE FROM `$tblname` WHERE `$colname` = $source_pid";
    echo "<br />$sql ($count)";
    if ($PRODUCTION) sqlStatement($sql);
  }
}

function updateRows($tblname, $colname, $source_pid, $target_pid) {
  global $PRODUCTION;
  $crow = sqlQuery("SELECT COUNT(*) AS count FROM `$tblname` WHERE `$colname` = $source_pid");
  $count = $crow['count'];
  if ($count) {
    $sql = "UPDATE `$tblname` SET `$colname` = '$target_pid' WHERE `$colname` = $source_pid";
    echo "<br />$sql ($count)";
    if ($PRODUCTION) sqlStatement($sql);
  }
}

if (!empty($_POST['form_submit'])) {
  $target_pid = intval($_POST['form_target_pid']);
  $source_pid = intval($_POST['form_source_pid']);

  $fatal = 0;

  if ($target_pid == $source_pid) die(xlt('Target and source pid may not be the same!'));

  $tprow = sqlQuery("SELECT * FROM patient_data WHERE pid = ?", array($target_pid));
  $sprow = sqlQuery("SELECT * FROM patient_data WHERE pid = ?", array($source_pid));

  // Do some checking to make sure source and target are the same person.
  if (empty($tprow['ss'])) die(xlt('Target patient not found or has no SSN'));
  if (empty($sprow['ss'])) die(xlt('Source patient not found or has no SSN'));
  if ($tprow['ss'] != $sprow['ss']) die(xlt('Target and source SSN do not match'));
  if (empty($tprow['DOB']) || $tprow['DOB'] == '0000-00-00') die(xlt('Target patient has no DOB'));
  if (empty($sprow['DOB']) || $sprow['DOB'] == '0000-00-00') die(xlt('Source patient has no DOB'));
  if ($tprow['DOB'] != $sprow['DOB']) die(xlt('Target and source DOB do not match'));

  $tdocdir = "$OE_SITE_DIR/documents/$target_pid";
  $sdocdir = "$OE_SITE_DIR/documents/$source_pid";
  $sencdir = "$sdocdir/encounters";
  $tencdir = "$tdocdir/encounters";

  // Check for any duplicate document names.
  if (is_dir($sdocdir)) {
    $dh = opendir($sdocdir);
    if (!$dh) die(xlt('Cannot read directory') . " '$sdocdir'");
    while (false !== ($sfname = readdir($dh))) {
      if ($sfname == '.' || $sfname == '..') continue;
      if ($sfname == 'index.html') continue;
      if ($sfname == 'encounters') continue;
      if (file_exists("$tdocdir/$sfname")) {
        ++$fatal;
        echo "<br />" . xlt('Duplicate document name') . " '$sfname' " . xlt('in source and target');
      }
    }
    closedir($dh);
  }

  if ($fatal) die("<br />" . xlt('Aborted due to document duplication'));

  // Move scanned encounter documents and delete their container.
  if (is_dir($sencdir)) {
    if ($PRODUCTION && !file_exists($tdocdir)) mkdir($tdocdir);
    if ($PRODUCTION && !file_exists($tencdir)) mkdir($tencdir);
    $dh = opendir($sencdir);
    if (!$dh) die(xlt('Cannot read directory') . " '$sencdir'");
    while (false !== ($sfname = readdir($dh))) {
      if ($sfname == '.' || $sfname == '..') continue;
      if ($sfname == 'index.html') {
        echo "<br />" . xlt('Deleting') . " $sencdir/$sfname";
        if ($PRODUCTION) {
          if (!unlink("$sencdir/$sfname"))
            die("<br />" . xlt('Delete failed!'));
        }
        continue;
      }
      echo "<br />" . xlt('Moving') . " $sencdir/$sfname " . xlt('to') . " $tencdir/$sfname";
      if ($PRODUCTION) {
        if (!rename("$sencdir/$sfname", "$tencdir/$sfname"))
          die("<br />" . xlt('Move failed!'));
      }
    }
    closedir($dh);
    echo "<br />" . xlt('Deleting') . " $sencdir";
    if ($PRODUCTION) {
      if (!rmdir($sencdir))
        die("<br />" . xlt('Delete failed!'));
    }
  }

  // Move normal documents and delete their container.
  if (is_dir($sdocdir)) {
    if ($PRODUCTION && !file_exists($tdocdir)) mkdir($tdocdir);
    $dh = opendir($sdocdir);
    if (!$dh) die(xlt('Cannot read directory') . " '$sdocdir'");
    while (false !== ($sfname = readdir($dh))) {
      if ($sfname == '.' || $sfname == '..') continue;
      if ($sfname == 'encounters') continue;
      if ($sfname == 'index.html') {
        echo "<br />" . xlt('Deleting') . " $sdocdir/$sfname";
        if ($PRODUCTION) {
          if (!unlink("$sdocdir/$sfname"))
            die("<br />" . xlt('Delete failed!'));
        }
        continue;
      }
      echo "<br />" . xlt('Moving') . " $sdocdir/$sfname " . xlt('to') . " $tdocdir/$sfname";
      if ($PRODUCTION) {
        if (!rename("$sdocdir/$sfname", "$tdocdir/$sfname"))
          die("<br />" . xlt('Move failed!'));
      }
    }
    closedir($dh);
    echo "<br />" . xlt('Deleting') . " $sdocdir";
    if ($PRODUCTION) {
      if (!rmdir($sdocdir))
        die("<br />" . xlt('Delete failed!'));
    }
  }

  $tres = sqlStatement("SHOW TABLES");
  while ($trow = sqlFetchArray($tres)) {
    $tblname = array_shift($trow);
    if ($tblname == 'patient_data' || $tblname == 'history_data' || $tblname == 'insurance_data') {
      deleteRows($tblname, 'pid', $source_pid);
    }
    else if ($tblname == 'chart_tracker') {
      updateRows($tblname, 'ct_pid', $source_pid, $target_pid);
    }
    else if ($tblname == 'documents') {
      $crow = sqlQuery("SELECT COUNT(*) AS count FROM `$tblname` WHERE `foreign_id` = '$source_pid'");
      $count = $crow['count'];
      if ($count) {
        $sql = "UPDATE `$tblname` SET " .
          "`url` = replace(`url`, '/documents/$source_pid/', '/documents/$target_pid/') " .
          "WHERE `foreign_id` = '$source_pid'";
        echo "<br />$sql ($count)";
        if ($PRODUCTION) sqlStatement($sql);
      }
      updateRows($tblname, 'foreign_id', $source_pid, $target_pid);
    }
    else if ($tblname == 'openemr_postcalendar_events') {
      updateRows($tblname, 'pc_pid', $source_pid, $target_pid);
    }
    else if ($tblname == 'log') {
      // Don't mess with log data.
    }
    else {
      $crow = sqlQuery("SHOW COLUMNS FROM `$tblname` WHERE " .
        "`Field` LIKE 'pid' OR `Field` LIKE 'patient_id'");
      if (!empty($crow['Field'])) {
        $colname = $crow['Field'];
        updateRows($tblname, $colname, $source_pid, $target_pid);
      }
    }
  }

  echo "<br />" . xlt('Merge complete.');

  exit(0);
}
?>

<p>

</p>

<form method='post' action='merge_patients.php'>
<center>
<table style='width:90%'>
 <tr>
  <td>
   <?php echo xlt('Target Patient') ?>
  </td>
  <td>
   <input type='text' size='30' name='form_target_patient'
    value=' (<?php echo xla('Click to select'); ?>)'
    onclick='sel_patient(this, this.form.form_target_pid)'
    title='Click to select patient' readonly />
   <input type='hidden' name='form_target_pid' value='0' />
  </td>
  <td>
   <?php echo xlt('This is the main chart that is to receive the merged data.'); ?>
  </td>
 </tr>
 <tr>
  <td>
   <?php echo xlt('Source Patient') ?>
  </td>
  <td>
   <input type='text' size='30' name='form_source_patient'
    value=' (<?php echo xla('Click to select'); ?>)'
    onclick='sel_patient(this, this.form.form_source_pid)'
    title='Click to select patient' readonly />
   <input type='hidden' name='form_source_pid' value='0' />
  </td>
  <td>
   <?php echo xlt('This is the chart that is to be merged into the main chart and then deleted.'); ?>
  </td>
 </tr>
</table>
<p><input type='submit' name='form_submit' value='<?php echo xla('Merge'); ?>' /></p>
</center>
</form>

<!-- I don't think it's good to run big globs of text through the current translation
system.  Let's find another way. -->

<p>This utility is experimental.  Back up your database and documents before using it!</p>

<?php if (!$PRODUCTION) { ?>
<p>This will be a "dry run" with no physical data updates.</p>
<?php } ?>

<p>This will merge two patient charts into one.  It is useful when a patient has been
duplicated by mistake.  If that happens often, fix your office procedures - do not run this
routinely!</p>

<p>The first ("target") chart is the one that is considered the most complete and accurate.
Demographics, history and insurance sections for this one will be retained.</p>

<p>The second ("source") chart will have its demographics, history and insurance sections
discarded.  Its other data will be merged into the target chart.</p>

<p>The merge will not run unless SSN and DOB for the two charts are present and identical.
Also there must not be any documents with identical names.  If any of these problems are
found then you should fix them and retry the merge.</p>

</body>
</html>
