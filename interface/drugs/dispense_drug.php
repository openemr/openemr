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

 $sales_id        = $_REQUEST['sales_id'];
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

 if (! $sales_id) {
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

  $sales_id = sqlInsert("INSERT INTO drug_sales ( " .
   "drug_id, inventory_id, prescription_id, pid, user, sale_date, quantity, fee " .
   ") VALUES ( " .
   "'$drug_id', '$inventory_id', '$prescription_id', '$pid', '$user', '$today',
   '$quantity', '$fee' "  .
   ")");

  echo "Inventory has been updated. Here we will send a PDF for the bottle label.\n";
 }

 // TBD: Generate the bottle label PDF for the sale identified by $sales_id.

?>
