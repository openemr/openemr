<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("drugs.inc.php");
 require_once($GLOBALS['fileroot'] . "/library/classes/class.phpmailer.php");
 require_once($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");

 function send_email($subject, $body) {
  $recipient = $GLOBALS['practice_return_email_path'];
  $mail = new PHPMailer();
  $mail->SetLanguage("en", $GLOBALS['fileroot'] . "/library/" );
  $mail->From = $recipient;
  $mail->FromName = 'In-House Pharmacy';
  $mail->isMail();
  $mail->Host = "localhost";
  $mail->Mailer = "mail";
  $mail->Body = $body;
  $mail->Subject = $subject;
  $mail->AddAddress($recipient);
  if(!$mail->Send()) {
   $die("There has been a mail error sending to " . $recipient .
    " " . $mail->ErrorInfo);
  }
 }

 $sale_id         = $_REQUEST['sale_id'];
 $drug_id         = $_REQUEST['drug_id'];
 $prescription_id = $_REQUEST['prescription'];
 $quantity        = $_REQUEST['quantity'];
 $fee             = $_REQUEST['fee'];
 $user            = $_SESSION['authUser'];

 if (!acl_check('admin', 'drugs')) die("Not authorized!");

 if (!$drug_id        ) $drug_id = 0;
 if (!$prescription_id) $prescription_id = 0;
 if (!$quantity       ) $quantity = 0;
 if (!$fee            ) $fee = 0.00;

 $inventory_id = 0;
 $bad_lot_list = '';
 $today = date('Y-m-d');

 // If there is no sale_id then this is a new dispensation.
 //
 if (! $sale_id) {
  // Find and update inventory, deal with errors.
  //
  if ($drug_id) {
   $res = sqlStatement("SELECT * FROM drug_inventory WHERE " .
    "drug_id = '$drug_id' AND on_hand > 0 " .
    "ORDER BY expiration, inventory_id");
   while ($row = sqlFetchArray($res)) {
    if ($row['expiration'] > $today && $row['on_hand'] >= $quantity) {
     break;
    }
    $tmp = $row['lot_number'];
    if (! $tmp) $tmp = '[missing lot number]';
    if ($bad_lot_list) $bad_lot_list .= ', ';
    $bad_lot_list .= $tmp;
   }

   if ($bad_lot_list) {
    send_email("Lot destruction needed",
     "The following lot(s) are expired or too small to fill prescription " .
     "$prescription_id and should be destroyed: $bad_lot_list\n");
   }

   if (! $row) {
    die("Inventory is not available for this order.");
   }

   $inventory_id = $row['inventory_id'];

   sqlStatement("UPDATE drug_inventory SET " .
    "on_hand = on_hand - $quantity " .
    "WHERE inventory_id = $inventory_id");

   $rowsum = sqlQuery("SELECT sum(on_hand) AS sum FROM drug_inventory WHERE " .
    "drug_id = '$drug_id' AND on_hand > '$quantity' AND expiration > CURRENT_DATE");
   $rowdrug = sqlQuery("SELECT * FROM drugs WHERE " .
    "drug_id = '$drug_id'");
   if ($rowsum['sum'] <= $rowdrug['reorder_point']) {
     send_email("Drug re-order required",
      "Drug '" . $rowdrug['name'] . "' has reached its reorder point.\n");
   }

   // TBD: Set and check a reorder notification date so we don't
   // send zillions of redundant emails.

  }

  $sale_id = sqlInsert("INSERT INTO drug_sales ( " .
   "drug_id, inventory_id, prescription_id, pid, user, sale_date, quantity, fee " .
   ") VALUES ( " .
   "'$drug_id', '$inventory_id', '$prescription_id', '$pid', '$user', '$today',
   '$quantity', '$fee' "  .
   ")");
 }

 // Generate the bottle label PDF for the sale identified by $sale_id.

 // Get details for what we guess is the primary facility.
 $frow = sqlQuery("SELECT * FROM facility " .
  "ORDER BY billing_location DESC, accepts_assignment DESC, id LIMIT 1");

 // Get everything else.
 $row = sqlQuery("SELECT " .
  "s.pid, s.quantity, s.prescription_id, " .
  "i.manufacturer, i.lot_number, i.expiration, " .
  "d.name, d.ndc_number, d.form, d.size, d.unit, " .
  "r.date_modified, r.dosage, r.route, r.interval, r.substitute, r.refills, " .
  "p.fname, p.lname, p.mname, " .
  "u.fname AS ufname, u.mname AS umname, u.lname AS ulname " .
  "FROM drug_sales AS s, drug_inventory AS i, drugs AS d, " .
  "prescriptions AS r, patient_data AS p, users AS u WHERE " .
  "s.sale_id = '$sale_id' AND " .
  "i.inventory_id = s.inventory_id AND " .
  "d.drug_id = i.drug_id AND " .
  "r.id = s.prescription_id AND " .
  "p.pid = s.pid AND " .
  "u.id = r.provider_id");

 $dconfig = $GLOBALS['oer_config']['druglabels'];
 $pdf =& new Cezpdf($dconfig['paper_size']);
 $pdf->ezSetMargins($dconfig['top'],$dconfig['bottom'],$dconfig['left'],$dconfig['right']);
 $pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");

 $header_text = $row['ufname'] . ' ' . $row['umname'] . ' ' . $row['ulname'] . "\n" .
  $frow['street'] . "\n" .
  $frow['city'] . ', ' . $frow['state'] . ' ' . $frow['postal_code'] .
  '  ' . $frow['phone'] . "\n";
 if ($dconfig['disclaimer']) $header_text .= $dconfig['disclaimer'] . "\n";

 $pdf->ezSetDy(20); // dunno why we have to do this...
 $pdf->ezText($header_text, 7, array('justification'=>'center'));

 if(!empty($dconfig['logo'])) {
  $pdf->ezSetDy(-5); // add space (move down) before the image
  $pdf->ezImage($dconfig['logo'], 0, 180, '', 'left');
  $pdf->ezSetDy(8);  // reduce space (move up) after the image
 }

 $label_text = $row['fname'] . ' ' . $row['lname'] . ' ' . $row['date_modified'] .
  ' RX#' . sprintf('%06u', $row['prescription_id']) . "\n" .
  $row['name'] . ' ' . $row['size'] . ' ' .
  $unit_array[$row['unit']] . ' QTY ' .
  $row['quantity'] . "\n" .
  'Take ' . $row['dosage'] . ' ' . $form_array[$row['form']] .
  ($row['dosage'] > 1 ? 's ' : ' ') .
  $interval_array_verbose[$row['interval']] . ' ' .
  $route_array_verbose[$row['route']] . "\n" .
  'Lot ' . $row['lot_number'] . ' Exp ' . $row['expiration'] . "\n" .
  'NDC ' . $row['ndc_number'] . ' ' . $row['manufacturer'];

 /****
 if ($row['refills']) {
  // Find out how many times this prescription has been filled/refilled.
  // Is this right?  Perhaps we should instead sum the dispensed quantities
  // and reconcile with the prescription quantities.
  $refills_row = sqlQuery("SELECT count(*) AS count FROM drug_sales " .
   "WHERE prescription_id = '" . $row['prescription_id'] .
   "' AND quantity > 0");
  $label_text .= ($refills_row['count'] - 1) . ' of ' . $row['refills'] . ' refills';
 }
 ****/

 $pdf->ezText($label_text, 9, array('justification'=>'center'));

 $pdf->ezStream();
?>