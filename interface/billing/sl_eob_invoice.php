<?php
  // Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  // This provides for manual posting of EOBs.  It is invoked from
  // sl_eob_search.php.  For automated (X12 835) remittance posting
  // see sl_eob_process.php.

  require_once("../globals.php");
  require_once("$srcdir/log.inc");
  require_once("$srcdir/patient.inc");
  require_once("$srcdir/forms.inc");
  require_once("$srcdir/sl_eob.inc.php");
  require_once("$srcdir/invoice_summary.inc.php");
  require_once("../../custom/code_types.inc.php");

  $debug = 0; // set to 1 for debugging mode

  $INTEGRATED_AR = $GLOBALS['oer_config']['ws_accounting']['enabled'] === 2;
 
  // If we permit deletion of transactions.  Might change this later.
  $ALLOW_DELETE = $INTEGRATED_AR;

  $info_msg = "";

  // Format money for display.
  //
  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  // Delete rows, with logging, for the specified table using the
  // specified WHERE clause.  Borrowed from deleter.php.
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
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<title><?php xl('EOB Posting - Invoice','e')?></title>
<script language="JavaScript">

// An insurance radio button is selected.
function setins(istr) {
<?php if (!$INTEGRATED_AR) { ?>
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  if (ename.indexOf('[src]') < 0) continue;
  var evalue = f.elements[i].value;
  var tmp = evalue.substring(0, 4).toLowerCase();
  if (tmp >= 'ins1' && tmp <= 'ins3')
   evalue = evalue.substring(4);
  else if (evalue.substring(0, 2).toLowerCase() == 'pt')
   evalue = evalue.substring(2);
  while (evalue.substring(0, 1) == '/')
   evalue = evalue.substring(1);
  f.elements[i].value = istr + '/' + evalue;
 }
<?php } ?>
 return true;
}

// Compute an adjustment that writes off the balance:
function writeoff(code) {
 var f = document.forms[0];
 var belement = f['form_line[' + code + '][bal]'];
 var pelement = f['form_line[' + code + '][pay]'];
 var aelement = f['form_line[' + code + '][adj]'];
 var relement = f['form_line[' + code + '][reason]'];
 var tmp = belement.value - pelement.value;
 aelement.value = Number(tmp).toFixed(2);
 if (aelement.value && ! relement.value) relement.selectedIndex = 1;
 return false;
}

