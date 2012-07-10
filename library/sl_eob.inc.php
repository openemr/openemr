<?php
  // Copyright (C) 2005-2009 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  include_once("patient.inc");
  include_once("billing.inc");

  if ($GLOBALS['oer_config']['ws_accounting']['enabled'] !== 2) {
    include_once("sql-ledger.inc");
    include_once("invoice_summary.inc.php");
  }

  $chart_id_cash   = 0;
  $chart_id_ar     = 0;
  $chart_id_income = 0;
  $services_id     = 0;

  function slInitialize() {
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) return;

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
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) return;
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
        "pid = '$pid' AND encounter = '" . $atmp[1] . "' AND activity = 1");
        
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
  // This should never be called if SQL-Ledger is not used.
  //
  function slAddTransaction($invid, $chartid, $amount, $date, $source, $memo, $insplan, $debug) {
    global $sl_err;
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2)
      die("Internal error calling slAddTransaction()");
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
  // This should never be called if SQL-Ledger is not used.
  //
  function slAddLineItem($invid, $serialnumber, $amount, $units, $insplan, $description, $debug) {
    global $sl_err, $services_id;
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2)
      die("Internal error calling slAddLineItem()");
    $units = max(1, intval($units));
    $price = $amount / $units;
    $tmp = sprintf("%01.2f", $price);
    if (abs($price - $tmp) < 0.000001) $price = $tmp;
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
      "$units, "            . // qty
      "0, "                 . // allocated
      "$price, "            . // sellprice
      "$price, "            . // fxsellprice
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
  // This should never be called if SQL-Ledger is not used.
  //
  function slUpdateAR($invid, $amount, $paid = 0, $paydate = "", $debug) {
    global $sl_err;
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2)
      die("Internal error calling slUpdateAR()");
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

  // This gets a posting session ID.  If the payer ID is not 0 and a matching
  // session already exists, then its ID is returned.  Otherwise a new session
  // is created.
  //
  function arGetSession($payer_id, $reference, $check_date, $deposit_date='', $pay_total=0) {
    if (empty($deposit_date)) $deposit_date = $check_date;
    if ($payer_id) {
      $row = sqlQuery("SELECT session_id FROM ar_session WHERE " .
        "payer_id = '$payer_id' AND reference = '$reference' AND " .
        "check_date = '$check_date' AND deposit_date = '$deposit_date' " .
        "ORDER BY session_id DESC LIMIT 1");
      if (!empty($row['session_id'])) return $row['session_id'];
    }
    return sqlInsert("INSERT INTO ar_session ( " .
      "payer_id, user_id, reference, check_date, deposit_date, pay_total " .
      ") VALUES ( " .
      "'$payer_id', " .
      "'" . $_SESSION['authUserID'] . "', " .
      "'$reference', " .
      "'$check_date', " .
      "'$deposit_date', " .
      "'$pay_total' " .
      ")");
  }

  // Post a payment, SQL-Ledger style.
  //
  function slPostPayment($trans_id, $thispay, $thisdate, $thissrc, $code, $thisins, $debug) {
    global $chart_id_cash, $chart_id_ar;
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2)
      die("Internal error calling slPostPayment()");
    // Post a payment: add to ar, subtract from cash.
    slAddTransaction($trans_id, $chart_id_ar  , $thispay    , $thisdate, $thissrc, $code, $thisins, $debug);
    slAddTransaction($trans_id, $chart_id_cash, 0 - $thispay, $thisdate, $thissrc, $code, $thisins, $debug);
    slUpdateAR($trans_id, 0, $thispay, $thisdate, $debug);
  }
  //writing the check details to Session Table on ERA proxcessing
