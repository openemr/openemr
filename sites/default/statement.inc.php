<?php

// Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
//
// Windows compatibility mods 2009 Bill Cernansky [mi-squared.com]
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Updated by Medical Information Integration, LLC to support download
//  and multi OS use - tony@mi-squared..com 12-2009
// Date changes made possible on advise of Terry Hill 2014-0814
// Some other advises and instructions made by Pimm Blankevoort 18-08-2014

//////////////////////////////////////////////////////////////////////
// This is a template for printing patient statements and collection
// letters.  You must customize it to suit your practice.  If your
// needs are simple then you do not need programming experience to do
// this - just read the comments and make appropriate substitutions.
// All you really need to do is replace the [strings in brackets].
//////////////////////////////////////////////////////////////////////

// The location/name of a temporary file to hold printable statements.
//


$STMT_TEMP_FILE = $GLOBALS['temporary_files_dir'] . "/openemr_statements.txt";
$STMT_TEMP_FILE_PDF = $GLOBALS['temporary_files_dir'] . "/openemr_statements.pdf";

$STMT_PRINT_CMD = $GLOBALS['print_command']; 

// This function builds a printable statement or collection letter from
// an associative array having the following keys:
//
//  today   = statement date yyyy-mm-dd can be changed if instruction in this file are followed
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

// A sample of the text based format follows:

//[Your Clinic Name]             Patient Name          2009-12-29
//[Your Clinic Address]          Chart Number: 1848
//[City, State Zip]              Insurance information on file
//
//
//ADDRESSEE                      REMIT TO
//Patient Name                     [Your Clinic Name]
//patient address                  [Your Clinic Address]
//city, state zipcode              [City, State Zip]
//                                 If paying by VISA/MC/AMEX/Dis
//
//Card_____________________  Exp______ Signature___________________
//                     Return above part with your payment
//-----------------------------------------------------------------
//
//_______________________ STATEMENT SUMMARY _______________________
//
//Visit Date  Description                                    Amount
//
//2009-08-20  Procedure 99345                                198.90
//            Paid 2009-12-15:                               -51.50
//... more details ...
//...
//...
// skipping blanks in example
//
//
//Name: Patient Name              Date: 2009-12-29     Due:   147.40
//_________________________________________________________________
//
//Please call if any of the above information is incorrect
//We appreciate prompt payment of balances due
//
//[Your billing contact name]
//  Billing Department
//  [Your billing dept phone]

  

function create_statement($stmt) {
 if (! $stmt['pid']) return ""; // get out if no data

 // These are your clinics return address, contact etc.  Edit them.
 // TBD: read this from the facility table
 
 // Facility (service location)
  $atres = sqlStatement("select f.name,f.street,f.city,f.state,f.postal_code from facility f " .
    " left join users u on f.id=u.facility_id " .
    " left join  billing b on b.provider_id=u.id and b.pid = '".$stmt['pid']."' " .
    " where  service_location=1");


// Change this line to make it today and dos into dd-mm-yyyy (8,2 5,2 2,2) or mm-dd-yyyy (5,2 8,2 2,2)  
$tmpdate=substr($stmt['today'] ,8,2) . "-" . substr($stmt['today'] ,5,2) . "-" . substr($stmt['today'] ,2,2);
$tmpdos=substr($dos ,5,2) . "-" . substr($dos ,8,2) . "-" . substr($dos ,2,2);

// change this line it is about line about 169 $out = sprintf("%-30s %-23s %-s\n",$clinic_name,$stmt['patient'],$tmpdate);
// add this line $tmpdos=substr($dos ,5,2) . "-" . substr($dos,8,2) . "-" . substr($dos ,2,2) mm-dd-yyyy;
// change this line it is about line about 235 $out .= sprintf("%-10s %-45s%8s\n", $tmpdos, $desc, $amount);
// and change this line about 257 $out .= sprintf("%-s: %-25s %-s: %-14s %-s:%8s\n",$label_ptname,$stmt['patient'],
// also line about 257 $label_today,$tmpdate,$label_due,$stmt['amount']);
// Terry Hill



  $row = sqlFetchArray($atres);
 
 // Facility (service location)
 $clinic_name = "{$row['name']}";
 $clinic_addr = "{$row['street']}";
 $clinic_csz = "{$row['city']}, {$row['state']}, {$row['postal_code']}";
 
 
 // Billing location
 $remit_name = $clinic_name;
 $remit_addr = $clinic_addr;
 $remit_csz = $clinic_csz;
 
 // Contacts
  $atres = sqlStatement("select f.attn,f.phone from facility f " .
    " left join users u on f.id=u.facility_id " .
    " left join  billing b on b.provider_id=u.id and b.pid = '".$stmt['pid']."'  " .
    " where billing_location=1");
  $row = sqlFetchArray($atres);
 $billing_contact = "{$row['attn']}";
 $billing_phone = "{$row['phone']}";

 // Text only labels
 
 $label_addressee = xl('ADDRESSEE');
 $label_remitto = xl('REMIT TO');
 $label_chartnum = xl('Chart number');
 $label_insinfo = xl('Insurance on file');
 $label_totaldue = xl('Total amount due');
 $label_payby = xl('If paying by');
 $label_cards = xl('VISA/MC/AMEX/Dis');  
 $label_cardnum = xl('Card');
 $label_expiry = xl('Exp');
 $label_sign = xl('Signature');
 $label_retpay = xl('Return above part with your payment');
 $label_pgbrk = xl('STATEMENT SUMMARY');
 $label_visit = xl('Visit Date');
 $label_desc = xl('Description');
 $label_amt = xl('Amount');

 // This is the text for the top part of the page, up to but not
 // including the detail lines.  Some examples of variable fields are:
 //  %s    = string with no minimum width
 //  %9s   = right-justified string of 9 characters padded with spaces
 //  %-25s = left-justified string of 25 characters padded with spaces
 // Note that "\n" is a line feed (new line) character.
 // reformatted to handle i8n by tony

// Add this line about 138
// $tmpdate=substr($stmt['today'] ,8,2) . "-" . substr($stmt['today'] ,5,2) . "-" . substr($stmt['today'] ,2,2);

$out = sprintf("%-30s %-23s %-s\n",$clinic_name,$stmt['patient'],$tmpdate);

// $out  = sprintf("%-30s %-s %s\n",$clinic_name,$stmt['patient'],$stmt['today']);


$out .= sprintf("%-30s %s: %-s\n",$clinic_addr,$label_chartnum,$stmt['pid']);
$out .= sprintf("%-30s %-s %-s\n",$clinic_csz,$label_insinfo,null);
$out .= sprintf("%-30s %s: %-s\n",null,$label_totaldue,null]);
$out .= "\n\n";
$out .= sprintf("  %-28s %-s\n",$label_addressee,$label_remitto);
$out .= sprintf("  %-28s %s\n",$stmt['to'][0],$remit_name);
$out .= sprintf("  %-28s %s\n",$stmt['to'][1],$remit_addr);
$out .= sprintf("  %-28s %s\n",$stmt['to'][2],$remit_csz);

