<?php

/**
 * Check/cash details are entered here.Used in New Payment and Edit Payment screen.
 * Special list function
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function generate_list_payment_category($tag_name, $list_id, $currvalue, $title, $empty_name = ' ', $class = '', $onchange = '', $PaymentType = 'insurance', $screen = 'new_payment')
{
    $s = '';
    $s .= "<select name='" . attr($tag_name) . "' id='" . attr($tag_name) . "'";
    if ($class) {
        $s .= " class='" . attr($class) . "'";
    }
    if ($onchange) {
        $s .= " onchange='" . $onchange . "'"; //Need to html escape $onchange prior to the generate_list_payment_category function call
    }
    $s .= " title='" . attr($title) . "'>";
    if ($empty_name) {
        $s .= "<option value=''>" . xlt($empty_name) . "</option>";
    }
    $lres = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = 1 ORDER BY seq, title", array($list_id));
    $got_selected = false;
    while ($lrow = sqlFetchArray($lres)) {
        $s .= "<option   id='option_" . attr($lrow['option_id']) . "'" . " value='" . attr($lrow['option_id']) . "'";
        if ((strlen($currvalue) == 0 && $lrow['is_default']) || (strlen($currvalue) > 0 && $lrow['option_id'] == $currvalue) || ($lrow['option_id'] == 'insurance_payment' && $screen == 'new_payment')) {
            $s .= " selected";
            $got_selected = true;
        }
        if (($PaymentType == 'insurance' || $screen == 'new_payment') && ($lrow['option_id'] == 'family_payment' || $lrow['option_id'] == 'patient_payment')) {
            $s .= " style='background-color: var(--light)' ";
        }
        if ($PaymentType == 'patient' && $lrow['option_id'] == 'insurance_payment') {
            $s .= " style='background-color: var(--light)' ";
        }
        $s .= ">" . text(xl_list_label($lrow['title'])) . "</option>\n";
    }
    if (!$got_selected && strlen($currvalue) > 0) {
        $currescaped = text($currvalue);
        $s .= "<option value='" . attr($currvalue) . "' selected>* " . text($currvalue) . " *</option>";
        $s .= "</select>";
        $fontTitle = xl('Please choose a valid selection from the list.');
        $fontText = xl('Fix this');
        $s .= " <font class='text-danger' title='" . attr($fontTitle) . "'>" . text($fontText) . "!</font>";
    } else {
        $s .= "</select>";
    }
    return $s;
}

// ================================================================================================
$CheckNumber = '';
$CheckDate = '';
$PaymentMethod = '';
$PaymentType = '';
$AdjustmentCode = '';
$div_after_save = '';
$DepositDate = '';
$Description = '';
$TypeCode = '';
$UndistributedAmount = 0;
if ($payment_id > 0) {
    $rs = sqlStatement("select pay_total,global_amount from ar_session where session_id=?", array($payment_id));
    $row = sqlFetchArray($rs);
    $pay_total = $row['pay_total'];
    $global_amount = $row['global_amount'];
    $rs = sqlStatement(
        "SELECT sum(pay_amount) sum_pay_amount FROM ar_activity WHERE session_id = ? AND deleted IS NULL",
        array($payment_id)
    );
    $row = sqlFetchArray($rs);
    $pay_amount = $row['sum_pay_amount'];
    $UndistributedAmount = $pay_total - $pay_amount - $global_amount;

    $res = sqlStatement("SELECT check_date ,reference ,insurance_companies.name,
        payer_id,pay_total,payment_type,post_to_date,patient_id ,
        adjustment_code,description,deposit_date,payment_method
        FROM ar_session left join insurance_companies on ar_session.payer_id=insurance_companies.id where ar_session.session_id =?", array($payment_id));
    $row = sqlFetchArray($res);
    $InsuranceCompanyName = $row['name'];
    $InsuranceCompanyId = $row['payer_id'];
    $PatientId = $row['patient_id'];
    $CheckNumber = $row['reference'];
    $CheckDate = $row['check_date'] == '0000-00-00' ? '' : $row['check_date'];
    $PayTotal = $row['pay_total'];
    $PostToDate = $row['post_to_date'] == '0000-00-00' ? '' : $row['post_to_date'];
    $PaymentMethod = $row['payment_method'];
    $PaymentType = $row['payment_type'];
    $AdjustmentCode = $row['adjustment_code'];
    $DepositDate = $row['deposit_date'] == '0000-00-00' ? '' : $row['deposit_date'];
    $Description = $row['description'];
    if ($row['payment_type'] == 'insurance' || $row['payer_id'] * 1 > 0) {
        $res = sqlStatement("SELECT insurance_companies.name FROM insurance_companies
            where insurance_companies.id =?", array($InsuranceCompanyId));
        $row = sqlFetchArray($res);
        $div_after_save = $row['name'] ?? '';
        $TypeCode = $InsuranceCompanyId;
        if ($PaymentType == '') {
            $PaymentType = 'insurance';
        }
    } elseif ($row['payment_type'] == 'patient' || $row['patient_id'] * 1 > 0) {
        $res = sqlStatement("SELECT fname,lname,mname FROM patient_data
            where pid =?", array($PatientId));
        $row = sqlFetchArray($res);
        $fname = $row['fname'];
        $lname = $row['lname'];
        $mname = $row['mname'];
        $div_after_save = $lname . ' ' . $fname . ' ' . $mname;
        $TypeCode = $PatientId;
        if ($PaymentType == '') {
            $PaymentType = 'patient';
        }
    }
}
?>
<?php
//================================================================================================
if (($screen == 'new_payment' && $payment_id * 1 == 0) || ($screen == 'edit_payment' && $payment_id * 1 > 0)) {//New entry or edit in edit screen comes here.
    ?>
    <?php
    if (isset($_REQUEST['ParentPage']) && $_REQUEST['ParentPage'] == 'new_payment') {//This case comes when the Finish Payments is pressed from the New Payment screen.
        ?>
        <div class="row h3">
            <?php echo xlt('Confirm Payment'); ?>
        </div>

        <?php
    } elseif ($screen == 'new_payment') { ?>
        <div class="row h3">
            <?php echo xlt('Batch Payment Entry'); ?>
        </div>
        <?php
    } else { ?>
        <div class="row h3">
            <?php echo xlt('Edit Payment'); ?>
        </div>
        <?php
    }
    ?>
    <div class="row">
        <div class="forms col-3">
            <label class="control-label" for="check_date"><?php echo xlt('Date'); ?>:</label>
            <input class="form-control datepicker" id='check_date' name='check_date' type='text' value="<?php echo attr(oeFormatShortDate($CheckDate)); ?>" autocomplete="off">
        </div>
        <div class="forms col-3">
            <label class="control-label" for="post_to_date"><?php echo xlt('Post To Date'); ?>:</label>
            <input class="form-control datepicker" id='post_to_date' name='post_to_date' type='text' value="<?php echo ($screen == 'new_payment') ? attr(oeFormatShortDate(date('Y-m-d'))) : attr(oeFormatShortDate($PostToDate)); ?>" autocomplete="off">
        </div>
        <div class="forms col-3">
            <label class="control-label" for="payment_method"><?php echo xlt('Payment Method'); ?>:</label>
            <div class="pl-0">
                <?php
                if ($PaymentMethod == '' && $screen == 'edit_payment') {
                    $blankValue = ' ';
                } else {
                    $blankValue = '';
                }
                echo generate_select_list("payment_method", "payment_method", "$PaymentMethod", "Payment Method", "$blankValue", "", 'CheckVisible("yes")');
                ?>
            </div>
        </div>
        <div class="forms col-3">
            <label class="control-label" for="check_number"><?php echo xlt('Check Number'); ?>:</label>
            <?php
            if ($PaymentMethod == 'check_payment' || $PaymentMethod == 'bank_draft' || $CheckNumber != '' || $screen == 'new_payment') {
                $CheckDisplay = '';
                $CheckDivDisplay = ' display: none; ';
            } else {
                $CheckDisplay = ' display: none; ';
                $CheckDivDisplay = '';
            }
            ?>
            <input type="text" name="check_number" style="<?php echo $CheckDisplay; ?>" autocomplete="off" class="form-control" value="<?php echo attr($CheckNumber); ?>" onKeyUp="ConvertToUpperCase(this)" id="check_number" class="form-control " />
            <div id="div_check_number" class="text border" style="width:140px;<?php echo $CheckDivDisplay; ?>">&nbsp;</div>
        </div>
    </div>
    <div class="row">
        <div class="forms col-3">
            <label class="control-label" for="payment_method"><?php echo xlt('Payment Amount'); ?>:</label>
            <input type="text" name="payment_amount" autocomplete="off" id="payment_amount" onchange="ValidateNumeric(this);<?php echo $screen == 'new_payment' ? 'FillUnappliedAmount();' : 'FillAmount();'; ?>" value="<?php echo ($screen == 'new_payment') ? attr('0.00') : attr($PayTotal); ?>" class="form-control text-right" />
        </div>
        <div class="forms col-3">
            <label class="control-label" for="type_name"><?php echo xlt('Paying Entity'); ?>:</label>
            <?php
            if ($PaymentType == '' && $screen == 'edit_payment') {
                $blankValue = ' ';
            } else {
                $blankValue = '';
            }
            echo generate_select_list("type_name", "payment_type", "$PaymentType", "Paying Entity", "$blankValue", "form-control", 'PayingEntityAction()');
            ?>
        </div>
        <div class="forms col-3">
            <label class="control-label" for="adjustment_code"><?php echo xlt('Payment Category'); ?>:</label>
            <?php
            if ($AdjustmentCode == '' && $screen == 'edit_payment') {
                $blankValue = ' ';
            } else {
                $blankValue = '';
            }
            echo generate_list_payment_category(
                "adjustment_code",
                "payment_adjustment_code",
                "$AdjustmentCode",
                "Payment Category",
                "$blankValue",
                "form-control",
                'FilterSelection(this)',
                "$PaymentType",
                "$screen"
            );
            ?>
        </div>
    </div>
    <div class="row">
        <div class="forms col-6">
            <label class="control-label" for="type_code"><?php echo xlt('Payment From'); ?>:</label>
            <input type="hidden" id="hidden_ajax_close_value" value="<?php echo attr($div_after_save); ?>" />
            <input name='type_code' id='type_code' type="text" class="form-control" onKeyDown="PreventIt(event)" value="<?php echo attr($div_after_save); ?>" autocomplete="off" />
            <!-- onKeyUp="ajaxFunction(event,'non','edit_payment.php');" -->
            <div id='ajax_div_insurance_section'>
                <div id='ajax_div_insurance_error'>
                </div>
                <div id="ajax_div_insurance" style="display: none;"></div>
            </div>
        </div>
        <div class="forms col-3">
            <label class="control-label" for="div_insurance_or_patient"><?php echo xlt('Payor ID'); ?>:</label>
            <!--<input class="form-control" type="text"  value = '<?php //echo attr($TypeCode);?>' name="div_insurance_or_patient" id="div_insurance_or_patient" placeholder="Payor ID…"  />-->
            <div name="div_insurance_or_patient" id="div_insurance_or_patient" class="form-control"><?php echo text($TypeCode); ?></div>
        </div>
    </div>
    <div class="row">
        <div class="forms col-2">
            <label class="control-label" for="deposit_date"><?php echo xlt('Deposit Date'); ?>:</label>
            <input type='text' class='form-control datepicker' name='deposit_date' id='deposit_date' onKeyDown="PreventIt(event)" value="<?php echo attr(oeFormatShortDate($DepositDate)); ?>" autocomplete="off" />
        </div>
        <div class="forms col-6">
            <label class="control-label" for="description"><?php echo xlt('Description'); ?>:</label>
            <input type="text" name="description" id="description" onKeyDown="PreventIt(event)" value="<?php echo attr($Description); ?>" class="form-control" />
        </div>
        <div class="forms col-2">
            <label class="control-label" for="GlobalReset"><?php echo xlt('Distributed to Global'); ?>:</label>
            <div class="input-group">
                <button class="input-group-prepend btn btn-secondary btn-delete" onclick="getElementById('GlobalReset').value='-0.00';this.classList.remove('btn-delete');event.target.classList.add('fa', 'fa-ban');">
                </button>
                <input id="GlobalReset" name="global_reset" class="form-control" value="<?php echo (($global_amount ?? null) * 1 === 0) ? attr("0.00") : attr(number_format(($global_amount ?? null), 2, '.', ',')); ?>" readonly />
            </div>
        </div>
        <div class="forms col-2">
            <label class="control-label" for="TdUnappliedAmount"><?php echo xlt('Undistributed'); ?>:</label>
            <div id="TdUnappliedAmount" class="form-control bg-danger text-light"><?php echo ($UndistributedAmount * 1 == 0) ? attr("0.00") : attr(number_format($UndistributedAmount, 2, '.', ',')); ?></div>
            <input name="HidUnappliedAmount" id="HidUnappliedAmount" value="<?php echo ($UndistributedAmount * 1 == 0) ? attr("0.00") : attr(number_format($UndistributedAmount, 2, '.', ',')); ?>" type="hidden" />
            <input name="HidUnpostedAmount" id="HidUnpostedAmount" value="<?php echo attr($UndistributedAmount); ?>" type="hidden" />
            <input name="HidCurrentPostedAmount" id="HidCurrentPostedAmount" value="" type="hidden" />
        </div>
    </div>
    <?php if ($screen == 'new_payment') { ?>
        <div class="form-group mt-3">
            <div class="row">
                <div class="col-sm-12 text-left position-override">
                    <div class="btn-group" role="group">
                        <button onClick="return SavePayment();" class="btn btn-primary btn-save"><?php echo xlt('Save Changes'); ?></button>
                        <button class="btn btn-primary btn-save" onClick="OpenEOBEntry();"><?php echo xlt('Allocate'); ?></button>
                        <button onclick="ResetForm(); return false;" class="btn btn-secondary btn-cancel"><?php echo xlt('Cancel Changes'); ?></button>
                        <br />
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    </fieldset><!--end of fieldset in edit-payment.php-->
    <?php
}//if(($screen=='new_payment' && $payment_id*1==0) || ($screen=='edit_payment' && $payment_id*1>0))
?>
<?php
if ($screen == 'new_payment' && $payment_id * 1 > 0) {//After saving from the New Payment screen,all values are  showed as labels.The date picker images are also removed.
    ?>
    <div class="col-12 h3">
        <?php echo xlt('Batch Payment Entry'); ?>
    </div>
    <div class="row">
        <div class="forms col-3">
            <label class="control-label" for="check_date"><?php echo xlt('Date'); ?>:</label>
            <input class="form-control" id='check_date' name='check_date' type='text' value="<?php echo attr(oeFormatShortDate($CheckDate)); ?> " disabled>
        </div>
        <div class="forms col-3">
            <label class="control-label" for="post_to_date"><?php echo xlt('Post To Date'); ?>:</label>
            <input class="form-control" id='post_to_date' name='post_to_date' type='text' value="<?php echo ($screen == 'new_payment') ? attr(oeFormatShortDate(date('Y-m-d'))) : attr(oeFormatShortDate($PostToDate)); ?>" disabled>
        </div>
        <div class="forms col-3">
            <label class="control-label" for="payment_method"><?php echo xlt('Payment Method'); ?>:</label>
            <input type="text" class="form-control" name="payment_method1" id="payment_method" value="<?php
            $list = 'payment_method';
            $option = $PaymentMethod;
            echo getListItemTitle($list, $option); ?>" disabled />
            <input type="hidden" name="payment_method" value="<?php echo attr($PaymentMethod); ?>" />
        </div>
        <div class="forms col-3">
            <label class="control-label" for="checknumber"><?php echo xlt('Check Number'); ?>:</label>
            <input type="text" class="form-control" name="check_number" id="checknumber" value="<?php echo attr($CheckNumber); ?>" disabled />
        </div>
    </div>
    <div class="row">
        <div class="forms col-3">
            <label class="control-label" for="payment_amount"><?php echo xlt('Payment Amount'); ?>:</label>
            <input type="text" class="form-control" name="payment_amount" id="payment_amount" value="<?php echo attr($PayTotal); ?>" disabled />
        </div>
        <div class="forms col-3">
            <label class="control-label" for="type_name"><?php echo xlt('Paying Entity'); ?>:</label>
            <input type="text" class="form-control" name="type_name1" id="type_name1" value="<?php
            $list = 'payment_type';
            $option = $PaymentType;
            echo getListItemTitle($list, $option); ?>" disabled />
            <input type="hidden" name="type_name" id="type_name" value="<?php echo attr($PaymentType); ?>" />
        </div>
        <div class="forms col-3">
            <label class="control-label" for="adjustment_code"><?php echo xlt('Payment Category'); ?>:</label>
            <input type="text" class="form-control" name="adjustment_code1" id="adjustment_code1" value="<?php
            $list = 'payment_adjustment_code';
            $option = $AdjustmentCode;
            echo getListItemTitle($list, $option); ?>" disabled />
            <input type="hidden" name="adjustment_code" value="<?php echo attr($AdjustmentCode); ?>" />
        </div>
    </div>
    <div class="row">
        <div class="forms col-6">
            <label class="control-label" for="div_insurance_or_patient"><?php echo xlt('Payment From'); ?>:</label>
            <input name='div_insurance_or_patient' id='div_insurance_or_patient' type="text" class="form-control" value="<?php echo attr($div_after_save); ?>" disabled />
        </div>
        <div class="forms col-3">
            <label class="control-label" for="type_code"><?php echo xlt('Payor ID'); ?>:</label>
            <input type="text" name="type_code" id="type_code" class="form-control" value="<?php echo attr($TypeCode); ?>" disabled />
        </div>
    </div>
    <div class="row oe-custom-line">
        <div class="forms col-2">
            <label class="control-label" for="deposit_date"><?php echo xlt('Deposit Date'); ?>:</label>
            <input type="text" class='form-control' name="deposit_date" id="deposit_date" value="<?php echo attr(oeFormatShortDate($DepositDate)); ?>" disabled />
        </div>
        <div class="forms col-6">
            <label class="control-label" for="description"><?php echo xlt('Description'); ?>:</label>
            <input type="text" name="description" id="description" value="<?php echo attr($Description); ?>" class="form-control" disabled />
        </div>
        <div class="forms col-2">
            <label class="control-label" for="GlobalResetView"><?php echo xlt('Distributed to Global'); ?>:</label>
            <input id="GlobalResetView" name="global_reset_view" class="form-control" value="<?php echo ($global_amount * 1 == 0) ? attr("0.00") : attr(number_format($global_amount, 2, '.', ',')); ?>" disabled />
        </div>
        <div class="forms col-2">
            <label class="control-label" for="TdUnappliedAmount"><?php echo xlt('Undistributed'); ?>:</label>
            <div id="TdUnappliedAmount" class="form-control bg-danger text-light"><?php echo ($UndistributedAmount * 1 == 0) ? attr("0.00") : attr(number_format($UndistributedAmount, 2, '.', ',')); ?></div>
            <input name="HidUnappliedAmount" id="HidUnappliedAmount" value="<?php echo ($UndistributedAmount * 1 == 0) ? attr("0.00") : attr(number_format($UndistributedAmount, 2, '.', ',')); ?>" type="hidden" />
            <input name="HidUnpostedAmount" id="HidUnpostedAmount" value="<?php echo attr($UndistributedAmount); ?>" type="hidden" />
            <input name="HidCurrentPostedAmount" id="HidCurrentPostedAmount" value="" type="hidden" />
        </div>
    </div>
    </fieldset><!--end of fieldset in new_payment.php -->
    <?php
}//if($screen=='new_payment' && $payment_id*1>0)
//================================================================================================
?>
