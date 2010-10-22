<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2005-2010 Z&H Healthcare Solutions, LLC <sam@zhservices.com>
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
//Check/cash details are entered here.Used in New Payment and Edit Payment screen.
//===============================================================================
//Special list function
function generate_list_payment_category($tag_name, $list_id, $currvalue, $title,
  $empty_name=' ', $class='', $onchange='',$PaymentType='insurance',$screen='new_payment')
{
  $s = '';
  $tag_name_esc = htmlspecialchars( $tag_name, ENT_QUOTES);
  $s .= "<select name='$tag_name_esc' id='$tag_name_esc'";
  if ($class) $s .= " class='$class'";
  if ($onchange) $s .= " onchange='$onchange'";
  $selectTitle = htmlspecialchars( $title, ENT_QUOTES);
  $s .= " title='$selectTitle'>";
  $selectEmptyName = htmlspecialchars( xl($empty_name), ENT_QUOTES);
  if ($empty_name) $s .= "<option value=''>" . $selectEmptyName . "</option>";
  $lres = sqlStatement("SELECT * FROM list_options " .
    "WHERE list_id = ? ORDER BY seq, title", array($list_id) );
  $got_selected = FALSE;
  while ($lrow = sqlFetchArray($lres)) {
    $optionValue = htmlspecialchars( $lrow['option_id'], ENT_QUOTES);
    $s .= "<option   id='option_" . $lrow['option_id'] . "'" . " value='$optionValue'";
    if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
        (strlen($currvalue)  > 0 && $lrow['option_id'] == $currvalue)  ||
		($lrow['option_id'] == 'insurance_payment' &&  $screen=='new_payment'))
    {
      $s .= " selected";
      $got_selected = TRUE;
    }
  if (($PaymentType == 'insurance' || $screen=='new_payment') && ($lrow['option_id'] == 'family_payment' || $lrow['option_id'] == 'patient_payment'))
	$s .=  " style='background-color:#DEDEDE' ";
  if ($PaymentType == 'patient' && $lrow['option_id'] == 'insurance_payment')
	$s .=  " style='background-color:#DEDEDE' ";
    $optionLabel = htmlspecialchars( xl_list_label($lrow['title']), ENT_QUOTES);
    $s .= ">$optionLabel</option>\n";
  }
  if (!$got_selected && strlen($currvalue) > 0) {
    $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);
    $s .= "<option value='$currescaped' selected>* $currescaped *</option>";
    $s .= "</select>";
    $fontTitle = htmlspecialchars( xl('Please choose a valid selection from the list.'), ENT_QUOTES);
    $fontText = htmlspecialchars( xl('Fix this'), ENT_QUOTES);
    $s .= " <font color='red' title='$fontTitle'>$fontText!</font>";
  }
  else {
    $s .= "</select>";
  }
  return $s;
}
//================================================================================================
if($payment_id>0)
 {
	$rs= sqlStatement("select pay_total,global_amount from ar_session where session_id='$payment_id'");
	$row=sqlFetchArray($rs);
	$pay_total=$row['pay_total'];
	$global_amount=$row['global_amount'];
	$rs= sqlStatement("select sum(pay_amount) sum_pay_amount from ar_activity where session_id='$payment_id'");
	$row=sqlFetchArray($rs);
	$pay_amount=$row['sum_pay_amount'];
	$UndistributedAmount=$pay_total-$pay_amount-$global_amount;
	
	$res = sqlStatement("SELECT check_date ,reference ,insurance_companies.name,
	payer_id,pay_total,payment_type,post_to_date,patient_id ,
	adjustment_code,description,deposit_date,payment_method
	 FROM ar_session left join insurance_companies on ar_session.payer_id=insurance_companies.id 	where ar_session.session_id ='$payment_id'");
	$row = sqlFetchArray($res);
	$InsuranceCompanyName=$row['name'];
	$InsuranceCompanyId=$row['payer_id'];
	$PatientId=$row['patient_id'];
	$CheckNumber=$row['reference'];
	$CheckDate=$row['check_date']=='0000-00-00'?'':$row['check_date'];
	$PayTotal=$row['pay_total'];
	$PostToDate=$row['post_to_date']=='0000-00-00'?'':$row['post_to_date'];
	$PaymentMethod=$row['payment_method'];
	$PaymentType=$row['payment_type'];
	$AdjustmentCode=$row['adjustment_code'];
	$DepositDate=$row['deposit_date']=='0000-00-00'?'':$row['deposit_date'];
	$Description=$row['description'];
	if($row['payment_type']=='insurance' || $row['payer_id']*1 > 0)
	 {
		$res = sqlStatement("SELECT insurance_companies.name FROM insurance_companies
				where insurance_companies.id ='$InsuranceCompanyId'");
		$row = sqlFetchArray($res);
		$div_after_save=$row['name'];
		$TypeCode=$InsuranceCompanyId;
		 if($PaymentType=='')
		  {
			$PaymentType='insurance';
		  }
	 }
	elseif($row['payment_type']=='patient' || $row['patient_id']*1 > 0)
	 {
		$res = sqlStatement("SELECT fname,lname,mname FROM patient_data
				where pid ='$PatientId'");
		$row = sqlFetchArray($res);
			$fname=$row['fname'];
			$lname=$row['lname'];
			$mname=$row['mname'];
			$div_after_save=$lname.' '.$fname.' '.$mname;
		$TypeCode=$PatientId;
		 if($PaymentType=='')
		  {
			$PaymentType='patient';
		  }
	 }
 }
?>
<?php
//================================================================================================
if(($screen=='new_payment' && $payment_id*1==0) || ($screen=='edit_payment' && $payment_id*1>0))
 {//New entry or edit in edit screen comes here.
?>
<table width="1024" border="0" cellspacing="0" cellpadding="10" bgcolor="#DEDEDE"><tr><td>
	<table width="1004" border="0" style="border:1px solid black" cellspacing="0" cellpadding="0">
	  <tr height="5">
		<td colspan="14" align="left" ></td>
	  </tr>
	  <tr>
		<td colspan="14" align="left">&nbsp;<font class='title'>
		<?php
		  if($_REQUEST['ParentPage']=='new_payment')//This case comes when the Finish Payments is pressed from the New Payment screen.
		  {
		  ?>
			<?php echo htmlspecialchars( xl('Confirm Payment'), ENT_QUOTES) ?>
		<?php
		  }
		 elseif($screen=='new_payment')
		  {
		  ?>
			<?php echo htmlspecialchars( xl('Batch Payment Entry'), ENT_QUOTES) ?>
		<?php
		  }
		 else
		  {
		  ?>
			<?php echo htmlspecialchars( xl('Edit Payment'), ENT_QUOTES) ?>
		<?php
		  }
		  ?>
		</font></td>
	  </tr>
	  <tr height="20">
	    <td align="left" width="5" ></td>
		<td align="left" width="110" ></td>
		<td align="left" width="128"></td>
		<td align="left" width="25"></td>
		<td align="left" width="5"></td>
	    <td align="left" width="85"></td>
	    <td align="left" width="128"></td>
	    <td align="left" width="25"></td>
	    <td align="left" width="5"></td>
	    <td align="left" width="113"></td>
	    <td align="left" width="125"></td>
	    <td align="left" width="5"></td>
	    <td align="left" width="93"></td>
	    <td align="left" width="152"></td>
	    </tr>
	  <tr>
	    <td align="left" class='text'></td>
		<td align="left" class='text'><?php echo htmlspecialchars( xl('Date'), ENT_QUOTES).':' ?></td>
		<td align="left" class="text" ><input type='text' size='9' name='check_date' id='check_date' class="class1 text "  value="<?php echo htmlspecialchars(oeFormatShortDate($CheckDate));?>"/></td>
		<td><img src='../../interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg' align='absbottom'
		id='img_checkdate' border='0' alt='[?]' style='cursor:pointer'
		title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
	   <script>
		Calendar.setup({inputField:"check_date", ifFormat:"<?php echo $DateFormat; ?>", button:"img_checkdate"});
	   </script></td>
		<td></td>
	    <td align="left" class='text'><?php echo htmlspecialchars( xl('Post To Date'), ENT_QUOTES).':' ?></td>
	    <td align="left" class="text"><input type='text' size='9' name='post_to_date' id='post_to_date' class="class1 text "   value="<?php echo $screen=='new_payment'?htmlspecialchars(oeFormatShortDate(date('Y-m-d'))):htmlspecialchars(oeFormatShortDate($PostToDate));?>"  readonly="" /></td>
	    <td><img src='../../interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg' align='absbottom'
		id='img_post_to_date' border='0' alt='[?]' style='cursor:pointer'
		title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
	   <script>
		Calendar.setup({inputField:"post_to_date", ifFormat:"<?php echo $DateFormat; ?>", button:"img_post_to_date"});
	   </script></td>
	    <td></td>
	    <td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Method'), ENT_QUOTES).':' ?></td>
	    <td align="left">
			<?php	
				if($PaymentMethod=='' && $screen=='edit_payment') 
					$blankValue=' '; 
				else 
					$blankValue='';
			echo generate_select_list("payment_method", "payment_method", "$PaymentMethod", "Payment Method","$blankValue","class1 text",'CheckVisible("yes")');
			?>
	  </td>
	    <td></td>
	    <td align="left" class="text"><?php echo htmlspecialchars( xl('Check Number'), ENT_QUOTES).':' ?></td>
	    <td>
		<?php
		if($PaymentMethod=='check_payment' || $PaymentMethod=='bank_draft' || $CheckNumber!='' || $screen=='new_payment')
		 {
		  $CheckDisplay='';
		  $CheckDivDisplay=' display:none; ';
		 }
	    else
		 {
		  $CheckDisplay=' display:none; ';
		  $CheckDivDisplay='';
		 }
		?>
		<input type="text" name="check_number"  style="width:140px;<?php echo $CheckDisplay;?>"  autocomplete="off"  value="<?php echo htmlspecialchars($CheckNumber);?>"  onKeyUp="ConvertToUpperCase(this)"  id="check_number"  class="text "   />
		<div  id="div_check_number" class="text"  style="border:1px solid black; width:140px;<?php echo $CheckDivDisplay;?>">&nbsp;</div>
	   </td>
	   </tr>
	  <tr height="1">
		<td colspan="14" align="left" ></td>
	  </tr>
	  <tr>
	    <td align="left" class="text"></td>
		<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Amount'), ENT_QUOTES).':' ?></td>
		<td align="left"><input   type="text" name="payment_amount"   autocomplete="off"  id="payment_amount"  onchange="ValidateNumeric(this);<?php echo $screen=='new_payment'?'FillUnappliedAmount();':'FillAmount();';?>"  value="<?php echo $screen=='new_payment'?htmlspecialchars('0.00'):htmlspecialchars($PayTotal);?>"  style="text-align:right"    class="class1 text "   /></td>
	    <td align="left" ></td>
	    <td align="left" ></td>
		<td align="left" class="text"><?php echo htmlspecialchars( xl('Paying Entity'), ENT_QUOTES).':' ?></td>
		<td align="left"><?php
				if($PaymentType=='' && $screen=='edit_payment') 
					$blankValue=' '; 
				else 
					$blankValue='';
			echo generate_select_list("type_name", "payment_type", "$PaymentType", "Paying Entity","$blankValue","class1 text",'PayingEntityAction()');
			?>
		</td>
	    <td align="left" ></td>
	    <td align="left" ></td>
		<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Category'), ENT_QUOTES).':' ?></td>
		<td align="left"><?php
				if($AdjustmentCode=='' && $screen=='edit_payment') 
					$blankValue=' '; 
				else 
					$blankValue='';
			echo generate_list_payment_category("adjustment_code", "payment_adjustment_code", "$AdjustmentCode", 
			"Payment Category","$blankValue","class1 text",'FilterSelection(this)',"$PaymentType","$screen");
			?>
	   </td>
	    <td align="left" ></td>
	    <td align="left" ></td>
	    <td align="left" ></td>
	    </tr>
	  <tr height="1">
		<td colspan="14" align="left" ></td>
	  </tr>
	  <tr>
	    <td align="left" class="text"></td>
		<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment From'), ENT_QUOTES).':' ?></td>
		<td align="left" colspan="5"><input type="hidden" id="hidden_ajax_close_value" value="<?php echo htmlspecialchars($div_after_save);?>" /><input name='type_code'  id='type_code' class="text "  style="width:369px"   onKeyDown="PreventIt(event)"  value="<?php echo htmlspecialchars($div_after_save);?>"  autocomplete="off"   /><br>
		<!-- onKeyUp="ajaxFunction(event,'non','edit_payment.php');" -->
		 <div id='ajax_div_insurance_section'>
		  <div id='ajax_div_insurance_error'>
		  </div>
		  <div id="ajax_div_insurance" style="display:none;"></div>
		  </div>
		 </div>
				</td>
		<td align="left" colspan="5"><div  name="div_insurance_or_patient" id="div_insurance_or_patient" class="text"  style="border:1px solid black; padding-left:5px; width:55px; height:17px;"><?php echo htmlspecialchars($TypeCode);?></div></td>
	    <td align="left" ></td>
	    <td align="left" ></td>
	    </tr>
	  <tr>
	    <td align="left" class='text'></td>
		<td align="left" class='text'><?php echo htmlspecialchars( xl('Deposit Date'), ENT_QUOTES).':' ?></td>
		<td align="left"><input type='text' size='9' name='deposit_date' id='deposit_date'  onKeyDown="PreventIt(event)"   class="class1 text " value="<?php echo htmlspecialchars(oeFormatShortDate($DepositDate));?>"    />	   </td>
		<td><img src='../../interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg' align='absbottom'
		id='img_depositdate' border='0' alt='[?]' style='cursor:pointer'
		title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
	   <script>
		Calendar.setup({inputField:"deposit_date", ifFormat:"<?php echo $DateFormat; ?>", button:"img_depositdate"});
	   </script></td>
		<td></td>
	    <td align="left" class="text"><?php echo htmlspecialchars( xl('Description'), ENT_QUOTES).':' ?></td>
	    <td colspan="6" align="left"><input type="text" name="description"  id="description"   onKeyDown="PreventIt(event)"   value="<?php echo htmlspecialchars($Description);?>"   style="width:396px" class="text "   /></td>
	    <td align="left" class="text"><font  style="font-size:11px"><?php echo htmlspecialchars( xl('UNDISTRIBUTED'), ENT_QUOTES).':' ?></font><input name="HidUnappliedAmount" id="HidUnappliedAmount"  value="<?php echo ($UndistributedAmount*1==0)? htmlspecialchars("0.00") : htmlspecialchars(number_format($UndistributedAmount,2,'.',','));?>" type="hidden"/><input name="HidUnpostedAmount" id="HidUnpostedAmount"  value="<?php echo htmlspecialchars($UndistributedAmount); ?>" type="hidden"/><input name="HidCurrentPostedAmount" id="HidCurrentPostedAmount"  value="" type="hidden"/></td>
		<td align="left" class="text"><div  id="TdUnappliedAmount" class="text"  style="border:1px solid black; width:75px; background-color:#EC7676; padding-left:5px;"><?php echo ($UndistributedAmount*1==0)? htmlspecialchars("0.00") : htmlspecialchars(number_format($UndistributedAmount,2,'.',','));?></div></td>
	    </tr>
	<?php
	if($screen=='new_payment')
	 {
	?>
	  <tr>
		<td colspan="14" align="center" class="text" id="TdContainingButtons">
		<table border="0" cellspacing="0" cellpadding="0" width="280">
		  <tr height="5">
			<td ></td>
			<td></td>
			<td></td>
		  </tr>
		  <tr>
			<td width="100"><a href="#" onClick="javascript:return SavePayment();" class="css_button"><span><?php echo htmlspecialchars( xl('Save Changes'), ENT_QUOTES);?></span></a></td>
		    <td width="110"><a href="#" onClick="javascript:ResetForm()" class="css_button"><span><?php echo htmlspecialchars( xl('Cancel Changes'), ENT_QUOTES);?></span></a></td>
		    <td width="70"><a href="#" class="css_button" onClick="javascript:OpenEOBEntry();"><span><?php echo htmlspecialchars( xl('Allocate'), ENT_QUOTES);?></span></a></td>
		  </tr>
		</table>		</td>
	  </tr>
	<?php
	 }
	?>
	  <tr height="5">
		<td colspan="14" align="left" ></td>
	  </tr>
	</table>
	</td></tr></table>
<?php
}//if(($screen=='new_payment' && $payment_id*1==0) || ($screen=='edit_payment' && $payment_id*1>0))
//================================================================================================
?>
<?php
if($screen=='new_payment' && $payment_id*1>0)
 {//After saving from the New Payment screen,all values are  showed as labels.The date picker images are also removed.
?>
<table width="1024" border="0" cellspacing="0" cellpadding="10" bgcolor="#DEDEDE"><tr><td align="left">
	<table width="1004" border="0" style="border:1px solid black" cellspacing="0" cellpadding="0">
	  <tr height="5">
		<td colspan="13" align="left" ></td>
	  </tr>
	  <tr>
		<td colspan="13" align="left" >

			<table width="969" border="0"  cellspacing="0" cellpadding="0" align="left">
				  <tr>
					<td colspan="13" align="left">&nbsp;<font class='title'><?php echo htmlspecialchars( xl('Batch Payment Entry'), ENT_QUOTES) ?></font></td>
				  </tr>
				  <tr height="20">
					<td align="left" width="5" ></td>
					<td align="left" width="106" ></td>
					<td align="left" width="128"></td>
					<td align="left" width="5"></td>
					<td align="left" width="84"></td>
					<td align="left" width="128"></td>
					<td align="left" width="5"></td>
					<td align="left" width="113"></td>
					<td align="left" width="135"></td>
					<td align="left" width="5"></td>
					<td align="left" width="92"></td>
					<td align="left" width="158"></td>
					<td align="left" width="5"></td>
				  </tr>
				  <tr>
					<td align="left" class='text'></td>
					<td align="left" class='text'><?php echo htmlspecialchars( xl('Date'), ENT_QUOTES).':' ?></td>
					<td align="left" class="text bottom left top right">&nbsp;<?php echo htmlspecialchars(oeFormatShortDate($CheckDate));?><input type="hidden" name="check_date" value="<?php echo htmlspecialchars(oeFormatShortDate($CheckDate));?>"/></td>
					<td></td>
					<td align="left" class='text'><?php echo htmlspecialchars( xl('Post To Date'), ENT_QUOTES).':' ?></td>
					<td align="left" class="text bottom left top right">&nbsp;<?php echo htmlspecialchars(oeFormatShortDate($PostToDate));?><input type="hidden" name="post_to_date" value="<?php echo htmlspecialchars(oeFormatShortDate($PostToDate));?>"/></td>
					<td></td>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Method'), ENT_QUOTES).':' ?></td>
					<td align="left" class="text bottom left top right">&nbsp;<?php
								$frow['data_type']=1;
								$frow['list_id']='payment_method';
								generate_print_field($frow, $PaymentMethod);
				  ?><input type="hidden" name="payment_method" value="<?php echo htmlspecialchars($PaymentMethod);?>"/></td>
					<td></td>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('Check Number'), ENT_QUOTES).':' ?></td>
					<td class="text bottom left top right">&nbsp;<?php echo htmlspecialchars($CheckNumber);?><input type="hidden" name="check_number" value="<?php echo htmlspecialchars($CheckNumber);?>"/></td>
					<td class="text"></td>
				  </tr>
				  <tr height="1">
					<td colspan="13" align="left" ></td>
				  </tr>
				  <tr>
					<td align="left" class="text"></td>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Amount'), ENT_QUOTES).':' ?></td>
					<td align="left" class="text bottom left top right">&nbsp;<?php echo htmlspecialchars($PayTotal);?><input type="hidden" name="payment_amount" value="<?php echo htmlspecialchars($PayTotal);?>"/></td>
					<td align="left" ></td>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('Paying Entity'), ENT_QUOTES).':' ?></td>
					<td align="left" class="text bottom left top right">&nbsp;<?php
								$frow['data_type']=1;
								$frow['list_id']='payment_type';
								generate_print_field($frow, $PaymentType);
				  ?><input type="hidden" name="type_name" id="type_name" value="<?php echo htmlspecialchars($PaymentType);?>"/></td>
					<td align="left" ></td>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment Category'), ENT_QUOTES).':' ?></td>
					<td align="left" class="text bottom left top right">&nbsp;<?php
								$frow['data_type']=1;
								$frow['list_id']='payment_adjustment_code';
								generate_print_field($frow, $AdjustmentCode);
				  ?><input type="hidden" name="adjustment_code" value="<?php echo htmlspecialchars($AdjustmentCode);?>"/></td>
					<td align="left" ></td>
					<td align="left" ></td>
					<td align="left" ></td>
					<td align="left" ></td>
				  </tr>
				  <tr height="1">
					<td colspan="13" align="left" ></td>
				  </tr>
				  <tr>
					<td align="left" class="text"></td>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('Payment From'), ENT_QUOTES).':' ?></td>
					<td colspan="4" align="left" class="text bottom left top right"><div  name="div_insurance_or_patient" id="div_insurance_or_patient" class="text"  >&nbsp;<?php echo htmlspecialchars($div_after_save);?>&nbsp;</div></td>
					<td align="left"></td>
					<td align="left" ><div  name="div_insurance_or_patient" id="div_insurance_or_patient" class="text"  style="border:1px solid black; padding-left:5px; width:55px"><?php echo htmlspecialchars($TypeCode);?></div><input type="hidden" name="type_code" value="<?php echo htmlspecialchars($TypeCode);?>"/></td>
					<td align="left" ></td>
					<td align="left" ></td>
					<td align="left" ></td>
					<td align="left" ></td>
					<td align="left" ></td>
				  </tr>
				  <tr height="1">
					<td colspan="13" align="left" ></td>
				  </tr>
				  <tr>
					<td align="left" class='text'></td>
					<td align="left" class='text'><?php echo htmlspecialchars( xl('Deposit Date'), ENT_QUOTES).':' ?></td>
					<td align="left" class="text bottom left top right">&nbsp;<?php echo htmlspecialchars(oeFormatShortDate($DepositDate));?><input type="hidden" name="deposit_date" value="<?php echo htmlspecialchars(oeFormatShortDate($DepositDate));?>"/>	   </td>
					<td></td>
					<td align="left" class="text"><?php echo htmlspecialchars( xl('Description'), ENT_QUOTES).':' ?></td>
					<td colspan="4" align="left" class="text bottom left top right">&nbsp;<?php echo htmlspecialchars($Description);?><input type="hidden" name="description" value="<?php echo htmlspecialchars($Description);?>"/></td>
					<td align="left" class='text'></td>
					<td align="left" class="text"><font  style="font-size:11px"><?php echo htmlspecialchars( xl('UNDISTRIBUTED'), ENT_QUOTES).':' ?></font><input name="HidUnappliedAmount" id="HidUnappliedAmount"  value="<?php echo ($UndistributedAmount*1==0)? htmlspecialchars("0.00") : htmlspecialchars(number_format($UndistributedAmount,2,'.',','));?>" type="hidden"/><input name="HidUnpostedAmount" id="HidUnpostedAmount"  value="<?php echo htmlspecialchars($UndistributedAmount); ?>" type="hidden"/>
 <input name="HidCurrentPostedAmount"  id="HidCurrentPostedAmount"  value="" type="hidden"/></td>
					<td align="left" ><div  id="TdUnappliedAmount" class="text"  style="border:1px solid black; background-color:#EC7676; width:75px; padding-left:5px;"><?php echo ($UndistributedAmount*1==0)? htmlspecialchars("0.00") : htmlspecialchars(number_format($UndistributedAmount,2,'.',','));?></div></td>
					<td align="left" class="text"></td>
				  </tr>
				  <tr height="5">
					<td colspan="13" align="left"></td>
				  </tr>
			</table>
		</td>
	  </tr>
	</table>
	</td></tr></table>
<?php
}//if($screen=='new_payment' && $payment_id*1>0)
//================================================================================================
?>
