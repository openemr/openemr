<?php

/**
 * ippf_cyp_report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

function formatcyp($amount)
{
    if ($amount) {
        return sprintf("%.2f", $amount);
    }

    return '';
}

function display_desc($desc)
{
    if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
        $desc = $matches[1];
    }

    return $desc;
}

function thisLineItem($patient_id, $encounter_id, $description, $transdate, $qty, $cypfactor, $irnumber = '')
{
    global $product, $productcyp, $producttotal, $productqty, $grandtotal, $grandqty;

    $invnumber = empty($irnumber) ? "$patient_id.$encounter_id" : $irnumber;
    $rowcyp    = sprintf('%01.2f', $cypfactor);
    $rowresult = sprintf('%01.2f', $rowcyp * $qty);

    $rowproduct = $description;
    if (! $rowproduct) {
        $rowproduct = 'Unknown';
    }

    if ($product != $rowproduct) {
        if ($product) {
            // Print product total.
            if ($_POST['form_csvexport']) {
                if (! $_POST['form_details']) {
                    echo csvEscape(display_desc($product)) . ',';
                    echo csvEscape($productqty)            . ',';
                    echo csvEscape(formatcyp($productcyp)) . ',';
                    echo csvEscape(formatcyp($producttotal)) . "\n";
                }
            } else {
                ?>

       <tr bgcolor="#ddddff">
        <td class="detail" colspan="<?php echo $_POST['form_details'] ? 3 : 1; ?>">
                <?php
                if ($_POST['form_details']) {
                    echo xlt('Total for ');
                }
                echo text(display_desc($product)); ?>
  </td>
  <td class="dehead" align="right">
                <?php echo text($productqty); ?>
  </td>
  <td class="dehead" align="right">
                <?php echo text(formatcyp($productcyp)); ?>
  </td>
  <td class="dehead" align="right">
                <?php echo text(formatcyp($producttotal)); ?>
  </td>
 </tr>
                <?php
            } // End not csv export
        }

        $producttotal = 0;
        $productqty = 0;
        $product = $rowproduct;
        $productleft = $product;
        $productcyp = $rowcyp;
    }

    if ($_POST['form_details']) {
        if ($_POST['form_csvexport']) {
            echo csvEscape(display_desc($product)) . ',';
            echo csvEscape(oeFormatShortDate(display_desc($transdate))) . ',';
            echo csvEscape(display_desc($invnumber)) . ',';
            echo csvEscape(display_desc($qty)) . ',';
            echo csvEscape(formatcyp($rowcyp)) . ',';
            echo csvEscape(formatcyp($rowresult)) . "\n";
        } else {
            ?>

     <tr>
      <td class="detail">
            <?php echo text(display_desc($productleft));
            $productleft = "&nbsp;"; ?>
  </td>
  <td class="dehead">
            <?php echo text(oeFormatShortDate($transdate)); ?>
  </td>
  <td class="detail">
            <?php echo text($invnumber); ?>
  </td>
  <td class="dehead" align="right">
            <?php echo text($qty); ?>
  </td>
  <td class="dehead" align="right">
            <?php echo text(formatcyp($rowcyp)); ?>
  </td>
  <td class="dehead" align="right">
            <?php echo text(formatcyp($rowresult)); ?>
  </td>
 </tr>
            <?php
        } // End not csv export
    } // end details
    $producttotal += $rowresult;
    $grandtotal   += $rowresult;
    $productqty   += $qty;
    $grandqty     += $qty;
} // end function

if (! AclMain::aclCheckCore('acct', 'rep')) {
    die(xlt("Unauthorized access."));
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_POST['form_facility'];

if ($_POST['form_csvexport']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=ippf_cyp_report.csv");
    header("Content-Description: File Transfer");
  // CSV headers:
    if ($_POST['form_details']) {
        echo csvEscape("Item") . ',';
        echo csvEscape("Date") . ',';
        echo csvEscape("Invoice") . ',';
        echo csvEscape("Qty") . ',';
        echo csvEscape("CYP") . ',';
        echo csvEscape("Result") . "\n";
    } else {
        echo csvEscape("Item") . ',';
        echo csvEscape("Qty") . ',';
        echo csvEscape("CYP") . ',';
        echo csvEscape("Result") . "\n";
    }
} else { // not export
    ?>
<html>
<head>

<title><?php echo xlt('CYP Report') ?></title>

    <?php Header::setupHeader(['datetime-picker']); ?>

<script>
    $(function () {
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
</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php echo xlt('CYP Report')?></h2>

<form method='post' action='ippf_cyp_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<table border='0' cellpadding='3'>

 <tr>
  <td>
    <?php
  // Build a drop-down list of facilities.
  //
    $query = "SELECT id, name FROM facility ORDER BY name";
    $fres = sqlStatement($query);
    echo "   <select name='form_facility'>\n";
    echo "    <option value=''>-- All Facilities --\n";
    while ($frow = sqlFetchArray($fres)) {
        $facid = $frow['id'];
        echo "    <option value='" . attr($facid) . "'";
        if ($facid == $form_facility) {
            echo " selected";
        }

        echo ">" . text($frow['name']) . "\n";
    }

    echo "   </select>\n";
    ?>
   &nbsp;<?php echo xlt('From')?>:
   <input type='text' class='datepicker' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
   &nbsp;<?php echo xlt('To{{Range}}')?>:
   <input type='text' class='datepicker' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
   &nbsp;
   <input type='checkbox' name='form_details' value='1'<?php echo ($_POST['form_details']) ? " checked" : ""; ?>><?php echo xlt('Details') ?>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?php echo xla('Refresh') ?>">
   &nbsp;
   <input type='submit' name='form_csvexport' value="<?php echo xla('Export to CSV') ?>">
   &nbsp;
   <input type='button' value='<?php echo xla('Print'); ?>' id='printbutton' />
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
    <?php echo xlt('Item') ?>
  </td>
    <?php if ($_POST['form_details']) { ?>
  <td class="dehead">
        <?php echo xlt('Date') ?>
  </td>
  <td class="dehead">
        <?php echo xlt('Invoice') ?>
  </td>
<?php } ?>
  <td class="dehead" align="right">
    <?php echo xlt('Qty') ?>
  </td>
  <td class="dehead" align="right">
    <?php echo xlt('CYP') ?>
  </td>
  <td class="dehead" align="right">
    <?php echo xlt('Result') ?>
  </td>
 </tr>
    <?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
    $from_date = $form_from_date;
    $to_date   = $form_to_date;

    $product = "";
    $productleft = "";
    $productcyp = 0;
    $producttotal = 0; // total of results for product
    $grandtotal = 0;   // grand total of results
    $productqty = 0;
    $grandqty = 0;

    $sqlBindArray = array();

    $query = "SELECT b.pid, b.encounter, b.code_type, b.code, b.units, " .
    "b.code_text, c.cyp_factor, fe.date, fe.facility_id, fe.invoice_refno " .
    "FROM billing AS b " .
    "JOIN codes AS c ON c.code_type = '12' AND c.code = b.code AND c.modifier = b.modifier AND c.cyp_factor > 0 " .
    "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
    "WHERE b.code_type = 'MA' AND b.activity = 1 AND " .
    "fe.date >= ? AND fe.date <= ?";
    array_push($sqlBindArray, $from_date . ' 00:00:00', $to_date . ' 23:59:59');

    // If a facility was specified.
    if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= " ORDER BY b.code, fe.date, fe.id";

    $res = sqlStatement($query, $sqlBindArray);
    while ($row = sqlFetchArray($res)) {
        thisLineItem(
            $row['pid'],
            $row['encounter'],
            $row['code'] . ' ' . $row['code_text'],
            substr($row['date'], 0, 10),
            $row['units'],
            $row['cyp_factor'],
            $row['invoice_refno']
        );
    }

    $sqlBindArray = array();

    $query = "SELECT s.sale_date, s.quantity, s.pid, s.encounter, " .
    "d.name, d.cyp_factor, fe.date, fe.facility_id, fe.invoice_refno " .
    "FROM drug_sales AS s " .
    "JOIN drugs AS d ON d.drug_id = s.drug_id AND d.cyp_factor > 0 " .
    "JOIN form_encounter AS fe ON " .
    "fe.pid = s.pid AND fe.encounter = s.encounter AND " .
    "fe.date >= ? AND fe.date <= ? " .
    "WHERE s.fee != 0";
    array_push($sqlBindArray, $from_date . ' 00:00:00', $to_date . ' 23:59:59');

    // If a facility was specified.
    if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= " ORDER BY d.name, fe.date, fe.id";

    $res = sqlStatement($query, $sqlBindArray);
    while ($row = sqlFetchArray($res)) {
        thisLineItem(
            $row['pid'],
            $row['encounter'],
            $row['name'],
            substr($row['date'], 0, 10),
            $row['quantity'],
            $row['cyp_factor'],
            $row['invoice_refno']
        );
    }

    if ($_POST['form_csvexport']) {
        if (! $_POST['form_details']) {
            echo csvEscape(display_desc($product)) . ',';
            echo csvEscape($productqty)            . ',';
            echo csvEscape(formatcyp($productcyp)) . ',';
            echo csvEscape(formatcyp($producttotal)) . "\n";
        }
    } else {
        ?>

   <tr bgcolor="#ddddff">
    <td class="detail" colspan="<?php echo $_POST['form_details'] ? 3 : 1; ?>">
        <?php
        if ($_POST['form_details']) {
            echo xlt('Total for ');
        }
        echo text(display_desc($product)); ?>
  </td>
  <td class="dehead" align="right">
        <?php echo text($productqty); ?>
  </td>
  <td class="dehead" align="right">
        <?php echo text(formatcyp($productcyp)); ?>
  </td>
  <td class="dehead" align="right">
        <?php echo text(formatcyp($producttotal)); ?>
  </td>
 </tr>

 <tr bgcolor="#ffdddd">
    <td class="detail" colspan="<?php echo $_POST['form_details'] ? 3 : 1; ?>">
        <?php echo xlt('Grand Total'); ?>
  </td>
  <td class="dehead" align="right">
        <?php echo text($grandqty); ?>
  </td>
  <td class="dehead" align="right">
   &nbsp;
  </td>
  <td class="dehead" align="right">
        <?php echo text(formatcyp($grandtotal)); ?>
  </td>
 </tr>

        <?php
    } // End not csv export
} // end report generation

if (! $_POST['form_csvexport']) {
    ?>

</table>
</form>
</center>
</body>
</html>
    <?php
} // End not csv export
?>
