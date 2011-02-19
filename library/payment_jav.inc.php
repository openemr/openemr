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
<script type="text/javascript">
function CheckVisible(MakeBlank)
 {//Displays and hides the check number text box.Add and edit page uses the same function.
 //In edit its value should not be lost on just a change.It is controlled be the 'MakeBlank' argument.
   if(document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value=='check_payment' ||
   	  document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value=='bank_draft'  )
   {
	document.getElementById('div_check_number').style.display='none';
	document.getElementById('check_number').style.display='';
   }
   else
   {
	document.getElementById('div_check_number').style.display='';
	if(MakeBlank=='yes')
	 {//In Add page clearing the field is done.
		document.getElementById('check_number').value='';
	 }
	document.getElementById('check_number').style.display='none';
   }
 }
function PayingEntityAction()
 {
  //Which ajax is to be active(patient,insurance), is decided by the 'Paying Entity' drop down, where this function is called.
  //So on changing some initialization is need.Done below.
  document.getElementById('type_code').value='';
  document.getElementById('hidden_ajax_close_value').value='';
  document.getElementById('hidden_type_code').value='';
  document.getElementById('div_insurance_or_patient').innerHTML='&nbsp;';
  document.getElementById('description').value='';
  if(document.getElementById('ajax_div_insurance'))
   {
	 $("#ajax_div_patient_error").empty();
	 $("#ajax_div_patient").empty();
	 $("#ajax_div_insurance_error").empty();
	 $("#ajax_div_insurance").empty();
	 $("#ajax_div_insurance").hide();
	  document.getElementById('payment_method').style.display='';
   }
	//As per the selected value, one value is selected in the 'Payment Category' drop down.
	if(document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value=='patient')
	 {
	  document.getElementById('adjustment_code').value='patient_payment';
	 }
	else if(document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value=='insurance')
	 {
	  document.getElementById('adjustment_code').value='insurance_payment';
	 }
	//As per the selected value, certain values are not selectable in the 'Payment Category' drop down.They are greyed out.
	var list=document.getElementById('type_name');
    var newValue = (list.options[list.selectedIndex].value);
    if (newValue=='patient') {
        if(document.getElementById('option_insurance_payment'))
        	document.getElementById('option_insurance_payment').style.backgroundColor='#DEDEDE';
        if(document.getElementById('option_family_payment'))
        	document.getElementById('option_family_payment').style.backgroundColor='#ffffff';
        if(document.getElementById('option_patient_payment'))
        	document.getElementById('option_patient_payment').style.backgroundColor='#ffffff';
    }
    if (newValue=='insurance') {
        if(document.getElementById('option_family_payment'))
        	document.getElementById('option_family_payment').style.backgroundColor='#DEDEDE';
        if(document.getElementById('option_patient_payment'))
        	document.getElementById('option_patient_payment').style.backgroundColor='#DEDEDE';
        if(document.getElementById('option_insurance_payment'))
        	document.getElementById('option_insurance_payment').style.backgroundColor='#ffffff';
    }
 }
function FilterSelection(listSelected) {
	//function PayingEntityAction() greyed out certain values as per the selection in the 'Paying Entity' drop down.
	//When the same are selected in the 'Payment Category' drop down, this function reverts back to the old value.
	if(document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value=='patient')
	 {
	  ValueToPut='patient_payment';
	 }
	else if(document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value=='insurance')
	 {
	  ValueToPut='insurance_payment';
	 }

    var newValueSelected = (listSelected.options[listSelected.selectedIndex].value);
	
	var list=document.getElementById('type_name');
    var newValue = (list.options[list.selectedIndex].value);
    if (newValue=='patient') {
        if(newValueSelected=='insurance_payment')
        	listSelected.value=ValueToPut;//Putting values back
    }
    if (newValue=='insurance') {
        if(newValueSelected=='family_payment')
        	listSelected.value=ValueToPut;
        if(newValueSelected=='patient_payment')
        	listSelected.value=ValueToPut;//Putting values back
    }
}
function RestoreValues(CountIndex)
 {//old remainder is restored back
   if(document.getElementById('Allowed'+CountIndex).value*1==0 && document.getElementById('Payment'+CountIndex).value*1==0 && document.getElementById('AdjAmount'+CountIndex).value*1==0 && document.getElementById('Takeback'+CountIndex).value*1==0)
    {
	 document.getElementById('RemainderTd'+CountIndex).innerHTML=document.getElementById('HiddenRemainderTd'+CountIndex).value*1
	}
 }
