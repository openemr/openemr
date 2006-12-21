<?php
 // Copyright (C) 2005-2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This is the first of two pages to support posting of EOBs.
 // The second is sl_eob_invoice.php.

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../library/sql-ledger.inc");
 include_once("../../library/invoice_summary.inc.php");
 include_once("../../custom/statement.inc.php");
 include_once("../../library/parse_era.inc.php");
 include_once("../../library/sl_eob.inc.php");

 $DEBUG = 0; // set to 0 for production, 1 to test

 $alertmsg = '';
 $where = '';
 $eraname = '';
 $eracount = 0;

 // This is called back by parse_era() if we are processing X12 835's.
 //
 function era_callback(&$out) {
  global $where, $eracount, $eraname;
  // print_r($out); // debugging
  ++$eracount;
  // $eraname = $out['isa_control_number'];
  $eraname = $out['gs_date'] . '_' . ltrim($out['isa_control_number'], '0') .
    '_' . ltrim($out['payer_id'], '0');
  list($pid, $encounter, $invnumber) = slInvoiceNumber($out);

  if ($pid && $encounter) {
   if ($where) $where .= ' OR ';
   $where .= "invnumber = '$invnumber'";
  }
 }

 function bucks($amount) {
  if ($amount)
   printf("%.2f", $amount);
 }

 $today = date("Y-m-d");

 SLConnect();

 // Print statements if requested.
 //
 if ($_POST['form_print'] && $_POST['form_cb']) {

  $fhprint = fopen($STMT_TEMP_FILE, 'w');

  $where = "";
  foreach ($_POST['form_cb'] as $key => $value) $where .= " OR ar.id = $key";
  $where = substr($where, 4);

  // Sort by patient so that multiple invoices can be
  // represented on a single statement.
  $res = SLQuery("SELECT ar.*, customer.name, " .
   "customer.address1, customer.address2, " .
   "customer.city, customer.state, customer.zipcode, " .
   "substring(trim(both from customer.name) from '% #\"%#\"' for '#') AS lname, " .
   "substring(trim(both from customer.name) from '#\"%#\" %' for '#') AS fname " .
   "FROM ar, customer WHERE ( $where ) AND " .
   "customer.id = ar.customer_id " .
   "ORDER BY lname, fname, ar.customer_id, ar.transdate");
  if ($sl_err) die($sl_err);

  $stmt = array();

  for ($irow = 0; $irow < SLRowCount($res); ++$irow) {
   $row = SLGetRow($res, $irow);

   // Determine the date of service.  An 8-digit encounter number is
   // presumed to be a date of service imported during conversion.
   // Otherwise look it up in the form_encounter table.
   //
   $svcdate = "";
   list($pid, $encounter) = explode(".", $row['invnumber']);
   if (strlen($encounter) == 8) {
    $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
      "-" . substr($encounter, 6, 2);
   } else if ($encounter) {
    $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
     "encounter = $encounter");
    $svcdate = substr($tmp['date'], 0, 10);
   }

   // How many times have we dunned them for this invoice?
   $intnotes = trim($row['intnotes']);
   $duncount = substr_count(strtolower($intnotes), "statement sent");

   // If this is a new patient then print the pending statement
   // and start a new one.  This is an associative array:
   //
   //  cid     = SQL-Ledger customer ID
   //  pid     = OpenEMR patient ID
   //  patient = patient name
   //  amount  = total amount due
   //  adjust  = adjustments (already applied to amount)
   //  duedate = due date of the oldest included invoice
   //  age     = number of days from duedate to today
   //  to      = array of addressee name/address lines
   //  lines   = array of:
   //    dos     = date of service "yyyy-mm-dd"
   //    desc    = description
   //    amount  = charge less adjustments
   //    paid    = amount paid
   //    notice  = 1 for first notice, 2 for second, etc.
   //    detail  = array of details, see invoice_summary.inc.php
   //
   if ($stmt['cid'] != $row['customer_id']) {
    fwrite($fhprint, create_statement($stmt));
    $stmt['cid'] = $row['customer_id'];
    $stmt['pid'] = $pid;
    $stmt['patient'] = $row['name'];
    $stmt['to'] = array($row['name']);
    if ($row['address1']) $stmt['to'][] = $row['address1'];
    if ($row['address2']) $stmt['to'][] = $row['address2'];
    $stmt['to'][] = $row['city'] . ", " . $row['state'] . " " . $row['zipcode'];
    $stmt['lines'] = array();
    $stmt['amount'] = '0.00';
    $stmt['today'] = $today;
    $stmt['duedate'] = $row['duedate'];
   } else {
    // Report the oldest due date.
    if ($row['duedate'] < $stmt['duedate']) {
     $stmt['duedate'] = $row['duedate'];
    }
   }

   $stmt['age'] = round((strtotime($today) - strtotime($stmt['duedate'])) /
    (24 * 60 * 60));

   $invlines = get_invoice_summary($row['id'], true); // true added by Rod 2006-06-09
   foreach ($invlines as $key => $value) {
    $line = array();
    $line['dos']     = $svcdate;
    $line['desc']    = ($key == 'CO-PAY') ? "Patient Payment" : "Procedure $key";
    $line['amount']  = sprintf("%.2f", $value['chg']);
    $line['adjust']  = sprintf("%.2f", $value['adj']);
    $line['paid']    = sprintf("%.2f", $value['chg'] - $value['bal']);
    $line['notice']  = $duncount + 1;
    $line['detail']  = $value['dtl']; // Added by Rod 2006-06-09
    $stmt['lines'][] = $line;
    $stmt['amount']  = sprintf("%.2f", $stmt['amount'] + $value['bal']);
   }

   // Record something in ar.intnotes about this statement run.
   if ($intnotes) $intnotes .= "\n";
   $intnotes = addslashes($intnotes . "Statement sent $today");
   if (! $DEBUG && ! $_POST['form_without']) {
    SLQuery("UPDATE ar SET intnotes = '$intnotes' WHERE id = " . $row['id']);
    if ($sl_err) die($sl_err);
   }
  }

  fwrite($fhprint, create_statement($stmt));

  if ($DEBUG) {
   $alertmsg = xl("Printing skipped; see test output in ").$STMT_TEMP_FILE;
  } else {
   exec("$STMT_PRINT_CMD $STMT_TEMP_FILE");
   if ($_POST['form_without']) {
    $alertmsg = xl("Now printing statements; invoices will not be updated.");
   } else {
    $alertmsg = xl("Now printing statements and updating invoices.");
   }
  }
 }
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?xl('EOB Posting - Search','e')?></title>

