<?php

/*
 * The functions of this class support the billing process like the script billing_process.php.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (C) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Billing\ParseERA;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\OeUI\OemrUI;

if (!AclMain::aclCheckCore('acct', 'bill', '', 'write') && !AclMain::aclCheckCore('acct', 'eob', '', 'write')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for acct/bill or acct/eob: ERA Posting", xl("ERA Posting"));
}

$hidden_type_code = $_POST['hidden_type_code'] ?? '';
$check_date = $_POST['check_date'] ?? '';
$post_to_date = $_POST['post_to_date'] ?? '';
$deposit_date = $_POST['deposit_date'] ?? '';
$type_code = $_POST['type_code'] ?? '';
$confirm_overwrite = is_string($_POST['confirm_overwrite'] ?? null) ? $_POST['confirm_overwrite'] : '';
$pending_era_temp = is_string($_POST['pending_era_temp'] ?? null) ? $_POST['pending_era_temp'] : '';
$pending_eraname = is_string($_POST['pending_eraname'] ?? null) ? $_POST['pending_eraname'] : '';
$showOverwriteConfirm = false;

//===============================================================================
// This is called back by ParseERA::parseERA() if we are processing X12 835's.
$alertmsg = '';
$where = '';
$eraname = '';
$eracount = 0;
$Processed = 0;
/**
 * @param array $out
 */
function era_payments_callback(array &$out): void
{
    global $where, $eracount, $eraname;
    ++$eracount;
    $eraname = $out['gs_date'] . '_' . ltrim((string) $out['isa_control_number'], '0') .
    '_' . ltrim((string) $out['payer_id'], '0');
    [$pid, $encounter, $invnumber] = SLEOB::slInvoiceNumber($out);
    if ($pid && $encounter) {
        if ($where) {
            $where .= ' OR ';
        }
        $where .= "( f.pid = '" . add_escape_custom($pid) . "' AND f.encounter = '" . add_escape_custom($encounter) . "' )";
    }
}
//===============================================================================
// Validate pending_eraname matches expected pattern (YYYYMMDD_controlnum_payerid)
// to prevent path traversal. Only alphanumeric and underscores are allowed.
$validEraName = $pending_eraname !== '' && preg_match('/^[0-9]{8}_[0-9]+_[0-9]+$/', $pending_eraname) === 1;

// Handle confirmed overwrite from pending upload
if ($confirm_overwrite === 'yes' && $validEraName) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $eraDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . "/documents/era";
    // basename() strips path components; realpath() resolves symlinks and verifies existence
    $safeName = basename($pending_eraname);
    $expectedTempFile = "$eraDir/.pending_$safeName.edi";
    $realTempFile = realpath($expectedTempFile);
    $realEraDir = realpath($eraDir);
    // Verify file exists and is within the ERA directory
    if ($realTempFile !== false && $realEraDir !== false
        && str_starts_with($realTempFile, $realEraDir . DIRECTORY_SEPARATOR)
    ) {
        $eraname = $safeName;
        $erafullname = "$eraDir/$safeName.edi";
        rename($realTempFile, $erafullname);
        $alertmsg .= xl("File overwritten successfully.") . ' ';
    }
} elseif ($confirm_overwrite === 'no' && $validEraName) {
    // User cancelled - clean up temp file
    $eraDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . "/documents/era";
    // basename() strips path components; realpath() resolves symlinks and verifies existence
    $safeName = basename($pending_eraname);
    $expectedTempFile = "$eraDir/.pending_$safeName.edi";
    $realTempFile = realpath($expectedTempFile);
    $realEraDir = realpath($eraDir);
    // Verify file exists and is within the ERA directory
    if ($realTempFile !== false && $realEraDir !== false
        && str_starts_with($realTempFile, $realEraDir . DIRECTORY_SEPARATOR)
    ) {
        unlink($realTempFile);
    }
    $alertmsg .= xl("Upload cancelled.") . ' ';
}
//===============================================================================
  // Handle X12 835 file upload.
