<?php

/**
 * inventory_list.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$include_root/drugs/drugs.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
    'name' => 'd.name, d.drug_id',
    'act'  => 'd.active, d.name, d.drug_id',
    'con'  => 'd.consumable, d.name, d.drug_id',
);

// Check permission for this report.
$auth_drug_reports = $GLOBALS['inhouse_pharmacy'] && (
    AclMain::aclCheckCore('admin', 'drugs') ||
    AclMain::aclCheckCore('inventory', 'reporting'));
if (!$auth_drug_reports) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Inventory List")]);
    exit;
}

// Note if user is restricted to any facilities and/or warehouses.
$is_user_restricted = isUserRestricted();

function addWarning($msg)
{
    global $warnings, $form_action;
    $break = $form_action != 'export' ? '<br />' : '; ';
    if ($warnings) {
        $warnings .= $break;
    }
    $warnings .= text($msg);
}

// Check if a product needs to be re-ordered, optionally for a given warehouse.
//
function checkReorder($drug_id, $min, $warehouse = '')
{
    global $form_days;

    if (!$min) {
        return false;
    }

    $query = "SELECT " .
        "SUM(s.quantity) AS sale_quantity " .
        "FROM drug_sales AS s " .
        "LEFT JOIN drug_inventory AS di ON di.inventory_id = s.inventory_id " .
        "WHERE " .
        "s.drug_id = ? AND " .
        "s.sale_date > DATE_SUB(NOW(), INTERVAL ? DAY) " .
        "AND s.pid != 0";
    $binds = array($drug_id, $form_days);
    if ($warehouse !== '') {
        $query .= " AND di.warehouse_id = ?";
        $binds[] = $warehouse;
    }
    $srow = sqlQuery($query, $binds);
    $sales = 0 + $srow['sale_quantity'];

    $query = "SELECT SUM(on_hand) AS on_hand " .
      "FROM drug_inventory AS di WHERE " .
      "di.drug_id = ? AND " .
      "(di.expiration IS NULL OR di.expiration > NOW()) AND " .
      "di.destroy_date IS NULL";
    $binds = array($drug_id);
    if ($warehouse !== '') {
        $query .= " AND di.warehouse_id = ?";
        $binds[] = $warehouse;
    }
    $ohrow = sqlQuery($query, $binds);
    $onhand = intval($ohrow['on_hand']);

    if (empty($GLOBALS['gbl_min_max_months'])) {
        if ($onhand <= $min) {
            return true;
        }
    } else {
        if ($sales != 0) {
            $stock_months = sprintf('%0.1f', $onhand * ($form_days / 30.41) / $sales);
            if ($stock_months <= $min) {
                return true;
            }
        }
    }

    return false;
}

// Generate the list of warehouse IDs that the current user is allowed to access.
// This is used to build SQL for $uwcond.
//
function genUserWarehouses($userid = 0)
{
    $list = '';
    $res = sqlStatement(
        "SELECT DISTINCT option_id, option_value FROM list_options WHERE " .
        "list_id = 'warehouse' AND activity = 1"
    );
    while ($row = sqlFetchArray($res)) {
        if (isWarehouseAllowed($row['option_value'], $row['option_id'], $userid)) {
            if ($list != '') {
                $list .= ', ';
            }
            // Using add_escape_custom because this string is for a SQL query.
            $list .= "'" . add_escape_custom($row['option_id']) . "'";
        }
    }
    return $list;
}

// This counts the number of days that have a starting zero inventory for a given product
// since a given start date with given restrictions for warehouse or facility.
// End date is assumed to be the current date.
//
// function zeroDays($product_id, $begdate, $warehouse_id = '~', $facility_id = 0) {
function zeroDays($product_id, $begdate, $extracond, $extrabind, $min_sale = 1)
{
    $today = date('Y-m-d');

    $prodcond = '';
    $prodbind = array();
    if ($product_id) {
        $prodcond = "AND di.drug_id = ?";
        $prodbind[] = $product_id;
    }

    // This will be an array where key is date and value is quantity.
    // For each date key the value represents net quantity changes for that day.
    $qtys = array();

    // Force it to have entries for the begin and end dates.
    $qtys[$today] = 0;
    $qtys[$begdate] = 0;

    // Get sums of current inventory quantities.
    $query = "SELECT SUM(di.on_hand) AS on_hand " .
        "FROM drug_inventory AS di " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "WHERE " .
        "di.destroy_date IS NULL $prodcond $extracond";
    $row = sqlQuery($query, array_merge($prodbind, $extrabind));
    $current_qoh = $row['on_hand'];

    // Add sums of destructions done for each date (effectively a type of transaction).
    $res = sqlStatement(
        "SELECT di.destroy_date, SUM(di.on_hand) AS on_hand " .
        "FROM drug_inventory AS di " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "WHERE " .
        "di.destroy_date IS NOT NULL AND di.destroy_date >= ? " .
        "$prodcond $extracond" .
        "GROUP BY di.destroy_date ORDER BY di.destroy_date",
        array_merge(array($begdate), $prodbind, $extrabind)
    );
    while ($row = sqlFetchArray($res)) {
        $thisdate = substr($row['destroy_date'], 0, 10);
        if (!isset($qtys[$thisdate])) {
            $qtys[$thisdate] = 0;
        }
        $qtys[$thisdate] += $row['on_hand'];
    }

    // Add sums of other transactions for each date.
    // Note sales are positive and purchases are negative.
    $res = sqlStatement(
        "SELECT ds.sale_date, SUM(ds.quantity) AS quantity " .
        "FROM drug_sales AS ds, drug_inventory AS di " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "WHERE " .
        "ds.sale_date >= ? AND " .
        "di.inventory_id = ds.inventory_id " .
        "$prodcond $extracond" .
        "GROUP BY ds.sale_date ORDER BY ds.sale_date",
        array_merge(array($begdate), $prodbind, $extrabind)
    );
    while ($row = sqlFetchArray($res)) {
        $thisdate = $row['sale_date'];
        if (!isset($qtys[$thisdate])) {
            $qtys[$thisdate] = 0;
        }
        $qtys[$thisdate] += $row['quantity'];
    }

    // Subtract sums of transfers out for each date.
    // Quantity for transfers, like purchases, is usually negative.
    $res = sqlStatement(
        "SELECT ds.sale_date, SUM(ds.quantity) AS quantity " .
        "FROM drug_sales AS ds, drug_inventory AS di " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "WHERE " .
        "ds.sale_date >= ? AND " .
        "di.inventory_id = ds.xfer_inventory_id " .
        "$prodcond $extracond" .
        "GROUP BY ds.sale_date ORDER BY ds.sale_date",
        array_merge(array($begdate), $prodbind, $extrabind)
    );
    while ($row = sqlFetchArray($res)) {
        $thisdate = $row['sale_date'];
        if (!isset($qtys[$thisdate])) {
            $qtys[$thisdate] = 0;
        }
        $qtys[$thisdate] -= $row['quantity'];
    }

    // Sort by reverse date.
    krsort($qtys);

    $lastdate = '';
    $lastqty = $current_qoh;

    // This will be the count of days that have zero quantity at the start of the day.
    $zerodays = 0;

    // Now we traverse the array in descending date order, adding a date's quantity adjustment
    // to the running total to get the quantity at the beginning of that date.
    foreach ($qtys as $key => $val) {
        if ($lastdate && $lastqty < $min_sale) {
            // The span of days from $key to start of $lastdate has zero quantity.
            // Add that number of days to $zerodays.
            $diff = date_diff(date_create($key), date_create($lastdate));
            $zerodays += $diff->days;
        }
        $lastdate = $key;
        $lastqty += $val; // giving qoh at the start of $lastdate
    }
    // The last array entry hasn't been accounted for yet, so do that.
    if ($lastqty < $min_sale) {
        ++$zerodays;
    }
    return $zerodays;
}

function write_report_line(&$row)
{
    global $form_details, $wrl_last_drug_id, $warnings, $encount, $fwcond, $fwbind, $form_days;
    global $gbl_expired_lot_warning_days, $form_facility, $form_warehouse, $form_action;

    $emptyvalue = $form_action != 'export' ? '&nbsp;' : '';
    $drug_id = (int) $row['drug_id'];
    $on_hand = (int) $row['on_hand'];
    $warehouse_id = isset($row['warehouse_id']) ? $row['warehouse_id'] : '';
    $facility_id = empty($row['option_value']) ? '0' : $row['option_value'];
    $warnings = '';

    // Get sales in the date range for this drug (and facility or warehouse if details).
    if ($form_details == 1) { // facility details
        $query = "SELECT " .
            "SUM(s.quantity) AS sale_quantity " .
            "FROM drug_sales AS s " .
            "LEFT JOIN drug_inventory AS di ON di.inventory_id = s.inventory_id " .
            "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
            "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
            "WHERE " .
            "s.drug_id = ? AND " .
            "lo.option_value IS NOT NULL AND lo.option_value = ? AND " .
            "s.sale_date > DATE_SUB(NOW(), INTERVAL " . escape_limit($form_days) . " DAY) " .
            "AND s.pid != 0 $fwcond";
        $srow = sqlQuery($query, array_merge(array($drug_id, $facility_id), $fwbind));
    } elseif ($form_details == 2) { // warehouse details
        $query = "SELECT " .
            "SUM(s.quantity) AS sale_quantity " .
            "FROM drug_sales AS s " .
            "LEFT JOIN drug_inventory AS di ON di.inventory_id = s.inventory_id " .
            "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
            "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
            "WHERE " .
            "s.drug_id = ? AND " .
            "di.warehouse_id IS NOT NULL AND di.warehouse_id = ? AND " .
            "s.sale_date > DATE_SUB(NOW(), INTERVAL " . escape_limit($form_days) . " DAY) " .
            "AND s.pid != 0 $fwcond";
        $srow = sqlQuery($query, array_merge(array($drug_id, $warehouse_id), $fwbind));
    } else {
        $srow = sqlQuery(
            "SELECT " .
            "SUM(s.quantity) AS sale_quantity " .
            "FROM drug_sales AS s " .
            "LEFT JOIN drug_inventory AS di ON di.inventory_id = s.inventory_id " .
            "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
            "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
            "WHERE " .
            "s.drug_id = ? AND " .
            "s.sale_date > DATE_SUB(NOW(), INTERVAL " . escape_limit($form_days) . " DAY) " .
            "AND s.pid != 0 $fwcond",
            array_merge(array($drug_id), $fwbind)
        );
    }
    $sale_quantity = $srow['sale_quantity'];

    // Compute the smallest quantity that might be taken from ANY lot for this product
    // (and facility or warehouse if details) based on the past $form_days days of sales.
    // If lot combining is allowed this is always 1.
    $extracond = $fwcond;
    $extrabind = $fwbind;
    if ($form_details == 1) {
        $extracond = "AND lo.option_value IS NOT NULL AND lo.option_value = ?";
        $extrabind = array($facility_id);
    }
    if ($form_details == 2) {
        $extracond = "AND di.warehouse_id = ?";
        $extrabind = array($warehouse_id);
    }
    $min_sale = 1;
    if (!$row['allow_combining']) {
        $sminrow = sqlQuery(
            "SELECT " .
            "MIN(s.quantity) AS min_sale " .
            "FROM drug_sales AS s " .
            "LEFT JOIN drug_inventory AS di ON di.drug_id = s.drug_id " .
            "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
            "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
            "WHERE " .
            "s.drug_id = ? AND " .
            "s.sale_date > DATE_SUB(NOW(), INTERVAL " . escape_limit($form_days) . " DAY) " .
            "AND s.pid != 0 " .
            "AND s.quantity > 0 $extracond",
            array_merge(array($drug_id), $extrabind)
        );
        $min_sale = 0 + $sminrow['min_sale'];
    }
    if (!$min_sale) {
        $min_sale = 1;
    }

    // Get number of days with no stock.
    $today = date('Y-m-d');
    $tmp_days = max($form_days - 1, 0);
    $begdate = date('Y-m-d', strtotime("$today - $tmp_days days"));
    $zerodays = zeroDays($drug_id, $begdate, $extracond, $extrabind, $min_sale);

    $months = $form_days / 30.41;

    $monthly = ($months && $sale_quantity && $form_days > $zerodays) ?
        sprintf('%0.1f', $sale_quantity / $months * $form_days / ($form_days - $zerodays))
        : 0;

    if ($monthly == 0.0 && $on_hand == 0) {
        // The row has no QOH and no recent sales, so is deemed uninteresting.
        // See CV email 2014-06-25.
        return;
    }

    if ($drug_id != $wrl_last_drug_id) {
        ++$encount;
    }
    $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

    $stock_months = 0;
    if ($monthly != 0) {
        $stock_months = sprintf('%0.1f', $on_hand / $monthly);
        if ($stock_months < 1.0) {
            addWarning(xl('QOH is less than monthly usage'));
        }
    }

    // Check for reorder point reached, once per product.
    if ($drug_id != $wrl_last_drug_id) {
        if (checkReorder($drug_id, $row['reorder_point'])) {
            addWarning(xl('Product-level reorder point has been reached'));
        }
    }
    // For warehouse details mode we want the message on the line for this warehouse.
    // If the warehouse is not shown because it has no QOH and no recent
    // activity, then this message doesn't matter any more either.
    if ($form_details == 2) {
        if (checkReorder($drug_id, $row['pw_min_level'], $warehouse_id)) {
            addWarning(xl("Reorder point has been reached for warehouse") .
                " '" . $row['title'] . "'");
        }
    }

    // Get all lots that we want to issue warnings about.  These are lots
    // expired, soon to expire, or with insufficient quantity for selling.
    $gbl_expired_lot_warning_days = empty($gbl_expired_lot_warning_days) ? 0 : intval($gbl_expired_lot_warning_days);
    if ($gbl_expired_lot_warning_days <= 0) {
        $gbl_expired_lot_warning_days = 30;
    }
    $ires = sqlStatement(
        "SELECT di.* " .
        "FROM drug_inventory AS di " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "WHERE " .
        "di.drug_id = ? AND " .
        "di.on_hand > 0 AND " .
        "di.destroy_date IS NULL AND ( " .
        "di.on_hand < ? OR " .
        "di.expiration IS NOT NULL AND di.expiration < DATE_ADD(NOW(), INTERVAL " . escape_limit($gbl_expired_lot_warning_days) . " DAY) " .
        ") $extracond ORDER BY di.lot_number",
        array_merge(array($drug_id, $min_sale), $extrabind)
    );
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
            } elseif ($expdays <= $gbl_expired_lot_warning_days) {
                addWarning(xl('Lot') . " '$lotno' " . xl('expires in') . " $expdays " . xl('days'));
            }
        }
    }

    // Per CV 2014-06-20:
    // Reorder Quantity should be calculated only if Stock Months is less than Months Min.
    // If Stock Months is [not] less than Months Min, Reorder Quantity should be zero.
    // The calculation should be: (Min Months minus Stock Months) times Avg Monthly.
    // Reorder Quantity should be rounded up to a whole number.
    $reorder_qty = 0;
    if ($monthly > 0.00) {
        // Note if facility details, this the sum of min levels for the facility's warehouses.
        $min_months = 0 + ($form_details ? $row['pw_min_level'] : $row['reorder_point']);
        // If min is not specified as months then compute it that way.
        if (empty($GLOBALS['gbl_min_max_months'])) {
            $min_months /= $monthly;
        }
        if ($stock_months < $min_months) {
            $reorder_qty = ceil(($min_months - $stock_months) * $monthly);
        }
    }

    if (empty($monthly)) {
        $monthly = $emptyvalue;
    }
    if (empty($stock_months)) {
        $stock_months = $emptyvalue;
    }

    $relcodes = '';
    $tmp = explode(';', $row['related_code']);
    foreach ($tmp as $codestring) {
        if ($codestring === '') {
            continue;
        }
        list($codetype, $code) = explode(':', $codestring);
        // For IPPF just the IPPFCM codes are wanted.
        if ($GLOBALS['ippf_specific'] && $codetype !== 'IPPFCM') {
            continue;
        }
        if ($relcodes) {
            $relcodes .= ';';
        }
        $relcodes .= $codestring;
    }

    $drug_form = empty($row['form']) ? '' : generate_display_field(
        array(
            'data_type' => '1',
            'list_id' => 'drug_form'
        ),
        $row['form']
    );

    if ($form_action == 'export') {
        echo csvEscape($row['name'])                          . ',';
        echo csvEscape($relcodes)                             . ',';
        echo csvEscape($row['ndc_number'])                    . ',';
        echo csvEscape($row['active'] ? xl('Yes') : xl('No')) . ',';
        echo csvEscape($row['consumable'] ? xl('Yes') : xl('No')) . ',';
        echo csvEscape($drug_form) . ',';
        if ($form_details) {
            echo csvEscape($row['facname'])                   . ',';
            if ($form_details == 2) { // warehouse details {
                echo csvEscape($row['title'])                 . ',';
            }
            echo csvEscape($row['pw_min_level'])              . ',';
            echo csvEscape($row['pw_max_level'])              . ',';
        } else {
            echo csvEscape($row['reorder_point'])             . ',';
            echo csvEscape($row['max_level'])                 . ',';
        }
        echo csvEscape($row['on_hand'])                       . ',';
        echo csvEscape($zerodays)                             . ',';
        echo csvEscape($monthly)                              . ',';
        echo csvEscape($stock_months)                         . ',';
        echo csvEscape($reorder_qty)                          . ',';
        echo csvEscape($warnings)                             . '';
        echo "\n";
    } else { // end exporting
        echo " <tr class='detail' bgcolor='$bgcolor'>\n";
        if ($drug_id == $wrl_last_drug_id) {
            echo "  <td colspan='6'>&nbsp;</td>\n";
        } else {
            echo "  <td>" . text($row['name'])                            . "</td>\n";
            echo "  <td>" . text($relcodes)                               . "</td>\n";
            echo "  <td>" . text($row['ndc_number'])                      . "</td>\n";
            echo "  <td>" . ($row['active'] ? xlt('Yes') : xlt('No'))     . "</td>\n";
            echo "  <td>" . ($row['consumable'] ? xlt('Yes') : xlt('No')) . "</td>\n";
            echo "  <td>" . text($drug_form)                              . "</td>\n";
        }
        if ($form_details) {
            echo "  <td>" . text($row['facname'])                         . "</td>\n";
            if ($form_details == 2) { // warehouse details {
                echo "  <td>" . text($row['title'])                         . "</td>\n";
            }
            echo "  <td align='right'>" . text($row['pw_min_level'])      . "</td>\n";
            echo "  <td align='right'>" . text($row['pw_max_level'])      . "</td>\n";
        } else {
            echo "  <td align='right'>" . text($row['reorder_point'])     . "</td>\n";
            echo "  <td align='right'>" . text($row['max_level'])         . "</td>\n";
        }
        echo "  <td align='right'>" . text($row['on_hand'])               . "</td>\n";
        echo "  <td align='right'>" . text($zerodays)                     . "</td>\n";
        // Do not text() these next 2 lines.
        echo "  <td align='right'>" . $monthly                            . "</td>\n";
        echo "  <td align='right'>" . $stock_months                       . "</td>\n";
        echo "  <td align='right'>" . text($reorder_qty)                  . "</td>\n";
        // Do not text() the following line.
        echo "  <td style='color:red'>" . $warnings                       . "</td>\n";
        echo " </tr>\n";
    } // end not exporting

    $wrl_last_drug_id = $drug_id;
}

if (!empty($_POST['form_days'])) {
    $form_days = $_POST['form_days'] + 0;
} else {
    $form_days = sprintf('%d', (strtotime(date('Y-m-d')) - strtotime(date('Y-01-01'))) / (60 * 60 * 24) + 1);
}

// this is "" or "submit".
$form_action = $_POST['form_action'] ?? '';

if (!empty($_POST['form_days'])) {
    $form_days = $_POST['form_days'] + 0;
} else {
    $form_days = sprintf('%d', (strtotime(date('Y-m-d')) - strtotime(date('Y-01-01'))) / (60 * 60 * 24) + 1);
}

// this is "" or "submit" or "export".
$form_action = empty($_POST['form_action']) ? '' : $_POST['form_action'];

$form_inactive = empty($_REQUEST['form_inactive']) ? 0 : 1;
$form_details = empty($_REQUEST['form_details']) ? 0 : intval($_REQUEST['form_details']);
$form_facility = 0 + empty($_REQUEST['form_facility']) ? 0 : $_REQUEST['form_facility'];
$form_consumable = isset($_REQUEST['form_consumable']) ? intval($_REQUEST['form_consumable']) : 0;
$form_orderby = $_REQUEST['form_orderby'] ?? 'name';
$orderby = $ORDERHASH[$form_orderby];

// Incoming form_warehouse, if not empty is in the form "warehouse/facility".
// The facility part is an attribute used by JavaScript logic.
$form_warehouse = $_REQUEST['form_warehouse'] ?? '';
$tmp = explode('/', $form_warehouse);
$form_warehouse = $tmp[0];

$mmtype = empty($GLOBALS['gbl_min_max_months']) ? xl('Units') : xl('Months');

// Compute WHERE condition for filtering on facility/warehouse.
$fwcond = '';
$fwbind = array();
if ($form_facility) {
    $fwcond .= " AND lo.option_value IS NOT NULL AND lo.option_value = ?";
    $fwbind[] = $form_facility;
}
if ($form_warehouse) {
    $fwcond .= " AND di.warehouse_id IS NOT NULL AND di.warehouse_id = ?";
    $fwbind[] = $form_warehouse;
}
if ($is_user_restricted) {
    $fwcond .= "AND di.warehouse_id IS NOT NULL AND di.warehouse_id IN (" . genUserWarehouses() . ")";
}

// Compute WHERE condition for filtering on activity and consumability (drugs table).
$actcond = '';
if (!$form_inactive) {
    $actcond .= " AND d.active = 1";
}
if ($form_consumable) {
    if ($form_consumable == 1) {
        $actcond .= " AND d.consumable = '1'";
    } else {
        $actcond .= " AND d.consumable != '1'";
    }
}

if ($form_details == 1) {
    // Query for the main loop if facility details are wanted.
    $query = "SELECT d.*, SUM(di.on_hand) AS on_hand, lo.option_value, fac.name AS facname, " .
        "SUM(pw.pw_min_level) AS pw_min_level, SUM(pw.pw_max_level) AS pw_max_level " .
        "FROM drugs AS d " .
        "LEFT JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
        "AND di.on_hand != 0 AND di.destroy_date IS NULL " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "LEFT JOIN facility AS fac ON fac.id = lo.option_value " .
        "LEFT JOIN product_warehouse AS pw ON pw.pw_drug_id = d.drug_id AND " .
        "pw.pw_warehouse = di.warehouse_id " .
        "WHERE 1 = 1 $fwcond $actcond " .
        "GROUP BY d.name, d.drug_id, lo.option_value ORDER BY $orderby, lo.option_value";
} elseif ($form_details == 2) {
    // Query for the main loop if warehouse/lot details are wanted.
    $query = "SELECT d.*, di.on_hand, di.inventory_id, di.lot_number, " .
        "di.expiration, di.warehouse_id, lo.title, fac.name AS facname, " .
        "pw.pw_min_level, pw.pw_max_level " .
        "FROM drugs AS d " .
        "LEFT JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
        "AND di.on_hand != 0 AND di.destroy_date IS NULL " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "LEFT JOIN facility AS fac ON fac.id = lo.option_value " .
        "LEFT JOIN product_warehouse AS pw ON pw.pw_drug_id = d.drug_id AND " .
        "pw.pw_warehouse = di.warehouse_id " .
        "WHERE 1 = 1 $fwcond $actcond " .
        "ORDER BY $orderby, lo.title, di.warehouse_id, di.lot_number, di.inventory_id";
} else {
    // Query for the main loop if summary report.
    $query = "SELECT d.*, SUM(di.on_hand) AS on_hand " .
        "FROM drugs AS d " .
        "LEFT JOIN drug_inventory AS di ON di.drug_id = d.drug_id " .
        "AND di.on_hand != 0 AND di.destroy_date IS NULL " .
        "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
        "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
        "WHERE 1 = 1 $fwcond $actcond " .
        "GROUP BY $orderby ORDER BY $orderby";
}

// $res = sqlStatement($query);
$res = sqlStatement($query, $fwbind);

if ($form_action == 'export') {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download; charset=utf-8");
    header("Content-Disposition: attachment; filename=inventory_list.csv");
    header("Content-Description: File Transfer");
    // Prepend a BOM (Byte Order Mark) header to mark the data as UTF-8.  This is
    // said to work for Excel 2007 pl3 and up and perhaps also Excel 2003 pl3.  See:
    // http://stackoverflow.com/questions/155097/microsoft-excel-mangles-diacritics-in-csv-files
    // http://crashcoursing.blogspot.com/2011/05/exporting-csv-with-special-characters.html
    echo "\xEF\xBB\xBF";

    // CSV headers:
    echo csvEscape(xl('Name'))              . ',';
    echo csvEscape(xl('Relates To'))        . ',';
    echo csvEscape(xl('NDC'))               . ',';
    echo csvEscape(xl('Active'))            . ',';
    echo csvEscape(xl('Consumable'))        . ',';
    echo csvEscape(xl('Form'))              . ',';
    if ($form_details) {
        echo csvEscape(xl('Facility'))      . ',';
        if ($form_details == 2) {
            echo csvEscape(xl('Warehouse')) . ',';
        }
        echo csvEscape($mmtype . xl('Min')) . ',';
        echo csvEscape($mmtype . xl('Max')) . ',';
    }
    echo csvEscape(xl('QOH'))               . ',';
    echo csvEscape(xl('Zero Stock Days'))   . ',';
    echo csvEscape(xl('Avg Monthly'))       . ',';
    echo csvEscape(xl('Stock Months'))      . ',';
    echo csvEscape(xl('Reorder Qty'))       . ',';
    echo csvEscape(xl('Warnings'))          . '';
    echo "\n";
} else { // not exporting
    ?>
<html>

<head>

<title><?php echo xlt('Inventory List'); ?></title>

    <?php Header::setupHeader(['report-helper']); ?>

<style>
/* specifically include & exclude from printing */
@media print {
 #report_parameters {visibility: hidden; display: none;}
 #report_parameters_daterange {visibility: visible; display: inline;}
 #report_results {margin-top: 30px;}
}
/* specifically exclude some from the screen */
@media screen {
 #report_parameters_daterange {visibility: hidden; display: none;}
}