// Onsubmit handler.  A good excuse to write some JavaScript.
function validate(f) {
 var delcount = 0;
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  // Count deletes.
  if (ename.substring(0, 9) == 'form_del[') {
   if (f.elements[i].checked) ++delcount;
   continue;
  }
  var pfxlen = ename.indexOf('[pay]');
  if (pfxlen < 0) continue;
  var pfx = ename.substring(0, pfxlen);
  var code = pfx.substring(pfx.indexOf('[')+1, pfxlen-1);
  if (f[pfx+'[pay]'].value || f[pfx+'[adj]'].value) {
<?php if (!$INTEGRATED_AR) { // source validation not appropriate ?>
   var srcobj = f[pfx+'[src]'];
   while (srcobj.value.length) {
    var tmp = srcobj.value.substring(srcobj.value.length - 1);
    if (tmp > ' ' && tmp != '/') break;
    srcobj.value = srcobj.value.substring(0, srcobj.value.length - 1);
   }
   var svalue = srcobj.value;
   if (! svalue) {
    alert('<?php xl('Source is missing for code ','e') ?>' + code);
    return false;
   } else {
    var tmp = svalue.substring(0, 4).toLowerCase();
    if (tmp >= 'ins1' && tmp <= 'ins3') {
     svalue = svalue.substring(4);
    } else if (svalue.substring(0, 2).toLowerCase() == 'pt') {
     svalue = svalue.substring(2);
    } else {
     alert('<?php xl('Invalid or missing payer in source for code ','e')?>' + code);
     return false;
    }
    if (svalue) {
     if (svalue.substring(0, 1) != '/') {
      alert('<?php xl('Missing slash after payer in source for code ','e')?>' + code);
      return false;
     }
     if (false) { // Please keep this, Oakland Clinic wants it.  -- Rod
      tmp = svalue.substring(1, 3).toLowerCase();
      if (tmp != 'nm' && tmp != 'ci' && tmp != 'cp' && tmp != 'ne' &&
          tmp != 'it' && tmp != 'pf' && tmp != 'pp' && tmp != 'ok')
      {
       alert('<?php xl('Invalid source designation "','e') ?>' + tmp + '<?php xl('" for code ','e') ?>' + code);
       return false;
      }
     } // End of OC code
    }
   }
<?php } ?>
   if (! f[pfx+'[date]'].value) {
    alert('<?php xl('Date is missing for code ','e')?>' + code);
    return false;
   }
  }
  if (f[pfx+'[pay]'].value && isNaN(parseFloat(f[pfx+'[pay]'].value))) {
   alert('<?php xl('Payment value for code ','e') ?>' + code + '<?php xl(' is not a number','e') ?>');
   return false;
  }
  if (f[pfx+'[adj]'].value && isNaN(parseFloat(f[pfx+'[adj]'].value))) {
   alert('<?php xl('Adjustment value for code ','e') ?>' + code + '<?php xl(' is not a number','e') ?>');
   return false;
  }
  if (f[pfx+'[adj]'].value && ! f[pfx+'[reason]'].value) {
   alert('<?php xl('Please select an adjustment reason for code ','e') ?>' + code);
   return false;
  }
  // TBD: validate the date format
 }
 // Demand confirmation if deleting anything.
 if (delcount > 0) {
  if (!confirm('<?php echo xl('Really delete'); ?> ' + delcount +
   ' <?php echo xl('transactions'); ?>?' +
   ' <?php echo xl('This action will be logged'); ?>!')
  ) return false;
 }
 return true;
}

<!-- Get current date -->

function getFormattedToday() 
{
   var today = new Date();
   var dd = today.getDate();
   var mm = today.getMonth()+1; //January is 0!
   var yyyy = today.getFullYear();
   if(dd<10){dd='0'+dd}
   if(mm<10){mm='0'+mm}

   return (yyyy + '-' + mm + '-' + dd);
}

<!-- Update Payment Fields -->

function updateFields(payField, adjField, balField, coPayField, isFirstProcCode)
{
   var payAmount = 0.0;
   var adjAmount = 0.0;
   var balAmount = 0.0;
   var coPayAmount = 0.0;
   
   // coPayFiled will be null if there is no co-pay entry in the fee sheet
   if (coPayField)
      coPayAmount = coPayField.value;

   // if balance field is 0.00, its value comes back as null, so check for nul-ness first
   if (balField)
      balAmount = (balField.value) ? balField.value : 0;
   if (payField)
      payAmount = (payField.value) ? payField.value : 0;

   //alert('balance = >' + balAmount +'<  payAmount = ' + payAmount + '  copay = ' + coPayAmount + '  isFirstProcCode = ' + isFirstProcCode);

   // subtract the co-pay only from the first procedure code
   if (isFirstProcCode == 1)
      balAmount = parseFloat(balAmount) + parseFloat(coPayAmount);

   adjAmount = balAmount - payAmount;
   
   // Assign rounded adjustment value back to TextField
   adjField.value = adjAmount = Math.round(adjAmount*100)/100;
}

