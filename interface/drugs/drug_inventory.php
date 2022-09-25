<?php

 // Copyright (C) 2006-2021 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("drugs.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Check authorizations.
$auth_admin = AclMain::aclCheckCore('admin', 'drugs');
$auth_lots  = $auth_admin                             ||
    AclMain::aclCheckCore('inventory', 'lots') ||
    AclMain::aclCheckCore('inventory', 'purchases') ||
    AclMain::aclCheckCore('inventory', 'transfers') ||
    AclMain::aclCheckCore('inventory', 'adjustments') ||
    AclMain::aclCheckCore('inventory', 'consumption') ||
    AclMain::aclCheckCore('inventory', 'destruction');
$auth_anything = $auth_lots                           ||
    AclMain::aclCheckCore('inventory', 'sales') ||
    AclMain::aclCheckCore('inventory', 'reporting');
if (!$auth_anything) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Drug Inventory")]);
    exit;
}
// Note if user is restricted to any facilities and/or warehouses.
$is_user_restricted = isUserRestricted();

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'prod' => 'd.name, d.drug_id, di.expiration, di.lot_number',
  'act'  => 'd.active, d.name, d.drug_id, di.expiration, di.lot_number',
  'ndc'  => 'd.ndc_number, d.name, d.drug_id, di.expiration, di.lot_number',
  'con'  => 'd.consumable, d.name, d.drug_id, di.expiration, di.lot_number',
  'form' => 'lof.title, d.name, d.drug_id, di.expiration, di.lot_number',
  'lot'  => 'di.lot_number, d.name, d.drug_id, di.expiration',
  'wh'   => 'lo.title, d.name, d.drug_id, di.expiration, di.lot_number',
  'fac'  => 'f.name, d.name, d.drug_id, di.expiration, di.lot_number',
  'qoh'  => 'di.on_hand, d.name, d.drug_id, di.expiration, di.lot_number',
  'exp'  => 'di.expiration, d.name, d.drug_id, di.lot_number',
);

$form_facility = 0 + empty($_REQUEST['form_facility']) ? 0 : $_REQUEST['form_facility'];
$form_show_empty = empty($_REQUEST['form_show_empty']) ? 0 : 1;
$form_show_inactive = empty($_REQUEST['form_show_inactive']) ? 0 : 1;
$form_consumable = isset($_REQUEST['form_consumable']) ? intval($_REQUEST['form_consumable']) : 0;

// Incoming form_warehouse, if not empty is in the form "warehouse/facility".
// The facility part is an attribute used by JavaScript logic.
$form_warehouse = empty($_REQUEST['form_warehouse']) ? '' : $_REQUEST['form_warehouse'];
$tmp = explode('/', $form_warehouse);
$form_warehouse = $tmp[0];

// Get the order hash array value and key for this request.
$form_orderby = isset($ORDERHASH[$_REQUEST['form_orderby'] ?? '']) ? $_REQUEST['form_orderby'] : 'prod';
$orderby = $ORDERHASH[$form_orderby];

$binds = array();
$where = "WHERE 1 = 1";
if ($form_facility) {
    $where .= " AND lo.option_value IS NOT NULL AND lo.option_value = ?";
    $binds[] = $form_facility;
}
if ($form_warehouse) {
    $where .= " AND di.warehouse_id IS NOT NULL AND di.warehouse_id = ?";
    $binds[] = $form_warehouse;
}
if (!$form_show_inactive) {
    $where .= " AND d.active = 1";
}
if ($form_consumable) {
    if ($form_consumable == 1) {
        $where .= " AND d.consumable = '1'";
    } else {
        $where .= " AND d.consumable != '1'";
    }
}

$dion = $form_show_empty ? "" : "AND di.on_hand != 0";

