<?php

/**
 * Collections report: various options to report on the current billing status of encounters
 *
 * (TLH) Added payor,provider,fixed cvs download to included selected fields
 * (TLH) Added ability to download selected invoices only or all for patient
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Terry Hill <terry@lillysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2006-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc.php");
require_once "$srcdir/options.inc.php";

use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Billing\SLEOB;
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

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Collections Report")]);
    exit;
}

$alertmsg = '';
$bgcolor = "#aaaaaa";
$export_patient_count = 0;
$export_dollars = 0;

$form_date      = (isset($_POST['form_date'])) ? DateToYYYYMMDD($_POST['form_date']) : "";
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : "";
$form_category  = $_POST['form_category'] ?? null;
$is_ins_summary = $form_category == 'Ins Summary';
$is_due_ins     = ($form_category == 'Due Ins') || $is_ins_summary;
$is_due_pt      = $form_category == 'Due Pt';
$is_all         = $form_category == 'All';
// $is_ageby_lad = is aged by last active date which is determined to be either the dos,
// the last payment date or the last statement date applied by statement.inc.php
$is_ageby_lad   = strpos(($_POST['form_ageby'] ?? ''), 'Last') !== false;
$form_facility  = $_POST['form_facility'] ?? null;
$form_provider  = $_POST['form_provider'] ?? null;
$form_payer_id  = $_POST['form_payer_id'] ?? null;
// reposition the page after closing invoice variables
$form_page_y    = $_POST['form_page_y'] ?? '';
$form_offset_y  = $_POST['form_offset_y'] ?? '';
$form_y         = $_POST['form_y'] ?? '';

if (!empty($_POST['form_refresh']) || !empty($_POST['form_export']) || !empty($_POST['form_csvexport'])) {
    if ($is_ins_summary) {
        $form_cb_ssn      = false;
        $form_cb_dob      = false;
        $form_cb_pubpid   = false;
        $form_cb_adate    = false;
        $form_cb_policy   = false;
        $form_cb_phone    = false;
        $form_cb_city     = false;
        $form_cb_ins1     = false;
        $form_cb_referrer = false;
        $form_cb_idays    = false;
        $form_cb_err      = false;
        $form_cb_group_number = false;
    } else {
        $form_cb_ssn      = (!empty($_POST['form_cb_ssn']))      ? true : false;
        $form_cb_dob      = (!empty($_POST['form_cb_dob']))      ? true : false;
        $form_cb_pubpid   = (!empty($_POST['form_cb_pubpid']))   ? true : false;
        $form_cb_adate    = (!empty($_POST['form_cb_adate']))    ? true : false;
        $form_cb_policy   = (!empty($_POST['form_cb_policy']))   ? true : false;
        $form_cb_phone    = (!empty($_POST['form_cb_phone']))    ? true : false;
        $form_cb_city     = (!empty($_POST['form_cb_city']))     ? true : false;
        $form_cb_ins1     = (!empty($_POST['form_cb_ins1']))     ? true : false;
        $form_cb_referrer = (!empty($_POST['form_cb_referrer'])) ? true : false;
        $form_cb_idays    = (!empty($_POST['form_cb_idays']))    ? true : false;
        $form_cb_err      = (!empty($_POST['form_cb_err']))      ? true : false;
        $form_cb_group_number      = (!empty($_POST['form_cb_group_number']))      ? true : false;
    }
} else {
    $form_cb_ssn      = false;
    $form_cb_dob      = true;
    $form_cb_pubpid   = false;
    $form_cb_adate    = false;
    $form_cb_policy   = true;
    $form_cb_phone    = true;
    $form_cb_city     = false;
    $form_cb_ins1     = true;
    $form_cb_referrer = false;
    $form_cb_idays    = true;
    $form_cb_err      = false;
    $form_cb_group_number = false;
}

$form_age_cols = (int) ($_POST['form_age_cols'] ?? null);
$form_age_inc  = (int) ($_POST['form_age_inc'] ?? null);
if ($form_age_cols > 0 && $form_age_cols < 50) {
    if ($form_age_inc <= 0) {
        $form_age_inc = 30;
    }
} else {
    $form_age_cols = 0;
    $form_age_inc  = 0;
}

$initial_colspan = 1;
if ($is_due_ins) {
    ++$initial_colspan;
}

if ($form_cb_ssn) {
    ++$initial_colspan;
}

if ($form_cb_dob) {
    ++$initial_colspan;
}

if ($form_cb_pubpid) {
    ++$initial_colspan;
}

if ($form_cb_policy) {
    ++$initial_colspan;
}

if ($form_cb_group_number) {
    ++$initial_colspan;
}

if ($form_cb_phone) {
    ++$initial_colspan;
}

if ($form_cb_city) {
    ++$initial_colspan;
}

if ($form_cb_ins1) {
    ++$initial_colspan;
}

if ($form_cb_referrer) {
    ++$initial_colspan;
}

if ($form_provider) {
    ++$initial_colspan;
}

if ($form_payer_id) {
    ++$initial_colspan;
}

$final_colspan = $form_cb_adate ? 6 : 5;
$form_cb_with_debt = (!empty($_POST['form_cb_with_debt'])) ? true : false;
$grand_total_charges     = 0;
$grand_total_adjustments = 0;
$grand_total_paid        = 0;
$grand_total_agedbal = array();
for ($c = 0; $c < $form_age_cols; ++$c) {
    $grand_total_agedbal[$c] = 0;
}

function endPatient($ptrow)
{
    global $export_patient_count, $export_dollars, $bgcolor;
    global $grand_total_charges, $grand_total_adjustments, $grand_total_paid;
    global $grand_total_agedbal, $is_due_ins, $form_age_cols;
    global $initial_colspan, $final_colspan, $form_cb_idays, $form_cb_err;

    if (!$ptrow['pid']) {
        return;
    }

    $pt_balance = $ptrow['amount'] - $ptrow['paid'];

    if ($_POST['form_export']) {
        // This is a fixed-length format used by Transworld Systems.  Your
        // needs will surely be different, so consider this just an example.
        //
        echo "1896H"; // client number goes here
        echo "000";   // filler
        echo sprintf("%-30s", substr($ptrow['ptname'], 0, 30));
        echo sprintf("%-30s", " ");
        echo sprintf("%-30s", substr($ptrow['address1'], 0, 30));
        echo sprintf("%-15s", substr($ptrow['city'], 0, 15));
        echo sprintf("%-2s", substr($ptrow['state'], 0, 2));
        echo sprintf("%-5s", $ptrow['zipcode'] ? substr($ptrow['zipcode'], 0, 5) : '00000');
        echo "1";                      // service code
        echo sprintf("%010.0f", $ptrow['pid']); // transmittal number = patient id
        echo " ";                      // filler
        echo sprintf("%-15s", substr($ptrow['ss'], 0, 15));
        echo substr($ptrow['dos'], 5, 2) . substr($ptrow['dos'], 8, 2) . substr($ptrow['dos'], 2, 2);
        echo sprintf("%08.0f", $pt_balance * 100);
        echo sprintf("%-9s\n", " ");

        if (empty($_POST['form_without'])) {
            sqlStatement("UPDATE form_encounter SET in_collection = 1 WHERE encounter = ?", array($ptrow['encounter']));
        }

        $export_patient_count += 1;
        $export_dollars += $pt_balance;
    } elseif ($_POST['form_csvexport']) {
        $export_patient_count += 1;
        $export_dollars += $pt_balance;
    } else {
        if ($ptrow['count'] > 1 && !$is_due_ins) {
            echo " <tr bgcolor='" . attr($bgcolor) . "'>\n";
            /***************************************************************
          echo "  <td class='detail' colspan='$initial_colspan'>";
          echo "&nbsp;</td>\n";
          echo "  <td class='detotal' colspan='$final_colspan'>&nbsp;Total Patient Balance:</td>\n";
            ***************************************************************/
            echo "  <td class='detotal' colspan='" . attr(($initial_colspan + $final_colspan)) .
            "'>&nbsp;" . xlt('Total Patient Balance') . ":</td>\n";
            /**************************************************************/
            if ($form_age_cols) {
                for ($c = 0; $c < $form_age_cols; ++$c) {
                    echo "  <td class='detotal' align='left'>&nbsp;" .
                    text(oeFormatMoney($ptrow['agedbal'][$c] ?? '')) . "&nbsp;</td>\n";
                }
            } else {
                echo "  <td class='detotal' align='left'>&nbsp;" .
                text(oeFormatMoney($pt_balance)) . "&nbsp;</td>\n";
            }

            if ($form_cb_idays) {
                echo "  <td class='detail'>&nbsp;</td>\n";
            }

            echo "  <td class='detail' colspan='2'>&nbsp;</td>\n";
            if ($form_cb_err) {
                echo "  <td class='detail'>&nbsp;</td>\n";
            }

            echo " </tr>\n";
        }
    }

    $grand_total_charges     += $ptrow['charges'];
    $grand_total_adjustments += $ptrow['adjustments'];
    $grand_total_paid        += $ptrow['paid'];
    for ($c = 0; $c < $form_age_cols; ++$c) {
        $grand_total_agedbal[$c] += ($ptrow['agedbal'][$c] ?? null);
    }
}

