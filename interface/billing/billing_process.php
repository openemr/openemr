<?php
/*
 * Billing process Program
 *
 * This program process data for claims generation
 *
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * Copyright (C) 2014 Brady Miller <brady.g.miller@gmail.com>
 * Copyright (C) 2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-license.php.
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
include_once("../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/billrep.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/gen_x12_837.inc.php");
include_once("$srcdir/gen_hcfa_1500.inc.php");

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
// Minutes since 1/1/1970 00:00:00 GMT will be our interchange control number:
$bat_icn = sprintf('%09.0f', $bat_time / 60);
$bat_filename = date("Y-m-d-Hi", $bat_time) . "-batch.";
$bat_filename .= (isset($_POST['bn_process_hcfa']) || isset($_POST['bn_process_hcfa_form']) || isset($_POST['bn_process_ub04']) || isset($_POST['bn_process_ub04_form'])) ? 'pdf' : 'txt';
$template = array();
$ub04id = array();

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
        if (! $seg) {
            continue;
        }
        $elems = explode('*', $seg);
        if ($elems[0] == 'ISA') {
            if (! $bat_content) {
                $bat_sendid = trim($elems[6]);
                $bat_recvid = trim($elems[8]);
                $bat_sender = $GS02 ? $GS02 : $bat_sendid;
                $bat_content = substr($seg, 0, 70) . "$bat_yymmdd*$bat_hhmm*" . $elems[11] . "*" . $elems[12] . "*$bat_icn*" . $elems[14] . "*" . $elems[15] . "*:~";
            }
            continue;
        } elseif (! $bat_content) {
            die("Error:<br>\nInput must begin with 'ISA'; " . "found '" . htmlentities($elems[0]) . "' instead");
        }
        if ($elems[0] == 'GS') {
            if ($bat_gscount == 0) {
                ++ $bat_gscount;
                $bat_content .= "GS*HC*" . $elems[2] . "*" . $elems[3] . "*$bat_yyyymmdd*$bat_hhmm*1*X*" . $elems[8] . "~";
            }
            continue;
        }
        if ($elems[0] == 'ST') {
            ++ $bat_stcount;
            $bat_content .= sprintf("ST*837*%04d", $bat_stcount);
            if (! empty($elems[3])) {
                $bat_content .= "*" . $elems[3];
            }

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
    global $bat_content, $bat_filename, $webserver_root;
    // If a writable edi directory exists, log the batch to it.
    // I guarantee you'll be glad we did this. :-)
    $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/edi/$bat_filename", 'a');
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

process_form($_POST);

function process_form($ar)
{
    global $bill_info, $webserver_root, $bat_filename, $pdf, $template;
    global $ub04id;

    if (isset($ar['bn_x12']) || isset($ar['bn_x12_encounter']) || isset($ar['bn_process_hcfa']) || isset($ar['bn_hcfa_txt_file']) || isset($ar['bn_process_hcfa_form'])
        || isset($ar['bn_process_ub04_form']) || isset($ar['bn_process_ub04']) || isset($ar['bn_ub04_x12'])) {
        if ($GLOBALS['billing_log_option'] == 1) {
            $hlog = fopen($GLOBALS['OE_SITE_DIR'] . "/edi/process_bills.log", 'a');
        } else { // ($GLOBALS['billing_log_option'] == 2)
            $hlog = fopen($GLOBALS['OE_SITE_DIR'] . "/edi/process_bills.log", 'w');
        }
    }

    if (isset($ar['bn_external'])) {
        // Open external billing file for output.
        $be = new BillingExport();
    }

    $db = $GLOBALS['adodb']['db'];

    if (empty($ar['claims'])) {
        $ar['claims'] = array();
    }
    $claim_count = 0;
    foreach ($ar['claims'] as $claimid => $claim_array) {
        $ta = explode("-", $claimid);
        $patient_id = $ta[0];
        $encounter = $ta[1];
        $payer_id = substr($claim_array['payer'], 1);
        $payer_type = substr($claim_array['payer'], 0, 1);
        $payer_type = $payer_type == 'T' ? 3 : $payer_type == 'S' ? 2 : 1;

        if (isset($claim_array['bill'])) {
            if (isset($ar['bn_external'])) {
                // Write external claim.
                $be->addClaim($patient_id, $encounter);
            } else {
                $sql = "SELECT x.processing_format from x12_partners as x where x.id =" . $db->qstr($claim_array['partner']);
                $result = $db->Execute($sql);
                $target = "x12";
                if ($result && ! $result->EOF) {
                    $target = $result->fields['processing_format'];
                }
            }

            $tmp = 1;
            if (isset($ar['HiddenMarkAsCleared']) && $ar['HiddenMarkAsCleared'] == 'yes') {
                $tmp = updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2); // $sql .= " billed = 1, ";
            }
            if (isset($ar['bn_x12']) || isset($ar['bn_x12_encounter'])) {
                $tmp = updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 1, 1, '', $target, $claim_array['partner']);
            } elseif (isset($ar['bn_ub04_x12'])) {
                $ub04id = get_ub04_array($patient_id, $encounter);
                $ub_save = json_encode($ub04id);
                $tmp = updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 1, 1, '', $target, $claim_array['partner'] . '-837I', 0, $ub_save);
            } elseif (isset($ar['bn_process_ub04_form']) || isset($ar['bn_process_ub04'])) {
                $ub04id = get_ub04_array($patient_id, $encounter);
                $ub_save = json_encode($ub04id);
                $tmp = updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 1, 1, '', 'ub04', - 1, 0, $ub_save);
            } elseif (isset($ar['bn_process_hcfa']) || isset($ar['bn_hcfa_txt_file']) || isset($ar['bn_process_hcfa_form'])) {
                $tmp = updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 1, 1, '', 'hcfa');
            } elseif (isset($ar['bn_mark'])) {
                // $sql .= " billed = 1, ";
                $tmp = updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2);
            } elseif (isset($ar['bn_reopen'])) {
                $tmp = updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 1, 0);
            } elseif (isset($ar['bn_external'])) {
                // $sql .= " billed = 1, ";
                $tmp = updateClaim(true, $patient_id, $encounter, $payer_id, $payer_type, 2);
            }

            if (! $tmp) {
                die(xl("Claim ") . $claimid . xl(" update failed, not in database?"));
            } else {
                if (isset($ar['bn_mark'])) {
                    $bill_info[] = xl("Claim ") . $claimid . xl(" was marked as billed only.") . "\n";
                } elseif (isset($ar['bn_reopen'])) {
                    $bill_info[] = xl("Claim ") . $claimid . xl(" has been re-opened.") . "\n";
                } elseif (isset($ar['bn_x12']) || isset($ar['bn_x12_encounter'])) {
                    $log = '';
                    $segs = explode("~\n", gen_x12_837($patient_id, $encounter, $log, isset($ar['bn_x12_encounter'])));
                    fwrite($hlog, $log);
                    append_claim($segs);
                    if (! updateClaim(false, $patient_id, $encounter, - 1, - 1, 2, 2, $bat_filename)) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_ub04_x12'])) {
                    $log = '';
                    $segs = explode("~\n", generate_x12_837I($patient_id, $encounter, $log, $ub04id));
                    fwrite($hlog, $log);
                    append_claim($segs);
                    if (! updateClaim(false, $patient_id, $encounter, - 1, - 1, 2, 2, $bat_filename, 'X12-837I', - 1, 0, json_encode($ub04id))) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_process_hcfa'])) {
                    $log = '';
                    $lines = gen_hcfa_1500($patient_id, $encounter, $log);
                    fwrite($hlog, $log);
                    $alines = explode("\014", $lines); // form feeds may separate pages
                    foreach ($alines as $tmplines) {
                        if ($claim_count ++) {
                            $pdf->ezNewPage();
                        }
                        $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
                        $pdf->ezText($tmplines, 12, array(
                            'justification' => 'left',
                            'leading' => 12
                        ));
                    }
                    if (! updateClaim(false, $patient_id, $encounter, - 1, - 1, 2, 2, $bat_filename)) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_process_hcfa_form'])) {
                    $log = '';
                    $lines = gen_hcfa_1500($patient_id, $encounter, $log);
                    $hcfa_image = $GLOBALS['images_static_absolute'] . "/cms1500.png";
                    fwrite($hlog, $log);
                    $alines = explode("\014", $lines); // form feeds may separate pages
                    foreach ($alines as $tmplines) {
                        if ($claim_count ++) {
                            $pdf->ezNewPage();
                        }
                        $pdf->ezSetY($pdf->ez['pageHeight'] - $pdf->ez['topMargin']);
                        $pdf->addPngFromFile("$hcfa_image", 0, 0, 612, 792);
                        $pdf->ezText($tmplines, 12, array(
                            'justification' => 'left',
                            'leading' => 12
                        ));
                    }
                    if (! updateClaim(false, $patient_id, $encounter, - 1, - 1, 2, 2, $bat_filename)) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_process_ub04_form']) || isset($ar['bn_process_ub04'])) {
                    $claim_count ++;
                    $log = "";
                    $template[] = buildTemplate($patient_id, $encounter, "", "", $log);
                    fwrite($hlog, $log);
                    if (! updateClaim(false, $patient_id, $encounter, - 1, - 1, 2, 2, $bat_filename, 'ub04', - 1, 0, json_encode($ub04id))) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } elseif (isset($ar['bn_hcfa_txt_file'])) {
                    $log = '';
                    $lines = gen_hcfa_1500($patient_id, $encounter, $log);
                    fwrite($hlog, $log);
                    $bat_content .= $lines;
                    if (! updateClaim(false, $patient_id, $encounter, - 1, - 1, 2, 2, $bat_filename)) {
                        $bill_info[] = xl("Internal error: claim ") . $claimid . xl(" not found!") . "\n";
                    }
                } else {
                    $bill_info[] = xl("Claim ") . $claimid . xl(" was queued successfully.") . "\n";
                }
            }
        } // end if this claim has billing
    } // end foreach

    if (isset($ar['bn_process_ub04_form']) || isset($ar['bn_process_ub04'])) {
        if (isset($ar['bn_process_ub04'])) {
            $action = "noform";
        } elseif (isset($ar['bn_process_ub04_form'])) {
            $action = "form";
        }
        ub04Dispose('download', $template, $bat_filename, $action);
        exit();
    }
    if (isset($ar['bn_x12']) || isset($ar['bn_x12_encounter']) || isset($ar['bn_ub04_x12'])) {
        append_claim_close();
        fclose($hlog);
        send_batch();
        exit();
    }

    if (isset($ar['bn_process_hcfa'])) {
        fclose($hlog);
        // If a writable edi directory exists (and it should), write the pdf to it.
        $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/edi/$bat_filename", 'a');
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
        fclose($hlog);
        // If a writable edi directory exists (and it should), write the pdf to it.
        $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/edi/$bat_filename", 'a');
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
        fclose($hlog);
        $fh = @fopen($GLOBALS['OE_SITE_DIR'] . "/edi/$bat_filename", 'a');
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
?>
<html>
<head>
<?php if (function_exists(html_header_show)) {
    html_header_show();
}?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript"
    src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-9-1/index.js"></script>
<script>
    $(document).ready( function() {
        $("#close-link").click( function() {
            window.close();
        });
    });
</script>

</head>
<body class="body_top">
<br><p><h3><?php echo xlt('Billing queue results'); ?>:</h3><a href="#" id="close-link"><?php echo xlt('Close'); ?></a><ul>
<?php
foreach ($bill_info as $infoline) {
    echo nl2br($infoline);
}
?>
</ul></p>
</body>
</html>
