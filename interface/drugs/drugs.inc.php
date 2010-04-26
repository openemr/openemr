<?php
// Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Modified 7-2009 by BM in order to migrate using the form,
// unit, route, and interval lists with the
// functions in openemr/library/options.inc.php .
// These lists are based on the constants found in the 
// openemr/library/classes/Prescription.class.php file.

$substitute_array = array('', xl('Allowed'), xl('Not Allowed'));

function send_drug_email($subject, $body) {
  require_once ($GLOBALS['srcdir'] . "/classes/class.phpmailer.php");
  $recipient = $GLOBALS['practice_return_email_path'];
  if (empty($recipient)) return;
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
    error_log("There has been a mail error sending to " . $recipient .
      " " . $mail->ErrorInfo);
  }
}

function sellDrug($drug_id, $quantity, $fee, $patient_id=0, $encounter_id=0,
  $prescription_id=0, $sale_date='', $user='', $default_warehouse='') {

  if (empty($patient_id))   $patient_id   = $GLOBALS['pid'];
  if (empty($sale_date))    $sale_date    = date('Y-m-d');
  if (empty($user))         $user         = $_SESSION['authUser'];

  // error_log("quantity = '$quantity'"); // debugging

  if (empty($default_warehouse)) {
    // Get the default warehouse, if any, for the user.
    $rowuser = sqlQuery("SELECT default_warehouse FROM users WHERE username = '$user'");
    $default_warehouse = $rowuser['default_warehouse'];
  }

  // Get relevant options for this product.
  $rowdrug = sqlQuery("SELECT allow_combining, reorder_point, name " .
    "FROM drugs WHERE drug_id = '$drug_id'");
  $allow_combining = $rowdrug['allow_combining'];

  // Combining is never allowed for prescriptions and will not work with
  // dispense_drug.php.
  if ($prescription_id) $allow_combining = 0;

  $rows = array();
  // $firstrow = false;
  $qty_left = $quantity;
  $bad_lot_list = '';
  $total_on_hand = 0;

  // If the user has a default warehouse, sort those lots first.
  $orderby = ($default_warehouse === '') ?
    "" : "di.warehouse_id != '$default_warehouse', ";
  $orderby .= "lo.seq, di.expiration, di.lot_number, di.inventory_id";

  // Retrieve lots in order of expiration date within warehouse preference.
  $res = sqlStatement("SELECT di.*, lo.option_id, lo.seq " .
    "FROM drug_inventory AS di " .
    "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
    "lo.option_id = di.warehouse_id " .
    "WHERE " .
    "di.drug_id = '$drug_id' AND di.destroy_date IS NULL " .
    "ORDER BY $orderby");

  // First pass.  Pick out lots to be used in filling this order, figure out
  // if there is enough quantity on hand and check for lots to be destroyed.
  while ($row = sqlFetchArray($res)) {
    // Warehouses with seq > 99 are not available.
    $seq = empty($row['seq']) ? 0 : $row['seq'] + 0;
    if ($seq > 99) continue;

    $on_hand = $row['on_hand'];
    $expired = (!empty($row['expiration']) && $row['expiration'] <= $sale_date);
    if ($expired || $on_hand < $quantity) {
      $tmp = $row['lot_number'];
      if (! $tmp) $tmp = '[missing lot number]';
      if ($bad_lot_list) $bad_lot_list .= ', ';
      $bad_lot_list .= $tmp;
    }
    if ($expired) continue;

    /*****************************************************************
    // Note the first row in case total quantity is insufficient and we are
    // allowed to go negative.
    if (!$firstrow) $firstrow = $row;
    *****************************************************************/

    $total_on_hand += $on_hand;

    if ($on_hand > 0 && $qty_left > 0 && ($allow_combining || $on_hand >= $qty_left)) {
      $rows[] = $row;
      $qty_left -= $on_hand;
    }
  }

  if ($bad_lot_list) {
    send_drug_email("Possible lot destruction needed",
      "The following lot(s) are expired or were too small to fill the " .
      "order for patient $patient_id: $bad_lot_list\n");
  }

  /*******************************************************************
  if (empty($firstrow)) return 0; // no suitable lots exist
  // This can happen when combining is not allowed.  We will use the
  // first row and take it negative.
  if (empty($rows)) {
    $rows[] = $firstrow;
    $qty_left -= $firstrow['on_hand'];
  }
  *******************************************************************/

  // The above was an experiment in permitting a negative lot quantity.
  // We decided that was a bad idea, so now we just error out if there
  // is not enough on hand.
  if ($qty_left > 0) return 0;

  $sale_id = 0;
  $qty_final = $quantity; // remaining unallocated quantity
  $fee_final = $fee;      // remaining unallocated fee

  // Second pass.  Update the database.
  foreach ($rows as $row) {
    $inventory_id = $row['inventory_id'];

    /*****************************************************************
    $thisqty = $row['on_hand'];
    if ($qty_left > 0) {
      $thisqty += $qty_left;
      $qty_left = 0;
    }
    else if ($thisqty > $qty_final) {
      $thisqty = $qty_final;
    }
    *****************************************************************/
    $thisqty = min($qty_final, $row['on_hand']);

    $qty_final -= $thisqty;

    // Compute the proportional fee for this line item.  For the last line
    // item take the remaining unallocated fee to avoid round-off error.
    if ($qty_final)
      $thisfee = sprintf('%0.2f', $fee * $thisqty / $quantity);
    else
      $thisfee = sprintf('%0.2f', $fee_final);
    $fee_final -= $thisfee;

    // Update inventory and create the sale line item.
    sqlStatement("UPDATE drug_inventory SET " .
      "on_hand = on_hand - $thisqty " .
      "WHERE inventory_id = $inventory_id");
    $sale_id = sqlInsert("INSERT INTO drug_sales ( " .
      "drug_id, inventory_id, prescription_id, pid, encounter, user, " .
      "sale_date, quantity, fee ) VALUES ( " .
      "'$drug_id', '$inventory_id', '$prescription_id', '$patient_id', " .
      "'$encounter_id', '$user', '$sale_date', '$thisqty', '$thisfee' )");
  }

  // If appropriate, generate email to notify that re-order is due.
  if (($total_on_hand - $quantity) <= $rowdrug['reorder_point']) {
    send_drug_email("Product re-order required",
      "Product '" . $rowdrug['name'] . "' has reached its reorder point.\n");
  }

  // If combining is allowed then $sale_id will be just the last inserted ID,
  // and it serves only to indicate that everything worked.  Otherwise there
  // can be only one inserted row and this is its ID.
  return $sale_id;
}
?>
