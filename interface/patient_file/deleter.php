<?php
 // Copyright (C) 2005-2011 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/log.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/sl_eob.inc.php");

 $patient     = $_REQUEST['patient'];
 $encounterid = $_REQUEST['encounterid'];
 $formid      = $_REQUEST['formid'];
 $issue       = $_REQUEST['issue'];
 $document    = $_REQUEST['document'];
 $payment     = $_REQUEST['payment'];
 $billing     = $_REQUEST['billing'];
 $transaction = $_REQUEST['transaction'];

 $info_msg = "";

 // Delete rows, with logging, for the specified table using the
 // specified WHERE clause.
 //
 function row_delete($table, $where) {
  $tres = sqlStatement("SELECT * FROM $table WHERE $where");
  $count = 0;
  while ($trow = sqlFetchArray($tres)) {
   $logstring = "";
   foreach ($trow as $key => $value) {
    if (! $value || $value == '0000-00-00 00:00:00') continue;
    if ($logstring) $logstring .= " ";
    $logstring .= $key . "='" . addslashes($value) . "'";
   }
   newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
   ++$count;
  }
  if ($count) {
   $query = "DELETE FROM $table WHERE $where";
   echo $query . "<br>\n";
   sqlStatement($query);
  }
 }

 // Deactivate rows, with logging, for the specified table using the
 // specified SET and WHERE clauses.
 //
 function row_modify($table, $set, $where) {
  if (sqlQuery("SELECT * FROM $table WHERE $where")) {
   newEvent("deactivate", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $where");
   $query = "UPDATE $table SET $set WHERE $where";
   echo $query . "<br>\n";
   sqlStatement($query);
  }
 }

// We use this to put dashes, colons, etc. back into a timestamp.
//
function decorateString($fmt, $str) {
  $res = '';
  while ($fmt) {
    $fc = substr($fmt, 0, 1);
    $fmt = substr($fmt, 1);
    if ($fc == '.') {
      $res .= substr($str, 0, 1);
      $str = substr($str, 1);
    } else {
      $res .= $fc;
    }
  }
  return $res;
}

// Delete and undo product sales for a given patient or visit.
// This is special because it has to replace the inventory.
//
function delete_drug_sales($patient_id, $encounter_id=0) {
  $where = $encounter_id ? "ds.encounter = '$encounter_id'" :
    "ds.pid = '$patient_id' AND ds.encounter != 0";
  sqlStatement("UPDATE drug_sales AS ds, drug_inventory AS di " .
    "SET di.on_hand = di.on_hand + ds.quantity " .
    "WHERE $where AND di.inventory_id = ds.inventory_id");
  if ($encounter_id) {
    row_delete("drug_sales", "encounter = '$encounter_id'");
  }
  else {
    row_delete("drug_sales", "pid = '$patient_id'");
  }
}

// Delete a form's data from its form-specific table.
//
function form_delete($formdir, $formid) {
  $formdir = ($formdir == 'newpatient') ? 'encounter' : $formdir;
  if (substr($formdir,0,3) == 'LBF')
    row_delete("lbf_data", "form_id = '$formid'");
  else
    row_delete("form_$formdir", "id = '$formid'");
}

// Delete a specified document including its associated relations and file.
//
function delete_document($document) {
  $trow = sqlQuery("SELECT url FROM documents WHERE id = '$document'");
  $url = $trow['url'];
  row_delete("categories_to_documents", "document_id = '$document'");
  row_delete("documents", "id = '$document'");
  row_delete("gprelations", "type1 = 1 AND id1 = '$document'");
  if (substr($url, 0, 7) == 'file://') {
    @unlink(substr($url, 7));
  }
}
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Delete Patient, Encounter, Form, Issue, Document, Payment, Billing or Transaction','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script language="javascript">
function submit_form()
{
document.deletefrm.submit();
}
</script>
</head>

<body class="body_top">
<?php
 // If the delete is confirmed...
 //
 if ($_POST['form_submit']) {

  if ($patient) {
   if (!acl_check('admin', 'super')) die("Not authorized!");
   row_modify("billing"       , "activity = 0", "pid = '$patient'");
   row_modify("pnotes"        , "deleted = 1" , "pid = '$patient'");
   // row_modify("prescriptions" , "active = 0"  , "patient_id = '$patient'");
   row_delete("prescriptions"  , "patient_id = '$patient'");
   row_delete("claims"         , "patient_id = '$patient'");
   delete_drug_sales($patient);
   row_delete("payments"       , "pid = '$patient'");
   row_delete("ar_activity"    , "pid = '$patient'");
   row_delete("openemr_postcalendar_events", "pc_pid = '$patient'");
   row_delete("immunizations"  , "patient_id = '$patient'");
   row_delete("issue_encounter", "pid = '$patient'");
   row_delete("lists"          , "pid = '$patient'");
   row_delete("transactions"   , "pid = '$patient'");
   row_delete("employer_data"  , "pid = '$patient'");
   row_delete("history_data"   , "pid = '$patient'");
   row_delete("insurance_data" , "pid = '$patient'");

   $res = sqlStatement("SELECT * FROM forms WHERE pid = '$patient'");
   while ($row = sqlFetchArray($res)) {
    form_delete($row['formdir'], $row['form_id']);
   }
   row_delete("forms", "pid = '$patient'");

   // integration_mapping is used for sql-ledger and is virtually obsolete now.
   $row = sqlQuery("SELECT id FROM patient_data WHERE pid = '$patient'");
   row_delete("integration_mapping", "local_table = 'patient_data' AND " .
    "local_id = '" . $row['id'] . "'");

   // Delete all documents for the patient.
   $res = sqlStatement("SELECT id FROM documents WHERE foreign_id = '$patient'");
   while ($row = sqlFetchArray($res)) {
    delete_document($row['id']);
   }

   // This table exists only for athletic teams.
   $tmp = sqlQuery("SHOW TABLES LIKE 'daily_fitness'");
   if (!empty($tmp)) {
    row_delete("daily_fitness", "pid = '$patient'");
   }

   row_delete("patient_data", "pid = '$patient'");
  }
  else if ($encounterid) {
   if (!acl_check('admin', 'super')) die("Not authorized!");
   row_modify("billing", "activity = 0", "encounter = '$encounterid'");
   delete_drug_sales(0, $encounterid);
   row_delete("ar_activity", "encounter = '$encounterid'");
   row_delete("claims", "encounter_id = '$encounterid'");
   row_delete("issue_encounter", "encounter = '$encounterid'");
   $res = sqlStatement("SELECT * FROM forms WHERE encounter = '$encounterid'");
   while ($row = sqlFetchArray($res)) {
    form_delete($row['formdir'], $row['form_id']);
   }
   row_delete("forms", "encounter = '$encounterid'");
  }
  else if ($formid) {
   if (!acl_check('admin', 'super')) die("Not authorized!");
   $row = sqlQuery("SELECT * FROM forms WHERE id = '$formid'");
   $formdir = $row['formdir'];
   if (! $formdir) die("There is no form with id '$formid'");
   form_delete($formdir, $row['form_id']);
   row_delete("forms", "id = '$formid'");
  }
  else if ($issue) {
   if (!acl_check('admin', 'super')) die("Not authorized!");
   row_delete("issue_encounter", "list_id = '$issue'");
   row_delete("lists", "id = '$issue'");
  }
  else if ($document) {
   if (!acl_check('admin', 'super')) die("Not authorized!");
   delete_document($document);
  }
  else if ($payment) {
   if (!acl_check('admin', 'super')) die("Not authorized!");
    list($patient_id, $timestamp) = explode(".", $payment);
    $timestamp = decorateString('....-..-.. ..:..:..', $timestamp);
    $payres = sqlStatement("SELECT * FROM payments WHERE " .
      "pid = '$patient_id' AND dtime = '$timestamp'");
    while ($payrow = sqlFetchArray($payres)) {
      if ($payrow['amount1'] != 0) {
        // Mark the payment as inactive.
        row_modify("billing", "activity = 0",
          "pid = '$patient_id' AND " .
          "encounter = '" . $payrow['encounter'] . "' AND " .
          "code_type = 'COPAY' AND " .
          "fee = '" . (0 - $payrow['amount1']) . "' AND " .
          "LEFT(date, 10) = '" . substr($timestamp, 0, 10) . "' AND " .
          "activity = 1 LIMIT 1");
      }
      if ($payrow['amount2'] != 0) {
        if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) {
          $thissrc = '';
          if ($payrow['method']) {
            $thissrc .= $payrow['method'];
            if ($payrow['source']) $thissrc .= ' ' . $payrow['source'];
          }
          $thissrc .= ' front office reversal';
          $session_id = 0; // Is this OK?
          arPostPayment($patient_id, $payrow['encounter'], $session_id,
            0 - $payrow['amount2'], '', 0, $thissrc, 0);
        }
        else {
          // Look up the matching invoice and post an offsetting payment.
          slInitialize();
          $invnum = "$patient_id." . $payrow['encounter'];
          $thissrc = 'Pt/';
          if ($payrow['method']) {
            $thissrc .= $payrow['method'];
            if ($payrow['source']) $thissrc .= ' ' . $payrow['source'];
          }
          $thissrc .= ' front office reversal';
          $trans_id = SLQueryValue("SELECT id FROM ar WHERE " .
            "ar.invnumber = '$invnum' LIMIT 1");
          if ($trans_id) {
            slPostPayment($trans_id, 0 - $payrow['amount2'], date('Y-m-d'),
              $thissrc, '', 0, 0);
          } else {
            $info_msg .= "Invoice '$invnum' not found; could not delete its " .
              "payment of \$" . $payrow['amount2'] . ". ";
          }
          SLClose();
        }
      }
      row_delete("payments", "id = '" . $payrow['id'] . "'");
    }
  }
  else if ($billing) {
    if (!acl_check('acct','disc')) die("Not authorized!");
    list($patient_id, $encounter_id) = explode(".", $billing);
    if ($GLOBALS['oer_config']['ws_accounting']['enabled'] === 2) {
      sqlStatement("DELETE FROM ar_activity WHERE " .
        "pid = '$patient_id' AND encounter = '$encounter_id'");
      sqlStatement("DELETE ar_session FROM ar_session LEFT JOIN " .
        "ar_activity ON ar_session.session_id = ar_activity.session_id " .
        "WHERE ar_activity.session_id IS NULL");
      row_modify("billing", "activity = 0",
        "pid = '$patient_id' AND " .
        "encounter = '$encounter_id' AND " .
        "code_type = 'COPAY' AND " .
        "activity = 1");
      sqlStatement("UPDATE form_encounter SET last_level_billed = 0, " .
        "last_level_closed = 0, stmt_count = 0, last_stmt_date = NULL " .
        "WHERE pid = '$patient_id' AND encounter = '$encounter_id'");
    }
    else {
      slInitialize();
      $trans_id = SLQueryValue("SELECT id FROM ar WHERE ar.invnumber = '$billing' LIMIT 1");
      if ($trans_id) {
        newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Invoice $billing from SQL-Ledger");
        SLQuery("DELETE FROM acc_trans WHERE trans_id = '$trans_id'");
        if ($sl_err) die($sl_err);
        SLQuery("DELETE FROM invoice WHERE trans_id = '$trans_id'");
        if ($sl_err) die($sl_err);
        SLQuery("DELETE FROM ar WHERE id = '$trans_id'");
        if ($sl_err) die($sl_err);
      } else {
        $info_msg .= "Invoice '$billing' not found!";
      }
      SLClose();
    }
    sqlStatement("UPDATE drug_sales SET billed = 0 WHERE " .
      "pid = '$patient_id' AND encounter = '$encounter_id'");
    updateClaim(true, $patient_id, $encounter_id, -1, -1, 1, 0, ''); // clears for rebilling
  }
  else if ($transaction) {
   if (!acl_check('admin', 'super')) die("Not authorized!");
   row_delete("transactions", "id = '$transaction'");
  }
  else {
   die("Nothing was recognized to delete!");
  }

  if (! $info_msg) $info_msg = xl('Delete successful.');

  // Close this window and tell our opener that it's done.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  if ($encounterid) //this code need to be same as 'parent.imdeleted($encounterid)' when the popup is div like
   {
    echo "window.opener.imdeleted($encounterid);\n";
   }
  else
   {
    echo "parent.imdeleted();\n";
   }
  echo "</script></body></html>\n";
  exit();
 }
