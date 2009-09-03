<?php
// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Disable PHP timeout.  This will not work in safe mode.
ini_set('max_execution_time', '0');

$ignoreAuth = true; // no login required

require_once('interface/globals.php');
require_once('library/sql.inc');
require_once('library/forms.inc');

$verbose = 0;
$debug = 0;
$insert_count = 0;

// Create a visit form from an abortion issue.  This may be called
// multiple times for a given issue.
//
function do_visit_form($irow, $encounter, $first) {
  global $insert_count, $debug, $verbose;

  $pid = $irow['pid'];

  // If a gcac form already exists for this visit, get out.
  $row = sqlQuery("SELECT COUNT(*) AS count FROM forms WHERE " .
    "pid = '$pid' AND encounter = '$encounter' AND " .
    "formdir = 'LBFgcac' AND deleted = 0");
  if ($row['count']) {
    echo "<br />*** Visit $pid.$encounter skipped, already has a GCAC visit form ***\n";
    return;
  }

  $a = array(
    'client_status' => $irow['client_status'],
    'in_ab_proc'    => $irow['in_ab_proc'],
    'ab_location'   => $irow['ab_location'],
    'complications' => $irow['fol_compl'],
    'contrameth'    => $irow['contrameth'],
  );

  // logic that applies only to the first related visit
  if ($first) {
    if ($a['ab_location'] == 'ma') $a['ab_location'] = 'proc';
    $a['complications'] = $irow['rec_compl'];
    $a['contrameth'] = '';
  }

  $newid = 0;
  $didone = false;
  foreach ($a as $field_id => $value) {
    if ($value !== '') {
      if ($newid) {
        $query = "INSERT INTO lbf_data " .
          "( form_id, field_id, field_value ) " .
          " VALUES ( '$newid', '$field_id', '$value' )";
        if ($verbose) echo "<br />$query\n";
        if (!$debug) sqlStatement($query);
      }
      else {
        $query = "INSERT INTO lbf_data " .
          "( field_id, field_value ) " .
          " VALUES ( '$field_id', '$value' )";
        if ($verbose) echo "<br />$query\n";
        if (!$debug) $newid = sqlInsert($query);
      }
      $didone = true;
    }
  }

  if ($newid && !$debug) {
    addForm($encounter, $irow['title'], $newid, 'LBFgcac', $pid, 1);
    ++$insert_count;
  }

  if (!$didone) echo "<br />*** Empty issue skipped for visit $pid.$encounter ***\n";
}
?>
<html>
<head>
<title>OpenEMR IPPF Upgrade</title>
<link rel='STYLESHEET' href='interface/themes/style_blue.css'>
</head>
<body>
<center>
<span class='title'>OpenEMR IPPF Upgrade</span>
<br>
</center>
<?php
if (!empty($_POST['form_submit'])) {

  // If database is not utf8, convert it.
  $trow = sqlQuery("SHOW CREATE DATABASE $dbase");
  array_shift($trow);
  $value = array_shift($trow);
  if (!preg_match('/SET utf8/', $value)) {
    echo "<br />Converting database to UTF-8 encoding...";
    $tres = sqlStatement("SHOW TABLES");
    while ($trow = sqlFetchArray($tres)) {
      $value = array_shift($trow);
      $query = "ALTER TABLE $value CONVERT TO CHARACTER SET utf8";
      if ($verbose) echo "<br />$query\n";
      sqlStatement($query);
    }
    $query = "ALTER DATABASE $dbase CHARACTER SET utf8";
    if ($verbose) echo "<br />$query\n";
    sqlStatement($query);
    echo "<br />&nbsp;\n";
  }

  $ires = sqlStatement("SELECT " .
    "l.pid, l.id, l.type, l.begdate, l.title, " .
    "g.client_status, g.in_ab_proc, g.ab_location, " .
    "g.rec_compl, g.contrameth, g.fol_compl " .
    "FROM lists AS l " .
    "JOIN lists_ippf_gcac AS g ON l.type = 'ippf_gcac' AND g.id = l.id " .
    "ORDER BY l.pid, l.begdate");

  while ($irow = sqlFetchArray($ires)) {
    $patient_id = $irow['pid'];
    $list_id = $irow['id'];
    $first = true;

    $ieres = sqlStatement("SELECT encounter " .
      "FROM issue_encounter " .
      "WHERE pid = '$patient_id' AND list_id = '$list_id' " .
      "ORDER BY encounter");

    if (sqlNumRows($ieres)) {
      while ($ierow = sqlFetchArray($ieres)) {
        do_visit_form($irow, $ierow['encounter'], $first);
        $first = false;
      }
    }
    else {
      echo "<br />*** Issue $list_id for pid $patient_id has no linked visits, skipped ***\n";
    }
  }

  echo "<p><font color='green'>Done. Inserted $insert_count visit forms.</font></p>\n";
  echo "</body></html>\n";
  exit();
}

?>
<p>This converts your OpenEMR database to UTF-8 encoding if it is not already,
and also converts GCAC issues to the corresponding visit forms.  Both of these
steps are needed for IPPF sites upgrading from releases prior to 2009-08-27.</p>
<center>
<form method='post' action='ippf_upgrade.php'>
<p><input type='submit' name='form_submit' value='Convert Database' /></p>
</form>
</center>
</body>
</html>
