<?php
  // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  include_once("sql-ledger.inc");
  include_once("patient.inc");
  include_once("invoice_summary.inc.php");

  $chart_id_cash   = 0;
  $chart_id_ar     = 0;
  $chart_id_income = 0;
  $services_id     = 0;

  function slInitialize() {
    global $chart_id_cash, $chart_id_ar, $chart_id_income, $services_id;
    global $sl_cash_acc, $sl_ar_acc, $sl_income_acc, $sl_services_id;
    SLConnect();

    $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
    if ($sl_err) die($sl_err);
    if (! $chart_id_cash) die(xl("There is no COA entry for cash account ") . "'$sl_cash_acc'");

    $chart_id_ar = SLQueryValue("select id from chart where accno = '$sl_ar_acc'");
    if ($sl_err) die($sl_err);
    if (! $chart_id_ar) die(xl("There is no COA entry for AR account ") . "'$sl_ar_acc'");

    $chart_id_income = SLQueryValue("select id from chart where accno = '$sl_income_acc'");
    if ($sl_err) die($sl_err);
    if (! $chart_id_income) die(xl("There is no COA entry for income account ") . "'$sl_income_acc'");

    $services_id = SLQueryValue("select id from parts where partnumber = '$sl_services_id'");
    if ($sl_err) die($sl_err);
    if (! $services_id) die(xl("There is no parts entry for services ID ") . "'$sl_services_id'");
  }

  function slTerminate() {
    SLClose();
  }

  // Try to figure out our invoice number (pid.encounter) from the
  // claim ID and other stuff in the ERA.  This should be straightforward
  // except that some payers mangle the claim ID that we give them.
  //
  function slInvoiceNumber(&$out) {
    $invnumber = $out['our_claim_id'];
    $atmp = preg_split('/[ -]/', $invnumber);
    $acount = count($atmp);

    $pid = 0;
    $encounter = 0;
    if ($acount == 2) {
      $pid = $atmp[0];
      $encounter = $atmp[1];
    }
    else if ($acount == 3) {
      $pid = $atmp[0];
      $brow = sqlQuery("SELECT encounter FROM billing WHERE " .
        "pid = '$pid' AND id = '" . $atmp[1] . "' AND activity = 1");
      $encounter = $brow['encounter'];
    }
    else if ($acount == 1) {
      $pres = sqlStatement("SELECT pid FROM patient_data WHERE " .
        "lname LIKE '" . addslashes($out['patient_lname']) . "' AND " .
        "fname LIKE '" . addslashes($out['patient_fname']) . "' " .
        "ORDER BY pid DESC");
      while ($prow = sqlFetchArray($pres)) {
        if (strpos($invnumber, $prow['pid']) === 0) {
          $pid = $prow['pid'];
          $encounter = substr($invnumber, strlen($pid));
          break;
        }
      }
    }

    if ($pid && $encounter) $invnumber = "$pid.$encounter";
    return array($pid, $encounter, $invnumber);
  }

  // Insert a row into the acc_trans table.
  //
  function slAddTransaction($invid, $chartid, $amount, $date, $source, $memo, $insplan, $debug) {
    global $sl_err;
    $date = fixDate($date);
    $query = "INSERT INTO acc_trans ( " .
      "trans_id, "     .
      "chart_id, "     .
      "amount, "       .
      "transdate, "    .
      "source, "       .
      "project_id, "   .
      "memo "          .
      ") VALUES ( "    .
      "$invid, "       . // trans_id
      "$chartid, "     . // chart_id
      "$amount, "      . // amount
      "'$date', "      . // transdate
      "'$source', "    . // source
      "$insplan, "     . // project_id
      "'$memo' "       . // memo
      ")";
    if ($debug) {
      echo $query . "<br>\n";
    } else {
      SLQuery($query);
      if ($sl_err) die($sl_err);
    }
  }

  // Insert a row into the invoice table.
  //
  function slAddLineItem($invid, $serialnumber, $amount, $adjdate, $insplan, $reason, $debug) {
    global $sl_err, $services_id;
    $adjdate = fixDate($adjdate);
    $description = "Adjustment $adjdate $reason";
    $query = "INSERT INTO invoice ( " .
      "trans_id, "          .
      "parts_id, "          .
      "description, "       .
      "qty, "               .
      "allocated, "         .
      "sellprice, "         .
      "fxsellprice, "       .
      "discount, "          .
      "unit, "              .
      "project_id, "        .
      "serialnumber "       .
      ") VALUES ( "         .
      "$invid, "            . // trans_id
      "$services_id, "      . // parts_id
      "'$description', "    . // description
      "1, "                 . // qty
      "0, "                 . // allocated
      "$amount, "           . // sellprice
      "$amount, "           . // fxsellprice
      "0, "                 . // discount
      "'', "                . // unit
      "$insplan, "          . // project_id
      "'$serialnumber'"     . // serialnumber
      ")";
    if ($debug) {
      echo $query . "<br>\n";
    } else {
      SLQuery($query);
      if ($sl_err) die($sl_err);
    }
  }

  // Update totals and payment date in the invoice header.  Dollar amounts are
  // stored as double precision floats so we have to be careful about rounding.
  //
  function slUpdateAR($invid, $amount, $paid = 0, $paydate = "", $debug) {
    global $sl_err;
    $paydate = fixDate($paydate);
    $query = "UPDATE ar SET amount = round(CAST (amount AS numeric) + $amount, 2), " .
      "netamount = round(CAST (netamount AS numeric) + $amount, 2)";
    if ($paid) $query .= ", paid = round(CAST (paid AS numeric) + $paid, 2), datepaid = '$paydate'";
    $query .= " WHERE id = $invid";
    if ($debug) {
      echo $query . "<br>\n";
    } else {
      SLQuery($query);
      if ($sl_err) die($sl_err);
    }
  }

  function slPostPayment($trans_id, $thispay, $thisdate, $thissrc, $code, $thisins, $debug) {
    global $chart_id_cash, $chart_id_ar;
    // Post a payment: add to ar, subtract from cash.
    slAddTransaction($trans_id, $chart_id_ar, $thispay, $thisdate, $thissrc, $code, $thisins, $debug);
    slAddTransaction($trans_id, $chart_id_cash, 0 - $thispay, $thisdate, $thissrc, $code, $thisins, $debug);
    slUpdateAR($trans_id, 0, $thispay, $thisdate, $debug);
  }

  function slPostAdjustment($trans_id, $thisadj, $thisdate, $thissrc, $code, $thisins, $reason, $debug) {
    global $chart_id_income, $chart_id_ar;
    // Post an adjustment: add negative invoice item, add to ar, subtract from income
    slAddLineItem($trans_id, $code, 0 - $thisadj, $thisdate, $thisins, $reason, $debug);
    if ($thisadj) {
      slAddTransaction($trans_id, $chart_id_ar, $thisadj, $thisdate, "InvAdj $thissrc", $code, $thisins, $debug);
      slAddTransaction($trans_id, $chart_id_income, 0 - $thisadj, $thisdate, "InvAdj $thissrc", $code, $thisins, $debug);
      slUpdateAR($trans_id, 0 - $thisadj, 0, '', $debug);
    }
  }

  // Do whatever is necessary to make this invoice re-billable.
  //
  function slSetupSecondary($invid, $debug) {
    global $sl_err, $GLOBALS;
    $info_msg = '';

    // Get some needed items from the SQL-Ledger invoice.
    $arres = SLQuery("select invnumber, transdate, customer_id, employee_id, " .
      "shipvia from ar where ar.id = $invid");
    if ($sl_err) die($sl_err);
    $arrow = SLGetRow($arres, 0);
    if (! $arrow) die(xl('There is no match for invoice id') . ' = ' . "$trans_id.");
    $customer_id = $arrow['customer_id'];
    list($trash, $encounter) = explode(".", $arrow['invnumber']);

    // Get the OpenEMR PID corresponding to the customer.
    $pdrow = sqlQuery("SELECT patient_data.pid " .
      "FROM integration_mapping, patient_data WHERE " .
      "integration_mapping.foreign_id = $customer_id AND " .
      "integration_mapping.foreign_table = 'customer' AND " .
      "patient_data.id = integration_mapping.local_id");
    $pid = $pdrow['pid'];
    if (! $pid) die(xl("Cannot find patient from SQL-Ledger customer id") . " = $customer_id.");

    // Determine the ID of the next insurance company (if any) to be billed.
    $new_payer_id = -1;
    $insdone = strtolower($arrow['shipvia']);
    foreach (array('ins1' => 'primary', 'ins2' => 'secondary', 'ins3' => 'tertiary') as $key => $value) {
      if (strpos($insdone, $key) === false) {
        $nprow = sqlQuery("SELECT provider FROM insurance_data WHERE " .
          "pid = '$pid' AND type = '$value'");
        if ($nprow['provider']) {
          $new_payer_id = $nprow['provider'];
        }
        break;
      }
    }

    // Find out if the encounter exists.
    $ferow = sqlQuery("SELECT pid FROM form_encounter WHERE " .
      "encounter = $encounter");
    $encounter_pid = $ferow['pid'];

    // If it exists, just update the billing items.
    if ($encounter_pid) {
      if ($encounter_pid != $pid)
        die(xl("Expected form_encounter.pid to be ") . $pid . ', ' . xl(' but was ') . $encounter_pid);

      // If there's a payer ID queue it up, otherwise just reopen it.
      if ($new_payer_id > 0) {
        // TBD: implement a default bill_process and target in config.php,
        // it should not really be hard-coded here.
        $query = "UPDATE billing SET billed = 0, bill_process = 5, " .
          "target = 'hcfa', payer_id = $new_payer_id, " .
          "bill_date = NOW(), process_date = NULL, process_file = NULL " .
          "WHERE encounter = $encounter AND pid = $pid AND activity = 1";
      } else {
        $query = "UPDATE billing SET billed = 0, bill_process = 0, payer_id = -1, " .
          "bill_date = NULL, process_date = NULL, process_file = NULL " .
          "WHERE encounter = $encounter AND pid = $pid AND activity = 1";
      }

      if ($debug) {
        echo $query . "<br>\n";
      } else {
        sqlQuery($query);
      }
      $info_msg = xl("Encounter ") . $encounter . xl(" is ready for re-billing.");
      return;
    }

    // If we get here then the encounter does not already exist.  This should
    // only happen if A/R was converted from an earlier system.  In this case
    // the encounter ID should be the date of service, and we will create the
    // encounter.

    // If it does not exist then it better be (or start with) a date.
    if (! preg_match("/^20\d\d\d\d\d\d/", $encounter))
      die(xl("Internal error: encounter '") . $encounter . xl("' should exist but does not."));

    $employee_id = $arrow['employee_id'];

    // Get the OpenEMR provider info corresponding to the SQL-Ledger salesman.
    $drrow = sqlQuery("SELECT users.id, users.username, users.facility " .
      "FROM integration_mapping, users WHERE " .
      "integration_mapping.foreign_id = $employee_id AND " .
      "integration_mapping.foreign_table = 'salesman' AND " .
      "users.id = integration_mapping.local_id");
    $provider_id = $drrow['id'];
    if (! $provider_id) die(xl("Cannot find provider from SQL-Ledger employee = ") . $employee_id );

    $date_of_service = $arrow['transdate'];
    if (! $date_of_service) die(xl("Invoice has no date!"));

    // Generate a new encounter number.
    $conn = $GLOBALS['adodb']['db'];
    $new_encounter = $conn->GenID("sequences");

    // Create the "new encounter".
    $encounter_id = 0;
    $query = "INSERT INTO form_encounter ( " .
      "date, reason, facility, pid, encounter, onset_date " .
      ") VALUES ( " .
      "'$date_of_service', " .
      "'" . xl('Imported from Accounting') . "', " .
      "'" . addslashes($drrow['facility']) . "', " .
      "$pid, " .
      "$new_encounter, " .
      "'$date_of_service' " .
      ")";
    if ($debug) {
      echo $query . "<br>\n";
      echo xl("Call to addForm() goes here.<br>") . "\n";
    } else {
      $encounter_id = idSqlStatement($query);
      if (! $encounter_id) die(xl("Insert failed: ") . $query);
      addForm($new_encounter, xl("New Patient Encounter"), $encounter_id,
        "newpatient", $pid, 1, $date_of_service);
      $info_msg = xl("Encounter ") . $new_encounter . xl(" has been created. ");
    }

    // For each invoice line item with a billing code we will insert
    // a billing row with payer_id set to -1.  Order the line items
    // chronologically so that each procedure code will be followed by
    // its associated icd9 code.

    $inres = SLQuery("SELECT * FROM invoice WHERE trans_id = $invid " .
      "ORDER BY id");
    if ($sl_err) die($sl_err);

    // When nonzero, this will be the ID of a billing row that needs to
    // have its justify field set.
    $proc_ins_id = 0;

    for ($irow = 0; $irow < SLRowCount($inres); ++$irow) {
      $row = SLGetRow($inres, $irow);
      $amount   = $row['sellprice'];

      // Extract the billing code.
      $code = xl("Unknown");
      if (preg_match("/([A-Za-z0-9]\d\d\S*)/", $row['serialnumber'], $matches)) {
        $code = strtoupper($matches[1]);
      }
      else if (preg_match("/([A-Za-z0-9]\d\d\S*)/", $row['description'], $matches)) {
        $code = strtoupper($matches[1]);
      }

      list($code, $modifier) = explode("-", $code);

      // Set the billing code type and description.
      $code_type = "";
      $code_text = "";

      foreach ($code_types as $key => $value) {
        if (preg_match("/$key/", $row['serialnumber'])) {
          $code_type = $key;
          if ($value['fee']) {
            $code_text = xl("Procedure") . " $code";
          } else {
            $code_text = xl("Diagnosis") . " $code";
            if ($proc_ins_id) {
              $query = "UPDATE billing SET justify = '$code' WHERE id = $proc_ins_id";
              if ($debug) {
                echo $query . "<br>\n";
              } else {
                sqlQuery($query);
              }
              $proc_ins_id = 0;
            }
          }
          break;
        }
      }

      // Skip adjustments.
      if (! $code_type) continue;

      // Insert the billing item.  If this for a procedure code then save
      // the row ID so that we can update the "justify" field with the ICD9
      // code, which should come next in the loop.
      //
      $query = "INSERT INTO billing ( " .
        "date, code_type, code, pid, provider_id, user, groupname, authorized, " .
        "encounter, code_text, activity, payer_id, billed, bill_process, " .
        "bill_date, modifier, units, fee, justify, target " .
        ") VALUES ( " .
        "NOW(), " .
        "'$code_type', " .
        "'$code', " .
        "$pid, " .
        "$provider_id, " .
        "'" . $_SESSION['authId'] . "', " .
        "'" . $_SESSION['authProvider'] . "', " .
        "1, " .
        "$new_encounter, " .
        "'$code_text', " .
        "1, " .
        "$new_payer_id, " .
        ($new_payer_id > 0 ? "1, " : "0, ") .
        ($new_payer_id > 0 ? "5, " : "0, ") .
        ($new_payer_id > 0 ? "NOW(), " : "NULL, ") .
        "'$modifier', " .
        "0, " .
        "$amount, " .
        "'', " .
        ($new_payer_id > 0 ? "'hcfa' " : "NULL ") .
        ")";
      if ($debug) {
        echo $query . "<br>\n";
      } else {
        $proc_ins_id = idSqlStatement($query);
        if ($code_type != "CPT4" && $code_type != "HCPCS")
          $proc_ins_id = 0;
      }
    }

    // Finally, change this invoice number to contain the new encounter number.
    //
    $new_invnumber = "$pid.$new_encounter";
    $query = "UPDATE ar SET invnumber = '$new_invnumber' WHERE id = $invid";
    if ($debug) {
      echo $query . "<br>\n";
    } else {
      SLQuery($query);
      if ($sl_err) die($sl_err);
      $info_msg .= xl("This invoice number has been changed to ") . $new_invnumber;
    }

    return $info_msg;
  }
?>