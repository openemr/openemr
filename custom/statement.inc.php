<?php

// Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//////////////////////////////////////////////////////////////////////
// This is a template for printing patient statements and collection
// letters.  You must customize it to suit your practice.  If your
// needs are simple then you do not need programming experience to do
// this - just read the comments and make appropriate substitutions.
// All you really need to do is replace the [strings in brackets].
//////////////////////////////////////////////////////////////////////

// The location/name of a temporary file to hold printable statements.
//
$STMT_TEMP_FILE = "/tmp/openemr_statements.txt";

// This is the command to be used for printing (without the filename).
// The word following "-P" should be the name of your printer.  This
// example is designed for 8.5x11-inch paper with 1-inch margins,
// 10 CPI, 6 LPI, 65 columns, 54 lines per page.
//
$STMT_PRINT_CMD = "lpr -P HPLaserjet6P -o cpi=10 -o lpi=6 -o page-left=72 -o page-top=72";

// This function builds a printable statement or collection letter from
// an associative array having the following keys:
//
//  today   = statement date yyyy-mm-dd
//  pid     = patient ID
//  patient = patient name
//  amount  = total amount due
//  to      = array of addressee name/address lines
//  lines   = array of lines, each with the following keys:
//    dos     = date of service yyyy-mm-dd
//    desc    = description
//    amount  = charge less adjustments
//    paid    = amount paid
//    notice  = 1 for first notice, 2 for second, etc.
//    detail  = associative array of details
//
// Each detail array is keyed on a string beginning with a date in
// yyyy-mm-dd format, or blanks in the case of the original charge
// items.  Its values are associative arrays like this:
//
//  pmt - payment amount as a positive number, only for payments
//  src - check number or other source, only for payments
//  chg - invoice line item amount amount, only for charges or
//        adjustments (adjustments may be zero)
//  rsn - adjustment reason, only for adjustments
//
// The returned value is a string that can be sent to a printer.
// This example is plain text, but if you are a hotshot programmer
// then you could make a PDF or PostScript or whatever peels your
// banana.  These strings are sent in succession, so append a form
// feed if that is appropriate.
//
function create_statement($stmt) {
 if (! $stmt['pid']) return ""; // get out if no data

 // This is the text for the top part of the page, up to but not
 // including the detail lines.  Some examples of variable fields are:
 //  %s    = string with no minimum width
 //  %9s   = right-justified string of 9 characters padded with spaces
 //  %-25s = left-justified string of 25 characters padded with spaces
 // Note that "\n" is a line feed (new line) character.
 //
 $out = sprintf(
  "[Your Clinic Name]             %-23s %s\n" .
  "[Your Clinic Address]          Chart Number %s\n" .
  "[City, State Zip]              Insurance information on file\n" .
  "                               Total amount due: %s\n" .
  "\n" .
  "\n" .
  "ADDRESSEE:                       REMIT TO:\n" .
  "\n" .
  "%-32s [Remit-To Name]\n" .
  "%-32s [Remit-To Address]\n" .
  "%-32s [City, State Zip]\n" .
  "%-32s If paying by VISA/MC/AMEX/Disc:\n" .
  "\n" .
  "Card#_____________________  Exp______ Signature__________________\n" .
  "              (Return above part with your payment)\n" .
  "-----------------------------------------------------------------\n" .
  "\n" .
  "_______________________ STATEMENT SUMMARY _______________________\n" .
  "\n" .
  "Visit Date  Description                                    Amount\n" .
  "\n",

  // These are the values for the variable fields.  They must appear
  // here in the same order as in the above text!
  //
  $stmt['patient'],
  $stmt['today'],
  $stmt['pid'],
  $stmt['amount'],
  $stmt['to'][0],
  $stmt['to'][1],
  $stmt['to'][2],
  $stmt['to'][3]);

 // This must be set to the number of lines generated above.
 //
 $count = 21;

 // This generates the detail lines.  Again, note that the values must
 // be specified in the order used.
 //
 foreach ($stmt['lines'] as $line) {
  $description = $line['desc'];
  $tmp = substr($description, 0, 14);
  if ($tmp == 'Procedure 9920' || $tmp == 'Procedure 9921')
   $description = 'Office Visit';

  $dos = $line['dos'];
  ksort($line['detail']);

  foreach ($line['detail'] as $dkey => $ddata) {
   $ddate = substr($dkey, 0, 10);
   if (preg_match('/^(\d\d\d\d)(\d\d)(\d\d)\s*$/', $ddate, $matches)) {
    $ddate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
   }
   $amount = '';

   if ($ddata['pmt']) {
    $amount = sprintf("%.2f", 0 - $ddata['pmt']);
    $desc = "Paid $ddate: " . $ddata['src'];
   } else if ($ddata['rsn']) {
    if ($ddata['chg']) {
     $amount = sprintf("%.2f", $ddata['chg']);
     $desc = "Adj  $ddate: " . $ddata['rsn'];
    } else {
     $desc = "Note $ddate: " . $ddata['rsn'];
    }
   } else if ($ddata['chg'] < 0) {
    $amount = sprintf("%.2f", $ddata['chg']);
    $desc = "Patient Payment";
   } else {
    $amount = sprintf("%.2f", $ddata['chg']);
    $desc = $description;
   }

   $out .= sprintf("%-10s  %-45s%8s\n", $dos, $desc, $amount);
   $dos = '';
   ++$count;
  }
 }

 // This generates blank lines until we are at line 42.
 //
 while ($count++ < 42) $out .= "\n";

 // This is the bottom portion of the page.  You know the drill.
 //
 $out .= sprintf(
  "Name: %-25s Date: %-10s     Due:%8s\n" .
  "_________________________________________________________________\n" .
  "\n" .
  "Thank you for choosing [Your Clinic Name].\n" .
  "\n" .
  "Please call if any of the above information is incorrect.\n" .
  "We appreciate prompt payment of balances due.\n" .
  "\n" .
  "[Your billing contact name]\n" .
  "Billing Department\n" .
  "[Your billing contact phone number]" .
  "\014", // this is a form feed

  $stmt['patient'], // values start here
  $stmt['today'],
  $stmt['amount']);

 return $out;
}
?>
