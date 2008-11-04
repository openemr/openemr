<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This may be run after an upgraded OpenEMR has been installed.
// Its purpose is to extract A/R information from SQL-Ledger and
// convert it to the OpenEMR tables that maintain A/R internally,
// thus eliminating SQL-Ledger.

$ignoreAuth=true; // no login required

require_once('interface/globals.php');
require_once('library/sql-ledger.inc');
require_once('library/invoice_summary.inc.php');
require_once('library/sl_eob.inc.php');

$tmp = sqlQuery("SELECT count(*) AS count FROM ar_activity");
if ($tmp['count']) die("ar_activity and ar_session must be empty to run this script!");
?>
<html>
<head>
<title>OpenEMR Conversion from SQL-Ledger</title>
<link rel='STYLESHEET' href='interface/themes/style_blue.css'>
</head>
<body>
<span class='title'>OpenEMR Conversion from SQL-Ledger</span>
<br><br>
<span class='text'>
<?php
SLConnect();

$invoice_count = 0;
$activity_count = 0;

$res = SLQuery("SELECT id, invnumber, transdate, shipvia, intnotes " .
  "FROM ar ORDER BY id");

for ($irow = 0; $irow < SLRowCount($res); ++$irow) {
  $row = SLGetRow($res, $irow);
  list($pid, $encounter) = explode(".", $row['invnumber']);
  $copays = array();
  $provider_id = 0;
  $last_biller = 0;

  // Scan billing table items to get the provider ID and copays.
  $bres = sqlStatement("SELECT * FROM billing WHERE " .
    "pid = '$pid' AND encounter = '$encounter' AND activity = 1 " .
    "AND billed = 1 AND fee != 0 ORDER BY fee DESC");
  while ($brow = sqlFetchArray($bres)) {
    if (!$provider_id) $provider_id = $brow['provider_id'];
    if (!$last_biller && !empty($brow['payer_id'])) $last_biller = $brow['payer_id'];
    if ($brow['code_type'] == 'COPAY') $copays[] = 0 - $brow['fee'];
  }

  $invlines = get_invoice_summary($row['id'], true);

  // print_r($invlines); // debugging

  ksort($invlines);
  foreach ($invlines as $codekey => $codeinfo) {
    ksort($codeinfo['dtl']);
    $code = strtoupper($codekey);
    if ($code == 'CO-PAY' || $code == 'UNKNOWN') $code = '';

    foreach ($codeinfo['dtl'] as $dtlkey => $dtlinfo) {
      $dtldate = trim(substr($dtlkey, 0, 10));
      if (empty($dtldate)) continue; // skip the charges
      $payer_id = empty($dtlinfo['ins']) ? 0 : $dtlinfo['ins'];
      $session_id = 0;

      // Compute a reasonable "source" value.  For payments this will
      // commonly be a check number, for adjustments we have none.
      $source = empty($dtlinfo['src']) ? '' : $dtlinfo['src'];
      $source = preg_replace('!^Ins[123]/!i', '', $source);
      $source = preg_replace('!^Pt/!i', '', $source);
      if ($source == '' && empty($dtlinfo['pmt'])) {
        $source = 'From SQL-Ledger';
      }

      // For insurance payers look up or create the session table entry.
      if ($payer_id) {
        $session_id = arGetSession($payer_id, $source, $dtldate);
      }
      // For non-insurance payers deal with copay duplication.
      else if ($code == '') {
        if (!empty($dtlinfo['pmt'])) {
          // Skip payments that are already present in the billing table as copays.
          foreach ($copays as $key => $value) {
            if ($value == $dtlinfo['pmt']) {
              unset($copays[$key]);
              continue 2; // skip this detail item
            }
          } // end foreach
        } // end if payment
      } // end not insurance

      $payer_type = 0;

      if (!empty($dtlinfo['pmt'])) { // it's a payment
        for ($i = 1; $i <= 3; ++$i) {
          if (stripos($dtlinfo['src'], "Ins$i") !== false) $payer_type = $i;
        }
        arPostPayment($pid, $encounter, $session_id, $dtlinfo['pmt'], $code,
          $payer_type, $source, 0, "$dtldate 00:00:00");
        if ($session_id) {
          sqlStatement("UPDATE ar_session SET pay_total = pay_total + '" .
            $dtlinfo['pmt'] . "' WHERE session_id = '$session_id'");
        }
      }
      else { // it's an adjustment
        for ($i = 1; $i <= 3; ++$i) {
          if (stripos($dtlinfo['rsn'], "Ins$i") !== false) $payer_type = $i;
        }
        arPostAdjustment($pid, $encounter, $session_id, 0 - $dtlinfo['chg'],
          $code, $payer_type, $dtlinfo['rsn'], 0, "$dtldate 00:00:00");
      }

      ++$activity_count;
    } // end detail item
  } // end code


  // Compute last insurance level billed.
  $last_level_billed = 0;
  if ($last_biller) {
    $invdate = $row['transdate'];
    $tmp = sqlQuery("SELECT type FROM insurance_data WHERE " .
      "pid = '$patient_id' AND provider = '$last_biller' AND " .
      "date <= '$invdate' ORDER BY date DESC, id ASC LIMIT 1");
    $last_level_billed = ($tmp['type'] == 'tertiary') ?
      3 : ($tmp['type'] == 'secondary') ? 2 : 1;
  }

  // Compute last insurance level closed.
  $last_level_closed = 0;
  for ($i = 1; $i <= 3; ++$i) {
    if (stripos($row['shipvia'], "Ins$i") !== false) $last_level_closed = $i;
  }

  // Compute last statement date and number of statements sent.
  $last_stmt_date = "NULL";
  $stmt_count = 0;
  $i = 0;
  while ($i = stripos($row['intnotes'], 'Statement sent ', $i) !== false) {
    $i += 15;
    $last_stmt_date = "'" . substr($row['intnotes'], $i, 10) . "'";
    ++$stmt_count;
  }

  sqlStatement("UPDATE form_encounter SET " .
    "last_level_billed = '$last_level_billed', " .
    "last_level_closed = '$last_level_closed', " .
    "last_stmt_date = $last_stmt_date, " .
    "stmt_count = '$stmt_count'");

  // Show a warning for any unmatched copays.
  foreach ($copays as $copay) {
    echo "Co-pay of \$$copay in the encounter was not found in " .
      "SQL-Ledger invoice $pid.$encounter.<br />\n";
  }

  ++$invoice_count;
} // end invoice
SLClose();
echo "<br />\n";
echo "$invoice_count SQL-Ledger invoices were processed.<br />\n";
echo "$activity_count payments and adjustments were posted.<br />\n";
?>
</span>

</body>
</html>
