<?php
// Copyright (C) 2005-2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This returns an associative array keyed on procedure code, representing
// all charge items for one invoice.  This array's values are themselves
// associative arrays having the following keys:
//
//  chg - the sum of line items, including adjustments, for the code
//  bal - the unpaid balance
//  adj - the (positive) sum of inverted adjustments
//  ins - the id of the insurance company that was billed (obsolete)
//  dtl - associative array of details, if requested
//
// Where details are requested, each dtl array is keyed on a string
// beginning with a date in yyyy-mm-dd format, or blanks in the case
// of the original charge items.  The value array is:
//
//  pmt - payment amount as a positive number, only for payments
//  src - check number or other source, only for payments
//  chg - invoice line item amount amount, only for charges or
//        adjustments (adjustments may be zero)
//  rsn - adjustment reason, only for adjustments
//  plv - provided for "integrated A/R" only: 0=pt, 1=Ins1, etc.

function get_invoice_summary($trans_id, $with_detail = false) {
  global $sl_err, $sl_cash_acc;

  $codes = array();

  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);
  if (! $chart_id_cash) die("There is no COA entry for cash account '$sl_cash_acc'");

  // Request all cash entries belonging to the invoice.
  $atres = SLQuery("select * from acc_trans where trans_id = $trans_id and chart_id = $chart_id_cash");
  if ($sl_err) die($sl_err);

  // Deduct payments for each procedure code from the respective balance owed.
  $keysuffix = 5000;
  for ($irow = 0; $irow < SLRowCount($atres); ++$irow) {
    $row = SLGetRow($atres, $irow);
    $code = strtoupper($row['memo']);
    $ins_id = $row['project_id'];
    if (! $code) $code = "Unknown";
    $amount = $row['amount'];
    $codes[$code]['bal'] += $amount; // amount is negative for a payment
    if ($ins_id)
      $codes[$code]['ins'] = $ins_id;

    // Add the details if they want 'em.
    if ($with_detail) {
      if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
      $tmpkey = $row['transdate'] . $keysuffix++;
      $tmp = array();
      $tmp['pmt'] = 0 - $amount;
      $tmp['src'] = $row['source'];
      if ($ins_id) $tmp['ins'] = $ins_id;
      $codes[$code]['dtl'][$tmpkey] = $tmp;
    }
  }

  // Request all line items with money or adjustment reasons belonging
  // to the invoice.
  $inres = SLQuery("SELECT * FROM invoice WHERE trans_id = $trans_id AND " .
    "( sellprice != 0 OR description LIKE 'Adjustment%' OR serialnumber = 'Claim' )");
  if ($sl_err) die($sl_err);

  // Add charges and adjustments for each procedure code into its total and balance.
  $keysuffix = 1000;
  for ($irow = 0; $irow < SLRowCount($inres); ++$irow) {
    $row = SLGetRow($inres, $irow);
    // $amount = $row['sellprice'];
    $amount = sprintf('%01.2f', $row['sellprice'] * $row['qty']);
    $ins_id = $row['project_id'];

    $code = "Unknown";
    if ($row['serialnumber'] == 'Claim') {
      $code = 'Claim';
    }
    else if (preg_match("/([A-Za-z0-9]\d\d\S*)/", $row['serialnumber'], $matches)) {
      $code = strtoupper($matches[1]);
    }
    else if (preg_match("/([A-Za-z0-9]\d\d\S*)/", $row['description'], $matches)) {
      $code = strtoupper($matches[1]);
    }

    $codes[$code]['chg'] += $amount;
    $codes[$code]['bal'] += $amount;
    if ($amount < 0) $codes[$code]['adj'] -= $amount;

    if ($ins_id)
      $codes[$code]['ins'] = $ins_id;

    // Add the details if they want 'em.
    if ($with_detail) {
      if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
      if (preg_match("/^Adjustment\s*(\S*)\s*(.*)/", $row['description'], $matches)) {
        $tmpkey = str_pad($matches[1], 10) . $keysuffix++;
        $tmp = array();
        $tmp['chg'] = $amount;
        $tmp['rsn'] = $matches[2];
        if ($ins_id) $tmp['ins'] = $ins_id;
        $codes[$code]['dtl'][$tmpkey] = $tmp;
      }
      else {
        $tmpkey = "          " . $keysuffix++;
        $tmp = array();
        $tmp['chg'] = $amount;
        $codes[$code]['dtl'][$tmpkey] = $tmp;
      }
    }
  }

  return $codes;
}

