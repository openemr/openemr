<?
  // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  // This is a report of receipts by payment method.  It's most useful for
  // sites using pos_checkout.php (e.g. weight loss clinics) because this
  // plugs a payment method like Cash, Check, VISA, etc. into the "source"
  // column of the SQL-Ledger acc_trans table.

  include_once("../globals.php");
  include_once("../../library/patient.inc");
  include_once("../../library/sql-ledger.inc");
  include_once("../../library/acl.inc");

  function bucks($amount) {
    if ($amount)
      printf("%.2f", $amount);
  }

  if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

  SLConnect();

  $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
  $form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
  $form_use_edate = $_POST['form_use_edate'];
?>
<html>
<head>
<title><?xl('Receipts by Payment Method','e')?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?xl('Receipts by Payment Method','e')?></h2>

<form method='post' action='receipts_by_method_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   &nbsp;<select name='form_use_edate'>
    <option value='0'><?php xl('Payment Date','e'); ?></option>
    <option value='1'<?php if ($form_use_edate) echo ' selected' ?>><?php xl('Invoice Date','e'); ?></option>
   </select>
   &nbsp;<?xl('From:','e')?>
   <input type='text' name='form_from_date' size='10' value='<? echo $form_from_date; ?>' title='MM/DD/YYYY'>
   &nbsp;<?php xl('To:','e'); ?>
   <input type='text' name='form_to_date' size='10' value='<? echo $form_to_date; ?>' title='MM/DD/YYYY'>
   &nbsp;
   <input type='checkbox' name='form_details' value='1'<? if ($_POST['form_details']) echo " checked"; ?>><?xl('Details','e')?>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?xl('Refresh','e')?>">
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
   <?xl('Method','e')?>
  </td>
  <td class="dehead">
   <?xl('Date','e')?>
  </td>
  <td class="dehead">
   <?xl('Invoice','e')?>
  </td>
  <td class="dehead">
   <?xl('Procedure','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Amount','e')?>
  </td>
 </tr>
<?
  $chart_id_cash = SLQueryValue("select id from chart where accno = '$sl_cash_acc'");
  if ($sl_err) die($sl_err);

  if ($_POST['form_refresh']) {
    $from_date = $form_from_date;
    $to_date   = $form_to_date;

    $query = "select acc_trans.amount, acc_trans.transdate, acc_trans.memo, " .
      "acc_trans.source, ar.invnumber, ar.employee_id from acc_trans, ar where " .
      "acc_trans.chart_id = $chart_id_cash and " .
      "ar.id = acc_trans.trans_id and ";

    if ($form_use_edate) {
      $query .= "ar.transdate >= '$from_date' and " .
      "ar.transdate <= '$to_date'";
    } else {
      $query .= "acc_trans.transdate >= '$from_date' and " .
      "acc_trans.transdate <= '$to_date'";
    }

    $query .= " order by acc_trans.source, acc_trans.transdate, ar.invnumber, acc_trans.memo";

    echo "<!-- $query -->\n";

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);

    $paymethod   = "";
    $paymethodleft = "";
    $methodtotal = 0;
    $grandtotal  = 0;

    for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
      $row = SLGetRow($t_res, $irow);
      $rowamount = 0 - $row['amount'];

      // Extract only the first word as the payment method because any following
      // text will be some petty detail like a check number.
      $rowmethod = substr($row['source'], 0, strcspn($row['source'], ' /'));
      if (! $rowmethod) $rowmethod = 'Unknown';

      if ($paymethod != $rowmethod) {
        if ($paymethod) {
          // Print method total.
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="4">
   <? echo xl('Total for ') . $paymethod ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($methodtotal) ?>
  </td>
 </tr>
<?
        }
        $methodtotal = 0;
        $paymethod = $rowmethod;
        $paymethodleft = $paymethod;
      }

      if ($_POST['form_details']) {
?>

 <tr>
  <td class="detail">
   <? echo $paymethodleft; $paymethodleft = "&nbsp;" ?>
  </td>
  <td class="dehead">
   <? echo $row['transdate'] ?>
  </td>
  <td class="detail">
   <? echo $row['invnumber'] ?>
  </td>
  <td class="dehead">
   <? echo $row['memo'] ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($rowamount) ?>
  </td>
 </tr>
<?
      }
      $methodtotal += $rowamount;
      $grandtotal  += $rowamount;
    }
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="4">
   <?echo xl('Total for ') . $paymethod ?>
  </td>
  <td class="dehead" align="right">
   <? bucks($methodtotal) ?>
  </td>
 </tr>

 <tr bgcolor="#ffdddd">
  <td class="detail" colspan="4">
   <?xl('Grand Total','e')?>
  </td>
  <td class="dehead" align="right">
   <? bucks($grandtotal) ?>
  </td>
 </tr>

<?
  }
  SLClose();
?>

</table>
</form>
</center>
</body>
</html>
