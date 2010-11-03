<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Report columns:
// Product Name (blank where repeated)
// Warehouse Name (blank where repeated) or Total for Product
// Starting Inventory (detail lines: date)
// Ending Inventory   (detail lines: invoice ID)
// Sales
// Distributions
// Purchases
// Transfers

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/sql-ledger.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

// Specify if product or warehouse is the first column.
$product_first = (!empty($_POST['form_by']) && $_POST['form_by'] == 'w') ? 0 : 1;

$last_warehouse_id = '~';
$last_product_id = 0;

// Get ending inventory for the report's end date.
// Optionally restricts by product ID and/or warehouse ID.
function getEndInventory($product_id = 0, $warehouse_id = '~') {
  global $form_from_date, $form_to_date, $form_product;

  $whidcond = '';
  if ($warehouse_id !== '~') {
    $whidcond = $warehouse_id === '' ?
      "AND ( di.warehouse_id IS NULL OR di.warehouse_id = '' )" :
      "AND di.warehouse_id = '$warehouse_id'";
  }

  $prodcond = '';
  if ($form_product) $product_id = $form_product;
  if ($product_id) {
    $prodcond = "AND di.drug_id = '$product_id'";
  }

  // Get sum of current inventory quantities.
  $eirow = sqlQuery("SELECT sum(di.on_hand) AS on_hand " .
    "FROM drug_inventory AS di WHERE " .
    "( di.destroy_date IS NULL OR di.destroy_date > '$form_from_date' ) " .
    "$prodcond $whidcond");

  // Get sum of sales after the report end date.
  $sarow = sqlQuery("SELECT sum(ds.quantity) AS quantity " .
    "FROM drug_sales AS ds, drug_inventory AS di WHERE " .
    "ds.sale_date > '$form_to_date' AND " .
    "di.inventory_id = ds.inventory_id " .
    "$prodcond $whidcond");

  // Get sum of transfers out after the report end date.
  $xfrow = sqlQuery("SELECT sum(ds.quantity) AS quantity " .
    "FROM drug_sales AS ds, drug_inventory AS di WHERE " .
    "ds.sale_date > '$form_to_date' AND " .
    "di.inventory_id = ds.xfer_inventory_id " .
    "$prodcond $whidcond");

  return $eirow['on_hand'] + $sarow['quantity'] - $xfrow['quantity'];
}

