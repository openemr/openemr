<?php

/*
 * Billing process Program
 *
 * This program processes data for claims generation
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2014-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\Hcfa1500;
use OpenEMR\Billing\X125010837I;
use OpenEMR\Billing\X125010837P;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if ($GLOBALS['ub04_support']) {
    require_once("./ub04_dispose.php");
}
$EXPORT_INC = "$webserver_root/custom/BillingExport.php";
if (file_exists($EXPORT_INC)) {
    include_once($EXPORT_INC);
    $BILLING_EXPORT = true;
}

$bill_info = array();

$bat_type = ''; // will be edi or hcfa
$bat_sendid = '';
$bat_recvid = '';
$bat_content = '';
$bat_gscount = 0;
$bat_stcount = 0;
$bat_time = time();
$bat_hhmm = date('Hi', $bat_time);
$bat_yymmdd = date('ymd', $bat_time);
$bat_yyyymmdd = date('Ymd', $bat_time);
// Seconds since 1/1/1970 00:00:00 GMT will be our interchange control number
// but since limited to 9 char must be without leading 1
$bat_icn = substr((string)$bat_time, 1, 9);
$bat_filename = date("Y-m-d-Hi", $bat_time) . "-batch.";
$bat_filename .= (isset($_POST['bn_process_hcfa']) || isset($_POST['bn_process_hcfa_form']) || isset($_POST['bn_process_ub04']) || isset($_POST['bn_process_ub04_form'])) ? 'pdf' : 'txt';
$template = array();
$ub04id = array();
$validatePass = false;

if (isset($_POST['bn_process_hcfa']) || isset($_POST['bn_process_hcfa_form'])) {
    $pdf = new Cezpdf('LETTER');
    $pdf->ezSetMargins(trim($_POST['top_margin']) + 0, 0, trim($_POST['left_margin']) + 0, 0);
    $pdf->selectFont('Courier');
}

function append_claim(&$segs)
{
    global $bat_content, $bat_sendid, $bat_recvid, $bat_sender, $bat_stcount;
    global $bat_gscount, $bat_yymmdd, $bat_yyyymmdd, $bat_hhmm, $bat_icn;

    foreach ($segs as $seg) {
        if (!$seg) {
            continue;
        }
        $elems = explode('*', $seg);
        if ($elems[0] == 'ISA') {
            if (!$bat_content) {
                $bat_sendid = trim($elems[6]);
                $bat_recvid = trim($elems[8]);
                $bat_sender = (!empty($GS02)) ? $GS02 : $bat_sendid;
                $bat_content = substr($seg, 0, 70) . "$bat_yymmdd*$bat_hhmm*" . $elems[11] . "*" . $elems[12] . "*$bat_icn*" . $elems[14] . "*" . $elems[15] . "*:~";
            }
            continue;
        } elseif (!$bat_content) {
            die("Error:<br />\nInput must begin with 'ISA'; " . "found '" . text($elems[0]) . "' instead");
        }
        if ($elems[0] == 'GS') {
            if ($bat_gscount == 0) {
                ++$bat_gscount;
                $bat_content .= "GS*HC*" . $elems[2] . "*" . $elems[3] . "*$bat_yyyymmdd*$bat_hhmm*1*X*" . $elems[8] . "~";
            }
            continue;
        }
        if ($elems[0] == 'ST') {
            ++$bat_stcount;
            $bat_st_02 = sprintf("%04d", $bat_stcount);
            $bat_content .= "ST*837*" . $bat_st_02;
            if (!empty($elems[3])) {
                $bat_content .= "*" . $elems[3];
            }

            $bat_content .= "~";
            continue;
        }

        if ($elems[0] == 'BHT') {
            // needle is set in OpenEMR\Billing\X125010837P
            $bat_content .= substr_replace($seg, '*' . $bat_icn . $bat_st_02 . '*', strpos($seg, '*0123*'), 6);
            $bat_content .= "~";
            continue;
        }

        if ($elems[0] == 'SE') {
            $bat_content .= sprintf("SE*%d*%04d~", $elems[1], $bat_stcount);
            continue;
        }

        if ($elems[0] == 'GE' || $elems[0] == 'IEA') {
            continue;
        }

        $bat_content .= $seg . '~';
    }
}

function append_claim_close()
{
    global $bat_content, $bat_stcount, $bat_gscount, $bat_icn;
    if ($bat_gscount) {
        $bat_content .= "GE*$bat_stcount*1~";
    }

    $bat_content .= "IEA*$bat_gscount*$bat_icn~";
}

function send_batch()
{
    global $bat_content, $bat_filename;
    // If a writable edi directory exists, log the batch to it.
    // I guarantee you'll be glad we did this. :-)
    $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/documents/edi/$bat_filename", 'a');
    if ($fh) {
        fwrite($fh, $bat_content);
        fclose($fh);
    }
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=$bat_filename");
    header("Content-Description: File Transfer");
    header("Content-Length: " . strlen($bat_content));
    echo $bat_content;
}

function validate_payer_reset(&$payer_id_held, $patient_id, $encounter)
{
    if ($payer_id_held > -1) {
        sqlStatement("UPDATE billing SET payer_id = ? WHERE " .
            "pid= ? AND encounter = ? AND activity = 1", array($payer_id_held, $patient_id, $encounter));
        $payer_id_held = -1;
    }
}

process_form($_POST);

function process_form($ar)
{
    global $bill_info, $bat_filename, $pdf, $template;
    global $ub04id, $validatePass;

    // Set up crypto object
    $cryptoGen = new CryptoGen();

    if (
        isset($ar['bn_x12']) || isset($ar['bn_x12_encounter']) || isset($ar['bn_process_hcfa']) || isset($ar['bn_hcfa_txt_file']) || isset($ar['bn_process_hcfa_form'])
        || isset($ar['bn_process_ub04_form']) || isset($ar['bn_process_ub04']) || isset($ar['bn_ub04_x12'])
    ) {
        if ($GLOBALS['billing_log_option'] == 1) {
            if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/edi/process_bills.log")) {
                $hlog = file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/edi/process_bills.log");
            }
            if ($cryptoGen->cryptCheckStandard($hlog)) {
                $hlog = $cryptoGen->decryptStandard($hlog, null, 'database');
            }
        } else { // ($GLOBALS['billing_log_option'] == 2)
            $hlog = '';
        }
    }


    if (isset($ar['bn_external'])) {
        // Open external billing file for output.
        $be = new BillingExport();
    }

    if (empty($ar['claims'])) {
        $ar['claims'] = array();
    }
    $bat_content = "";
    $claim_count = 0;
    foreach ($ar['claims'] as $claimid => $claim_array) {
        $ta = explode("-", $claimid);
        $patient_id = $ta[0];
        $encounter = $ta[1];
        $payer_id = substr($claim_array['payer'], 1);
        $payer_type = substr(strtoupper($claim_array['payer']), 0, 1);
        if ($payer_type == 'P') {
            $payer_type = 1;
        } elseif ($payer_type == 'S') {
            $payer_type = 2;
        } elseif ($payer_type == 'T') {
            $payer_type = 3;
        } else {
            $payer_type = 0;
        }

        if (isset($claim_array['bill'])) {
            if (isset($ar['bn_external'])) {
                // Write external claim.
                $be->addClaim($patient_id, $encounter);
            } else {
                $sql = "SELECT x.processing_format from x12_partners as x where x.id =?";
                $result = sqlQuery($sql, [$claim_array['partner']]);
                $target = "x12";
                if (!empty($result['processing_format'])) {
                    $target = $result['processing_format'];
                }
            }

            $clear_claim = isset($ar['btn-clear']);
            $validate_claim = isset($ar['btn-validate']);
            $validatePass = $validate_claim || $clear_claim;
            $payer_id_held = -1;
            $tmp = 1;
            if (!$validate_claim) {
                if ($clear_claim) {
                    $tmp = BillingUtilities::updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2); // $sql .= " billed = 1, ";
                }
                if (isset($ar['bn_x12']) || isset($ar['bn_x12_encounter']) && !$clear_claim) {
                    $tmp = BillingUtilities::updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2, 1, '', $target, $claim_array['partner']);
                } elseif (isset($ar['bn_ub04_x12'])) {
                    $ub04id = get_ub04_array($patient_id, $encounter);
                    $ub_save = json_encode($ub04id);
                    $tmp = BillingUtilities::updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2, 1, '', $target, $claim_array['partner'] . '-837I', 0, $ub_save);
                } elseif (isset($ar['bn_process_ub04_form']) || isset($ar['bn_process_ub04'])) {
                    $ub04id = get_ub04_array($patient_id, $encounter);
                    $ub_save = json_encode($ub04id);
                    $tmp = BillingUtilities::updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2, 1, '', 'ub04', -1, 0, $ub_save);
                } elseif (isset($ar['bn_process_hcfa']) || isset($ar['bn_hcfa_txt_file']) || isset($ar['bn_process_hcfa_form']) && !$clear_claim) {
                    $tmp = BillingUtilities::updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2, 1, '', 'hcfa');
                } elseif (isset($ar['bn_mark'])) {
                    // $sql .= " billed = 1, ";
                    $tmp = BillingUtilities::updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2);
                } elseif (isset($ar['bn_reopen'])) {
                    $tmp = BillingUtilities::updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 1, 0);
                } elseif (isset($ar['bn_external'])) {
                    // $sql .= " billed = 1, ";
                    $tmp = BillingUtilities::updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2);
                }
            } else {
                // so if we validate lets validate against currently set payer.
                // will reset to current payer once claim processed(below).
                $payer_id_held = sqlQueryNoLog("SELECT payer_id FROM billing WHERE " .
                    "pid= ? AND encounter = ? AND activity = 1", array($patient_id, $encounter))['payer_id'];
                sqlStatementNoLog("UPDATE billing SET payer_id = ? WHERE " .
                    "pid= ? AND encounter = ? AND activity = 1", array($payer_id, $patient_id, $encounter));
            }
            if (!$tmp) {
                die(xlt("Claim ") . text($claimid) . xlt(" update failed, not in database?"));
            } else {
                if ($validate_claim) {
                    $hlog .= xl("Validating Claim") . " " . $claimid . " " . xl("existing claim status is not altered.") . "\n";
                }
                if ($clear_claim) {
                    $hlog .= xl("Validating Claim") . " " . $claimid . " " . xl("and resetting claim status.") . "\n";
                }
                if (isset($ar['bn_mark'])) {
                    $bill_info[] = xl("Claim ") . $claimid . xl(" was marked as billed only.") . "\n";
                } elseif (isset($ar['bn_reopen'])) {
                    $bill_info[] = xl("Claim ") . $claimid . xl(" has been re-opened.") . "\n";
                } elseif (isset($ar['bn_x12']) || isset($ar['bn_x12_encounter'])) {
                    $log = '';
                    $segs = explode("~\n", X125010837P::genX12837P($patient_id, $encounter, $log, isset($ar['bn_x12_encounter'])));
                    $hlog .= $log;
                    append_claim($segs);
                    if ($validatePass) {
                        validate_payer_reset($payer_id_held, $patient_id, $encounter);
                        continue;
                    }
                    if (!BillingUtilities::updateClaim(false, $patient_id, $encounter, -1, -1, 2, 2, $bat_filename)) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_ub04_x12'])) {
                    $log = '';
                    $segs = explode("~\n", X125010837I::generateX12837I($patient_id, $encounter, $log, $ub04id));
                    $hlog .= $log;
                    append_claim($segs);
                    if ($validatePass) {
                        validate_payer_reset($payer_id_held, $patient_id, $encounter);
                        continue;
                    }
                    if (!BillingUtilities::updateClaim(false, $patient_id, $encounter, -1, -1, 2, 2, $bat_filename, 'X12-837I', -1, 0, json_encode($ub04id))) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_process_hcfa'])) {
                    $log = '';
                    $hcfa = new Hcfa1500();
                    $lines = $hcfa->genHcfa1500($patient_id, $encounter, $log);
                    $hlog .= $log;
                    $alines = explode("\014", $lines); // form feeds may separate pages
                    foreach ($alines as $tmplines) {
                        if ($claim_count++) {
                            $pdf->ezNewPage();
                        }
                        $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
                        $pdf->ezText($tmplines, 12, array(
                            'justification' => 'left',
                            'leading' => 12
                        ));
                    }
                    if ($validatePass) {
                        validate_payer_reset($payer_id_held, $patient_id, $encounter);
                        continue;
                    }
                    if (!BillingUtilities::updateClaim(false, $patient_id, $encounter, -1, -1, 2, 2, $bat_filename)) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_process_hcfa_form'])) {
                    $log = '';
                    $hcfa = new Hcfa1500();
                    $lines = $hcfa->genHcfa1500($patient_id, $encounter, $log);
                    $hcfa_image = $GLOBALS['images_static_absolute'] . "/cms1500.png";
                    $hlog .= $log;
                    $alines = explode("\014", $lines); // form feeds may separate pages
                    foreach ($alines as $tmplines) {
                        if ($claim_count++) {
                            $pdf->ezNewPage();
                        }
                        $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
                        $pdf->addPngFromFile("$hcfa_image", 0, 0, 612, 792);
                        $pdf->ezText($tmplines, 12, array(
                            'justification' => 'left',
                            'leading' => 12
                        ));
                    }
                    if ($validatePass) {
                        validate_payer_reset($payer_id_held, $patient_id, $encounter);
                        continue;
                    }
                    if (!BillingUtilities::updateClaim(false, $patient_id, $encounter, -1, -1, 2, 2, $bat_filename)) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_process_ub04_form']) || isset($ar['bn_process_ub04'])) {
                    $claim_count++;
                    $log = "";
                    $template[] = buildTemplate($patient_id, $encounter, "", "", $log);
                    $hlog .= $log;
                    if ($validatePass) {
                        validate_payer_reset($payer_id_held, $patient_id, $encounter);
                        continue;
                    }
                    if (!BillingUtilities::updateClaim(false, $patient_id, $encounter, -1, -1, 2, 2, $bat_filename, 'ub04', -1, 0, json_encode($ub04id))) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_hcfa_txt_file'])) {
                    $log = '';
                    $hcfa = new Hcfa1500();
                    $lines = $hcfa->genHcfa1500($patient_id, $encounter, $log);
                    $hlog .= $log;
                    $bat_content .= $lines;
                    if ($validatePass) {
                        validate_payer_reset($payer_id_held, $patient_id, $encounter);
                        continue;
                    }
                    if (!BillingUtilities::updateClaim(false, $patient_id, $encounter, -1, -1, 2, 2, $bat_filename)) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } else {
                    $bill_info[] = xl("Claim ") . $claimid . xl(" was queued successfully.") . "\n";
                }
            }
        } // end if this claim has billing
    } // end foreach

    if (!empty($hlog)) {
        if ($GLOBALS['drive_encryption']) {
            $hlog = $cryptoGen->encryptStandard($hlog, null, 'database');
        }
        file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/edi/process_bills.log", $hlog);
    }

    if (isset($ar['bn_process_ub04_form']) || isset($ar['bn_process_ub04'])) {
        if (isset($ar['bn_process_ub04'])) {
            $action = "noform";
        } elseif (isset($ar['bn_process_ub04_form'])) {
            $action = "form";
        }
        ub04Dispose('download', $template, $bat_filename, $action);
        exit();
    }

    if ($validatePass) {
        if (isset($ar['bn_hcfa_txt_file'])) {
            $format_bat = $bat_content;
            $wrap = "<!DOCTYPE html><html><head></head><body><div><pre>" . text($format_bat) . "</pre></div></body></html>";
            echo $wrap;
            exit();
        } elseif (isset($ar['bn_x12']) || isset($ar['bn_x12_encounter']) || isset($ar['bn_ub04_x12'])) {
            global $bat_content;
            append_claim_close();
            $format_bat = str_replace('~', PHP_EOL, $bat_content);
            $wrap = "<!DOCTYPE html><html><head></head><body><div style='overflow: hidden;'><pre>" . text($format_bat) . "</pre></div></body></html>";
            echo $wrap;
            exit();
        } else {
            $fname = tempnam($GLOBALS['temporary_files_dir'], 'PDF');
            file_put_contents($fname, $pdf->ezOutput());
            // Send the content for view.
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $bat_filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($fname));
            ob_end_clean();
            @readfile($fname);
            unlink($fname);
            exit();
        }
        die(xlt("Unknown Selection"));
    } else {
        if (isset($ar['bn_x12']) || isset($ar['bn_x12_encounter']) || isset($ar['bn_ub04_x12'])) {
            append_claim_close();
            send_batch();
            exit();
        }
        if (isset($ar['bn_process_hcfa'])) {
            // If a writable edi directory exists (and it should), write the pdf to it.
            $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/documents/edi/$bat_filename", 'a');
            if ($fh) {
                fwrite($fh, $pdf->ezOutput());
                fclose($fh);
            }
            // Send the PDF download.
            $pdf->ezStream(array(
                'Content-Disposition' => $bat_filename
            ));
            exit();
        }
        if (isset($ar['bn_process_hcfa_form'])) {
            // If a writable edi directory exists (and it should), write the pdf to it.
            $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/documents/edi/$bat_filename", 'a');
            if ($fh) {
                fwrite($fh, $pdf->ezOutput());
                fclose($fh);
            }
            // Send the PDF download.
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=$bat_filename");
            header("Content-Description: File Transfer");
            // header("Content-Length: " . strlen($bat_content));
            echo $pdf->ezOutput();

            exit();
        }
        if (isset($ar['bn_hcfa_txt_file'])) {
            $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/documents/edi/$bat_filename", 'a');
            if ($fh) {
                fwrite($fh, $bat_content);
                fclose($fh);
            }
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=$bat_filename");
            header("Content-Description: File Transfer");
            header("Content-Length: " . strlen($bat_content));
            echo $bat_content;
            exit();
        }
        if (isset($ar['bn_external'])) {
            // Close external billing file.
            $be->close();
        }
    }
}

?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <script>
        $(function () {
            $("#close-link").click(function () {
                window.close();
            });
        });
    </script>
</head>
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h3><?php echo xlt('Billing queue results'); ?>:</h3>
                <ul>
                    <li>
                        <?php
                        foreach ($bill_info as $infoline) {
                            echo nl2br($infoline);
                        }
                        ?>
                    </li>
                </ul>
                <button class="btn btn-secondary btn-sm btn-cancel" id="close-link">
                    <?php echo xlt('Close'); ?>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
