<?php

/**
 * Report columns:
 * Product Name (blank where repeated)
 * Warehouse Name (blank where repeated) or Total for Product
 * Starting Inventory (detail lines: date)
 * Ending Inventory   (detail lines: invoice ID)
 * Sales
 * Distributions
 * Purchases
 * Transfers
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Specify if product or warehouse is the first column.
$product_first = (!empty($_POST['form_by']) && $_POST['form_by'] == 'w') ? 0 : 1;

$last_warehouse_id = '~';
$last_product_id = 0;

// Get ending inventory for the report's end date.
// Optionally restricts by product ID and/or warehouse ID.
function getEndInventory($product_id = 0, $warehouse_id = '~')
{
    global $form_from_date, $form_to_date, $form_product;

    $whidcond = '';
    if ($warehouse_id !== '~') {
        $whidcond = $warehouse_id === '' ?
        "AND ( di.warehouse_id IS NULL OR di.warehouse_id = '' )" :
        "AND di.warehouse_id = '" . add_escape_custom($warehouse_id) . "'";
    }

    $prodcond = '';
    if ($form_product) {
        $product_id = $form_product;
    }

    if ($product_id) {
        $prodcond = "AND di.drug_id = '" . add_escape_custom($product_id) . "'";
    }

  // Get sum of current inventory quantities + destructions done after the
  // report end date (which is effectively a type of transaction).
    $eirow = sqlQuery("SELECT sum(di.on_hand) AS on_hand " .
    "FROM drug_inventory AS di WHERE " .
    "( di.destroy_date IS NULL OR di.destroy_date > ? ) " .
    "$prodcond $whidcond", array($form_to_date));

  // Get sum of sales/adjustments/purchases after the report end date.
    $sarow = sqlQuery("SELECT sum(ds.quantity) AS quantity " .
    "FROM drug_sales AS ds, drug_inventory AS di WHERE " .
    "ds.sale_date > ? AND " .
    "di.inventory_id = ds.inventory_id " .
    "$prodcond $whidcond", array($form_to_date));

  // Get sum of transfers out after the report end date.
    $xfrow = sqlQuery("SELECT sum(ds.quantity) AS quantity " .
    "FROM drug_sales AS ds, drug_inventory AS di WHERE " .
    "ds.sale_date > ? AND " .
    "di.inventory_id = ds.xfer_inventory_id " .
    "$prodcond $whidcond", array($form_to_date));

    return $eirow['on_hand'] + $sarow['quantity'] - $xfrow['quantity'];
}

function thisLineItem(
    $product_id,
    $warehouse_id,
    $patient_id,
    $encounter_id,
    $rowprod,
    $rowwh,
    $transdate,
    $qtys,
    $irnumber = ''
) {

    global $warehouse, $product, $secqtys, $priqtys, $grandqtys;
    global $whleft, $prodleft; // left 2 columns, blank where repeated
    global $last_warehouse_id, $last_product_id, $product_first;
    global $form_action;

    $invnumber = empty($irnumber) ? ($patient_id ? "$patient_id.$encounter_id" : "") : $irnumber;

  // Product name for this detail line item.
    if (empty($rowprod)) {
        $rowprod = 'Unnamed Product';
    }

  // Warehouse name for this line item.
    if (empty($rowwh)) {
        $rowwh = 'None';
    }

  // If new warehouse or product...
    if ($warehouse_id != $last_warehouse_id || $product_id != $last_product_id) {
        // If there was anything to total...
        if (($product_first && $last_warehouse_id != '~') || (!$product_first && $last_product_id)) {
            $secei = getEndInventory($last_product_id, $last_warehouse_id);

            // Print second-column totals.
            if ($form_action == 'export') {
                // Export:
                if (! $_POST['form_details']) {
                    if ($product_first) {
                        echo csvEscape($product);
                        echo ',' . csvEscape($warehouse);
                    } else {
                        echo csvEscape($warehouse);
                        echo ',' . csvEscape($product);
                    }

                    echo ',' . csvEscape($secei - $secqtys[0] - $secqtys[1] - $secqtys[2] - $secqtys[3] - $secqtys[4]); // start inventory
                    echo ',' . csvEscape($secqtys[0]); // sales
                    echo ',' . csvEscape($secqtys[1]); // distributions
                    echo ',' . csvEscape($secqtys[2]); // purchases
                    echo ',' . csvEscape($secqtys[3]); // transfers
                    echo ',' . csvEscape($secqtys[4]); // adjustments
                    echo ',' . csvEscape($secei); // end inventory
                    echo "\n";
                }
            } else {
                // Not export:
                ?>
                <tr bgcolor="#ddddff">
                <?php if ($product_first) { ?>
                    <td class="detail">
                        <?php echo text($prodleft);
                        $prodleft = " "; ?>
                    </td>
                    <td class="detail" colspan='3'>
                        <?php
                        if ($_POST['form_details']) {
                            echo xlt('Total for') . ' ';
                        }
                        echo text($warehouse); ?>
                    </td>
                <?php } else { ?>
                    <td class="detail">
                        <?php echo text($whleft);
                        $whleft = " "; ?>
                    </td>
                    <td class="detail" colspan='3'>
                        <?php
                        if ($_POST['form_details']) {
                            echo xlt('Total for') . ' ';
                        }
                        echo text($product); ?>
                    </td>
                <?php } ?>
                <td class="dehead" align="right">
                    <?php echo text($secei - $secqtys[0] - $secqtys[1] - $secqtys[2] - $secqtys[3] - $secqtys[4]); ?>
                </td>
                <td class="dehead" align="right">
                    <?php echo text($secqtys[0]); ?>
                </td>
                <td class="dehead" align="right">
                    <?php echo text($secqtys[1]); ?>
                </td>
                <td class="dehead" align="right">
                    <?php echo text($secqtys[2]); ?>
                </td>
                <td class="dehead" align="right">
                    <?php echo text($secqtys[3]); ?>
                </td>
                <td class="dehead" align="right">
                    <?php echo text($secqtys[4]); ?>
                </td>
                <td class="dehead" align="right">
                    <?php echo text($secei); ?>
                </td>
                </tr>
                <?php
            } // End not csv export
        }

        $secqtys = array(0, 0, 0, 0, 0);
        if ($product_first) {
            $whleft = $warehouse = $rowwh;
            $last_warehouse_id = $warehouse_id;
        } else {
            $prodleft = $product = $rowprod;
            $last_product_id = $product_id;
        }
    }

    // If first column is changing, time for its totals.
    if (
        ($product_first && $product_id != $last_product_id) ||
        (!$product_first && $warehouse_id != $last_warehouse_id)
    ) {
        if (
            ($product_first && $last_product_id) ||
            (!$product_first && $last_warehouse_id != '~')
        ) {
            $priei = $product_first ? getEndInventory($last_product_id) :
              getEndInventory(0, $last_warehouse_id);
            // Print first column total.
            if ($form_action != 'export') {
                ?>

                <tr bgcolor="#ffdddd">
                <td class="detail">
                &nbsp;
                </td>
                <td class="detail" colspan="3">
                <?php echo xlt('Total for') . ' ';
                echo text($product_first ? $product : $warehouse); ?>
                </td>
                <td class="dehead" align="right">
                <?php echo text($priei - $priqtys[0] - $priqtys[1] - $priqtys[2] - $priqtys[3] - $priqtys[4]); ?>
                </td>
                <td class="dehead" align="right">
                <?php echo text($priqtys[0]); ?>
                </td>
                <td class="dehead" align="right">
                <?php echo text($priqtys[1]); ?>
                </td>
                <td class="dehead" align="right">
                <?php echo text($priqtys[2]); ?>
                </td>
                <td class="dehead" align="right">
                <?php echo text($priqtys[3]); ?>
                </td>
                <td class="dehead" align="right">
                <?php echo text($priqtys[4]); ?>
                </td>
                <td class="dehead" align="right">
                <?php echo text($priei); ?>
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

    // Detail line.
    if ($_POST['form_details'] && $product_id && ($qtys[0] + $qtys[1] + $qtys[2] + $qtys[3] + $qtys[4])) {
        if ($form_action == 'export') {
            if ($product_first) {
                echo csvEscape($product);
                echo ',' . csvEscape($warehouse);
            } else {
                echo csvEscape($warehouse);
                echo ',' . csvEscape($product);
            }

            echo ',' . csvEscape(oeFormatShortDate($transdate));
            echo ',' . csvEscape($invnumber);
            echo ',' . csvEscape($qtys[0]); // sales
            echo ',' . csvEscape($qtys[1]); // distributions
            echo ',' . csvEscape($qtys[2]); // purchases
            echo ',' . csvEscape($qtys[3]); // transfers
            echo ',' . csvEscape($qtys[4]); // adjustments
            echo "\n";
        } else {
            ?>
            <tr>
            <?php if ($product_first) { ?>
                <td class="detail">
                    <?php echo text($prodleft);
                    $prodleft = " "; ?>
                </td>
                <td class="detail">
                    <?php echo text($whleft);
                    $whleft = " "; ?>
                </td>
            <?php } else { ?>
                <td class="detail">
                    <?php echo text($whleft);
                    $whleft = " "; ?>
                </td>
                <td class="detail">
                    <?php echo text($prodleft);
                    $prodleft = " "; ?>
                </td>
            <?php } ?>
            <td class="dehead">
                <?php echo text(oeFormatShortDate($transdate)); ?>
            </td>
            <td class="detail">
                <?php echo text($invnumber); ?>
            </td>
            <td class="detail">
                &nbsp;
            </td>
            <td class="dehead" align="right">
                <?php echo text($qtys[0]); ?>
            </td>
            <td class="dehead" align="right">
                <?php echo text($qtys[1]); ?>
            </td>
            <td class="dehead" align="right">
                <?php echo text($qtys[2]); ?>
            </td>
            <td class="dehead" align="right">
                <?php echo text($qtys[3]); ?>
            </td>
            <td class="dehead" align="right">
                <?php echo text($qtys[4]); ?>
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

if (! AclMain::aclCheckCore('acct', 'rep')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Inventory Activity")]);
    exit;
}

// this is "" or "submit" or "export".
$form_action = $_POST['form_action'];

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_product  = $_POST['form_product'];

if ($form_action == 'export') {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=inventory_activity.csv");
    header("Content-Description: File Transfer");
  // CSV headers:
    if ($product_first) {
        echo csvEscape(xl('Product')) . ',';
        echo csvEscape(xl('Warehouse')) . ',';
    } else {
        echo csvEscape(xl('Warehouse')) . ',';
        echo csvEscape(xl('Product')) . ',';
    }

    if ($_POST['form_details']) {
        echo csvEscape(xl('Date')) . ',';
        echo csvEscape(xl('Invoice')) . ',';
        echo csvEscape(xl('Sales')) . ',';
        echo csvEscape(xl('Distributions')) . ',';
        echo csvEscape(xl('Purchases')) . ',';
        echo csvEscape(xl('Transfers')) . ',';
        echo csvEscape(xl('Adjustments')) . "\n";
    } else {
        echo csvEscape(xl('Start')) . ',';
        echo csvEscape(xl('Sales')) . ',';
        echo csvEscape(xl('Distributions')) . ',';
        echo csvEscape(xl('Purchases')) . ',';
        echo csvEscape(xl('Transfers')) . ',';
        echo csvEscape(xl('Adjustments')) . ',';
        echo csvEscape(xl('End')) . "\n";
    }
} else { // end export
    ?>
<html>
<head>
<title><?php echo xlt('Inventory Activity'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

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
 .dehead    { color:var(--black); font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:var(--black); font-family:sans-serif; font-size:10pt; font-weight:normal }

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

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });

    function mysubmit(action) {
        var f = document.forms[0];
        f.form_action.value = action;
        top.restoreSession();
        f.submit();
    }

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class='body_top'>

<center>

<h2><?php echo xlt('Inventory Activity'); ?></h2>

<form method='post' action='inventory_activity.php?product=<?php echo attr_url($product_first); ?>' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<!-- form_action is set to "submit" or "export" at form submit time -->
<input type='hidden' name='form_action' value='' />
<table>
 <tr>
  <td width='50%'>
   <table class='text'>
    <tr>
     <td class='label_custom'>
        <?php echo xlt('By'); ?>:
     </td>
     <td nowrap>
      <select name='form_by'>
       <option value='p'><?php echo xlt('Product'); ?></option>
       <option value='w'<?php echo (!$product_first) ? ' selected' : ''; ?>><?php echo xlt('Warehouse'); ?></option>
      </select>
     </td>
     <td class='label_custom'>
        <?php echo xlt('From'); ?>:
     </td>
     <td nowrap>
      <input type='text' class='datepicker' name='form_from_date' id="form_from_date" size='10'
       value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
     </td>
     <td class='label_custom'>
        <?php echo xlt('To{{Range}}'); ?>:
     </td>
     <td nowrap>
      <input type='text' class='datepicker' name='form_to_date' id="form_to_date" size='10'
       value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
     </td>
    </tr>
    <tr>
     <td class='label_custom'>
        <?php echo xlt('For'); ?>:
     </td>
     <td nowrap>
    <?php
// Build a drop-down list of products.
//
    $query = "SELECT drug_id, name FROM drugs ORDER BY name, drug_id";
    $pres = sqlStatement($query);
    echo "      <select name='form_product'>\n";
    echo "       <option value=''>-- " . xlt('All Products') . " --\n";
    while ($prow = sqlFetchArray($pres)) {
        $drug_id = $prow['drug_id'];
        echo "       <option value='" . attr($drug_id) . "'";
        if ($drug_id == $form_product) {
            echo " selected";
        }

        echo ">" . text($prow['name']) . "\n";
    }

    echo "      </select>\n";
    ?>
     </td>
     <td class='label_custom'>
        <?php echo xlt('Details'); ?>:
     </td>
     <td colspan='3' nowrap>
      <input type='checkbox' name='form_details' value='1'<?php echo ($_POST['form_details']) ? " checked" : "";?> />
     </td>
    </tr>
   </table>
  </td>
  <td align='left' valign='middle'>
   <table class='w-100 h-100' style='border-left:1px solid;'>
    <tr>
     <td valign='middle'>
      <a href='#' class='btn btn-primary' onclick='mysubmit("submit")' style='margin-left:1em'>
       <span><?php echo xlt('Submit'); ?></span>
      </a>
    <?php if ($form_action) { ?>
      <a href='#' class='btn btn-primary' id='printbutton' style='margin-left:1em'>
       <span><?php echo xlt('Print'); ?></span>
      </a>
      <a href='#' class='btn btn-primary' onclick='mysubmit("export")' style='margin-left:1em'>
       <span><?php echo xlt('CSV Export'); ?></span>
      </a>
<?php } ?>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</div>

    <?php if ($form_action) { // if submit (already not export here) ?>
<div id="report_results">
<table width='98%' id='mymaintable' class='mymaintable'>
 <thead>
 <tr bgcolor="#dddddd">
  <td class="dehead">
        <?php echo text($product_first ? xl('Product') : xl('Warehouse')); ?>
  </td>
        <?php if ($_POST['form_details']) { ?>
  <td class="dehead">
            <?php echo text($product_first ? xl('Warehouse') : xl('Product')); ?>
  </td>
  <td class="dehead">
            <?php echo xlt('Date'); ?>
  </td>
  <td class="dehead">
            <?php echo xlt('Invoice'); ?>
  </td>
<?php } else { ?>
  <td class="dehead" colspan="3">
            <?php echo text($product_first ? xl('Warehouse') : xl('Product')); ?>
  </td>
<?php } ?>
  <td class="dehead" align="right" width="8%">
        <?php echo xlt('Start'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
        <?php echo xlt('Sales'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
        <?php echo xlt('Distributions'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
        <?php echo xlt('Purchases'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
        <?php echo xlt('Transfers'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
        <?php echo xlt('Adjustments'); ?>
  </td>
  <td class="dehead" align="right" width="8%">
        <?php echo xlt('End'); ?>
  </td>
 </tr>
 </thead>
 <tbody>
        <?php
    } // end if submit
} // end not export

if ($form_action) { // if submit or export
    $from_date = $form_from_date;
    $to_date   = $form_to_date;

    $product   = "";
    $prodleft  = "";
    $warehouse = "";
    $whleft    = "";
    $grandqtys = array(0, 0, 0, 0, 0);
    $priqtys   = array(0, 0, 0, 0, 0);
    $secqtys   = array(0, 0, 0, 0, 0);
    $last_inventory_id = 0;

    $sqlBindArray = array();

    $query = "SELECT s.sale_id, s.sale_date, s.quantity, s.fee, s.pid, s.encounter, " .
    "s.xfer_inventory_id, s.distributor_id, d.name, lo.title, " .
    "di.drug_id, di.warehouse_id, di.inventory_id, di.destroy_date, di.on_hand, " .
    "fe.invoice_refno " .
    "FROM drug_inventory AS di " .
    "JOIN drugs AS d ON d.drug_id = di.drug_id " .
    "LEFT JOIN drug_sales AS s ON " .
    "s.sale_date >= ? AND s.sale_date <= ? AND " .
    "s.drug_id = di.drug_id AND " .
    "( s.inventory_id = di.inventory_id OR s.xfer_inventory_id = di.inventory_id ) " .
    "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
    "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
    "LEFT JOIN form_encounter AS fe ON fe.pid = s.pid AND fe.encounter = s.encounter " .
    "WHERE ( di.destroy_date IS NULL OR di.destroy_date >= ? ) AND " .
    "( di.on_hand != 0 OR s.sale_id IS NOT NULL )";

    array_push($sqlBindArray, $from_date, $to_date, $form_from_date);

    // If a product was specified.
    if ($form_product) {
        $query .= " AND di.drug_id = ?";
        array_push($sqlBindArray, $form_product);
    }

    if ($product_first) {
        $query .= " ORDER BY d.name, d.drug_id, lo.title, di.warehouse_id, " .
        "di.inventory_id, s.sale_date, s.sale_id";
    } else {
        $query .= " ORDER BY lo.title, di.warehouse_id, d.name, d.drug_id, " .
        "di.inventory_id, s.sale_date, s.sale_id";
    }

    $res = sqlStatement($query, $sqlBindArray);
    while ($row = sqlFetchArray($res)) {
        // If new lot and it was destroyed during the reporting period,
        // generate a pseudo-adjustment for that.
        if ($row['inventory_id'] != $last_inventory_id) {
            $last_inventory_id = $row['inventory_id'];
            if (
                !empty($row['destroy_date']) && $row['on_hand'] != 0
                && $row['destroy_date'] <= $form_to_date
            ) {
                thisLineItem(
                    $row['drug_id'],
                    $row['warehouse_id'],
                    0,
                    0,
                    $row['name'],
                    $row['title'],
                    $row['destroy_date'],
                    array(0, 0, 0, 0, 0 - $row['on_hand']),
                    xl('Destroyed')
                );
            }
        }

        $qtys = array(0, 0, 0, 0, 0);
        if ($row['sale_id']) {
            if ($row['xfer_inventory_id']) {
                // A transfer sale item will appear twice, once with each lot.
                if ($row['inventory_id'] == $row['xfer_inventory_id']) {
                    $qtys[3] = $row['quantity'];
                } else {
                    $qtys[3] = 0 - $row['quantity'];
                }
            } elseif ($row['pid']) {
                $qtys[0] = 0 - $row['quantity'];
            } elseif ($row['distributor_id']) {
                $qtys[1] = 0 - $row['quantity'];
            } elseif ($row['fee'] != 0) {
                $qtys[2] = 0 - $row['quantity'];
            } else { // no pid, distributor, source lot or fee: must be an adjustment
                $qtys[4] = 0 - $row['quantity'];
            }
        }

        thisLineItem(
            $row['drug_id'],
            $row['warehouse_id'],
            $row['pid'] + 0,
            $row['encounter'] + 0,
            $row['name'],
            $row['title'],
            $row['sale_date'],
            $qtys,
            $row['invoice_refno']
        );
    }

    // Generate totals for last product and warehouse.
    thisLineItem(0, '~', 0, 0, '', '', '0000-00-00', array(0, 0, 0, 0, 0));

    // Grand totals line.
    if ($form_action != 'export') { // if submit
        $grei = getEndInventory();
        ?>
        <tr bgcolor="#dddddd">
        <td class="detail" colspan="4">
            <?php echo xlt('Grand Total'); ?>
        </td>
        <td class="dehead" align="right">
            <?php echo text($grei - $grandqtys[0] - $grandqtys[1] - $grandqtys[2] - $grandqtys[3] - $grandqtys[4]); ?>
        </td>
        <td class="dehead" align="right">
            <?php echo text($grandqtys[0]); ?>
        </td>
        <td class="dehead" align="right">
            <?php echo text($grandqtys[1]); ?>
        </td>
        <td class="dehead" align="right">
            <?php echo text($grandqtys[2]); ?>
        </td>
        <td class="dehead" align="right">
            <?php echo text($grandqtys[3]); ?>
        </td>
        <td class="dehead" align="right">
            <?php echo text($grandqtys[4]); ?>
        </td>
        <td class="dehead" align="right">
            <?php echo text($grei); ?>
        </td>
        </tr>
        <?php
    } // End if submit
} // end if submit or export

if ($form_action != 'export') {
    if ($form_action) {
        ?>
        </tbody>
        </table>
        </div>
        <?php
    } // end if ($form_action)
    ?>
    </form>
    </center>
    </body>
    </html>
    <?php
} // End not export
?>
