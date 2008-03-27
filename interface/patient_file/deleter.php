<?php
 // Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
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

 $info_msg = "";

 $thisauth = acl_check('admin', 'super');
 if (! $thisauth) die("Not authorized!");

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
   newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], "$table: $logstring");
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
   newEvent("deactivate", $_SESSION['authUser'], $_SESSION['authProvider'], "$table: $where");
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

?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Delete Patient, Encounter, Form, Issue or Document','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

</head>

<body class="body_top">
<?php
 // If the delete is confirmed...
 //
 if ($_POST['form_submit']) {

  if ($patient) {
   row_modify("billing"       , "activity = 0", "pid = '$patient'");
   row_modify("pnotes"        , "activity = 0", "pid = '$patient'");
   // row_modify("prescriptions" , "active = 0"  , "patient_id = '$patient'");

   row_delete("prescriptions"  , "patient_id = '$patient'");
   row_delete("claims"         , "patient_id = '$patient'");
   row_delete("drug_sales"     , "pid = '$patient'");
   row_delete("payments"       , "pid = '$patient'");
   row_delete("openemr_postcalendar_events", "pc_pid = '$patient'");
   row_delete("immunizations"  , "patient_id = '$patient'");
   row_delete("issue_encounter", "pid = '$patient'");
   row_delete("lists"          , "pid = '$patient'");
   row_delete("transactions"   , "pid = '$patient'");
   row_delete("employer_data"  , "pid = '$patient'");
   row_delete("history_data"   , "pid = '$patient'");
   row_delete("insurance_data" , "pid = '$patient'");
   row_delete("patient_data"   , "pid = '$patient'");

   $res = sqlStatement("SELECT * FROM forms WHERE pid = '$patient'");
   while ($row = sqlFetchArray($res)) {
    $formdir = ($row['formdir'] == 'newpatient') ? 'encounter' : $row['formdir'];
    row_delete("form_$formdir", "id = '" . $row['form_id'] . "'");
   }
   row_delete("forms", "pid = '$patient'");

   $row = sqlQuery("SELECT id FROM patient_data WHERE pid = '$patient'");
   row_delete("integration_mapping", "local_table = 'patient_data' AND " .
    "local_id = '" . $row['id'] . "'");
  }
  else if ($encounterid) {
   row_modify("billing", "activity = 0", "encounter = '$encounterid'");
   row_delete("claims", "encounter_id = '$encounterid'");
   row_delete("issue_encounter", "encounter = '$encounterid'");
   $res = sqlStatement("SELECT * FROM forms WHERE encounter = '$encounterid'");
   while ($row = sqlFetchArray($res)) {
    $formdir = ($row['formdir'] == 'newpatient') ? 'encounter' : $row['formdir'];
    row_delete("form_$formdir", "id = '" . $row['form_id'] . "'");
   }
   row_delete("forms", "encounter = '$encounterid'");
  }
  else if ($formid) {
   $row = sqlQuery("SELECT * FROM forms WHERE id = '$formid'");
   $formdir = $row['formdir'];
   if (! $formdir) die("There is no form with id '$formid'");
   $formname = ($formdir == 'newpatient') ? 'encounter' : $formdir;
   row_delete("form_$formname", "id = '" . $row['form_id'] . "'");
   row_delete("forms", "id = '$formid'");
  }
  else if ($issue) {
   row_delete("issue_encounter", "list_id = '$issue'");
   row_delete("lists", "id = '$issue'");
  }
  else if ($document) {
   $trow = sqlQuery("SELECT url FROM documents WHERE id = '$document'");
   $url = $trow['url'];
   row_delete("categories_to_documents", "document_id = '$document'");
   row_delete("documents", "id = '$document'");
   if (substr($url, 0, 7) == 'file://') {
    @unlink(substr($url, 7));
   }
  }
  else if ($payment) {
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
      row_delete("payments", "id = '" . $payrow['id'] . "'");
    }
  }
  else {
   die("Nothing was recognized to delete!");
  }

  if (! $info_msg) $info_msg = "Delete successful.";

  // Close this window and tell our opener that it's done.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  echo " if (opener.imdeleted) opener.imdeleted();\n";
  echo "</script></body></html>\n";
  exit();
 }
?>

<form method='post' action='deleter.php?patient=<?php echo $patient ?>&encounterid=<?php echo $encounterid ?>&formid=<?php echo $formid ?>&issue=<?php echo $issue ?>&document=<?php echo $document ?>&payment=<?php echo $payment ?>'>

<p>&nbsp;<br><?php xl('
Do you really want to delete','e'); ?>

<?php
 if ($patient) {
  echo "patient $patient";
 } else if ($encounterid) {
  echo "encounter $encounterid";
 } else if ($formid) {
  echo "form $formid";
 } else if ($issue) {
  echo "issue $issue";
 } else if ($document) {
  echo "document $document";
 } else if ($payment) {
  echo "payment $payment";
 }
?> <?php xl('and all subordinate data? This action will be logged','e'); ?>!</p>

<center>

<p>&nbsp;<br>
<input type='submit' name='form_submit' value='Yes, Delete and Log' />
&nbsp;
<input type='button' value='No, Cancel' onclick='window.close()' />
</p>

</center>
</form>
</body>
</html>
