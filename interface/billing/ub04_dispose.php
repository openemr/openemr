<?php

/**
 * UB04 Functions
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\Claim;
use OpenEMR\Pdf\PdfCreator;

function ub04_dispose()
{
    $dispose = ($_POST['handler'] ?? null) ? $_POST['handler'] : ($_GET['handler'] ?? null);
    if ($dispose) {
        if ($dispose == "edit_save") {
            $ub04id = isset($_POST['ub04id']) ? $_POST['ub04id'] : $_GET['ub04id'];
            $pid = isset($_POST['pid']) ? $_POST['pid'] : $_GET['pid'];
            $encounter = isset($_POST['encounter']) ? $_POST['encounter'] : $_GET['encounter'];
            $action = $_REQUEST['action'];
            $ub04id = json_decode($ub04id, true);
            saveTemplate($encounter, $pid, $ub04id, $action);
            exit();
        } elseif ($dispose == "payer_save") {
            $ub04id = isset($_POST['ub04id']) ? $_POST['ub04id'] : $_GET['ub04id'];
            $payerid = isset($_POST['payerid']) ? $_POST['payerid'] : $_GET['payerid'];
            savePayerTemplate($payerid, $ub04id);
            exit("done");
        } elseif ($dispose == "batch_save") {
            $pid = isset($_POST['pid']) ? $_POST['pid'] : $_GET['pid'];
            $encounter = isset($_POST['encounter']) ? $_POST['encounter'] : $_GET['encounter'];
            $ub04id = isset($_POST['ub04id']) ? $_POST['ub04id'] : $_GET['ub04id'];
            saveTemplate($encounter, $pid, $ub04id, $dispose);
            exit("done");
        } elseif ($dispose == "reset_claim") {
            $pid = $_POST['pid'] ?? $_GET['pid'];
            $encounter = $_POST['encounter'] ?? $_GET['encounter'];
            // clear claim first otherwise get ub04 returns cuurent version.
            //
            $flg = exist_ub04_claim($pid, $encounter, true);
            if ($flg === true) {
                BillingUtilities::updateClaim(false, $pid, $encounter, -1, -1, -1, -1, '', 'ub04', -1, 0, "");
            }
            $ub04id = get_ub04_array($pid, $encounter);
            $ub04id = json_encode($ub04id);

            echo $ub04id;
            exit();
        }
        die(xlt('Do not know what to do!'));
    }
}

function get_payer_defaults($payerid)
{
    $p = sqlQuery("SELECT insurance_companies.* FROM insurance_companies WHERE insurance_companies.id = ?", array($payerid));
    if ($p['claim_template']) {
        return $p['claim_template'];
    } else {
        return json_encode(array());
    }
}

function savePayerTemplate($payerid, $ubo4id)
{
    $ub04id = json_encode($ub04id);
    sqlStatement("update insurance_companies set claim_template = ? where id = ?", array(
        $ubo4id,
        $payerid
    ));
}

function saveTemplate($encounter, $pid, $ub04id, $action = 'form')
{
    global $isAuthorized;
    if ($action != 'batch_save') {
        $ub04id = json_encode($ub04id);
        $isAuthorized = true;
        ob_start();
        require(dirname(__file__) . "/ub04_form.php");
        $htmlin = ob_get_clean();
        $isAuthorized = false;
        ub04Dispose('download', $htmlin, "ub04_download.pdf", $action);
    }
    $flg = exist_ub04_claim($pid, $encounter, true);
    if ($flg === true) {
        BillingUtilities::updateClaim(false, $pid, $encounter, - 1, - 1, - 1, - 1, '', 'ub04', - 1, 0, $ub04id);
    } else {
        BillingUtilities::updateClaim(true, $pid, $encounter, - 1, - 1, 1, 1, '', 'ub04', - 1, 0, $ub04id);
    }
}

function buildTemplate(string $pid = null, string $encounter = null, $htmlin, string $action = null, &$log)
{
    global $srcdir, $isAuthorized;

    if (!$action) {
        $action = 'form';
    }
    $ub04id = get_ub04_array($pid, $encounter, $log);

    $ub04id = json_encode($ub04id);

    $isAuthorized = true;
    ob_start();
    require $srcdir . "/../interface/billing/ub04_form.php";
    $htmlin = ob_get_clean();
    $isAuthorized = false;

    return $htmlin;
}

function ub04Dispose($dispose = 'download', $htmlin = "", $filename = "ub04.pdf", $form_action = "")
{
    $top = $_POST["left_ubmargin"] ?? $GLOBALS['left_ubmargin_default'];
    $side = $_POST["top_ubmargin"] ?? $GLOBALS['top_ubmargin_default'];
    $form_filename = $GLOBALS['OE_SITE_DIR'] . "/documents/edi/$filename";
    // convert points to inches-some tricky calculus here! 72 pts/inch
    $top = round($top / 72.00, 2) . "in";
    $side = round($side / 72.00, 2) . "in";

    try {
        if ($dispose == 'download') {
            $isnotform = false;
            if ($form_action == "noform") {
                $isnotform = true;
            }
            $options = array(
                'margin-top' => $top,
                'margin-bottom' => '0in',
                'margin-left' => $side,
                'margin-right' => $side,
                'zoom' => '1.045',
                'print-media-type' => true,
                'lowquality' => true,
                'no-outline' => true,
                'keep-relative-links' => true,
                'no-images' => $isnotform,
                'grayscale' => true,
                'page-size' => 'Letter',
                'orientation' => 'Portrait',
                'load-media-error-handling' => 'ignore',
                'load-error-handling' => 'ignore'
            );

            $PdfCreator = new PdfCreator();
            $pdfwkout = $PdfCreator->getPdf($htmlin, $options);

            /*
             * $fh = @fopen($form_filename, 'w'); // Future Use!
             * if ($fh) {
             * fwrite($fh, $pdfwkout);
             * fclose($fh);
             * }
             */

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Type: application/pdf');
            header("Content-Disposition: download; filename=$filename");
            header("Content-Description: File Transfer");
            echo $pdfwkout;
        }
    } catch (Exception $e) {
        echo xlt($e->getMessage());
    }
    return true;
}

