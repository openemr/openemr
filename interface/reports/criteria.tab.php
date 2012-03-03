<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
?>
<!-- This displays the search criteria.The master processing is done here.This page is included in the billing_report.php  -->
<style>
.criteria_class1{width:125px;}
.criteria_class2{padding-left:5px;}
</style>
<?php
$ThisPageSearchCriteriaKey=array();
$ThisPageSearchCriteriaDataType=array();
$ThisPageSearchCriteriaDisplay=array();

$ThisPageSearchCriteriaRadioKey=array();
$ThisPageSearchCriteriaDisplayRadio=array();

$ThisPageSearchCriteriaQueryDropDown=array();
$ThisPageSearchCriteriaQueryDropDownDefault=array();
$ThisPageSearchCriteriaQueryDropDownDefaultKey=array();

$ThisPageSearchCriteriaInclude=array();
//Filling the input array.
$ThisPageSearchCriteriaDisplay=$ThisPageSearchCriteriaDisplayMaster;
$ThisPageSearchCriteriaKey=split(',',$ThisPageSearchCriteriaKeyMaster);
$ThisPageSearchCriteriaDataType=split(',',$ThisPageSearchCriteriaDataTypeMaster);
//--------------------------------------------------------------
//Filling the input array.
$NumberOfRadioThisPageSearchCriteria=0;
$NumberOfQueryDropDownThisPageSearchCriteria=0;
$NumberOfIncludeThisPageSearchCriteria=0;
for($ThisPageSearchCriteriaIndex=0;$ThisPageSearchCriteriaIndex<sizeof($ThisPageSearchCriteriaDataType);$ThisPageSearchCriteriaIndex++) 
 {
    if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio' || $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio_like')
     {
        $NumberOfRadioThisPageSearchCriteria++;
        $ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex]=$ThisPageSearchCriteriaDisplayRadioMaster[$NumberOfRadioThisPageSearchCriteria];
        $ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex]=split(',',$ThisPageSearchCriteriaRadioKeyMaster[$NumberOfRadioThisPageSearchCriteria]);
     }
    if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='query_drop_down')
     {
        $NumberOfQueryDropDownThisPageSearchCriteria++;
        $ThisPageSearchCriteriaQueryDropDown[$ThisPageSearchCriteriaIndex]=$NumberOfQueryDropDownThisPageSearchCriteria;
        $ThisPageSearchCriteriaQueryDropDownDefault[$ThisPageSearchCriteriaIndex]=
                                            $ThisPageSearchCriteriaQueryDropDownMasterDefault[$NumberOfQueryDropDownThisPageSearchCriteria];
        $ThisPageSearchCriteriaQueryDropDownDefaultKey[$ThisPageSearchCriteriaIndex]=
                                            $ThisPageSearchCriteriaQueryDropDownMasterDefaultKey[$NumberOfQueryDropDownThisPageSearchCriteria];
     }
    if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='include')
     {
        $NumberOfIncludeThisPageSearchCriteria++;
        $ThisPageSearchCriteriaInclude[$ThisPageSearchCriteriaIndex]=$NumberOfIncludeThisPageSearchCriteria;
     }
 }
