<?php
  // Copyright (C) 2006-2008 Rod Roark <rod@sunsetsystems.com>
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

  function display_desc($desc) {
    if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
      $desc = $matches[1];
    }
    return $desc;
  }

  if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

  SLConnect();

  $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
  $form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
  $form_facility  = $_POST['form_facility'];

  if ($_POST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=sales_by_item.csv");
    header("Content-Description: File Transfer");
    // CSV headers:
    if ($_POST['form_details']) {
      echo '"Item",';
      echo '"Date",';
      echo '"Invoice",';
      echo '"Qty",';
      echo '"Amount"' . "\n";
    }
    else {
      echo '"Item",';
      echo '"Qty",';
      echo '"Total"' . "\n";
    }
  }
  else {
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Sales by Item','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Sales by Item','e')?></h2>

<form method='post' action='sales_by_item.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
<?php
  // Build a drop-down list of facilities.
  //
  $query = "SELECT id, name FROM facility ORDER BY name";
  $fres = sqlStatement($query);
  echo "   <select name='form_facility'>\n";
  echo "    <option value=''>-- All Facilities --\n";
  while ($frow = sqlFetchArray($fres)) {
    $facid = $frow['id'];
    echo "    <option value='$facid'";
    if ($facid == $form_facility) echo " selected";
    echo ">" . $frow['name'] . "\n";
  }
  echo "   </select>\n";
?>
   &nbsp;<?xl('From:','e')?>
   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;To:
   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;
   <input type='checkbox' name='form_details' value='1'<?php if ($_POST['form_details']) echo " checked"; ?>><?php xl('Details','e') ?>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?php xl('Refresh','e') ?>">
   &nbsp;
   <input type='submit' name='form_csvexport' value="<?php xl('Export to CSV','e') ?>">
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
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
<?php
  } // end not export

  if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
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

      // If a facility was specified then skip invoices whose encounters
      // do not indicate that facility.
      if ($form_facility) {
        list($patient_id, $encounter_id) = explode(".", $row['invnumber']);
        $tmp = sqlQuery("SELECT count(*) AS count FROM form_encounter WHERE " .
          "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
          "facility_id = '$form_facility'");
        if (empty($tmp['count'])) continue;
      }

      // $rowamount = $row['fxsellprice'];
      $rowamount = sprintf('%01.2f', $row['sellprice'] * $row['qty']);

      // Extract only the first word as the payment method because any following
      // text will be some petty detail like a check number.
      $rowproduct = $row['description'];
      if (! $rowproduct) $rowproduct = 'Unknown';

      if ($product != $rowproduct) {
        if ($product) {
          // Print product total.
          if ($_POST['form_csvexport']) {
            if (! $_POST['form_details']) {
              echo '"' . display_desc($product) . '",';
              echo '"' . $productqty            . '",';
              echo '"'; bucks($producttotal); echo '"' . "\n";
            }
          }
          else {
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="3">
   <? echo xl('Total for ') . display_desc($product) ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $productqty; ?>
  </td>
  <td class="dehead" align="right">
   <?php bucks($producttotal); ?>
  </td>
 </tr>
<?php
          } // End not csv export
        }
        $producttotal = 0;
        $productqty = 0;
        $product = $rowproduct;
        $productleft = $product;
      }

      if ($_POST['form_details']) {
        if ($_POST['form_csvexport']) {
          echo '"' . display_desc($product         ) . '",';
          echo '"' . display_desc($row['transdate']) . '",';
          echo '"' . display_desc($row['invnumber']) . '",';
          echo '"' . display_desc($row['qty']      ) . '",';
          echo '"'; bucks($rowamount); echo '"' . "\n";
        }
        else {
?>

 <tr>
  <td class="detail">
   <?php echo display_desc($productleft); $productleft = "&nbsp;"; ?>
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
        } // End not csv export
      }
      $producttotal += $rowamount;
      $grandtotal   += $rowamount;
      $productqty   += $row['qty'];
      $grandqty     += $row['qty'];
    }

    if ($_POST['form_csvexport']) {
      if (! $_POST['form_details']) {
        echo '"' . display_desc($product) . '",';
        echo '"' . $productqty            . '",';
        echo '"'; bucks($producttotal); echo '"' . "\n";
      }
    }
    else {
?>

 <tr bgcolor="#ddddff">
  <td class="detail" colspan="3">
   <?php echo xl('Total for ') . display_desc($product) ?>
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
    } // End not csv export
  }
  SLClose();

  if (! $_POST['form_csvexport']) {
?>

</table>
</form>
</center>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
<?php
  } // End not csv export
?>
