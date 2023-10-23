<?php

/**
 * This is an inventory transactions list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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
use OpenEMR\Common\Utils\FormatMoney;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

function thisLineItem($row, $xfer = false)
{
    global $grandtotal, $grandqty, $encount, $form_action;

    $invnumber = '';
    $dpname = '';

    if (!empty($row['pid'])) {
        $ttype = xl('Sale');
        $dpname = $row['plname'];
        if (!empty($row['pfname'])) {
            $dpname .= ', ' . $row['pfname'];
            if (!empty($row['pmname'])) {
                $dpname .= ' ' . $row['pmname'];
            }
        }

        $invnumber = empty($row['invoice_refno']) ?
        "{$row['pid']}.{$row['encounter']}" : $row['invoice_refno'];
    } elseif (!empty($row['distributor_id'])) {
        $ttype = xl('Distribution');
        if (!empty($row['organization'])) {
            $dpname = $row['organization'];
        } else {
            $dpname = $row['dlname'];
            if (!empty($row['dfname'])) {
                $dpname .= ', ' . $row['dfname'];
                if (!empty($row['dmname'])) {
                    $dpname .= ' ' . $row['dmname'];
                }
            }
        }
    } elseif (!empty($row['xfer_inventory_id']) || $xfer) {
        $ttype = xl('Transfer');
    } elseif ($row['fee'] != 0) {
        $ttype = xl('Purchase');
    } else {
        $ttype = xl('Adjustment');
    }

    if ($form_action == 'export') {
        echo csvEscape(oeFormatShortDate($row['sale_date'])) . ',';
        echo csvEscape($ttype)                               . ',';
        echo csvEscape($row['name'])                         . ',';
        echo csvEscape($row['lot_number'])                   . ',';
        echo csvEscape($row['warehouse'])                    . ',';
        echo csvEscape($dpname)                              . ',';
        echo csvEscape(0 - $row['quantity'])                 . ',';
        echo csvEscape(FormatMoney::getBucks($row['fee']))   . ',';
        echo csvEscape($row['billed'])                       . ',';
        echo csvEscape($row['notes'])                        . "\n";
    } else {
        $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";
        ?>

     <tr bgcolor="<?php echo $bgcolor; ?>">
  <td class="detail">
        <?php echo text(oeFormatShortDate($row['sale_date'])); ?>
  </td>
  <td class="detail">
        <?php echo text($ttype); ?>
  </td>
  <td class="detail">
        <?php echo text($row['name']); ?>
  </td>
  <td class="detail">
        <?php echo text($row['lot_number']); ?>
  </td>
  <td class="detail">
        <?php echo text($row['warehouse']); ?>
  </td>
  <td class="detail">
        <?php echo text($dpname); ?>
  </td>
  <td class="detail" align="right">
        <?php echo text(0 - $row['quantity']); ?>
  </td>
  <td class="detail" align="right">
        <?php echo text(FormatMoney::getBucks($row['fee'])); ?>
  </td>
  <td class="detail" align="center">
        <?php echo empty($row['billed']) ? '&nbsp;' : '*'; ?>
  </td>
  <td class="detail">
        <?php echo text($row['notes']); ?>
  </td>
 </tr>
        <?php
    } // End not csv export

    $grandtotal   += $row['fee'];
    $grandqty     -= $row['quantity'];

  // In the special case of a transfer, generate a second line item for
  // the source lot.
    if (!empty($row['xfer_inventory_id'])) {
        $row['xfer_inventory_id'] = 0;
        $row['lot_number'] = $row['lot_number_2'];
        $row['warehouse'] = $row['warehouse_2'];
        $row['quantity'] = 0 - $row['quantity'];
        $row['fee'] = 0 - $row['fee'];
        thisLineItem($row, true);
    }
} // end function

if (! AclMain::aclCheckCore('acct', 'rep')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Inventory Transactions")]);
    exit;
}

// this is "" or "submit" or "export".
$form_action = $_POST['form_action'];

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_trans_type = isset($_POST['form_trans_type']) ? $_POST['form_trans_type'] : '0';

$encount = 0;

if ($form_action == 'export') {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=inventory_transactions.csv");
    header("Content-Description: File Transfer");
    // CSV headers:
    echo csvEscape(xl('Date')) . ',';
    echo csvEscape(xl('Transaction')) . ',';
    echo csvEscape(xl('Product')) . ',';
    echo csvEscape(xl('Lot')) . ',';
    echo csvEscape(xl('Warehouse')) . ',';
    echo csvEscape(xl('Who')) . ',';
    echo csvEscape(xl('Qty')) . ',';
    echo csvEscape(xl('Amount')) . ',';
    echo csvEscape(xl('Billed')) . ',';
    echo csvEscape(xl('Notes')) . "\n";
} else { // end export
    ?>
<html>
<head>
<title><?php echo xlt('Inventory Transactions'); ?></title>

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
  #report_parameters_daterange {
      visibility: hidden;
      display: none;
}
 }

 body {
     font-family:sans-serif;
     font-size:10pt;
     font-weight:normal;
}
 .dehead {
     color:var(--black);
     font-family:sans-serif;
     font-size:10pt;
     font-weight:bold;
}
 .detail { color:var(--black);
     font-family:sans-serif;
     font-size:10pt;
     font-weight:normal;
}

 #report_results table thead {
  font-size:10pt;
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

<h2><?php echo xlt('Inventory Transactions'); ?></h2>

<form method='post' action='inventory_transactions.php' onsubmit='return top.restoreSession()'>
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
        <?php echo xlt('Type'); ?>:
     </td>
     <td nowrap>
      <select name='form_trans_type' onchange='trans_type_changed()'>
    <?php
    foreach (
        array(
        '0' => xl('All'),
        '2' => xl('Purchase/Return'),
        '1' => xl('Sale'),
        '6' => xl('Distribution'),
        '4' => xl('Transfer'),
        '5' => xl('Adjustment'),
        ) as $key => $value
    ) {
        echo "       <option value='" . attr($key) . "'";
        if ($key == $form_trans_type) {
            echo " selected";
        }

        echo ">" . text($value) . "</option>\n";
    }
    ?>
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
        <?php xl('To{{Range}}', 'e'); ?>:
     </td>
     <td nowrap>
      <input type='text' class='datepicker' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>' />
     </td>
    </tr>
   </table>
  </td>
  <td align='left' valign='middle'>
   <table style='border-left:1px solid; width:100%; height:100%'>
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
<table border='0' cellpadding='1' cellspacing='2' width='98%' id='mymaintable' class='mymaintable'>
 <thead>
 <tr bgcolor="#dddddd">
  <td class="dehead">
        <?php echo xlt('Date'); ?>
  </td>
  <td class="dehead">
        <?php echo xlt('Transaction'); ?>
  </td>
  <td class="dehead">
        <?php echo xlt('Product'); ?>
  </td>
  <td class="dehead">
        <?php echo xlt('Lot'); ?>
  </td>
  <td class="dehead">
        <?php echo xlt('Warehouse'); ?>
  </td>
  <td class="dehead">
        <?php echo xlt('Who'); ?>
  </td>
  <td class="dehead" align="right">
        <?php echo xlt('Qty'); ?>
  </td>
  <td class="dehead" align="right">
        <?php echo xlt('Amount'); ?>
  </td>
  <td class="dehead" align="Center">
        <?php echo xlt('Billed'); ?>
  </td>
  <td class="dehead">
        <?php echo xlt('Notes'); ?>
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

    $grandtotal = 0;
    $grandqty = 0;

    $query = "SELECT s.sale_date, s.fee, s.quantity, s.pid, s.encounter, " .
    "s.billed, s.notes, s.distributor_id, s.xfer_inventory_id, " .
    "p.fname AS pfname, p.mname AS pmname, p.lname AS plname, " .
    "u.fname AS dfname, u.mname AS dmname, u.lname AS dlname, u.organization, " .
    "d.name, fe.date, fe.invoice_refno, " .
    "i1.lot_number, i2.lot_number AS lot_number_2, " .
    "lo1.title AS warehouse, lo2.title AS warehouse_2 " .
    "FROM drug_sales AS s " .
    "JOIN drugs AS d ON d.drug_id = s.drug_id " .
    "LEFT JOIN drug_inventory AS i1 ON i1.inventory_id = s.inventory_id " .
    "LEFT JOIN drug_inventory AS i2 ON i2.inventory_id = s.xfer_inventory_id " .
    "LEFT JOIN patient_data AS p ON p.pid = s.pid " .
    "LEFT JOIN users AS u ON u.id = s.distributor_id " .
    "LEFT JOIN list_options AS lo1 ON lo1.list_id = 'warehouse' AND " .
    "lo1.option_id = i1.warehouse_id AND lo1.activity = 1 " .
    "LEFT JOIN list_options AS lo2 ON lo2.list_id = 'warehouse' AND " .
    "lo2.option_id = i2.warehouse_id AND lo2.activity = 1 " .
    "LEFT JOIN form_encounter AS fe ON fe.pid = s.pid AND fe.encounter = s.encounter " .
    "WHERE s.sale_date >= ? AND s.sale_date <= ? ";
    if ($form_trans_type == 2) { // purchase/return
        $query .= "AND s.pid = 0 AND s.distributor_id = 0 AND s.xfer_inventory_id = 0 AND s.fee != 0 ";
    } elseif ($form_trans_type == 4) { // transfer
        $query .= "AND s.xfer_inventory_id != 0 ";
    } elseif ($form_trans_type == 5) { // adjustment
        $query .= "AND s.pid = 0 AND s.distributor_id = 0 AND s.xfer_inventory_id = 0 AND s.fee = 0 ";
    } elseif ($form_trans_type == 6) { // distribution
        $query .= "AND s.distributor_id != 0 ";
    } elseif ($form_trans_type == 1) { // sale
        $query .= "AND s.pid != 0 ";
    }

    $query .= "ORDER BY s.sale_date, s.sale_id";
  //
    $res = sqlStatement($query, array($from_date, $to_date));
    while ($row = sqlFetchArray($res)) {
        thisLineItem($row);
    }

  // Grand totals line.
    if ($form_action != 'export') { // if submit
        ?>

   <tr bgcolor="#dddddd">
    <td class="dehead" colspan="6">
        <?php echo xlt('Grand Total'); ?>
  </td>
  <td class="dehead" align="right">
        <?php echo text($grandqty); ?>
  </td>
  <td class="dehead" align="right">
        <?php echo text(FormatMoney::getBucks($grandtotal)); ?>
  </td>
  <td class="dehead" colspan="2">

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