<script language="JavaScript">

function checkAll(checked) {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  var ename = f.elements[i].name;
  if (ename.indexOf('form_cb[') == 0)
   f.elements[i].checked = checked;
 }
}

function npopup(pid) {
 window.open('sl_eob_patient_note.php?patient_id=' + pid, '_blank', 'width=500,height=250,resizable=1');
 return false;
}

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<form method='post' action='sl_eob_search.php' enctype='multipart/form-data'>

<table border='0' cellpadding='5' cellspacing='0'>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

 <tr>
  <td colspan='2'>
   &nbsp;
  </td>
  <td>
   <?xl('Source:','e')?>
  </td>
  <td>
   <input type='text' name='form_source' size='10' value='<?php echo $_POST['form_source']; ?>'
    title='<?xl("A check number or claim number to identify the payment","e")?>'>
  </td>
  <td>
   <?xl('Pay Date:','e')?>
  </td>
  <td>
   <input type='text' name='form_paydate' size='10' value='<?php echo $_POST['form_paydate']; ?>'
    title='<?xl("Date of payment mm/dd/yyyy","e")?>'>
  </td>
  <td>
   <?xl('Amount:','e')?>
  </td>
  <td>
   <input type='text' name='form_amount' size='10' value='<?php echo $_POST['form_amount']; ?>'
    title='<?xl("Paid amount that you will allocate","e")?>'>
  </td>
  <td colspan='2' align='right'>
   <a href='sl_eob_help.php' target='_blank'><?xl('Help','e')?></a>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   <?xl('Name:','e')?>
  </td>
  <td>
   <input type='text' name='form_name' size='10' value='<?php echo $_POST['form_name']; ?>'
    title='<?xl("Any part of the patient name","e")?>'>
  </td>
  <td>
   <?xl('Chart ID:','e')?>
  </td>
  <td>
   <input type='text' name='form_pid' size='10' value='<?php echo $_POST['form_pid']; ?>'
    title='<?xl("Patient chart ID","e")?>'>
  </td>
  <td>
   <?xl('Encounter:','e')?>
  </td>
  <td>
   <input type='text' name='form_encounter' size='10' value='<?php echo $_POST['form_encounter']; ?>'
    title='<?xl("Encounter number","e")?>'>
  </td>
  <td>
   <?xl('Svc Date:','e')?>
  </td>
  <td>
   <input type='text' name='form_date' size='10' value='<?php echo $_POST['form_date']; ?>'
    title='<?xl("Date of service mm/dd/yyyy","e")?>'>
  </td>
  <td>
   <?xl('To:','e')?>
  </td>
  <td>
   <input type='text' name='form_to_date' size='10' value='<?php echo $_POST['form_to_date']; ?>'
    title='<?xl("Ending DOS mm/dd/yyyy if you wish to enter a range","e")?>'>
  </td>
  <td>
   <select name='form_category'>