function exist_ub04_claim($pid, $encounter, $flag = false)
{
    $sql = "SELECT * FROM claims WHERE patient_id = ? AND encounter_id = ? AND status > 0 AND status < 4 ";
    $sql .= "ORDER BY version DESC LIMIT 1";
    $row = sqlQuery($sql, array(
        $pid,
        $encounter
    ));
    if ($row) {
        if (!empty($row['submitted_claim'])) {
            if ($flag === false) {
                return $row['submitted_claim'];
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    return false;
}

function get_ub04_array($pid, $encounter, &$log = "")
{

    $exist_ub04 = exist_ub04_claim($pid, $encounter);
    if ($exist_ub04) {
        $log .= "*** Info: Using saved edited claim.";
        return json_decode($exist_ub04, true);
    }
    $log .= "*** Generating UB04 Claim.";
    $today = time();
    $claim = new Claim($pid, $encounter, false);

    $ub04id = array();
    $ub04id[0] = array();
    // $ub04id[0] Is reserved for params
    $ub04id[2] = $claim->billingFacilityName();
    $ub04id[4] = $claim->billingFacilityStreet();
    $ub04id[8] = $claim->billingFacilityCity() . ', ' . $claim->billingFacilityState() . ', ' . $claim->billingFacilityZip(); /* 1. BILLING PROVIDER CITY, STATE, ZIP */
    $tmp = $claim->billingContactPhone();
    $ub04id[10] = substr($tmp, 0, 3) . '-' . substr($tmp, 3, 3) . '-' . substr($tmp, 6);
    $ub04id[3] = $pid . ' ' . $encounter; /* 3a. PATIENT CONTROL NUMBER */
    $ub04id[6] = $pid; /* 3b. MEDICAL/HEALTH RECORD NUMBER */
    $ub04id[7] = ! empty($ub04id[7]) ? $ub04id[7] : '831'; /* 4. TYPE OF BILL */
    $ub04id[12] = $claim->facilityETIN(); /* 5. FEDERAL TAX NUMBER */

    $tmp = $claim->serviceDate();
    $ub04id[13] = substr($tmp, 4, 2) . substr($tmp, 6, 2) . substr($tmp, 2, 2); /* 6. STATEMENT COVERS PERIOD FROM DATE */
    $ub04id[14] = ''; /* 6. STATEMENT COVERS PERIOD TO DATE */
    // $ub04id[16] = ''; /* 8a. PATIENT IDENTIFIER */
    $tmp = $claim->patientLastName() . ', ' . $claim->patientFirstName();
    if ($claim->patientMiddleName()) {
        $tmp .= ', ' . substr($claim->patientMiddleName(), 0, 1);
    }
    $ub04id[18] = $tmp; /* 8b. PATIENT NAME */
    $ub04id[17] = $claim->patientStreet(); /* 9a. PATIENT STREET ADDRESS */
    $ub04id[19] = $claim->patientCity(); /* 9b. PATIENT CITY */
    $ub04id[20] = $claim->patientState(); /* 9c. PATIENT STATE */
    $ub04id[21] = $claim->patientZip(); /* 9d. PATIENT ZIP CODE */
    $ub04id[22] = ''; /* 9e. PATIENT COUNTRY CODE */
    $tmp = $claim->patientDOB();
    $ub04id[23] = substr($tmp, 4, 2) . substr($tmp, 6, 2) . substr($tmp, 0, 4); /* 10. PATIENT BIRTH DATE (MMDDYYYY) */
    $ub04id[24] = $claim->patientSex(); /* 11. PATIENT SEX */
    $tmp = $claim->onsetDate();
    $ub04id[25] = substr($tmp, 4, 2) . substr($tmp, 6, 2) . substr($tmp, 2, 2); /* 12. PATIENT ADMISSION/START OF CARE DATE (MMDDYY) */

    $ub04_proc_index = 0; // Test for second page of charges.
    $proccount = $claim->procCount();
    $clm_total_charges = 0;
    $clm_amount_adjusted = 0;
    $clm_amount_paid = $ub04_proc_index ? 0 : $claim->patientPaidAmount();
    for ($tlh = 0; $tlh < $proccount; ++$tlh) {
        $tmp = $claim->procs[$tlh]['code_text'];
        if ($claim->procs[$tlh]['code_type'] == 'HCPCS') {
            $tmpcode = '3';
        } else {
            $tmpcode = '1';
        }
        $getrevcd = $claim->cptCode($tlh);
        $sql = "SELECT * FROM codes WHERE code_type = ? and code = ? ORDER BY revenue_code DESC";
        $revcode[$tlh] = sqlQuery($sql, array(
            $tmpcode,
            $getrevcd
        ));
        if (!empty($revcode[$tlh])) {
            $claim->procs[$tlh]['revenue_code'] = $claim->procs[$tlh]['revenue_code'] ? $claim->procs[$tlh]['revenue_code'] : $revcode[$tlh]['revenue_code'];
            $revcode2[$tlh] = array_merge($revcode[$tlh], $claim->procs[$tlh]);
        }
    }
    foreach ($revcode as $key => $row) {
        if (!empty($row)) {
            $revcod[$key] = $row['revenue_code'];
        }
    }
    array_multisort($revcod, SORT_ASC, $revcode2);
    // Procedure loop starts here.
    $os = 99; // Line 1 - 23 offset
    $dos = 382;
    $pcnt = 0;
    for ($svccount = 0; $svccount < 22 && $ub04_proc_index < $proccount; ++$ub04_proc_index) {
        $dia = $claim->diagIndexArray($ub04_proc_index);
        if (! $claim->cptCharges($ub04_proc_index)) {
            $log .= "*** Procedure '" . $claim->cptKey($ub04_proc_index) . "' has no charges!\n";
        }
        if (empty($dia)) {
            $log .= "*** Procedure '" . $claim->cptKey($ub04_proc_index) . "' is not justified!\n";
        }
        $clm_total_charges += $claim->cptCharges($ub04_proc_index);
        // Compute prior payments and "hard" adjustments.
        for ($ins = 1; $ins < $claim->payerCount(); ++$ins) {
            if ($claim->payerSequence($ins) > $claim->payerSequence()) {
                continue; // skip future payers
            }
            $payerpaid = $claim->payerTotals($ins, $claim->cptKey($ub04_proc_index));
            $clm_amount_paid += $payerpaid[1];
            $clm_amount_adjusted += $payerpaid[2];
        }
        ++$svccount;
        $mcnt = $ub04_proc_index;

        // @todo need if inpatient or out patient for box 74 - 74e
        $tmp = $claim->cleanDate($revcode2[$mcnt]['date']);
        $sdate = substr($tmp, 4, 2) . substr($tmp, 6, 2) . substr($tmp, 2, 2);
        if ($pcnt < 6) {
            $ub04id[$dos++] = $revcode2[$mcnt]['code']; /* 74. PRINCIPAL PROCEDURE CODE */
            $ub04id[$dos++] = $sdate; /* 74. PRINCIPAL PROCEDURE DATE */
            if ($dos == 388) {
                $dos = 393;
            }
            $pcnt++;
        }
        // @todo Deal with code modifiers $revcode2[$mcnt][modifier]
        $tmp = $claim->serviceDate();
        $sdate = substr($tmp, 4, 2) . substr($tmp, 6, 2) . substr($tmp, 2, 2);
        $ub04id[$os] = $claim->procs[$mcnt]['revenue_code']; // 42. REVENUE CODE, Line 1-23 */
        $ub04id[++$os] = strtoupper($revcode2[$mcnt]['code_text']); /* 43. REVENUE DESCRIPTION, Line 1-23 */
        $ub04id[++$os] = trim($revcode2[$mcnt]['code'] . ' ' . $revcode2[$mcnt]['modifier']); /* 44. HCPCS/ACCOMMODATION RATES/HIPPS RATE CODES, Line 1-23 */
        $ub04id[++$os] = $sdate; /* 45. SERVICE DATE, Line 1-23 */
        $ub04id[++$os] = $revcode2[$mcnt]['units']; /* 46. SERVICE UNITS, Line 1-23 */
        $ub04id[++$os] = str_replace('.', '  ', sprintf('%8.2f', $revcode2[$mcnt]['fee'])); /* 47. TOTAL CHARGES, Line 1-23 */
        $ub04id[++$os] = ''; /* 48. NON-COVERED CHARGES, Line 1-23 */
        $os += 2;
    }
    $ub04id[275] = '0001'; /* 42. REVENUE CODE, Line 23 */
    $ub04id[276] = '1'; /* 43. CLAIM PAGE NUMBER */
    $ub04id[277] = '1'; /* 43. TOTAL NUMBER OF CLAIM PAGES */
    $ub04id[278] = date('mdy', $today); /* 45. CREATION DATE, Line 23 */
    $ub04id[279] = str_replace('.', '  ', sprintf('%8.2f', $clm_total_charges)); /* 47. TOTAL OF TOTAL CHARGES, Line 23 */
    if (! $clm_total_charges) {
        $log .= "* Claim total is zero charges!\n";
    }
    // Diagnosis Should be ICD10
    $ub04id[347] = '0'; /* 66. DIAGNOSIS AND PROCEDURE CODE QUALIFIER (ICD VERSION INDICATOR) */
    $os = 328; /* 67. PRINCIPAL DIAGNOSIS CODE AND POA INDICATOR */
    $diagnosis = array();
    foreach ($claim->diagArray() as $diag) {
        $diagnosis[] = $diag;
        if (! empty($diag)) {
            if ($os == 362) {
                $ub04id[$os++] = substr($diag, 0, 7);
                $ub04id[366] = "";
                continue;
            }
            $ub04id[$os++] = substr($diag, 0, 7);
            $ub04id[$os++] = "";
        }
        if ($os == 346) {
            $os = 348;
        }
        if ($os == 365) {
            break;
        }
    }
    // @todo Not sure
    $ub04id[367] = $diagnosis[0] ?? '' ? substr($diagnosis[0], 0, 7) : ''; /* 69. ADMITTING DIAGNOSIS CODE */
    $ub04id[368] = $diagnosis[1] ?? '' ? substr($diagnosis[1], 0, 7) : ''; /* 70a. PATIENT'S REASON FOR VISIT */
    $ub04id[369] = $diagnosis[2] ?? '' ? substr($diagnosis[2], 0, 7) : ''; /* 70b. PATIENT'S REASON FOR VISIT */
    $ub04id[370] = $diagnosis[3] ?? '' ? substr($diagnosis[3], 0, 7) : ''; /* 70c. PATIENT'S REASON FOR VISIT */

    $payer_os = 0;
    if (empty($claim->payerName(0))) {
        $payer_os = 1;
    }
    $ub04id[282] = $claim->facilityNPI(); /* 56. NATIONAL PROVIDER IDENTIFIER - BILLING PROVIDER */
    $ub04id[283] = $claim->payerName($payer_os); /* 50a. PRIMARY PAYER NAME */
    $ub04id[284] = $claim->planName($payer_os); /* 51a. PRIMARY PAYER HEALTH PLAN ID */
    $ub04id[285] = 'Y'; /* 52a. RELEASE OF INFORMATION CERTIFICATION INDICATOR, PRIMARY PAYER */
    $tmp = $claim->billingFacilityAssignment() ? 'Y' : 'N';
    $ub04id[286] = $tmp; /* 53a. ASSIGNMENT OF BENEFITS CERTIFICATION INDICATOR, PRIMARY PAYER */
    //$ub04id[287] = ''; /* 54a. PRIMARY PAYER PRIOR PAYMENTS */
    //$ub04id[288] = ''; /* 55a. PRIMARY PAYER ESTIMATED AMOUNT DUE */
    if (!empty($claim->payerName($payer_os + 1))) {
        $ub04id[290] = $claim->payerName($payer_os + 1); /* 50b. SECONDARY PAYER NAME */
        $ub04id[291] = $claim->planName($payer_os + 1); /* 51b. SECONDARY PAYER HEALTH PLAN ID */
        $ub04id[292] = 'Y'; /* 52b. RELEASE OF INFORMATION CERTIFICATION INDICATOR, SECONDARY PAYER */
        $tmp = (null !== $claim->payerName($payer_os + 1)) && $claim->billingFacilityAssignment() ? 'Y' : 'N';
        $ub04id[293] = $tmp; /* 53b. ASSIGNMENT OF BENEFITS CERTIFICATION INDICATOR, SECONDARY PAYER */
    }

    if ($claim->insuredMiddleName()) {
        $tmp = $claim->insuredLastName() . ', ' . $claim->insuredFirstName() . ' ' . substr($claim->insuredMiddleName(), 0, 1);
    } else {
        $tmp = $claim->insuredLastName() . ', ' . $claim->insuredFirstName();
    }
    $ub04id[304] = $tmp; /* 58a. INSURED'S NAME - PRIMARY PLAN */
    $ub04id[305] = $claim->insuredRelationship(); /* 59a. PATIENT'S RELATIONSHIP TO INSURED - PRIMARY PLAN */
    $ub04id[306] = $claim->policyNumber(); /* 60a. INSURED'S UNIQUE IDENTIFIER - PRIMARY PLAN */
    $ub04id[307] = $claim->groupName(); /* 61a. INSURED'S GROUP NAME - PRIMARY PLAN */
    $ub04id[308] = $claim->groupNumber(); /* 62a. INSURANCE GROUP NUMBER - PRIMARY PLAN */

    return $ub04id;
}