function thisLineItem($product_id, $warehouse_id, $patient_id, $encounter_id,
  $rowprod, $rowwh, $transdate, $qtys, $irnumber='')
{
  global $warehouse, $product, $secqtys, $priqtys, $grandqtys;
  global $whleft, $prodleft; // left 2 columns, blank where repeated
  global $last_warehouse_id, $last_product_id, $product_first;

  $invnumber = empty($irnumber) ? ($patient_id ? "$patient_id.$encounter_id" : "") : $irnumber;

  // Product name for this detail line item.
  if (empty($rowprod)) $rowprod = 'Unnamed Product';

  // Warehouse name for this line item.
  if (empty($rowwh)) $rowwh = 'None';

  if ($warehouse_id != $last_warehouse_id || $product_id != $last_product_id) {
    if (($product_first && $last_warehouse_id != '~') || (!$product_first && $last_product_id)) {

      $secei = getEndInventory($last_product_id, $last_warehouse_id);

      // Print second-column totals.
      if ($_POST['form_csvexport']) {
        if (! $_POST['form_details']) {
          if ($product_first) {
            echo '"'  . display_desc($product)   . '"';
            echo ',"' . display_desc($warehouse) . '"';
          } else {
            echo '"'  . display_desc($warehouse) . '"';
            echo ',"' . display_desc($product)   . '"';
          }
          echo ',"' . ($secei - $secqtys[0] - $secqtys[1] - $secqtys[2] - $secqtys[3] - $secqtys[4]) . '"'; // start inventory
          echo ',"' . $secqtys[0] . '"'; // sales
          echo ',"' . $secqtys[1] . '"'; // distributions
          echo ',"' . $secqtys[2] . '"'; // purchases
          echo ',"' . $secqtys[3] . '"'; // transfers
          echo ',"' . $secqtys[4] . '"'; // adjustments
          echo ',"' . $secei      . '"'; // end inventory
          echo "\n";
        }
      }
      else {
        // Warehouse totals and not export:
?>
 <tr bgcolor="#ddddff">
<?php if ($product_first) { ?>
  <td class="detail">
   <?php echo display_desc($prodleft); $prodleft = "&nbsp;"; ?>
  </td>
  <td class="detail" colspan='3'>
   <?php if ($_POST['form_details']) echo xl('Total for') . ' '; echo display_desc($warehouse); ?>
  </td>
<?php } else { ?>
  <td class="detail">
   <?php echo display_desc($whleft); $whleft = "&nbsp;"; ?>
  </td>
  <td class="detail" colspan='3'>
   <?php if ($_POST['form_details']) echo xl('Total for') . ' '; echo display_desc($product); ?>
  </td>
<?php } ?>
  <td class="dehead" align="right">
   <?php echo $secei - $secqtys[0] - $secqtys[1] - $secqtys[2] - $secqtys[3] - $secqtys[4]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $secqtys[0]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $secqtys[1]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $secqtys[2]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $secqtys[3]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $secqtys[4]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $secei; ?>
  </td>
 </tr>
<?php
      } // End not csv export
    }
    $secqtys = array(0, 0, 0, 0, 0);
    if ($product_first ) {
      $whleft = $warehouse = $rowwh;
      $last_warehouse_id = $warehouse_id;
    } else {
      $prodleft = $product = $rowprod;
      $last_product_id = $product_id;
    }
  }

  if (($product_first && $product_id != $last_product_id) ||
      (!$product_first && $warehouse_id != $last_warehouse_id))
  {
    if (($product_first && $last_product_id) ||
        (!$product_first && $last_warehouse_id != '~'))
    {
      $priei = $product_first ? getEndInventory($last_product_id) :
        getEndInventory(0, $last_warehouse_id);
      // Print first column total.
      if (!$_POST['form_csvexport']) {
?>

 <tr bgcolor="#ffdddd">
  <td class="detail">
   &nbsp;
  </td>
  <td class="detail" colspan="3">
   <?php echo xl('Total for') . ' '; echo display_desc($product_first ? $product : $warehouse); ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $priei - $priqtys[0] - $priqtys[1] - $priqtys[2] - $priqtys[3] - $priqtys[4]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $priqtys[0]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $priqtys[1]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $priqtys[2]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $priqtys[3]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $priqtys[4]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $priei; ?>
  </td>
 </tr>
<?php
      } // End not csv export
    }
    $priqtys = array(0, 0, 0, 0, 0);
    if ($product_first) {
      $prodleft = $product = $rowprod;
      $last_product_id = $product_id;
    } else {
      $whleft = $warehouse = $rowwh;
      $last_warehouse_id = $warehouse_id;
    }
  }

  if ($_POST['form_details'] && $product_id && ($qtys[0] + $qtys[1] + $qtys[2] + $qtys[3] + $qtys[4])) {
    if ($_POST['form_csvexport']) {
      if ($product_first) {
        echo '"'  . display_desc($product )  . '"';
        echo ',"' . display_desc($warehouse) . '"';
      } else {
        echo '"'  . display_desc($warehouse) . '"';
        echo ',"' . display_desc($product)   . '"';
      }
      echo ',"' . oeFormatShortDate(display_desc($transdate)) . '"';
      echo ',"' . display_desc($invnumber) . '"';
      echo ',"' . $qtys[0]             . '"'; // sales
      echo ',"' . $qtys[1]             . '"'; // distributions
      echo ',"' . $qtys[2]             . '"'; // purchases
      echo ',"' . $qtys[3]             . '"'; // transfers
      echo ',"' . $qtys[4]             . '"'; // adjustments
      echo "\n";
    }
    else {
?>
 <tr>
<?php if ($product_first) { ?>
  <td class="detail">
   <?php echo display_desc($prodleft); $prodleft = "&nbsp;"; ?>
  </td>
  <td class="detail">
   <?php echo display_desc($whleft); $whleft = "&nbsp;"; ?>
  </td>
<?php } else { ?>
  <td class="detail">
   <?php echo display_desc($whleft); $whleft = "&nbsp;"; ?>
  </td>
  <td class="detail">
   <?php echo display_desc($prodleft); $prodleft = "&nbsp;"; ?>
  </td>
<?php } ?>
  <td class="dehead">
   <?php echo oeFormatShortDate($transdate); ?>
  </td>
  <td class="detail">
   <?php echo $invnumber; ?>
  </td>
  <td class="detail">
   &nbsp;
  </td>
  <td class="dehead" align="right">
   <?php echo $qtys[0]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $qtys[1]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $qtys[2]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $qtys[3]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $qtys[4]; ?>
  </td>
  <td class="detail">
   &nbsp;
  </td>
 </tr>
<?php
    } // End not csv export
  } // end details
  for ($i = 0; $i < 5; ++$i) {
    $secqtys[$i]   += $qtys[$i];
    $priqtys[$i]   += $qtys[$i];
    $grandqtys[$i] += $qtys[$i];
  }
} // end function

