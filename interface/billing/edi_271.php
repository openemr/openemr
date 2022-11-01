<?php

/**
 * Functions to globally validate and prepare data for sql database insertion.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MMF Systems, Inc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2010 MMF Systems, Inc
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__file__) . "/../globals.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/report.inc.php");
require_once("$srcdir/calendar.inc.php");

use OpenEMR\Billing\EDI270;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

//  File location (URL or server path)
$target = $GLOBALS['edi_271_file_path'];
$batch_log = '';

if (isset($_FILES) && !empty($_FILES)) {
    $target = $target . time() . basename($_FILES['uploaded']['name']);

    if ($_FILES['uploaded']['size'] > 350000) {
        $message .=  xlt('Your file is too large') . "<br />";
    }
    if (mime_content_type($_FILES['uploaded']['tmp_name']) != "text/plain") {
        $message .= xlt('You may only upload .txt files') . "<br />";
    }
    if (preg_match("/(.*)\.(inc|php|php7|php8)$/i", $_FILES['uploaded']['name']) !== 0) {
        $message .= xlt('Invalid file type.') . "<br />";
    }
    if (!isset($message)) {
        $cryptoGen = new CryptoGen();
        $uploadedFile = file_get_contents($_FILES['uploaded']['tmp_name']);
        if ($GLOBALS['drive_encryption']) {
            $uploadedFile = $cryptoGen->encryptStandard($uploadedFile, null, 'database');
        }
        if (file_put_contents($target, $uploadedFile)) {
            $message = xlt('The following EDI file has been uploaded') . ': "' . text(basename($_FILES['uploaded']['name'])) . '"';
            $Response271 = file_get_contents($target);
            if ($cryptoGen->cryptCheckStandard($Response271)) {
                $Response271 = $cryptoGen->decryptStandard($Response271, null, 'database');
            }
            if ($Response271) {
                $batch_log = EDI270::parseEdi271($Response271);
            } else {
                $message = xlt('The following EDI file upload failed to open') . ': "' . text(basename($_FILES['uploaded']['name'])) . '"';
            }
        } else {
            $message = xlt('The following EDI file failed save to archive') . ': "' . text(basename($_FILES['uploaded']['name'])) . '"';
        }
    } else {
        $message .= xlt('Sorry, there was a problem uploading your file') . "<br /><br />";
    }
}
if ($batch_log && !$GLOBALS['disable_eligibility_log']) {
    $fn = sprintf(
        'elig-batch_log_%s.txt',
        date("Y-m-d:H:i:s")
    );
    $batch_log = str_replace('~', "~\r", $batch_log);
    while (@ob_end_flush()) {
    }
    header('Content-Type: text/plain');
    header("Content-Length: " . strlen($batch_log));
    header('Content-Disposition: attachment; filename="' . $fn . '"');
    ob_start();
    echo $batch_log;
    exit();
}
?>
<html>
<head>
<title><?php echo xlt('EDI-271 Response File Upload'); ?></title>

<?php Header::setupHeader(); ?>

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
<script>
    function edivalidation() {
        var mypcc = <?php echo xlj('Required Field Missing: Please choose the EDI-271 file to upload'); ?>;
        if (document.getElementById('uploaded').value == "") {
            alert(mypcc);
            return false;
        } else {
            $("#theform").trigger("submit");
        }
    }
</script>
</head>
<body class="body_top">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
    <?php if (isset($message) && !empty($message)) { ?>
                <div class="text-danger text-center bg-light w-50" style="margin-left:25%; font-family: 'Arial', sans-serif; font-size:15px; border:1px solid;"><?php echo $message; ?></div>
        <?php
                $message = "";
    }
    if (isset($messageEDI)) { ?>
    <div class="text-danger text-center bg-light w-50" style="margin-left:25%; font-family: 'Arial', sans-serif; font-size:15px; border:1px solid;">
            <?php echo xlt('Please choose the proper formatted EDI-271 file'); ?>
    </div>
        <?php
        $messageEDI = "";
    } ?>
<div>
<span class='title'><?php echo xlt('EDI-271 File Upload'); ?></span>
<form enctype="multipart/form-data" name="theform" id="theform" action="edi_271.php" method="POST" onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
    <table>
        <tr>
            <td width='550px'>
                <div class='float-left'>
                    <table class='text'>
                        <tr>
                            <td style='width:125px;' class='label_custom'> <?php echo xlt('Select EDI-271 file'); ?>:   </td>
                            <td> <input name="uploaded" id="uploaded" type="file" size="37" /></td>
                        </tr>
                    </table>
                </div>
            </td>
            <td align='left' valign='middle' height="100%">
                <table class='w-100 h-100' style='border-left:1px solid;'>
                    <tr>
                        <td>
                            <div style='margin-left:15px'>
                                <a href='#' class='btn btn-primary' onclick='return edivalidation(); '><span><?php echo xlt('Upload'); ?></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby ?? ''); ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
</form>
</body>
</html>
