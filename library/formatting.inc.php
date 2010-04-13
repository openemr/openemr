<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

function oeFormatMoney($amount) {
  return number_format($amount,
    $GLOBALS['currency_decimals'],
    $GLOBALS['currency_dec_point'],
    $GLOBALS['currency_thousands_sep']);
}

function oeFormatShortDate($date='today') {
  if ($date === 'today') $date = date('Y-m-d');
  if (strlen($date) == 10) {
    // assume input is yyyy-mm-dd
    if ($GLOBALS['date_display_format'] == 1)      // mm/dd/yyyy
      $date = substr($date, 5, 2) . '/' . substr($date, 8, 2) . '/' . substr($date, 0, 4);
    else if ($GLOBALS['date_display_format'] == 2) // dd/mm/yyyy
      $date = substr($date, 8, 2) . '/' . substr($date, 5, 2) . '/' . substr($date, 0, 4);
  }
  return $date;
}

// Format short date from time.
function oeFormatSDFT($time) {
  return oeFormatShortDate(date('Y-m-d', $time));
}

// Format the body of a patient note.
function oeFormatPatientNote($note) {
  $i = 0;
  while ($i !== false) {
    if (preg_match('/^\d\d\d\d-\d\d-\d\d/', substr($note, $i))) {
      $note = substr($note, 0, $i) . oeFormatShortDate(substr($note, $i, 10)) . substr($note, $i + 10);
    }
    $i = strpos("\n", $note, $i);
    if ($i !== false) ++$i;
  }
  return $note;
}

function oeFormatClientID($id) {

  // TBD

  return $id;
}

function oeFormatInventoryID($id) {

  // TBD

  return $id;
}

function oeFormatInvoiceNumber($invno) {

  // TBD

  return $invno;
}

?>
