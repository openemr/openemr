<?php
// Copyright (C) 2006, 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("drugs.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");

function QuotedOrNull($fld) {
  if ($fld) return "'$fld'";
  return "NULL";
}

function checkWarehouseUsed($warehouse_id) {
  global $drug_id;
  $row = sqlQuery("SELECT count(*) AS count FROM drug_inventory WHERE " .
    "drug_id = '$drug_id' AND " .
    "destroy_date IS NULL AND warehouse_id = '$warehouse_id'");
  return $row['count'];
}

// Generate a <select> list of warehouses.
// If multiple lots are not allowed for this product, then restrict the
// list to warehouses that are unused for the product.
// Returns the number of warehouses allowed.
// For these purposes the "unassigned" option is considered a warehouse.
//
function genWarehouseList($tag_name, $currvalue, $title, $class='') {
  global $drug_id;

  $drow = sqlQuery("SELECT allow_multiple FROM drugs WHERE drug_id = '$drug_id'");
  $allow_multiple = $drow['allow_multiple'];

  $lres = sqlStatement("SELECT * FROM list_options " .
    "WHERE list_id = 'warehouse' ORDER BY seq, title");

  echo "<select name='$tag_name' id='$tag_name'";
  if ($class) echo " class='$class'";
  echo " title='$title'>";

  $got_selected = FALSE;
  $count = 0;

  if ($allow_multiple /* || !checkWarehouseUsed('') */) {
    echo "<option value=''>" . xl('Unassigned') . "</option>";
    ++$count;
  }

  while ($lrow = sqlFetchArray($lres)) {
    $whid = $lrow['option_id'];
    if ($whid != $currvalue && !$allow_multiple && checkWarehouseUsed($whid)) continue;

    echo "<option value='$whid'";
    if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
        (strlen($currvalue)  > 0 && $whid == $currvalue))
    {
      echo " selected";
      $got_selected = TRUE;
    }
    echo ">" . $lrow['title'] . "</option>\n";

    ++$count;
  }

  if (!$got_selected && strlen($currvalue) > 0) {
    $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);
    echo "<option value='$currescaped' selected>* $currescaped *</option>";
    echo "</select>";
    echo " <font color='red' title='" .
      xl('Please choose a valid selection from the list.') . "'>" .
      xl('Fix this') . "!</font>";
  }
  else {
    echo "</select>";
  }

  return $count;
}

$drug_id = $_REQUEST['drug'] + 0;
$lot_id  = $_REQUEST['lot'] + 0;
$info_msg = "";

if (!acl_check('admin', 'drugs')) die(xl('Not authorized'));
if (!$drug_id) die(xl('Drug ID missing!'));
?>
<html>
<head>
<?php html_header_show();?>
<title><?php echo $lot_id ? xl("Edit") : xl("Add New"); xl('Lot','e',' '); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<style  type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function validate() {
  var f = document.forms[0];
  if (f.form_source_lot.value == '0' && f.form_lot_number.value.search(/\S/) < 0) {
   alert('<?php xl('A lot number is required!','e'); ?>');
   return false;
  }
  return true;
 }

</script>

</head>

<body class="body_top">
<?php
// If we are saving, then save and close the window.
//
if ($_POST['form_save'] || $_POST['form_delete']) {

  $form_quantity = formData('form_quantity') + 0;

  if ($lot_id) {
    if ($_POST['form_save']) {
      sqlStatement("UPDATE drug_inventory SET " .
        "lot_number = '"   . formData('form_lot_number')    . "', " .
        "manufacturer = '" . formData('form_manufacturer')  . "', " .
        "expiration = "    . QuotedOrNull($form_expiration) . ", "  .
        "vendor_id = '"    . formData('form_vendor_id')     . "', " .
        "warehouse_id = '" . formData('form_warehouse_id')  . "', " .
        "on_hand = on_hand + '" . $form_quantity            . "' "  .
        "WHERE drug_id = '$drug_id' AND inventory_id = '$lot_id'");
    }
    else {
      sqlStatement("DELETE FROM drug_inventory WHERE drug_id = '$drug_id' " .
        "AND inventory_id = '$lot_id'");
    }
  }
  else {
    $lot_id = sqlInsert("INSERT INTO drug_inventory ( " .
      "drug_id, lot_number, manufacturer, expiration, " .
      "vendor_id, warehouse_id, on_hand " .
      ") VALUES ( " .
      "'$drug_id', "                            .
      "'" . formData('form_lot_number')   . "', " .
      "'" . formData('form_manufacturer') . "', " .
      QuotedOrNull($form_expiration)      . ", "  .
      "'" . formData('form_vendor_id')    . "', " .
      "'" . formData('form_warehouse_id') . "', " .
      "'" . $form_quantity                . "' "  .
      ")");
  }

  // Create the corresponding drug_sales transaction.
  if ($_POST['form_save'] && $form_quantity) {
    $form_source_lot = formData('form_source_lot') + 0;
    $form_cost = sprintf('%0.2f', formData('form_cost'));
    sqlInsert("INSERT INTO drug_sales ( " .
      "drug_id, inventory_id, prescription_id, pid, encounter, user, " .
      "sale_date, quantity, fee, xfer_inventory_id " .
      ") VALUES ( " .
      "'$drug_id', '$lot_id', '0', '0', '0', " .
      "'" . $_SESSION['authUser'] . "', " .
      "'" . date('Y-m-d')         . "', " .
      "'" . (0 - $form_quantity)  . "', " .
      "'" . (0 - $form_cost)      . "', " .
      "'$form_source_lot' )");

    // If this is a transfer then reduce source QOH, and also copy some
    // fields from the source when they are missing.
    if ($form_source_lot) {
      sqlStatement("UPDATE drug_inventory SET " .
        "on_hand = on_hand - '$form_quantity' " .
        "WHERE inventory_id = '$form_source_lot'");

      foreach (array('lot_number', 'manufacturer', 'expiration', 'vendor_id') as $item) {
        sqlStatement("UPDATE drug_inventory AS di1, drug_inventory AS di2 " .
          "SET di1.$item = di2.$item " .
          "WHERE di1.inventory_id = '$lot_id' AND " .
          "di2.inventory_id = '$form_source_lot' AND " .
          "( di1.$item IS NULL OR di1.$item = '' OR di1.$item = '0' )");
      }
    }
  }

  // Close this window and redisplay the updated list of drugs.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
}

