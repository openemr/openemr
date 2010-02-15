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

 // get drugs
 $res = sqlStatement("SELECT d.*, SUM(di.on_hand) AS on_hand, " .
  "SUM(ds.quantity) AS sale_quantity, MIN(ds.sale_date) AS min_sale_date " .
  "FROM drugs AS d " .
  "LEFT JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
  "AND di.on_hand != 0 AND di.destroy_date IS NULL " .
  "LEFT JOIN drug_sales AS ds ON ds.sale_date > DATE_SUB(NOW(), INTERVAL 1 YEAR) " .
  "AND ds.pid != 0 " .
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
   <input type="button" value="Print" onclick="window.print()">
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
   <th><?php  xl('Stock Months','e'); ?></th>
  </tr>
 </thead>
 <tbody>
<?php 
$encount = 0;
while ($row = sqlFetchArray($res)) {
  ++$encount;
  $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

  $sale_quantity = $row['sale_quantity'];
  $msd = $row['min_sale_date'];
  $months = (time() - mktime(0, 0, 0, substr($msd,5,2),
    substr($msd,8,2), substr($msd,0,4))) / (60 * 60 * 24 * 30.5);

  $stock_months = '&nbsp;';
  if ($sale_quantity != 0) $stock_months = sprintf('%0.1f',
    $row['on_hand'] * $months / $sale_quantity);

  echo " <tr class='detail' bgcolor='$bgcolor'>\n";
  echo "  <td>" . htmlentities($row['name']) . "</td>\n";
  echo "  <td>" . htmlentities($row['ndc_number']) . "</td>\n";
  echo "  <td>" .
       generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']) .
       "</td>\n";
  echo "  <td align='right'>" . $row['on_hand'] . "</td>\n";
  echo "  <td align='right'>$stock_months</td>\n";
  echo " </tr>\n";
 }
?>
 </tbody>
</table>

</center>
</body>
</html>