function endInsurance($insrow)
{
    global $export_patient_count, $export_dollars, $bgcolor;
    global $grand_total_charges, $grand_total_adjustments, $grand_total_paid;
    global $grand_total_agedbal, $is_due_ins, $form_age_cols;
    global $initial_colspan, $form_cb_idays, $form_cb_err;
    if (!$insrow['pid']) {
        return;
    }

    $ins_balance = $insrow['amount'] - $insrow['paid'];
    if ($_POST['form_export'] || $_POST['form_csvexport']) {
        // No exporting of insurance summaries.
        $export_patient_count += 1;
        $export_dollars += $ins_balance;
    } else {
        echo " <tr bgcolor='" . attr($bgcolor) . "'>\n";
        echo "  <td class='detail'>" . text($insrow['insname']) . "</td>\n";
        echo "  <td class='detotal' align='left'>&nbsp;" .
        text(oeFormatMoney($insrow['charges'])) . "&nbsp;</td>\n";
        echo "  <td class='detotal' align='left'>&nbsp;" .
        text(oeFormatMoney($insrow['adjustments'])) . "&nbsp;</td>\n";
        echo "  <td class='detotal' align='left'>&nbsp;" .
        text(oeFormatMoney($insrow['paid'])) . "&nbsp;</td>\n";
        if ($form_age_cols) {
            for ($c = 0; $c < $form_age_cols; ++$c) {
                echo "  <td class='detotal' align='left'>&nbsp;" .
                text(oeFormatMoney($insrow['agedbal'][$c])) . "&nbsp;</td>\n";
            }
        } else {
            echo "  <td class='detotal' align='left'>&nbsp;" .
            text(oeFormatMoney($ins_balance)) . "&nbsp;</td>\n";
        }

        echo " </tr>\n";
    }

    $grand_total_charges     += $insrow['charges'];
    $grand_total_adjustments += $insrow['adjustments'];
    $grand_total_paid        += $insrow['paid'];
    for ($c = 0; $c < $form_age_cols; ++$c) {
        $grand_total_agedbal[$c] += $insrow['agedbal'][$c];
    }
}

function getInsName($payerid)
{
    $tmp = sqlQuery("SELECT name FROM insurance_companies WHERE id = ? ", array($payerid));
    return $tmp['name'];
}

$ins_co_name = '';
function insuranceSelect()
{
    global $ins_co_name;
    $insurancei = getInsuranceProviders();
    if (!empty($_POST['form_csvexport'])) {
        foreach ($insurancei as $iid => $iname) {
            if ($iid == ($_POST['form_payer_id'] ?? null)) {
                $ins_co_name = $iname;
            }
        }
    } else {
         // added dropdown for payors (TLH)
         echo "   <select name='form_payer_id' class='form-control'>\n";
         echo "    <option value='0'>-- " . xlt('All') . " --</option>\n";
        foreach ($insurancei as $iid => $iname) {
            echo "<option value='" . attr($iid) . "'";
            if ($iid == ($_POST['form_payer_id'] ?? null)) {
                echo " selected";
            }
            echo ">" . text($iname) . "</option>\n";
            if ($iid == ($_POST['form_payer_id'] ?? null)) {
                $ins_co_name = $iname;
            }
        }
        echo "   </select>\n";
    }
}