</script>
</head>
<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<?php
  $trans_id = 0 + $_GET['id'];
  if (! $trans_id) die(xl("You cannot access this page directly."));

  if ($INTEGRATED_AR) {
    // In the Integrated A/R case, $trans_id matches form_encounter.id.
    $ferow = sqlQuery("SELECT e.*, p.fname, p.mname, p.lname " .
      "FROM form_encounter AS e, patient_data AS p WHERE " .
      "e.id = '$trans_id' AND p.pid = e.pid");
    if (empty($ferow)) die("There is no encounter with form_encounter.id = '$trans_id'.");
    $patient_id        = 0 + $ferow['pid'];
    $encounter_id      = 0 + $ferow['encounter'];
    $svcdate           = substr($ferow['date'], 0, 10);
    $form_payer_id     = 0 + $_POST['form_payer_id'];
    $form_reference    = $_POST['form_reference'];
    $form_check_date   = fixDate($_POST['form_check_date'], date('Y-m-d'));
    $form_deposit_date = fixDate($_POST['form_deposit_date'], $form_check_date);
    $form_pay_total    = 0 + $_POST['form_pay_total'];
  }
  else {
    slInitialize();
  }

  $payer_type = 0;
  if (preg_match('/^Ins(\d)/i', $_POST['form_insurance'], $matches)) {
    $payer_type = $matches[1];
  }

  if ($_POST['form_save'] || $_POST['form_cancel']) {
    if ($_POST['form_save']) {
      if ($debug) {
        echo xl("This module is in test mode. The database will not be changed.",'','<p><b>',"</b><p>\n");
      }

      if ($INTEGRATED_AR) {
        $session_id = arGetSession($form_payer_id, $form_reference,
          $form_check_date, $form_deposit_date, $form_pay_total);
        // The sl_eob_search page needs its invoice links modified to invoke
        // javascript to load form parms for all the above and submit.
        // At the same time that page would be modified to work off the
        // openemr database exclusively.
        // And back to the sl_eob_invoice page, I think we may want to move
        // the source input fields from row level to header level.

        // Handle deletes. row_delete() is borrowed from deleter.php.
        if ($ALLOW_DELETE && !$debug) {
          foreach ($_POST['form_del'] as $arseq => $dummy) {
            row_delete("ar_activity", "pid = '$patient_id' AND " .
              "encounter = '$encounter_id' AND sequence_no = '$arseq'");
          }
        }
      }

      $paytotal = 0;
      foreach ($_POST['form_line'] as $code => $cdata) {
        if (!$INTEGRATED_AR) {
          $thissrc  = trim($cdata['src']);
          $thisdate = trim($cdata['date']);
        }
        $thispay  = trim($cdata['pay']);
        $thisadj  = trim($cdata['adj']);
        $thisins  = trim($cdata['ins']);
        $reason   = trim($cdata['reason']);
        if (strpos(strtolower($reason), 'ins') !== false)
          $reason .= ' ' . $_POST['form_insurance'];
        if (! $thisins) $thisins = 0;

        if ($thispay) {
          if ($INTEGRATED_AR) {
            arPostPayment($patient_id, $encounter_id, $session_id,
              $thispay, $code, $payer_type, $reason, $debug);
          } else {
            slPostPayment($trans_id, $thispay, $thisdate, $thissrc, $code, $thisins, $debug);
          }
          $paytotal += $thispay;
        }

        // Be sure to record adjustment reasons even for zero adjustments.
        if ($thisadj || $reason) {
          // "To copay" and "To ded'ble" need to become a comment in a zero
          // adjustment, formatted just like sl_eob_process.php.
          if (preg_match("/To copay/", $reason)) {
            $reason = $_POST['form_insurance'] . " coins: $thisadj";
            $thisadj = 0;
          }
          else if (preg_match("/To ded'ble/", $reason)) {
            $reason = $_POST['form_insurance'] . " dedbl: $thisadj";
            $thisadj = 0;
          }
          if ($INTEGRATED_AR) {
            arPostAdjustment($patient_id, $encounter_id, $session_id,
              $thisadj, $code, $payer_type, $reason, $debug);
          } else {
            slPostAdjustment($trans_id, $thisadj, $thisdate, $thissrc, $code, $thisins, $reason, $debug);
          }
        }
      }

      // Maintain which insurances are marked as finished.

      if ($INTEGRATED_AR) {
        $form_done = 0 + $_POST['form_done'];
        $form_stmt_count = 0 + $_POST['form_stmt_count'];
        sqlStatement("UPDATE form_encounter " .
          "SET last_level_closed = $form_done, " .
          "stmt_count = $form_stmt_count WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id'");
      }
      else {
        $form_duedate = fixDate($_POST['form_duedate']);
        $form_notes = trim($_POST['form_notes']);
        // We use the "Ship Via" field of the invoice to hold these.
        $form_eobs = "";
        foreach (array('Ins1', 'Ins2', 'Ins3') as $value) {
          if ($_POST["form_done_$value"]) {
            if ($form_eobs) $form_eobs .= ","; else $form_eobs = "Done: ";
            $form_eobs .= $value;
          }
        }
        $query = "UPDATE ar SET duedate = '$form_duedate', notes = '$form_notes', " .
          "shipvia = '$form_eobs' WHERE id = $trans_id";
        if ($debug) {
          echo $query . "<br>\n";
        } else {
          SLQuery($query);
          if ($sl_err) die($sl_err);
        }
      }

      if ($_POST['form_secondary']) {
        if ($INTEGRATED_AR) {
          arSetupSecondary($patient_id, $encounter_id, $debug);
        } else {
          slSetupSecondary($trans_id, $debug);
        }
      }

      echo "<script language='JavaScript'>\n";
      echo " if (opener.document.forms[0].form_amount) {\n";
      echo "  var tmp = opener.document.forms[0].form_amount.value - $paytotal;\n";
      echo "  opener.document.forms[0].form_amount.value = Number(tmp).toFixed(2);\n";
      echo " }\n";
    } else {
      echo "<script language='JavaScript'>\n";
    }
    if ($info_msg) echo " alert('$info_msg');\n";
    if (! $debug) echo " window.close();\n";
    echo "</script></body></html>\n";
    if (!$INTEGRATED_AR) SLClose();
    exit();
  }

  if ($INTEGRATED_AR) {
    // Get invoice charge details.
    $codes = ar_get_invoice_summary($patient_id, $encounter_id, true);
  }
  else {
    // Get invoice data into $arrow.
    $arres = SLQuery("select ar.*, customer.name, employee.name as doctor " .
      "from ar, customer, employee where ar.id = $trans_id and " .
      "customer.id = ar.customer_id and employee.id = ar.employee_id");
    if ($sl_err) die($sl_err);
    $arrow = SLGetRow($arres, 0);
    if (! $arrow) die(xl("There is no match for invoice id = ") . $trans_id);
    //
    // Determine the date of service.  An 8-digit encounter number is
    // presumed to be a date of service imported during conversion.
    // Otherwise look it up in the form_encounter table.
    //
    $svcdate = "";
    list($patient_id, $encounter) = explode(".", $arrow['invnumber']);
    if (strlen($encounter) == 8) {
      $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
        "-" . substr($encounter, 6, 2);
    }
    else if ($encounter) {
      $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
        "encounter = $encounter");
      $svcdate = substr($tmp['date'], 0, 10);
    }

    // Get invoice charge details.
    $codes = get_invoice_summary($trans_id, true);
  }

  $pdrow = sqlQuery("select genericname2, genericval2 " .
    "from patient_data where pid = '$patient_id' limit 1");
