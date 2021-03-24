<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Paul Simon K <paul@zhservices.com>
//
// +------------------------------------------------------------------------------+
//===============================================================================
//This section handles payment related javascript functios.Add, Search and Edit screen uses these functions.
//===============================================================================
?>
<script>
    function CheckVisible(MakeBlank) {//Displays and hides the check number text box.Add and edit page uses the same function.
                                      //In edit its value should not be lost on just a change.It is controlled be the 'MakeBlank' argument.
        if (document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value == 'check_payment' ||
            document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value == 'bank_draft') {
            document.getElementById('div_check_number').style.display = 'none';
            document.getElementById('check_number').style.display = '';
        } else {
            document.getElementById('div_check_number').style.display = '';
            if (MakeBlank == 'yes') {//In Add page clearing the field is done.
                document.getElementById('check_number').value = '';
            }
            document.getElementById('check_number').style.display = 'none';
        }
    }

    function PayingEntityAction() {
        //Which ajax is to be active(patient,insurance), is decided by the 'Paying Entity' drop down, where this function is called.
        //So on changing some initialization is need.Done below.
        document.getElementById('type_code').value = '';
        document.getElementById('hidden_ajax_close_value').value = '';
        document.getElementById('hidden_type_code').value = '';
        document.getElementById('div_insurance_or_patient').innerHTML = '&nbsp;';
        document.getElementById('description').value = '';
        if (document.getElementById('ajax_div_insurance')) {
            $("#ajax_div_patient_error").empty();
            $("#ajax_div_patient").empty();
            $("#ajax_div_insurance_error").empty();
            $("#ajax_div_insurance").empty();
            $("#ajax_div_insurance").hide();
            document.getElementById('payment_method').style.display = '';
        }
        //As per the selected value, one value is selected in the 'Payment Category' drop down.
        if (document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value == 'patient') {
            document.getElementById('adjustment_code').value = 'patient_payment';
        } else if (document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value == 'insurance') {
            document.getElementById('adjustment_code').value = 'insurance_payment';
        }
        //As per the selected value, certain values are not selectable in the 'Payment Category' drop down.They are greyed out.
        var list = document.getElementById('type_name');
        var newValue = (list.options[list.selectedIndex].value);
        if (newValue == 'patient') {
            if (document.getElementById('option_insurance_payment'))
                document.getElementById('option_insurance_payment').style.backgroundColor = '#DEDEDE';
            if (document.getElementById('option_family_payment'))
                document.getElementById('option_family_payment').style.backgroundColor = 'var(--white)';
            if (document.getElementById('option_patient_payment'))
                document.getElementById('option_patient_payment').style.backgroundColor = 'var(--white)';
        }
        if (newValue == 'insurance') {
            if (document.getElementById('option_family_payment'))
                document.getElementById('option_family_payment').style.backgroundColor = '#DEDEDE';
            if (document.getElementById('option_patient_payment'))
                document.getElementById('option_patient_payment').style.backgroundColor = '#DEDEDE';
            if (document.getElementById('option_insurance_payment'))
                document.getElementById('option_insurance_payment').style.backgroundColor = 'var(--white)';
        }
    }

    function FilterSelection(listSelected) {
        //function PayingEntityAction() greyed out certain values as per the selection in the 'Paying Entity' drop down.
        //When the same are selected in the 'Payment Category' drop down, this function reverts back to the old value.
        let ValueToPut = "";
        if (document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value == 'patient') {
            ValueToPut = 'patient_payment';
        } else if (document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value == 'insurance') {
            ValueToPut = 'insurance_payment';
        }

        let newValueSelected = (listSelected.options[listSelected.selectedIndex].value);

        let list = document.getElementById('type_name');
        let newValue = (list.options[list.selectedIndex].value);
        if (newValue == 'patient') {
            if (newValueSelected == 'insurance_payment')
                listSelected.value = ValueToPut;//Putting values back
        }
        if (newValue == 'insurance') {
            if (newValueSelected == 'family_payment')
                listSelected.value = ValueToPut;
            if (newValueSelected == 'patient_payment')
                listSelected.value = ValueToPut;//Putting values back
        }
    }

    function RestoreValues(CountIndex) {
        //old remainder is restored back
        if (document.getElementById('Allowed' + CountIndex).value * 1 === 0 &&
            document.getElementById('Payment' + CountIndex).value * 1 === 0 &&
            document.getElementById('AdjAmount' + CountIndex).value * 1 === 0 &&
            document.getElementById('Takeback' + CountIndex).value * 1 === 0) {
            document.getElementById('RemainderTd' + CountIndex).innerHTML = document.getElementById('HiddenRemainderTd' + CountIndex).value * 1
        }
    }

    function ActionFollowUp(CountIndex) {//Activating or deactivating the FollowUpReason text box.
        if (document.getElementById('FollowUp' + CountIndex).checked) {
            document.getElementById('FollowUpReason' + CountIndex).readOnly = false;
            document.getElementById('FollowUpReason' + CountIndex).value = '';
        } else {
            document.getElementById('FollowUpReason' + CountIndex).value = '';
            document.getElementById('FollowUpReason' + CountIndex).readOnly = true;
        }
    }

    function ValidateDateGreaterThanNow(DateValue, DateFormat) {//Validate whether the date is greater than now.The 3 formats of date is taken care of.
        let DateValueArray = [];
        if (DateFormat == '%Y-%m-%d') {
            DateValueArray = DateValue.split('-');
            DateValue = DateValueArray[1] + '/' + DateValueArray[2] + '/' + DateValueArray[0];
        } else if (DateFormat == '%m/%d/%Y') {
        } else if (DateFormat == '%d/%m/%Y') {
            DateValueArray = DateValue.split('/');
            DateValue = DateValueArray[1] + '/' + DateValueArray[0] + '/' + DateValueArray[2];
        }
        let PassedDate = new Date(DateValue);
        let Now = new Date();
        return PassedDate <= Now;
    }

    function DateCheckGreater(DateValue1, DateValue2, DateFormat) {//Checks which date is greater.The 3 formats of date is taken care of.
        let DateValueArray = [];
        if (DateFormat == '%Y-%m-%d') {
            DateValueArray = DateValue1.split('-');
            DateValue1 = DateValueArray[1] + '/' + DateValueArray[2] + '/' + DateValueArray[0];
            DateValueArray = DateValue2.split('-');
            DateValue2 = DateValueArray[1] + '/' + DateValueArray[2] + '/' + DateValueArray[0];
        } else if (DateFormat == '%m/%d/%Y') {
        } else if (DateFormat == '%d/%m/%Y') {
            DateValueArray = DateValue1.split('/');
            DateValue1 = DateValueArray[1] + '/' + DateValueArray[0] + '/' + DateValueArray[2];
            DateValueArray = DateValue2.split('/');
            DateValue2 = DateValueArray[1] + '/' + DateValueArray[0] + '/' + DateValueArray[2];
        }
        let PassedDateValue1 = new Date(DateValue1);
        let PassedDateValue2 = new Date(DateValue2);
        if (PassedDateValue1 <= PassedDateValue2)
            return true;
        else
            return false;
    }

    function ConvertToUpperCase(ObjectPassed) {//Convert To Upper Case.Example:- onKeyUp="ConvertToUpperCase(this)".
        ObjectPassed.value = ObjectPassed.value.toUpperCase();
    }

    //--------------------------------
    function SearchOnceMore() {//Used in the option buttons,listing the charges.
                               //'Non Paid', 'Show Primary Complete', 'Show All Transactions' uses this when a patient is selected through ajax.
        if (document.getElementById('hidden_patient_code').value * 1 > 0) {
            document.getElementById('mode').value = 'search';
            top.restoreSession();
            document.forms[0].submit();
        } else {
            alert("<?php echo htmlspecialchars(xl('Please Select a Patient.'), ENT_QUOTES) ?>")
        }
    }

    function CheckUnappliedAmount() {//The value retured from here decides whether Payments can be posted/modified or not.
        let UnappliedAmount = document.getElementById('TdUnappliedAmount').innerHTML * 1;
        if (UnappliedAmount < 0) {
            return 1;
        } else if (UnappliedAmount > 0) {
            return 2;
        } else {
            return 3;
        }
    }

    function ValidateNumeric(TheObject) {
        //Numeric validations, used while typing numbers.
        // Take into account comma currency numbers and allow.
        if (isNaN(formatNumber(TheObject.value))) {
            alert("<?php echo htmlspecialchars(xl('Value Should be Numeric'), ENT_QUOTES) ?>");
            TheObject.focus();
            return false;
        }
    }

    function SavePayment() {//Used before saving.
        if (FormValidations())//FormValidations contains the form checks
        {
            if (confirm("<?php echo htmlspecialchars(xl('Would you like to save?'), ENT_QUOTES) ?>")) {
                top.restoreSession();
                document.getElementById('mode').value = 'new_payment';
                document.forms[0].submit();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function OpenEOBEntry() {//Used before allocating the recieved amount.
        if (FormValidations())//FormValidations contains the form checks
        {
            top.restoreSession();
            document.getElementById('mode').value = 'distribute';
            document.forms[0].submit();
        } else {
            return false;
        }
    }

    function ScreenAdjustment(PassedObject, CountIndex) {
        //Called when there is change in the amount by typing.
        //Readjusts the various values.Another function FillAmount() is also used.
        //Ins1 case and allowed is filled means it is primary's first payment.
        //It moves to secondary or patient balance.
        //If primary again pays means ==>change Post For to Ins1 and do not enter any value in the allowed box.
        //
        let Allowed = formatNumber(document.getElementById('Allowed' + CountIndex).value);
        if (document.getElementById('Allowed' + CountIndex).id === PassedObject.id) {
            document.getElementById('Payment' + CountIndex).value = Allowed;
        }
        let Payment = formatNumber(document.getElementById('Payment' + CountIndex).value * 1);
        let ChargeAmount = formatNumber(document.getElementById('HiddenChargeAmount' + CountIndex).value * 1);
        let Remainder = formatNumber(document.getElementById('HiddenRemainderTd' + CountIndex).value * 1);
        if (document.getElementById('Allowed' + CountIndex).id === PassedObject.id) {
            if (document.getElementById('HiddenIns' + CountIndex).value === 1) {
                document.getElementById('AdjAmount' + CountIndex).value = Math.round((ChargeAmount - Allowed) * 100) / 100;
            } else {
                document.getElementById('AdjAmount' + CountIndex).value = Math.round((Remainder - Allowed) * 100) / 100;
            }
        }
        let AdjustmentAmount = formatNumber(document.getElementById('AdjAmount' + CountIndex).value * 1);
        let CopayAmount = formatNumber(document.getElementById('HiddenCopayAmount' + CountIndex).value * 1);
        let Takeback = formatNumber(document.getElementById('Takeback' + CountIndex).value * 1);
        if (document.getElementById('HiddenIns' + CountIndex).value === 1 && Allowed !== 0) {//Means it is primary's first payment.
            document.getElementById('RemainderTd' + CountIndex).innerHTML = Math.round((ChargeAmount - AdjustmentAmount - CopayAmount - Payment + Takeback) * 100) / 100;
        } else {//All other case.
            document.getElementById('RemainderTd' + CountIndex).innerHTML = Math.round((Remainder - AdjustmentAmount - Payment + Takeback) * 100) / 100;
        }
        FillAmount();
    }

    function FillAmount() {
        //Called when there is change in the amount by typing.
        //Readjusts the various values.
        let UnpostedAmt = 0;
        <?php
        if (!empty($screen) && ($screen == 'new_payment')) { ?>
        UnpostedAmt = formatNumber(document.getElementById('HidUnpostedAmount').value * 1);
            <?php
        } else { ?>
        UnpostedAmt = formatNumber(document.getElementById('payment_amount').value * 1);
        <?php } ?>

        let TempTotal = 0;
        let RowCount, Takeback, thisPayment;
        for (RowCount = 1; ; RowCount++) {
            if (!document.getElementById('Payment' + RowCount))
                break;
            else {
                Takeback = formatNumber(document.getElementById('Takeback' + RowCount).value * 1);
                thisPayment = formatNumber(document.getElementById('Payment' + RowCount).value * 1)
                TempTotal = Math.round((TempTotal + thisPayment - Takeback) * 100) / 100;
            }
        }
        document.getElementById('TdUnappliedAmount').innerHTML = Math.round((UnpostedAmt - TempTotal) * 100) / 100;
        document.getElementById('HidUnappliedAmount').value = Math.round((UnpostedAmt - TempTotal) * 100) / 100;
        document.getElementById('HidCurrentPostedAmount').value = TempTotal;
    }

    function ActionOnInsPat(CountIndex) {//Called when there is onchange in the Ins/Pat drop down.
        let InsPatDropDownValue = document.getElementById('payment_ins' + CountIndex).options[document.getElementById('payment_ins' + CountIndex).selectedIndex].value;
        document.getElementById('HiddenIns' + CountIndex).value = InsPatDropDownValue;
        InsPatDropDownValue = parseInt(InsPatDropDownValue);
        if (InsPatDropDownValue === 1) {
            document.getElementById('trCharges' + CountIndex).bgColor = '#ddddff';
        } else if (InsPatDropDownValue === 2) {
            document.getElementById('trCharges' + CountIndex).bgColor = '#ffdddd';
        } else if (InsPatDropDownValue === 3) {
            document.getElementById('trCharges' + CountIndex).bgColor = '#F2F1BC';
        } else if (InsPatDropDownValue === 0) {
            document.getElementById('trCharges' + CountIndex).bgColor = '#AAFFFF';
        }
    }

    function CheckPayingEntityAndDistributionPostFor() {//Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
        let PayingEntity = document.getElementById('type_name').options ? document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value : document.getElementById('type_name').value;
        let CountIndexAbove = 0;
        let InsPatDropDownValue, RowCount;
        for (RowCount = CountIndexAbove + 1; ; RowCount++) {
            if (!document.getElementById('Payment' + RowCount))
                break;
            else if (document.getElementById('Allowed' + RowCount).value === '' && document.getElementById('Payment' + RowCount).value === '' && document.getElementById('AdjAmount' + RowCount).value === '' && document.getElementById('Deductible' + RowCount).value === '' && document.getElementById('Takeback' + RowCount).value === '' && document.getElementById('FollowUp' + RowCount).checked === false) {
            } else {
                InsPatDropDownValue = document.getElementById('payment_ins' + RowCount).options[document.getElementById('payment_ins' + RowCount).selectedIndex].value;
                if (PayingEntity == 'patient' && InsPatDropDownValue > 0) {
                    alert("<?php echo htmlspecialchars(xl('Cannot Post for Insurance.The Paying Entity selected is Patient.'), ENT_QUOTES) ?>");
                    return false;
                } else if (PayingEntity == 'insurance' && InsPatDropDownValue == 0) {
                    alert("<?php echo htmlspecialchars(xl('Cannot Post for Patient.The Paying Entity selected is Insurance.'), ENT_QUOTES) ?>");
                    return false;
                }
            }
        }
        return true;
    }

    function FormValidations() {//Screen validations are done here.
        if (document.getElementById('check_date').value == '') {
            let message = <?php echo xlj('Please Fill the Date') ?>;
            // a good use of syncAlertMsg when a promise or an await (then({})) with actions and/or
            // for an alert to time out, is not needed. et al validation alerts.
            syncAlertMsg('<h4 class="bg-light text-danger">'+message+'</h4>', 1500, 'warning', 'lg');
            document.getElementById('check_date').focus();
            return false;
        } else if (!ValidateDateGreaterThanNow(document.getElementById('check_date').value, '<?php echo DateFormatRead();?>')) {
            let message = <?php echo xlj('Date Cannot be greater than Today') ?>;
            syncAlertMsg('<h4 class="bg-light text-danger">'+message+'</h4>', 1500, 'warning', 'lg');
            document.getElementById('check_date').focus();
            return false;
        }
        if (document.getElementById('post_to_date').value == '') {
            let message = <?php echo xlj('Please Fill the Post To Date') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('post_to_date').focus();
            return false;
        } else if (!ValidateDateGreaterThanNow(document.getElementById('post_to_date').value, '<?php echo DateFormatRead();?>')) {
            let message = <?php echo xlj('Post To Date Cannot be greater than Today') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('post_to_date').focus();
            return false;
        } else if (DateCheckGreater(document.getElementById('post_to_date').value, '<?php echo $GLOBALS['post_to_date_benchmark'] == '' ? date('Y-m-d', time() - (10 * 24 * 60 * 60)) : htmlspecialchars(oeFormatShortDate($GLOBALS['post_to_date_benchmark']));?>',
            '<?php echo DateFormatRead();?>')) {
            let message = <?php echo xlj('Post To Date must be greater than the financial close date.') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('post_to_date').focus();
            return false;
        }
        if (((document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value == 'check_payment' ||
            document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value == 'bank_draft') &&
            document.getElementById('check_number').value == '')) {
            let message = <?php echo xlj('Please Fill the Check Number') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('check_number').focus();
            return false;
        }
        <?php
        if (!empty($screen) && ($screen == 'edit_payment')) {
            ?>
        if (document.getElementById('check_number').value != '' &&
            document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value == '') {
            let message = <?php echo xlj('Please Select the Payment Method') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('payment_method').focus();
            return false;
        }
            <?php
        }
        ?>
        if (document.getElementById('payment_amount').value == '') {
            let message = <?php echo xlj('Please Fill the Payment Amount') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('payment_amount').focus();
            return false;
        }
        if (document.getElementById('payment_amount').value != document.getElementById('payment_amount').value * 1) {
            let message = <?php echo xlj('Payment Amount must be Numeric') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('payment_amount').focus();
            return false;
        }
        <?php
        if (!empty($screen) && ($screen == 'edit_payment')) {
            ?>
        if (document.getElementById('adjustment_code').options[document.getElementById('adjustment_code').selectedIndex].value == '') {
            let message = <?php echo xlj('Please Fill the Payment Category') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('adjustment_code').focus();
            return false;
        }
            <?php
        }
        ?>
        if (document.getElementById('type_code').value == '') {
            let message = <?php echo xlj('Please Fill the Payment From') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('type_code').focus();
            return false;
        }
        if (document.getElementById('hidden_type_code').value != document.getElementById('div_insurance_or_patient').innerHTML) {
            let message = <?php echo xlj('Take Payment From, from Drop Down')?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('type_code').focus();
            return false;
        }
        if (document.getElementById('deposit_date').value == '') {
        } else if (!ValidateDateGreaterThanNow(document.getElementById('deposit_date').value, '<?php echo DateFormatRead();?>')) {
            let message = <?php echo xlj('Deposit Date Cannot be greater than Today') ?>;
            (async (message, time) => {
                await asyncAlertMsg(message, time, 'warning', 'lg');
            })(message, 1500).then(res => {
            });
            document.getElementById('deposit_date').focus();
            return false;
        }
        return true;
    }

    //========================================================================================
    function UpdateTotalValues(start, count, Payment, PaymentTotal) {
        //Used in totaling the columns.
        var paymenttot = 0;
        if (count > 0) {
            let tmpVal = 0.00;
            for (i = start; i < start + count; i++) {
                if (document.getElementById(Payment + i)) {
                    tmpVal = formatNumber(document.getElementById(Payment + i).value);
                    paymenttot = paymenttot + tmpVal;
                }
            }
            document.getElementById(PaymentTotal).innerHTML = Math.round((paymenttot) * 100) / 100;
        }
    }

    function formatNumber(sNum) {
        let fNum = 0.00;
        if (isNaN(sNum)) {
            fNum = parseFloat(sNum.replace(/,/g, '')) * 1;
        } else {
            fNum = (sNum * 1);
        }

        if(isNaN(fNum)) {
            return fNum;
        }

        return fNum;
    }
    /*
    * Just to ensure our in screen calculations are up to date from value fetches.
    *  Start from AdjAmount otherwise ajustments will reset for 0 balance auto's.
    *
    * return awaited promise.
    * */
    async function ScreenAdjustmentAll($TotalRows) {
        return await new Promise((resolve, reject) => {
            try {
                let PassedObject, CountIndex = 0;
                for (CountIndex = 1; CountIndex <= $TotalRows; CountIndex++) {
                    PassedObject = document.getElementById('AdjAmount' + CountIndex) ?? null;
                    if (PassedObject !== null) {
                        ScreenAdjustment(PassedObject, CountIndex);
                    }
                }
                resolve(true);
            } catch (e) {
                reject(e.message);
            }
        });
    }

    /*
    * Recalculate totals from form items on startup
    * Include row item calculations.
    *
    * This function is an async/await in case used for various billing environments.
    * */
    async function updateAllFormTotals($TotalRows) {
        $TotalRows = $TotalRows * 1;
        // Do our row items.
        // ScreenAdjust must complete first for valid totals
        await ScreenAdjustmentAll($TotalRows).then(imFullfilled => {
            // Now our totals..
            if(imFullfilled === true) {
                UpdateTotalValues(1, $TotalRows, 'Allowed', 'allowtotal');
                UpdateTotalValues(1, $TotalRows, 'Payment', 'paymenttotal');
                UpdateTotalValues(1, $TotalRows, 'AdjAmount', 'AdjAmounttotal');
                UpdateTotalValues(1, $TotalRows, 'Deductible', 'deductibletotal');
                UpdateTotalValues(1, $TotalRows, 'Takeback', 'takebacktotal');
            } else {
                alert("error " + e.message);
            }
        });

        return false;
    }

</script>