// In the case of CSV export only, a download will be forced.
if (!empty($_POST['form_csvexport'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=collections_report.csv");
    header("Content-Description: File Transfer");
    insuranceSelect();
} else {
    ?>
<html>
<head>

    <title><?php echo xlt('Collections Report')?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

    <style>
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
        function reSubmit() {
            $("#form_refresh").attr("value","true");
            $("#form_export").val("");
            $("#form_csvexport").val("");
            $("#form_clear_ins_debt").val("");
            $("#theform").submit();
        }
        // open dialog to edit an invoice w/o opening encounter.
        function editInvoice(e, id) {
            e.stopPropagation();
            e.preventDefault();
            $("#form_page_y").val(e.pageY);
            $("#form_offset_y").val(e.offsetY);
            $("#form_y").val(e.y);
            let url = './../billing/sl_eob_invoice.php?id=' + encodeURIComponent(id);
            dlgopen(url,'','modal-lg',750,false,'', {
                onClosed: 'reSubmit'
            });
        }

        function toEncounter(newpid, enc) {
            top.restoreSession();
            top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + encodeURIComponent(newpid) + "&set_encounterid=" + encodeURIComponent(enc);
        }

        $(function () {
            let Y = parseFloat($("#form_page_y").val()) - parseFloat($("#form_offset_y").val()) - parseFloat($("#form_y").val());
            $("html, body").animate({scrollTop: Y}, 800);
        });

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

        function checkAll(checked) {
            var f = document.forms[0];
            for (var i = 0; i < f.elements.length; ++i) {
                var ename = f.elements[i].name;
                if (ename.indexOf('form_cb[') == 0)
                    f.elements[i].checked = checked;
            }
        }
    </script>

</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Collections'); ?></span>

<form method='post' action='collections_report.php' enctype='multipart/form-data' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_export' id='form_export' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<input type='hidden' name='form_page_y' id='form_page_y' value='<?php echo attr($form_page_y); ?>'/>
<input type='hidden' name='form_offset_y' id='form_offset_y' value='<?php echo attr($form_offset_y); ?>'/>
<input type='hidden' name='form_y' id='form_y' value='<?php echo attr($form_y); ?>'/>
<input type='hidden' name='form_clear_ins_debt' id='form_clear_ins_debt' value=''/>

<table>
 <tr>
  <td width=75%>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <table>
                    <tr>
                        <td><?php echo xlt('Displayed Columns') ?>:</td>
                    </tr>
                    <tr>
                        <td>
                           <label><input type='checkbox' name='form_cb_ssn'<?php echo ($form_cb_ssn) ? ' checked' : ''; ?>>
                            <?php echo xlt('SSN') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_dob'<?php echo ($form_cb_dob) ? ' checked' : ''; ?>>
                            <?php echo xlt('DOB') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_pubpid'<?php echo ($form_cb_pubpid) ? ' checked' : ''; ?>>
                            <?php echo xlt('ID') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_policy'<?php echo ($form_cb_policy) ? ' checked' : ''; ?>>
                            <?php echo xlt('Policy') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_phone'<?php echo ($form_cb_phone) ? ' checked' : ''; ?>>
                            <?php echo xlt('Phone') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_city'<?php echo ($form_cb_city) ? ' checked' : ''; ?>>
                            <?php echo xlt('City') ?>&nbsp;</label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                           <label><input type='checkbox' name='form_cb_ins1'<?php echo ($form_cb_ins1) ? ' checked' : ''; ?>>
                            <?php echo xlt('Primary Ins') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_referrer'<?php echo ($form_cb_referrer) ? ' checked' : ''; ?>>
                            <?php echo xlt('Referrer') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_adate'<?php echo ($form_cb_adate) ? ' checked' : ''; ?>>
                            <?php echo xlt('Act Date') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_idays'<?php echo ($form_cb_idays) ? ' checked' : ''; ?>>
                            <?php echo xlt('Inactive Days') ?>&nbsp;</label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_err'<?php echo ($form_cb_err) ? ' checked' : ''; ?>>
                            <?php echo xlt('Errors') ?></label>
                        </td>
                        <td>
                           <label><input type='checkbox' name='form_cb_group_number'<?php echo ($form_cb_group_number) ? ' checked' : ''; ?>>
                            <?php echo xlt('Group Number') ?></label>
                        </td>

                    </tr>
                </table>
            </td>
        </tr>
        </tr>
            <td>
                <table>

                    <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Service Date'); ?>:
                        </td>
                        <td>
                           <input type='text' class='datepicker form-control' name='form_date' id="form_date" size='10' value='<?php echo attr(oeFormatShortDate($form_date)); ?>'>
                        </td>
                        <td class='col-form-label'>
                            <?php echo xlt('To{{Range}}'); ?>:
                        </td>
                        <td>
                           <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
                        </td>
                        <td>
                           <select name='form_category' class='form-control'>
                        <?php
                        foreach (array('Open' => xl('Open'),'Due Pt' => xl('Due Pt'),'Due Ins' => xl('Due Ins'),'Ins Summary' => xl('Ins Summary'),'Credits' => xl('Credits'),'All' => xl('All')) as $key => $value) {
                            echo "    <option value='" . attr($key) . "'";
                            if ($form_category == $key) {
                                echo " selected";
                            }

                            echo ">" . text($value) . "</option>\n";
                        }
                        ?>
                           </select>
                        </td>

                    </tr>


                    <tr>
                        <td class='col-form-label'>
                        <?php echo xlt('Facility'); ?>:
                        </td>
                        <td>
                        <?php dropdown_facility($form_facility, 'form_facility', false); ?>
                        </td>

                        <td class='col-form-label'>
                        <?php echo xlt('Payor'); ?>:
                        </td>
                        <td>
                        <?php  //added dropdown for payors (TLH)
                        insuranceSelect();
                        ?>
                        </td>
                    </tr>

                    <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Age By') ?>:
                        </td>
                        <td>
                           <select name='form_ageby' class='form-control'>
                        <?php
                        foreach (array( 'Service Date' => xl('Service Date'), 'Last Activity Date' => xl('Last Activity Date')) as $key => $value) {
                            echo "    <option value='" . attr($key) . "'";
                            if (!empty($_POST['form_ageby']) && ($_POST['form_ageby'] == $value)) {
                                echo " selected";
                            }

                            echo ">" . text($value) . "</option>\n";
                        }
                        ?>
                           </select>
                        </td>

                        <td class='col-form-label'>
                            <?php echo xlt('Provider') ?>:
                        </td>
                        <td>
                        <?php  # Build a drop-down list of providers.
                               # Added (TLH)

                               $query = "SELECT id, lname, fname FROM users WHERE " .
                               "authorized = 1  ORDER BY lname, fname"; #(CHEMED) facility filter

                               $ures = sqlStatement($query);

                               echo "   <select name='form_provider' class='form-control'>\n";
                               echo "    <option value=''>-- " . xlt('All') . " --\n";

                        while ($urow = sqlFetchArray($ures)) {
                            $provid = $urow['id'];
                            echo "    <option value='" . attr($provid) . "'";
                            if (!empty($_POST['form_provider']) && ($provid == $_POST['form_provider'])) {
                                echo " selected";
                            }

                            echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                            if (!empty($_POST['form_provider']) && ($provid == $_POST['form_provider'])) {
                                $provider_name = $urow['lname'] . ", " . $urow['fname'];
                            }
                        }

                               echo "   </select>\n";
                        ?>
                        </td>
                    </tr>
                    </tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Aging Columns') ?>:
                        </td>
                        <td>
                           <input type='text' name='form_age_cols' class='form-control' size='2' value='<?php echo ($form_age_cols) ? attr($form_age_cols) : "3" ; ?>' />
                        </td>
                        <td class='col-form-label'>
                            <?php echo xlt('Days/Col') ?>:
                        </td>
                        <td>
                           <input type='text' name='form_age_inc' class='form-control' size='3' value='<?php echo ($form_age_inc) ? attr($form_age_inc) : "30"; ?>' />
                        </td>
                        <td>
              <div class="checkbox">
                           <label><input type='checkbox' name='form_cb_with_debt'<?php echo ($form_cb_with_debt) ? ' checked' : ''; ?>>
                            <?php echo xlt('Patients with debt') ?></label>
              </div>
                        </td>
                    </tr>


                </table>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                      <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").val(""); $("#form_export").val(""); $("#form_clear_ins_debt").val(""); $("#theform").submit();'>
                            <?php echo xlt('Submit'); ?>
                      </a>
                        <?php if (!empty($_POST['form_refresh'])) { ?>
                        <a href='#' class='btn btn-secondary btn-print' onclick='window.print()'>
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
</div>


    <?php
} // end not form_csvexport

if (!empty($_POST['form_refresh']) || !empty($_POST['form_export']) || !empty($_POST['form_csvexport'])) {
    $rows = array();
    $where = "";
    $sqlArray = array();
    if ($_POST['form_export'] || $_POST['form_csvexport']) {
        $where = "( 1 = 2";
        foreach ($_POST['form_cb'] as $key => $value) {
             list($key_newval['pid'], $key_newval['encounter']) = explode(".", $key);
             $newkey = $key_newval['pid'];
             $newencounter =  $key_newval['encounter'];
             # added this condition to handle the downloading of individual invoices (TLH)
            if ($_POST['form_individual'] ?? '' == 1) {
                $where .= " OR f.encounter = ? ";
                array_push($sqlArray, $newencounter);
            } else {
                $where .= " OR f.pid = ? ";
                array_push($sqlArray, $newkey);
            }
        }

        $where .= ' )';
    }

    if ($form_date) {
        if ($where) {
            $where .= " AND ";
        }

        if ($form_to_date) {
            $where .= "f.date >= ? AND f.date <= ? ";
            array_push($sqlArray, $form_date . ' 00:00:00', $form_to_date . ' 23:59:59');
        } else {
            $where .= "f.date >= ? AND f.date <= ? ";
            array_push($sqlArray, $form_date . ' 00:00:00', $form_date . ' 23:59:59');
        }
    }

    if ($form_facility) {
        if ($where) {
            $where .= " AND ";
        }

        $where .= "f.facility_id = ? ";
        array_push($sqlArray, $form_facility);
    }

    # added for filtering by provider (TLH)
    if ($form_provider) {
        if ($where) {
            $where .= " AND ";
        }

        $where .= "f.provider_id = ? ";
        array_push($sqlArray, $form_provider);
    }

    if (! $where) {
        $where = "1 = 1";
    }

    # added provider from encounter to the query (TLH)
    $query = "SELECT f.id, f.date, f.pid, CONCAT(w.lname, ', ', w.fname) AS provider_id, f.encounter, f.last_level_billed, " .
      "f.last_level_closed, f.last_stmt_date, f.stmt_count, f.invoice_refno, f.in_collection, " .
      "p.fname, p.mname, p.lname, p.street, p.city, p.state, " .
      "p.postal_code, p.phone_home, p.ss, p.billing_note, " .
      "p.pubpid, p.DOB, CONCAT(u.lname, ', ', u.fname) AS referrer, " .
      "( SELECT bill_date FROM billing AS b WHERE " .
      "b.pid = f.pid AND b.encounter = f.encounter AND " .
      "b.activity = 1 AND b.code_type != 'COPAY' LIMIT 1) AS bill_date, " .
      "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
      "b.pid = f.pid AND b.encounter = f.encounter AND " .
      "b.activity = 1 AND b.code_type != 'COPAY' ) AS charges, " .
      "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
      "b.pid = f.pid AND b.encounter = f.encounter AND " .
      "b.activity = 1 AND b.code_type = 'COPAY' ) AS copays, " .
      "( SELECT SUM(s.fee) FROM drug_sales AS s WHERE " .
      "s.pid = f.pid AND s.encounter = f.encounter ) AS sales, " .
      "( SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE " .
      "a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL) AS payments, " .
      "( SELECT SUM(a.adj_amount) FROM ar_activity AS a WHERE " .
      "a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL) AS adjustments " .
      "FROM form_encounter AS f " .
      "JOIN patient_data AS p ON p.pid = f.pid " .
      "LEFT OUTER JOIN users AS u ON u.id = p.ref_providerID " .
      "LEFT OUTER JOIN users AS w ON w.id = f.provider_id " .
      "WHERE $where " .
      "ORDER BY f.pid, f.encounter";

    $eres = sqlStatement($query, $sqlArray);

    while ($erow = sqlFetchArray($eres)) {
        $patient_id = $erow['pid'];
        $encounter_id = $erow['encounter'];
        $pt_balance = $erow['charges'] + $erow['sales'] + $erow['copays'] - $erow['payments'] - $erow['adjustments'];
        $pt_balance = 0 + sprintf("%.2f", $pt_balance); // yes this seems to be necessary
        $svcdate = substr($erow['date'], 0, 10);

        if ($form_cb_with_debt && $pt_balance <= 0) {
            unset($erow);
            continue;
        }

        if ($_POST['form_refresh'] && !$is_all) {
            if ($pt_balance == 0) {
                continue;
            }
        }

        if ($form_category == 'Credits') {
            if ($pt_balance > 0) {
                continue;
            }
        }

        // If we have not yet billed the patient, then compute $duncount as a
        // negative count of the number of insurance plans for which we have not
        // yet closed out insurance.  Here we also compute $insname as the name of
        // the insurance plan from which we are awaiting payment, and its sequence
        // number $insposition (1-3).
        $last_level_closed = $erow['last_level_closed'];
        $duncount = $erow['stmt_count'];
        $payerids = array();
        $insposition = 0;
        $insname = '';
        if (!$duncount) {
            for ($i = 1; $i <= 3; ++$i) {
                $tmp = SLEOB::arGetPayerID($patient_id, $svcdate, $i);
                if (empty($tmp)) {
                    break;
                }
                $payerids[] = $tmp;
            }

            $duncount = $last_level_closed - count($payerids);
            if ($duncount < 0) {
                if (!empty($payerids[$last_level_closed])) {
                    $ins_id = $payerids[$last_level_closed];
                    $insname = getInsName($ins_id);
                    $insposition = $last_level_closed + 1;
                }
            }
        }

        // Skip invoices not in the desired "Due..." category.
        if ($is_due_ins && $duncount >= 0) {
            continue;
        }

        if ($is_due_pt && $duncount <  0) {
            continue;
        }

        // An invoice is due from the patient if money is owed and we are
        // not waiting for insurance to pay.
        $isduept = ($duncount >= 0) ? " checked" : "";

        $row = array();
        $row['id']        = $erow['id'];
        $row['pid']       = $patient_id;
        $row['encounter'] = $encounter_id;
        $row['invnumber'] = "$patient_id.$encounter_id";
        $row['custid']    = $patient_id;
        $row['name']      = $erow['fname'] . ' ' . $erow['lname'];
        $row['address1']  = $erow['street'];
        $row['city']      = $erow['city'];
        $row['state']     = $erow['state'];
        $row['zipcode']   = $erow['postal_code'];
        $row['phone']     = $erow['phone_home'];
        $row['duncount']  = $duncount;
        $row['dos']       = $svcdate;
        $row['ss']        = $erow['ss'];
        $row['DOB']       = $erow['DOB'];
        $row['pubpid']    = $erow['pubpid'];
        $row['billnote']  = $erow['billing_note'];
        $row['referrer']  = $erow['referrer'];
        $row['provider']  = $erow['provider_id'];
        $row['irnumber']  = $erow['invoice_refno'];
        $row['bill_date'] = $erow['bill_date'];  // use this for ins_due claim age date
        $row['in_collection'] = $erow['in_collection'];

        // Also get the primary insurance company name whenever there is one.
        $row['ins1'] = '';
        if ($insposition == 1) {
            $row['ins1'] = $insname;
        } else {
            if (empty($payerids)) {
                $tmp = SLEOB::arGetPayerID($patient_id, $svcdate, 1);
                if (!empty($tmp)) {
                    $payerids[] = $tmp;
                }
            }

            if (!empty($payerids)) {
                $row['ins1'] = getInsName($payerids[0]);
            }
        }

        // This computes the invoice's total original charges and adjustments,
        // date of last activity, and determines if insurance has responded to
        // all billing items.
        $invlines = InvoiceSummary::arGetInvoiceSummary($patient_id, $encounter_id, true);

        $row['charges'] = 0;
        $row['adjustments'] = 0;
        $row['paid'] = 0;
        $ins_seems_done = true;
        $aging_date = $svcdate;
        foreach ($invlines as $key => $value) {
            $row['charges'] += $value['chg'] + ($value['adj'] ?? null);
            $row['adjustments'] += 0 - ($value['adj'] ?? null);
            $row['paid'] += $value['chg'] - $value['bal'];
            foreach ($value['dtl'] as $dkey => $dvalue) {
                $dtldate = trim(substr($dkey, 0, 10));
                if ($dtldate && $dtldate > $aging_date) {
                    $aging_date = $dtldate;
                }
            }

            $lckey = strtolower($key);
            if ($lckey == 'co-pay' || $lckey == 'claim') {
                continue;
            }

            if (count($value['dtl']) <= 1) {
                $ins_seems_done = false;
            }
        }

        // Amount is charges with adjustments:
        $row['amount'] = $row['charges'] + $row['adjustments'];

        $row['billing_errmsg'] = '';
        if ($is_due_ins && $last_level_closed < 1 && $ins_seems_done) {
            $row['billing_errmsg'] = 'Ins1 seems done';
        } elseif ($last_level_closed >= 1 && !$ins_seems_done) {
            $row['billing_errmsg'] = 'Ins1 seems not done';
        }

        // Check billing for more recent age date even if due pt
        $aging_date = ($row['bill_date'] > $aging_date) ? $row['bill_date'] : $aging_date;

        $row['aging_date'] = $aging_date;

        if ($aging_date == '') {
            $row['inactive_days'] = "n/a";
        } else {
            // Compute number of days since last activity.
            $latime = mktime(
                0,
                0,
                0,
                substr($aging_date, 5, 2),
                substr($aging_date, 8, 2),
                substr($aging_date, 0, 4)
            );
            $row['inactive_days'] = floor((time() - $latime) / (60 * 60 * 24));
        }

        // Look up insurance policy number if we need it.
        if ($form_cb_policy) {
            $instype = ($insposition == 2) ? 'secondary' : (($insposition == 3) ? 'tertiary' : 'primary');
            $insrow = sqlQuery("SELECT policy_number FROM insurance_data WHERE " .
            "pid = ? AND type = ? AND (date <= ? OR date IS NULL) " .
            "ORDER BY date DESC LIMIT 1", array($patient_id, $instype, $svcdate));
            $row['policy'] = $insrow['policy_number'] ?? '';
        }
        if ($form_cb_group_number) {
            $instype = ($insposition == 2) ? 'secondary' : (($insposition == 3) ? 'tertiary' : 'primary');
            $insrow = sqlQuery("SELECT group_number FROM insurance_data WHERE " .
            "pid = ? AND type = ? AND (date <= ? OR date IS NULL) " .
            "ORDER BY date DESC LIMIT 1", array($patient_id, $instype, $svcdate));
            $row['groupnumber'] = $insrow['group_number'];
        }


        $ptname = $erow['lname'] . ", " . $erow['fname'];
        if ($erow['mname']) {
            $ptname .= " " . substr($erow['mname'], 0, 1);
        }

        if (!$is_due_ins) {
            $insname = '';
        }

        $rows[$insname . '|' . $patient_id . '|' . $ptname . '|' . $encounter_id] = $row;
    // end while
    }

    ksort($rows);

    if ($_POST['form_export']) {
        echo "<textarea rows='35' cols='100' readonly>";
    } elseif ($_POST['form_csvexport']) {
        # CSV headers added conditions if they are checked to display then export them (TLH)
        if (true) {
            echo csvEscape(xl('Insurance')) . ',';
            echo csvEscape(xl('Name')) . ',';
            if ($form_cb_ssn) {
                echo csvEscape(xl('SSN')) . ',';
            }

            if ($form_cb_dob) {
                echo csvEscape(xl('DOB')) . ',';
            }

            if ($form_cb_pubpid) {
                echo csvEscape(xl('Pubpid')) . ',';
            }

            if ($form_cb_policy) {
                echo csvEscape(xl('Policy')) . ',';
            }
            if ($form_cb_group_number) {
                echo csvEscape(xl('Group Number')) . ',';
            }
            if ($form_cb_phone) {
                echo csvEscape(xl('Phone')) . ',';
            }

            if ($form_cb_city) {
                echo csvEscape(xl('City')) . ',';
            }

            echo csvEscape(xl('Invoice')) . ',';
            echo csvEscape(xl('DOS')) . ',';
            echo csvEscape(xl('Referrer')) . ',';
            echo csvEscape(xl('Provider')) . ',';
            echo csvEscape(xl('Charge')) . ',';
            echo csvEscape(xl('Adjust')) . ',';
            echo csvEscape(xl('Paid')) . ',';
            echo csvEscape(xl('Balance')) . ',';

            if ($form_cb_idays) {
                echo csvEscape(xl('Aging Days')) . ',';
            }

            if ($form_cb_err) {
                echo csvEscape(xl('Error')) . "\n";
            } else {
                echo "\n";
            }
        }
    } else {
        ?>

  <div id="report_results">
  <table class='table' id='mymaintable'>

   <thead class='thead-light'>
        <?php if ($is_due_ins) { ?>
    <th>&nbsp;<?php echo xlt('Insurance')?></th>
    <?php } ?>
        <?php if (!$is_ins_summary) { ?>
    <th>&nbsp;<?php echo xlt('Name')?></th>
    <?php } ?>
        <?php if ($form_cb_ssn) { ?>
    <th>&nbsp;<?php echo xlt('SSN')?></th>
    <?php } ?>
        <?php if ($form_cb_dob) { ?>
    <th>&nbsp;<?php echo xlt('DOB')?></th>
    <?php } ?>
        <?php if ($form_cb_pubpid) { ?>
    <th>&nbsp;<?php echo xlt('ID')?></th>
    <?php } ?>
        <?php if ($form_cb_policy) { ?>
    <th>&nbsp;<?php echo xlt('Policy')?></th>
    <?php } ?>
        <?php if ($form_cb_group_number) { ?>
    <th>&nbsp;<?php echo xlt('Group Number')?></th>
    <?php } ?>
        <?php if ($form_cb_phone) { ?>
    <th>&nbsp;<?php echo xlt('Phone')?></th>
    <?php } ?>
        <?php if ($form_cb_city) { ?>
    <th>&nbsp;<?php echo xlt('City')?></th>
    <?php } ?>
        <?php if ($form_cb_ins1 || $form_payer_id) { ?>
    <th>&nbsp;<?php echo xlt('Primary Ins')?></th>
    <?php } ?>
        <?php if ($form_provider) { ?>
    <th>&nbsp;<?php echo xlt('Provider')?></th>
    <?php } ?>
        <?php if ($form_cb_referrer) { ?>
    <th>&nbsp;<?php echo xlt('Referrer')?></th>
    <?php } ?>
        <?php if (!$is_ins_summary) { ?>
    <th>&nbsp;<?php echo xlt('Invoice') ?></th>
    <th>&nbsp;<?php echo xlt('Svc Date') ?></th>
            <?php if ($form_cb_adate) { ?>
    <th>&nbsp;<?php echo xlt('Act Date')?></th>
    <?php } ?>
    <?php } ?>
        <th align="right"><?php echo xlt('Charge') ?>&nbsp;</th>
        <th align="right"><?php echo xlt('Adjust') ?>&nbsp;</th>
        <th align="right"><?php echo xlt('Paid') ?>&nbsp;</th>
        <?php
    // Generate aging headers if appropriate, else balance header.
        if ($form_age_cols) {
            for ($c = 0; $c < $form_age_cols;) {
                echo "  <th class='dehead' align='left'>";
                echo $form_age_inc * $c;
                if (++$c < $form_age_cols) {
                    echo "-" . text(($form_age_inc * $c - 1));
                } else {
                    echo "+";
                }
                echo "</th>\n";
            }
        } else {
            ?>
<th align="right"><?php echo xlt('Balance') ?>&nbsp;</th>
            <?php
        }
        ?>
        <?php if ($form_cb_idays) { ?>
  <th align="right"><?php echo xlt('Aging Days')?>&nbsp;</th>
    <?php } ?>
        <?php if (!$is_ins_summary) { ?>
  <th align="center"><?php echo xlt('Prv') ?></th>
  <th align="center"><?php echo xlt('Sel') ?></th>
<?php } ?>
        <?php if ($form_cb_err) { ?>
  <th>&nbsp;<?php echo xlt('Error')?></th>
<?php } ?>
 </thead>

        <?php
    } // end not export

    $ptrow = array('insname' => '', 'pid' => 0);
    $orow = -1;

    foreach ($rows as $key => $row) {
        list($insname, $unused , $ptname, $trash) = explode('|', $key);
        list($pid, $encounter) = explode(".", $row['invnumber']);
        if ($form_payer_id) {
            if ($ins_co_name <> $row['ins1']) {
                continue;
            }
        }

        if ($is_ins_summary && $insname != $ptrow['insname']) {
            endInsurance($ptrow);
            $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
            $ptrow = array('insname' => $insname, 'ptname' => $ptname, 'pid' => $pid, 'count' => 1);
            foreach ($row as $key => $value) {
                $ptrow[$key] = $value;
            }

            $ptrow['agedbal'] = array();
        } elseif (!$is_ins_summary && ($insname != $ptrow['insname'] || $pid != $ptrow['pid'])) {
            // For the report, this will write the patient totals.  For the
            // collections export this writes everything for the patient:
            endPatient($ptrow);
            $bgcolor = ((++$orow & 1) ? "#ffdddd" : "#ddddff");
            $ptrow = array('insname' => $insname, 'ptname' => $ptname, 'pid' => $pid, 'count' => 1);
            foreach ($row as $key => $value) {
                $ptrow[$key] = $value;
            }

            $ptrow['agedbal'] = array();
        } else {
            $ptrow['amount']      += $row['amount'];
            $ptrow['paid']        += $row['paid'];
            $ptrow['charges']     += $row['charges'];
            $ptrow['adjustments'] += $row['adjustments'];
            ++$ptrow['count'];
        }

        // Compute invoice balance and aging column number, and accumulate aging.
        $balance = $row['charges'] + $row['adjustments'] - $row['paid'];
        if ($form_age_cols) {
            $agedate = $is_ageby_lad ? $row['aging_date'] : $row['dos'];
            $agetime = mktime(
                0,
                0,
                0,
                (int) substr($agedate, 5, 2),
                (int) substr($agedate, 8, 2),
                (int) substr($agedate, 0, 4)
            );
            $days = floor((time() - $agetime) / (60 * 60 * 24));
            $agecolno = min($form_age_cols - 1, max(0, floor($days / $form_age_inc)));

            $ptrow['agedbal'][$agecolno] = $ptrow['agedbal'][$agecolno] ?? null;
            $ptrow['agedbal'][$agecolno] += $balance;
        }

        if (!$is_ins_summary && !$_POST['form_export'] && !$_POST['form_csvexport']) {
            $in_collections = stristr($row['billnote'], 'IN COLLECTIONS') !== false
                || $row['in_collection'] == 1;
            ?>
       <tr bgcolor='<?php echo attr($bgcolor) ?>'>
            <?php
            if ($ptrow['count'] == 1) {
                if ($is_due_ins) {
                    echo "  <td class='detail'>&nbsp;" . text($insname) . "</td>\n";
                }

                echo "  <td class='detail'>&nbsp;" . text($ptname) . "</td>\n";
                if ($form_cb_ssn) {
                    echo "  <td class='detail'>&nbsp;" . text($row['ss']) . "</td>\n";
                }

                if ($form_cb_dob) {
                    echo "  <td class='detail'>&nbsp;" . text(oeFormatShortDate($row['DOB'])) . "</td>\n";
                }

                if ($form_cb_pubpid) {
                    echo "  <td class='detail'>&nbsp;" . text($row['pubpid']) . "</td>\n";
                }

                if ($form_cb_policy) {
                    echo "  <td class='detail'>&nbsp;" . text($row['policy']) . "</td>\n";
                }
                if ($form_cb_group_number) {
                    echo "  <td class='detail'>&nbsp;" . text($row['groupnumber']) . "</td>\n";
                }

                if ($form_cb_phone) {
                    echo "  <td class='detail'>&nbsp;" . text($row['phone']) . "</td>\n";
                }

                if ($form_cb_city) {
                    echo "  <td class='detail'>&nbsp;" . text($row['city']) . "</td>\n";
                }

                if ($form_cb_ins1 || $form_payer_id) {
                    echo "  <td class='detail'>&nbsp;" . text($row['ins1']) . "</td>\n";
                }

                if ($form_provider) {
                    echo "  <td class='detail'>&nbsp;" . text($provider_name) . "</td>\n";
                }

                if ($form_cb_referrer) {
                    echo "  <td class='detail'>&nbsp;" . text($row['referrer']) . "</td>\n";
                }
            } else { // end $ptrow['count'] == 1
                echo "  <td class='detail' colspan='" . attr($initial_colspan) . "'>";
                echo "&nbsp;</td>\n";
            } // end not $ptrow['count'] == 1
            ?>
  <td class="detail">
     &nbsp;<a href="#" title='<?php echo xla("Open invoice (will refresh page)"); ?>' onclick="editInvoice(event,<?php echo attr_js($row['id']) ?>)">
            <?php echo empty($row['irnumber']) ? text($row['invnumber']) : text($row['irnumber']); ?></a>
  </td>
  <td class="detail">
   &nbsp;<?php echo "<input title='" . xla('To encounter') . "' type='button' class='btn btn-sm btn-secondary' value='" .
                   attr(oeFormatShortDate($row['dos'])) . "' onClick='toEncounter(" .
                   attr_js($row['pid']) . ", " . attr_js($row['encounter']) .
                   "); ' />"; ?>
  </td>
            <?php if ($form_cb_adate) { ?>
  <td class='detail'>
   &nbsp;<?php echo text(oeFormatShortDate($row['aging_date'])); ?>
  </td>
<?php } // end $form_cb_adate ?>
  <td class="detail" align="left">
            <?php echo text(FormatMoney::getBucks($row['charges'])) ?>&nbsp;
  </td>
  <td class="detail" align="left">
            <?php echo text(FormatMoney::getBucks($row['adjustments'])) ?>&nbsp;
  </td>
  <td class="detail" align="left">
            <?php echo text(FormatMoney::getBucks($row['paid'])) ?>&nbsp;
  </td>
            <?php
            if ($form_age_cols) {
                for ($c = 0; $c < $form_age_cols; ++$c) {
                    echo "  <td class='detail' align='left'>";
                    if ($c == $agecolno) {
                        echo text(FormatMoney::getBucks($balance));
                    }

                    echo "&nbsp;</td>\n";
                }
            } else { // end $form_age_cols
                ?>
<td class="detail" align="left"><?php echo text(FormatMoney::getBucks($balance)); ?>&nbsp;</td>
                <?php
            } // end else
            ?>
            <?php
            if ($form_cb_idays) {
                echo "  <td class='detail' align='right'>";
                echo text($row['inactive_days']) . "&nbsp;</td>\n";
            } // end $form_cb_idays
            ?>
  <td class="detail" align="center">
            <?php echo $row['duncount'] ? text($row['duncount']) : "&nbsp;" ?>
  </td>
  <td class="detail" align="center">
            <?php
            if ($in_collections) {
                echo "   <span class='font-weight-bold text-danger'>IC</span>\n";
            } else {
                echo "   <input type='checkbox' name='form_cb[" .  attr($row['invnumber'])  . "]' />\n";
            }
            ?>
  </td>
            <?php
            if ($form_cb_err) {
                echo "  <td class='detail'>&nbsp;";
                echo text($row['billing_errmsg']) . "</td>\n";
            }
            ?>
 </tr>
            <?php
        } elseif ($_POST['form_csvexport']) { // end not insurance summary, not export, not csvexport, not clear_ins_debt
          // The CSV detail line is written here added conditions for checked items (TLH).
          // Added zero balances for a complete spreadsheet view
            $balance = $row['charges'] + $row['adjustments'] - $row['paid'];

            if ($balance > 0 && $_POST['form_clear_ins_debt']) {
                foreach ($invlines as $key => $value) {
                    $ar_session_id = SLEOB::arGetSession($ins_id, 'Adj from collt report', date('YmdHis'), '', $balance);
                    SLEOB::arPostAdjustment($pid, $encounter, $ar_session_id, $value['bal'], $key, $insposition, 'Adj from collt report', 0, date('YmdHis'), $value['code_type']);
                    sqlStatement("UPDATE form_encounter SET last_level_closed = ? WHERE pid = ? AND encounter = ?", array($insposition, $pid, $encounter));
                }
            }

            if (
                (
                    $balance > 0 || ($_POST['form_zero_balances'] ?? '')
                )
            ) {
                echo csvEscape($row['ins1'])                         . ','; // insname
                echo csvEscape($ptname)                              . ',';
                if ($form_cb_ssn) {
                    echo csvEscape($row['ss'])                          . ',';
                }

                if ($form_cb_dob) {
                    echo csvEscape(oeFormatShortDate($row['DOB']))       . ',';
                }

                if ($form_cb_pubpid) {
                    echo csvEscape($row['pubpid'])                       . ',';
                }

                if ($form_cb_policy) {
                    echo csvEscape($row['policy'])                       . ',';
                }
                if ($form_cb_group_number) {
                    echo csvEscape($row['groupnumber'])                 . ',';
                }

                if ($form_cb_phone) {
                    echo csvEscape($row['phone'])                       . ',';
                }

                if ($form_cb_city) {
                    echo csvEscape($row['city'])                       . ',';
                }

                echo (empty($row['irnumber']) ? csvEscape($row['invnumber']) : csvEscape($row['irnumber'])) . ',';
                echo csvEscape(oeFormatShortDate($row['dos']))       . ',';
                echo csvEscape($row['referrer'])                     . ',';
                echo csvEscape($row['provider'])                     . ',';
                echo csvEscape(oeFormatMoney($row['charges']))       . ',';
                echo csvEscape(oeFormatMoney($row['adjustments']))   . ',';
                echo csvEscape(oeFormatMoney($row['paid']))          . ',';
                echo csvEscape(oeFormatMoney($balance))              . ',';
                echo csvEscape($row['inactive_days'])                . ',';
                if ($form_cb_err) {
                    echo csvEscape($row['billing_errmsg'])               . "\n";
                } else {
                    echo "\n";
                }
            } // end $balance > 0 or $_POST['form_zero_balances']
        }
    } // end loop

    if ($is_ins_summary) {
        endInsurance($ptrow);
    } else {
        endPatient($ptrow);
    }

    if ($_POST['form_export']) {
        echo "</textarea>\n";
        $alertmsg .= "$export_patient_count patients with a total of " .
        oeFormatMoney($export_dollars) . " have been exported ";
        if ($_POST['form_without'] ?? null) {
            $alertmsg .= "but NOT flagged as in collections.";
        } else {
            $alertmsg .= "AND flagged as in collections.";
        }
    } elseif ($_POST['form_csvexport']) {
        // todo: trigger alert with this message
        // echo "</textarea>\n";
        // $alertmsg .= "$export_patient_count patients representing $" .
        //    sprintf("%.2f", $export_dollars) . " have been exported.";
    } else {
        echo " <tr class='bg-white'>\n";
        if ($is_ins_summary) {
            echo "  <td class='dehead'>&nbsp;" . xlt('Report Totals') . ":</td>\n";
        } else {
            echo "  <td class='detail' colspan='" . attr($initial_colspan) . "'>\n";
            echo "   &nbsp;</td>\n";
            echo "  <td class='dehead' colspan='" . attr($final_colspan - 3) .
            "'>&nbsp;" . xlt('Report Totals') . ":</td>\n";
        }

        echo "  <td class='dehead' align='left'>&nbsp;" .
        text(oeFormatMoney($grand_total_charges)) . "&nbsp;</td>\n";
        echo "  <td class='dehead' align='left'>&nbsp;" .
        text(oeFormatMoney($grand_total_adjustments)) . "&nbsp;</td>\n";
        echo "  <td class='dehead' align='left'>&nbsp;" .
        text(oeFormatMoney($grand_total_paid)) . "&nbsp;</td>\n";
        if ($form_age_cols) {
            for ($c = 0; $c < $form_age_cols; ++$c) {
                echo "  <td class='dehead' align='left'>" .
                text(oeFormatMoney($grand_total_agedbal[$c])) . "&nbsp;</td>\n";
            }
        } else {
            echo "  <td class='dehead' align='left'>" .
            text(oeFormatMoney($grand_total_charges +
            $grand_total_adjustments - $grand_total_paid)) . "&nbsp;</td>\n";
        }

        if ($form_cb_idays) {
            echo "  <td class='detail'>&nbsp;</td>\n";
        }

        if (!$is_ins_summary) {
            echo "  <td class='detail' colspan='2'>&nbsp;</td>\n";
        }

        if ($form_cb_err) {
            echo "  <td class='detail'>&nbsp;</td>\n";
        }

        echo " </tr>\n";
        echo "</table>\n";
        echo "</div>\n";
    }
} // end if form_refresh


if (empty($_POST['form_csvexport'])) {
    if (empty($_POST['form_export'])) {
        ?>

  <div style='margin-top:5px'>
    <div class="btn-group float-left" role="group">
    <a href='javascript:;' class='btn btn-secondary btn-save'  onclick='checkAll(true)'><?php echo xlt('Select All'); ?></a>
    <a href='javascript:;' class='btn btn-secondary btn-cancel'  onclick='checkAll(false)'><?php echo xlt('Clear All'); ?></a>
    <a href='javascript:;' class='btn btn-secondary btn-transmit' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
            <?php echo xlt('Export Selected as CSV'); ?>
    </a>
    <a href='javascript:;' class='btn btn-secondary btn-transmit' onclick='$("#form_export").attr("value","true"); $("#form_csvexport").val(""); $("#form_clear_ins_debt").val("");$("#theform").submit();'>
            <?php echo xlt('Export Selected to Collections'); ?>
    </a>
    <a href='javascript:;' class='btn btn-secondary btn-transmit' onclick='$("#form_clear_ins_debt").attr("value", "true"); $("#form_export").val(""); $("#form_csvexport").attr("value", "true"); $("#theform").submit();'>
            <?php echo xlt('Clear Insurance Debt'); ?>
    </a>
  </div>

  <div style='float:left'>
    <label><input type='checkbox' name='form_zero_balances' value='1' /> <?php echo xlt('Export Zero Balances') ?>&nbsp;&nbsp;</label>
  </div>

  <div style='float:left'>
    <label><input type='checkbox' name='form_individual' value='1' /> <?php echo xlt('Export Individual Invoices') ?>&nbsp;&nbsp;</label>
  </div>

  <div style='float:left'>
    <label><input type='checkbox' name='form_without' value='1' /> <?php echo xlt('Without Update') ?></label>
  </div>
</div>

            <?php
    } // end not export
    ?>
</form>
<script>
    <?php
    if ($alertmsg) {
        echo "alert(" . js_escape($alertmsg) . ");\n";
    }
    ?>
</script>
</body>
</html>
    <?php
} // end not form_csvexport
?>
