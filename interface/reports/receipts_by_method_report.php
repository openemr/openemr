<?php

/**
 * This is a report of receipts by payer or payment method.
 *
 * The payer option means an insurance company name or "Personal pay".
 *
 * The payment method option is most useful for sites using
 * pos_checkout.php (e.g. weight loss clinics) because this plugs
 * a payment method like Cash, Check, VISA, etc. into the "source"
 * column of the SQL-Ledger acc_trans table or ar_session table.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\InsuranceService;

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Receipts Summary")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// This controls whether we show pt name, policy number and DOS.
$showing_ppd = true;

$insarray = array();

function bucks($amount)
{
    if ($amount) {
        return oeFormatMoney($amount);
    }
}

function thisLineItem(
    $patient_id,
    $encounter_id,
    $memo,
    $transdate,
    $rowmethod,
    $rowpayamount,
    $rowadjamount,
    $payer_type = 0,
    $irnumber = ''
) {

    global $form_report_by, $insarray, $grandpaytotal, $grandadjtotal;

    if ($form_report_by != '1') { // reporting by method or check number
        showLineItem(
            $patient_id,
            $encounter_id,
            $memo,
            $transdate,
            $rowmethod,
            $rowpayamount,
            $rowadjamount,
            $payer_type,
            $irnumber
        );
        return;
    }

  // Reporting by payer.
  //
    if (!empty($_POST['form_details'])) { // details are wanted
        // Save everything for later sorting.
        $insarray[] = array(
            $patient_id,
            $encounter_id,
            $memo,
            $transdate,
            $rowmethod,
            $rowpayamount,
            $rowadjamount,
            $payer_type,
            $irnumber
        );
    } else { // details not wanted
        if (empty($insarray[$rowmethod])) {
            $insarray[$rowmethod] = array(0, 0);
        }

        $insarray[$rowmethod][0] += $rowpayamount;
        $insarray[$rowmethod][1] += $rowadjamount;
        $grandpaytotal  += $rowpayamount;
        $grandadjtotal  += $rowadjamount;
    }
}

function showLineItem(
    $patient_id,
    $encounter_id,
    $memo,
    $transdate,
    $rowmethod,
    $rowpayamount,
    $rowadjamount,
    $payer_type = 0,
    $irnumber = ''
) {

    global $paymethod, $paymethodleft, $methodpaytotal, $methodadjtotal,
    $grandpaytotal, $grandadjtotal, $showing_ppd;

    if (!$rowmethod) {
        $rowmethod = 'Unknown';
    }

    if ($paymethod != $rowmethod) {
        if ($paymethod) {
            // Print method total.
            ?>

            <tr class="table-secondary">
                <td colspan="<?php echo $showing_ppd ? 8 : 4; ?>">
                    <?php echo xlt('Total for ') . text($paymethod); ?>
                </td>
                <td class="text-right">
                    <?php echo text(bucks($methodadjtotal)); ?>
                </td>
                <td class="text-right">
                    <?php echo text(bucks($methodpaytotal)); ?>
                </td>
            </tr>
            <?php
        }

        $methodpaytotal = 0;
        $methodadjtotal  = 0;
        $paymethod = $rowmethod;
        $paymethodleft = $paymethod;
    }

    if ($_POST['form_details']) {
        ?>

    <tr>
        <td>
            <?php echo text($paymethodleft); $paymethodleft = " " ?>
        </td>
        <td>
            <?php echo text($memo); $memo = " " ?>
        </td>
        <td>
            <?php echo text(oeFormatShortDate($transdate)); ?>
        </td>
        <td>
            <?php
                $pferow = sqlQuery("SELECT p.fname, p.mname, p.lname, fe.date, fe.id " .
                "FROM patient_data AS p, form_encounter AS fe WHERE " .
                "p.pid = ? AND fe.pid = p.pid AND " .
                "fe.encounter = ? LIMIT 1", array($patient_id, $encounter_id));
            if (!empty($irnumber)) {
                echo text($invnumber);
            } else {
                echo "<input type='button' class='btn btn-sm btn-secondary' value='" .
                      attr($patient_id) . "-" . attr($encounter_id) .
                      "' onclick='editInvoice(event, " . attr_js($pferow['id']) . ")' />";
            }
            ?>
        </td>
        <?php
        if ($showing_ppd) {
            $dos = substr($pferow['date'], 0, 10);

            echo "  <td class='font-weight-bold'>\n";
            echo "   " . text($pferow['lname']) . ", " . text($pferow['fname']) . " " . text($pferow['mname']);
            echo "  </td>\n";

            echo "  <td class='font-weight-bold'>\n";
            if ($payer_type) {
                $ptarr = array(1 => 'primary', 2 => 'secondary', 3 => 'tertiary');
                $insrow = getInsuranceDataByDate(
                    $patient_id,
                    $dos,
                    $ptarr[$payer_type],
                    "policy_number"
                );
                echo "   " . text($insrow['policy_number']);
            }

            echo "  </td>\n";

            echo "  <td class='font-weight-bold'>\n";
            echo "   " . text(oeFormatShortDate($dos)) . "\n";
            echo "  </td>\n";
        }
        ?>

  <td>
        <?php echo text($memo); ?>
  </td>
  <td align="right">
        <?php echo text(bucks($rowadjamount)); ?>
  </td>
  <td align="right">
        <?php echo text(bucks($rowpayamount)); ?>
  </td>
 </tr>
        <?php
    }

    $methodpaytotal += $rowpayamount;
    $grandpaytotal  += $rowpayamount;
    $methodadjtotal += $rowadjamount;
    $grandadjtotal  += $rowadjamount;
}

// This is called by usort() when reporting by payer with details.
// Sorts by payer/date/patient/encounter/memo.
function payerCmp($a, $b)
{
    foreach (array(4,3,0,1,2,7) as $i) {
        if ($a[$i] < $b[$i]) {
            return -1;
        }

        if ($a[$i] > $b[$i]) {
            return  1;
        }
    }

    return 0;
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_use_edate = $_POST['form_use_edate'] ?? null;
$form_facility  = $_POST['form_facility'] ?? null;
$form_report_by = $_POST['form_report_by'] ?? null;
$form_proc_codefull = trim($_POST['form_proc_codefull'] ?? '');
// Parse the code type and the code from <code_type>:<code>
$tmp_code_array = explode(':', $form_proc_codefull);
$form_proc_codetype = $tmp_code_array[0];
$form_proc_code = $tmp_code_array[1] ?? null;

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo xlt('Receipts Summary')?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

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
            #report_results {
               margin-top: 30px;
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

        // This is for callback by the find-code popup.
        // Erases the current entry
        function set_related(codetype, code, selector, codedesc) {
            var f = document.forms[0];
            var s = f.form_proc_codefull.value;
            if (code) {
                s = codetype + ':' + code;
            } else {
                s = '';
            }
            f.form_proc_codefull.value = s;
        }

        // This invokes the find-code popup.
        function sel_procedure() {
            dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo attr_url(collect_codetypes("procedure", "csv")) ?>', '_blank', 500, 400);
        }

        function editInvoice(e, id) {
            e.preventDefault();
            let url = '../billing/sl_eob_invoice.php?id=' + encodeURIComponent(id);
            <?php if (isset($_FILES['form_erafile']['size']) && !$_FILES['form_erafile']['size']) { ?>
                dlgopen(url,'','modal-full',700,false,'', {
                sizeHeight: 'full',
                onClosed: 'reSubmit'
            }); <?php } else { // keep era page up so can check on other remits ?>
                dlgopen(url,'','modal-full',700,false,'', {
                sizeHeight: 'full',
                onClosed: ''
            }); <?php } ?>
        }
    </script>
</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Receipts Summary'); ?></span>

<form method='post' action='receipts_by_method_report.php' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
    <div class="form-row col-md-6">
        <input type='hidden' name='form_refresh' id='form_refresh' value=''/>
        <div class="form-group auto">
            <label for='form_report_by'><?php echo xlt('Report by'); ?></label>
            <?php echo " <select name='form_report_by' id='form_report_by' class='form-control'>\n";
            foreach (
                    array(
                        1 => 'Payer',
                        2 => 'Payment Method',
                        3 => 'Check Number'
                    ) as $key => $value
            ) {
                echo "    <option value='" . attr($key) . "'";
                if ($key == $form_report_by) {
                    echo ' selected';
                }

                echo ">" . xlt($value) . "</option>\n";
            }

                echo "   </select>&nbsp;\n";
            ?>
        </div>
        <div class="form-group col-auto">
            <label for='form_facility'><?php echo xlt('Facility'); ?></label>
            <?php dropdown_facility($form_facility, 'form_facility', false); ?>
        </div>    
    </div>
    <div class="form-row col-md-6">
        <div class="form-group col-auto">
            <label for='form_provider'><?php echo xlt('Provider'); ?></label>
            <?php echo xlt('Provider'); ?>:
                <td>
                    <?php
                    if (AclMain::aclCheckCore('acct', 'rep_a')) {
                        // Build a drop-down list of providers.
                        //
                        $query = "select id, lname, fname from users where " .
                            "authorized = 1 order by lname, fname";
                        $res = sqlStatement($query);
                        echo "<select name='form_provider' class='form-control'>\n";
                        echo "    <option value=''>-- " . xlt('All Providers') . " --\n";
                        while ($row = sqlFetchArray($res)) {
                            $provid = $row['id'];
                            echo "    <option value='" . attr($provid) . "'";
                            if (!empty($_POST['form_provider']) && ($provid == $_POST['form_provider'])) {
                                echo " selected";
                            }

                            echo ">" . text($row['lname']) . ", " . text($row['fname']) . "\n";
                        }

                        echo "   </select>\n";
                    } else {
                        echo "<input type='hidden' name='form_provider' value='" . attr($_SESSION['authUserID']) . "'>";
                    }
                    ?>
                </td>
        </div>
        <div class="form-group col-auto">
            <label for="form_proc_codefull">
            <?php
            if (!$GLOBALS['simplified_demographics']) {
                echo xlt('Procedure/Service');
            }
            ?>
            </label>   
            <input type='text' name='form_proc_codefull' id='form_proc_codefull' class='form-control' size='12' value='<?php echo attr($form_proc_codefull); ?>' onclick='sel_procedure()'
                title='<?php echo xla('Click to select optional procedure code'); ?>'
            <?php
            if ($GLOBALS['simplified_demographics']) {
                echo "style='display:none'";
            } ?> />
        </div>
    </div>    
    <div class="form-row col-md-6">
        <div class="form-group col-auto">
            <label for='form_use_edate'>
                <select name='form_use_edate' class='form-control'>
                    <option value='0'><?php echo xlt('Payment Date'); ?></option>
                    <option value='1'<?php echo ($form_use_edate) ? ' selected' : ''; ?>><?php echo xlt('Invoice Date'); ?></option>
                </select>
            </label>
        </div>
        <div class="form-group col-auto">
            <div class="form-check">
                <input class="form-check-input" type='checkbox' name='form_details' value='1'<?php echo (!empty($_POST['form_details'])) ? " checked" : ""; ?> />
                <label class="form-check-label">
                    <?php echo xlt('Details')?>
                </label>
            </div>
        </div>
        <div class="form-group col-auto">
            <label for="form_from_date">
                <?php echo xlt('From'); ?>
            </label>
            <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
        </div>
        <div class="form-group col-auto">
            <label for="form_to_date">
                <?php echo xlt('To{{Range}}'); ?>
            </label>
            <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
        </div>
    </div>
    <div class="form-row col-md-2 mb-2">
        <div class="btn-group col-auto" role="group">
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
</div> <!-- end of parameters -->

<?php
if (!empty($_POST['form_refresh'])) {
    ?>
<div id="report_results">

<table id='mymaintable' class='table table-hover'>

<thead class="thead-light">
    <tr>
        <th scope="col">
            <?php echo xlt('Method') ?>
        </th>
        <th scope ="col"><?php echo xlt('Reference') ?>
        </th>
        <th scope="col">
            <?php echo xlt('Date') ?>
        </th>
        <th scope="col">
            <?php echo xlt('Invoice') ?>
        </th>
            <?php if ($showing_ppd) { ?>
        <th scope="col">
                <?php echo xlt('Patient')?>
        </th>
        <th scope="col">
                <?php echo xlt('Policy')?>
        </th>
        <th scope="col">
                <?php echo xlt('DOS')?>
        </th>
            <?php } ?>
        <th scope="col">
            <?php echo xlt('Procedure')?>
        </th>
        <th class="text-right" scope="col">
            <?php echo xlt('Adjustments')?>
        </th>
        <th class="text-right" scope="col"">
            <?php echo xlt('Payments')?>
        </th>
    </tr>
</thead>
<tbody>
    <?php

    if ($_POST['form_refresh']) {
        $paymethod   = "";
        $paymethodleft = "";
        $methodpaytotal = 0;
        $grandpaytotal  = 0;
        $methodadjtotal  = 0;
        $grandadjtotal  = 0;

        $form_provider = $_POST['form_provider'];
        if (!AclMain::aclCheckCore('acct', 'rep_a')) {
            // only allow user to see their encounter information
            $form_provider = $_SESSION['authUserID'];
        }


        // Get co-pays using the encounter date as the pay date.  These will
        // always be considered patient payments.  Ignored if selecting by
        // billing code.
        //
        if (!$form_proc_code || !$form_proc_codetype) {
            $sqlBindArray = array();
            $query = "SELECT b.fee, b.pid, b.encounter, b.code_type, " .
            "fe.date, fe.facility_id, fe.invoice_refno, fe.provider_id " .
            "FROM billing AS b " .
            "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
            "WHERE b.code_type = 'COPAY' AND b.activity = 1 AND b.fee != 0 AND " .
            "fe.date >= ? AND fe.date <= ?";
            array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
            // If a facility was specified.
            if ($form_facility) {
                $query .= " AND fe.facility_id = ?";
                array_push($sqlBindArray, $form_facility);
            }

            // If a provider was specified.
            if ($form_provider) {
                $query .= " AND fe.provider_id = ?";
                array_push($sqlBindArray, $form_provider);
            }

            $query .= " ORDER BY fe.date, b.pid, b.encounter, fe.id";

            $res = sqlStatement($query, $sqlBindArray);

            while ($row = sqlFetchArray($res)) {
                $rowmethod = $form_report_by == 1 ? 'Patient' : 'Co-Pay';
                thisLineItem(
                    $row['pid'],
                    $row['encounter'],
                    $row['code_text'],
                    substr($row['date'], 0, 10),
                    $rowmethod,
                    0 - $row['fee'],
                    0,
                    0,
                    $row['invoice_refno']
                );
            }
        } // end if not form_proc_code

        // Get all other payments and adjustments and their dates, corresponding
        // payers and check reference data, and the encounter dates separately.
        //
        $sqlBindArray = array();
        $query = "SELECT a.pid, a.encounter, a.post_time, a.pay_amount, " .
          "a.adj_amount, a.memo, a.session_id, a.code, a.payer_type, fe.id, fe.date, fe.provider_id, fe.id, " .
          "fe.invoice_refno, s.deposit_date, s.payer_id, s.reference, s.payment_method, i.name " .
          "FROM ar_activity AS a " .
          "JOIN form_encounter AS fe ON fe.pid = a.pid AND fe.encounter = a.encounter " .
          "JOIN forms AS f ON f.pid = a.pid AND f.encounter = a.encounter AND f.formdir = 'newpatient' " .
          "LEFT JOIN ar_session AS s ON s.session_id = a.session_id " .
          "LEFT JOIN insurance_companies AS i ON i.id = s.payer_id " .
          "LEFT OUTER JOIN billing AS b ON b.pid = a.pid AND b.encounter = a.encounter AND " .
          "b.code = a.code AND b.modifier = a.modifier AND b.activity = 1 AND " .
          "b.code_type != 'COPAY' AND b.code_type != 'TAX' " .
          "WHERE a.deleted IS NULL AND (a.pay_amount != 0 OR a.adj_amount != 0)";
        //
        if ($form_use_edate) {
            $query .= " AND fe.date >= ? AND fe.date <= ?";
            array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
        } else {
            $query .= " AND ( ( s.deposit_date IS NOT NULL AND " .
            "s.deposit_date >= ? AND s.deposit_date <= ? ) OR " .
            "( s.deposit_date IS NULL AND a.post_time >= ? AND " .
            "a.post_time <= ? ) )";
            array_push($sqlBindArray, $form_from_date, $form_to_date, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
        }

        // If a procedure code was specified.
        if ($form_proc_code && $form_proc_codetype) {
          // if a code_type is entered into the ar_activity table, then use it. If it is not entered in, then do not use it.
            $query .= " AND ( a.code_type = ? OR a.code_type = '' ) AND a.code LIKE ?";
            array_push($sqlBindArray, $form_proc_codetype, $form_proc_code . '%');
        }

        // If a facility was specified.
        if ($form_facility) {
            $query .= " AND fe.facility_id = ?";
            array_push($sqlBindArray, $form_facility);
        }

        // If a provider was specified.
        if ($form_provider) {
            $query .= " AND ( b.provider_id = ? OR " .
            "( ( b.provider_id IS NULL OR b.provider_id = 0 ) AND " .
            "fe.provider_id = ? ) )";
            array_push($sqlBindArray, $form_provider, $form_provider);
        }

        //
        if ($form_use_edate) {
            $query .= " ORDER BY s.reference, fe.date, a.pid, a.encounter, fe.id";
        } else {
            $query .= " ORDER BY s.payment_method, s.deposit_date, a.post_time, a.pid, a.encounter, fe.id";
        }

        //
        $res = sqlStatement($query, $sqlBindArray);
        while ($row = sqlFetchArray($res)) {
            if ($form_use_edate) {
                $thedate = substr($row['date'], 0, 10);
            } elseif (!empty($row['deposit_date'])) {
                $thedate = $row['deposit_date'];
            } else {
                $thedate = substr($row['post_time'], 0, 10);
            }

          // Compute reporting key: insurance company name or payment method.
            if ($form_report_by == '1') {
                if (empty($row['payer_id'])) {
                    // 'ar_session' is not capturing payer_id when entering payments through invoice or era posting
                    if ($row['payer_type'] == '1') {
                        $insurance_id = (new InsuranceService())->getOneByPid($row['pid'], "primary");
                    } elseif ($row['payer_type'] == '2') {
                        $insurance_id = (new InsuranceService())->getOneByPid($row['pid'], "secondary");
                    } elseif ($row['payer_type'] == '3') {
                        $insurance_id = (new InsuranceService())->getOneByPid($row['pid'], "tertiary");
                    } elseif ($row['payer_type'] == '0') {
                        $rowmethod = xl('Personal pay');
                        $rowreference = trim($row['reference']);
                    } else {
                        $rowmethod = xl('Unnamed insurance company');
                    }
                    if (!empty($insurance_id['provider'])) {
                        $insurance_company = (new InsuranceCompanyService())->getOneById($insurance_id['provider']) ?? '';
                        $rowmethod = xl($insurance_company['name']);
                    } elseif (!($row['payer_type'] == '0')) {
                        $rowmethod = xl('Unnamed insurance company');
                    }
                } else {
                    $rowmethod = $row['name'];
                }
            } else {
                if (empty($row['session_id'])) {
                    $rowmethod = trim($row['memo']);
                } else {
                    $rowmethod = trim(getListItemTitle('payment_method', $row['payment_method']));
                    $rowreference = trim($row['reference']);
                }
            }

            thisLineItem(
                $row['pid'],
                $row['encounter'],
                ($rowreference ?? ''),
                $thedate,
                $rowmethod,
                $row['pay_amount'],
                $row['adj_amount'],
                $row['payer_type'],
                $row['invoice_refno']
            );
        }

      // Not payer summary.
        if ($form_report_by != '1' || !empty($_POST['form_details'])) {
            if ($form_report_by == '1') { // by payer with details
                // Sort and dump saved info, and consolidate items with all key
                // fields being the same.
                usort($insarray, 'payerCmp');
                $b = array();
                foreach ($insarray as $a) {
                    if (empty($a[4])) {
                        $a[4] = xl('Patient');
                    }

                    if (empty($b)) {
                        $b = $a;
                    } else {
                        $match = true;
                        foreach (array(4,3,0,1,2,7) as $i) {
                            if ($a[$i] != $b[$i]) {
                                $match = false;
                            }
                        }

                        if ($match) {
                            $b[5] += $a[5];
                            $b[6] += $a[6];
                        } else {
                            showLineItem($b[0], $b[1], $b[2], $b[3], $b[4], $b[5], $b[6], $b[7], $b[8]);
                            $b = $a;
                        }
                    }
                }

                if (!empty($b)) {
                    showLineItem($b[0], $b[1], $b[2], $b[3], $b[4], $b[5], $b[6], $b[7], $b[8]);
                }
            } // end by payer with details

            // Print last method total.
            ?>
            <tr class="table-secondary" scope="row">
                <td colspan="<?php echo $showing_ppd ? 8 : 4; ?>">
                    <?php echo xlt('Total for ') . text($paymethod); ?>
                </td>
                <td class="text-right">
                    <?php echo text(bucks($methodadjtotal)); ?>
                </td>
                <td class="text-right">
                    <?php echo text(bucks($methodpaytotal)); ?>
                </td>
            </tr>
            <?php
        } else { // Payer summary: need to sort and then print it all.
            ksort($insarray);
            foreach ($insarray as $key => $value) {
                if (empty($key)) {
                    $key = xl('Patient');
                }
                ?>
                <tr>
                    <td colspan="<?php echo $showing_ppd ? 8 : 4; ?>">
                        <?php echo text($key); ?>
                    </td>
                    <td class="text-right">
                        <?php echo text(bucks($value[1])); ?>
                    </td>
                    <td class="text-right">
                        <?php echo text(bucks($value[0])); ?>
                    </td>
                </tr>
                <?php
            } // end foreach
        } // end payer summary
        ?>
        <tr class="table-info">
            <td colspan="<?php echo $showing_ppd ? 8 : 4; ?>">
                <?php echo xlt('Grand Total') ?>
            </td>
            <td class="text-right">
                <?php echo text(bucks($grandadjtotal)); ?>
            </td>
            <td class="text-right">
                <?php echo text(bucks($grandpaytotal)); ?>
            </td>
        </tr>

        <?php
    } // end form refresh
    ?>

</tbody>
</table>
</div>
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>

</form>
</body>

</html>