?>

<form method='post' name="deletefrm" action='deleter.php?patient=<?php echo $patient ?>&encounterid=<?php echo $encounterid ?>&formid=<?php echo $formid ?>&issue=<?php echo $issue ?>&document=<?php echo $document ?>&payment=<?php echo $payment ?>&billing=<?php echo $billing ?>&transaction=<?php echo $transaction ?>' onsubmit="javascript:alert('1');document.deleform.submit();">

<p class="text">&nbsp;<br><?php xl('Do you really want to delete','e'); ?>

<?php
 if ($patient) {
  echo xl('patient') . " $patient";
 } else if ($encounterid) {
  echo xl('encounter') . " $encounterid";
 } else if ($formid) {
  echo xl('form') . " $formid";
 } else if ($issue) {
  echo xl('issue') . " $issue";
 } else if ($document) {
  echo xl('document') . " $document";
 } else if ($payment) {
  echo xl('payment') . " $payment";
 } else if ($billing) {
  echo xl('invoice') . " $billing";
 } else if ($transaction) {
  echo xl('transaction') . " $transaction";
 }
?> <?php xl('and all subordinate data? This action will be logged','e'); ?>!</p>

<center>

<p class="text">&nbsp;<br>
<a href="#" onclick="submit_form()" class="css_button"><span><?php xl('Yes, Delete and Log','e'); ?></span></a>
<input type='hidden' name='form_submit' value=<?php xl('Yes, Delete and Log','e','\'','\''); ?>/>
<a href='#' class="css_button" onclick='parent.$.fn.fancybox.close();'><span><?php echo xl('No, Cancel');?></span></a>
</p>

</center>
</form>
</body>
</html>