elseif (!empty($_FILES['form_erafile']['size'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $tmp_name = $_FILES['form_erafile']['tmp_name'] ?? null;
    if (!is_string($tmp_name)) {
        $alertmsg .= xl("Invalid file upload") . " ";
    } else {
        // Handle .zip extension if present.  Probably won't work on Windows.
        if (strtolower(substr((string) $_FILES['form_erafile']['name'], -4)) == '.zip') {
            rename($tmp_name, "$tmp_name.zip");
            exec("unzip -p " . escapeshellarg($tmp_name . ".zip") . " > " . escapeshellarg($tmp_name));
            unlink("$tmp_name.zip");
        }
        $alertmsg .= ParseERA::parseERA($tmp_name, 'era_payments_callback');

        // Ensure the ERA directory exists
        $eraDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . "/documents/era";
        if (!is_dir($eraDir) && !mkdir($eraDir, 0755, true) && !is_dir($eraDir)) {
            $alertmsg .= xl("Cannot create ERA directory") . " ";
        }

        $erafullname = "$eraDir/$eraname.edi";
        if (is_file($erafullname)) {
            // File exists - ask user for confirmation before overwriting
            // Move temp file to a persistent temp location so it survives the request
            $pending_era_temp = "$eraDir/.pending_$eraname.edi";
            rename($tmp_name, $pending_era_temp);
            $pending_eraname = $eraname;
            $showOverwriteConfirm = true;

            if (is_file("$eraDir/$eraname.html")) {
                $Processed = 1;
            }
        } else {
            // File doesn't exist - proceed normally
            rename($tmp_name, $erafullname);
        }
    } // end is_string($tmp_name)
} // End 835 upload
//===============================================================================

//===============================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'common']);?>
    <?php require_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
    <script>
    function Validate()
    {
     if(document.getElementById('uploadedfile').value=='')
      {
       alert(<?php echo xlj('Please Choose a file');?>);
       return false;
      }
     if(document.getElementById('hidden_type_code').value=='')
      {
       alert(<?php echo xlj('Select Insurance, by typing'); ?>);
       document.getElementById('type_code').focus();
       return false;
      }
     if(document.getElementById('hidden_type_code').value!=document.getElementById('div_insurance_or_patient').innerHTML)
      {
       alert(<?php echo xlj('Take Insurance, from Drop Down'); ?>);
       document.getElementById('type_code').focus();
       return false;
      }
       top.restoreSession();
       document.forms[0].submit();
    }
    function OnloadAction()
    {//Displays message after upload action,and popups the details.
     after_value=document.getElementById('after_value').value;
     if(after_value!='')
      {
       alert(after_value);
      }
        <?php
        // Only open the processing popup if:
        // 1. A new file was uploaded and doesn't need confirmation, OR
        // 2. User confirmed the overwrite
        $shouldOpenPopup = (!empty($_FILES['form_erafile']['size']) && !$showOverwriteConfirm)
            || ($confirm_overwrite === 'yes' && $pending_eraname);
        if ($shouldOpenPopup) {
            ?>
            var f = document.forms[0];
            var debug = <?php echo js_escape(($_REQUEST['form_without'] ?? null) * 1); ?> ;
         var paydate = f.check_date.value;
         var post_to_date = f.post_to_date.value;
         var deposit_date = f.deposit_date.value;
         // AI-generated code (GitHub Copilot) - Refactored to use URLSearchParams
         const params = new URLSearchParams({
             eraname: <?php echo js_escape($eraname); ?>,
             debug: debug,
             paydate: paydate,
             post_to_date: post_to_date,
             deposit_date: deposit_date,
             original: 'original',
             InsId: <?php echo js_escape($hidden_type_code); ?>,
             csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
         });
         window.open('sl_eob_process.php?' + params.toString(), '_blank');
         return false;
            <?php
        }
        ?>
    }

    $(function () {
       $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
       });
    });
    </script>
    <script>
    document.onclick=HideTheAjaxDivs;
    </script>
    <style>
    #ajax_div_insurance {
        position: absolute;
        z-index: 10;
        background-color: #FBFDD0;
        border: 1px solid var(--gray);
        padding: 10px;
    }
    .bottom {
        border-bottom:1px solid var(--black);
    }
    .top {
        border-top:1px solid var(--black);
    }
    .left {
        border-left:1px solid var(--black);
    }
    .right {
        border-right:1px solid var(--black);
    }
    @media only screen and (max-width: 768px) {
        [class*="col-"] {
            width: 100%;
            text-align: left !important;
        }
    }


    @media only screen and (max-width: 700px) {
        [class*="col-"] {
        width: 100%;
        text-align: left !important;
        }
        #form_without {
        margin-left: 0px !important;
        }

    }
    .input-group .form-control {
        margin-bottom: 3px;
        margin-left: 0px;
    }
    #form_without {
        margin-left: 5px !important;
    }
    </style>
    <?php
    //to determine and set the form to open in the desired state - expanded or centered, any selection the user makes will
    //become the user-specific default for that page. collectAndOrganizeExpandSetting() contains a single array as an
    //argument, containing one or more elements, the name of the current file is the first element, if there are linked
    // files they should be listed thereafter, please add _xpd suffix to the file name
    $arr_files_php = ["era_payments_xpd", "search_payments_xpd", "new_payment_xpd"];
    $current_state = collectAndOrganizeExpandSetting($arr_files_php);
    require_once("$srcdir/expand_contract_inc.php");
    ?>
    <title><?php echo xlt('ERA Posting'); ?></title>
    <?php
    $arrOeUiSettings = [
        'heading_title' => xl('Payments'),
        'include_patient_name' => false,// use only in appropriate pages
        'expandable' => true,
        'expandable_files' => ["era_payments_xpd", "search_payments_xpd", "new_payment_xpd"],//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => false,
        'help_file_name' => ""
    ];
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>
<body onload="OnloadAction()">
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer());?> mt-3">
        <div class="row">
            <div class="col-sm-12">
                <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
            </div>
        </div>
        <nav class="navbar navbar-nav navbar-expand-md navbar-light text-body bg-light mb-4 p-4">
            <button class="navbar-toggler icon-bar" data-target="#myNavbar" data-toggle="collapse" type="button"> <span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" href='new_payment.php'><?php echo xlt('New Payment'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" href='search_payments.php'><?php echo xlt('Search Payment'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" href='era_payments.php'><?php echo xlt('ERA Posting'); ?></a>
                    </li>
                </ul>
            </div>
        </nav>
        <div class="row">
            <div class="col-sm-12">
                <form action='era_payments.php' enctype="multipart/form-data" method='post' style="display:inline">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <fieldset>
                        <div class="jumbotron py-4">
                            <div class="row h3">
                                <?php echo xlt('ERA Posting'); ?>
                            </div>
                            <div class="row">
                                <div class="form-group col-12 oe-file-div">
                                    <div class="input-group">
                                        <label class="input-group-prepend">
                                            <span class="btn btn-secondary">
                                                <?php echo xlt('Browse'); ?>&hellip;<input type="file" id="uploadedfile" name="form_erafile" style="display: none;" />
                                                <input name="MAX_FILE_SIZE" type="hidden" value="5000000" />
                                            </span>
                                        </label>
                                        <input type="text" class="form-control" placeholder="<?php echo xla('Click Browse and select one Electronic Remittance Advice (ERA) file...'); ?>" readonly />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-3">
                                    <label class="control-label" for="check_date"><?php echo xlt('Date'); ?>:</label>
                                    <input class="form-control datepicker" id='check_date' name='check_date' onkeydown="PreventIt(event)" type='text' value="<?php echo attr($check_date); ?>" />
                                </div>
                                <div class="form-group col-3">
                                    <label class="control-label" for="post_to_date"><?php echo xlt('Post To Date'); ?>:</label>
                                    <input class="form-control datepicker" id='post_to_date' name='post_to_date' onkeydown="PreventIt(event)" type='text' value="<?php echo attr($post_to_date); ?>" />
                                </div>
                                <div class="form-group col-3 clearfix">
                                    <label class="control-label" for="form_without"><?php echo xlt('Select'); ?>:</label>
                                    <label class="checkbox">
                                        <input name='form_without'  id='form_without' type='checkbox' value='1' />
                                        <span class="oe-ckbox-label"><?php echo xlt('Without Update'); ?></span>
                                    </label>
                                </div>
                                <div class="form-group col-3">
                                    <label class="control-label" for="deposit_date"><?php echo xlt('Deposit Date'); ?>:</label>
                                    <input class="form-control datepicker" id='deposit_date' name='deposit_date' onkeydown="PreventIt(event)" type='text' value="<?php echo attr($deposit_date); ?>" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-6">
                                    <label class="control-label" for="type_code"><?php echo xlt('Insurance'); ?>:</label>
                                    <input id="hidden_ajax_close_value" type="hidden" value="<?php echo attr($type_code); ?>" />
                                    <input autocomplete="off" class="form-control" id='type_code' name='type_code' onkeydown="PreventIt(event)"  type="text" value="<?php echo attr($type_code); ?>" />
                                    <br />
                                    <!--onKeyUp="ajaxFunction(event,'non','search_payments.php');"-->
                                    <div id='ajax_div_insurance_section'>
                                        <div id='ajax_div_insurance_error'></div>
                                        <div id="ajax_div_insurance" style="display:none;"></div>
                                    </div>
                                </div>
                                <div class="form-group col-6">
                                    <label class="control-label" for="div_insurance_or_patient"><?php echo xlt('Insurance ID'); ?>:</label>
                                    <div class="form-control" id="div_insurance_or_patient" >
                                        <?php echo text($hidden_type_code); ?>
                                    </div>
                                    <input id="description" name="description" type="hidden" />
                                </div>
                            </div>
                            <!-- can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets -->
                            <div class="form-group mt-3">
                                <div class="col-sm-12 text-left position-override">
                                    <div class="btn-group" role="group">
                                        <a class="btn btn-primary btn-save" href="#" onclick="javascript:return Validate();"><?php echo xlt('Process ERA File');?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <input type="hidden" name="after_value" id="after_value" value="<?php echo attr($alertmsg); ?>" />
                    <input type="hidden" name="hidden_type_code" id="hidden_type_code" value="<?php echo attr($hidden_type_code); ?>" />
                    <input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
                    <input type="hidden" name="confirm_overwrite" id="confirm_overwrite" value="" />
                    <input type="hidden" name="pending_era_temp" id="pending_era_temp" value="<?php echo attr($pending_era_temp); ?>" />
                    <input type="hidden" name="pending_eraname" id="pending_eraname" value="<?php echo attr($pending_eraname); ?>" />
                </form>
            </div>
        </div>
    </div><!-- End of Container Div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
    <script src = '<?php echo $webroot;?>/library/js/oeUI/oeFileUploads.js'></script>

    <!-- Overwrite Confirmation Modal -->
    <div class="modal fade" id="overwriteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="overwriteConfirmModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="overwriteConfirmModalLabel"><?php echo xlt('File Already Exists'); ?></h5>
                </div>
                <div class="modal-body">
                    <p><?php echo xlt('An ERA file with the name'); ?> <strong><?php echo text($pending_eraname); ?></strong> <?php echo xlt('has already been uploaded.'); ?></p>
                    <?php if ($Processed) { ?>
                        <p class="text-warning"><strong><?php echo xlt('Warning'); ?>:</strong> <?php echo xlt('This file has already been processed.'); ?></p>
                    <?php } else { ?>
                        <p class="text-info"><?php echo xlt('This file has not yet been processed.'); ?></p>
                    <?php } ?>
                    <p><?php echo xlt('Do you want to overwrite the existing file?'); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cancelOverwrite();"><?php echo xlt('Cancel'); ?></button>
                    <button type="button" class="btn btn-danger" onclick="confirmOverwrite();"><?php echo xlt('Overwrite'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmOverwrite() {
        document.getElementById('confirm_overwrite').value = 'yes';
        document.forms[0].submit();
    }
    function cancelOverwrite() {
        document.getElementById('confirm_overwrite').value = 'no';
        document.forms[0].submit();
    }
    <?php if ($showOverwriteConfirm) { ?>
    $(document).ready(function() {
        $('#overwriteConfirmModal').modal('show');
    });
    <?php } ?>
    </script>
</body>
</html>