function arPostSession($payer_id,$check_number,$check_date,$pay_total,$post_to_date,$deposit_date,$debug) {
      $query = "INSERT INTO ar_session( " .
      "payer_id,user_id,closed,reference,check_date,pay_total,post_to_date,deposit_date,patient_id,payment_type,adjustment_code,payment_method " .
      ") VALUES ( " .
      "'$payer_id'," .
      $_SESSION['authUserID']."," .
      "0," .
      "'ePay - $check_number'," .
      "'$check_date', " .
      "$pay_total, " .
      "'$post_to_date','$deposit_date', " .
      "0,'insurance','insurance_payment','electronic'" .
        ")";
    if ($debug) {
      echo $query . "<br>\n";
    } else {
     $sessionId=sqlInsert($query);
    return $sessionId;
    }
  }
  
  // Post a payment, new style.
  //
  function arPostPayment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $memo, $debug, $time='', $codetype='') {
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
      $codeonly = substr($code, 0, $tmp);
      $modifier = substr($code, $tmp+1);
    }
    if (empty($time)) $time = date('Y-m-d H:i:s');
    $query = "INSERT INTO ar_activity ( " .
      "pid, encounter, code_type, code, modifier, payer_type, post_time, post_user, " .
      "session_id, memo, pay_amount " .
      ") VALUES ( " .
      "'$patient_id', " .
      "'$encounter_id', " .
      "'$codetype', " .
      "'$codeonly', " .
      "'$modifier', " .
      "'$payer_type', " .
      "'$time', " .
      "'" . $_SESSION['authUserID'] . "', " .
      "'$session_id', " .
      "'$memo', " .
      "'$amount' " .
      ")";
    sqlStatement($query);
    return;
  }

  // Post a charge.  This is called only from sl_eob_process.php where
  // automated remittance processing can create a new service item.
  // Here we add it as an unauthorized item to the billing table.
  //
  function arPostCharge($patient_id, $encounter_id, $session_id, $amount, $units, $thisdate, $code, $description, $debug, $codetype='') {
    /*****************************************************************
    // Select an existing billing item as a template.
    $row= sqlQuery("SELECT * FROM billing WHERE " .
      "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
      "code_type = 'CPT4' AND activity = 1 " .
      "ORDER BY id DESC LIMIT 1");
    $this_authorized = 0;
    $this_provider = 0;
    if (!empty($row)) {
      $this_authorized = $row['authorized'];
      $this_provider = $row['provider_id'];
    }
    *****************************************************************/

    if (empty($codetype)) {
      // default to CPT4 if empty, which is consistent with previous functionality.
      $codetype="CPT4";
    }
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
      $codeonly = substr($code, 0, $tmp);
      $modifier = substr($code, $tmp+1);
    }

    addBilling($encounter_id,
      $codetype,
      $codeonly,
      $description,
      $patient_id,
      0,
      0,
      $modifier,
      $units,
      $amount,
      '',
      '');
  }

  // See comments above.
  // In the SQL-Ledger case this service item is added only to SL and
  // not to the billing table.
  //
  function slPostCharge($trans_id, $thisamt, $thisunits, $thisdate, $code, $thisins, $description, $debug) {
    global $chart_id_income, $chart_id_ar;
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2)
      die("Internal error calling slPostCharge()");
    // Post an adjustment: add negative invoice item, add to ar, subtract from income
    slAddLineItem($trans_id, $code, $thisamt, $thisunits, $thisins, $description, $debug);
    if ($thisamt) {
      slAddTransaction($trans_id, $chart_id_ar    , 0 - $thisamt, $thisdate, $description, $code, $thisins, $debug);
      slAddTransaction($trans_id, $chart_id_income, $thisamt    , $thisdate, $description, $code, $thisins, $debug);
      slUpdateAR($trans_id, $thisamt, 0, '', $debug);
    }
  }

  // Post an adjustment, SQL-Ledger style.
  //
  function slPostAdjustment($trans_id, $thisadj, $thisdate, $thissrc, $code, $thisins, $reason, $debug) {
    global $chart_id_income, $chart_id_ar;
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2)
      die("Internal error calling slPostAdjustment()");
    // Post an adjustment: add negative invoice item, add to ar, subtract from income
    $adjdate = fixDate($thisdate);
    $description = "Adjustment $adjdate $reason";
    slAddLineItem($trans_id, $code, 0 - $thisadj, 1, $thisins, $description, $debug);
    if ($thisadj) {
      slAddTransaction($trans_id, $chart_id_ar, $thisadj, $thisdate, "InvAdj $thissrc", $code, $thisins, $debug);
      slAddTransaction($trans_id, $chart_id_income, 0 - $thisadj, $thisdate, "InvAdj $thissrc", $code, $thisins, $debug);
      slUpdateAR($trans_id, 0 - $thisadj, 0, '', $debug);
    }
  }

  // Post an adjustment, new style.
  //
  function arPostAdjustment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $reason, $debug, $time='', $codetype='') {
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
      $codeonly = substr($code, 0, $tmp);
      $modifier = substr($code, $tmp+1);
    }
    if (empty($time)) $time = date('Y-m-d H:i:s');
    $query = "INSERT INTO ar_activity ( " .
      "pid, encounter, code_type, code, modifier, payer_type, post_user, post_time, " .
      "session_id, memo, adj_amount " .
      ") VALUES ( " .
      "'$patient_id', " .
      "'$encounter_id', " .
      "'$codetype', " .
      "'$codeonly', " .
      "'$modifier', " .
      "'$payer_type', " .
      "'" . $_SESSION['authUserID'] . "', " .
      "'$time', " .
      "'$session_id', " .
      "'$reason', " .
      "'$amount' " .
      ")";
    sqlStatement($query);
    return;
  }

  function arGetPayerID($patient_id, $date_of_service, $payer_type) {
    if ($payer_type < 1 || $payer_type > 3) return 0;
    $tmp = array(1 => 'primary', 2 => 'secondary', 3 => 'tertiary');
    $value = $tmp[$payer_type];
    $query = "SELECT provider FROM insurance_data WHERE " .
      "pid = ? AND type = ? AND date <= ? " .
      "ORDER BY date DESC LIMIT 1";
    $nprow = sqlQuery($query, array($patient_id,$value,$date_of_service) );
    if (empty($nprow)) return 0;
    return $nprow['provider'];
  }

  // Make this invoice re-billable, new style.
  //
  function arSetupSecondary($patient_id, $encounter_id, $debug,$crossover=0) {
    if ($crossover==1) {
    //if claim forwarded setting a new status 
    $status=6;
    
    } else {
    
    $status=1;
    
    }
    // Determine the next insurance level to be billed.
    $ferow = sqlQuery("SELECT date, last_level_billed " .
      "FROM form_encounter WHERE " .
      "pid = '$patient_id' AND encounter = '$encounter_id'");
    $date_of_service = substr($ferow['date'], 0, 10);
    $new_payer_type = 0 + $ferow['last_level_billed'];
    if ($new_payer_type < 3 && !empty($ferow['last_level_billed']) || $new_payer_type == 0)
      ++$new_payer_type;

    $new_payer_id = arGetPayerID($patient_id, $date_of_service, $new_payer_type);

    if ($new_payer_id) {
      // Queue up the claim.
      if (!$debug)
        updateClaim(true, $patient_id, $encounter_id, $new_payer_id, $new_payer_type,$status, 5, '', 'hcfa','',$crossover);
    }
    else {
      // Just reopen the claim.
      if (!$debug)
        updateClaim(true, $patient_id, $encounter_id, -1, -1, $status, 0, '','','',$crossover);
    }

    return xl("Encounter ") . $encounter . xl(" is ready for re-billing.");
  }

  // Make this invoice re-billable, SQL-Ledger style.
  //
  function slSetupSecondary($invid, $debug) {
    global $sl_err, $GLOBALS, $code_types;

    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2)
      die("Internal error calling slSetupSecondary()");

    $info_msg = '';

    // Get some needed items from the SQL-Ledger invoice.
    $arres = SLQuery("select invnumber, transdate, customer_id, employee_id, " .
      "shipvia from ar where ar.id = $invid");
    if ($sl_err) die($sl_err);
    $arrow = SLGetRow($arres, 0);
    if (! $arrow) die(xl('There is no match for invoice id') . ' = ' . "$trans_id.");
    $customer_id = $arrow['customer_id'];
    $date_of_service = $arrow['transdate'];
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
    $new_payer_type = -1;
    $insdone = strtolower($arrow['shipvia']);
    foreach (array('ins1' => 'primary', 'ins2' => 'secondary', 'ins3' => 'tertiary') as $key => $value) {
      if (strpos($insdone, $key) === false) {
        $nprow = sqlQuery("SELECT provider FROM insurance_data WHERE " .
          "pid = '$pid' AND type = '$value' AND date <= '$date_of_service' " .
          "ORDER BY date DESC LIMIT 1");
        if (!empty($nprow['provider'])) {
          $new_payer_id = $nprow['provider'];
          $new_payer_type = substr($key, 3);
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
        if (!$debug)
          updateClaim(true, $pid, $encounter, $new_payer_id, $new_payer_type, 1, 5, '', 'hcfa');
      } else {
        if (!$debug)
          updateClaim(true, $pid, $encounter, -1, -1, 1, 0, '');
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
    $drrow = sqlQuery("SELECT users.id, users.username, users.facility_id " .
      "FROM integration_mapping, users WHERE " .
      "integration_mapping.foreign_id = $employee_id AND " .
      "integration_mapping.foreign_table = 'salesman' AND " .
      "users.id = integration_mapping.local_id");
    $provider_id = $drrow['id'];
    if (! $provider_id) die(xl("Cannot find provider from SQL-Ledger employee = ") . $employee_id );

    if (! $date_of_service) die(xl("Invoice has no date!"));

    // Generate a new encounter number.
    $conn = $GLOBALS['adodb']['db'];
    $new_encounter = $conn->GenID("sequences");

    // Create the "new encounter".
    $encounter_id = 0;
    $query = "INSERT INTO form_encounter ( " .
      "date, reason, facility_id, pid, encounter, onset_date, provider_id " .
      ") VALUES ( " .
      "'$date_of_service', " .
      "'" . xl('Imported from Accounting') . "', " .
      "'" . addslashes($drrow['facility_id']) . "', " .
      "$pid, " .
      "$new_encounter, " .
      "'$date_of_service', " .
      "'$provider_id' " .
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
      $amount = sprintf('%01.2f', $row['sellprice'] * $row['qty']);

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
          if (!$value['diag']) {
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
        "0, " . // was $provider_id but that is now in form_encounter
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
        if ($code_types[$code_type]['diag'])
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