// get drugs
$res = sqlStatement(
    "SELECT d.*, " .
    "di.inventory_id, di.lot_number, di.expiration, di.manufacturer, di.on_hand, " .
    "di.warehouse_id, lo.title, lo.option_value AS facid, f.name AS facname " .
    "FROM drugs AS d " .
    "LEFT JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
    "AND di.destroy_date IS NULL $dion " .
    "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
    "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
    "LEFT JOIN facility AS f ON f.id = lo.option_value " .
    "LEFT JOIN list_options AS lof ON lof.list_id = 'drug_form' AND " .
    "lof.option_id = d.form AND lof.activity = 1 " .
    "$where ORDER BY d.active DESC, $orderby",
    $binds
);

function generateEmptyTd($n)
{
    $temp = '';
    while ($n > 0) {
        $temp .= "<td></td>";
        $n--;
    }
    echo $temp;
}
function processData($data)
{
    $data['inventory_id'] = [$data['inventory_id']];
    $data['lot_number'] = [$data['lot_number']];
    $data['facname'] =  [$data['facname']];
    $data['title'] =  [$data['title']];
    $data['on_hand'] = [$data['on_hand']];
    $data['expiration'] = [$data['expiration']];
    return $data;
}
function mergeData($d1, $d2)
{
    $d1['inventory_id'] = array_merge($d1['inventory_id'], $d2['inventory_id']);
    $d1['lot_number'] = array_merge($d1['lot_number'], $d2['lot_number']);
    $d1['facname'] = array_merge($d1['facname'], $d2['facname']);
    $d1['title'] = array_merge($d1['title'], $d2['title']);
    $d1['on_hand'] = array_merge($d1['on_hand'], $d2['on_hand']);
    $d1['expiration'] = array_merge($d1['expiration'], $d2['expiration']);
    return $d1;
}
function mapToTable($row)
{
    global $auth_admin, $auth_lots;
    $today = date('Y-m-d');
    if ($row) {
        echo " <tr class='detail'>\n";
        $lastid = $row['drug_id'];
        if ($auth_admin) {
            echo "<td title='" . xla('Click to edit') . "' onclick='dodclick(" . attr(addslashes($lastid)) . ")'>" .
            "<a href='' onclick='return false'>" .
            text($row['name']) . "</a></td>\n";
        } else {
            echo "  <td>" . text($row['name']) . "</td>\n";
        }
        echo "  <td>" . ($row['active'] ? xlt('Yes') : xlt('No')) . "</td>\n";
        echo "  <td>" . ($row['consumable'] ? xlt('Yes') : xlt('No')) . "</td>\n";
        echo "  <td>" . text($row['ndc_number']) . "</td>\n";
        echo "  <td>" .
        generate_display_field(array('data_type' => '1','list_id' => 'drug_form'), $row['form']) .
        "</td>\n";
        echo "  <td>" . text($row['size']) . "</td>\n";
        echo "  <td title='" . xla('Measurement Units') . "'>" .
        generate_display_field(array('data_type' => '1','list_id' => 'drug_units'), $row['unit']) .
        "</td>\n";

        if ($auth_lots && $row['dispensable']) {
            echo "  <td onclick='doiclick(" . intval($lastid) . ",0)' title='" .
                xla('Purchase or Transfer') . "' style='padding:0'>" .
                "<input type='button' value='" . xla('Tran') . "'style='padding:0' /></td>\n";
        } else {
            echo "  <td title='" . xla('Not applicable') . "'>&nbsp;</td>\n";
        }

        if (!empty($row['inventory_id'][0])) {
            echo "<td>";
            foreach ($row['inventory_id'] as $key => $value) {
                if ($auth_lots) {
                    echo "<div title='" .
                        xla('Adjustment, Consumption, Return, or Edit') .
                        "' onclick='doiclick(" . intval($lastid) . "," .
                        intval($row['inventory_id'][$key]) . ")'>" .
                        "<a href='' onclick='return false'>" .
                        text($row['lot_number'][$key]) .
                        "</a></div>";
                } else {
                    echo "  <div>" . text($row['lot_number'][$key]) . "</div>\n";
                }
            }
            echo "</td>\n<td>";

            foreach ($row['facname'] as $value) {
                $value = $value != null ? $value : "N/A";
                echo "<div >" .  text($value) . "</div>";
            }
            echo "</td>\n<td>";

            foreach ($row['title'] as $value) {
                $value = $value != null ? $value : "N/A";
                echo "<div >" .  text($value) . "</div>";
            }
            echo "</td>\n<td>";

            foreach ($row['on_hand'] as $value) {
                $value = $value != null ? $value : "N/A";
                echo "<div >" . text($value) . "</div>";
            }
            echo "</td>\n<td>";

            foreach ($row['expiration'] as $value) {
                // Make the expiration date red if expired.
                $expired = !empty($value) && strcmp($value, $today) <= 0;
                $value = !empty($value) ? oeFormatShortDate($value) : xl('N/A');
                echo "<div" . ($expired ? " style='color:red'" : "") . ">" . text($value) . "</div>";
            }
            echo "</td>\n";
        } else {
                generateEmptyTd(4);
        }
        echo " </tr>\n";
    }
}
?>
<html>