function ActionFollowUp(CountIndex)
 {//Activating or deactivating the FollowUpReason text box.
  if(document.getElementById('FollowUp'+CountIndex).checked)
   {
    document.getElementById('FollowUpReason'+CountIndex).readOnly=false;
    document.getElementById('FollowUpReason'+CountIndex).value='';
   }
  else
   {
    document.getElementById('FollowUpReason'+CountIndex).value='';
    document.getElementById('FollowUpReason'+CountIndex).readOnly=true;
   }
 }
function ValidateDateGreaterThanNow(DateValue,DateFormat)
 {//Validate whether the date is greater than now.The 3 formats of date is taken care of.
  if(DateFormat=='%Y-%m-%d')
   {
    DateValueArray=DateValue.split('-');
	DateValue=DateValueArray[1]+'/'+DateValueArray[2]+'/'+DateValueArray[0];
   }
  else if(DateFormat=='%m/%d/%Y')
   {
   }
  else if(DateFormat=='%d/%m/%Y')
   {
    DateValueArray=DateValue.split('/');
	DateValue=DateValueArray[1]+'/'+DateValueArray[0]+'/'+DateValueArray[2];
   }
  PassedDate = new Date(DateValue);
  Now = new Date();
  if(PassedDate > Now)
   return false;
  else
   return true; 
 }
function DateCheckGreater(DateValue1,DateValue2,DateFormat)
 {//Checks which date is greater.The 3 formats of date is taken care of.
  if(DateFormat=='%Y-%m-%d')
   {
    DateValueArray=DateValue1.split('-');
	DateValue1=DateValueArray[1]+'/'+DateValueArray[2]+'/'+DateValueArray[0];
    DateValueArray=DateValue2.split('-');
	DateValue2=DateValueArray[1]+'/'+DateValueArray[2]+'/'+DateValueArray[0];
   }
  else if(DateFormat=='%m/%d/%Y')
   {
   }
  else if(DateFormat=='%d/%m/%Y')
   {
    DateValueArray=DateValue1.split('/');
	DateValue1=DateValueArray[1]+'/'+DateValueArray[0]+'/'+DateValueArray[2];
    DateValueArray=DateValue2.split('/');
	DateValue2=DateValueArray[1]+'/'+DateValueArray[0]+'/'+DateValueArray[2];
   }
  PassedDateValue1 = new Date(DateValue1);
  PassedDateValue2 = new Date(DateValue2);
  if(PassedDateValue1 <= PassedDateValue2)
   return true;
  else
   return false;
 }
function ConvertToUpperCase(ObjectPassed)
 {//Convert To Upper Case.Example:- onKeyUp="ConvertToUpperCase(this)".
  ObjectPassed.value=ObjectPassed.value.toUpperCase();
 }
 //--------------------------------
 function SearchOnceMore()
 {//Used in the option buttons,listing the charges.
 //'Non Paid', 'Show Primary Complete', 'Show All Transactions' uses this when a patient is selected through ajax.
	if(document.getElementById('hidden_patient_code').value*1>0)
	 {
		document.getElementById('mode').value='search';
		top.restoreSession();
		document.forms[0].submit();
	 }
	else
	 {
		alert("<?php echo htmlspecialchars( xl('Please Select a Patient.'), ENT_QUOTES) ?>")
	 }
 }
function CheckUnappliedAmount()
 {//The value retured from here decides whether Payments can be posted/modified or not.
  UnappliedAmount=document.getElementById('TdUnappliedAmount').innerHTML*1;
  if(UnappliedAmount<0)
   {
    return 1;
   }
  else if(UnappliedAmount>0)
   {
    return 2;
   }
  else
   {
    return 3;
   }
 }
