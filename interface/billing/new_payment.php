<?php

/**
 * This screen handles the cash/cheque entry and its distribution to various charges.
 *
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
require_once("$srcdir/auth.inc");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/payment.inc.php");

use OpenEMR\Billing\ParseERA;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

//===============================================================================
    $screen = 'new_payment';
//===============================================================================
// Initialisations
$mode                    = isset($_POST['mode'])                   ? $_POST['mode']                   : '';
$payment_id              = isset($_REQUEST['payment_id'])          ? $_REQUEST['payment_id'] + 0      : 0;
$request_payment_id      = $payment_id ;
$hidden_patient_code     = isset($_REQUEST['hidden_patient_code']) ? $_REQUEST['hidden_patient_code'] : '';
$default_search_patient  = isset($_POST['default_search_patient']) ? $_POST['default_search_patient'] : '';
$hidden_type_code        = isset($_REQUEST['hidden_type_code']) ? $_REQUEST['hidden_type_code'] : '';
//===============================================================================
//ar_session addition code
//===============================================================================

if ($mode == "new_payment" || $mode == "distribute") {
    if (trim($_POST['type_name']) == 'insurance') {
        $QueryPart = "payer_id = '" . add_escape_custom($hidden_type_code) . "', patient_id = '0" ;
    } elseif (trim($_POST['type_name']) == 'patient') {
        $QueryPart = "payer_id = '0', patient_id = '" . add_escape_custom($hidden_type_code);
    }
      $user_id = $_SESSION['authUserID'];
      $closed = 0;
      $modified_time = date('Y-m-d H:i:s');
      $check_date = DateToYYYYMMDD(formData('check_date'));
      $deposit_date = DateToYYYYMMDD(formData('deposit_date'));
      $post_to_date = DateToYYYYMMDD(formData('post_to_date'));
    if ($post_to_date == '') {
        $post_to_date = date('Y-m-d');
    }
    if ($_POST['deposit_date'] == '') {
        $deposit_date = $post_to_date;
    }
      $payment_id = sqlInsert("insert into ar_session set "    .
        $QueryPart .
        "', user_id = '"     . trim(add_escape_custom($user_id))  .
        "', closed = '"      . trim(add_escape_custom($closed))  .
        "', reference = '"   . trim(formData('check_number')) .
        "', check_date = '"  . trim(add_escape_custom($check_date)) .
        "', deposit_date = '" . trim(add_escape_custom($deposit_date))  .
        "', pay_total = '"    . trim(formData('payment_amount')) .
        "', modified_time = '" . trim(add_escape_custom($modified_time))  .
        "', payment_type = '"   . trim(formData('type_name')) .
        "', description = '"   . trim(formData('description')) .
        "', adjustment_code = '"   . trim(formData('adjustment_code')) .
        "', post_to_date = '" . trim(add_escape_custom($post_to_date))  .
        "', payment_method = '"   . trim(formData('payment_method')) .
        "'");
}

//===============================================================================
//ar_activity addition code
//===============================================================================
if ($mode == "PostPayments" || $mode == "FinishPayments") {
    $user_id = $_SESSION['authUserID'];
    $created_time = date('Y-m-d H:i:s');
    for ($CountRow = 1;; $CountRow++) {
        if (isset($_POST["HiddenEncounter$CountRow"])) {
            DistributionInsert($CountRow, $created_time, $user_id);
        } else {
            break;
        }
    }
    if ($_REQUEST['global_amount'] == 'yes') {
        sqlStatement("update ar_session set global_amount=? where session_id =?", [(isset($_POST["HidUnappliedAmount"]) ? trim($_POST["HidUnappliedAmount"]) : ''), $payment_id]);
    }
    if ($mode == "FinishPayments") {
        // @todo This is not useful. Gonna let fall through to form init.
        header("Location: edit_payment.php?payment_id=" . urlencode($payment_id) . "&ParentPage=new_payment");
        die();
    }
    $mode = "search";
    $_POST['mode'] = $mode;
}

//==============================================================================
//===============================================================================
$payment_id = $payment_id * 1 > 0 ? $payment_id + 0 : $request_payment_id + 0;
//===============================================================================

//==============================================================================
//===============================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['common', 'datetime-picker']);?>


    <script>
        var mypcc = '1';
    </script>
    <?php include_once("{$GLOBALS['srcdir']}/payment_jav.inc.php"); ?>
    <?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
    <script>
        function CancelDistribute() {
            // Used in the cancel button.Helpful while cancelling the distribution.
            if (confirm(<?php echo xlj('Would you like to Cancel Distribution for this Patient?') ?>)) {
                document.getElementById('hidden_patient_code').value='';
                document.getElementById('mode').value='search';
                top.restoreSession();
                document.forms[0].submit();
            } else {
                return false;
            }
        }

        function PostPayments() {
            // Used in saving the allocation
            if (CompletlyBlank()) {
                // Checks whether any of the allocation row is filled.
                alert(<?php echo xlj('Fill the Row.'); ?>);
                return false;
            }
            if (!CheckPayingEntityAndDistributionPostFor()) {
                // Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
                return false;
            }
            PostValue = CheckUnappliedAmount();
            // Decides TdUnappliedAmount >0, or <0 or =0
            if (PostValue == 1) {
                alert(<?php echo xlj('Cannot Post Payments.Undistributed is Negative.'); ?>);
                return false;
            }
            if (confirm(<?php echo xlj('Would you like to Post Payments?'); ?>)) {
                document.getElementById('mode').value='PostPayments';
                top.restoreSession();
                document.forms[0].submit();
            } else {
                return false;
            }
        }

        function FinishPayments() {
            // Used in finishig the allocation.Usually done when the amount gets reduced to zero.
            // After this is pressed a confirmation screen comes,where you can edit if needed.
            // Checks whether any of the allocation row is filled.
            if (CompletlyBlank()) {
                alert(<?php echo xlj('Fill the Row.'); ?>);
                return false;
            }
            // Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
            if (!CheckPayingEntityAndDistributionPostFor()){
                return false;
            }
            PostValue = CheckUnappliedAmount();
            // Decides TdUnappliedAmount >0, or <0 or =0
            if (PostValue == 1) {
                alert(<?php echo xlj('Cannot Post Payments.Undistributed is Negative.'); ?>);
                return false;
            }

            if (PostValue == 2) {
                if (confirm(<?php echo xlj('Would you like to Post and Finish Payments?'); ?>)) {
                UnappliedAmount = document.getElementById('TdUnappliedAmount').innerHTML*1;
                if(confirm(<?php echo xlj('Undistributed is'); ?> + ' ' + UnappliedAmount +  '.' + '\n' + <?php echo xlj('Would you like the balance amount to apply to Global Account?'); ?>)) {
                    document.getElementById('mode').value='FinishPayments';
                    document.getElementById('global_amount').value='yes';
                    top.restoreSession();
                    document.forms[0].submit();
                } else {
                    document.getElementById('mode').value='FinishPayments';
                    top.restoreSession();
                    document.forms[0].submit();
                }
                } else {
                return false;
               }
            } else {
                if (confirm(<?php echo xlj('Would you like to Post and Finish Payments?'); ?>)) {
                    document.getElementById('mode').value='FinishPayments';
                    top.restoreSession();
                    document.forms[0].submit();
                } else {
                    return false;
                }
            }
        }

        function CompletlyBlank() {
            // Checks whether any of the allocation row is filled.
            for (RowCount = 1;;RowCount++) {
                if(!document.getElementById('Payment'+RowCount)) {
                    break;
                } else {
                    if(document.getElementById('Allowed'+RowCount).value==''
                        && document.getElementById('Payment'+RowCount).value==''
                        && document.getElementById('AdjAmount'+RowCount).value==''
                        && document.getElementById('Deductible'+RowCount).value==''
                        && document.getElementById('Takeback'+RowCount).value==''
                        && document.getElementById('FollowUp'+RowCount).checked==false) {

                    } else {
                        return false;
                    }
                }
            }
            return true;
        }

        function OnloadAction() {
            // Displays message after saving to master table.
            after_value = document.getElementById("after_value").value;
            payment_id = document.getElementById('payment_id').value;
            if (after_value === 'distribute') {

            } else if (after_value=='new_payment') {
                if (document.getElementById('TablePatientPortion')) {
                    document.getElementById('TablePatientPortion').style.display = 'none';
                }
                if (confirm(<?php echo xlj('Successfully Saved.Would you like to Allocate?'); ?>)) {
                    if (document.getElementById('TablePatientPortion')) {
                        document.getElementById('TablePatientPortion').style.display = '';
                        document.getElementById('TablePatientPortion').scrollIntoView(false);
                    }
                }
            }
        }

        function ResetForm() {
            // Resets form used in the 'Cancel Changes' button in the master screen.
            document.forms[0].reset();
            document.getElementById('TdUnappliedAmount').innerHTML = '0.00';
            document.getElementById('div_insurance_or_patient').innerHTML = '&nbsp;';
            CheckVisible('yes'); // Payment Method is made 'Check Payment' and the Check box is made visible.
            PayingEntityAction(); // Paying Entity is made 'insurance' and Payment Category is 'Insurance Payment'
        }

        function FillUnappliedAmount(){
            // Filling the amount
            document.getElementById('TdUnappliedAmount').innerHTML = document.getElementById('payment_amount').value;
        }

        $(function () {
            $('.datepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = false; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });

            document.getElementById('payment_amount').addEventListener('focus', (event) => {
                event.target.select();
            });
        });

        document.onclick = HideTheAjaxDivs;
    </script>
    <style>
        .class1 {
            width: 125px;
        }
        .amt_input {
            max-width: 75px;
        }
    </style>
    <title><?php echo xlt('New Payment'); ?></title>
    <?php
    $arrOeUiSettings = array(
        'heading_title' => xl('Payments'),
        'include_patient_name' => false,// use only in appropriate pages
        'expandable' => true,
        'expandable_files' => array("new_payment_xpd", "search_payments_xpd", "era_payments_xpd"),//all file names need suffix _xpd
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
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?> mt-3">
        <div class="row">
            <div class="col-sm-12">
                <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
            </div>
        </div>
        <nav class="navbar navbar-nav navbar-expand-md navbar-light text-body bg-light mb-4 p-2">
            <button class="navbar-toggler icon-bar" data-target="#myNavbar" data-toggle="collapse" type="button"> <span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" href='new_payment.php'><?php echo xlt('New Payment'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" href='search_payments.php'><?php echo xlt('Search Payment'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" href='era_payments.php'><?php echo xlt('ERA Posting'); ?></a>
                    </li>
                </ul>
            </div>
        </nav>
            <div class="col-sm-12">
                <form action="new_payment.php" id="new_payment" method='post' name='new_payment' onsubmit="
                <?php
                if ($payment_id * 1 == 0) {
                    echo 'top.restoreSession();';
                } else {
                    echo 'return false;';
                }?>" style="display:inline">

                    <fieldset>
                        <div class="jumbotron py-4">
                        <?php
                            require_once("payment_master.inc.php"); //Check/cash details are entered here.
                        ?>
                        </div>
                        <br />
                        <?php
                        if ($payment_id * 1 > 0) {
                            ?>
                            <?php
                            if ($PaymentType == 'patient' && $default_search_patient != "default_search_patient") {
                                $default_search_patient = "default_search_patient";
                                $_POST['default_search_patient'] = $default_search_patient;
                                $hidden_patient_code = $TypeCode;
                                $_REQUEST['hidden_patient_code'] = $hidden_patient_code;
                                $_REQUEST['RadioPaid'] = 'Show_Paid';
                            }
                                require_once("payment_pat_sel.inc.php"); //Patient ajax section and listing of charges.
                            ?>
                            <?php
                            if ($CountIndexBelow > 0) {
                                ?>
                                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                            <br />
                            <div class="form-group clearfix">
                                <div class="col-sm-12 text-left position-override">
                                    <br />
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-primary btn-save" href="#" onclick="return PostPayments();"><?php echo xlt('Post Payments');?></button>
                                        <button class="btn btn-primary btn-save" href="#" onclick="return FinishPayments();"><?php echo xlt('Finish Payments');?></button>
                                        <button class="btn btn-secondary btn-cancel" href="#" onclick="CancelDistribute()"><?php echo xlt('Cancel');?></button>
                                    </div>
                                </div>
                            </div>
                                <?php
                            }//if($CountIndexBelow>0)
                            ?>
                            <?php
                        }
                        ?>
                    </fieldset>
                    <input id="hidden_patient_code" name="hidden_patient_code" type="hidden" value="<?php echo attr($hidden_patient_code);?>" />
                    <input id='mode' name='mode' type='hidden' value='' />
                    <input id='default_search_patient' name='default_search_patient' type='hidden' value='<?php echo attr($default_search_patient); ?>' />
                    <input id='ajax_mode' name='ajax_mode' type='hidden' value='' />
                    <input id="after_value" name="after_value" type="hidden" value="<?php echo attr($mode);?>" />
                    <input id="payment_id" name="payment_id" type="hidden" value="<?php echo attr($payment_id);?>" />
                    <input id="hidden_type_code" name="hidden_type_code" type="hidden" value="<?php echo attr($hidden_type_code);?>" />
                    <input id='global_amount' name='global_amount' type='hidden' value='' />
                </form>
            </div>
        <!-- end of row div -->
        <div class="clearfix">.</div>
    </div><!-- end of container div -->
    <?php $oemr_ui->oeBelowContainerDiv();?>
<script src = '<?php echo $webroot;?>/library/js/oeUI/oeFileUploads.js'></script>
<script>
$(function () {
    $('select').removeClass('class1 text');
});
</script>
</body>
</html>