?>
<center>

<form method='post' action='sl_eob_invoice.php?id=<?php echo $trans_id ?>'
 onsubmit='return validate(this)'>

<table border='0' cellpadding='3'>
 <tr>
  <td>
   <?php xl('Patient:','e')?>
  </td>
  <td>
<?php
  if ($INTEGRATED_AR) {
    echo $ferow['fname'] . ' ' . $ferow['mname'] . ' ' . $ferow['lname'];
  }
  else {
    echo $arrow['name'];
  }
?>
  </td>
  <td colspan="2" rowspan="3">
<?php
  if ($INTEGRATED_AR) {
    for ($i = 1; $i <= 3; ++$i) {
      $payerid = arGetPayerID($patient_id, $svcdate, $i);
      if ($payerid) {
        $tmp = sqlQuery("SELECT name FROM insurance_companies WHERE id = $payerid");
        echo "Ins$i: " . $tmp['name'] . "<br />";
      }
    }
  }
  else {
    echo "   <textarea name='form_notes' cols='50' style='height:100%'>";
    echo $arrow['notes'];
    echo "</textarea>\n";
  }
?>
  </td>
<?php
  if ($INTEGRATED_AR) {
    echo "<td rowspan='3' valign='bottom'>\n";
    echo xl('Statements Sent:');
    echo "</td>\n";
    echo "<td rowspan='3' valign='bottom'>\n";
    echo "<input type='text' name='form_stmt_count' size='10' value='" .
      (0 + $ferow['stmt_count']) . "' />\n";
    echo "</td>\n";
  }
