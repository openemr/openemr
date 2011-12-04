<?php

// require_once("{$GLOBALS['srcdir']}/sql.inc");
require_once(dirname(__FILE__) . "/sql.inc");

function getBillingById ($id, $cols = "*")
{
    return sqlQuery("select $cols from billing where id='$id' and activity=1 order by date DESC limit 0,1");
}

function getBillingByPid ($pid, $cols = "*")
{
    return  sqlQuery("select $cols from billing where pid ='$pid' and activity=1 order by date DESC limit 0,1");
}

function getBillingByEncounter ($pid,$encounter, $cols = "code_type, code, code_text")
{
    $res = sqlStatement("select $cols from billing where encounter = ? and pid=? and activity=1 order by code_type, date ASC", array($encounter,$pid) );

    for($iter=0; $row=sqlFetchArray($res); $iter++)
    {
        $all[$iter] = $row;
    }
    return $all;
}

function addBilling($encounter_id, $code_type, $code, $code_text, $pid,
  $authorized="0", $provider, $modifier="", $units="", $fee="0.00",
  $ndc_info='', $justify='', $billed=0, $notecodes='')
{
  $sql = "insert into billing (date, encounter, code_type, code, code_text, " .
    "pid, authorized, user, groupname, activity, billed, provider_id, " .
    "modifier, units, fee, ndc_info, justify, notecodes) values (" .
    "NOW(), '$encounter_id', '$code_type', '$code', '$code_text', '$pid', " .
    "'$authorized', '" . $_SESSION['authId'] . "', '" .
    $_SESSION['authProvider'] . "', 1, $billed, $provider, '$modifier', '$units', " .
    "'$fee', '$ndc_info', '$justify', '$notecodes')";
  return sqlInsert($sql);
}

function authorizeBilling($id, $authorized = "1")
{
    sqlQuery("update billing set authorized = '$authorized' where id = '$id'");
}

function deleteBilling($id)
{
    sqlStatement("update billing set activity = 0 where id = '$id'");
}

function clearBilling($id)
{
    sqlStatement("update billing set justify = '' where id = '$id'");
}

