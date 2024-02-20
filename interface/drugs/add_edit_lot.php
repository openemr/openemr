<?php

/**
 * add and edit lot
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 // TODO: Replace tables with BS4 grid classes for GSoC


require_once("../globals.php");
require_once("drugs.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Check authorizations.
$auth_admin = AclMain::aclCheckCore('admin', 'drugs');
$auth_lots  = $auth_admin               ||
    AclMain::aclCheckCore('inventory', 'lots') ||
    AclMain::aclCheckCore('inventory', 'purchases') ||
    AclMain::aclCheckCore('inventory', 'transfers') ||
    AclMain::aclCheckCore('inventory', 'adjustments') ||
    AclMain::aclCheckCore('inventory', 'consumption') ||
    AclMain::aclCheckCore('inventory', 'destruction');
if (!$auth_lots) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit/Add Lot")]);
    exit;
}

function checkWarehouseUsed($warehouse_id)
{
    global $drug_id;
    $row = sqlQuery("SELECT count(*) AS count FROM drug_inventory WHERE " .
    "drug_id = ? AND on_hand != 0 AND " .
    "destroy_date IS NULL AND warehouse_id = ?", array($drug_id,$warehouse_id));
    return $row['count'];
}

function areVendorsUsed()
{
    $row = sqlQuery(
        "SELECT COUNT(*) AS count FROM users " .
        "WHERE active = 1 AND (info IS NULL OR info NOT LIKE '%Inactive%') " .
        "AND abook_type LIKE 'vendor%'"
    );
    return $row['count'];
}

// Generate a <select> list of warehouses.
// If multiple lots are not allowed for this product, then restrict the
// list to warehouses that are unused for the product.
// Returns the number of warehouses allowed.
// For these purposes the "unassigned" option is considered a warehouse.
//
function genWarehouseList($tag_name, $currvalue, $title, $class = '')
{
    global $drug_id, $is_user_restricted;

    $drow = sqlQuery("SELECT allow_multiple FROM drugs WHERE drug_id = ?", array($drug_id));
    $allow_multiple = $drow['allow_multiple'];

    $lres = sqlStatement("SELECT * FROM list_options " .
    "WHERE list_id = 'warehouse' AND activity = 1 ORDER BY seq, title");

    echo "<select name='" . attr($tag_name) . "' id='" . attr($tag_name) . "'";
    if ($class) {
        echo " class='" . attr($class) . "'";
    }

    echo " title='" . attr($title) . "'>";

    $got_selected = false;
    $count = 0;

    if ($allow_multiple /* || !checkWarehouseUsed('') */) {
        echo "<option value=''>" . xlt('Unassigned') . "</option>";
        ++$count;
    }

    while ($lrow = sqlFetchArray($lres)) {
        $whid = $lrow['option_id'];
        $facid = (int) ($lrow['option_value'] ?? null);
        if ($whid != $currvalue) {
            if (!$allow_multiple && checkWarehouseUsed($whid)) {
                continue;
            }
            if ($is_user_restricted && !isWarehouseAllowed($facid, $whid)) {
                continue;
            }
        }
        // Value identifies both warehouse and facility to support validation.
        echo "<option value='" . attr("$whid|$facid") . "'";

        if (
            (strlen($currvalue) == 0 && $lrow['is_default']) ||
            (strlen($currvalue)  > 0 && $whid == $currvalue)
        ) {
            echo " selected";
            $got_selected = true;
        }

        echo ">" . text($lrow['title']) . "</option>\n";

        ++$count;
    }

    if (!$got_selected && strlen($currvalue) > 0) {
        echo "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
        echo "</select>";
        echo " <span class='text-danger' title='" .
        xla('Please choose a valid selection from the list.') . "'>" .
        xlt('Fix this') . "!</span>";
    } else {
        echo "</select>";
    }

    return $count;
}

