<?php
// Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This returns an associative array keyed on procedure code.
// Its values are associative arrays having the following keys:
//  chg - the sum of line items, including adjustments, for the code
//  bal - the unpaid balance
//  ins - the id of the insurance company that was billed
//
function get_invoice_summary($trans_id) {
  global $sl_err, $sl_cash_acc;

  $codes = array();

  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);
  if (! $chart_id_cash) die("There is no COA entry for cash account '$sl_cash_acc'");

  // Request all cash entries belonging to the invoice.
  $atres = SLQuery("select * from acc_trans where trans_id = $trans_id and chart_id = $chart_id_cash");
  if ($sl_err) die($sl_err);

  // Deduct payments for each procedure code from the respective balance owed.
  for ($irow = 0; $irow < SLRowCount($atres); ++$irow) {
    $row = SLGetRow($atres, $irow);
    $code = strtoupper($row['memo']);
    $ins_id = $row['project_id'];
    if (! $code) $code = "Unknown";
    $amount = $row['amount'];
    $codes[$code]['bal'] += $amount; // amount is negative for a payment
    if ($ins_id)
      $codes[$code]['ins'] = $ins_id;
  }

  // Request all line items with money belonging to the invoice.
  $inres = SLQuery("select * from invoice where trans_id = $trans_id and sellprice != 0");
  if ($sl_err) die($sl_err);

  // Add charges and adjustments for each procedure code into its total and balance.
  for ($irow = 0; $irow < SLRowCount($inres); ++$irow) {
    $row = SLGetRow($inres, $irow);
    $amount = $row['sellprice'];
    $ins_id = $row['project_id'];

    $code = "Unknown";
    if (preg_match("/([A-Za-z0-9]\d\d\S*)/", $row['serialnumber'], $matches)) {
      $code = strtoupper($matches[1]);
    }
    else if (preg_match("/([A-Za-z0-9]\d\d\S*)/", $row['description'], $matches)) {
      $code = strtoupper($matches[1]);
    }

    $codes[$code]['chg'] += $amount;
    $codes[$code]['bal'] += $amount;

    if ($ins_id)
      $codes[$code]['ins'] = $ins_id;
  }

  return $codes;
}
?>
