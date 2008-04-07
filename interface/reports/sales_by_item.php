<?php
  // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
  //
  // This program is free software; you can redistribute it and/or
  // modify it under the terms of the GNU General Public License
  // as published by the Free Software Foundation; either version 2
  // of the License, or (at your option) any later version.

  // This is a report of sales by item description.  It's driven from
  // SQL-Ledger so as to include all types of invoice items.

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
?>
<html>
<head>
<? html_header_show();?>
<title><?xl('Sales by Item','e')?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?xl('Sales by Item','e')?></h2>

<form method='post' action='sales_by_item.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   &nbsp;<?xl('From:','e')?>
   <input type='text' name='form_from_date' size='10' value='<? echo $form_from_date; ?>' title='MM/DD/YYYY'>
   &nbsp;To:
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
   <?xl('Item','e')?>
  </td>
  <td class="dehead">
   <?xl('Date','e')?>
  </td>
  <td class="dehead">
   <?xl('Invoice','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Qty','e')?>
  </td>
  <td class="dehead" align="right">
   <?xl('Amount','e')?>
  </td>
 </tr>
<?
  if ($_POST['form_refresh']) {
    $from_date = $form_from_date;
    $to_date   = $form_to_date;

    $query = "SELECT ar.invnumber, ar.transdate, " .
      "invoice.description, invoice.qty, invoice.sellprice " .
      "FROM ar, invoice WHERE " .
      "ar.transdate >= '$from_date' AND ar.transdate <= '$to_date' " .
      "AND invoice.trans_id = ar.id " .
      "ORDER BY invoice.description, ar.transdate, ar.id";

    // echo "<!-- $query -->\n"; // debugging

    $t_res = SLQuery($query);
    if ($sl_err) die($sl_err);

    $product = "";
    $productleft = "";
    $producttotal = 0;
    $grandtotal = 0;
    $productqty = 0;
    $grandqty = 0;

    for ($irow = 0; $irow < SLRowCount($t_res); ++$irow) {
      $row = SLGetRow($t_res, $irow);
      // $rowamount = $row['fxsellprice'];
      $rowamount = sprintf('%01.2f', $row['sellprice'] * $row['qty']);

      // Extract only the first word as the payment method because any following
      // text will be some petty detail like a check number.
      $rowproduct = $row['description'];
      if (! $rowproduct) $rowproduct = 'Unknown';

      if ($product != $rowproduct) {
        if ($product) {
          // Print product total.
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="3">
   <? echo xl('Total for ') . $product ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $productqty; ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($producttotal); ?>
  </td>
 </tr>
<?
        }
        $producttotal = 0;
        $productqty = 0;
        $product = $rowproduct;
        $productleft = $product;
      }

      if ($_POST['form_details']) {
?>

 <tr>
  <td class="detail">
   <?php echo $productleft; $productleft = "&nbsp;"; ?>
  </td>
  <td class="dehead">
   <?php echo $row['transdate']; ?>
  </td>
  <td class="detail">
   <?php echo $row['invnumber']; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $row['qty']; ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($rowamount); ?>
  </td>
 </tr>
<?
      }
      $producttotal += $rowamount;
      $grandtotal   += $rowamount;
      $productqty   += $row['qty'];
      $grandqty     += $row['qty'];
    }
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="3">
   <?echo xl('Total for ') . $product ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $productqty; ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($producttotal); ?>
  </td>
 </tr>

 <tr bgcolor="#ffdddd">
  <td class="detail" colspan="3">
   <?php xl('Grand Total','e'); ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $grandqty; ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($grandtotal); ?>
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