?>
 </tr>
 <tr>
  <td>
   <?php xl('Provider:','e')?>
  </td>
  <td>
   <?php
  if ($INTEGRATED_AR) {
    $tmp = sqlQuery("SELECT fname, mname, lname " .
      "FROM users WHERE id = " . $ferow['provider_id']);
    echo $tmp['fname'] . ' ' . $tmp['mname'] . ' ' . $tmp['lname'];
    $tmp = sqlQuery("SELECT bill_date FROM billing WHERE " .
      "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
      "activity = 1 ORDER BY fee DESC, id ASC LIMIT 1");
    $billdate = substr(($tmp['bill_date'] . "Not Billed"), 0, 10);
  }
  else {
    echo $arrow['doctor'];
  }
?>
  </td>
 </tr>
 <tr>
  <td>
   <?php xl('Invoice:','e')?>
  </td>
  <td>
<?php
  if ($INTEGRATED_AR) {
    echo "$patient_id.$encounter_id";
  }
  else {
    echo $arrow['invnumber'];
  }
?>
  </td>
 </tr>

 <tr>
  <td>
<?php xl('Svc Date:','e'); ?>
  </td>
  <td>
<?php
    echo $svcdate;
?>
  </td>
  <td colspan="2">
   <?php xl('Done with:','e','',"&nbsp")?>;
<?php
  // Write a checkbox for each insurance.  It is to be checked when
  // we no longer expect any payments from that company for the claim.
  if ($INTEGRATED_AR) {
    $last_level_closed = 0 + $ferow['last_level_closed'];
    foreach (array(0 => 'None', 1 => 'Ins1', 2 => 'Ins2', 3 => 'Ins3') as $key => $value) {
      if ($key && !arGetPayerID($patient_id, $svcdate, $key)) continue;
      $checked = ($last_level_closed == $key) ? " checked" : "";
      echo "   <input type='radio' name='form_done' value='$key'$checked />$value&nbsp;\n";
    }
  }
  else {
    // The information is stored in the 'shipvia' field of the invoice.
    $insgot  = strtolower($arrow['notes']);
    $insdone = strtolower($arrow['shipvia']);
    foreach (array('Ins1', 'Ins2', 'Ins3') as $value) {
      $lcvalue = strtolower($value);
      $checked  = (strpos($insdone, $lcvalue) === false) ? "" : " checked";
      if (strpos($insgot, $lcvalue) !== false) {
        echo "   <input type='checkbox' name='form_done_$value' value='1'$checked />$value&nbsp;\n";
      }
    }
  }
?>
  </td>
<?php
  if ($INTEGRATED_AR) {
    echo "<td>\n";
    echo xl('Check/EOB No.:');
    echo "</td>\n";
    echo "<td>\n";
    echo "<input type='text' name='form_reference' size='10' value='' />\n";
    echo "</td>\n";
  }
?>
 </tr>

 <tr>
  <td>
   <?php xl('Last Bill Date:','e') ?>
  </td>
  <td>
   <?php
  if ($INTEGRATED_AR) {
    echo $billdate;
  }
  else {
    echo $arrow['transdate'];
  }
?>
  </td>
  <td colspan="2">
   <?php xl('Now posting for:','e','',"&nbsp")?>;

<?php
  // TBD: check the first not-done-with insurance, not always Ins1!
