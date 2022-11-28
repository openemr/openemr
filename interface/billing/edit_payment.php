<?php

/*
 * Payments can be edited here whch includes deletion of an allocation, modifying the
 * same or adding a new allocation. Log is kept for the deleted ones.
 * The functions of this class support the billing process like the script billing_process.php.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (C) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Rod Roark <rod@sunsetsystems.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/payment.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('acct', 'bill', '', 'write') && !AclMain::aclCheckCore('acct', 'eob', '', 'write')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Confirm Payment")]);
    exit;
}

$screen = 'edit_payment';

// Deletion of payment distribution code

if (isset($_POST["mode"])) {
    if ($_POST["mode"] == "DeletePaymentDistribution") {
        $DeletePaymentDistributionId = (isset($_POST['DeletePaymentDistributionId']) ? trim($_POST['DeletePaymentDistributionId']) : '');
        $DeletePaymentDistributionIdArray = explode('_', $DeletePaymentDistributionId);
        $payment_id = $DeletePaymentDistributionIdArray[0];
        $PId = $DeletePaymentDistributionIdArray[1];
        $Encounter = $DeletePaymentDistributionIdArray[2];
        $Code = $DeletePaymentDistributionIdArray[3];
        $Modifier = $DeletePaymentDistributionIdArray[4];
        $Codetype = $DeletePaymentDistributionIdArray[5];
        //delete and log that action
        row_modify(
            "ar_activity",
            "deleted = NOW()",
            "session_id = '" . add_escape_custom($payment_id) . "' AND " .
            "pid = '" . add_escape_custom($PId) . "' AND " .
            "deleted IS NULL AND " .
            "encounter = '" . add_escape_custom($Encounter) . "' AND " .
            "code_type = '" . add_escape_custom($Codetype) . "' AND " .
            "code = '" . add_escape_custom($Code) . "' AND " .
            "modifier='" . add_escape_custom($Modifier) . "'"
        );
        $Message = 'Delete';
        //------------------
        $_POST["mode"] = "searchdatabase";
    }
}

//===============================================================================
//Modify Payment Code.
//===============================================================================

if (isset($_POST["mode"])) {
    if ($_POST["mode"] == "ModifyPayments" || $_POST["mode"] == "FinishPayments") {
        $payment_id = $_REQUEST['payment_id'];
        //ar_session Code
        //===============================================================================
        if (trim($_POST['type_name']) == 'insurance') {
            $QueryPart = "payer_id = '" . trim(formData('hidden_type_code')) .
                "', patient_id = '" . 0;
        } elseif (trim($_POST['type_name']) == 'patient') {
            $QueryPart = "payer_id = '" . 0 .
                "', patient_id = '" . trim(formData('hidden_type_code'));
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

        $global_account = "";
        if (formData('global_reset') == '-0.00') {
            $global_account = "', global_amount = '" . trim(formData('global_reset'));
        }

        sqlStatement("update ar_session set " .
            $QueryPart .
            "', user_id = '" . trim(add_escape_custom($user_id)) .
            "', closed = '" . trim(add_escape_custom($closed)) .
            "', reference = '" . trim(formData('check_number')) .
            "', check_date = '" . trim(add_escape_custom($check_date)) .
            "', deposit_date = '" . trim(add_escape_custom($deposit_date)) .
            "', pay_total = '" . trim(formData('payment_amount')) .
            "', modified_time = '" . trim(add_escape_custom($modified_time)) .
            $global_account .
            "', payment_type = '" . trim(formData('type_name')) .
            "', description = '" . trim(formData('description')) .
            "', adjustment_code = '" . trim(formData('adjustment_code')) .
            "', post_to_date = '" . trim(add_escape_custom($post_to_date)) .
            "', payment_method = '" . trim(formData('payment_method')) .
            "'    where session_id='" . add_escape_custom($payment_id) . "'");
        //===============================================================================
        $CountIndexAbove = $_REQUEST['CountIndexAbove'];
        $CountIndexBelow = $_REQUEST['CountIndexBelow'];
        $hidden_patient_code = $_REQUEST['hidden_patient_code'];
        $user_id = $_SESSION['authUserID'];
        $created_time = date('Y-m-d H:i:s');
        //==================================================================
        //UPDATION
        //It is done with out deleting any old entries.
        //==================================================================
        for ($CountRow = 1; $CountRow <= $CountIndexAbove; $CountRow++) {
            if (isset($_POST["HiddenEncounter$CountRow"])) {
                $where1 = "WHERE deleted IS NULL AND session_id = '" . add_escape_custom($payment_id) .
                    "' AND pid ='" . trim(formData("HiddenPId$CountRow")) .
                    "' AND encounter = '" . trim(formData("HiddenEncounter$CountRow")) .
                    "' AND code_type = '" . trim(formData("HiddenCodetype$CountRow")) .
                    "' AND code = '" . trim(formData("HiddenCode$CountRow")) .
                    "' AND modifier = '" . trim(formData("HiddenModifier$CountRow")) .
                    "'";

                $where = "$where1 AND pay_amount > 0";
                if (!empty($_POST["Payment$CountRow"])) {
                    if (trim($_POST['type_name']) == 'insurance') {
                        if (trim($_POST["HiddenIns$CountRow"]) == 1) {
                            $AccountCode = "IPP";
                        }
                        if (trim($_POST["HiddenIns$CountRow"]) == 2) {
                            $AccountCode = "ISP";
                        }
                        if (trim($_POST["HiddenIns$CountRow"]) == 3) {
                            $AccountCode = "ITP";
                        }
                    } elseif (trim($_POST['type_name']) == 'patient') {
                        $AccountCode = "PP";
                    }
                    $resPayment = sqlStatement("SELECT * from ar_activity $where");
                    if (sqlNumRows($resPayment) > 0) {
                        sqlStatement("UPDATE ar_activity SET deleted = NOW() $where");
                    }
                    sqlBeginTrans();
                    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment " .
                        "FROM ar_activity WHERE pid = '" . trim(formData("HiddenPId$CountRow")) .
                        "' AND encounter = '" . trim(formData("HiddenEncounter$CountRow")) . "'");
                    sqlStatement("insert into ar_activity set " .
                        "pid = '" . trim(formData("HiddenPId$CountRow")) .
                        "', encounter = '" . trim(formData("HiddenEncounter$CountRow")) .
                        "', sequence_no = '" . add_escape_custom($sequence_no['increment']) .
                        "', code_type = '" . trim(formData("HiddenCodetype$CountRow")) .
                        "', code = '" . trim(formData("HiddenCode$CountRow")) .
                        "', modifier = '" . trim(formData("HiddenModifier$CountRow")) .
                        "', payer_type = '" . trim(formData("HiddenIns$CountRow")) .
                        "', reason_code = '" . trim(formData("ReasonCode$CountRow")) .
                        "', post_time = '" . trim(add_escape_custom($created_time)) .
                        "', post_user = '" . trim(add_escape_custom($user_id)) .
                        "', session_id = '" . trim(formData('payment_id')) .
                        "', modified_time = '" . trim(add_escape_custom($created_time)) .
                        "', pay_amount = '" . trim(formData("Payment$CountRow")) .
                        "', adj_amount = '" . 0 .
                        "', account_code = '" . add_escape_custom($AccountCode) .
                        "'");
                    sqlCommitTrans();
                } else {
                    sqlStatement("UPDATE ar_activity SET deleted = NOW() $where");
                }

                //==============================================================================================================================

                $where = "$where1 AND adj_amount != 0";
                if (isset($_POST["AdjAmount$CountRow"]) && floatval($_POST["AdjAmount$CountRow"]) !== 0) {
                    if (trim($_POST['type_name']) == 'insurance') {
                        $AdjustString = "Ins adjust Ins" . trim($_POST["HiddenIns$CountRow"]);
                        $AccountCode = "IA";
                    } elseif (trim($_POST['type_name']) == 'patient') {
                        $AdjustString = "Pt adjust";
                        $AccountCode = "PA";
                    }
                    $resPayment = sqlStatement("SELECT  * from ar_activity $where");
                    if (sqlNumRows($resPayment) > 0) {
                        sqlStatement("update ar_activity set deleted = NOW() $where");
                    }
                    sqlBeginTrans();
                    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = '" . trim(formData("HiddenPId$CountRow")) . "' AND encounter = '" . trim(formData("HiddenEncounter$CountRow")) . "'");
                    sqlStatement("insert into ar_activity set " .
                        "pid = '" . trim(formData("HiddenPId$CountRow")) .
                        "', encounter = '" . trim(formData("HiddenEncounter$CountRow")) .
                        "', sequence_no = '" . add_escape_custom($sequence_no['increment']) .
                        "', code_type = '" . trim(formData("HiddenCodetype$CountRow")) .
                        "', code = '" . trim(formData("HiddenCode$CountRow")) .
                        "', modifier = '" . trim(formData("HiddenModifier$CountRow")) .
                        "', payer_type = '" . trim(formData("HiddenIns$CountRow")) .
                        "', post_time = '" . trim(add_escape_custom($created_time)) .
                        "', post_user = '" . trim(add_escape_custom($user_id)) .
                        "', session_id = '" . trim(formData('payment_id')) .
                        "', modified_time = '" . trim(add_escape_custom($created_time)) .
                        "', pay_amount = '" . 0 .
                        "', adj_amount = '" . trim(formData("AdjAmount$CountRow")) .
                        "', memo = '" . add_escape_custom($AdjustString) .
                        "', account_code = '" . add_escape_custom($AccountCode) .
                        "'");
                    sqlCommitTrans();
                } else {
                    sqlStatement("update ar_activity set deleted = NOW() $where");
                }

                //==============================================================================================================================

                $where = "$where1 AND (memo LIKE 'Deductable%' OR memo LIKE 'Deductible%')";
                if (!empty($_POST["Deductible$CountRow"])) {
                    $resPayment = sqlStatement("SELECT  * from ar_activity $where");
                    if (sqlNumRows($resPayment) > 0) {
                        sqlStatement("update ar_activity set deleted = NOW() $where");
                    }
                    sqlBeginTrans();
                    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = '" . trim(formData("HiddenPId$CountRow")) . "' AND encounter = '" . trim(formData("HiddenEncounter$CountRow")) . "'");
                    sqlStatement("insert into ar_activity set " .
                        "pid = '" . trim(formData("HiddenPId$CountRow")) .
                        "', encounter = '" . trim(formData("HiddenEncounter$CountRow")) .
                        "', sequence_no = '" . add_escape_custom($sequence_no['increment']) .
                        "', code_type = '" . trim(formData("HiddenCodetype$CountRow")) .
                        "', code = '" . trim(formData("HiddenCode$CountRow")) .
                        "', modifier = '" . trim(formData("HiddenModifier$CountRow")) .
                        "', payer_type = '" . trim(formData("HiddenIns$CountRow")) .
                        "', post_time = '" . trim(add_escape_custom($created_time)) .
                        "', post_user = '" . trim(add_escape_custom($user_id)) .
                        "', session_id = '" . trim(formData('payment_id')) .
                        "', modified_time = '" . trim(add_escape_custom($created_time)) .
                        "', pay_amount = '" . 0 .
                        "', adj_amount = '" . 0 .
                        "', memo = '" . "Deductible $" . trim(formData("Deductible$CountRow")) .
                        "', account_code = '" . "Deduct" .
                        "'");
                    sqlCommitTrans();
                } else {
                    sqlStatement("delete from ar_activity $where");
                }

                //==============================================================================================================================

                $where = "$where1 AND pay_amount < 0";
                if (!empty($_POST["Takeback$CountRow"])) {
                    $resPayment = sqlStatement("SELECT  * from ar_activity $where");
                    if (sqlNumRows($resPayment) > 0) {
                        sqlStatement("update ar_activity set deleted = NOW() $where");
                    }
                    sqlBeginTrans();
                    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = '" . trim(formData("HiddenPId$CountRow")) . "' AND encounter = '" . trim(formData("HiddenEncounter$CountRow")) . "'");
                    sqlStatement("insert into ar_activity set " .
                        "pid = '" . trim(formData("HiddenPId$CountRow")) .
                        "', encounter = '" . trim(formData("HiddenEncounter$CountRow")) .
                        "', sequence_no = '" . add_escape_custom($sequence_no['increment']) .
                        "', code_type = '" . trim(formData("HiddenCodetype$CountRow")) .
                        "', code = '" . trim(formData("HiddenCode$CountRow")) .
                        "', modifier = '" . trim(formData("HiddenModifier$CountRow")) .
                        "', payer_type = '" . trim(formData("HiddenIns$CountRow")) .
                        "', post_time = '" . trim(add_escape_custom($created_time)) .
                        "', post_user = '" . trim(add_escape_custom($user_id)) .
                        "', session_id = '" . trim(formData('payment_id')) .
                        "', modified_time = '" . trim(add_escape_custom($created_time)) .
                        "', pay_amount = '" . trim(formData("Takeback$CountRow")) * -1 .
                        "', adj_amount = '" . 0 .
                        "', account_code = '" . "Takeback" .
                        "'");
                    sqlCommitTrans();
                } else {
                    sqlStatement("delete from ar_activity $where");
                }

                //==============================================================================================================================

                $where = "$where1 AND follow_up = 'y'";
                if (isset($_POST["FollowUp$CountRow"]) && $_POST["FollowUp$CountRow"] == 'y') {
                    $resPayment = sqlStatement("SELECT  * from ar_activity $where");
                    if (sqlNumRows($resPayment) > 0) {
                        sqlStatement("update ar_activity set deleted = NOW() $where");
                    }
                    sqlBeginTrans();
                    $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = '" . trim(formData("HiddenPId$CountRow")) . "' AND encounter = '" . trim(formData("HiddenEncounter$CountRow")) . "'");
                    sqlStatement("insert into ar_activity set " .
                        "pid = '" . trim(formData("HiddenPId$CountRow")) .
                        "', encounter = '" . trim(formData("HiddenEncounter$CountRow")) .
                        "', sequence_no = '" . add_escape_custom($sequence_no['increment']) .
                        "', code_type = '" . trim(formData("HiddenCodetype$CountRow")) .
                        "', code = '" . trim(formData("HiddenCode$CountRow")) .
                        "', modifier = '" . trim(formData("HiddenModifier$CountRow")) .
                        "', payer_type = '" . trim(formData("HiddenIns$CountRow")) .
                        "', post_time = '" . trim(add_escape_custom($created_time)) .
                        "', post_user = '" . trim(add_escape_custom($user_id)) .
                        "', session_id = '" . trim(formData('payment_id')) .
                        "', modified_time = '" . trim(add_escape_custom($created_time)) .
                        "', pay_amount = '" . 0 .
                        "', adj_amount = '" . 0 .
                        "', follow_up = '" . "y" .
                        "', follow_up_note = '" . trim(formData("FollowUpReason$CountRow")) .
                        "'");
                    sqlCommitTrans();
                } else {
                    sqlStatement("delete from ar_activity $where");
                }

                //==============================================================================================================================
            } else {
                break;
            }
        }

        //=========
        //INSERTION of new entries,continuation of modification.
        //=========
        for ($CountRow = $CountIndexAbove + 1; $CountRow <= $CountIndexAbove + $CountIndexBelow; $CountRow++) {
            if (isset($_POST["HiddenEncounter$CountRow"])) {
                DistributionInsert($CountRow, $created_time, $user_id);
            } else {
                break;
            }
        }

        if ($_REQUEST['global_amount'] == 'yes') {
            sqlStatement(
                "update ar_session set global_amount=? where session_id =?",
                [(isset($_POST["HidUnappliedAmount"]) ? floatval($_POST["HidUnappliedAmount"]) : 0), $payment_id]
            );
        } elseif ($_REQUEST['global_reset'] == '-0.00') {
            sqlStatement("update ar_session set global_amount=? where session_id =?", [0, $payment_id]);
        }

        if ($_POST["mode"] == "FinishPayments") {
            $Message = 'Finish';
        }

        $_POST["mode"] = "searchdatabase";
        $Message = 'Modify';
    }
}

//==============================================================================
//Search Code
//===============================================================================
$payment_id = !empty($payment_id) ? (int) $payment_id : (int) $_REQUEST['payment_id'];
$ResultSearchSub = sqlStatement(
    "SELECT DISTINCT encounter, code_type, code, modifier, pid " .
    "FROM ar_activity WHERE deleted IS NULL AND session_id = ? " .
    "ORDER BY pid, encounter, code, modifier",
    [$payment_id]
);

//==============================================================================

//==============================================================================
//===============================================================================
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Confirm Payment'); ?></title>
    <?php Header::setupHeader(['datetime-picker', 'common']); ?>

    <script>
    const mypcc = '1';
    </script>
    <?php include_once("{$GLOBALS['srcdir']}/payment_jav.inc.php"); ?>
    <?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
    <script>
        function ModifyPayments() {//Used while modifying the allocation
            if (!FormValidations())//FormValidations contains the form checks
            {
                return false;
            }
            if (CompletlyBlankAbove())//The distribution rows already in the database are checked.
            {
                alert(<?php echo xlj('None of the Top Distribution Row Can be Completly Blank.'); ?> +"\n" + <?php echo xlj('Use Delete Option to Remove.'); ?>);
                return false;
            }
    if (!CheckPayingEntityAndDistributionPostFor()) {
        //Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
                return false;
            }
    if (CompletlyBlankBelow()) {
        //The newly added distribution rows are checked.
                alert(<?php echo xlj('Fill any of the Below Row.'); ?>);
                return false;
            }
    let PostValue = CheckUnappliedAmount();//Decides TdUnappliedAmount >0, or <0 or =0
            if (PostValue == 1) {
                alert(<?php echo xlj('Cannot Modify Payments.Undistributed is Negative.'); ?>);
                return false;
            }
            dialog.confirm(<?php echo xlj('Would you like to Modify Payments?'); ?>).then(returned => {
                if (returned === true) {
                    document.getElementById('mode').value = 'ModifyPayments';
                    top.restoreSession();
                    document.forms[0].submit();
                } else {
                    dialog.close();
                    return false;
                }
            });
        }

        function FinishPayments() {
            if (!FormValidations())//FormValidations contains the form checks
            {
                return false;
            }
            if (CompletlyBlankAbove())//The distribution rows already in the database are checked.
            {
                alert(<?php echo xlj('None of the Top Distribution Row Can be Completly Blank.'); ?> +"\n" + <?php echo xlj('Use Delete Option to Remove.'); ?>);
                return false;
            }
            if (!CheckPayingEntityAndDistributionPostFor())//Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
            {
                return false;
            }
            if (CompletlyBlankBelow())//The newly added distribution rows are checked.
            {
                alert(<?php echo xlj('Fill any of the Below Row.'); ?>);
                return false;
            }
    let PostValue = CheckUnappliedAmount();//Decides TdUnappliedAmount >0, or <0 or =0
            if (PostValue == 1) {
                alert(<?php echo xlj('Cannot Modify Payments.Undistributed is Negative.'); ?>);
                return false;
            }
            if (PostValue == 2) {
                if (confirm(<?php echo xlj('Would you like to Modify and Finish Payments?'); ?>)) {
                    UnappliedAmount = document.getElementById('TdUnappliedAmount').innerHTML * 1;
                    if (confirm(<?php echo xlj('Undistributed is'); ?> +' ' + UnappliedAmount + '.' + '\n' + <?php echo xlj('Would you like the balance amount to apply to Global Account?'); ?>)) {
                        document.getElementById('mode').value = 'FinishPayments';
                        document.getElementById('global_amount').value = 'yes';
                        top.restoreSession();
                        document.forms[0].submit();
                    } else {
                        document.getElementById('mode').value = 'FinishPayments';
                        top.restoreSession();
                        document.forms[0].submit();
                    }
                } else
                    return false;
            } else {
                if (confirm(<?php echo xlj('Would you like to Modify and Finish Payments?'); ?>)) {
                    document.getElementById('mode').value = 'FinishPayments';
                    top.restoreSession();
                    document.forms[0].submit();
                } else
                    return false;
            }

        }

        function CompletlyBlankAbove() {//The distribution rows already in the database are checked.
            //It is not allowed to be made completly empty.If needed delete option need to be used.
    let CountIndexAbove = document.getElementById('CountIndexAbove').value * 1;
            for (RowCount = 1; RowCount <= CountIndexAbove; RowCount++) {
                if (document.getElementById('Allowed' + RowCount).value == '' && document.getElementById('Payment' + RowCount).value == '' && document.getElementById('AdjAmount' + RowCount).value == '' && document.getElementById('Deductible' + RowCount).value == '' && document.getElementById('Takeback' + RowCount).value == '' && document.getElementById('FollowUp' + RowCount).checked == false) {
                    return true;
                }
            }
            return false;
        }

        function CompletlyBlankBelow() {//The newly added distribution rows are checked.
            //It is not allowed to be made completly empty.
    let CountIndexAbove = document.getElementById('CountIndexAbove').value * 1;
    let CountIndexBelow = document.getElementById('CountIndexBelow').value * 1;
            if (CountIndexBelow == 0)
                return false;
            for (RowCount = CountIndexAbove + 1; RowCount <= CountIndexAbove + CountIndexBelow; RowCount++) {
                if (document.getElementById('Allowed' + RowCount).value == '' && document.getElementById('Payment' + RowCount).value == '' && document.getElementById('AdjAmount' + RowCount).value == '' && document.getElementById('Deductible' + RowCount).value == '' && document.getElementById('Takeback' + RowCount).value == '' && document.getElementById('FollowUp' + RowCount).checked == false) {

                } else
                    return false;
            }
            return true;
        }

        function OnloadAction() {//Displays message while loading after some action.
    let after_value = document.getElementById('ActionStatus').value;
            if (after_value == 'Delete') {
                alert(<?php echo xlj('Successfully Deleted'); ?>);
                return true;
            }
            if (after_value == 'Modify' || after_value == 'Finish') {
                alert(<?php echo xlj('Successfully Modified'); ?>);
                return true;
            }
            after_value = document.getElementById('after_value').value;
    let payment_id = document.getElementById('payment_id').value;
            if (after_value == 'distribute') {
            } else if (after_value == 'new_payment') {
                if (document.getElementById('TablePatientPortion')) {
                    document.getElementById('TablePatientPortion').style.display = 'none';
                }
                if (confirm(<?php echo xlj('Successfully Saved.Would you like to Distribute?'); ?>)) {
                    if (document.getElementById('TablePatientPortion')) {
                        document.getElementById('TablePatientPortion').style.display = '';
                    }
                }
            }

        }

        function DeletePaymentDistribution(DeleteId) {//Confirms deletion of payment distribution.
            if (confirm(<?php echo xlj('Would you like to Delete Payment Distribution?'); ?>)) {
                document.getElementById('mode').value = 'DeletePaymentDistribution';
                document.getElementById('DeletePaymentDistributionId').value = DeleteId;
                top.restoreSession();
                document.forms[0].submit();
            } else
                return false;
        }

        //========================================================================================

        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

        document.onclick = HideTheAjaxDivs;
    </script>
    <style>
        .amt_input {
            max-width: 75px;
        }
        .bottom {
            border-bottom: 1px solid var(--black);
        }

        .top {
            border-top: 1px solid var(--black);
        }

        .left {
            border-left: 1px solid var(--black);
        }

        .right {
            border-right: 1px solid var(--black);
        }

        .form-group {
            margin-bottom: 5px;
        }

        legend {
            border-bottom: 2px solid #E5E5E5;
            background: #E5E5E5;
            padding-left: 10px;
        }

        .form-horizontal .control-label {
            padding-top: 2px;
        }

        fieldset {
            border-color: #68171A !important;
            background-color: #f2f2f2;
            margin-bottom: 10px;
            padding-bottom: 15px;
        }

        @media only screen and (max-width: 768px) {
            [class*="col-"] {
                width: 100%;
                text-align: left !important;
            }
        }
    </style>
    <?php Header::setupHeader(['datetime-picker', 'common']); ?>
</head>
<body class="body_top" onload="OnloadAction()">
    <div class="container-fluid">
        <?php
        if ($_REQUEST['ParentPage'] ?? '' == 'new_payment') {
            ?>
            <div class="row">
                <div class="col-12">
                    <h2><?php echo xlt('Payments'); ?></h2>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <nav class="navbar navbar-nav navbar-expand-md navbar-light text-body bg-light static-top">
                        <button class="navbar-toggler icon-bar" data-target="#myNavbar" data-toggle="collapse" type="button"><span class="navbar-toggler-icon"></span></button>
                        <div class="collapse navbar-collapse" id="myNavbar">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item active">
                                    <a class="nav-link font-weight-bold" href='new_payment.php'><?php echo xlt('New Payment'); ?></a>
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
                </div>
            </div>
            <?php
        }
        ?>
        <?php
        if (empty($payment_id)) {
            $onclick = "top.restoreSession();return SavePayment();";
        } else {
            $onclick = "return false;";
        }
        ?>
        <form class="form" name='new_payment' method='post' action="edit_payment.php" onsubmit='<?php echo $onclick; ?>'>
            <?php
            if (!empty($payment_id)) { ?>
            <fieldset>
                <?php
                require_once("payment_master.inc.php");  //Check/cash details are entered here.
                ?>
                <?php }//End of if($payment_id*1>0) ?>
                <?php
                if (!empty($payment_id)) {//Distribution rows already in the database are displayed.
                    ?>
                    <?php //
                    $resCount = sqlStatement(
                        "SELECT DISTINCT encounter, code_type, code, modifier FROM ar_activity " .
                        "WHERE deleted IS NULL AND session_id = ?",
                        [$payment_id]
                    );
                    $TotalRows = sqlNumRows($resCount);
                    $CountPatient = 0;
                    $CountIndex = 0;
                    $CountIndexAbove = 0;
                    $paymenttot = 0;
                    $adjamttot = 0;
                    $deductibletot = 0;
                    $takebacktot = 0;
                    $allowedtot = 0;
                    if ($RowSearchSub = sqlFetchArray($ResultSearchSub)) {
                        do {
                            $CountPatient++;
                            $PId = $RowSearchSub['pid'];
                            $EncounterMaster = $RowSearchSub['encounter'];
                            // Only use the code_type in the queries below if it is specified in the ar_activity table.
                            // If it is not specified in the ar_activity table, also note it is not requested from the
                            // billing table in below query, thus making it blank in all queries below in this script.
                            $CodetypeMaster = $RowSearchSub['code_type'];
                            $sql_select_part_codetype = "";
                            $sql_where_part_codetype = "";
                            if (!empty($CodetypeMaster)) {
                                $sql_select_part_codetype = "billing.code_type,";
                                $sql_where_part_codetype = "and billing.code_type ='" . add_escape_custom($CodetypeMaster) . "'";
                            }
                            $CodeMaster = $RowSearchSub['code'];
                            $ModifierMaster = $RowSearchSub['modifier'];
                            $res = sqlStatement("SELECT fname,lname,mname FROM patient_data where pid =?", [$PId]);
                            $row = sqlFetchArray($res);
                            $fname = $row['fname'];
                            $lname = $row['lname'];
                            $mname = $row['mname'];
                            $NameDB = $lname . ' ' . $fname . ' ' . $mname;
                            $ResultSearch = sqlStatement("SELECT billing.id,last_level_closed,billing.encounter,form_encounter.`date`,$sql_select_part_codetype billing.code,billing.modifier,fee
                             FROM billing ,form_encounter
                             where billing.encounter=form_encounter.encounter and billing.pid=form_encounter.pid and
                             code_type !='ICD9' and  code_type !='COPAY' and billing.activity !=0 and
                             form_encounter.pid ='" . add_escape_custom($PId) . "' and billing.pid ='" . add_escape_custom($PId) . "' and billing.encounter ='" . add_escape_custom($EncounterMaster) . "'
                             $sql_where_part_codetype
                             and billing.code ='" . add_escape_custom($CodeMaster) . "'
                             and billing.modifier ='" . add_escape_custom($ModifierMaster) . "'
                             ORDER BY form_encounter.`date`,form_encounter.encounter,billing.code,billing.modifier");
                            if (sqlNumRows($ResultSearch) > 0) {
                                if ($CountPatient === 1) {
                                    $Table = 'yes';
                                    ?>
                                    <br /><br />
                                    <div class="row" id="tableRow">
                                    <legend><?php echo xlt("Distributed Edits") ?></legend>
                                    <div class="table-responsive-lg">
                                    <table class="table table-sm table-bordered table-light" id="TableDistributedEdit" >
                                    <thead class="bg-dark text-light">
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th><?php echo xlt('Patient Name'); ?></th>
                                        <th><?php echo xlt('Post For'); ?></th>
                                        <th><?php echo xlt('Service Date'); ?></th>
                                        <th><?php echo xlt('Enc#'); ?></th>
                                        <th><?php echo xlt('Code'); ?></th>
                                        <th><?php echo xlt('Charge'); ?></th>
                                        <th><?php echo xlt('Copay'); ?></th>
                                        <th><?php echo xlt('Bal-Due'); ?></th>
                                        <th><?php echo xlt('Allowed(c)'); ?></th><!-- (c) means it is calculated.Not stored one. -->
                                        <th><?php echo xlt('Payment'); ?></th>
                                        <th><?php echo xlt('Adj Amount'); ?></th>
                                        <th><?php echo xlt('Deductible'); ?></th>
                                        <th><?php echo xlt('Takeback'); ?></th>
                                        <th><?php echo xlt('MSP'); ?></th>
                                        <th><?php echo xlt('Resn'); ?></th>
                                        <th><?php echo xlt('Follow Up Reason'); ?></th>
                                    </tr>
                                    </thead>
                                <?php }
                                while ($RowSearch = sqlFetchArray($ResultSearch)) {
                                    $CountIndex++;
                                    $CountIndexAbove++;
                                    $ServiceDateArray = explode(' ', $RowSearch['date']);
                                    $ServiceDate = oeFormatShortDate($ServiceDateArray[0]);
                                    $Codetype = $RowSearch['code_type'];
                                    $Code = $RowSearch['code'];
                                    $Modifier = $RowSearch['modifier'];
                                    if ($Modifier != '') {
                                        $ModifierString = ", $Modifier";
                                    } else {
                                        $ModifierString = "";
                                    }
                                    $Fee = $RowSearch['fee'];
                                    $Encounter = $RowSearch['encounter'];

                                    $resPayer = sqlStatement(
                                        "SELECT payer_type FROM ar_activity WHERE " .
                                        "deleted IS NULL AND session_id = ? AND pid = ? AND encounter = ? " .
                                        "AND code_type = ? AND code = ? AND modifier = ?",
                                        [$payment_id, $PId, $Encounter, $Codetype, $Code, $Modifier]
                                    );
                                    $rowPayer = sqlFetchArray($resPayer);
                                    $Ins = $rowPayer['payer_type'];

                                    //Always associating the copay to a particular charge.
                                    $BillingId = $RowSearch['id'];
                                    $resId = sqlStatement("SELECT id FROM billing where code_type != 'ICD9' and code_type != 'COPAY' and
                                    pid =? and encounter =? and billing.activity!=0 order by id", [$PId, $Encounter]);
                                    $rowId = sqlFetchArray($resId);
                                    $Id = $rowId['id'];

                                    if ($BillingId != $Id) {//multiple cpt in single encounter
                                        $Copay = 0.00;
                                    } else {
                                        $resCopay = sqlStatement("SELECT sum(fee) as copay FROM billing where
                                    code_type='COPAY' and pid =? and encounter =? and billing.activity!=0", [$PId, $Encounter]);
                                        $rowCopay = sqlFetchArray($resCopay);
                                        $Copay = $rowCopay['copay'] * -1;

                                        $resMoneyGot = sqlStatement(
                                            "SELECT sum(pay_amount) AS PatientPay FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? and encounter = ? AND " .
                                            "payer_type = 0 AND account_code = 'PCP'",
                                            [$PId, $Encounter]
                                        ); //new fees screen copay gives account_code='PCP'
                                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                                        $PatientPay = $rowMoneyGot['PatientPay'];

                                        $Copay = $Copay + $PatientPay;
                                    }

                                    //For calculating Remainder
                                    if ($Ins == 0) {//Fetch all values
                                        $resMoneyGot = sqlStatement(
                                            "SELECT sum(pay_amount) as MoneyGot FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? and code_type = ? AND code = ? AND " .
                                            "modifier = ? AND encounter = ? AND " .
                                            "!(payer_type = 0 AND account_code = 'PCP')",
                                            [$PId, $Codetype, $Code, $Modifier, $Encounter]
                                        );
                                        //new fees screen copay gives account_code='PCP'
                                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                                        $MoneyGot = $rowMoneyGot['MoneyGot'];

                                        $resMoneyAdjusted = sqlStatement(
                                            "SELECT sum(adj_amount) AS MoneyAdjusted FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? and code_type = ? and code = ? AND " .
                                            "modifier = ? AND encounter = ?",
                                            [$PId, $Codetype, $Code, $Modifier, $Encounter]
                                        );
                                        $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                                        $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];
                                    } else {
                                        //Fetch till that much got
                                        //Fetch the HIGHEST sequence_no till this session.
                                        //Used maily in  the case if primary/others pays once more.
                                        $resSequence = sqlStatement("SELECT sequence_no from ar_activity where session_id=? and
                                    pid=? and encounter=? order by sequence_no desc ", [$payment_id, $PId, $Encounter]);
                                        $rowSequence = sqlFetchArray($resSequence);
                                        $Sequence = $rowSequence['sequence_no'];

                                        $resMoneyGot = sqlStatement(
                                            "SELECT sum(pay_amount) as MoneyGot FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? and code_type = ? AND code = ? AND " .
                                            "modifier = ? and encounter = ? AND " .
                                            "payer_type > 0 and payer_type <= ? and sequence_no <= ?",
                                            [$PId, $Codetype, $Code, $Modifier, $Encounter, $Ins, $Sequence]
                                        );
                                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                                        $MoneyGot = $rowMoneyGot['MoneyGot'];

                                        $resMoneyAdjusted = sqlStatement(
                                            "SELECT sum(adj_amount) AS MoneyAdjusted FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? and code_type = ? and code = ? AND " .
                                            "modifier = ? AND encounter = ? AND payer_type > 0 AND " .
                                            "payer_type <= ? and sequence_no <= ?",
                                            [$PId, $Codetype, $Code, $Modifier, $Encounter, $Ins, $Sequence]
                                        );
                                        $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                                        $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];
                                    }
                                    $Remainder = $Fee - $Copay - $MoneyGot - $MoneyAdjusted;
                                    //For calculating RemainderJS.Used while restoring back the values.
                                    if ($Ins == 0) {//Got just before Patient
                                        $resMoneyGot = sqlStatement(
                                            "SELECT sum(pay_amount) AS MoneyGot FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? AND code_type = ? AND code = ? AND " .
                                            "modifier = ? AND encounter = ? and payer_type != 0",
                                            [$PId, $Codetype, $Code, $Modifier, $Encounter]
                                        );
                                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                                        $MoneyGot = $rowMoneyGot['MoneyGot'];

                                        $resMoneyAdjusted = sqlStatement(
                                            "SELECT sum(adj_amount) AS MoneyAdjusted FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? AND code_type = ? AND code = ? AND " .
                                            "modifier = ? and encounter = ? and payer_type != 0",
                                            [$PId, $Codetype, $Code, $Modifier, $Encounter]
                                        );
                                        $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                                        $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];
                                    } else {
                                        //Got just before the previous
                                        //Fetch the LOWEST sequence_no till this session.
                                        //Used maily in  the case if primary/others pays once more.
                                        $resSequence = sqlStatement(
                                            "SELECT sequence_no FROM ar_activity WHERE " .
                                            "session_id = ? AND deleted IS NULL AND pid = ? AND encounter = ? " .
                                            "order by sequence_no",
                                            [$payment_id, $PId, $Encounter]
                                        );
                                        $rowSequence = sqlFetchArray($resSequence);
                                        $Sequence = $rowSequence['sequence_no'];

                                        $resMoneyGot = sqlStatement(
                                            "SELECT sum(pay_amount) as MoneyGot FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? AND code_type = ? AND code = ? " .
                                            "AND modifier = ? AND encounter = ? AND payer_type > 0 AND " .
                                            "payer_type <= ? AND sequence_no < ?",
                                            [$PId, $Codetype, $Code, $Modifier, $Encounter, $Ins, $Sequence]
                                        );
                                        $rowMoneyGot = sqlFetchArray($resMoneyGot);
                                        $MoneyGot = $rowMoneyGot['MoneyGot'];

                                        $resMoneyAdjusted = sqlStatement(
                                            "SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity WHERE " .
                                            "deleted IS NULL AND pid = ? and code_type = ? and code = ? AND " .
                                            "modifier = ? AND encounter = ? AND payer_type <= ? AND sequence_no < ?",
                                            [$PId, $Codetype, $Code, $Modifier, $Encounter, $Ins, $Sequence]
                                        );
                                        $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                                        $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];
                                    }
                                    //Stored in hidden so that can be used while restoring back the values.
                                    $RemainderJS = $Fee - $Copay - $MoneyGot - $MoneyAdjusted;

                                    $resPayment = sqlStatement(
                                        "SELECT pay_amount FROM ar_activity WHERE " .
                                        "deleted IS NULL AND session_id=? AND pid = ? AND encounter = ? AND " .
                                        "code_type = ? AND code = ? AND modifier = ? AND pay_amount > 0",
                                        [$payment_id, $PId, $Encounter, $Codetype, $Code, $Modifier]
                                    );
                                    $rowPayment = sqlFetchArray($resPayment);
                                    $PaymentDB = floatval($rowPayment['pay_amount'] ?? null);

                                    $resPayment = sqlStatement(
                                        "SELECT pay_amount FROM ar_activity WHERE " .
                                        "deleted IS NULL AND session_id = ? AND pid = ? AND encounter = ? AND " .
                                        "code_type = ? AND code = ? AND modifier = ? AND pay_amount < 0",
                                        [$payment_id, $PId, $Encounter, $Codetype, $Code, $Modifier]
                                    );
                                    $rowPayment = sqlFetchArray($resPayment);
                                    $TakebackDB = floatval($rowPayment['pay_amount'] ?? null);

                                    $resPayment = sqlStatement(
                                        "SELECT adj_amount FROM ar_activity WHERE " .
                                        "deleted IS NULL AND session_id = ? AND pid = ? AND encounter = ? AND " .
                                        "code_type = ? AND code = ? AND modifier = ? AND adj_amount != 0",
                                        [$payment_id, $PId, $Encounter, $Codetype, $Code, $Modifier]
                                    );
                                    $rowPayment = sqlFetchArray($resPayment);
                                    $AdjAmountDB = floatval($rowPayment['adj_amount'] ?? null);

                                    $resPayment = sqlStatement(
                                        "SELECT memo FROM ar_activity WHERE " .
                                        "deleted IS NULL AND session_id = ? AND pid = ? AND encounter = ? AND " .
                                        "code_type = ? AND code = ? AND modifier = ? AND " .
                                        "(memo LIKE 'Deductable%' OR memo LIKE 'Deductible%')",
                                        [$payment_id, $PId, $Encounter, $Codetype, $Code, $Modifier]
                                    );
                                    $rowPayment = sqlFetchArray($resPayment);
                                    $DeductibleDB = $rowPayment['memo'] ?? '';
                                    $DeductibleDB = str_replace('Deductable $', '', $DeductibleDB);
                                    $DeductibleDB = str_replace('Deductible $', '', $DeductibleDB);

                                    $resPayment = sqlStatement(
                                        "SELECT follow_up, follow_up_note FROM ar_activity WHERE " .
                                        "deleted IS NULL AND session_id = ? AND pid = ? AND encounter = ? AND " .
                                        "code_type = ? AND code = ? AND modifier = ? AND follow_up = 'y'",
                                        [$payment_id, $PId, $Encounter, $Codetype, $Code, $Modifier]
                                    );
                                    $rowPayment = sqlFetchArray($resPayment);
                                    $FollowUpDB = $rowPayment['follow_up'] ?? '';
                                    $FollowUpReasonDB = $rowPayment['follow_up_note'] ?? '';

                                    $resPayment = sqlStatement(
                                        "SELECT reason_code FROM ar_activity WHERE " .
                                        "deleted IS NULL AND session_id = ? AND pid = ? AND encounter = ? AND " .
                                        "code_type = ? AND code = ? AND modifier = ?",
                                        [$payment_id, $PId, $Encounter, $Codetype, $Code, $Modifier]
                                    );
                                    $rowPayment = sqlFetchArray($resPayment);
                                    $ReasonCodeDB = $rowPayment['reason_code'];

                                    if ($Ins == 1) {
                                        $AllowedDB = number_format($Fee - floatval($AdjAmountDB), 2);
                                    } else {
                                        $AllowedDB = 0;
                                    }

                                    if ($Ins == 1) {
                                        $bgcolor = '#ddddff';
                                    } elseif ($Ins == 2) {
                                        $bgcolor = '#ffdddd';
                                    } elseif ($Ins == 3) {
                                        $bgcolor = '#F2F1BC';
                                    } elseif ($Ins == 0) {
                                        $bgcolor = '#AAFFFF';
                                    }
                                    $paymenttot = $paymenttot + floatval($PaymentDB);
                                    $adjamttot = $adjamttot + floatval($AdjAmountDB);
                                    $deductibletot = $deductibletot + floatval($DeductibleDB);
                                    $takebacktot = $takebacktot + floatval($TakebackDB);
                                    $allowedtot = $allowedtot + floatval($AllowedDB);
                                    ?>

                                <tr class="border-dark" bgcolor='<?php echo attr($bgcolor); ?>' class="text" id="trCharges<?php echo attr($CountIndex); ?>">
                                    <td align="left">
                                        <a href="#" onclick="javascript:return DeletePaymentDistribution(<?php echo attr_js($payment_id . '_' . $PId . '_' . $Encounter . '_' . $Code . '_' . $Modifier . '_' . $Codetype); ?>);"><img border="0" src="../pic/Delete.gif"></a>
                                    </td>
                                    <td align="left">
                                        <?php echo text($NameDB); ?><input name="HiddenPId<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($PId); ?>" />
                                    </td>
                                    <td align="left">
                                        <input id="HiddenIns<?php echo attr($CountIndex); ?>" name="HiddenIns<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($Ins); ?>" /><?php echo generate_select_list("payment_ins$CountIndex", "payment_ins", "$Ins", "Insurance/Patient", '', 'w-100', 'ActionOnInsPat("' . $CountIndex . '")'); ?>
                                    </td>
                                    <td>
                                        <?php echo text($ServiceDate); ?>
                                    </td>
                                    <td align="right">
                                        <input name="HiddenEncounter<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($Encounter); ?>" /><?php echo text($Encounter); ?>
                                    </td>
                                    <td>
                                        <input name="HiddenCodetype<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($Codetype); ?>" />
                                        <input name="HiddenCode<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($Code); ?>" /><?php echo text($Codetype . "-" . $Code . $ModifierString); ?>
                                        <input name="HiddenModifier<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($Modifier); ?>" />
                                    </td>
                                    <td align="right">
                                        <input id="HiddenChargeAmount<?php echo attr($CountIndex); ?>" name="HiddenChargeAmount<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($Fee); ?>" /><?php echo text($Fee); ?>
                                    </td>
                                    <td align="right">
                                        <input id="HiddenCopayAmount<?php echo attr($CountIndex); ?>" name="HiddenCopayAmount<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($Copay); ?>"><?php echo text(number_format($Copay, 2)); ?>
                                    </td>
                                    <td align="right" id="RemainderTd<?php echo attr($CountIndex); ?>"> <?php echo text(round($Remainder, 2)); ?> </td>
                                    <input name="HiddenRemainderTd<?php echo attr($CountIndex); ?>" id="HiddenRemainderTd<?php echo attr($CountIndex); ?>" value="<?php echo attr(round($Remainder, 2)); ?>" type="hidden" />
                                    <td>
                                        <input autocomplete="off" id="Allowed<?php echo attr($CountIndex); ?>" name="Allowed<?php echo attr($CountIndex); ?>" onchange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo attr_js($CountIndex); ?>);UpdateTotalValues(1,<?php echo attr_js($TotalRows); ?>,'Allowed','allowtotal');UpdateTotalValues(1,<?php echo attr_js($TotalRows); ?>,'Payment','paymenttotal');UpdateTotalValues(1,<?php echo attr_js($TotalRows); ?>,'AdjAmount','AdjAmounttotal');RestoreValues(<?php echo attr_js($CountIndex); ?>)" onkeydown="PreventIt(event)"  class="text-right input-sm w-100" type="text" value="<?php echo attr($AllowedDB); ?>" />
                                    </td>

                                    <td>
                                        <input autocomplete="off" id="Payment<?php echo attr($CountIndex); ?>" name="Payment<?php echo attr($CountIndex); ?>" onchange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo attr_js($CountIndex); ?>);UpdateTotalValues(1,<?php echo attr_js($TotalRows); ?>,'Payment','paymenttotal');RestoreValues(<?php echo attr_js($CountIndex); ?>)" onkeydown="PreventIt(event)"  class="text-right  input-sm w-100" type="text" value="<?php echo attr($PaymentDB); ?>" />
                                    </td>
                                    <td>
                                        <input autocomplete="off" id="AdjAmount<?php echo attr($CountIndex); ?>" name="AdjAmount<?php echo attr($CountIndex); ?>" onchange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo attr_js($CountIndex); ?>);UpdateTotalValues(1,<?php echo attr_js($TotalRows); ?>,'AdjAmount','AdjAmounttotal');RestoreValues(<?php echo attr_js($CountIndex); ?>)" onkeydown="PreventIt(event)"  class="text-right  input-sm w-100" type="text" value="<?php echo attr($AdjAmountDB); ?>" />
                                    </td>
                                    <td>
                                        <input autocomplete="off" id="Deductible<?php echo attr($CountIndex); ?>" name="Deductible<?php echo attr($CountIndex); ?>" onchange="ValidateNumeric(this);UpdateTotalValues(1,<?php echo attr_js($TotalRows); ?>,'Deductible','deductibletotal');" onkeydown="PreventIt(event)"  class="text-right  input-sm w-100" type="text" value="<?php echo attr($DeductibleDB); ?>" />
                                    </td>
                                    <td>
                                        <input autocomplete="off" id="Takeback<?php echo attr($CountIndex); ?>" name="Takeback<?php echo attr($CountIndex); ?>" onchange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo attr_js($CountIndex); ?>);UpdateTotalValues(1,<?php echo attr_js($TotalRows); ?>,'Takeback','takebacktotal');RestoreValues(<?php echo attr_js($CountIndex); ?>)" onkeydown="PreventIt(event)"  class="text-right  input-sm w-100" type="text" value="<?php echo attr($TakebackDB); ?>" />
                                    </td>
                                    <td align="left">
                                        <input id="HiddenReasonCode<?php echo attr($CountIndex); ?>" name="HiddenReasonCode<?php echo attr($CountIndex); ?>" type="hidden" value="<?php echo attr($ReasonCodeDB); ?>" /><?php echo generate_select_list("ReasonCode$CountIndex", "msp_remit_codes", "$ReasonCodeDB", "MSP", '', 'w-100'); ?>
                                    </td>
                                    <td align="center">
                                        <input id="FollowUp<?php echo attr($CountIndex); ?>" name="FollowUp<?php echo attr($CountIndex); ?>" onclick="ActionFollowUp(<?php echo attr_js($CountIndex); ?>)" type="checkbox" value="y" />
                                    </td>
                                    <td>
                                        <input id="FollowUpReason<?php echo attr($CountIndex); ?>" name="FollowUpReason<?php echo attr($CountIndex); ?>" onkeydown="PreventIt(event)" class=" input-sm w-100" type="text" value="<?php echo attr($FollowUpReasonDB); ?>" readonly>
                                    </td>
                                    </tr><?php
                                }//End of while ($RowSearch = sqlFetchArray($ResultSearch))
                                ?>
                                <?php
                            }//End of if(sqlNumRows($ResultSearch)>0)
                        } while ($RowSearchSub = sqlFetchArray($ResultSearchSub));
                        if ($Table == 'yes') { ?>
                            <tr>
                                <td class="text-right text-dark" align="left" colspan="9"><b><?php echo (xlt("Totals") . ": ") ?></b></td>
                                <td class="bg-dark text-secondary" align="center" id="allowtotal"><?php echo text(number_format($allowedtot, 2)); ?></td>
                                <td class="bg-dark text-secondary" align="center" id="paymenttotal"><?php echo text(number_format($paymenttot, 2)); ?></td>
                                <td class="bg-dark text-secondary" align="center" id="AdjAmounttotal"><?php echo text(number_format($adjamttot, 2)); ?></td>
                                <td class="bg-dark text-secondary" align="center" id="deductibletotal"><?php echo text(number_format($deductibletot, 2)); ?></td>
                                <td class="bg-dark text-secondary" align="center" id="takebacktotal"><?php echo text(number_format($takebacktot, 2)); ?></td>
                                <td align="center" colspan="2">&nbsp;</td>
                                <td align="right">
                                    <button type="button" class="btn btn-sm btn-secondary btn-refresh pull-right"
                                        onclick="updateAllFormTotals(<?php echo attr_js($TotalRows); ?>);"><?php echo xlt("Recalculate"); ?></button>
                                </td>
                            </tr>
                            </table>
                            <?php
                        }
                        ?>
                        <?php
                        echo '<br/>';
                    }//End of if($RowSearchSub = sqlFetchArray($ResultSearchSub))
                    ?>
                    </div>
                    </div>
                    <div>
                        <?php
                        require_once("payment_pat_sel.inc.php"); //Patient ajax section and listing of charges.
                        ?>
                    </div>
                    <?php
                }//End of if($payment_id*1>0)
                ?>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                <div class="form-group clearfix">
                    <div class="col-sm-12 text-left position-override">
                        <div class="btn-group" role="group">
                            <a class="btn btn-secondary btn-save" href="#" onclick="return ModifyPayments();"><span><?php echo xlt('Modify Payments'); ?></span></a>
                            <a class="btn btn-secondary btn-save" href="#" onclick="return FinishPayments();"><span><?php echo xlt('Finish Payments'); ?></span></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <input type="hidden" name="hidden_patient_code" id="hidden_patient_code" value="<?php echo attr($hidden_patient_code ?? ''); ?>" />
                    <input type='hidden' name='mode' id='mode' value='' />
                    <input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
                    <input type="hidden" name="after_value" id="after_value" value="<?php echo attr($_POST["mode"] ?? ''); ?>" />
                    <input type="hidden" name="payment_id" id="payment_id" value="<?php echo attr($payment_id); ?>" />
                    <input type="hidden" name="hidden_type_code" id="hidden_type_code" value="<?php echo attr($TypeCode); ?>" />
                    <input type='hidden' name='global_amount' id='global_amount' value='' />
                    <input type='hidden' name='DeletePaymentDistributionId' id='DeletePaymentDistributionId' value='' />
                    <input type="hidden" name="ActionStatus" id="ActionStatus" value="<?php echo attr($Message ?? ''); ?>" />
                    <input type='hidden' name='CountIndexAbove' id='CountIndexAbove' value='<?php echo (int) attr($CountIndexAbove); ?>' />
                    <input type='hidden' name='CountIndexBelow' id='CountIndexBelow' value='<?php echo (int) attr($CountIndexBelow); ?>' />
                    <input type="hidden" name="ParentPage" id="ParentPage" value="<?php echo attr($_REQUEST['ParentPage'] ?? ''); ?>" />
                </div>
        </form>
    </div><!-- End of container div-->
    <script>
        function ResetForm() {//Resets form used in the 'Cancel Changes' button in the master screen.
            document.forms[0].reset();
            document.getElementById('TdUnappliedAmount').innerHTML = '0.00';
            document.getElementById('div_insurance_or_patient').innerHTML = '&nbsp;';
            CheckVisible('yes');//Payment Method is made 'Check Payment' and the Check box is made visible.
            PayingEntityAction();//Paying Entity is made 'insurance' and Payment Category is 'Insurance Payment'
        }

        $(function () {
            if (document.getElementById("TableDistributePortion")) {
                $("html").animate({scrollTop: $("#TableDistributePortion").offset().top}, 800);
            } else if (document.getElementById("TableDistributedEdit")) {
                $("html").animate({scrollTop: $("#TableDistributedEdit").offset().top}, 800);
            }
        });
    </script>
</body>
</html>