<head>

<title><?php echo xlt('Drug Inventory'); ?></title>

<style>
a, a:visited, a:hover {
  color: var(--primary);
}
#mymaintable thead .sorting::before,
#mymaintable thead .sorting_asc::before,
#mymaintable thead .sorting_asc::after,
#mymaintable thead .sorting_desc::before,
#mymaintable thead .sorting_desc::after,
#mymaintable thead .sorting::after {
  display: none;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
  padding: 0 !important;
  margin: 0 !important;
  border: 0 !important;
}

.paginate_button:hover {
  background: transparent !important;
}

</style>

<?php Header::setupHeader(['datatables', 'datatables-dt', 'datatables-bs', 'report-helper']); ?>

<script>

// callback from add_edit_drug.php or add_edit_drug_inventory.php:
function refreshme() {
 // Avoiding reload() here because it generates a browser warning about repeating a POST.
 location.href = location.href;
}

// Process click on drug title.
function dodclick(id) {
 dlgopen('add_edit_drug.php?drug=' + id, '_blank', 900, 600);
}

// Process click on drug QOO or lot.
function doiclick(id, lot) {
 dlgopen('add_edit_lot.php?drug=' + id + '&lot=' + lot, '_blank', 600, 475);
}

// Enable/disable warehouse options depending on current facility.
function facchanged() {
    var f = document.forms[0];
    var facid = f.form_facility.value;
    var theopts = f.form_warehouse.options;
    for (var i = 1; i < theopts.length; ++i) {
        var tmp = theopts[i].value.split('/');
        var dis = facid && (tmp.length < 2 || tmp[1] != facid);
        theopts[i].disabled = dis;
        if (dis) {
            theopts[i].selected = false;
        }
    }
}

$(function () {
  $('#mymaintable').DataTable({
            stripeClasses:['stripe1','stripe2'],
            orderClasses: false,
            <?php // Bring in the translations ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
        });
});
</script>

</head>

<body class="body_top">
<form method='post' action='drug_inventory.php' onsubmit='return top.restoreSession()'>

<table border='0' cellpadding='3' width='100%'>
 <tr>
  <td>
   <b><?php echo xlt('Inventory Management'); ?></b>
  </td>
  <td align='right'>
<?php
// Build a drop-down list of facilities.
$query = "SELECT id, name FROM facility ORDER BY name";
$fres = sqlStatement($query);
echo "   <select name='form_facility' onchange='facchanged()'>\n";
echo "    <option value=''>-- " . xlt('All Facilities') . " --\n";
while ($frow = sqlFetchArray($fres)) {
    $facid = $frow['id'];
    if ($is_user_restricted && !isFacilityAllowed($facid)) {
        continue;
    }
    echo "    <option value='" . attr($facid) . "'";
    if ($facid == $form_facility) {
        echo " selected";
    }
    echo ">" . text($frow['name']) . "\n";
}
echo "   </select>\n";