?>
   <input type='radio' name='form_insurance' value='Ins1' onclick='setins("Ins1")' checked /><?php xl('Ins1','e')?>&nbsp;
   <input type='radio' name='form_insurance' value='Ins2' onclick='setins("Ins2")' /><?php xl('Ins2','e')?>&nbsp;
   <input type='radio' name='form_insurance' value='Ins3' onclick='setins("Ins3")' /><?php xl('Ins3','e')?>&nbsp;
   <input type='radio' name='form_insurance' value='Pt'   onclick='setins("Pt")'   /><?php xl('Patient','e')?>

<?php
  // TBD: I think the following is unused and can be removed.
?>
   <input type='hidden' name='form_eobs' value='<?php echo addslashes($arrow['shipvia']) ?>' />

  </td>
<?php
  if ($INTEGRATED_AR) {
    echo "<td>\n";
    echo xl('Check/EOB Date:');
    echo "</td>\n";
    echo "<td>\n";
    echo "<input type='text' name='form_check_date' size='10' value='' />\n";
    echo "</td>\n";
  }
?>
 </tr>
 <tr>
  <td>
   <?php
  if (!$INTEGRATED_AR) xl('Due Date:','e');
?>
  </td>
  <td>

<?php if (!$INTEGRATED_AR) { ?>
   <input type='text' name='form_duedate' size='10' value='<?php echo $arrow['duedate'] ?>'
    title='<?php xl('Due date mm/dd/yyyy or yyyy-mm-dd','e')?>'>
<?php } ?>
  </td>
  <td colspan="2">
   <input type="checkbox" name="form_secondary" value="1"> <?php xl('Needs secondary billing','e')?>
   &nbsp;&nbsp;
   <input type='submit' name='form_save' value='<?php xl('Save','e')?>'>
   &nbsp;
   <input type='button' value='<?php xl('Cancel','e')?>' onclick='window.close()'>
  </td>
<?php
  if ($INTEGRATED_AR) {
    echo "<td>\n";
    echo xl('Deposit Date:');
    echo "</td>\n";
    echo "<td>\n";
    echo "<input type='text' name='form_deposit_date' size='10' value='' />\n";
    echo "<input type='hidden' name='form_payer_id' value='' />\n";
    echo "<input type='hidden' name='form_orig_reference' value='' />\n";
    echo "<input type='hidden' name='form_orig_check_date' value='' />\n";
    echo "<input type='hidden' name='form_orig_deposit_date' value='' />\n";
    echo "<input type='hidden' name='form_pay_total' value='' />\n";
    echo "</td>\n";
  }
?>
 </tr>
<?php if ($pdrow['genericname2'] == 'Billing') { ?>
 <tr>
  <td>
   <?php xl('Billing Note:','e')?>
  </td>
  <td colspan='3' style='color:red'>
   <?php echo $pdrow['genericval2'] ?>
  </td>
 </tr>
<?php } ?>
 <tr>
  <td height="1">
  </td>
 </tr>
</table>

<table border='0' cellpadding='2' cellspacing='0' width='98%'>

 <tr bgcolor="#cccccc">
  <td class="dehead">
   <?php xl('Code','e')?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Charge','e')?>
  </td>
  <td class="dehead" align="right">
   <?php xl('Balance','e')?>&nbsp;
  </td>
  <td class="dehead">
   <?php xl('By/Source','e')?>
  </td>
  <td class="dehead">
   <?php xl('Date','e')?>
  </td>
  <td class="dehead">
   <?php xl('Pay','e')?>
  </td>
  <td class="dehead">
   <?php xl('Adjust','e')?>
  </td>
  <td class="dehead">
   <?php xl('Reason','e')?>
  </td>
<?php if ($ALLOW_DELETE) { ?>
  <td class="dehead">
   <?php xl('Del','e')?>
  </td>
<?php } ?>
 </tr>