body       { font-family:sans-serif; font-size:10pt; font-weight:normal }

tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }

table.mymaintable, table.mymaintable td, table.mymaintable th {
 border: 1px solid #aaaaaa;
 border-collapse: collapse;
}
table.mymaintable td, table.mymaintable th {
 padding: 1pt 4pt 1pt 4pt;
}
</style>

<script>

 $(function () {
  oeFixedHeaderSetup(document.getElementById('mymaintable'));
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));
 });

 function mysubmit(action) {
  var f = document.forms[0];
  f.form_action.value = action;
  top.restoreSession();
  f.submit();
 }

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.form_action.value = 'submit';
  top.restoreSession();
  f.submit();
  return false;
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
   if (dis) theopts[i].selected = false;
  }
 }

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class='body_top'>

<center>

<form method='post' action='inventory_list.php' name='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<!-- form_action is set to "submit" or "export" at form submit time -->
<input type='hidden' name='form_action' value='' />

<!-- Sorting key -->
<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby) ?>" />

<div id="report_parameters">
<table>
 <tr>
  <td class='title'>
    <?php echo xlt('Inventory List'); ?>
  </td>
  <td class='text' align='right'>
    <?php
    // Build a drop-down list of facilities.
    //
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
    echo "   </select>&nbsp;\n";
    echo "   <select name='form_warehouse'>\n";
    echo "    <option value=''>" . xlt('All Warehouses') . "</option>\n";
    $lres = sqlStatement(
        "SELECT * FROM list_options " .
        "WHERE list_id = 'warehouse' AND activity = 1 ORDER BY seq, title"
    );
    while ($lrow = sqlFetchArray($lres)) {
        $whid = $lrow['option_id'];
        $facid = $lrow['option_value'];
        if ($is_user_restricted && !isWarehouseAllowed($facid, $whid)) {
            continue;
        }
        echo "    <option value='" . attr($whid . "/" . $facid) . "'";
        echo " id='fac" . attr($facid) . "'";
        if (strlen($form_warehouse)  > 0 && $whid == $form_warehouse) {
            echo " selected";
        }
        echo ">" . text(xl_list_label($lrow['title'])) . "</option>\n";
    }
    echo "   </select>&nbsp;\n";
    ?>

    <?php echo xlt('For the past'); ?>
   <input type="input" name="form_days" size='3' value="<?php echo attr($form_days); ?>" />
    <?php echo xlt('days'); ?>&nbsp;

   <select name='form_consumable'><?php
    foreach (
        array(
        '0' => xl('All Product Types'),
        '1' => xl('Consumable Only'),
        '2' => xl('Non-Consumable Only'),
        ) as $key => $value
    ) {
        echo "<option value='" . attr($key) . "'";
        if ($key == $form_consumable) {
            echo " selected";
        }
        echo ">" . text($value) . "</option>";
    }
    ?></select>&nbsp;

   <input type='checkbox' name='form_inactive' value='1'<?php if ($form_inactive) {
        echo " checked";} ?>
   /><?php echo xlt('Include Inactive'); ?>&nbsp;

    <?php
    echo "   <select name='form_details'>\n";
    $tmparr = array(0 => xl('Summary'), 1 => xl('Facility Details'), 2 => xl('Warehouse Details'));
    foreach ($tmparr as $key => $value) {
        echo "    <option value='" . attr($key) . "'";
        if ($key == $form_details) {
            echo " selected";
        }
        echo ">" . text($value) . "\n";
    }
    echo "   </select>&nbsp;\n";
    ?>
   <a href='#' class='btn btn-primary' onclick='mysubmit("submit")' style='margin-left:1em'>
    <span><?php echo xlt('Refresh'); ?></span>
   </a>
   <a href='#' class='btn btn-primary' onclick='mysubmit("export")' style='margin-left:1em'>
    <span><?php echo xlt('Export to CSV'); ?></span>
   </a>
   <a href='#' class='btn btn-primary' onclick='window.print()' style='margin-left:1em' id='printbutton'>
    <span><?php echo xlt('Print'); ?></span>
   </a>
  </td>
 </tr>
