<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
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

 $alertmsg = '';

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
   "customer.city, customer.state, customer.zipcode " .
   "FROM ar, customer WHERE ( $where ) AND " .
   "customer.id = ar.customer_id " .
   "ORDER BY ar.customer_id, ar.transdate");
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
   //  pid     = patient ID
   //  patient = patient name
   //  amount  = total amount due
   //  to      = array of addressee name/address lines
   //  lines   = array of:
   //    dos     = date of service "yyyy-mm-dd"
   //    desc    = description
   //    amount  = charge less adjustments
   //    paid    = amount paid
   //    notice  = 1 for first notice, 2 for second, etc.
   //
   if ($stmt['pid'] != $row['customer_id']) {
    fwrite($fhprint, create_statement($stmt));
    $stmt['pid'] = $row['customer_id'];
    $stmt['patient'] = $row['name'];
    $stmt['to'] = array($row['name']);
    if ($row['address1']) $stmt['to'][] = $row['address1'];
    if ($row['address2']) $stmt['to'][] = $row['address2'];
    $stmt['to'][] = $row['city'] . ", " . $row['state'] . " " . $row['zipcode'];
    $stmt['lines'] = array();
    $stmt['amount'] = '0.00';
    $stmt['today'] = $today;
   }

   $invlines = get_invoice_summary($row['id']);
   foreach ($invlines as $key => $value) {
    $line = array();
    $line['dos']     = $svcdate;
    $line['desc']    = "Procedure $key";
    $line['amount']  = sprintf("%.2f", $value['chg']);
    $line['paid']    = sprintf("%.2f", $value['chg'] - $value['bal']);
    $line['notice']  = $duncount + 1;
    $stmt['lines'][] = $line;
    $stmt['amount']  = sprintf("%.2f", $stmt['amount'] + $value['bal']);
   }

   // Record something in ar.intnotes about this statement run.
   if ($intnotes) $intnotes .= "\n";
   $intnotes = addslashes($intnotes . "Statement sent $today");
   SLQuery("UPDATE ar SET intnotes = '$intnotes' WHERE id = " . $row['id']);
   if ($sl_err) die($sl_err);
  }

  fwrite($fhprint, create_statement($stmt));

  exec("$STMT_PRINT_CMD $STMT_TEMP_FILE");
  $alertmsg = "Now printing statements from $STMT_TEMP_FILE";
 }
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title>EOB Posting - Search</title>

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

<form method='post' action='sl_eob_search.php'>

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
   Source:
  </td>
  <td>
   <input type='text' name='form_source' size='10' value='<? echo $_POST['form_source']; ?>'
    title='A check number or claim number to identify the payment'>
  </td>
  <td>
   Pay Date:
  </td>
  <td>
   <input type='text' name='form_paydate' size='10' value='<? echo $_POST['form_paydate']; ?>'
    title='Date of payment mm/dd/yyyy'>
  </td>
  <td>
   Amount:
  </td>
  <td>
   <input type='text' name='form_amount' size='10' value='<? echo $_POST['form_amount']; ?>'
    title='Paid amount that you will allocate'>
  </td>
  <td colspan='2' align='right'>
   <a href='sl_eob_help.php' target='_blank'>Help</a>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td>
   Name:
  </td>
  <td>
   <input type='text' name='form_name' size='10' value='<? echo $_POST['form_name']; ?>'
    title='Any part of the patient name'>
  </td>
  <td>
   Chart ID:
  </td>
  <td>
   <input type='text' name='form_pid' size='10' value='<? echo $_POST['form_pid']; ?>'
    title='Patient chart ID'>
  </td>
  <td>
   Encounter:
  </td>
  <td>
   <input type='text' name='form_encounter' size='10' value='<? echo $_POST['form_encounter']; ?>'
    title='Encounter number'>
  </td>
  <td>
   Svc Date:
  </td>
  <td>
   <input type='text' name='form_date' size='10' value='<? echo $_POST['form_date']; ?>'
    title='Date of service mm/dd/yyyy'>
  </td>
  <td>
   <select name='form_category'>
<?
 foreach (array('Open', 'All', 'Due') as $value) {
  echo "    <option value='$value'";
  if ($_POST['form_category'] == $value) echo " selected";
  echo ">$value</option>\n";
 }