<?php
  $firstProcCodeIndex = -1;
  $encount = 0;
  foreach ($codes as $code => $cdata) {
   ++$encount;
   $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
   $dispcode = $code;

   // remember the index of the first entry whose code is not "CO-PAY", i.e. it's a legitimate proc code
   if ($firstProcCodeIndex == -1 && strcmp($code, "CO-PAY") !=0)
    $firstProcCodeIndex = $encount;

   // this sorts the details more or less chronologically:
   ksort($cdata['dtl']);
   foreach ($cdata['dtl'] as $dkey => $ddata) {
    $ddate = substr($dkey, 0, 10);
    if (preg_match('/^(\d\d\d\d)(\d\d)(\d\d)\s*$/', $ddate, $matches)) {
     $ddate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
    }
    $tmpchg = "";
    $tmpadj = "";
    /*****************************************************************
    if ($ddata['chg'] > 0)
     $tmpchg = $ddata['chg'];
    else if ($ddata['chg'] < 0)
     $tmpadj = 0 - $ddata['chg'];
    *****************************************************************/
    if ($ddata['chg'] != 0) {
      if (isset($ddata['rsn'])) $tmpadj = 0 - $ddata['chg'];
      else $tmpchg = $ddata['chg'];
    }
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td class="detail">
   <?php echo $dispcode; $dispcode = "" ?>
  </td>
  <td class="detail" align="right">
   <?php bucks($tmpchg) ?>
  </td>
  <td class="detail" align="right">
   &nbsp;
  </td>
  <td class="detail">
   <?php
    if (isset($ddata['plv'])) {
      if (!$ddata['plv']) echo 'Pt/';
      else echo 'Ins' . $ddata['plv'] . '/';
    }
    echo $ddata['src'];
   ?>
  </td>
  <td class="detail">
   <?php echo $ddate ?>
  </td>
  <td class="detail">
   <?php bucks($ddata['pmt']) ?>
  </td>
  <td class="detail">
   <?php bucks($tmpadj) ?>
  </td>
  <td class="detail">
   <?php echo $ddata['rsn'] ?>
  </td>
<?php if ($ALLOW_DELETE) { ?>
  <td class="detail">
<?php if (!empty($ddata['arseq'])) { ?>
   <input type="checkbox" name="form_del[<?php echo $ddata['arseq']; ?>]" />
<?php } else { ?>
   &nbsp;
<?php } ?>
  </td>
<?php } ?>
 </tr>
<?php
   } // end of prior detail line
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td class="detail">
   <?php echo $dispcode; $dispcode = "" ?>
  </td>
  <td class="detail" align="right">
   &nbsp;
  </td>
  <td class="detail" align="right">
   <input type="hidden" name="form_line[<?php echo $code ?>][bal]" value="<?php bucks($cdata['bal']) ?>">
   <input type="hidden" name="form_line[<?php echo $code ?>][ins]" value="<?php echo $cdata['ins'] ?>">
   <?php printf("%.2f", $cdata['bal']) ?>&nbsp;
  </td>
  <td class="detail">

<?php if (!$INTEGRATED_AR) { ?>
   <input type="text" name="form_line[<?php echo $code ?>][src]" size="10"
    style="background-color:<?php echo $bgcolor ?>" />
    <!-- title="NM=notmet, CI=coins, CP=copay, NE=notelig, IT=insterm, PF=ptfull, PP=ptpart" -->
<?php } ?>

  </td>
  <td class="detail">

<?php if (!$INTEGRATED_AR) { ?>
   <input type="text" name="form_line[<?php echo $code ?>][date]" size="10"
    style="background-color:<?php echo $bgcolor ?>" />
<?php } ?>

  </td>
  <td class="detail">
   <input type="text" name="form_line[<?php echo $code ?>][pay]" size="10"
    style="background-color:<?php echo $bgcolor ?>" 
    onKeyUp="updateFields(document.forms[0]['form_line[<?php echo $code ?>][pay]'], 
                          document.forms[0]['form_line[<?php echo $code ?>][adj]'],
                          document.forms[0]['form_line[<?php echo $code ?>][bal]'],
                          document.forms[0]['form_line[CO-PAY][bal]'],
                          <?php echo ($firstProcCodeIndex == $encount) ? 1 : 0 ?>)"/>
  </td>
  <td class="detail">
   <input type="text" name="form_line[<?php echo $code ?>][adj]" size="10"
    value='<?php echo $totalAdjAmount ?>' 
    style="background-color:<?php echo $bgcolor ?>" />
   &nbsp; <a href="" onclick="return writeoff('<?php echo $code ?>')">W</a>
  </td>
  <td class="detail">
   <select name="form_line[<?php echo $code ?>][reason]"
    style="background-color:<?php echo $bgcolor ?>">
