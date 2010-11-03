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
//Patient ajax section and listing of charges..Used in New Payment and Edit Payment screen.
//===============================================================================
if (isset($_POST["mode"]))
 {
  if (($_POST["mode"] == "search" || $_POST["default_search_patient"] == "default_search_patient") && $_REQUEST['hidden_patient_code']*1>0)
   {
	$hidden_patient_code=$_REQUEST['hidden_patient_code'];
	$RadioPaid=$_REQUEST['RadioPaid'];
	if($RadioPaid=='Show_Paid')
	 {
	  $StringForQuery='';
	 }
	elseif($RadioPaid=='Non_Paid')
	 {
	  $StringForQuery=" and last_level_closed = 0 ";
	 }
	elseif($RadioPaid=='Show_Primary_Complete')
	 {
	  $StringForQuery=" and last_level_closed >= 1 ";
	 }
	$ResultSearchNew = sqlStatement("SELECT billing.id,last_level_closed,billing.encounter,form_encounter.`date`,billing.code,billing.modifier,fee
	 FROM billing ,form_encounter
			 where billing.encounter=form_encounter.encounter and code_type!='ICD9' and  code_type!='COPAY' and billing.activity!=0 and
			 form_encounter.pid ='$hidden_patient_code' and billing.pid ='$hidden_patient_code'  $StringForQuery ORDER BY form_encounter.`date`, 
			 form_encounter.encounter,billing.code,billing.modifier");
	$res = sqlStatement("SELECT fname,lname,mname FROM patient_data
			where pid ='".$_REQUEST['hidden_patient_code']."'");
	$row = sqlFetchArray($res);
		$fname=$row['fname'];
		$lname=$row['lname'];
		$mname=$row['mname'];
		$NameNew=$lname.' '.$fname.' '.$mname;
   }
 }
//===============================================================================
?>
<table width="1004" border="0" cellspacing="0" cellpadding="0"  id="TablePatientPortion">
	  <tr height="5">
		<td colspan="13" align="left" >
			<table width="705" border="0" cellspacing="0" cellpadding="0" bgcolor="#DEDEDE">
			  <tr height="5">
				<td class='title' width="700" ></td>
			  </tr>
			  <tr>
				<td  class='text'><table width="799" border="0" cellspacing="0" cellpadding="0" style="border:1px solid black" >
			  <tr>
				<td width="45" align="left" class="text">&nbsp;<?php echo htmlspecialchars( xl('Patient'), ENT_QUOTES).':' ?>
				</td>
				<td width="265"><input type="hidden" id="hidden_ajax_patient_close_value" value="<?php echo $Message=='' ? htmlspecialchars($NameNew) : '' ;?>" />
				<input name='patient_code'  style="width:265px"   id='patient_code' class="text"  onKeyDown="PreventIt(event)"  
				value="<?php echo $Message=='' ? htmlspecialchars($NameNew) : '' ;?>"  autocomplete="off" /></td> <!--onKeyUp="ajaxFunction(event,'patient','edit_payment.php');" -->
				<td width="55" colspan="2" style="padding-left:5px;" ><div  class="text" name="patient_name" id="patient_name"  
				style="border:1px solid black; ; padding-left:5px; width:55px; height:17px;"><?php echo $Message=='' ? htmlspecialchars(formData('hidden_patient_code')) : '' ;?></div>
				</td>
				<td width="84" class="text">&nbsp;<input type="radio" name="RadioPaid" onClick="SearchOnceMore()" <?php echo $_REQUEST['RadioPaid']=='Non_Paid' 
				|| $_REQUEST['RadioPaid']=='' ? 'checked' : '' ; ?>  value="Non_Paid" id="Non_Paid"  /><?php echo htmlspecialchars( xl('Non Paid'), ENT_QUOTES) ?></td>
				<td width="168" class="text"><input type="radio" name="RadioPaid" onClick="SearchOnceMore()" 
				<?php echo $_REQUEST['RadioPaid']=='Show_Primary_Complete' ? 'checked' : '' ; ?>  value="Show_Primary_Complete" 
				id="Show_Primary_Complete" /><?php echo htmlspecialchars( xl('Show Primary Complete'), ENT_QUOTES) ?></td>
				<td width="157" class="text"><input type="radio" name="RadioPaid" onClick="SearchOnceMore()" 
				<?php echo $_REQUEST['RadioPaid']=='Show_Paid' ? 'checked' : '' ; ?>  value="Show_Paid" id="Show_Paid" /><?php echo htmlspecialchars( xl('Show All Transactions'), ENT_QUOTES) ?>
				</td>
			  </tr>
			  <tr>
				<td align="left" class="text"></td>
				<td><div id='ajax_div_patient_section'>
					  <div id='ajax_div_patient_error'>
					  </div>
					  <div id="ajax_div_patient" style="display:none;"></div>
					  </div>
					 </div>
				</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td class="text"></td>
				</tr>
			</table>		</td>
			  </tr>
			</table>

		</td>
	  </tr>
	<tr>
	<td colspan="13" align="left" >
			
			<?php //New distribution section
			//$CountIndex=0;
			$CountIndexBelow=0;
			$PreviousEncounter=0;
			$PreviousPID=0;
			if($RowSearch = sqlFetchArray($ResultSearchNew))
			 {
			?>
			<table width="1004"  border="0" cellpadding="0" cellspacing="0" align="center" id="TableDistributePortion">
			  <tr class="text" height="10">
			    <td colspan="14"></td>
		      </tr>
			  <tr class="text" bgcolor="#dddddd">
				<td width="55" class="left top" ><?php echo htmlspecialchars( xl('Post For'), ENT_QUOTES) ?></td>
				<td width="80" class="left top" ><?php echo htmlspecialchars( xl('Service Date'), ENT_QUOTES) ?></td>
				<td width="65" class="left top" ><?php echo htmlspecialchars( xl('Encounter'), ENT_QUOTES) ?></td>
				<td width="70" class="left top" ><?php echo htmlspecialchars( xl('CPT Code'), ENT_QUOTES) ?></td>
				<td width="55" class="left top" ><?php echo htmlspecialchars( xl('Charge'), ENT_QUOTES) ?></td>
				<td width="40" class="left top" ><?php echo htmlspecialchars( xl('Copay'), ENT_QUOTES) ?></td>
				<td width="45" class="left top" ><?php echo htmlspecialchars( xl('Remdr'), ENT_QUOTES) ?></td>
				<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Allowed'), ENT_QUOTES) ?></td>
				<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Payment'), ENT_QUOTES) ?></td>
				<td width="70" class="left top" ><?php echo htmlspecialchars( xl('Adj Amount'), ENT_QUOTES) ?></td>
				<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Deductible'), ENT_QUOTES) ?></td>
				<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Takeback'), ENT_QUOTES) ?></td>
				<td width="60" class="left top" ><?php echo htmlspecialchars( xl('Follow Up'), ENT_QUOTES) ?></td>
				<td width="209" class="left top right" ><?php echo htmlspecialchars( xl('Follow Up Reason'), ENT_QUOTES) ?></td>
			  </tr>
			  <?php
				do 
				 {

					$CountIndex++;
					$CountIndexBelow++;
					$Ins=0;
					// Determine the next insurance level to be billed.
					$ferow = sqlQuery("SELECT date, last_level_closed " .
					  "FROM form_encounter WHERE " .
					  "pid = '$hidden_patient_code' AND encounter = '".$RowSearch['encounter']."'");
					$date_of_service = substr($ferow['date'], 0, 10);
					$new_payer_type = 0 + $ferow['last_level_closed'];
					if ($new_payer_type <= 3 && !empty($ferow['last_level_closed']) || $new_payer_type == 0)
					  ++$new_payer_type;
					$new_payer_id = arGetPayerID($hidden_patient_code, $date_of_service, $new_payer_type);
					
					if($new_payer_id==0)
					 {
						$Ins=0;
					 }
					elseif($new_payer_id>0)
					 {
						$Ins=$new_payer_type;
					 }


					$ServiceDateArray=split(' ',$RowSearch['date']);
					$ServiceDate=oeFormatShortDate($ServiceDateArray[0]);
					$Code=$RowSearch['code'];
					$Modifier =$RowSearch['modifier'];
					if($Modifier!='')
					 $ModifierString=", $Modifier";
					else
					 $ModifierString="";
					$Fee=$RowSearch['fee'];
					$Encounter=$RowSearch['encounter'];
					
					//Always associating the copay to a particular charge.
					$BillingId=$RowSearch['id'];
					$resId = sqlStatement("SELECT id  FROM billing where code_type!='ICD9' and  code_type!='COPAY'  and
					pid ='$hidden_patient_code' and  encounter  ='$Encounter' and billing.activity!=0 order by id");
					$rowId = sqlFetchArray($resId);
					$Id=$rowId['id'];

					if($BillingId!=$Id)//multiple cpt in single encounter
					 {
						$Copay=0.00;
					 }
					else
					 {
						$resCopay = sqlStatement("SELECT sum(fee) as copay FROM billing where  code_type='COPAY'  and
						pid ='$hidden_patient_code' and  encounter  ='$Encounter' and billing.activity!=0");
						$rowCopay = sqlFetchArray($resCopay);
						$Copay=$rowCopay['copay']*-1;
						
						$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where
						pid ='$hidden_patient_code'  and  encounter  ='$Encounter' and  payer_type=0 and 
						(code='CO-PAY' or account_code='PCP')");//new fees screen copay gives account_code='PCP'
						//openemr payment screen copay gives code='CO-PAY'
						$rowMoneyGot = sqlFetchArray($resMoneyGot);
						$PatientPay=$rowMoneyGot['PatientPay'];
						
						$Copay=$Copay+$PatientPay;
					 }
						//payer_type!=0
						$resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where
						pid ='$hidden_patient_code' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and  !(payer_type=0 and 
						(code='CO-PAY' or account_code='PCP'))");//new fees screen copay gives account_code='PCP'
						//openemr payment screen copay gives code='CO-PAY'
						$rowMoneyGot = sqlFetchArray($resMoneyGot);
						$MoneyGot=$rowMoneyGot['MoneyGot'];

						$resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where
						pid ='$hidden_patient_code' and  code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter'");
						$rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
						$MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];

						$Remainder=$Fee-$Copay-$MoneyGot-$MoneyAdjusted;

					if($CountIndexBelow==sqlNumRows($ResultSearchNew))
					 {
						$StringClass=' bottom left top ';
					 }
					else
					 {
						$StringClass=' left top ';
					 }


					if($Ins==1)
					 {
						$bgcolor='#ddddff';
					 }
					elseif($Ins==2)
					 {
						$bgcolor='#ffdddd';
					 }
					elseif($Ins==3)
					 {
						$bgcolor='#F2F1BC';
					 }
					elseif($Ins==0)
					 {
						$bgcolor='#AAFFFF';
					 }
			  ?>
			  <tr class="text"  bgcolor='<?php echo $bgcolor; ?>' id="trCharges<?php echo $CountIndex; ?>">
				<td align="left" class="<?php echo $StringClass; ?>" ><input name="HiddenIns<?php echo $CountIndex; ?>" id="HiddenIns<?php echo $CountIndex; ?>" 
				 value="<?php echo htmlspecialchars($Ins); ?>" type="hidden"/><?php echo generate_select_list("payment_ins$CountIndex", "payment_ins", "$Ins", "Insurance/Patient",'','','ActionOnInsPat("'.$CountIndex.'")');?></td>
				<td class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars($ServiceDate); ?></td>
				<td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenEncounter<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Encounter); ?>" 
				type="hidden"/><?php echo htmlspecialchars($Encounter); ?></td>
				<td class="<?php echo $StringClass; ?>" ><input name="HiddenCode<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Code); ?>"
				 type="hidden"/><?php echo htmlspecialchars($Code.$ModifierString); ?><input name="HiddenModifier<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Modifier); ?>"
				  type="hidden"/></td>
				<td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenChargeAmount<?php echo $CountIndex; ?>"
				 id="HiddenChargeAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Fee); ?>" type="hidden"/><?php echo htmlspecialchars($Fee); ?></td>
				<td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenCopayAmount<?php echo $CountIndex; ?>"
				 id="HiddenCopayAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Copay); ?>" type="hidden"/><?php echo htmlspecialchars(number_format($Copay,2)); ?></td>
				<td align="right"   id="RemainderTd<?php echo $CountIndex; ?>"  class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars(round($Remainder,2)); ?></td>
				<input name="HiddenRemainderTd<?php echo $CountIndex; ?>" id="HiddenRemainderTd<?php echo $CountIndex; ?>" 
				 value="<?php echo htmlspecialchars(round($Remainder,2)); ?>" type="hidden"/>
				<td class="<?php echo $StringClass; ?>" ><input  name="Allowed<?php echo $CountIndex; ?>" id="Allowed<?php echo $CountIndex; ?>" 
				 onKeyDown="PreventIt(event)"  autocomplete="off"  
				 onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);RestoreValues(<?php echo $CountIndex; ?>)" 
				   type="text"   style="width:60px;text-align:right; font-size:12px"  /></td>
				<td class="<?php echo $StringClass; ?>" ><input   type="text"  name="Payment<?php echo $CountIndex; ?>" 
				 onKeyDown="PreventIt(event)"   autocomplete="off"  id="Payment<?php echo $CountIndex; ?>" 
				  onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);RestoreValues(<?php echo $CountIndex; ?>)" 
				   style="width:60px;text-align:right; font-size:12px" /></td>
				<td class="<?php echo $StringClass; ?>" ><input  name="AdjAmount<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)" 
				  autocomplete="off"  id="AdjAmount<?php echo $CountIndex; ?>"  
				  onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);RestoreValues(<?php echo $CountIndex; ?>)"  
				  type="text"   style="width:70px;text-align:right; font-size:12px" /></td>
				<td class="<?php echo $StringClass; ?>" ><input  name="Deductible<?php echo $CountIndex; ?>"  id="Deductible<?php echo $CountIndex; ?>" 
				 onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);"   autocomplete="off"   type="text"   
				 style="width:60px;text-align:right; font-size:12px" /></td>
				<td class="<?php echo $StringClass; ?>" ><input  name="Takeback<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"  
				 id="Takeback<?php echo $CountIndex; ?>"  
				 onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);RestoreValues(<?php echo $CountIndex; ?>)"  
				  type="text"   style="width:60px;text-align:right; font-size:12px" /></td>
				<td align="center" class="<?php echo $StringClass; ?>" ><input type="checkbox" id="FollowUp<?php echo $CountIndex; ?>" 
				 name="FollowUp<?php echo $CountIndex; ?>" value="y" onClick="ActionFollowUp(<?php echo $CountIndex; ?>)"  /></td>
				<td class="<?php echo $StringClass; ?> right" ><input  onKeyDown="PreventIt(event)" id="FollowUpReason<?php echo $CountIndex; ?>"   
				 name="FollowUpReason<?php echo $CountIndex; ?>"  readonly=""  type="text"   style="width:209px;font-size:12px" /></td>
			  </tr>
			<?php
					
				 }while($RowSearch = sqlFetchArray($ResultSearchNew));
			?>
			</table>
			<?php
			 }//if($RowSearch = sqlFetchArray($ResultSearchNew))
			?>
	  </td>
	  </tr>
</table>