function ValidateNumeric(TheObject)
 {//Numeric validations, used while typing numbers.
  if(TheObject.value!=TheObject.value*1)
   {
    alert("<?php echo htmlspecialchars( xl('Value Should be Numeric'), ENT_QUOTES) ?>");
	TheObject.focus();
	return false;
   }
 }
function SavePayment()
 {//Used before saving.
 	if(FormValidations())//FormValidations contains the form checks
	 {
		if(confirm("<?php echo htmlspecialchars( xl('Would you like to save?'), ENT_QUOTES) ?>"))
		 {
			top.restoreSession();
			document.getElementById('mode').value='new_payment';
			document.forms[0].submit();
		 }
		else
		 return false;
	 }
	else
	 return false;
 }
function OpenEOBEntry()
 {//Used before allocating the recieved amount.
 	if(FormValidations())//FormValidations contains the form checks
	 {
		if(confirm("<?php echo htmlspecialchars( xl('Would you like to Allocate?'), ENT_QUOTES) ?>"))
		 {
			top.restoreSession();
			document.getElementById('mode').value='distribute';
			document.forms[0].submit();
		 }
		else
		 return false;
	 }
	else
	 return false;
 }
function ScreenAdjustment(PassedObject,CountIndex)
 {//Called when there is change in the amount by typing.
 //Readjusts the various values.Another function FillAmount() is also used.
 //Ins1 case and allowed is filled means it is primary's first payment.
 //It moves to secondary or patient balance.
 //If primary again pays means ==>change Post For to Ins1 and do not enter any value in the allowed box.
  Allowed=document.getElementById('Allowed'+CountIndex).value*1;
  if(document.getElementById('Allowed'+CountIndex).id==PassedObject.id)
   {
	  document.getElementById('Payment'+CountIndex).value=Allowed;
   }
  Payment=document.getElementById('Payment'+CountIndex).value*1;
  ChargeAmount=document.getElementById('HiddenChargeAmount'+CountIndex).value*1;
  Remainder=document.getElementById('HiddenRemainderTd'+CountIndex).value*1;
  if(document.getElementById('Allowed'+CountIndex).id==PassedObject.id)
   {
	  if(document.getElementById('HiddenIns'+CountIndex).value==1)
	   {
		  document.getElementById('AdjAmount'+CountIndex).value=Math.round((ChargeAmount-Allowed)*100)/100;
	   }
	  else
	   {
		  document.getElementById('AdjAmount'+CountIndex).value=Math.round((Remainder-Allowed)*100)/100;
	   }
   }
  AdjustmentAmount=document.getElementById('AdjAmount'+CountIndex).value*1;
  CopayAmount=document.getElementById('HiddenCopayAmount'+CountIndex).value*1;
  Takeback=document.getElementById('Takeback'+CountIndex).value*1;
  if(document.getElementById('HiddenIns'+CountIndex).value==1 && Allowed!=0)
   {//Means it is primary's first payment.
	  document.getElementById('RemainderTd'+CountIndex).innerHTML=Math.round((ChargeAmount-AdjustmentAmount-CopayAmount-Payment+Takeback)*100)/100;
   }
  else
   {//All other case.
	  document.getElementById('RemainderTd'+CountIndex).innerHTML=Math.round((Remainder-AdjustmentAmount-Payment+Takeback)*100)/100;
   }
  FillAmount();
 }
function FillAmount()
 {//Called when there is change in the amount by typing.
 //Readjusts the various values.
  <?php 
  if($screen=='new_payment')
   {
  ?>
  	UnpostedAmt=document.getElementById('HidUnpostedAmount').value*1;
  <?php 
   }
  else
   {
  ?>
  	UnpostedAmt=document.getElementById('payment_amount').value*1;
  <?php 
   }
  ?>
  
  TempTotal=0;
  for(RowCount=1;;RowCount++)
   {
	  if(!document.getElementById('Payment'+RowCount))
	   break;
	  else
	   {
		 Takeback=document.getElementById('Takeback'+RowCount).value*1;
		 TempTotal=Math.round((TempTotal+document.getElementById('Payment'+RowCount).value*1-Takeback)*100)/100;
	   }
   }
  document.getElementById('TdUnappliedAmount').innerHTML=Math.round((UnpostedAmt-TempTotal)*100)/100;
  document.getElementById('HidUnappliedAmount').value=Math.round((UnpostedAmt-TempTotal)*100)/100;
  document.getElementById('HidCurrentPostedAmount').value=TempTotal;
 }
