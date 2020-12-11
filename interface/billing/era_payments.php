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
 * @copyright Copyright (c) Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (C) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once($GLOBALS['OE_SITE_DIR'] . "/statement.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Billing\ParseERA;
use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

$hidden_type_code = isset($_POST['hidden_type_code']) ? $_POST['hidden_type_code'] : '';
$check_date = isset($_POST['check_date']) ? $_POST['check_date'] : '';
$post_to_date = isset($_POST['post_to_date']) ? $_POST['post_to_date'] : '';
$deposit_date = isset($_POST['deposit_date']) ? $_POST['deposit_date'] : '';
$type_code = isset($_POST['type_code']) ? $_POST['type_code'] : '';

//===============================================================================
// This is called back by ParseERA::parseERA() if we are processing X12 835's.
$alertmsg = '';
$where = '';
$eraname = '';
$eracount = 0;
$Processed = 0;
function era_callback(&$out)
{
    global $where, $eracount, $eraname;
    ++$eracount;
    $eraname = $out['gs_date'] . '_' . ltrim($out['isa_control_number'], '0') .
    '_' . ltrim($out['payer_id'], '0');
    list($pid, $encounter, $invnumber) = SLEOB::slInvoiceNumber($out);
    if ($pid && $encounter) {
        if ($where) {
            $where .= ' OR ';
        }
        $where .= "( f.pid = '" . add_escape_custom($pid) . "' AND f.encounter = '" . add_escape_custom($encounter) . "' )";
    }
}
//===============================================================================
  // Handle X12 835 file upload.
if (!empty($_FILES['form_erafile']['size'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $tmp_name = $_FILES['form_erafile']['tmp_name'];
    // Handle .zip extension if present.  Probably won't work on Windows.
    if (strtolower(substr($_FILES['form_erafile']['name'], -4)) == '.zip') {
        rename($tmp_name, "$tmp_name.zip");
        exec("unzip -p " . escapeshellarg($tmp_name . ".zip") . " > " . escapeshellarg($tmp_name));
        unlink("$tmp_name.zip");
    }
    $alertmsg .= ParseERA::parseERA($tmp_name, 'era_callback');
    $erafullname = $GLOBALS['OE_SITE_DIR'] . "/documents/era/$eraname.edi";
    if (is_file($erafullname)) {
        $alertmsg .=  xl("Warning") . ': ' . xl("Set") . ' ' . $eraname . ' ' . xl("was already uploaded") . ' ';
        if (is_file($GLOBALS['OE_SITE_DIR'] . "/documents/era/$eraname.html")) {
            $Processed = 1;
            $alertmsg .=  xl("and processed.") . ' ';
        } else {
            $alertmsg .=  xl("but not yet processed.") . ' ';
        };
    }
    rename($tmp_name, $erafullname);
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
        if (!empty($_FILES['form_erafile']['size'])) {
            ?>
            var f = document.forms[0];
            var debug = <?php echo js_escape(($_REQUEST['form_without'] ?? null) * 1); ?> ;
         var paydate = f.check_date.value;
         var post_to_date = f.post_to_date.value;
         var deposit_date = f.deposit_date.value;
         window.open('sl_eob_process.php?eraname=' + <?php echo js_url($eraname); ?> + '&debug=' + encodeURIComponent(debug) + '&paydate=' + encodeURIComponent(paydate) + '&post_to_date=' + encodeURIComponent(post_to_date) + '&deposit_date=' + encodeURIComponent(deposit_date) + '&original=original' + '&InsId=' + <?php echo js_url($hidden_type_code); ?> + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank');
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
    $arr_files_php = array("era_payments_xpd", "search_payments_xpd", "new_payment_xpd");
    $current_state = collectAndOrganizeExpandSetting($arr_files_php);
    require_once("$srcdir/expand_contract_inc.php");
    ?>
    <title><?php echo xlt('ERA Posting'); ?></title>
    <?php
    $arrOeUiSettings = array(
        'heading_title' => xl('Payments'),
        'include_patient_name' => false,// use only in appropriate pages
        'expandable' => true,
        'expandable_files' => array("era_payments_xpd", "search_payments_xpd", "new_payment_xpd"),//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => false,
        'help_file_name' => ""
    );
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
                </form>
            </div>
        </div>
    </div><!-- End of Container Div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
    <script src = '<?php echo $webroot;?>/library/js/oeUI/oeFileUploads.js'></script>
</body>
</html>
