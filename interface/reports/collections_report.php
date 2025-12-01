<?php

/**
 * Collections report: various options to report on the current billing status of encounters
 * Refactored with Warp Terminal by Sherwin Gaddis 2025-11-27
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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Reports\Collections\Services\HeaderService;
use OpenEMR\Reports\Collections\Services\RowService;
use OpenEMR\Reports\Collections\Services\GroupingService;
use OpenEMR\Reports\Collections\Services\TotalsService;
use OpenEMR\Reports\Collections\Repository\CollectionsReportRepository;

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
    global $encounters;

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
            foreach ($encounters as $key => $item) {
                sqlStatement("UPDATE form_encounter SET in_collection = 1 WHERE encounter = ?", [$item]);
            }
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

// Prepare data for Twig template (Phase 1 modernization)
if (empty($_POST['form_csvexport'])) {
    // Step 1: Prepare filter data array
    $filterData = [
        'form_page_y' => $form_page_y,
        'form_offset_y' => $form_offset_y,
        'form_y' => $form_y,
        'form_cb_ssn' => $form_cb_ssn,
        'form_cb_dob' => $form_cb_dob,
        'form_cb_pubpid' => $form_cb_pubpid,
        'form_cb_policy' => $form_cb_policy,
        'form_cb_phone' => $form_cb_phone,
        'form_cb_city' => $form_cb_city,
        'form_cb_ins1' => $form_cb_ins1,
        'form_cb_referrer' => $form_cb_referrer,
        'form_cb_adate' => $form_cb_adate,
        'form_cb_idays' => $form_cb_idays,
        'form_cb_err' => $form_cb_err,
        'form_cb_group_number' => $form_cb_group_number,
        'form_date' => oeFormatShortDate($form_date),
        'form_to_date' => oeFormatShortDate($form_to_date),
        'form_category' => $form_category,
        'form_ageby' => $_POST['form_ageby'] ?? 'Service Date',
        'form_age_cols' => $form_age_cols ?: 3,
        'form_age_inc' => $form_age_inc ?: 30,
        'form_cb_with_debt' => $form_cb_with_debt,
    ];

    // Step 2: Generate facility dropdown
    ob_start();
    dropdown_facility($form_facility, 'form_facility', false);
    $facilityDropdown = ob_get_clean();

    // Step 3: Generate insurance dropdown
    ob_start();
    insuranceSelect();
    $insuranceDropdown = ob_get_clean();

    // Step 4: Generate provider dropdown
    ob_start();
    $query = "SELECT id, lname, fname FROM users WHERE authorized = 1 ORDER BY lname, fname";
    $ures = sqlStatement($query);
    echo "<select name='form_provider' id='form_provider' class='form-control'>\n";
    echo "<option value=''>-- " . xlt('All') . " --</option>\n";
    while ($urow = sqlFetchArray($ures)) {
        $provid = $urow['id'];
        echo "<option value='" . attr($provid) . "'";
        if ($provid == $form_provider) {
            echo " selected";
        }
        echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "</option>\n";
    }
    echo "</select>\n";
    $providerDropdown = ob_get_clean();
}

// === STEP 1: PREPARE DATA (if form was submitted) ===
// Use repository to fetch and prepare invoice data
$rows = [];
if (!empty($_POST['form_refresh']) || !empty($_POST['form_export']) || !empty($_POST['form_csvexport'])) {
    // Build filter array for repository
    $filters = [
        'form_export' => $_POST['form_export'] ?? false,
        'form_csvexport' => $_POST['form_csvexport'] ?? false,
        'form_cb' => $_POST['form_cb'] ?? [],
        'form_individual' => $_POST['form_individual'] ?? '',
        'form_date' => $form_date,
        'form_to_date' => $form_to_date,
        'form_facility' => $form_facility,
        'form_provider' => $form_provider,
        'form_cb_with_debt' => $form_cb_with_debt,
        'form_refresh' => $_POST['form_refresh'] ?? false,
        'is_all' => $is_all,
        'form_category' => $form_category,
        'form_cb_policy' => $form_cb_policy,
        'form_cb_group_number' => $form_cb_group_number,
        'is_due_ins' => $is_due_ins,
        'is_due_pt' => $is_due_pt,
        'is_ins_summary' => $is_ins_summary,
    ];

    // Instantiate repository and fetch data
    $repository = new CollectionsReportRepository();
    $rows = $repository->fetchInvoiceData($filters);

    // Sort by the composite key (already included as '_sort_key' in repository results)
    ksort($rows);
} // End data preparation

// === STEP 2: TRANSFORM DATA WITH SERVICES (for Twig rendering) ===
$patient_groups = [];
$grand_totals = [];
$headers = [];
$serviceConfig = [];

if (!empty($_POST['form_refresh']) && !empty($rows)) {
    // Instantiate service classes
    $headerService = new HeaderService();
    $rowService = new RowService();
    $groupingService = new GroupingService();
    $totalsService = new TotalsService();

    // Build configuration array for services
    $serviceConfig = [
        'form_cb_ssn' => $form_cb_ssn,
        'form_cb_dob' => $form_cb_dob,
        'form_cb_pubpid' => $form_cb_pubpid,
        'form_cb_policy' => $form_cb_policy,
        'form_cb_group_number' => $form_cb_group_number,
        'form_cb_phone' => $form_cb_phone,
        'form_cb_city' => $form_cb_city,
        'form_cb_ins1' => $form_cb_ins1,
        'form_cb_referrer' => $form_cb_referrer,
        'form_cb_adate' => $form_cb_adate,
        'form_cb_idays' => $form_cb_idays,
        'form_cb_err' => $form_cb_err,
        'form_provider' => $form_provider,
        'form_payer_id' => $form_payer_id,
        'form_age_cols' => $form_age_cols,
        'form_age_inc' => $form_age_inc,
        'form_ageby' => $_POST['form_ageby'] ?? 'Service Date',
        'is_ins_summary' => $is_ins_summary,
        'is_due_ins' => $is_due_ins,
    ];

    // Calculate colspan values for grand totals row (matches original algorithm)
    // $initial_colspan counts demographic columns before Invoice column
    $initial_colspan = 1; // Always start with 1 for Name column
    if ($is_due_ins) {
        ++$initial_colspan; // Insurance column in "Due Ins" mode
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
    if ($form_cb_ins1 || $form_payer_id) {
        ++$initial_colspan; // Primary Ins column
    }
    if ($form_provider) {
        ++$initial_colspan;
    }
    if ($form_cb_referrer) {
        ++$initial_colspan;
    }

    // $final_colspan counts Invoice, Svc Date, and Activity Date columns
    $final_colspan = $form_cb_adate ? 6 : 5;

    $serviceConfig['initial_colspan'] = $initial_colspan;
    $serviceConfig['final_colspan'] = $final_colspan;
    $serviceConfig['show_aging_cols'] = ($form_age_cols > 0);

    // Generate headers
    $headers = $headerService->generateHeaders($serviceConfig);

    // Prepare invoice rows with RowService
    $preparedRows = [];
    foreach ($rows as $rowKey => $row) {
        $preparedRows[] = $rowService->prepareRow($row, $serviceConfig, false);
    }

    // Group by patient or insurance
    if ($is_ins_summary) {
        $patient_groups = $groupingService->groupByInsurance($preparedRows, $serviceConfig);
    } else {
        $patient_groups = $groupingService->groupByPatient($preparedRows, $serviceConfig);
    }

    // Calculate grand totals
    $grand_totals = $totalsService->calculateGrandTotals($patient_groups, $serviceConfig);
}

// === STEP 3: RENDER TEMPLATES OR HANDLE EXPORTS ===
if (!empty($_POST['form_csvexport'])) {
    // CSV Export: Force download with headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=collections_report.csv");
    header("Content-Description: File Transfer");
    insuranceSelect();
} elseif (!empty($_POST['form_export'])) {
    // Collections Export: Will render below in old format
} else {
    // Normal rendering via Twig templates
    $templateVars = [
        'csrf_token_form' => CsrfUtils::collectCsrfToken(),
        'filters' => $filterData ?? [],
        'facility_dropdown' => $facilityDropdown ?? '',
        'insurance_dropdown' => $insuranceDropdown ?? '',
        'provider_dropdown' => $providerDropdown ?? '',
        'show_results' => !empty($_POST['form_refresh']),
        'webroot' => $GLOBALS['webroot'],
        // Service-prepared data for results table
        'patient_groups' => $patient_groups,
        'grand_totals' => $grand_totals,
        'headers' => $headers,
        'config' => $serviceConfig,
        'is_insurance_summary' => $is_ins_summary,
    ];

    $twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();
    echo $twig->render('reports/collections/collections_report.twig', $templateVars);
}

// === STEP 3: EXPORT RENDERING (CSV and Collections formats) ===
if (!empty($_POST['form_refresh']) || !empty($_POST['form_export']) || !empty($_POST['form_csvexport'])) {
    if ($_POST['form_export'] ?? false) {
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
    }
} // end if form_refresh

// End of Collections Report controller
// All rendering now handled by Twig templates and service classes
