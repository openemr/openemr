<?php
 // Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("drugs.inc.php");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/formatting.inc.php");

 // Check authorization.
 $thisauth = acl_check('admin', 'drugs');
 if (!$thisauth) die(xl('Not authorized'));

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'prod' => 'd.name, d.drug_id, di.expiration, di.lot_number',
  'ndc'  => 'd.ndc_number, d.name, d.drug_id, di.expiration, di.lot_number',
  'form' => 'lof.title, d.name, d.drug_id, di.expiration, di.lot_number',
  'lot'  => 'di.lot_number, d.name, d.drug_id, di.expiration',
  'wh'   => 'lo.title, d.name, d.drug_id, di.expiration, di.lot_number',
  'qoh'  => 'di.on_hand, d.name, d.drug_id, di.expiration, di.lot_number',
  'exp'  => 'di.expiration, d.name, d.drug_id, di.lot_number',
);

// Get the order hash array value and key for this request.
$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'prod';
$orderby = $ORDERHASH[$form_orderby];

 // get drugs
 $res = sqlStatement("SELECT d.*, " .
  "di.inventory_id, di.lot_number, di.expiration, di.manufacturer, " .
  "di.on_hand, lo.title " .
  "FROM drugs AS d " .
  "LEFT JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
  "AND di.destroy_date IS NULL " .
  "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
  "lo.option_id = di.warehouse_id " .
  "LEFT JOIN list_options AS lof ON lof.list_id = 'drug_form' AND " .
  "lof.option_id = d.form " .
  "ORDER BY $orderby");
?>
<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<title><?php  xl('Drug Inventory','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }
</style>

<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

// callback from add_edit_drug.php or add_edit_drug_inventory.php:
function refreshme() {
 location.reload();
}

// Process click on drug title.
function dodclick(id) {
 dlgopen('add_edit_drug.php?drug=' + id, '_blank', 725, 475);
}

// Process click on drug QOO or lot.
function doiclick(id, lot) {
 dlgopen('add_edit_lot.php?drug=' + id + '&lot=' + lot, '_blank', 600, 475);
}

// Process click on a column header for sorting.
function dosort(orderby) {
 var f = document.forms[0];
 f.form_orderby.value = orderby;
 top.restoreSession();
 f.submit();
 return false;
}

</script>

</head>

<body class="body_top">
<form method='post' action='drug_inventory.php'>

<table width='100%' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <td title='<?php echo htmlspecialchars(xl('Click to edit'), ENT_QUOTES); ?>'>
   <a href="#" onclick="return dosort('prod')"
   <?php if ($form_orderby == "prod") echo " style=\"color:#00cc00\""; ?>>
   <?php echo htmlspecialchars(xl('Name')); ?> </a>
  </td>
  <td>
   <?php echo htmlspecialchars(xl('Act')); ?>
  </td>
  <td>
   <a href="#" onclick="return dosort('ndc')"
   <?php if ($form_orderby == "ndc") echo " style=\"color:#00cc00\""; ?>>
   <?php echo htmlspecialchars(xl('NDC')); ?> </a>
  </td>
  <td>
   <a href="#" onclick="return dosort('form')"
   <?php if ($form_orderby == "form") echo " style=\"color:#00cc00\""; ?>>
   <?php echo htmlspecialchars(xl('Form')); ?> </a>
  </td>
  <td>
   <?php echo htmlspecialchars(xl('Size')); ?>
  </td>
  <td>
   <?php echo htmlspecialchars(xl('Unit')); ?>
  </td>
  <td title='<?php echo htmlspecialchars(xl('Click to receive (add) new lot'), ENT_QUOTES); ?>'>
   <?php echo htmlspecialchars(xl('New')); ?>
  </td>
  <td title='<?php echo htmlspecialchars(xl('Click to edit'), ENT_QUOTES); ?>'>
   <a href="#" onclick="return dosort('lot')"
   <?php if ($form_orderby == "lot") echo " style=\"color:#00cc00\""; ?>>
   <?php echo htmlspecialchars(xl('Lot')); ?> </a>
  </td>
  <td>
   <a href="#" onclick="return dosort('wh')"
   <?php if ($form_orderby == "wh") echo " style=\"color:#00cc00\""; ?>>
   <?php echo htmlspecialchars(xl('Warehouse')); ?> </a>
  </td>
  <td>
   <a href="#" onclick="return dosort('qoh')"
   <?php if ($form_orderby == "qoh") echo " style=\"color:#00cc00\""; ?>>
   <?php echo htmlspecialchars(xl('QOH')); ?> </a>
  </td>
  <td>
   <a href="#" onclick="return dosort('exp')"
   <?php if ($form_orderby == "exp") echo " style=\"color:#00cc00\""; ?>>
   <?php echo htmlspecialchars(xl('Expires')); ?> </a>
  </td>
 </tr>
<?php 
 $lastid = "";
 $encount = 0;
 while ($row = sqlFetchArray($res)) {
  if ($lastid != $row['drug_id']) {
   ++$encount;
   $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
   $lastid = $row['drug_id'];
   echo " <tr class='detail' bgcolor='$bgcolor'>\n";
   echo "  <td onclick='dodclick($lastid)'>" .
    "<a href='' onclick='return false'>" .
    htmlentities($row['name']) . "</a></td>\n";
   echo "  <td>" . ($row['active'] ? xl('Yes') : xl('No')) . "</td>\n";
   echo "  <td>" . htmlentities($row['ndc_number']) . "</td>\n";
   echo "  <td>" . 
	generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']) .
	"</td>\n";
   echo "  <td>" . $row['size'] . "</td>\n";
   echo "  <td>" .
	generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['unit']) .
	"</td>\n";
   echo "  <td onclick='doiclick($lastid,0)' title='" . xl('Add new lot and transaction') . "'>" .
    "<a href='' onclick='return false'>" . xl('New') . "</a></td>\n";
  } else {
   echo " <tr class='detail' bgcolor='$bgcolor'>\n";
   echo "  <td colspan='7'>&nbsp;</td>\n";
  }
  if (!empty($row['inventory_id'])) {
   $lot_number = htmlentities($row['lot_number']);
   echo "  <td onclick='doiclick($lastid," . $row['inventory_id'] . ")'>" .
    "<a href='' onclick='return false'>$lot_number</a></td>\n";
   echo "  <td>" . $row['title'] . "</td>\n";
   echo "  <td>" . $row['on_hand'] . "</td>\n";
   echo "  <td>" . oeFormatShortDate($row['expiration']) . "</td>\n";
  } else {
   echo "  <td colspan='4'>&nbsp;</td>\n";
  }
  echo " </tr>\n";
 } // end while
?>
</table>

<center><p>
 <input type='button' value='<?php echo htmlspecialchars(xl('Add Drug')); ?>' onclick='dodclick(0)' style='background-color:transparent' />
</p></center>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />

</form>
</body>
</html>
