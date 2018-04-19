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
//Patient ajax section and listing of charges..Used in New Payment and Edit Payment screen.
//===============================================================================
if (isset($_POST["mode"])) {
    if (($_POST["mode"] == "search" || $_POST["default_search_patient"] == "default_search_patient") && $_REQUEST['hidden_patient_code']*1>0) {
        $hidden_patient_code=$_REQUEST['hidden_patient_code'];
        $RadioPaid=$_REQUEST['RadioPaid'];
        if ($RadioPaid=='Show_Paid') {
            $StringForQuery='';
        } elseif ($RadioPaid=='Non_Paid') {
            $StringForQuery=" and last_level_closed = 0 ";
        } elseif ($RadioPaid=='Show_Primary_Complete') {
            $StringForQuery=" and last_level_closed >= 1 ";
        }
        $ResultSearchNew = sqlStatement("SELECT b.id,last_level_closed,b.encounter,fe.`date`,b.code_type,b.code,b.modifier,fee
            FROM billing AS b,form_encounter AS fe, code_types AS ct
            WHERE b.encounter=fe.encounter AND b.code_type=ct.ct_key AND ct.ct_diag=0
            AND b.activity!=0 AND fe.pid ='$hidden_patient_code' AND b.pid ='$hidden_patient_code'
            $StringForQuery ORDER BY fe.`date`, fe.encounter,b.code,b.modifier");
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
                    <fieldset>
                    <legend class=""><?php echo xlt('Distribute')?></legend>
                    <div class="col-xs-12" style="padding-bottom:5px">
                        <div class="col-xs-3">
                            <label class="control-label" for="patient_name"><?php echo xlt('Patient'); ?>:</label> 
                            <input id="hidden_ajax_patient_close_value" type="hidden" value="<?php echo $Message=='' ? htmlspecialchars($NameNew) : '' ;?>">
                            <!--<input autocomplete="off" class="form-control" type="text" id='patient_name' name='patient_name' onkeydown="PreventIt(event)" value="<?php echo $Message=='' ? htmlspecialchars($NameNew) : '' ;?>">-->
                            <input name='patient_code'  class="form-control"   id='patient_code' class="text"  onKeyDown="PreventIt(event)" value="<?php echo $Message=='' ? htmlspecialchars($NameNew) : '' ;?>"  autocomplete="off" />
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label" for="patient_name"><?php echo xlt('Patient Id'); ?>:</label>
                            <div class="form-control" name="patient_name" id="patient_name"" >
                                <?php echo $Message=='' ? htmlspecialchars(formData('hidden_patient_code')) : '' ;?>
                            </div>            
                        </div>
                        <div class="col-xs-7">
                            <label class="control-label" for="type_code"><?php echo xlt('Select'); ?>:</label>
                            <div>
                                <label class="radio-inline">
                                  <input type="radio" id="Non_Paid" name="RadioPaid" onclick="SearchOnceMore()" <?php echo $_REQUEST['RadioPaid']=='Non_Paid' || $_REQUEST['RadioPaid']=='' ? 'checked' : '' ; ?> value="Non_Paid"><?php echo xlt('Non Paid'); ?>
                                </label>
                                <label class="radio-inline">
                                  <input type="radio" id="Show_Primary_Complete" name="RadioPaid" onclick="SearchOnceMore()" <?php echo $_REQUEST['RadioPaid']=='Show_Primary_Complete' ? 'checked' : '' ; ?> value="Show_Primary_Complete"><?php echo xlt('Show Primary Complete'); ?>
                                </label>
                                <label class="radio-inline">
                                  <input type="radio" id="Show_Paid" name="RadioPaid" onclick="SearchOnceMore()" <?php echo $_REQUEST['RadioPaid']=='Show_Paid' ? 'checked' : '' ; ?> value="Show_Paid"><?php echo xlt('Show All Transactions'); ?>
                                </label>
                            </div>
                        </div>
                        <div id='ajax_div_patient_section'>
                            <div id='ajax_div_patient_error'>
                            </div>
                            <div id="ajax_div_patient" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    </fieldset>
                <?php //New distribution section
                //$CountIndex=0;
                $CountIndexBelow=0;
                $PreviousEncounter=0;
                $PreviousPID=0;
                if ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                ?>
                <div class = "table-responsive">  
                <table class="table-condensed"   id="TableDistributePortion">    
                  <thead class="" bgcolor="#dddddd">
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Post For'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Service Date'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Encounter'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Service Code'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('Charge'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Copay'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Remdr'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Allowed'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Payment'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Adj Amount'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Deductible'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Takeback'), ENT_QUOTES) ?></td>
                    <td class="left top" ><?php echo htmlspecialchars(xl('MSP Code'), ENT_QUOTES) ?></td>
                    <td  class="left top" ><?php echo htmlspecialchars(xl('Follow Up'), ENT_QUOTES) ?></td>
                    <td  class="left top right" ><?php echo htmlspecialchars(xl('Follow Up Reason'), ENT_QUOTES) ?>
                  </thead>
                    <?php
                    do {
                        $CountIndex++;
                        $CountIndexBelow++;
                        $Ins=0;
                        // Determine the next insurance level to be billed.
                        $ferow = sqlQuery("SELECT date, last_level_closed " .
                          "FROM form_encounter WHERE " .
                          "pid = '$hidden_patient_code' AND encounter = '".$RowSearch['encounter']."'");
                        $date_of_service = substr($ferow['date'], 0, 10);
                        $new_payer_type = 0 + $ferow['last_level_closed'];
                        if ($new_payer_type <= 3 && !empty($ferow['last_level_closed']) || $new_payer_type == 0) {
                            ++$new_payer_type;
                        }
                        $new_payer_id = arGetPayerID($hidden_patient_code, $date_of_service, $new_payer_type);
                        
                        if ($new_payer_id==0) {
                            $Ins=0;
                        } elseif ($new_payer_id>0) {
                            $Ins=$new_payer_type;
                        }


                        $ServiceDateArray=explode(' ', $RowSearch['date']);
                        $ServiceDate=oeFormatShortDate($ServiceDateArray[0]);
                                            $Codetype=$RowSearch['code_type'];
                        $Code=$RowSearch['code'];
                        $Modifier =$RowSearch['modifier'];
                        if ($Modifier!='') {
                            $ModifierString=", $Modifier";
                        } else {
                            $ModifierString="";
                        }
                        $Fee=$RowSearch['fee'];
                        $Encounter=$RowSearch['encounter'];
                        
                        //Always associating the copay to a particular charge.
                        $BillingId=$RowSearch['id'];
                        $resId = sqlStatement("SELECT b.id FROM billing AS b, code_types AS ct 
                                               WHERE b.code_type=ct.ct_key AND ct.ct_diag=0 AND
                                               b.pid='$hidden_patient_code' AND b.encounter='$Encounter' 
                                               AND b.activity!=0 ORDER BY id");
                        $rowId = sqlFetchArray($resId);
                        $Id=$rowId['id'];

                        if ($BillingId!=$Id) {//multiple cpt in single encounter
                            $Copay=0.00;
                        } else {
                            $resCopay = sqlStatement("SELECT sum(fee) as copay FROM billing where  code_type='COPAY'  and
                            pid ='$hidden_patient_code' and  encounter  ='$Encounter' and billing.activity!=0");
                            $rowCopay = sqlFetchArray($resCopay);
                            $Copay=$rowCopay['copay']*-1;
                            
                            $resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where
                            pid ='$hidden_patient_code'  and  encounter  ='$Encounter' and  payer_type=0 and 
                            account_code='PCP'");//new fees screen copay gives account_code='PCP'
                            $rowMoneyGot = sqlFetchArray($resMoneyGot);
                            $PatientPay=$rowMoneyGot['PatientPay'];
                            
                            $Copay=$Copay+$PatientPay;
                        }
                            //payer_type!=0, supports both mapped and unmapped code_type in ar_activity
                            $resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where
                            pid ='$hidden_patient_code' and (code_type='$Codetype' or code_type='') and code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter' and  !(payer_type=0 and 
                            account_code='PCP')");//new fees screen copay gives account_code='PCP'
                            $rowMoneyGot = sqlFetchArray($resMoneyGot);
                            $MoneyGot=$rowMoneyGot['MoneyGot'];
                                                    //supports both mapped and unmapped code_type in ar_activity
                            $resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where
                            pid ='$hidden_patient_code' and (code_type='$Codetype' or code_type='') and code='$Code' and modifier='$Modifier'  and  encounter  ='$Encounter'");
                            $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                            $MoneyAdjusted=$rowMoneyAdjusted['MoneyAdjusted'];

                            $Remainder=$Fee-$Copay-$MoneyGot-$MoneyAdjusted;

                        $TotalRows=sqlNumRows($ResultSearchNew);
                        if ($CountIndexBelow==sqlNumRows($ResultSearchNew)) {
                            $StringClass=' bottom left top ';
                        } else {
                            $StringClass=' left top ';
                        }


                        if ($Ins==1) {
                            $bgcolor='#ddddff';
                        } elseif ($Ins==2) {
                            $bgcolor='#ffdddd';
                        } elseif ($Ins==3) {
                            $bgcolor='#F2F1BC';
                        } elseif ($Ins==0) {
                            $bgcolor='#AAFFFF';
                        }
                    ?>
                  <tr class="text"  bgcolor='<?php echo $bgcolor; ?>' id="trCharges<?php echo $CountIndex; ?>">
                    <td align="left" class="<?php echo $StringClass; ?>" ><input name="HiddenIns<?php echo $CountIndex; ?>" style="width:70px;text-align:right; font-size:12px" id="HiddenIns<?php echo $CountIndex; ?>" 
                     value="<?php echo htmlspecialchars($Ins); ?>" type="hidden"/><?php echo generate_select_list("payment_ins$CountIndex", "payment_ins", "$Ins", "Insurance/Patient", '', 'oe-payment-select class3', 'ActionOnInsPat("'.$CountIndex.'")');?></td>
                    <td class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars($ServiceDate); ?></td>
                    <td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenEncounter<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Encounter); ?>" 
                    type="hidden"/><?php echo htmlspecialchars($Encounter); ?></td>
                    <td class="<?php echo $StringClass; ?>" ><input name="HiddenCodetype<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Codetype); ?>" type="hidden"/><input name="HiddenCode<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Code); ?>"
                     type="hidden"/><?php echo htmlspecialchars($Codetype."-".$Code.$ModifierString); ?><input name="HiddenModifier<?php echo $CountIndex; ?>" value="<?php echo htmlspecialchars($Modifier); ?>"
                      type="hidden"/></td>
                    <td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenChargeAmount<?php echo $CountIndex; ?>"
                     id="HiddenChargeAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Fee); ?>" type="hidden"/><?php echo htmlspecialchars($Fee); ?></td>
                    <td align="right" class="<?php echo $StringClass; ?>" ><input name="HiddenCopayAmount<?php echo $CountIndex; ?>"
                     id="HiddenCopayAmount<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($Copay); ?>" type="hidden"/><?php echo htmlspecialchars(number_format($Copay, 2)); ?></td>
                    <td align="right"   id="RemainderTd<?php echo $CountIndex; ?>"  class="<?php echo $StringClass; ?>" ><?php echo htmlspecialchars(round($Remainder, 2)); ?></td>
                    <input name="HiddenRemainderTd<?php echo $CountIndex; ?>" id="HiddenRemainderTd<?php echo $CountIndex; ?>" 
                     value="<?php echo htmlspecialchars(round($Remainder, 2)); ?>" type="hidden"/>
                    <td class="<?php echo $StringClass; ?>" ><input  name="Allowed<?php echo $CountIndex; ?>" id="Allowed<?php echo $CountIndex; ?>" 
                     onKeyDown="PreventIt(event)"  autocomplete="off"  
                     onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Allowed','initialallowtotal');UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Payment','initialpaymenttotal');UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'AdjAmount','initialAdjAmounttotal');RestoreValues(<?php echo $CountIndex; ?>)" 
                       type="text"   style="width:60px;text-align:right; font-size:12px"  /></td>
                    <td class="<?php echo $StringClass; ?>" ><input   type="text"  name="Payment<?php echo $CountIndex; ?>" 
                     onKeyDown="PreventIt(event)"   autocomplete="off"  id="Payment<?php echo $CountIndex; ?>" 
                      onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Payment','initialpaymenttotal');RestoreValues(<?php echo $CountIndex; ?>)" 
                       style="width:60px;text-align:right; font-size:12px" /></td>
                    <td class="<?php echo $StringClass; ?>" ><input  name="AdjAmount<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)" 
                      autocomplete="off"  id="AdjAmount<?php echo $CountIndex; ?>"  
                      onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'AdjAmount','initialAdjAmounttotal');RestoreValues(<?php echo $CountIndex; ?>)"  
                      type="text"   style="width:70px;text-align:right; font-size:12px" /></td>
                    <td class="<?php echo $StringClass; ?>" ><input  name="Deductible<?php echo $CountIndex; ?>"  id="Deductible<?php echo $CountIndex; ?>" 
                     onKeyDown="PreventIt(event)"  onChange="ValidateNumeric(this);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Deductible','initialdeductibletotal');"   autocomplete="off"   type="text"   
                     style="width:60px;text-align:right; font-size:12px" /></td>
                    <td class="<?php echo $StringClass; ?>" ><input  name="Takeback<?php echo $CountIndex; ?>"  onKeyDown="PreventIt(event)"   autocomplete="off"  
                     id="Takeback<?php echo $CountIndex; ?>"  
                     onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo $CountIndex; ?>);UpdateTotalValues(<?php echo $CountIndexAbove*1+1; ?>,<?php echo $TotalRows; ?>,'Takeback','initialtakebacktotal');RestoreValues(<?php echo $CountIndex; ?>)"  
                      type="text"   style="width:70px;text-align:right; font-size:12px" /></td>
                    <td align="left" class="<?php echo $StringClass; ?>" ><input name="HiddenReasonCode<?php echo $CountIndex; ?>" id="HiddenReasonCode<?php echo $CountIndex; ?>"  value="<?php echo htmlspecialchars($ReasonCodeDB); ?>" type="hidden"/><?php echo generate_select_list("ReasonCode$CountIndex", "msp_remit_codes", "", "MSP Code", "--", "oe-payment-select class3"); ?></td>                            
                    <td align="center" class="<?php echo $StringClass; ?>" ><input type="checkbox" id="FollowUp<?php echo $CountIndex; ?>" 
                     name="FollowUp<?php echo $CountIndex; ?>" value="y" onClick="ActionFollowUp(<?php echo $CountIndex; ?>)"  /></td> 
                    <td  class="<?php echo $StringClass; ?> right"> <textarea  name="FollowUpReason<?php echo $CountIndex; ?>" onKeyDown="PreventIt(event)" id="FollowUpReason<?php echo $CountIndex; ?>" class="form-control class4" cols="5" rows="2" readonly ></textarea></td>
                  </tr>
                <?php
                    } while ($RowSearch = sqlFetchArray($ResultSearchNew));
                ?>
                 <tr class="text">
                    <td align="left" colspan="7">&nbsp;</td>
                    <td class="left bottom" bgcolor="#6699FF" id="initialallowtotal" align="right" >0</td>
                    <td class="left bottom" bgcolor="#6699FF" id="initialpaymenttotal" align="right" >0</td>
                    <td class="left bottom" bgcolor="#6699FF" id="initialAdjAmounttotal" align="right" >0</td>                        
                    <td class="left bottom" bgcolor="#6699FF" id="initialdeductibletotal" align="right">0</td>
                    <td class="left bottom right" bgcolor="#6699FF" id="initialtakebacktotal" align="right">0</td>
                    <td  align="center">&nbsp;</td>
                    <td  align="center">&nbsp;</td>
                  </tr>
                </table>
                </div>
                <br>
                <?php
                }//if($RowSearch = sqlFetchArray($ResultSearchNew))
                ?>
        <!--</td>
    </tr>
</table>
</div>-->
