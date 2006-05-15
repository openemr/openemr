<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("drugs.inc.php");

 // Check authorization.
 $thisauth = acl_check('admin', 'drugs');
 if (!$thisauth) die("Not authorized.");

 // get drugs
 $res = sqlStatement("SELECT d.*, " .
  "di.lot_number, di.expiration, di.manufacturer, di.on_hand " .
  "FROM drugs AS d " .
  "LEFT OUTER JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
  "AND di.on_hand != 0 " .
  "ORDER BY d.name, d.drug_id, di.expiration, di.lot_number");
?>
<html>

<head>

<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>
<title><? xl('Drug Inventory','e'); ?></title>

<style>
tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
</style>

<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

// callback from add_edit_drug.php or add_edit_drug_inventory.php:
function refreshme() {
 location.reload();
}

// Process click on drug title.
function dodclick(id) {
 dlgopen('add_edit_drug.php?drug=' + id, '_blank', 600, 475);
}

// Process click on drug QOO or lot.
function doiclick(id, lot) {
 dlgopen('add_edit_lot.php?drug=' + id + '&lot=' + lot, '_blank', 600, 475);
}

</script>

</head>

<body <?echo $top_bg_line;?>>
<form method='post' action='drug_inventory.php'>

<table width='100%' cellpadding='1' cellspacing='2'>
 <tr class='head'>
  <td title='Click to edit'><? xl('Identifier','e'); ?></td>
  <td><? xl('Name','e'); ?></td>
  <td><? xl('NDC','e'); ?></td>
  <td><? xl('Form','e'); ?></td>
  <td><? xl('Size','e'); ?></td>
  <td><? xl('Unit','e'); ?></td>
  <td title='Click to receive (add) new lot'><? xl('Add','e'); ?></td>
  <td title='Click to edit'><? xl('Lot','e'); ?></td>
  <td><? xl('QOH','e'); ?></td>
  <td><? xl('Expires','e'); ?></td>
 </tr>
<?
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
    htmlentities($row['selector']) . "</a></td>\n";
   echo "  <td>" . htmlentities($row['name']) . "</td>\n";
   echo "  <td>" . htmlentities($row['ndc_number']) . "</td>\n";
   echo "  <td>" . $form_array[$row['form']] . "</td>\n";
   echo "  <td>" . $row['size'] . "</td>\n";
   echo "  <td>" . $unit_array[$row['unit']] . "</td>\n";
   echo "  <td onclick='doiclick($lastid,\"\")'>" .
    "<a href='' onclick='return false'>Add</a></td>\n";
  } else {
   echo " <tr class='detail' bgcolor='$bgcolor'>\n";
   echo "  <td colspan='7'>&nbsp;</td>\n";
  }
  if ($row['lot_number']) {
   $lot_number = htmlentities($row['lot_number']);
   echo "  <td onclick='doiclick($lastid,\"$lot_number\")'>" .
    "<a href='' onclick='return false'>$lot_number</a></td>\n";
   echo "  <td>" . $row['on_hand'] . "</td>\n";
   echo "  <td>" . $row['expiration'] . "</td>\n";
  } else {
   echo "  <td colspan='3'>&nbsp;</td>\n";
  }
  echo " </tr>\n";
 } // end while
?>
</table>

<center><p>
 <input type='button' value='<?xl('Add Drug','e'); ?>' onclick='dodclick(0)' style='background-color:transparent' />
</p></center>

</form>
</body>
</html>