// Build a drop-down list of warehouses.
echo "&nbsp;";
echo "   <select name='form_warehouse'>\n";
echo "    <option value=''>" . xlt('All Warehouses') . "</option>\n";
$lres = sqlStatement(
    "SELECT * FROM list_options " .
    "WHERE list_id = 'warehouse' ORDER BY seq, title"
);
while ($lrow = sqlFetchArray($lres)) {
    $whid  = $lrow['option_id'];
    $facid = $lrow['option_value'];
    if ($is_user_restricted && !isWarehouseAllowed($facid, $whid)) {
        continue;
    }
    echo "    <option value='" . attr("$whid/$facid") . "'";
    echo " id='fac" . attr($facid) . "'";
    if (strlen($form_warehouse)  > 0 && $whid == $form_warehouse) {
        echo " selected";
    }
    echo ">" . text(xl_list_label($lrow['title'])) . "</option>\n";
}
echo "   </select>\n";
?>
   &nbsp;
   <select name='form_consumable'>
<?php
foreach (
    array(
    '0' => xl('All Product Types'),
    '1' => xl('Consumable Only'),
    '2' => xl('Non-Consumable Only'),
    ) as $key => $value
) {
    echo "    <option value='" . attr($key) . "'";
    if ($key == $form_consumable) {
        echo " selected";
    }
    echo ">" . text($value) . "</option>\n";
}
?>
   </select>&nbsp;
  </td>
  <td>
   <input type='checkbox' name='form_show_empty' value='1'<?php if ($form_show_empty) {
        echo " checked";} ?> />
   <?php echo xlt('Show empty lots'); ?><br />
   <input type='checkbox' name='form_show_inactive' value='1'<?php if ($form_show_inactive) {
        echo " checked";} ?> />
   <?php echo xlt('Show inactive'); ?>
  </td>
  <td>
   <input type='submit' name='form_refresh' value="<?php echo xla('Refresh'); ?>" />
  </td>
 </tr>
 <tr>
  <td height="1">
  </td>
 </tr>
</table>

<!-- TODO: Why are we not using the BS4 table class here? !-->
<table id='mymaintable' class="display table-striped">
 <thead>
 <tr class='head'>
  <th>
    <?php echo xlt('Name'); ?> </a>
  </th>
  <th>
    <?php echo xlt('Act'); ?>
  </th>
  <th>
    <?php echo xlt('Cons'); ?>
  </th>
  <th>
   <?php echo xlt('NDC'); ?> </a>
  </th>
  <th>
   <?php echo xlt('Form'); ?> </a>
  </th>
  <th>
    <?php echo xlt('Size'); ?>
  </th>
  <th title='<?php echo xlt('Measurement Units'); ?>'>
    <?php echo xlt('Unit'); ?>
  </th>
  <th title='<?php echo xla('Purchase or Transfer'); ?>'>
    <?php echo xlt('Tran'); ?>
  </th>
  <th>
    <?php echo xlt('Lot'); ?> </a>
  </th>
  <th>
    <?php echo xlt('Facility'); ?> </a>
  </th>
  <th>
    <?php echo xlt('Warehouse'); ?> </a>
  </th>
  <th>
    <?php echo xlt('QOH'); ?> </a>
  </th>
  <th>
    <?php echo xlt('Expires'); ?> </a>
  </th>
 </tr>
 </thead>
 <tbody>
<?php
 $prevRow = '';
while ($row = sqlFetchArray($res)) {
    if (!empty($row['inventory_id']) && $is_user_restricted && !isWarehouseAllowed($row['facid'], $row['warehouse_id'])) {
        continue;
    }
    $row = processData($row);
    if ($prevRow == '') {
        $prevRow = $row;
        continue;
    }
    if ($prevRow['drug_id'] == $row['drug_id']) {
        $row = mergeData($prevRow, $row);
    } else {
        mapToTable($prevRow);
    }
    $prevRow = $row;
} // end while
mapToTable($prevRow);
?>
 </tbody>
</table>

<input class="btn btn-primary btn-block w-25 mx-auto" type='button' value='<?php echo xla('Add Drug'); ?>' onclick='dodclick(0)' />

<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby) ?>" />

</form>

<script>
facchanged();
</script>

</body>
</html>
