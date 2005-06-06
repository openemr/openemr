<?
  // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  // This is the second of two pages to support posting of EOBs.
  // The first is sl_eob_search.php.

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/forms.inc");
  include_once("../../library/sql-ledger.inc");
  include_once("../../library/invoice_summary.inc.php");
  include_once("../../custom/code_types.inc.php");

  $debug = 0; // set to 1 for debugging mode

  $reasons = array(
    "Ins adjust",
    "Coll w/o",
    "Pt released",
    "Sm debt w/o",
    "To ded'ble",
    "To copay",
    "Bad debt",
    "Discount",
    "Hardship w/o",
    "Ins refund",
    "Pt refund",
    "Ins overpaid",
    "Pt overpaid"
  );

  $info_msg = "";

  // Format money for display.
  //
  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  // Insert a row into the acc_trans table.
  //
  function addTransaction($invid, $chartid, $amount, $date, $source, $memo, $insplan) {
    global $sl_err, $debug;
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
  function addLineItem($invid, $serialnumber, $amount, $adjdate, $insplan, $reason) {
    global $sl_err, $services_id, $debug;
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
  function updateAR($invid, $amount, $paid = 0, $paydate = "") {
    global $sl_err, $debug;
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

  // Do whatever is necessary to make this invoice re-billable.
  //
  function setupSecondary($invid) {
    global $sl_err, $debug, $info_msg, $GLOBALS;

    // Get some needed items from the SQL-Ledger invoice.
    $arres = SLQuery("select invnumber, transdate, customer_id, employee_id " .
      "from ar where ar.id = $invid");
    if ($sl_err) die($sl_err);
    $arrow = SLGetRow($arres, 0);
    if (! $arrow) die("There is no match for invoice id = $trans_id.");
    $customer_id = $arrow['customer_id'];
    list($trash, $encounter) = explode(".", $arrow['invnumber']);

    // Get the OpenEMR PID corresponding to the customer.
    $pdrow = sqlQuery("SELECT patient_data.pid " .
      "FROM integration_mapping, patient_data WHERE " .
      "integration_mapping.foreign_id = $customer_id AND " .
      "integration_mapping.foreign_table = 'customer' AND " .
      "patient_data.id = integration_mapping.local_id");
    $pid = $pdrow['pid'];
    if (! $pid) die("Cannot find patient from SQL-Ledger customer id = $customer_id.");

    // Find out if the encounter exists.
    $ferow = sqlQuery("SELECT pid FROM form_encounter WHERE " .
      "encounter = $encounter");
    $encounter_pid = $ferow['pid'];

    // If it exists, just update the billing items.
    if ($encounter_pid) {
      if ($encounter_pid != $pid)
        die("Expected form_encounter.pid to be $pid, but was $encounter_pid");
      $query = "UPDATE billing SET billed = 0, bill_process = 0, payer_id = -1, " .
        "bill_date = NULL, process_date = NULL, process_file = NULL " .
        "WHERE encounter = $encounter AND pid = $pid AND activity = 1";
      if ($debug) {
        echo $query . "<br>\n";
      } else {
        sqlQuery($query);
      }
      $info_msg = "Encounter $encounter is ready for re-billing.";
      return;
    }

    // It does not exist then it better be a date.
    if (! preg_match("/^20\d\d\d\d\d\d$/", $encounter))
      die("Internal error: encounter '$encounter' should exist but does not.");

    $employee_id = $arrow['employee_id'];

    // Get the OpenEMR provider info corresponding to the SQL-Ledger salesman.
    $drrow = sqlQuery("SELECT users.id, users.username, users.facility " .
      "FROM integration_mapping, users WHERE " .
      "integration_mapping.foreign_id = $employee_id AND " .
      "integration_mapping.foreign_table = 'salesman' AND " .
      "users.id = integration_mapping.local_id");
    $provider_id = $drrow['id'];
    if (! $provider_id) die("Cannot find provider from SQL-Ledger employee = $employee_id.");

    $date_of_service = $arrow['transdate'];
    if (! $date_of_service) die("Invoice has no date!");

    // Generate a new encounter number.
    $conn = $GLOBALS['adodb']['db'];
    $new_encounter = $conn->GenID("sequences");

    // Create the "new encounter".
    $encounter_id = 0;
    $query = "INSERT INTO form_encounter ( " .
      "date, reason, facility, pid, encounter, onset_date " .
      ") VALUES ( " .
      "'$date_of_service', " .
      "'Imported from Accounting', " .
      "'" . addslashes($drrow['facility']) . "', " .
      "$pid, " .
      "$new_encounter, " .
      "'$date_of_service' " .
      ")";
    if ($debug) {
      echo $query . "<br>\n";
      echo "Call to addForm() goes here.<br>\n";
    } else {
      $encounter_id = idSqlStatement($query);
      if (! $encounter_id) die("Insert failed: $query");
      addForm($new_encounter, "New Patient Encounter", $encounter_id,
        "newpatient", $pid, 1, $date_of_service);
      $info_msg = "Encounter $new_encounter has been created. ";
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
      $code = "Unknown";
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

      /****
      if (preg_match("/CPT/", $row['serialnumber'])) {
        $code_type = "CPT4";
        $code_text = "Procedure $code";
      }
      else if (preg_match("/HCPCS/", $row['serialnumber'])) {
        $code_type = "HCPCS";
        $code_text = "Procedure $code";
      }
      else if (preg_match("/ICD/", $row['serialnumber'])) {
        $code_type = "ICD9";
        $code_text = "Diagnosis $code";
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
      ****/

      foreach ($code_types as $key => $value) {
        if (preg_match("/$key/", $row['serialnumber'])) {
          $code_type = $key;
          if ($value['fee']) {
            $code_text = "Procedure $code";
          } else {
            $code_text = "Diagnosis $code";
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
        "modifier, units, fee, justify " .
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
        "-1, " .
        "0, " .
        "0, " .
        "'$modifier', " .
        "0, " .
        "$amount, " .
        "'' " .
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
      $info_msg .= "This invoice number has been changed to $new_invnumber.";
    }
  }
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title>EOB Posting - Invoice</title>
<script language="JavaScript">

// Compute an adjustment that writes off the balance:
function writeoff(code) {
 var f = document.forms[0];
 var tmp =
  f['form_line[' + code + '][bal]'].value -
  f['form_line[' + code + '][pay]'].value;
 f['form_line[' + code + '][adj]'].value = Number(tmp).toFixed(2);
 return false;
}

// Onsubmit handler.  A good excuse to write some JavaScript.
function validate(f) {
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  var pfxlen = ename.indexOf('[pay]');
  if (pfxlen < 0) continue;
  var pfx = ename.substring(0, pfxlen);
  var code = pfx.substring(pfx.indexOf('[')+1, pfxlen-1);
  if (f[pfx+'[pay]'].value || f[pfx+'[adj]'].value) {
   if (! f[pfx+'[src]'].value) {
    alert('Source is missing for code ' + code + '; this should be a check or EOB number');
    return false;
   }
   if (! f[pfx+'[date]'].value) {
    alert('Date is missing for code ' + code);
    return false;
   }
  }
  if (f[pfx+'[pay]'].value && isNaN(parseFloat(f[pfx+'[pay]'].value))) {
   alert('Payment value for code ' + code + ' is not a number');
   return false;
  }
  if (f[pfx+'[adj]'].value && isNaN(parseFloat(f[pfx+'[adj]'].value))) {
   alert('Adjustment value for code ' + code + ' is not a number');
   return false;
  }
  // TBD: validate the date format
 }
 return true;
}

</script>
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<?
  $trans_id = $_GET['id'];
  if (! $trans_id) die("You cannot access this page directly.");

  SLConnect();

  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);
  if (! $chart_id_cash) die("There is no COA entry for cash account '$sl_cash_acc'");

  $chart_id_ar = SLQueryValue("select id from chart where accno = '$sl_ar_acc'");
  if ($sl_err) die($sl_err);
  if (! $chart_id_ar) die("There is no COA entry for AR account '$sl_ar_acc'");

  $chart_id_income = SLQueryValue("select id from chart where accno = '$sl_income_acc'");
  if ($sl_err) die($sl_err);
  if (! $chart_id_income) die("There is no COA entry for income account '$sl_income_acc'");

  $services_id = SLQueryValue("select id from parts where partnumber = '$sl_services_id'");
  if ($sl_err) die($sl_err);
  if (! $services_id) die("There is no parts entry for services ID '$sl_services_id'");

  if ($_POST['form_save'] || $_POST['form_cancel']) {
    if ($_POST['form_save']) {
      if ($debug) {
        echo "<p><b>This module is in test mode. The database will not be changed.</b><p>\n";
      }
      $paytotal = 0;
      foreach ($_POST['form_line'] as $code => $cdata) {
        $thissrc  = trim($cdata['src']);
        $thisdate = trim($cdata['date']);
        $thispay  = trim($cdata['pay']);
        $thisadj  = trim($cdata['adj']);
        $thisins  = trim($cdata['ins']);
        $reason   = trim($cdata['reason']);
        if (strpos(strtolower($reason), 'ins') !== false)
          $reason .= ' ' . $_POST['form_insurance'];
        if (! $thisins) $thisins = 0;
        if ($thispay) {
          // Post a payment: add to ar, subtract from cash.
          addTransaction($trans_id, $chart_id_ar, $thispay, $thisdate, $thissrc, $code, $thisins);
          addTransaction($trans_id, $chart_id_cash, 0 - $thispay, $thisdate, $thissrc, $code, $thisins);
          updateAR($trans_id, 0, $thispay, $thisdate);
          $paytotal += $thispay;
        }
        if ($thisadj) {
          // Post an adjustment: add negative invoice item, add to ar, subtract from income
          addLineItem($trans_id, $code, 0 - $thisadj, $thisdate, $thisins, $reason);
          addTransaction($trans_id, $chart_id_ar, $thisadj, $thisdate, "InvAdj $thissrc", $code, $thisins);
          addTransaction($trans_id, $chart_id_income, 0 - $thisadj, $thisdate, "InvAdj $thissrc", $code, $thisins);
          updateAR($trans_id, 0 - $thisadj);
        }
      }
      $form_duedate = fixDate($_POST['form_duedate']);
      $form_notes = trim($_POST['form_notes']);
      $query = "UPDATE ar SET duedate = '$form_duedate', notes = '$form_notes' " .
        "WHERE id = $trans_id";
      if ($debug) {
        echo $query . "<br>\n";
      } else {
        SLQuery($query);
        if ($sl_err) die($sl_err);
      }
      if ($_POST['form_secondary']) {
        setupSecondary($trans_id);
      }
      echo "<script language='JavaScript'>\n";
      echo " var tmp = opener.document.forms[0].form_amount.value - $paytotal;\n";
      echo " opener.document.forms[0].form_amount.value = Number(tmp).toFixed(2);\n";
    } else {
      echo "<script language='JavaScript'>\n";
    }
    if ($info_msg) echo " alert('$info_msg');\n";
    if (! $debug) echo " window.close();\n";
    echo "</script></body></html>\n";
    SLClose();
    exit();
  }

  // Get invoice data into $arrow.
  $arres = SLQuery("select ar.*, customer.name, employee.name as doctor " .
    "from ar, customer, employee where ar.id = $trans_id and " .
    "customer.id = ar.customer_id and employee.id = ar.employee_id");
  if ($sl_err) die($sl_err);
  $arrow = SLGetRow($arres, 0);
  if (! $arrow) die("There is no match for invoice id = $trans_id.");

  // Get invoice charge details.
  $codes = get_invoice_summary($trans_id);
?>
<center>

<form method='post' action='sl_eob_invoice.php?id=<? echo $trans_id ?>'
 onsubmit='return validate(this)'>

<table border='0' cellpadding='3'>
 <tr>
  <td>
   Patient:
  </td>
  <td>
   <?echo $arrow['name'] ?>
  </td>
  <td colspan="2" rowspan="3">
   <textarea name="form_notes" cols="50" style="height:100%"><?echo $arrow['notes'] ?></textarea>
  </td>
 </tr>
 <tr>
  <td>
   Provider:
  </td>
  <td>
   <?echo $arrow['doctor'] ?>
  </td>
 </tr>
 <tr>
  <td>
   Invoice:
  </td>
  <td>
   <?echo $arrow['invnumber'] ?>
  </td>
 </tr>
 <tr>
  <td>
   Bill Date:
  </td>
  <td>
   <?echo $arrow['transdate'] ?>
  </td>
  <td colspan="2">
   Now posting for:&nbsp;
   <input type='radio' name='form_insurance' value='Primary' checked />Ins1&nbsp;
   <input type='radio' name='form_insurance' value='Secondary' />Ins2&nbsp;
   <input type='radio' name='form_insurance' value='Tertiary' />Ins3
  </td>
 </tr>
 <tr>
  <td>
   Due Date:
  </td>
  <td>
   <input type='text' name='form_duedate' size='10' value='<?echo $arrow['duedate'] ?>'
    title='Due date mm/dd/yyyy or yyyy-mm-dd'>
  </td>
  <td colspan="2">
   <input type="checkbox" name="form_secondary" value="1"> Needs secondary billing
   &nbsp;&nbsp;
   <input type='submit' name='form_save' value='Save'>
   &nbsp;
   <input type='button' value='Cancel' onclick='window.close()'>
  </td>
 </tr>
 <tr>
  <td height="1">
  </td>
 </tr>
</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   Code
  </td>
  <td class="dehead" align="right">
   Charge
  </td>
  <td class="dehead" align="right">
   Balance
  </td>
  <td class="dehead">
   Source
  </td>
  <td class="dehead">
   Date
  </td>
  <td class="dehead">
   Pay
  </td>
  <td class="dehead">
   Adjust
  </td>
  <td class="dehead">
   Reason
  </td>
 </tr>
<?
  foreach ($codes as $code => $cdata) {
?>
 <tr>
  <td class="detail">
   <? echo $code ?>
  </td>
  <td class="detail" align="right">
   <? bucks($cdata['chg']) ?>
  </td>
  <td class="detail" align="right">
   <input type="hidden" name="form_line[<? echo $code ?>][bal]" value="<? bucks($cdata['bal']) ?>">
   <input type="hidden" name="form_line[<? echo $code ?>][ins]" value="<? echo $cdata['ins'] ?>">
   <? bucks($cdata['bal']) ?>
  </td>
  <td class="detail">
   <input type="text" name="form_line[<? echo $code ?>][src]" size="10">
  </td>
  <td class="detail">
   <input type="text" name="form_line[<? echo $code ?>][date]" size="10">
  </td>
  <td class="detail">
   <input type="text" name="form_line[<? echo $code ?>][pay]" size="10">
  </td>
  <td class="detail">
   <input type="text" name="form_line[<? echo $code ?>][adj]" size="10">
   &nbsp; <a href="" onclick="return writeoff('<? echo $code ?>')">W</a>
  </td>
  <td class="detail">
   <select name="form_line[<? echo $code ?>][reason]">
<?
 foreach ($reasons as $value) {
  echo "    <option value=\"$value\">$value</option>\n";
 }
?>
   </select>
  </td>
 </tr>
<?
  }
  SLClose();
?>

</table>
</form>
</center>
<script language="JavaScript">
 var f1 = opener.document.forms[0];
 var f2 = document.forms[0];
<?
  foreach ($codes as $code => $cdata) {
    echo " f2['form_line[$code][src]'].value  = f1.form_source.value;\n";
    echo " f2['form_line[$code][date]'].value = f1.form_paydate.value;\n";
  }
?>
</script>
</body>
</html>
