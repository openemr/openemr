<?php
 // Copyright (C) 2008-2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/options.inc.php");
 require_once("$include_root/drugs/drugs.inc.php");

 // Check authorization.
 $thisauth = acl_check('admin', 'drugs');
 if (!$thisauth) die(xl('Not authorized'));

function addWarning($msg) {
  global $warnings;
  if ($warnings) $warnings .= '<br />';
  $warnings .= $msg;
}

if (!empty($_POST['form_days'])) {
  $form_days = $_POST['form_days'] + 0;
}
else {
  $form_days = sprintf('%d', (strtotime(date('Y-m-d')) - strtotime(date('Y-01-01'))) / (60 * 60 * 24) + 1);
}

// get drugs
$res = sqlStatement("SELECT d.*, SUM(di.on_hand) AS on_hand " .
  "FROM drugs AS d " .
  "LEFT JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
  "AND di.on_hand != 0 AND di.destroy_date IS NULL " .
  "WHERE d.active = 1 " .
  "GROUP BY d.name, d.drug_id ORDER BY d.name, d.drug_id");
?>
<html>

<head>
<?php html_header_show(); ?>

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<title><?php  xl('Inventory List','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }
</style>

<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">
</script>

</head>

<body>
<center>

<form method='post' action='inventory_list.php' name='theform'>
<table border='0' cellpadding='5' cellspacing='0' width='98%'>
 <tr>
  <td class='title'>
   <?php xl('Inventory List','e'); ?>
  </td>
  <td class='text' align='right'>
   <?php xl('For the past','e'); ?>
   <input type="input" name="form_days" size='3' value="<?php echo $form_days; ?>" />
   <?php xl('days','e'); ?>&nbsp;
   <input type="submit" value="<?php xl('Refresh','e'); ?>" />&nbsp;
   <input type="button" value="<?php xl('Print','e'); ?>" onclick="window.print()" />
  </td>
 </tr>
</table>
</form>

<table width='98%' cellpadding='2' cellspacing='2'>
 <thead style='display:table-header-group'>
  <tr class='head'>
   <th><?php  xl('Name','e'); ?></th>
   <th><?php  xl('NDC','e'); ?></th>
   <th><?php  xl('Form','e'); ?></th>
   <th align='right'><?php  xl('QOH','e'); ?></th>
   <th align='right'><?php  xl('Reorder','e'); ?></th>
   <th align='right'><?php  xl('Avg Monthly','e'); ?></th>
   <th align='right'><?php  xl('Stock Months','e'); ?></th>
   <th><?php xl('Warnings','e'); ?></th>
  </tr>
 </thead>
 <tbody>
<?php 
$encount = 0;
while ($row = sqlFetchArray($res)) {
  $on_hand = 0 + $row['on_hand'];
  $drug_id = 0 + $row['drug_id'];
  $warnings = '';

  $srow = sqlQuery("SELECT " .
    "SUM(quantity) AS sale_quantity " .
    "FROM drug_sales WHERE " .
    "drug_id = '$drug_id' AND " .
    "sale_date > DATE_SUB(NOW(), INTERVAL $form_days DAY) " .
    "AND pid != 0");

  ++$encount;
  $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

  $sale_quantity = $srow['sale_quantity'];
  $months = $form_days / 30.5;

  $monthly = ($months && $sale_quantity) ?
    sprintf('%0.1f', $sale_quantity / $months) : '&nbsp;';

  $stock_months = '&nbsp;';
  if ($sale_quantity != 0) {
    $stock_months = sprintf('%0.1f', $on_hand * $months / $sale_quantity);
    if ($stock_months < 1.0) {
      addWarning(xl('QOH is less than monthly usage'));
    }
  }

  // Check for reorder point reached.
  if (!empty($row['reorder_point']) && $on_hand <= $row['reorder_point']) {
    addWarning(xl('Reorder point has been reached'));
  }

  // Compute the smallest quantity that might be taken from a lot based on the
  // past 30 days of sales.  If lot combining is allowed this is always 1.
  $min_sale = 1;
  if (!$row['allow_combining']) {
    $sminrow = sqlQuery("SELECT " .
      "MIN(quantity) AS min_sale " .
      "FROM drug_sales WHERE " .
      "drug_id = '$drug_id' AND " .
      "sale_date > DATE_SUB(NOW(), INTERVAL $form_days DAY) " .
      "AND pid != 0 " .
      "AND quantity > 0");
    $min_sale = 0 + $sminrow['min_sale'];
  }

  // Get all lots that we want to issue warnings about.  These are lots
  // expired, soon to expire, or with insufficient quantity for selling.
  $ires = sqlStatement("SELECT * " .
    "FROM drug_inventory WHERE " .
    "drug_id = '$drug_id' AND " .
    "on_hand > 0 AND " .
    "destroy_date IS NULL AND ( " .
    "on_hand < '$min_sale' OR " .
    "expiration IS NOT NULL AND expiration < DATE_ADD(NOW(), INTERVAL 30 DAY) " .
    ") ORDER BY lot_number");

  // Generate warnings associated with individual lots.
  while ($irow = sqlFetchArray($ires)) {
    $lotno = $irow['lot_number'];
    if ($irow['on_hand'] < $min_sale) {
      addWarning(xl('Lot') . " '$lotno' " . xl('quantity seems unusable'));
    }
    if (!empty($irow['expiration'])) {
      $expdays = (int) ((strtotime($irow['expiration']) - time()) / (60 * 60 * 24));
      if ($expdays <= 0) {
        addWarning(xl('Lot') . " '$lotno' " . xl('has expired'));
      }
      else if ($expdays <= 30) {
        addWarning(xl('Lot') . " '$lotno' " . xl('expires in') . " $expdays " . xl('days'));
      }
    }
  }

  echo " <tr class='detail' bgcolor='$bgcolor'>\n";
  echo "  <td>" . htmlentities($row['name']) . "</td>\n";
  echo "  <td>" . htmlentities($row['ndc_number']) . "</td>\n";
  echo "  <td>" .
       generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']) .
       "</td>\n";
  echo "  <td align='right'>" . $row['on_hand'] . "</td>\n";
  echo "  <td align='right'>" . $row['reorder_point'] . "</td>\n";
  echo "  <td align='right'>$monthly</td>\n";
  echo "  <td align='right'>$stock_months</td>\n";
  echo "  <td style='color:red'>$warnings</td>\n";
  echo " </tr>\n";
 }
?>
 </tbody>
</table>

</center>
</body>
</html>