// This function supports the Billing page (billing_process.php), freeb
// processing (process_bills.php), and initiation of secondary processing
// (sl_eob.inc.php).  It is called in the following situations:
//
// * billing_process.php sets bill_time, bill_process, payer and target on
//   queueing a claim for freeb processing.  Create claims row.
// * billing_process.php sets claim status to 2, and payer, on marking a
//   claim as billed without actually generating any billing.  Create a
//   claims row.  In this case bill_process will remain at 0 and process_time
//   and process_file will not be set.
// * billing_process.php sets bill_process, payer, target and x12 partner
//   before calling gen_x12_837.  Create a claims row.
// * billing_process.php sets claim status to 2 (billed), bill_process to 2,
//   process_time and process_file after calling gen_x12_837.  Claims row
//   already exists.
// * process_bills.php sets bill_process to 2, process_time, and process_file
//   after invoking freeb to process a claim.  Claims row already exists.
// * billing_process.php sets claim status to 2 (billed) after creating
//   an electronic freeb batch (hcfa-only with recent changes).  Claims
//   row already exists.
// * EOB posting updates claim status to mark a payer as done.  Claims row
//   already exists.
// * EOB posting reopens an encounter for billing a secondary payer.  Create
//   a claims row.
//
// $newversion should be passed to us to indicate if a new claims row
// is to be generated, otherwise one must already exist.  The payer, if
// passed in for the latter case, must match the existing claim.
//
// Currently on the billing page the user can select any of the patient's
// payers.  That logic will tailor the payer choices to the encounter date.
//
function updateClaim($newversion, $patient_id, $encounter_id, $payer_id=-1, $payer_type=-1,
  $status=-1, $bill_process=-1, $process_file='', $target='', $partner_id=-1,$crossover=0)
{
  if (!$newversion) {
    $sql = "SELECT * FROM claims WHERE patient_id = '$patient_id' AND " .
      "encounter_id = '$encounter_id' AND status > 0 AND status < 4 ";
    if ($payer_id >= 0) $sql .= "AND payer_id = '$payer_id' ";
    $sql .= "ORDER BY version DESC LIMIT 1";
    $row = sqlQuery($sql);
    if (!$row) return 0;
    if ($payer_id     < 0) $payer_id     = $row['payer_id'];
    if ($status       < 0) $status       = $row['status'];
    if ($bill_process < 0) $bill_process = $row['bill_process'];
    if ($partner_id   < 0) $partner_id   = $row['x12_partner_id'];
    if (!$process_file   ) $process_file = $row['process_file'];
    if (!$target         ) $target       = $row['target'];
  }

  $claimset = "";
  $billset = "";
  if (empty($payer_id) || $payer_id < 0) $payer_id = 0;

  if ($status==7) {//$status==7 is the claim denial case.
    $claimset .= ", status = '$status'";
  }
  elseif ($status >= 0) {
    $claimset .= ", status = '$status'";
    if ($status > 1) {
      $billset .= ", billed = 1";
      if ($status == 2) $billset  .= ", bill_date = NOW()";
    } else {
      $billset .= ", billed = 0";
    }
  }
  if ($status==7) {//$status==7 is the claim denial case.
    $billset  .= ", bill_process = '$status'";
  }
  elseif ($bill_process >= 0) {
    $claimset .= ", bill_process = '$bill_process'";
    $billset  .= ", bill_process = '$bill_process'";
  }
  if ($status==7) {//$status==7 is the claim denial case.
    $claimset  .= ", process_file = '$process_file'";//Denial reason code is stored here
  }
  elseif ($process_file) {
    $claimset .= ", process_file = '$process_file', process_time = NOW()";
    $billset  .= ", process_file = '$process_file', process_date = NOW()";
  }
  if ($target) {
    $claimset .= ", target = '$target'";
    $billset  .= ", target = '$target'";
  }
  if ($payer_id >= 0) {
    $claimset .= ", payer_id = '$payer_id', payer_type = '$payer_type'";
    $billset  .= ", payer_id = '$payer_id'";
  }
  if ($partner_id >= 0) {
    $claimset .= ", x12_partner_id = '$partner_id'";
    $billset  .= ", x12_partner_id = '$partner_id'";
  }

  if ($billset) {
    $billset = substr($billset, 2);
    sqlStatement("UPDATE billing SET $billset WHERE " .
      "encounter = '$encounter_id' AND pid='$patient_id' AND activity = 1");
  }

  // If a new claim version is requested, insert its row.
  //
  if ($newversion) {
    /****
    $payer_id = ($payer_id < 0) ? $row['payer_id'] : $payer_id;
    $bill_process = ($bill_process < 0) ? $row['bill_process'] : $bill_process;
    $process_file = ($process_file) ? $row['process_file'] : $process_file;
    $target = ($target) ? $row['target'] : $target;
    $partner_id = ($partner_id < 0) ? $row['x12_partner_id'] : $partner_id;
    $sql = "INSERT INTO claims SET " .
      "patient_id = '$patient_id', " .
      "encounter_id = '$encounter_id', " .
      "bill_time = UNIX_TIMESTAMP(NOW()), " .
      "payer_id = '$payer_id', " .
      "status = '$status', " .
      "payer_type = '" . $row['payer_type'] . "', " .
      "bill_process = '$bill_process', " .
      "process_time = '" . $row['process_time'] . "', " .
      "process_file = '$process_file', " .
      "target = '$target', " .
      "x12_partner_id = '$partner_id'";
    ****/
    if($crossover<>1)
    {
    $sql = "INSERT INTO claims SET " .
      "patient_id = '$patient_id', " .
      "encounter_id = '$encounter_id', " .
      "bill_time = NOW() $claimset";
      }
     else
     {//Claim automatic forward case.
     
     $sql = "INSERT INTO claims SET " .
      "patient_id = '$patient_id', " .
      "encounter_id = '$encounter_id', " .
      "bill_time = NOW(), status=$status";
     
     }
    
    sqlStatement($sql);
  }

  // Otherwise update the existing claim row.
  //
  else if ($claimset) {
    $claimset = substr($claimset, 2);
    sqlStatement("UPDATE claims SET $claimset WHERE " .
      "patient_id = '$patient_id' AND encounter_id = '$encounter_id' AND " .
      // "payer_id = '" . $row['payer_id'] . "' AND " .
      "version = '" . $row['version'] . "'");
  }

  // Whenever a claim is marked billed, update A/R accordingly.
  //
  if ($status == 2) {
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) {
      if ($payer_type > 0) {
        sqlStatement("UPDATE form_encounter SET " .
          "last_level_billed = '$payer_type' WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id'");
      }
    }
    else {
      $ws = new WSClaim($patient_id, $encounter_id);
    }
  }

  return 1;
}

// Determine if anything in a visit has been billed.
//
function isEncounterBilled($pid, $encounter) {
  $row = sqlQuery("SELECT count(*) AS count FROM billing WHERE " .
    "pid = '$pid' AND encounter = '$encounter' AND activity = 1 AND " .
    "billed = 1");
  $count = $row['count'];
  if (!$count) {
    $row = sqlQuery("SELECT count(*) AS count FROM drug_sales WHERE " .
      "pid = '$pid' AND encounter = '$encounter' AND billed = 1");
    $count = $row['count'];
  }
  return $count ? true : false;
}
?>
