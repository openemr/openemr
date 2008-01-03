<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
require_once("$srcdir/transactions.inc");
require_once("$srcdir/options.inc.php");
include_once("$srcdir/patient.inc");

$template_file = "$webserver_root/custom/referral_template.html";

if (!is_file($template_file)) die("$template_file does not exist!");

$transid = empty($_REQUEST['transid']) ? 0 : $_REQUEST['transid'] + 0;
if (!$transid) die("Transaction ID is missing!");

$trow = getTransById($transid);
$patient_id = $trow['pid'];
$refer_date = $trow['refer_date'] ? $trow['refer_date'] : date('Y-m-d');

$patdata = getPatientData($patient_id);

$patient_age = getPatientAge(str_replace('-', '', $patdata['DOB']));

$frrow = sqlQuery("SELECT * FROM users WHERE id = '" . $trow['refer_from'] . "'");
if (empty($frrow)) $frrow = array();

$torow = sqlQuery("SELECT * FROM users WHERE id = '" . $trow['refer_to'] . "'");
if (empty($torow)) $torow = array(
  'organization' => '',
  'street' => '',
  'city' => '',
  'state' => '',
  'zip' => '',
  'phone' => '',
);

$vrow = sqlQuery("SELECT * FROM form_vitals WHERE " .
  "pid = '$patient_id' AND date <= '$refer_date 23:59:59' " .
  "ORDER BY date DESC LIMIT 1");
if (empty($vrow)) $vrow = array(
  'bps' => '',
  'bpd' => '',
  'weight' => '',
  'height' => '',
);

$facrow = sqlQuery("SELECT name FROM facility ORDER BY " .
  "service_location DESC, billing_location DESC, id ASC LIMIT 1");

$s = '';
$fh = fopen($template_file, 'r');
while (!feof($fh)) $s .= fread($fh, 8192);
fclose($fh);

$s = str_replace("{fac_name}", $facrow['name'], $s);
$s = str_replace("{ref_id}"  , $trow['id']    , $s);
$s = str_replace("{ref_pid}" , $patient_id    , $s);
$s = str_replace("{pt_age}"  , $patient_age   , $s);

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'REF' ORDER BY group_name, seq");
while ($frow = sqlFetchArray($fres)) {
  $data_type = $frow['data_type'];
  $field_id  = $frow['field_id'];
  $currvalue = '';
  if (isset($trow[$field_id])) $currvalue = $trow[$field_id];
  $s = str_replace("{ref_$field_id}",
    generate_display_field($frow, $currvalue), $s);
}

foreach ($patdata as $key => $value) {
  $s = str_replace("{pt_$key}", $value, $s);
}

foreach ($frrow as $key => $value) {
  $s = str_replace("{from_$key}", $value, $s);
}

foreach ($torow as $key => $value) {
  $s = str_replace("{to_$key}", $value, $s);
}

foreach ($vrow as $key => $value) {
  $s = str_replace("{v_$key}", $value, $s);
}

echo $s;
?>