//------------------------------------------------------------------------------
?>
        <table width="560" border="0" cellspacing="0" cellpadding="0" >
          <tr>
            <td><fieldset style="border-color:#000000; border-width:1px;padding-left:5px;padding-right:0px;padding-top:0px;padding-bottom:0px;" >
                <legend class='text'><b><?php echo htmlspecialchars( xl('Choose Criteria'), ENT_QUOTES) ?></b></legend>
                <table width="290" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class='text'><?php echo htmlspecialchars( xl('Criteria'), ENT_QUOTES) ?></td>
                    <td ></td>
                  </tr>
                  <tr>
                    <td width="140" >
                    <select name="choose_this_page_criteria" id="choose_this_page_criteria" title="Choose Criteria" 
                    class="text" style="width:140px;"  onChange="CriteriaVisible()" size='8' >
                    <?php 
                      for ($ThisPageSearchCriteriaIndex=0;$ThisPageSearchCriteriaIndex<sizeof($ThisPageSearchCriteriaKey);$ThisPageSearchCriteriaIndex++) 
                      {
                        $optionValue = $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex];
                        echo "<option value='".attr($optionValue)."'";
                        $optionLabel = $ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex];
                        echo ">".text($optionLabel)."</option>\n";
                      }
                    ?>
                    </select>                
                    </td>
                    <td width="150"  valign="top">
                    <!-- Below section comes as per the defined criteria arrays.Initially all are hidden.As per the click the corresponding items gets visible. -->
                        <?php 
                          for ($ThisPageSearchCriteriaIndex=0;$ThisPageSearchCriteriaIndex<sizeof($ThisPageSearchCriteriaKey);$ThisPageSearchCriteriaIndex++) 
                          {
                            if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='date' ||
                            $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='datetime')
                             {
                              $DateNamePart=str_replace('.','_',$ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
                        ?>
                                <table width="150" border="0" cellspacing="0" cellpadding="0" 
                                    id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                                  <tr>
                                    <td class='text criteria_class2' ><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?></td>
                                  </tr>
                                  <tr>
                                    <td width="150" class='text criteria_class2' ><?php echo generate_select_list("date_master_criteria_$DateNamePart", 
                                    "date_master_criteria", $_REQUEST["date_master_criteria_$DateNamePart"], 
                                    "Date Criteria","","text criteria_class1",
                                    'calendar_function(this.value,"master_from_date_'.$DateNamePart.'","master_to_date_'.$DateNamePart.'");
                                    appendOptionDateCriteria("'.attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]).'","'.
                                    $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex].'",this.options[this.selectedIndex].text,'.
                                    'this.options[this.selectedIndex].value," = ","master_from_date_'.$DateNamePart.'","master_to_date_'.$DateNamePart.'",
                                    "'.$ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex].'")');?>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class='text' align="right" style="padding-right:5px;padding-bottom:2px;padding-top:2px">
                                        <?php echo htmlspecialchars( xl('From'), ENT_QUOTES).':' ?><input type='text' size='7' 
                                        name='master_from_date_<?php echo $DateNamePart;?>' 
                                        id='master_from_date_<?php echo $DateNamePart;?>' class="text " readonly=""  
                                        value="<?php echo attr($_REQUEST["master_from_date_$DateNamePart"]) ?>"
                                        onChange="SetDateCriteriaCustom('date_master_criteria_<?php echo $DateNamePart;?>');
                                        appendOptionDateCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]);?>',
                                        '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex];?>',
                                        '<?php echo htmlspecialchars( xl('Custom'), ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars( xl('Custom'), ENT_QUOTES); ?>',
                                        ' = ','master_from_date_<?php echo $DateNamePart;?>','master_to_date_<?php echo $DateNamePart;?>',
                                        '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')" />&nbsp;
                                        <img src="<?php echo $web_root ?>/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg" 
                                        align="texttop"    id='img_master_fromdate_<?php echo $DateNamePart;?>' border='0' alt='[?]' style='cursor:pointer'
                                        title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
                                       <script>
                                        Calendar.setup({inputField:"master_from_date_<?php echo $DateNamePart;?>", ifFormat:"%Y-%m-%d", button:"img_master_fromdate_<?php echo $DateNamePart;?>"});
                                       </script>                            
                                   </td>
                                  </tr>
                                  <tr>
                                    <td class='text' align="right" style="padding-right:5px">
                                        <?php echo htmlspecialchars( xl('To'), ENT_QUOTES).':' ?><input type='text' size='7' 
                                        name='master_to_date_<?php echo $DateNamePart;?>' 
                                        id='master_to_date_<?php echo $DateNamePart;?>' class="text " readonly=""
                                        value="<?php echo attr($_REQUEST["master_to_date_$DateNamePart"]) ?>"
                                        onChange="SetDateCriteriaCustom('date_master_criteria_<?php echo $DateNamePart;?>');
                                        appendOptionDateCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]);?>',
                                        '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex];?>',
                                        '<?php echo htmlspecialchars( xl('Custom'), ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars( xl('Custom'), ENT_QUOTES); ?>',
                                        ' = ','master_from_date_<?php echo $DateNamePart;?>','master_to_date_<?php echo $DateNamePart;?>',
                                        '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')" />&nbsp;
                                        <img src="<?php echo $web_root ?>/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/new.jpg" 
                                        align="texttop"    id='img_master_todate_<?php echo $DateNamePart;?>' border='0' alt='[?]' style='cursor:pointer'
                                        title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>' />
                                       <script>
                                        Calendar.setup({inputField:"master_to_date_<?php echo $DateNamePart;?>", ifFormat:"%Y-%m-%d", button:"img_master_todate_<?php echo $DateNamePart;?>"});
                                       </script>                            
                                   </td>
                                  </tr>
                                </table>
                        <?php }?>                
                        <?php 
                            if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='query_drop_down')
                             {
                              $array_query_drop_down=BuildArrayForReport($ThisPageSearchCriteriaQueryDropDownMaster[$ThisPageSearchCriteriaQueryDropDown[$ThisPageSearchCriteriaIndex]]);
                              $QueryDropDownNamePart=str_replace('.','_',$ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
                        ?>
                            <table width="150" border="0" cellspacing="0" cellpadding="0" 
                                id="table_<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>" style="display:none">
                              <tr>
                                <td  class='text criteria_class2'  ><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?></td>
                              </tr>
                              <tr>
                                <td width="150" class='text criteria_class2' >
                                
                                <select style="width:140px;"  name="query_drop_down_master_<?php echo $QueryDropDownNamePart;?>" 
                                id="query_drop_down_master_<?php echo $QueryDropDownNamePart;?>" onchange="appendOptionRadioCriteria(
                                '<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) ?>',
                                    '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',this.options[this.selectedIndex].text,
                                    this.options[this.selectedIndex].value,' = ',
                                    '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')">
                                    
                                    <option value="<?php echo attr($ThisPageSearchCriteriaQueryDropDownDefaultKey[$ThisPageSearchCriteriaIndex]) ?>"
                                    ><?php echo text($ThisPageSearchCriteriaQueryDropDownDefault[$ThisPageSearchCriteriaIndex]) ?></option>
                                    
                                    <?php
                                        foreach($array_query_drop_down as $array_query_drop_down_key => $array_query_drop_down_value)
                                         {
                                          if($_REQUEST["query_drop_down_master_".$QueryDropDownNamePart]==$array_query_drop_down_key)
                                                    $Selected=' selected ';
                                          else
                                                    $Selected='';
                                    ?>
                                    <option value="<?php echo attr($array_query_drop_down_key) ?>" <?php echo $Selected ?> 
                                        ><?php echo text($array_query_drop_down_value) ?></option>
                                    <?php
                                         }
                                    ?>
                                </select>
                                
                                </td>
                              </tr>
                            </table>
                        <?php }?>                
                        <?php 
                            if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='include')
                             {
                              $IncludeNamePart=str_replace('.','_',$ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
                        ?>
                            <table width="150" border="0" cellspacing="0" cellpadding="0" 
                                id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                              <tr>
                                <td  class='text criteria_class2'  ><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?></td>
                              </tr>
                              <tr>
                                <td width="150" class='text criteria_class2' ><?php 
                                $FunctionName=$ThisPageSearchCriteriaIncludeMaster[$ThisPageSearchCriteriaInclude[$ThisPageSearchCriteriaIndex]];
                                $FunctionName();
                                ?></td>
                              </tr>
                            </table>
                        <?php }?>                
                        <?php 
                            if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='text' ||
                                $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='text_like')
                             {
                              $TextNamePart=str_replace('.','_',$ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
                              if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='text')
                               {
                                $TextSeperator=' = ';
                               } 
                              if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='text_like')
                               {
                                $TextSeperator=' like ';
                               } 
                        ?>
                            <table width="150" border="0" cellspacing="0" cellpadding="0" 
                                id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                              <tr>
                                <td  class='text criteria_class2'  ><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?></td>
                              </tr>
                              <tr>
                                <td width="150" class='text criteria_class2' ><input type="text"  name="text_master_<?php echo attr($TextNamePart);?>"
                                  id="text_master_<?php echo attr($TextNamePart);?>" value="<?php echo attr($_REQUEST["text_master_$TextNamePart"]) ?>"
                                onkeyup="appendOptionTextCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) ?>',
                                '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',this.value,this.value,'<?php echo $TextSeperator ?>',
                                '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')"  
                                onchange="appendOptionTextCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) ?>',
                                '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',this.value,this.value,'<?php echo $TextSeperator ?>',
                                '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')"  
                                size="15"  autocomplete="off" /></td>
                              </tr>
                            </table>
                        <?php }?>                
                        <?php 
                            if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio' || 
                                $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio_like')
                             {
                        ?>
                            <table width="150" border="0" cellspacing="0" cellpadding="0" 
                                id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                              <tr>
                                <td  class='text criteria_class2'   width="150" ><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?></td>
                              </tr>
                                <?php 
                                  if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio')
                                   {
                                       $RadioSeperator=' = ';
                                   } 
                                  if($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio_like')
                                   {
                                       $RadioSeperator=' like ';
                                   } 
                                  for ($ThisPageSearchCriteriaRadioIndex=0;
                                          $ThisPageSearchCriteriaRadioIndex<sizeof($ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex]);
                                            $ThisPageSearchCriteriaRadioIndex++) 
                                  {
                                  $RadioNamePart=str_replace('.','_',$ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
                                  if($_REQUEST["radio_".$RadioNamePart]==
                                                  $ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex])
                                            $Checked=' checked ';
                                  else
                                            $Checked='';
                                ?>
                                  <tr>
                                    <td class='text'><input type="radio" name="radio_<?php echo attr($RadioNamePart) ?>" 
                                        id="radio_<?php echo attr($RadioNamePart.$ThisPageSearchCriteriaRadioIndex) ?>" 
                                        value="<?php echo attr($ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]) ?>" 
                                        <?php echo  $Checked;?>
                                        onClick="appendOptionRadioCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) ?>',
                                        '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',
                                        '<?php echo attr($ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]) ?>',
                                        '<?php echo $ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex] ?>',
                                        '<?php echo $RadioSeperator ?>','<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')" />
                                        <?php echo text($ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]) ?>
                                    </td>
                                  </tr>
                                <?php 
                                  }
                                ?>                
                            </table>
                        <?php 
                          }
                        ?>                
                    <?php 
                      }
                    ?>                
                    </td>
                  </tr>
                </table>
                </fieldset>
            </td>
            <td valign="top"><fieldset style="border-color:#000000; border-width:1px;padding-left:5px;padding-right:0px;padding-top:0px;padding-bottom:0px;" >
                <legend class='text'><b><?php echo htmlspecialchars( xl('Current Criteria'), ENT_QUOTES) ?></b></legend>
                <table width="260" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="117" class='text'><?php echo htmlspecialchars( xl('Criteria'), ENT_QUOTES) ?></td>
                    <td width="118" class='text'><?php echo htmlspecialchars( xl('Set To'), ENT_QUOTES) ?></td>
                    <td width="25" class='text'></td>
                  </tr>
                  <tr>
                    <td colspan="2"><select name='final_this_page_criteria[]' id='final_this_page_criteria'  
                        size='8' style="width:235px;"   class='text'  title='Criteria' multiple="multiple" >
                        <?php 
                            for($final_this_page_criteria_index=0;$final_this_page_criteria_index<sizeof($_REQUEST['final_this_page_criteria']);
                                                                                    $final_this_page_criteria_index++)
                             {
                        ?> 
                        <option value="<?php echo attr($_REQUEST['final_this_page_criteria'][$final_this_page_criteria_index]) ?>" >
                            <?php echo xlt($_REQUEST['final_this_page_criteria_text'][$final_this_page_criteria_index]) ?></option>
                        <?php 
                             }
                        
                        ?>
                        </select>
                        <select name='final_this_page_criteria_text[]' id='final_this_page_criteria_text' style="display:none" multiple="multiple" > 
                        <?php 
                            for($final_this_page_criteria_index=0;$final_this_page_criteria_index<sizeof($_REQUEST['final_this_page_criteria']);
                                                                                    $final_this_page_criteria_index++)
                             {
                        ?> 
                        <option value="<?php echo attr($_REQUEST['final_this_page_criteria_text'][$final_this_page_criteria_index]) ?>" >1</option>
                        <?php 
                             }
                        
                        ?>
                        </select>
                        </td>
                    <td valign="top"><a href="#" onClick="removeOptionSelected()"><img src="<?php echo $web_root ?>/interface/pic/Delete.gif" border="0" /></a></td>
                  </tr>
                </table>
                </fieldset>
            </td>
          </tr>
        </table><?php //print_r($_REQUEST['final_this_page_criteria']); ?>
<!-- ============================================================================================================================================= -->
                                                        <!-- Criteria section Ends -->
<!-- ============================================================================================================================================= -->