// Like the above, but for Integrated A/R.
//
function ar_get_invoice_summary($patient_id, $encounter_id, $with_detail = false) {
  $codes = array();
  $keysuff1 = 1000;
  $keysuff2 = 5000;

  // Get charges from services.
  $res = sqlStatement("SELECT " .
    "date, code_type, code, modifier, code_text, fee " .
    "FROM billing WHERE " .
    "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
    "activity = 1 AND fee != 0.00 ORDER BY id");

  while ($row = sqlFetchArray($res)) {
    $amount = sprintf('%01.2f', $row['fee']);

    if ($row['code_type'] == 'COPAY') {
      $code = 'CO-PAY';
      $codes[$code]['bal'] += $amount;
    }
    else {
      $code = strtoupper($row['code']);
      if (! $code) $code = "Unknown";
      if ($row['modifier']) $code .= ':' . strtoupper($row['modifier']);
      $codes[$code]['chg'] += $amount;
      $codes[$code]['bal'] += $amount;
    }
    // Add the details if they want 'em.
    if ($with_detail) {
      if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
      $tmp = array();
      if ($row['code_type'] == 'COPAY') {
        $tmp['pmt'] = 0 - $amount;
        $tmp['src'] = 'Pt Paid';
        $tmp['plv'] = 0;
        $tmpkey = substr($row['date'], 0, 10) . $keysuff2++;
      }
      else {
        $tmp['chg'] = $amount;
        $tmpkey = "          " . $keysuff1++;
      }
      $codes[$code]['dtl'][$tmpkey] = $tmp;
    }
  }

  // Get charges from product sales.
  $query = "SELECT s.drug_id, s.sale_date, s.fee, s.quantity, d.name " .
    "FROM drug_sales AS s " .
    "JOIN drugs AS d ON d.drug_id = s.drug_id WHERE " .
    "s.pid = '$patient_id' AND s.encounter = '$encounter_id' AND s.fee != 0 " .
    "ORDER BY s.sale_id";
  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
    $amount = sprintf('%01.2f', $row['fee']);
    $code = 'PROD:' . $row['drug_id'];
    $codes[$code]['chg'] += $amount;
    $codes[$code]['bal'] += $amount;
    // Add the details if they want 'em.
    if ($with_detail) {
      if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
      $tmp = array();
      $tmp['chg'] = $amount;
      $tmpkey = "          " . $keysuff1++;
      $codes[$code]['dtl'][$tmpkey] = $tmp;
    }
  }

  // Get payments and adjustments.
  $res = sqlStatement("SELECT " .
    "a.code, a.modifier, a.memo, a.payer_type, a.adj_amount, a.pay_amount, " .
    "a.post_time, a.session_id, " .
    "s.payer_id, s.reference, s.check_date, s.deposit_date " .
    "FROM ar_activity AS a " .
    "LEFT OUTER JOIN ar_session AS s ON s.session_id = a.session_id " .
    "WHERE a.pid = '$patient_id' AND a.encounter = '$encounter_id' " .
    "ORDER BY s.check_date, a.sequence_no");
  while ($row = sqlFetchArray($res)) {
    $code = strtoupper($row['code']);
    if (! $code) $code = "Unknown";
    if ($row['modifier']) $code .= ':' . strtoupper($row['modifier']);
    $ins_id = 0 + $row['payer_id'];
    $codes[$code]['bal'] -= $row['pay_amount'];
    $codes[$code]['bal'] -= $row['adj_amount'];
    $codes[$code]['chg'] -= $row['adj_amount'];
    $codes[$code]['adj'] += $row['adj_amount'];
    if ($ins_id) $codes[$code]['ins'] = $ins_id;
    // Add the details if they want 'em.
    if ($with_detail) {
      if (! $codes[$code]['dtl']) $codes[$code]['dtl'] = array();
      $tmp = array();
      $paydate = empty($row['deposit_date']) ? substr($row['post_time'], 0, 10) : $row['deposit_date'];
      if ($row['pay_amount'] != 0) $tmp['pmt'] = $row['pay_amount'];
      if ($row['adj_amount'] != 0 || $row['pay_amount'] == 0) {
        $tmp['chg'] = 0 - $row['adj_amount'];
        $tmp['rsn'] = (empty($row['memo']) || empty($row['session_id'])) ? 'Unknown adjustment' : $row['memo'];
        $tmpkey = $paydate . $keysuff1++;
      }
      else {
        $tmpkey = $paydate . $keysuff2++;
      }
      $tmp['src'] = empty($row['session_id']) ? $row['memo'] : $row['reference'];
      if ($ins_id) $tmp['ins'] = $ins_id;
      $tmp['plv'] = $row['payer_type'];
      $codes[$code]['dtl'][$tmpkey] = $tmp;
    }
  }
  return $codes;
}

// This determines the party from whom payment is currently expected.
// Returns: -1=Nobody, 0=Patient, 1=Ins1, 2=Ins2, 3=Ins3.
//
function responsible_party($trans_id) {
  global $sl_err;
  $arres = SLQuery("select * from ar where id = $trans_id");
  if ($sl_err) die($sl_err);
  $arrow = SLGetRow($arres, 0);
  if (! $arrow) die(xl("There is no match for invoice id = ") . $trans_id);
  if ($arrow['paid'] >= $arrow['netamount']) return -1;
  $insgot  = strtolower($arrow['notes']);
  $insdone = strtolower($arrow['shipvia']);
  for ($i = 1; $i <= 3; ++$i) {
    $lcvalue = "ins$i";
    if (strpos($insgot, $lcvalue) !== false && strpos($insdone, $lcvalue) === false)
      return $i;
  }
  return 0;
}

// As above but for Integrated A/R.
//
function ar_responsible_party($patient_id, $encounter_id) {
  $row = sqlQuery("SELECT date, last_level_billed, last_level_closed " .
    "FROM form_encounter WHERE " .
    "pid = '$patient_id' AND encounter = '$encounter_id' " .
    "ORDER BY id DESC LIMIT 1");
  if (empty($row)) return -1;
  $next_level = $row['last_level_closed'] + 1;
  if ($next_level <= $row['last_level_billed'])
    return $next_level;
  if (arGetPayerID($patient_id, substr($row['date'], 0, 10), $payer_type))
    return $next_level;
  // There is no unclosed insurance, so see if there is an unpaid balance.
  // Currently hoping that form_encounter.balance_due can be discarded.
  $balance = 0;
  $codes = ar_get_invoice_summary($patient_id, $encounter_id);
  foreach ($codes as $cdata) $balance += $cdata['bal'];
  if ($balance > 0) return 0;
  return -1;
}
?>