if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_product  = $_POST['form_product'];

if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=inventory_activity.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if ($product_first) {
    echo '"' . xl('Product'  ) . '",';
    echo '"' . xl('Warehouse') . '",';
  } else {
    echo '"' . xl('Warehouse') . '",';
    echo '"' . xl('Product'  ) . '",';
  }
  if ($_POST['form_details']) {
    echo '"' . xl('Date'     ) . '",';
    echo '"' . xl('Invoice'  ) . '",';
    echo '"' . xl('Sales'    ) . '",';
    echo '"' . xl('Distributions') . '",';
    echo '"' . xl('Purchases') . '",';
    echo '"' . xl('Transfers') . '",';
    echo '"' . xl('Adjustments') . '"' . "\n";
  }
  else {
    echo '"' . xl('Start'    ) . '",';
    echo '"' . xl('Sales'    ) . '",';
    echo '"' . xl('Distributions') . '",';
    echo '"' . xl('Purchases') . '",';
    echo '"' . xl('Transfers') . '",';
    echo '"' . xl('Adjustments') . '",';
    echo '"' . xl('End'      ) . '"' . "\n";
  }
} // end export
else {
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Inventory Activity','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Inventory Activity','e')?></h2>

<form method='post' action='inventory_activity.php?product=<?php echo $product_first; ?>'>

<table border='0' cellpadding='3'>

 <tr>
  <td>

   <?php xl('By','e'); ?>:
   <select name='form_by'>
    <option value='p'>Product</option>
    <option value='w'<?php if (!$product_first) echo ' selected'; ?>>Warehouse</option>
   </select>

   &nbsp;
<?php
// Build a drop-down list of products.
//
$query = "SELECT drug_id, name FROM drugs ORDER BY name, drug_id";
$pres = sqlStatement($query);
echo "   <select name='form_product'>\n";
echo "    <option value=''>-- " . xl('All Products') . " --\n";
while ($prow = sqlFetchArray($pres)) {
  $drug_id = $prow['drug_id'];
  echo "    <option value='$drug_id'";
  if ($drug_id == $form_product) echo " selected";
  echo ">" . $prow['name'] . "\n";
}
echo "   </select>\n";
?>

   &nbsp;<?php xl('From','e'); ?>:
   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>

   &nbsp;<?php xl('To','e'); ?>:
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
   <?php echo $product_first ? xl('Product') : xl('Warehouse'); ?>
  </td>
<?php if ($_POST['form_details']) { ?>
  <td class="dehead">
   <?php echo $product_first ? xl('Warehouse') : xl('Product'); ?>
  </td>
  <td class="dehead">
   <?php xl('Date','e'); ?>
  </td>
  <td class="dehead">
   <?php xl('Invoice','e'); ?>
  </td>
<?php } else { ?>
  <td class="dehead" colspan="3">
   <?php echo $product_first ? xl('Warehouse') : xl('Product'); ?>
  </td>
<?php } ?>
  <td class="dehead" align="right" width="8%">
   <?php xl('Start','e'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
   <?php xl('Sales','e'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
   <?php xl('Distributions','e'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
   <?php xl('Purchases','e'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
   <?php xl('Transfers','e'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
   <?php xl('Adjustments','e'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
   <?php xl('End','e'); ?>
  </td>
 </tr>
<?php
} // end not export

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;

  $product   = "";
  $prodleft  = "";
  $warehouse = "";
  $whleft    = "";
  $grandqtys = array(0, 0, 0, 0, 0);
  $priqtys   = array(0, 0, 0, 0, 0);
  $secqtys   = array(0, 0, 0, 0, 0);

  /*******************************************************************
  $query = "SELECT s.sale_date, s.quantity, s.pid, s.encounter, " .
    "s.xfer_inventory_id, d.name, lo.title, di.drug_id, di.warehouse_id " .
    "FROM drug_sales AS s " .
    "JOIN drug_inventory AS di ON di.drug_id = s.drug_id AND di.inventory_id = s.inventory_id " .
    "JOIN drugs AS d ON d.drug_id = s.drug_id " .
    "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
    "lo.option_id = di.warehouse_id " .
    "WHERE s.sale_date >= '$from_date' AND s.sale_date <= '$to_date'";
  *******************************************************************/
  $query = "SELECT s.sale_id, s.sale_date, s.quantity, s.fee, s.pid, s.encounter, " .
    "s.xfer_inventory_id, s.distributor_id, d.name, lo.title, " .
    "di.drug_id, di.warehouse_id, di.inventory_id, fe.invoice_refno " .
    "FROM drug_inventory AS di " .
    "JOIN drugs AS d ON d.drug_id = di.drug_id " .
    "LEFT JOIN drug_sales AS s ON " .
    "s.sale_date >= '$from_date' AND s.sale_date <= '$to_date' AND " .
    "s.drug_id = di.drug_id AND " .
    "( s.inventory_id = di.inventory_id OR s.xfer_inventory_id = di.inventory_id ) " .
    "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
    "lo.option_id = di.warehouse_id " .
    "LEFT JOIN form_encounter AS fe ON fe.pid = s.pid AND fe.encounter = s.encounter " .
    "WHERE ( di.destroy_date IS NULL OR di.destroy_date > '$form_from_date' )";

  // If a product was specified.
  if ($form_product) {
    $query .= " AND di.drug_id = '$form_product'";
  }

  if ($product_first) {
    $query .= " ORDER BY d.name, d.drug_id, lo.title, di.warehouse_id, " .
      "s.sale_date, s.sale_id";
  } else {
    $query .= " ORDER BY lo.title, di.warehouse_id, d.name, d.drug_id, " .
      "s.sale_date, s.sale_id";
  }

  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
    $qtys = array(0, 0, 0, 0, 0);
    if ($row['sale_id']) {
      if ($row['xfer_inventory_id']) {
        // A transfer sale item will appear twice, once with each lot.
        if ($row['inventory_id'] == $row['xfer_inventory_id'])
          $qtys[3] = $row['quantity'];
        else
          $qtys[3] = 0 - $row['quantity'];
      }
      else if ($row['pid'])
        $qtys[0] = 0 - $row['quantity'];
      else if ($row['distributor_id'])
        $qtys[1] = 0 - $row['quantity'];
      else if ($row['fee'] != 0)
        $qtys[2] = 0 - $row['quantity'];
      else // no pid, distributor, source lot or fee: must be an adjustment
        $qtys[4] = 0 - $row['quantity'];
    }
    thisLineItem($row['drug_id'], $row['warehouse_id'], $row['pid'] + 0,
      $row['encounter'] + 0, $row['name'], $row['title'], $row['sale_date'],
      $qtys, $row['invoice_refno']);
  }

  // Generate totals for last product and warehouse.
  thisLineItem(0, '~', 0, 0, '', '', '0000-00-00', array(0, 0, 0, 0, 0));

  // Grand totals line.
  if (!$_POST['form_csvexport']) {
    $grei = getEndInventory();
?>
 <tr bgcolor="#dddddd">
  <td class="detail" colspan="4">
   <?php xl('Grand Total','e'); ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $grei - $grandqtys[0] - $grandqtys[1] - $grandqtys[2] - $grandqtys[3] - $grandqtys[4]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $grandqtys[0]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $grandqtys[1]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $grandqtys[2]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $grandqtys[3]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $grandqtys[4]; ?>
  </td>
  <td class="dehead" align="right">
   <?php echo $grei; ?>
  </td>
 </tr>
<?php
  } // End not csv export
} // end refresh or export

if (!$_POST['form_csvexport']) {
?>
</table>
</form>
</center>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
<?php
} // End not export
?>