<?php
// Adjustment reasons are now taken from the list_options table.

// ViCarePlus :: Fix for Aujustment Reasons Default :: Sep 27,2010
// Issue :: Adjustment reasons - Default value is not getting selected in the EOB Posting-Invoice Page
// When a Default value for "Reasons" is set from Administration -> Lists -> Adjustment_reasons, 
// it is not getting reflected in the EOB Posting Invoice Page. Hence, a minor modification is done
// in the query to fix this issue.  

//echo "    <option value=''></option>\n";
/*$ores = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'adjreason' ORDER BY seq, title");
while ($orow = sqlFetchArray($ores)) {
  echo "    <option value='" . addslashes($orow['option_id']) . "'";
  echo ">" . $orow['title'] . "</option>\n";
}*/
$ores = sqlStatement("SELECT option_id, title,is_default FROM list_options " .
  "WHERE list_id = 'adjreason'  ORDER BY is_default DESC, seq, title ASC");
while ($orow = sqlFetchArray($ores)) {
  echo "    <option value='" . addslashes($orow['option_id']) . "'";
  echo ">" . $orow['title'] . "</option>\n";
}
echo "    <option value=''></option>\n";
?>

   </select>
<?php
    // TBD: Maybe a comment field would be good here, for appending
    // to the reason.
?>
  </td>

<?php if ($ALLOW_DELETE) { ?>
  <td class="detail">
   &nbsp;
  </td>
<?php } ?>

 </tr>
<?php
  } // end of code
  if (!$INTEGRATED_AR) SLClose();
?>

</table>
</form>
</center>
<script language="JavaScript">
 var f1 = opener.document.forms[0];
 var f2 = document.forms[0];
 if (f1.form_source) {
<?php
  if ($INTEGRATED_AR) {
    // These support creation and lookup of ar_session table entries:
    echo "  f2.form_reference.value         = f1.form_source.value;\n";
    echo "  f2.form_check_date.value        = f1.form_paydate.value;\n";
    echo "  //f2.form_deposit_date.value      = f1.form_deposit_date.value;\n";
    echo "  if (f1.form_deposit_date.value != '')\n";
    echo "     f2.form_deposit_date.value      = f1.form_deposit_date.value;\n";
    echo "  else\n";
    echo "     f2.form_deposit_date.value      = getFormattedToday();\n";
    echo "  f2.form_payer_id.value          = f1.form_payer_id.value;\n";
    echo "  f2.form_pay_total.value         = f1.form_amount.value;\n";
    echo "  f2.form_orig_reference.value    = f1.form_source.value;\n";
    echo "  f2.form_orig_check_date.value   = f1.form_paydate.value;\n";
    echo "  f2.form_orig_deposit_date.value = f1.form_deposit_date.value;\n";

    // While I'm thinking about it, some notes about eob sessions.
    // If they do not have all of the session key fields in the search
    // page, then show a warning at the top of the invoice page.
    // Also when they go to save the invoice page and a session key
    // field has changed, alert them to that and allow a cancel.

    // Another point... when posting EOBs, the incoming payer ID might
    // not match the payer ID for the patient's insurance.  This is
    // because the same payer might be entered more than once into the
    // insurance_companies table.  I don't think it matters much.
  }
  else {
    foreach ($codes as $code => $cdata) {
      echo "  f2['form_line[$code][src]'].value  = f1.form_source.value;\n";
      echo "  f2['form_line[$code][date]'].value = f1.form_paydate.value;\n";
    }
  }
?>
 }
 setins("Ins1");
</script>
</body>
</html>