function ActionOnInsPat(CountIndex)
 {//Called when there is onchange in the Ins/Pat drop down.
 	InsPatDropDownValue=document.getElementById('payment_ins'+CountIndex).options[document.getElementById('payment_ins'+CountIndex).selectedIndex].value;
 	document.getElementById('HiddenIns'+CountIndex).value=InsPatDropDownValue;
	if(InsPatDropDownValue==1)
	 {
	  document.getElementById('trCharges'+CountIndex).bgColor='#ddddff';
	 }
	else if(InsPatDropDownValue==2)
	 {
	  document.getElementById('trCharges'+CountIndex).bgColor='#ffdddd';
	 }
	else if(InsPatDropDownValue==3)
	 {
	  document.getElementById('trCharges'+CountIndex).bgColor='#F2F1BC';
	 }
	else if(InsPatDropDownValue==0)
	 {
	  document.getElementById('trCharges'+CountIndex).bgColor='#AAFFFF';
	 }
 }
function CheckPayingEntityAndDistributionPostFor()
 {//Ensures that Insurance payment is distributed under Ins1,Ins2,Ins3 and Patient paymentat under Pat.
	PayingEntity=document.getElementById('type_name').options?document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value:document.getElementById('type_name').value;
	CountIndexAbove=0;
	for(RowCount=CountIndexAbove+1;;RowCount++)
	 {
	  if(!document.getElementById('Payment'+RowCount))
	   break;
	  else if(document.getElementById('Allowed'+RowCount).value=='' && document.getElementById('Payment'+RowCount).value=='' && document.getElementById('AdjAmount'+RowCount).value=='' && document.getElementById('Deductible'+RowCount).value=='' && document.getElementById('Takeback'+RowCount).value=='' && document.getElementById('FollowUp'+RowCount).checked==false)
	  {
	  }
	 else
	   {
		InsPatDropDownValue=document.getElementById('payment_ins'+RowCount).options[document.getElementById('payment_ins'+RowCount).selectedIndex].value;
		if(PayingEntity=='patient' && InsPatDropDownValue>0)
		 {
		  alert("<?php echo htmlspecialchars( xl('Cannot Post for Insurance.The Paying Entity selected is Patient.'), ENT_QUOTES) ?>");
		  return false;
		 }
		else if(PayingEntity=='insurance' && InsPatDropDownValue==0)
		 {
		  alert("<?php echo htmlspecialchars( xl('Cannot Post for Patient.The Paying Entity selected is Insurance.'), ENT_QUOTES) ?>");
		  return false;
		 }
	   }
     }
  return true;
 }