if ($lot_id) {
  $row = sqlQuery("SELECT * FROM drug_inventory WHERE drug_id = '$drug_id' " .
    "AND inventory_id = '$lot_id'");
}
?>

<form method='post' name='theform' action='add_edit_lot.php?drug=<?php echo $drug_id ?>&lot=<?php echo $lot_id ?>'
 onsubmit='return validate()'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' width='1%' nowrap><b><?php xl('Lot Number','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_lot_number' maxlength='40' value='<?php echo $row['lot_number'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Manufacturer','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_manufacturer' maxlength='250' value='<?php echo $row['manufacturer'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Expiration','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_expiration' id='form_expiration'
    value='<?php echo $row['expiration'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title=<?php xl('yyyy-mm-dd date of expiration','e','\'','\''); ?> />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_expiration' border='0' alt='[?]' style='cursor:pointer'
    title=<?php xl('Click here to choose a date','e','\'','\''); ?>>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Vendor','e'); ?>:</b></td>
  <td>
<?php
// Address book entries for vendors.
generate_form_field(array('data_type' => 14, 'field_id' => 'vendor_id',
  'list_id' => '', 'edit_options' => 'V',
  'description' => xl('Address book entry for the vendor')),
  $row['vendor_id']);
?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Warehouse','e'); ?>:</b></td>
  <td>
<?php
  // generate_select_list("form_warehouse_id", 'warehouse',
  //   $row['warehouse_id'], xl('Location of this lot'), xl('Unassigned'));
  if (!genWarehouseList("form_warehouse_id", $row['warehouse_id'],
    xl('Location of this lot')))
  {
    $info_msg = xl('This product allows only one lot per warehouse.');
  }
?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('On Hand','e'); ?>:</b></td>
  <td>
   <?php echo $row['on_hand'] + 0; ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap>&nbsp;</td>
  <td>
   <b><?php xl('Use the fields below for a purchase or transfer.','e'); ?></b>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Quantity','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_quantity' maxlength='7' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Total Cost','e'); ?>:</b></td>
  <td>
   <input type='text' size='7' name='form_cost' maxlength='12' />
   (for purchase only)
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php xl('Source Lot','e'); ?>:</b></td>
  <td>
   <select name='form_source_lot'>
    <option value='0'> </option>
<?php
$lres = sqlStatement("SELECT " .
  "di.inventory_id, di.lot_number, di.on_hand, lo.title " .
  "FROM drug_inventory AS di " .
  "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
  "lo.option_id = di.warehouse_id " .
  "WHERE di.drug_id = '$drug_id' AND di.on_hand > 0 AND di.destroy_date IS NULL " .
  "ORDER BY di.lot_number, lo.title, di.inventory_id");
while ($lrow = sqlFetchArray($lres)) {
  echo "<option value='" . $lrow['inventory_id'] . "'>";
  echo $lrow['lot_number'];
  if (!empty($lrow['title'])) echo " / " . $lrow['title'];
  echo " (" . $lrow['on_hand'] . ")";
  echo "</option>\n";
}
?>
   </select>
   (for transfer only)
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php xl('Save','e'); ?>' />

<?php if ($lot_id) { ?>
&nbsp;
<input type='button' value='<?php xl('Destroy...','e'); ?>'
 onclick="window.location.href='destroy_lot.php?drug=<?php echo $drug_id ?>&lot=<?php echo $lot_id ?>'" />
<?php } ?>

&nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />
</p>

</center>
</form>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_expiration", ifFormat:"%Y-%m-%d", button:"img_expiration"});
<?php
if ($info_msg) {
  echo " alert('$info_msg');\n";
  echo " window.close();\n";
}
?>
</script>
</body>
</html>