</table>
</div>

    <?php
    if ($form_action) {
        // We are not exporting and have submitted the form.
        ?>
<div id="report_results">
<table width='98%' id='mymaintable' class='mymaintable'>
 <thead style='display:table-header-group'>
  <tr class='head'>
   <th>
    <a href="#" onclick="return dosort('name')"
        <?php if ($form_orderby == "name") {
            echo " style=\"color:#00cc00\"";} ?>>
        <?php echo xlt('Name'); ?> </a>
   </th>
   <th><?php echo xlt('Relates To'); ?></th>
   <th><?php echo xlt('NDC'); ?></th>
   <th>
    <a href="#" onclick="return dosort('act')"
        <?php echo $form_orderby == "act" ? " style=\"color:#00cc00\"" : ""; ?>>
        <?php echo xlt('Active'); ?> </a>
   </th>
   <th>
    <a href="#" onclick="return dosort('con')"
        <?php echo $form_orderby == "con" ? " style=\"color:#00cc00\"" : ""; ?>>
        <?php echo xlt('Consumable'); ?> </a>
   </th>
   <th><?php echo xlt('Form'); ?></th>
        <?php if ($form_details) { ?>
   <th><?php echo xlt('Facility'); ?></th>
            <?php if ($form_details == 2) { ?>
   <th><?php echo xlt('Warehouse'); ?></th>
<?php } ?>
<?php } ?>
   <th align='right'><?php echo text("$mmtype " . xl('Min')); ?></th>
   <th align='right'><?php echo text("$mmtype " . xl('Max')); ?></th>
   <th align='right'><?php echo xlt('QOH'); ?></th>
   <th align='right'><?php echo xlt('Zero Stock Days'); ?></th>
   <th align='right'><?php echo xlt('Avg Monthly'); ?></th>
   <th align='right'><?php echo xlt('Stock Months'); ?></th>
   <th align='right'><?php echo xlt('Reorder Qty'); ?></th>
   <th><?php echo xlt('Warnings'); ?></th>
  </tr>
 </thead>
 <tbody>
        <?php
    } // end if submit
} // end not exporting

if ($form_action) { // if submit
    $encount = 0;
    $last_drug_id = '';
    $wrl_last_drug_id = '';
    $warehouse_row = array('drug_id' => 0, 'warehouse_id' => '');

    while ($row = sqlFetchArray($res)) {
        $drug_id = (int) $row['drug_id'];
        if ($form_details == 2) {
            if ($drug_id != $last_drug_id || $row['warehouse_id'] != $warehouse_row['warehouse_id']) {
                if (!empty($warehouse_row['drug_id'])) {
                    write_report_line($warehouse_row);
                }
                $warehouse_row = $row;
                $warehouse_row['on_hand'] = 0;
            }
            $warehouse_row['on_hand'] += $row['on_hand'];
        } else {
            write_report_line($row);
        }
        $last_drug_id = $drug_id;
    }

    if ($form_details == 2) {
        if (!empty($warehouse_row['drug_id'])) {
            write_report_line($warehouse_row);
        }
    }

    if ($form_action != 'export') {
        echo " </tbody>\n";
        echo "</table>\n";
        echo "</div>\n";
    }
} // end if submit

if ($form_action != 'export') {
    ?>
</form>
</center>
</body>
</html>
    <?php
} // end not export
