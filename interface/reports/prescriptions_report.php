<?php

/**
 * This report lists prescriptions and their dispensations according
 * to various input selection criteria.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("../drugs/drugs.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Prescriptions and Dispensations")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date  = (!empty($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date    = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_patient_id = trim($_POST['form_patient_id'] ?? '');
$form_drug_name  = trim($_POST['form_drug_name'] ?? '');
$form_lot_number = trim($_POST['form_lot_number'] ?? '');
$form_facility   = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
?>
<html>
<head>

<title><?php echo xlt('Prescriptions and Dispensations'); ?></title>

<?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

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

    // The OnClick handler for receipt display.
    function show_receipt(payid) {
        // dlgopen('../patient_file/front_payment.php?receipt=1&payid=' + payid, '_blank', 550, 400);
        return false;
    }

</script>

<style>

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Prescriptions and Dispensations'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='prescriptions_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='640px'>
    <div style='float: left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
            <?php dropdown_facility($form_facility, 'form_facility', true); ?>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('From'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>' />
            </td>
            <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>' />
            </td>
        </tr>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Patient ID'); ?>:
            </td>
            <td>
               <input type='text' class='form-control' name='form_patient_id' size='10' maxlength='20' value='<?php echo attr($form_patient_id); ?>' title='<?php echo xla('Optional numeric patient ID'); ?>' />
            </td>
            <td class='col-form-label'>
                <?php echo xlt('Drug'); ?>:
            </td>
            <td>
               <input type='text' class='form-control' name='form_drug_name' size='10' maxlength='250' value='<?php echo attr($form_drug_name); ?>'
                title='<?php echo xla('Optional drug name, use % as a wildcard'); ?>' />
            </td>
            <td class='col-form-label'>
                <?php echo xlt('Lot'); ?>:
            </td>
            <td>
               <input type='text' class='form-control' name='form_lot_number' size='10' maxlength='20' value='<?php echo attr($form_lot_number); ?>'
                title='<?php echo xla('Optional lot number, use % as a wildcard'); ?>' />
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left:1px solid;'>
        <tr>
            <td>
       <div class="text-center">
                <div class="btn-group" role="group">
                    <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                    </a>
                    <?php if (!empty($_POST['form_refresh'])) { ?>
                    <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                            <?php echo xlt('Print'); ?>
                    </a>
                    <?php } ?>
                </div>
       </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

<?php
if (!empty($_POST['form_refresh'])) {
    ?>
<div id="report_results">
<table class='table' id='mymaintable'>
<thead class='thead-light'>
<th> <?php echo xlt('Patient'); ?> </th>
<th> <?php echo xlt('ID'); ?> </th>
<th> <?php echo xlt('RX'); ?> </th>
<th> <?php echo xlt('Drug Name'); ?> </th>
<th> <?php echo xlt('NDC'); ?> </th>
<th> <?php echo xlt('Units'); ?> </th>
<th> <?php echo xlt('Refills'); ?> </th>
<th> <?php echo xlt('Instructed'); ?> </th>
<th> <?php echo xlt('Reactions'); ?> </th>
<th> <?php echo xlt('Dispensed'); ?> </th>
<th> <?php echo xlt('Qty'); ?> </th>
<th> <?php echo xlt('Manufacturer'); ?> </th>
<th> <?php echo xlt('Lot'); ?> </th>
</thead>
<tbody>
    <?php
    if ($_POST['form_refresh']) {
        $sqlBindArray = array();

        $where = "r.date_modified >= ? AND " .
        "r.date_modified <= ?";
        array_push($sqlBindArray, $form_from_date, $form_to_date);

        if ($form_patient_id) {
            $where .= " AND p.pubpid = ?";
            array_push($sqlBindArray, $form_patient_id);
        }

        if ($form_drug_name) {
            $where .= " AND (d.name LIKE ? OR r.drug LIKE ?)";
            array_push($sqlBindArray, $form_drug_name, $form_drug_name);
        }

        if ($form_lot_number) {
            $where .= " AND i.lot_number LIKE ?";
            array_push($sqlBindArray, $form_lot_number);
        }

        $query = "SELECT r.id, r.patient_id, " .
        "r.date_modified, r.dosage, r.route, r.interval, r.refills, r.drug, " .
        "d.name, d.ndc_number, d.form, d.size, d.unit, d.reactions, " .
        "s.sale_id, s.sale_date, s.quantity, " .
        "i.manufacturer, i.lot_number, i.expiration, " .
        "p.pubpid, " .
        "p.fname, p.lname, p.mname, u.facility_id " .
        "FROM prescriptions AS r " .
        "LEFT OUTER JOIN drugs AS d ON d.drug_id = r.drug_id " .
        "LEFT OUTER JOIN drug_sales AS s ON s.prescription_id = r.id " .
        "LEFT OUTER JOIN drug_inventory AS i ON i.inventory_id = s.inventory_id " .
        "LEFT OUTER JOIN patient_data AS p ON p.pid = r.patient_id " .
        "LEFT OUTER JOIN users AS u ON u.id = r.provider_id " .
        "WHERE $where " .
        "ORDER BY p.lname, p.fname, p.pubpid, r.id, s.sale_id";

        $res = sqlStatement($query, $sqlBindArray);

        $last_patient_id      = 0;
        $last_prescription_id = 0;
        while ($row = sqlFetchArray($res)) {
            // If a facility is specified, ignore rows that do not match.
            if ($form_facility !== '') {
                if ($form_facility) {
                    if ($row['facility_id'] != $form_facility) {
                        continue;
                    }
                } else {
                    if (!empty($row['facility_id'])) {
                        continue;
                    }
                }
            }

            $patient_name    = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
            $patient_id      = $row['pubpid'];
            $prescription_id = $row['id'];
            $drug_name       = empty($row['name']) ? $row['drug'] : $row['name'];
            $ndc_number      = $row['ndc_number'];
            $drug_units      = text($row['size']) . ' ' .
                   generate_display_field(array('data_type' => '1','list_id' => 'drug_units'), $row['unit']);
            $refills         = $row['refills'];
            $reactions       = $row['reactions'];
            $instructed      = text($row['dosage']) . ' ' .
                   generate_display_field(array('data_type' => '1','list_id' => 'drug_form'), $row['form']) .
                   ' ' .
                       generate_display_field(array('data_type' => '1','list_id' => 'drug_interval'), $row['interval']);
            //if ($row['patient_id'] == $last_patient_id) {
            if (strcmp($row['pubpid'], $last_patient_id) == 0) {
                $patient_name = '&nbsp;';
                $patient_id   = '&nbsp;';
                if ($row['id'] == $last_prescription_id) {
                    $prescription_id = '&nbsp;';
                    $drug_name       = '&nbsp;';
                    $ndc_number      = '&nbsp;';
                    $drug_units      = '&nbsp;';
                    $refills         = '&nbsp;';
                    $reactions       = '&nbsp;';
                    $instructed      = '&nbsp;';
                }
            }
            ?>
   <tr>
    <td>
            <?php echo text($patient_name); ?>
  </td>
  <td>
            <?php echo text($patient_id); ?>
  </td>
  <td>
            <?php echo text($prescription_id); ?>
  </td>
  <td>
            <?php echo text($drug_name); ?>
  </td>
  <td>
            <?php echo text($ndc_number); ?>
  </td>
  <td>
            <?php echo $drug_units; ?>
  </td>
  <td>
            <?php echo text($refills); ?>
  </td>
  <td>
            <?php echo $instructed; ?>
  </td>
  <td>
            <?php echo text($reactions); ?>
  </td>
  <td>
     <a href='../drugs/dispense_drug.php?sale_id=<?php echo attr_url($row['sale_id']); ?>'
    style='color:#0000ff' target='_blank'>
            <?php echo text(oeFormatShortDate($row['sale_date'])); ?>
   </a>
  </td>
  <td>
            <?php echo text($row['quantity']); ?>
  </td>
  <td>
            <?php echo text($row['manufacturer']); ?>
  </td>
  <td>
            <?php echo text($row['lot_number']); ?>
  </td>
 </tr>
            <?php
            $last_prescription_id = $row['id'];
         //$last_patient_id = $row['patient_id'];
            $last_patient_id = $row['pubpid'];
        } // end while
    } // end if
    ?>
</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>
</form>
</body>

</html>