<?php
 foreach (array(xl('Open'), xl('All'), xl('Due Pt'), xl('Due Ins')) as $value) {
  echo "    <option value='$value'";
  if ($_POST['form_category'] == $value) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
  </td>
  <td>
   <input type='submit' name='form_search' value='<?xl("Search","e")?>'>
  </td>
 </tr>

 <!-- Support for X12 835 upload -->
 <tr bgcolor='#ddddff'>
  <td colspan='12'>
   <?xl('Or upload ERA file:','e')?>
   <input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
   <input name="form_erafile" type="file" />
  </td>
 </tr>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

</table>

<?php
  if ($_POST['form_search'] || $_POST['form_print']) {
    $form_name      = trim($_POST['form_name']);
    $form_pid       = trim($_POST['form_pid']);
    $form_encounter = trim($_POST['form_encounter']);
    $form_date      = fixDate($_POST['form_date'], "");
    $form_to_date   = fixDate($_POST['form_to_date'], "");

    $where = "";

    // Handle X12 835 file upload.
    //
    if ($_FILES['form_erafile']['size']) {
      $tmp_name = $_FILES['form_erafile']['tmp_name'];

      // Handle .zip extension if present.  Probably won't work on Windows.
      if (strtolower(substr($_FILES['form_erafile']['name'], -4)) == '.zip') {
        rename($tmp_name, "$tmp_name.zip");
        exec("unzip -p $tmp_name.zip > $tmp_name");
        unlink("$tmp_name.zip");
      }

      echo "<!-- Notes from ERA upload processing:\n";
      $alertmsg .= parse_era($tmp_name, 'era_callback');
      echo "-->\n";
      $erafullname = "$webserver_root/era/$eraname.edi";

      if (is_file($erafullname)) {
        $alertmsg .= "Warning: Set $eraname was already uploaded ";
        if (is_file("$webserver_root/era/$eraname.html"))
          $alertmsg .= "and processed. ";
        else
          $alertmsg .= "but not yet processed. ";
      }
      // if (!move_uploaded_file($_FILES['form_erafile']['tmp_name'], $erafullname)) {
      //   die("Upload failed! $alertmsg");
      // }
      rename($tmp_name, $erafullname);
    }

    if ($eracount) {
      if (! $where) $where = '1 = 2';
    }
    else {
      if ($form_name) {
        // Allow the last name to be followed by a comma and some part of a first name.
        if (preg_match('/^(.*\S)\s*,\s*(.*)/', $form_name, $matches)) {
          $form_name = $matches[2] . '% ' . $matches[1] . '%';
        } else {
          $form_name = "%$form_name%";
        }
        if ($where) $where .= " AND ";
        $where .= "customer.name ILIKE '$form_name'";
      }

      if ($form_pid && $form_encounter) {
        if ($where) $where .= " AND ";
        $where .= "ar.invnumber = '$form_pid.$form_encounter'";
      }
      else if ($form_pid) {
        if ($where) $where .= " AND ";
        $where .= "ar.invnumber LIKE '$form_pid.%'";
      }
      else if ($form_encounter) {
        if ($where) $where .= " AND ";
        $where .= "ar.invnumber like '%.$form_encounter'";
      }

      if ($form_date) {
        if ($where) $where .= " AND ";
        $date1 = substr($form_date, 0, 4) . substr($form_date, 5, 2) .
          substr($form_date, 8, 2);
        if ($form_to_date) {
          $date2 = substr($form_to_date, 0, 4) . substr($form_to_date, 5, 2) .
            substr($form_to_date, 8, 2);
          $where .= "((CAST (substring(ar.invnumber from position('.' in ar.invnumber) + 1 for 8) AS integer) " .
            "BETWEEN '$date1' AND '$date2')";
          $tmp = "date >= '$form_date' AND date <= '$form_to_date'";
        }
        else {
          // This catches old converted invoices where we have no encounters:
          $where .= "(ar.invnumber LIKE '%.$date1'";
          $tmp = "date = '$form_date'";
        }
        // Pick out the encounters from MySQL with the desired DOS:
        $rez = sqlStatement("SELECT pid, encounter FROM form_encounter WHERE $tmp");
        while ($row = sqlFetchArray($rez)) {
          $where .= " OR ar.invnumber = '" . $row['pid'] . "." . $row['encounter'] . "'";
        }
        $where .= ")";
      }

      if (! $where) {
        if ($_POST['form_category'] == 'All') {
          die("At least one search parameter is required if you select All.");
        } else {
          $where = "1 = 1";
        }
      }
    }

    $query = "SELECT ar.id, ar.invnumber, ar.duedate, ar.amount, ar.paid, " .
      "ar.intnotes, ar.notes, ar.shipvia, customer.name, " .
      "substring(trim(both from customer.name) from '% #\"%#\"' for '#') AS lname, " .
      "substring(trim(both from customer.name) from '#\"%#\" %' for '#') AS fname " .
      "FROM ar, customer WHERE ( $where ) AND customer.id = ar.customer_id ";
    if ($_POST['form_category'] != 'All' && !$eracount) {
      $query .= "AND ar.amount != ar.paid ";
      // if ($_POST['form_category'] == 'Due') {
      //   $query .= "AND ar.duedate <= CURRENT_DATE ";
      // }
    }
    $query .= "ORDER BY lname, fname, ar.invnumber";

    echo "<!-- $query -->\n"; // debugging

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);

    $num_invoices = SLRowCount($t_res);
    if ($eracount && $num_invoices != $eracount) {
      $alertmsg .= "Of $eracount remittances, there are $num_invoices " .
        "matching claims in OpenEMR. ";
    }
?>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   &nbsp;<?xl('Patient','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Invoice','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Svc Date','e')?>
  </td>
  <td class="dehead">
   &nbsp;<?xl('Due Date','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Amount','e')?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?xl('Paid','e')?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?xl('Balance','e')?>&nbsp;
  </td>
  <td class="dehead" align="center">
   <?xl('Prv','e')?>
  </td>
<?php if (!$eracount) { ?>
  <td class="dehead" align="center">
   <?xl('Sel','e')?>
  </td>
<?php } ?>
 </tr>

<?php
    $orow = -1;
    for ($irow = 0; $irow < $num_invoices; ++$irow) {
      $row = SLGetRow($t_res, $irow);

      // $duncount was originally supposed to be the number of times that
      // the patient was sent a statement for this invoice.
      //
      $duncount = substr_count(strtolower($row['intnotes']), "statement sent");

      // But if we have not yet billed the patient, then compute $duncount as a
      // negative count of the number of insurance plans for which we have not
      // yet closed out insurance.
      //
      if (! $duncount) {
        $insgot = strtolower($row['notes']);
        $inseobs = strtolower($row['shipvia']);
        foreach (array('ins1', 'ins2', 'ins3') as $value) {
          if (strpos($insgot, $value) !== false &&
              strpos($inseobs, $value) === false)
            --$duncount;
        }
      }

//    $isdue = ($row['duedate'] <= $today && $row['amount'] > $row['paid']) ? " checked" : "";

      $isdueany = sprintf("%.2f",$row['amount']) > sprintf("%.2f",$row['paid']);

      // An invoice is now due from the patient if money is owed and we are
      // not waiting for insurance to pay.  We no longer look at the due date
      // for this.
      //
      $isduept = ($duncount >= 0 && $isdueany) ? " checked" : "";

      // Skip invoices not in the desired "Due..." category.
      //
      if (substr($_POST['form_category'], 0, 3) == 'Due' && !$isdueany) continue;
      if ($_POST['form_category'] == 'Due Ins' && $duncount >= 0) continue;
      if ($_POST['form_category'] == 'Due Pt'  && $duncount <  0) continue;

      $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");

      // Determine the date of service.  If this was a search parameter
      // then we already know it.  Or an 8-digit encounter number is
      // presumed to be a date of service imported during conversion.
      // Otherwise look it up in the form_encounter table.
      //
      $svcdate = "";
      list($pid, $encounter) = explode(".", $row['invnumber']);
      // if ($form_date) {
      //   $svcdate = $form_date;
      // } else
      if (strlen($encounter) == 8) {
        $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
          "-" . substr($encounter, 6, 2);
      }
      else if ($encounter) {
        $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
          "encounter = $encounter");
        $svcdate = substr($tmp['date'], 0, 10);
      }
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td class="detail">
   &nbsp;<a href="" onclick="return npopup(<?php echo $pid ?>)"
   ><?php echo $row['lname'] . ', ' . $row['fname']; ?></a>
  </td>
  <td class="detail">
   &nbsp;<a href="sl_eob_invoice.php?id=<?php echo $row['id'] ?>"
    target="_blank"><?php echo $row['invnumber'] ?></a>
  </td>
  <td class="detail">
   &nbsp;<?php echo $svcdate ?>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['duedate'] ?>
  </td>
  <td class="detail" align="right">
   <?php bucks($row['amount']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php bucks($row['paid']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php bucks($row['amount'] - $row['paid']) ?>&nbsp;
  </td>
  <td class="detail" align="center">
   <?php echo $duncount ? $duncount : "&nbsp;" ?>
  </td>
<?php if (!$eracount) { ?>
  <td class="detail" align="center">
   <input type='checkbox' name='form_cb[<?php echo($row['id']) ?>]'<?php echo $isduept ?> />
  </td>
<?php } ?>
 </tr>
<?
    }
  }
  SLClose();
?>

</table>

<p>
<?php if ($eracount) { ?>
<input type='button' value='Process ERA File' onclick='processERA()' /> &nbsp;
<?php } else { ?>
<input type='button' value='Select All' onclick='checkAll(true)' /> &nbsp;
<input type='button' value='Clear All' onclick='checkAll(false)' /> &nbsp;
<input type='submit' name='form_print' value='Print Selected Statements' /> &nbsp;
<?php } ?>
<input type='checkbox' name='form_without' value='1' /> <?xl('Without Update','e')?>
</p>

</form>
</center>
<script language="JavaScript">
 function processERA() {
  var f = document.forms[0];
  var debug = f.form_without.checked ? '1' : '0';
  window.open('sl_eob_process.php?eraname=<?php echo $eraname ?>&debug=' + debug, '_blank');
  return false;
 }
<?php
 if ($alertmsg) {
  echo "alert('" . htmlentities($alertmsg) . "');\n";
 }
?>
</script>
</body>
</html>