$drug_id = $_REQUEST['drug'] + 0;
$lot_id  = $_REQUEST['lot'] + 0;
$info_msg = "";

$form_trans_type = intval(isset($_POST['form_trans_type']) ? $_POST['form_trans_type'] : '0');

// Note if user is restricted to any facilities and/or warehouses.
$is_user_restricted = isUserRestricted();

if (!$drug_id) {
    die(xlt('Drug ID missing!'));
}
?>
<html>
<head>
<title><?php echo $lot_id ? xlt("Edit") : xlt("Add New");
echo " " . xlt('Lot'); ?></title>

<?php Header::setupHeader(['datetime-picker', 'opener']); ?>

<style>
td {
    font-size: 0.8125rem;
}
</style>

<script>

 function validate() {
  var f = document.forms[0];
  var trans_type = f.form_trans_type.value;

  if (trans_type > '0') {
   // Transaction date validation. Must not be later than today or before 2000.
   if (f.form_sale_date.value > <?php echo js_escape(date('Y-m-d')) ?> || f.form_sale_date.value < '2000-01-01') {
    alert(<?php echo xlj('Transaction date must not be in the future or before 2000'); ?>);
    return false;
   }
   // Quantity validations.
   var qty = parseInt(f.form_quantity.value);
   if (!qty) {
    alert(<?php echo xlj('A quantity is required'); ?>);
    return false;
   }
   if (f.form_trans_type.value != '5' && qty < 0) {
    alert(<?php echo xlj('Quantity cannot be negative for this transaction type'); ?>);
    return false;
   }
  }

  // Get source and target facility IDs.
  var facfrom = 0;
  var facto = 0;
  var a = f.form_source_lot.value.split('|', 2);
  var lotfrom = parseInt(a[0]);
  if (a.length > 1) facfrom = parseInt(a[1]);
  a = f.form_warehouse_id.value.split('|', 2);
  whid = a[0];
  if (a.length > 1) facto = parseInt(a[1]);

  if (lotfrom == '0' && f.form_lot_number.value.search(/\S/) < 0) {
   alert(<?php echo xlj('A lot number is required'); ?>);
   return false;
  }

  // Require warehouse selection.
  if (whid == '') {
   alert(<?php echo xlj('A warehouse is required'); ?>);
   return false;
  }

  // Require comments for all transactions.
  if (f.form_trans_type.value > '0' && f.form_notes.value.search(/\S/) < 0) {
   alert(<?php echo xlj('Comments are required'); ?>);
   return false;
  }

  if (f.form_trans_type.value == '4') {
   // Transfers require a source lot.
   if (!lotfrom) {
    alert(<?php echo xlj('A source lot is required'); ?>);
    return false;
   }

  // Check the case of a transfer between different facilities.
  if (facto != facfrom) {
   if (!confirm(<?php echo xlj('Warning: Source and target facilities differ. Continue anyway?'); ?>))
    return false;
  }

  // Check for missing expiration date on a purchase or simple update.
  if (f.form_expiration.value == '' && f.form_trans_type.value <= '2') {
   if (!confirm(<?php echo xlj('Warning: Most lots should have an expiration date. Continue anyway?'); ?>)) {
    return false;
   }
  }

  return true;
 }

 function trans_type_changed() {
  var f = document.forms[0];
  var sel = f.form_trans_type;
  var type = sel.options[sel.selectedIndex].value;
  // display attributes
  var showQuantity  = true;
  var showOnHand       = true;
  var showSaleDate  = true;
  var showCost      = true;
  var showSourceLot = true;
  var showNotes     = true;
  var showManufacturer = true;
  var showLotNumber    = true;
  var showWarehouse    = true;
  var showExpiration   = true;
  var showVendor       = <?php echo areVendorsUsed() ? 'true' : 'false'; ?>;

  // readonly attributes
  var roManufacturer   = true;
  var roLotNumber      = true;
  var roExpiration     = true;

  labelWarehouse       = <?php echo xlj('Warehouse'); ?>;

  if (type == '2') { // purchase
    showSourceLot = false;
    roManufacturer = false;
    roLotNumber    = false;
    roExpiration   = false;
<?php if (!$lot_id) { // target lot is not known yet ?>
    showOnHand     = false;
<?php } ?>
  }
  else if (type == '3') { // return
    showSourceLot = false;
    showManufacturer = false;
    showVendor       = false;
  }
  else if (type == '4') { // transfer
    showCost         = false;
    showManufacturer = false;
    showVendor       = false;
    showLotNumber    = false;
    showExpiration   = false;
<?php if ($lot_id) { // disallow warehouse change on xfer to existing lot ?>
    showWarehouse    = false;
<?php } else { // target lot is not known yet ?>
    showOnHand       = false;
<?php } ?>
    labelWarehouse = <?php echo xlj('Destination Warehouse'); ?>;
  }
  else if (type == '5') { // adjustment
    showCost = false;
    showSourceLot = false;
    showManufacturer = false;
    showVendor       = false;
  }
  else if (type == '7') { // consumption
    showCost      = false;
    showSourceLot = false;
    showManufacturer = false;
    showVendor       = false;
  }
  else {                  // Edit Only
    showQuantity  = false;
    showSaleDate  = false;
    showCost      = false;
    showSourceLot = false;
    showNotes     = false;
    roManufacturer = false;
    roLotNumber    = false;
    roExpiration   = false;
  }
  document.getElementById('row_quantity'  ).style.display = showQuantity  ? '' : 'none';
  document.getElementById('row_on_hand'     ).style.display = showOnHand       ? '' : 'none';
  document.getElementById('row_sale_date' ).style.display = showSaleDate  ? '' : 'none';
  document.getElementById('row_cost'      ).style.display = showCost      ? '' : 'none';
  document.getElementById('row_source_lot').style.display = showSourceLot ? '' : 'none';
  document.getElementById('row_notes'     ).style.display = showNotes     ? '' : 'none';
  document.getElementById('row_manufacturer').style.display = showManufacturer ? '' : 'none';
  document.getElementById('row_vendor'      ).style.display = showVendor       ? '' : 'none';
  document.getElementById('row_lot_number'  ).style.display = showLotNumber    ? '' : 'none';
  document.getElementById('row_warehouse'   ).style.display = showWarehouse    ? '' : 'none';
  document.getElementById('row_expiration'  ).style.display = showExpiration   ? '' : 'none';

  f.form_manufacturer.readOnly = roManufacturer;
  f.form_lot_number.readOnly   = roLotNumber;
  f.form_expiration.readOnly   = roExpiration;
  document.getElementById('img_expiration').style.display = roExpiration ? 'none' : '';

  document.getElementById('label_warehouse').innerHTML = labelWarehouse;
 }

    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
</script>

</head>

<body class="body_top">
<?php
if ($lot_id) {
    $row = sqlQuery("SELECT * FROM drug_inventory WHERE drug_id = ? " .
    "AND inventory_id = ?", array($drug_id,$lot_id));
}

// If we are saving, then save and close the window.
//
if (!empty($_POST['form_save'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $form_quantity = is_numeric($_POST['form_quantity']) ? intval($_POST['form_quantity']) : 0;
    $form_cost = sprintf('%0.2f', $_POST['form_cost']);
    // $form_source_lot = $_POST['form_source_lot'] + 0;

    list($form_source_lot, $form_source_facility) = explode('|', $_POST['form_source_lot']);
    $form_source_lot = intval($form_source_lot);

    list($form_warehouse_id) = explode('|', $_POST['form_warehouse_id']);

    $form_expiration   = $_POST['form_expiration'] ?? '';
    $form_lot_number   = $_POST['form_lot_number'] ?? '';
    $form_manufacturer = $_POST['form_manufacturer'] ?? '';
    $form_vendor_id    = $_POST['form_vendor_id'] ?? '';

    if ($form_trans_type < 0 || $form_trans_type > 7) {
        die(xlt('Internal error!'));
    }

    if (
        !$auth_admin && (
            $form_trans_type == 2 && !AclMain::aclCheckCore('inventory', 'purchases') ||
            $form_trans_type == 3 && !AclMain::aclCheckCore('inventory', 'purchases') ||
            $form_trans_type == 4 && !AclMain::aclCheckCore('inventory', 'transfers') ||
            $form_trans_type == 5 && !AclMain::aclCheckCore('inventory', 'adjustments') ||
            $form_trans_type == 7 && !AclMain::aclCheckCore('inventory', 'consumption')
            )
    ) {
        die(xlt('Not authorized'));
    }

      // Some fixups depending on transaction type.
    if ($form_trans_type == 3) { // return
        $form_quantity = 0 - $form_quantity;
        $form_cost = 0 - $form_cost;
    } elseif ($form_trans_type == 5) { // adjustment
        $form_cost = 0;
    } elseif ($form_trans_type == 7) { // consumption
        $form_quantity = 0 - $form_quantity;
        $form_cost = 0;
    } elseif ($form_trans_type == 0) { // no transaction
        $form_quantity = 0;
        $form_cost = 0;
    }
    if ($form_trans_type != 4) { // not transfer
        $form_source_lot = 0;
    }

    // If a transfer, make sure there is sufficient quantity in the source lot
    // and apply some default values from it.
    if ($form_source_lot) {
        $srow = sqlQuery(
            "SELECT lot_number, expiration, manufacturer, vendor_id, on_hand " .
            "FROM drug_inventory WHERE drug_id = ? AND inventory_id = ?",
            array($drug_id, $form_source_lot)
        );
        if (empty($form_lot_number)) {
            $form_lot_number = $srow['lot_number'  ];
        }
        if (empty($form_expiration)) {
             $form_expiration = $srow['expiration'  ];
        }
        if (empty($form_manufacturer)) {
             $form_manufacturer = $srow['manufacturer'];
        }
        if (empty($form_vendor_id)) {
             $form_vendor_id = $srow['vendor_id'   ];
        }
        if ($form_quantity && $srow['on_hand'] < $form_quantity) {
            $info_msg = xl('Transfer failed, insufficient quantity in source lot');
        }
    }

    if (!$info_msg) {
        // If purchase or transfer with no destination lot specified, see if one already exists.
        if (!$lot_id && $form_lot_number && ($form_trans_type == 2 || $form_trans_type == 4)) {
            $erow = sqlQuery(
                "SELECT * FROM drug_inventory WHERE " .
                "drug_id = ? AND warehouse_id = ? AND lot_number = ? AND destroy_date IS NULL AND on_hand != 0 " .
                "ORDER BY inventory_id DESC LIMIT 1",
                array($drug_id, $form_warehouse_id, $form_lot_number)
            );
            if (!empty($erow['inventory_id'])) {
                // Yes a matching lot exists, use it and its values.
                $lot_id = $erow['inventory_id'];
                if (empty($form_expiration)) {
                    $form_expiration   = $erow['expiration'  ];
                }
                if (empty($form_manufacturer)) {
                    $form_manufacturer = $erow['manufacturer'];
                }
                if (empty($form_vendor_id)) {
                    $form_vendor_id    = $erow['vendor_id'   ];
                }
            }
        }

        // Destination lot already exists.
        if ($lot_id) {
            if ($_POST['form_save']) {
                // Make sure the destination quantity will not end up negative.
                if (($row['on_hand'] + $form_quantity) < 0) {
                    $info_msg = xl('Transaction failed, insufficient quantity in destination lot');
                } else {
                    sqlStatement(
                        "UPDATE drug_inventory SET " .
                        "lot_number = ?, " .
                        "manufacturer = ?, " .
                        "expiration = ?, "  .
                        "vendor_id = ?, " .
                        "warehouse_id = ?, " .
                        "on_hand = on_hand + ? "  .
                        "WHERE drug_id = ? AND inventory_id = ?",
                        array(
                            $form_lot_number,
                            $form_manufacturer,
                            (empty($form_expiration) ? "NULL" : $form_expiration),
                            $form_vendor_id,
                            $form_warehouse_id,
                            $form_quantity,
                            $drug_id,
                            $lot_id
                        )
                    );
                }
            } else {
                sqlStatement("DELETE FROM drug_inventory WHERE drug_id = ? " .
                "AND inventory_id = ?", array($drug_id,$lot_id));
            }
        } else { // Destination lot will be created.
            if ($form_quantity < 0) {
                $info_msg = xl('Transaction failed, quantity is less than zero');
            } else {
                $exptest = $form_expiration ?
                    ("expiration = '" . add_escape_custom($form_expiration) . "'") : "expiration IS NULL";
                $crow = sqlQuery(
                    "SELECT count(*) AS count from drug_inventory " .
                    "WHERE lot_number = ? " .
                    "AND drug_id = ? " .
                    "AND warehouse_id = ? " .
                    "AND $exptest " .
                    "AND on_hand != 0 " .
                    "AND destroy_date IS NULL",
                    array($form_lot_number, $drug_id, $form_warehouse_id)
                );
                if ($crow['count']) {
                    $info_msg = xl('Transaction failed, duplicate lot');
                } else {
                    $lot_id = sqlInsert(
                        "INSERT INTO drug_inventory ( " .
                        "drug_id, lot_number, manufacturer, expiration, " .
                        "vendor_id, warehouse_id, on_hand " .
                        ") VALUES ( " .
                        "?, "                            .
                        "?, " .
                        "?, " .
                        "?, "  .
                        "?, " .
                        "?, " .
                        "? "  .
                        ")",
                        array(
                            $drug_id,
                            $form_lot_number,
                            $form_manufacturer,
                            (empty($form_expiration) ? "NULL" : $form_expiration),
                            $form_vendor_id,
                            $form_warehouse_id,
                            $form_quantity
                        )
                    );
                }
            }
        }

        // Create the corresponding drug_sales transaction.
        if ($_POST['form_save'] && $form_quantity && !$info_msg) {
            $form_notes = $_POST['form_notes'];
            $form_sale_date = $_POST['form_sale_date'];
            if (empty($form_sale_date)) {
                $form_sale_date = date('Y-m-d');
            }

            sqlStatement(
                "INSERT INTO drug_sales ( " .
                "drug_id, inventory_id, prescription_id, pid, encounter, user, sale_date, " .
                "quantity, fee, xfer_inventory_id, distributor_id, notes, trans_type " .
                ") VALUES ( " .
                "?, " .
                "?, '0', '0', '0', " .
                "?, " .
                "?, " .
                "?, " .
                "?, " .
                "?, " .
                "?, " .
                "?, " .
                "? )",
                array(
                    $drug_id,
                    $lot_id,
                    $_SESSION['authUser'],
                    $form_sale_date,
                    (0 - $form_quantity),
                    (0 - $form_cost),
                    $form_source_lot,
                    0,
                    $form_notes,
                    $form_trans_type
                )
            );

            // If this is a transfer then reduce source QOH.
            if ($form_source_lot) {
                sqlStatement(
                    "UPDATE drug_inventory SET " .
                    "on_hand = on_hand - ? " .
                    "WHERE inventory_id = ?",
                    array($form_quantity,$form_source_lot)
                );
            }
        }
    } // end if not $info_msg

    // Close this window and redisplay the updated list of drugs.
    //
    echo "<script>\n";
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }

    echo " window.close();\n";
    echo " if (opener.refreshme) opener.refreshme();\n";
    echo "</script></body></html>\n";
    exit();
}
$title = $lot_id ? xl("Update Lot") : xl("Add Lot");
?>
<h3 class="ml-1"><?php echo text($title);?></h3>
<form method='post' name='theform' action='add_edit_lot.php?drug=<?php echo attr_url($drug_id); ?>&lot=<?php echo attr_url($lot_id); ?>' onsubmit='return validate()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<table class="table table-borderless w-100">

 <tr id='row_sale_date'>
  <td class="text-nowrap align-top"><?php echo xlt('Date'); ?>:</td>
  <td>
   <input type='text' class="datepicker" size='10' name='form_sale_date' id='form_sale_date'
    value='<?php echo attr(date('Y-m-d')) ?>'
    title='<?php echo xla('yyyy-mm-dd date of purchase or transfer'); ?>' />
  </td>
 </tr>

 <tr>
  <td class="text-nowrap align-top"><?php echo xlt('Transaction Type'); ?>:</td>
  <td>
   <select name='form_trans_type' class='form-control' onchange='trans_type_changed()'>
<?php
foreach (
    array(
    '2' => xl('Purchase/Receipt'),
    '3' => xl('Return'),
    '4' => xl('Transfer'),
    '5' => xl('Adjustment'),
    '7' => xl('Consumption'),
    '0' => xl('Edit Only'),
    ) as $key => $value
) {
    echo "<option value='" . attr($key) . "'";
    if (
        !$auth_admin && (
        $key == 2 && !AclMain::aclCheckCore('inventory', 'purchases') ||
        $key == 3 && !AclMain::aclCheckCore('inventory', 'purchases') ||
        $key == 4 && !AclMain::aclCheckCore('inventory', 'transfers') ||
        $key == 5 && !AclMain::aclCheckCore('inventory', 'adjustments') ||
        $key == 7 && !AclMain::aclCheckCore('inventory', 'consumption')
        )
    ) {
        echo " disabled";
    } else if (
        $lot_id  && in_array($key, array('2', '4'     )) ||
        // $lot_id  && in_array($key, array('2')) ||
        !$lot_id && in_array($key, array('0', '3', '5', '7'))
    ) {
        echo " disabled";
    } else {
        if (isset($_POST['form_trans_type']) && $key == $form_trans_type) {
            echo " selected";
        }
    }
    echo ">" . text($value) . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

 <tr id='row_lot_number'>
  <td class="text-nowrap align-top"><?php echo xlt('Lot Number'); ?>:</td>
  <td>
   <input class="form-control w-100" type='text' size='40' name='form_lot_number' maxlength='40' value='<?php echo attr($row['lot_number']) ?>' />
  </td>
 </tr>

 <tr id='row_manufacturer'>
  <td class="text-nowrap align-top"><?php echo xlt('Manufacturer'); ?>:</td>
  <td>
   <input class="form-control w-100" type='text' size='40' name='form_manufacturer' maxlength='250' value='<?php echo attr($row['manufacturer']) ?>' />
  </td>
 </tr>

 <tr id='row_expiration'>
  <td class="text-nowrap align-top"><?php echo xlt('Expiration'); ?>:</td>
  <td>
   <input type='text' class='datepicker form-control w-50' size='10' name='form_expiration' id='form_expiration'
    value='<?php echo attr($row['expiration']) ?>'
    title='<?php echo xla('yyyy-mm-dd date of expiration'); ?>' />
  </td>
 </tr>

 <tr id='row_source_lot'>
  <td class="text-nowrap align-top"><?php echo xlt('Source Lot'); ?>:</td>
  <td>
   <select name='form_source_lot' class='form-control'>
    <option value='0'> </option>
<?php
$lres = sqlStatement(
    "SELECT " .
    "di.inventory_id, di.lot_number, di.on_hand, lo.title, lo.option_value, di.warehouse_id " .
    "FROM drug_inventory AS di " .
    "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
    "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
    "WHERE di.drug_id = ? AND di.inventory_id != ? AND " .
    "di.on_hand > 0 AND di.destroy_date IS NULL " .
    "ORDER BY di.lot_number, lo.title, di.inventory_id",
    array ($drug_id,$lot_id)
);
while ($lrow = sqlFetchArray($lres)) {
    // TBD: For transfer to an existing lot do we want to force the same lot number?
    // Check clinic/wh permissions.
    $facid = (int) ($lrow['option_value'] ?? null);
    if ($is_user_restricted && !isWarehouseAllowed($facid, $lrow['warehouse_id'])) {
        continue;
    }
    echo "<option value='" . attr($lrow['inventory_id']) . '|' . attr($facid)  . "'>";
    echo text($lrow['lot_number']);
    if (!empty($lrow['title'])) {
        echo " / " . text($lrow['title']);
    }
    echo " (" . text($lrow['on_hand']) . ")";
    echo "</option>\n";
}
?>
   </select>
  </td>
 </tr>

 <tr id='row_vendor'>
  <td class="text-nowrap align-top"><?php echo xlt('Vendor'); ?>:</td>
  <td>
<?php
// Address book entries for vendors.
generate_form_field(
    array('data_type' => 14, 'field_id' => 'vendor_id',
    'list_id' => '', 'edit_options' => 'V',
    'description' => xl('Address book entry for the vendor')),
    $row['vendor_id']
);
?>
  </td>
 </tr>

 <tr id='row_warehouse'>
  <td class="text-nowrap align-top" id="label_warehouse"><?php echo xlt('Warehouse'); ?>:</td>
  <td>
<?php
if (
    !genWarehouseList(
        "form_warehouse_id",
        $row['warehouse_id'],
        xl('Location of this lot'),
        "form-control"
    )
) {
    $info_msg = xl('This product allows only one lot per warehouse.');
}
?>
  </td>
 </tr>

 <tr id='row_on_hand'>
  <td class="text-nowrap align-top"><?php echo xlt('On Hand'); ?>:</td>
  <td>
    <span><?php echo text($row['on_hand'] + 0); ?></span>
  </td>
 </tr>

 <tr id='row_quantity'>
  <td class="text-nowrap align-top"><?php echo xlt('Quantity'); ?>:</td>
  <td>
   <input class="form-control" type='text' size='5' name='form_quantity' maxlength='7' />
  </td>
 </tr>

 <tr id='row_cost'>
  <td class="text-nowrap align-top"><?php echo xlt('Total Cost'); ?>:</td>
  <td>
   <input class="form-control" type='text' size='7' name='form_cost' maxlength='12' />
  </td>
 </tr>

 <tr id='row_notes' title='<?php echo xla('Include your initials and details of reason for transaction.'); ?>'>
  <td class="text-nowrap align-top"><?php echo xlt('Comments'); ?>:</td>
  <td>
   <input class="form-control w-100" type='text' size='40' name='form_notes' maxlength='255' />
  </td>
 </tr>

</table>

<div class="btn-group mt-3">
<input type='submit' class="btn btn-primary" name='form_save' value='<?php echo $lot_id ? xla('Update') : xla('Add') ?>' />

<?php if ($lot_id && ($auth_admin || AclMain::aclCheckCore('inventory', 'destruction'))) { ?>
<input type='button' class="btn btn-danger" value='<?php echo xla('Destroy'); ?>'
 onclick="window.location.href='destroy_lot.php?drug=<?php echo attr_url($drug_id); ?>&lot=<?php echo attr_url($lot_id); ?>'" />
<?php } ?>

<input type='button' class="btn btn-primary btn-print" value='<?php echo xla('Print'); ?>' onclick='window.print()' />

<input type='button' class="btn btn-warning" value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</div>

</form>
<script>
<?php
if ($info_msg) {
    echo " alert('" . addslashes($info_msg) . "');\n";
    echo " window.close();\n";
}
?>
trans_type_changed();
</script>
</body>
</html>