?>
   </select>
  </td>
  <td>
   <input type='submit' name='form_search' value='Search'>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="10">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   &nbsp;Patient
  </td>
  <td class="dehead">
   &nbsp;Invoice
  </td>
  <td class="dehead">
   &nbsp;Svc Date
  </td>
  <td class="dehead">
   &nbsp;Due Date
  </td>
  <td class="dehead" align="right">
   Amount&nbsp;
  </td>
  <td class="dehead" align="right">
   Paid&nbsp;
  </td>
  <td class="dehead" align="center">
   Prv
  </td>
  <td class="dehead" align="center">
   Sel
  </td>
 </tr>
<?
  if ($_POST['form_search'] || $_POST['form_print']) {
    $form_name      = trim($_POST['form_name']);
    $form_pid       = trim($_POST['form_pid']);
    $form_encounter = trim($_POST['form_encounter']);
    $form_date      = fixDate($_POST['form_date'], "");

    $where = "";

    if ($form_name) {
      if ($where) $where .= " AND ";
      $where .= "customer.name ILIKE '%$form_name%'";
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
      $where .= "(ar.invnumber LIKE '%." . substr($form_date, 0, 4) . substr($form_date, 5, 2) .
        substr($form_date, 8, 2) . "'";
      $rez = sqlStatement("SELECT pid, encounter FROM form_encounter WHERE date = '$form_date'");
      while ($row = sqlFetchArray($rez)) {
        $where .= " OR ar.invnumber = '" . $row['pid'] . "." . $row['encounter'] . "'";
      }
      $where .= ")";
    }

    if (! $where) die("At least one search parameter is required.");

    $query = "SELECT ar.id, ar.invnumber, ar.duedate, ar.amount, ar.paid, " .
      "ar.intnotes, customer.name " .
      "FROM ar, customer WHERE $where AND customer.id = ar.customer_id ";
    if ($_POST['form_category'] != 'All') {
      $query .= "AND ar.amount != ar.paid ";
      if ($_POST['form_category'] == 'Due') {
        $query .= "AND ar.duedate <= CURRENT_DATE ";
      }
    }
    $query .= "ORDER BY customer.name, ar.invnumber";

    // echo "<!-- $query -->\n"; // debugging

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);

    for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
      $row = SLGetRow($t_res, $irow);

      $bgcolor = (($irow & 1) ? "#ffdddd" : "#ddddff");

      // Determine the date of service.  If this was a search parameter
      // then we already know it.  Or an 8-digit encounter number is
      // presumed to be a date of service imported during conversion.
      // Otherwise look it up in the form_encounter table.
      //
      $svcdate = "";
      list($pid, $encounter) = explode(".", $row['invnumber']);
      if ($form_date) {
        $svcdate = $form_date;
      }
      else if (strlen($encounter) == 8) {
        $svcdate = substr($encounter, 0, 4) . "-" . substr($encounter, 4, 2) .
          "-" . substr($encounter, 6, 2);
      }
      else if ($encounter) {
        $tmp = sqlQuery("SELECT date FROM form_encounter WHERE " .
          "encounter = $encounter");
        $svcdate = substr($tmp['date'], 0, 10);
      }

      $duncount = substr_count(strtolower($row['intnotes']), "statement sent");

      $isdue = ($row['duedate'] <= $today && $row['amount'] > $row['paid']) ? " checked" : "";
?>
 <tr bgcolor='<? echo $bgcolor ?>'>
  <td class="detail">
   &nbsp;<a href="" onclick="return npopup(<? echo $pid ?>)"><? echo $row['name'] ?></a>
  </td>
  <td class="detail">
   &nbsp;<a href="sl_eob_invoice.php?id=<? echo $row['id'] ?>"
    target="_blank"><? echo $row['invnumber'] ?></a>
  </td>
  <td class="detail">
   &nbsp;<? echo $svcdate ?>
  </td>
  <td class="detail">
   &nbsp;<? echo $row['duedate'] ?>
  </td>
  <td class="detail" align="right">
   <? bucks($row['amount']) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <? bucks($row['paid']) ?>&nbsp;
  </td>
  <td class="detail" align="center">
   <? echo $duncount ? $duncount : "&nbsp;" ?>
  </td>
  <td class="detail" align="center">
   <input type='checkbox' name='form_cb[<? echo($row['id']) ?>]'<? echo $isdue ?> />
  </td>
 </tr>
<?
    }
  }
  SLClose();
?>

</table>

<p>
<input type='button' value='Select All' onclick='checkAll(true)' /> &nbsp;
<input type='button' value='Clear All' onclick='checkAll(false)' /> &nbsp;
<input type='submit' name='form_print' value='Print Selected Statements' />
</p>

</form>
</center>
<script>
<?
	if ($alertmsg) {
		echo "alert('$alertmsg');\n";
	}
?>
</script>
</body>
</html>