if($stmt['to'][3]!='')//to avoid double blank lines the if condition is put.
 	$out .= sprintf("   %-32s\n",$stmt['to'][3]);
$out .= sprintf("_________________________________________________________________\n");
$out .= "\n";

// Take this out of there is no Credit card activity to the next End credit Card activity

$out .= sprintf("%-32s\n",$label_payby.' '.$label_cards);
$out .= "\n";
$out .= sprintf("%s_____________________  %s______ %s___________________\n",
               $label_cardnum,$label_expiry,$label_sign);
$out .= sprintf("%-20s %s\n",null,$label_retpay);
$out .= sprintf("-----------------------------------------------------------------\n");
$out .= "\n";
$out .= sprintf("_______________________ %s _______________________\n",$label_pgbrk);
$out .= "\n";
$out .= sprintf("%-11s %-46s %s\n",$label_visit,$label_desc,$label_amt);

// End no use of Credit Card activity

$out .= "\n";
 
 // This must be set to the number of lines generated above.
 $count = 21;

 // This generates the detail lines.  Again, note that the values must
 // be specified in the order used.
 //
 foreach ($stmt['lines'] as $line) {
  $description = $line['desc'];
  $tmp = substr($description, 0, 14);
  if ($tmp == 'Procedure 9920' || $tmp == 'Procedure 9921')
   $description = xl('Office Visit');

  $dos = $line['dos'];

// Makes the date mm-dd-yyyy: $tmpdos=substr($dos ,5,2) . "-" . substr($dos,8,2) . "-" . substr($dos ,2,2);
  $tmpdos=substr($dos ,2,2) . "-" . substr($dos ,5,2) . "-" . substr($dos ,8,2);

  ksort($line['detail']);

  foreach ($line['detail'] as $dkey => $ddata) {
   $ddate = substr($dkey, 0, 10);
   if (preg_match('/^(\d\d\d\d)(\d\d)(\d\d)\s*$/', $ddate, $matches)) {
    $ddate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
   }
   $amount = '';

 
   if ($ddata['pmt']) {
    $amount = sprintf("%.2f", 0 - $ddata['pmt']);
    $desc = xl('Paid') .' '. $ddate .': '. $ddata['src'].' '. $ddata['insurance_company'];
   } else if ($ddata['rsn']) {
    if ($ddata['chg']) {
     $amount = sprintf("%.2f", $ddata['chg']);
     $desc = xl('Adj') .' '.  $ddate .': ' . $ddata['rsn'].' '. $ddata['insurance_company'];
    } else {
     $desc = xl('Note') .' '. $ddate .': '. $ddata['rsn'].' '. $ddata['insurance_company'];
    }
   } else if ($ddata['chg'] < 0) {
    $amount = sprintf("%.2f", $ddata['chg']);
    $desc = xl('Patient Payment');
   } else {
    $amount = sprintf("%.2f", $ddata['chg']);
    $desc = $description;
   }

   // Original: $out .= sprintf("%-10s  %-45s%8s\n", $dos, $desc, $amount);
   $out .= sprintf("%-10s  %-45s%8s\n", $tmpdos, $desc, $amount);
   $dos = '';
   ++$count;
  }
 }

 // This generates blank lines until we are at line 42. If credit-card activity is taken out (about ten lines) 42 can set to 55.
 while ($count++ < 42) $out .= "\n";

 // Fixed text labels
 $label_ptname = xl('Name');
 $label_today = xl('Date');
 $label_due = xl('Due');
 $label_thanks = xl('Thank you for choosing');
 $label_call = xl('Please call if any of the above information is incorrect');
 $label_prompt = xl('We appreciate prompt payment of balances due');
 $label_dept = xl('Billing Department');
 
// This is the bottom portion of the page.

 $out .= sprintf("%-s: %-25s %-s: %-14s %-s:%8s\n",$label_ptname,$stmt['patient'],$label_today,$tmpdate,$label_due,$stmt['amount']);
 $out .= sprintf("__________________________________________________________________\n");
 $out .= "\n";
 $out .= sprintf("%-s\n",$label_prompt);
 $out .= sprintf("%-s\n",$label_call);
 $out .= "\n";
 $out .= sprintf("%-s\n",$billing_contact);
 $out .= sprintf("%-s:",$label_dept,$billing_phone);
 $out .= sprintf("%-s",$billing_phone);

 $out .= "\014"; // this is a form feed
 
 return $out;
}
?>
