<?
  // This is the second of two pages to support posting of EOBs.
  // The first is sl_eob_search.php.

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/sql-ledger.inc");

  $debug = 0; // set to 1 for debugging mode

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
  function addLineItem($invid, $serialnumber, $amount, $adjdate, $insplan) {
    global $sl_err, $services_id, $debug;
    $adjdate = fixDate($adjdate);
    $description = "Adjustment $adjdate";
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
          addLineItem($trans_id, $code, 0 - $thisadj, $thisdate, $thisins);
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
      echo "<script language='JavaScript'>\n";
      echo " var tmp = opener.document.forms[0].form_amount.value - $paytotal;\n";
      echo " opener.document.forms[0].form_amount.value = Number(tmp).toFixed(2);\n";
    } else {
      echo "<script language='JavaScript'>\n";
    }
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

  // Request all cash entries belonging to the invoice.
  $atres = SLQuery("select * from acc_trans where trans_id = $trans_id and chart_id = $chart_id_cash");
  if ($sl_err) die($sl_err);

  // Deduct payments for each procedure code from the respective balance owed.
  $codes = array();
  for ($irow = 0; $irow < SLRowCount($atres); ++$irow) {
    $row = SLGetRow($atres, $irow);
    $code     = strtoupper($row['memo']);
    $ins_id   = $row['project_id'];
    if (! $code) $code = "Unknown";
    $amount   = $row['amount'];
    $codes[$code]['bal'] += $amount; // amount is negative for a payment
    if ($ins_id)
      $codes[$code]['ins'] = $ins_id;
    // echo "<!-- $code $chart_id $amount -->\n"; // debugging
  }

  // Request all line items with money belonging to the invoice.
  $inres = SLQuery("select * from invoice where trans_id = $trans_id and sellprice != 0");
  if ($sl_err) die($sl_err);

  // Add charges and adjustments for each procedure code into its total and balance.
  for ($irow = 0; $irow < SLRowCount($inres); ++$irow) {
    $row = SLGetRow($inres, $irow);
    $amount   = $row['sellprice'];
    $ins_id   = $row['project_id'];

    $code = "Unknown";
    if (preg_match("/([A-Za-z0-9]\d\d\S*)/", $row['serialnumber'], $matches)) {
      $code = strtoupper($matches[1]);
    }
    else if (preg_match("/([A-Za-z0-9]\d\d\S*)/", $row['description'], $matches)) {
      $code = strtoupper($matches[1]);
    }

    $codes[$code]['chg'] += $amount;
    $codes[$code]['bal'] += $amount;

    if ($ins_id)
      $codes[$code]['ins'] = $ins_id;
  }
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
  <td colspan="2" rowspan="4">
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
