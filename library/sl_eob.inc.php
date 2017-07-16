<?php
  // Copyright (C) 2005-2009 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  include_once("patient.inc");
  include_once("billing.inc");
  include_once("invoice_summary.inc.php");

  $chart_id_cash   = 0;
  $chart_id_ar     = 0;
  $chart_id_income = 0;
  $services_id     = 0;


  // Try to figure out our invoice number (pid.encounter) from the
  // claim ID and other stuff in the ERA.  This should be straightforward
  // except that some payers mangle the claim ID that we give them.
  //
function slInvoiceNumber(&$out)
{
    $invnumber = $out['our_claim_id'];
    $atmp = preg_split('/[ -]/', $invnumber);
    $acount = count($atmp);

    $pid = 0;
    $encounter = 0;
    if ($acount == 2) {
        $pid = $atmp[0];
        $encounter = $atmp[1];
    } else if ($acount == 3) {
        $pid = $atmp[0];
        $brow = sqlQuery("SELECT encounter FROM billing WHERE " .
        "pid = '$pid' AND encounter = '" . $atmp[1] . "' AND activity = 1");
        
        $encounter = $brow['encounter'];
    } else if ($acount == 1) {
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

    if ($pid && $encounter) {
        $invnumber = "$pid.$encounter";
    }

    return array($pid, $encounter, $invnumber);
}

  // This gets a posting session ID.  If the payer ID is not 0 and a matching
  // session already exists, then its ID is returned.  Otherwise a new session
  // is created.
  //
function arGetSession($payer_id, $reference, $check_date, $deposit_date = '', $pay_total = 0)
{
    if (empty($deposit_date)) {
        $deposit_date = $check_date;
    }

    if ($payer_id) {
        $row = sqlQuery("SELECT session_id FROM ar_session WHERE " .
        "payer_id = '$payer_id' AND reference = '$reference' AND " .
        "check_date = '$check_date' AND deposit_date = '$deposit_date' " .
        "ORDER BY session_id DESC LIMIT 1");
        if (!empty($row['session_id'])) {
            return $row['session_id'];
        }
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
  //writing the check details to Session Table on ERA proxcessing
function arPostSession($payer_id, $check_number, $check_date, $pay_total, $post_to_date, $deposit_date, $debug)
{
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
function arPostPayment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $memo, $debug, $time = '', $codetype = '')
{
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
        $codeonly = substr($code, 0, $tmp);
        $modifier = substr($code, $tmp+1);
    }

    if (empty($time)) {
        $time = date('Y-m-d H:i:s');
    }

    sqlBeginTrans();
    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($patient_id, $encounter_id));
    $query = "INSERT INTO ar_activity ( " .
    "pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user, " .
    "session_id, memo, pay_amount " .
    ") VALUES ( " .
    "'$patient_id', " .
    "'$encounter_id', " .
    "'{$sequence_no['increment']}', " .
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
    sqlCommitTrans();
    return;
}

  // Post a charge.  This is called only from sl_eob_process.php where
  // automated remittance processing can create a new service item.
  // Here we add it as an unauthorized item to the billing table.
  //
function arPostCharge($patient_id, $encounter_id, $session_id, $amount, $units, $thisdate, $code, $description, $debug, $codetype = '')
{
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

    addBilling(
        $encounter_id,
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
        ''
    );
}

  // Post an adjustment, new style.
  //
function arPostAdjustment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $reason, $debug, $time = '', $codetype = '')
{
    $codeonly = $code;
    $modifier = '';
    $tmp = strpos($code, ':');
    if ($tmp) {
        $codeonly = substr($code, 0, $tmp);
        $modifier = substr($code, $tmp+1);
    }

    if (empty($time)) {
        $time = date('Y-m-d H:i:s');
    }

    sqlBeginTrans();
    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($patient_id, $encounter_id));
    $query = "INSERT INTO ar_activity ( " .
    "pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_user, post_time, " .
    "session_id, memo, adj_amount " .
    ") VALUES ( " .
    "'$patient_id', " .
    "'$encounter_id', " .
    "'{$sequence_no['increment']}', " .
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
    sqlCommitTrans();
    return;
}

function arGetPayerID($patient_id, $date_of_service, $payer_type)
{
    if ($payer_type < 1 || $payer_type > 3) {
        return 0;
    }

    $tmp = array(1 => 'primary', 2 => 'secondary', 3 => 'tertiary');
    $value = $tmp[$payer_type];
    $query = "SELECT provider FROM insurance_data WHERE " .
    "pid = ? AND type = ? AND date <= ? " .
    "ORDER BY date DESC LIMIT 1";
    $nprow = sqlQuery($query, array($patient_id,$value,$date_of_service));
    if (empty($nprow)) {
        return 0;
    }

    return $nprow['provider'];
}

  // Make this invoice re-billable, new style.
  //
function arSetupSecondary($patient_id, $encounter_id, $debug, $crossover = 0)
{
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
    if ($new_payer_type < 3 && !empty($ferow['last_level_billed']) || $new_payer_type == 0) {
        ++$new_payer_type;
    }

    $new_payer_id = arGetPayerID($patient_id, $date_of_service, $new_payer_type);

    if ($new_payer_id) {
        // Queue up the claim.
        if (!$debug) {
            updateClaim(true, $patient_id, $encounter_id, $new_payer_id, $new_payer_type, $status, 5, '', 'hcfa', '', $crossover);
        }
    } else {
      // Just reopen the claim.
        if (!$debug) {
            updateClaim(true, $patient_id, $encounter_id, -1, -1, $status, 0, '', '', '', $crossover);
        }
    }

    return xl("Encounter ") . $encounter . xl(" is ready for re-billing.");
}