function FormValidations()
 {//Screen validations are done here.
  if(document.getElementById('check_date').value=='')
   {
    alert("<?php echo htmlspecialchars( xl('Please Fill the Date'), ENT_QUOTES) ?>");
	document.getElementById('check_date').focus();
	return false;
   }
  else if(!ValidateDateGreaterThanNow(document.getElementById('check_date').value,'<?php echo DateFormatRead();?>'))
   {
    alert("<?php echo htmlspecialchars( xl('Date Cannot be greater than Today'), ENT_QUOTES) ?>");
	document.getElementById('check_date').focus();
	return false;
   }
  if(document.getElementById('post_to_date').value=='')
   {
    alert("<?php echo htmlspecialchars( xl('Please Fill the Post To Date'), ENT_QUOTES) ?>");
	document.getElementById('post_to_date').focus();
	return false;
   }
  else if(!ValidateDateGreaterThanNow(document.getElementById('post_to_date').value,'<?php echo DateFormatRead();?>'))
   {
    alert("<?php echo htmlspecialchars( xl('Post To Date Cannot be greater than Today'), ENT_QUOTES) ?>");
	document.getElementById('post_to_date').focus();
	return false;
   }
  else if(DateCheckGreater(document.getElementById('post_to_date').value,'<?php echo $GLOBALS['post_to_date_benchmark']=='' ? date('Y-m-d',time() - (10 * 24 * 60 * 60)) : htmlspecialchars(oeFormatShortDate($GLOBALS['post_to_date_benchmark']));?>',
  '<?php echo DateFormatRead();?>'))
   {
    alert("<?php echo htmlspecialchars( xl('Post To Date Must be greater than the Financial Close Date.'), ENT_QUOTES) ?>");
	document.getElementById('post_to_date').focus();
	return false;
   }
   if(((document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value=='check_payment' ||
   	  document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value=='bank_draft') &&
	   document.getElementById('check_number').value=='' ))
   {
    alert("<?php echo htmlspecialchars( xl('Please Fill the Check Number'), ENT_QUOTES) ?>");
	document.getElementById('check_number').focus();
	return false;
   }
  <?php 
  if($screen=='edit_payment')
   {
  ?>
	   if(document.getElementById('check_number').value!='' &&
	   document.getElementById('payment_method').options[document.getElementById('payment_method').selectedIndex].value=='')
		{
		alert("<?php echo htmlspecialchars( xl('Please Select the Payment Method'), ENT_QUOTES) ?>");
		document.getElementById('payment_method').focus();
		return false;
		}
  <?php 
   }
  ?>
   if(document.getElementById('payment_amount').value=='')
   {
    alert("<?php echo htmlspecialchars( xl('Please Fill the Payment Amount'), ENT_QUOTES) ?>");
	document.getElementById('payment_amount').focus();
	return false;
   }
   if(document.getElementById('payment_amount').value!=document.getElementById('payment_amount').value*1)
   {
    alert("<?php echo htmlspecialchars( xl('Payment Amount must be Numeric'), ENT_QUOTES) ?>");
	document.getElementById('payment_amount').focus();
	return false;
   }
  <?php 
  if($screen=='edit_payment')
   {
  ?>
	  if(document.getElementById('adjustment_code').options[document.getElementById('adjustment_code').selectedIndex].value=='')
	   {
		alert("<?php echo htmlspecialchars( xl('Please Fill the Payment Category'), ENT_QUOTES) ?>");
		document.getElementById('adjustment_code').focus();
		return false;
	   }
  <?php 
   }
  ?>
  if(document.getElementById('type_code').value=='')
   {
    alert("<?php echo htmlspecialchars( xl('Please Fill the Payment From'), ENT_QUOTES) ?>");
	document.getElementById('type_code').focus();
	return false;
   }
  if(document.getElementById('hidden_type_code').value!=document.getElementById('div_insurance_or_patient').innerHTML)
   {
	alert("<?php echo htmlspecialchars( xl('Take Payment From, from Drop Down'), ENT_QUOTES) ?>");
	document.getElementById('type_code').focus();
	return false;
   }
  if(document.getElementById('deposit_date').value=='')
   {
   }
  else if(!ValidateDateGreaterThanNow(document.getElementById('deposit_date').value,'<?php echo DateFormatRead();?>'))
   {
    alert("<?php echo htmlspecialchars( xl('Deposit Date Cannot be greater than Today'), ENT_QUOTES) ?>");
	document.getElementById('deposit_date').focus();
	return false;
   }
  return true;
}
//========================================================================================
function UpdateTotalValues(start,count,Payment,PaymentTotal)
{//Used in totaling the columns.
 var paymenttot=0;
	 if(count > 0)
	 {
	   for(i=start;i<start+count;i++)
	   {
			 if(document.getElementById(Payment+i))
			 {
				   paymenttot=paymenttot+document.getElementById(Payment+i).value*1;
			 }
	   }
		 document.getElementById(PaymentTotal).innerHTML=Math.round((paymenttot)*100)/100;
	}
}
</script>