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
$ThisPageSearchCriteriaKey=explode(',', $ThisPageSearchCriteriaKeyMaster);
$ThisPageSearchCriteriaDataType=explode(',', $ThisPageSearchCriteriaDataTypeMaster);
//--------------------------------------------------------------
//Filling the input array.
$NumberOfRadioThisPageSearchCriteria=0;
$NumberOfQueryDropDownThisPageSearchCriteria=0;
$NumberOfIncludeThisPageSearchCriteria=0;
for ($ThisPageSearchCriteriaIndex=0; $ThisPageSearchCriteriaIndex<sizeof($ThisPageSearchCriteriaDataType); $ThisPageSearchCriteriaIndex++) {
    if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio' || $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio_like') {
        $NumberOfRadioThisPageSearchCriteria++;
        $ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex]=$ThisPageSearchCriteriaDisplayRadioMaster[$NumberOfRadioThisPageSearchCriteria];
        $ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex]=explode(',', $ThisPageSearchCriteriaRadioKeyMaster[$NumberOfRadioThisPageSearchCriteria]);
    }
    if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='query_drop_down') {
        $NumberOfQueryDropDownThisPageSearchCriteria++;
        $ThisPageSearchCriteriaQueryDropDown[$ThisPageSearchCriteriaIndex]=$NumberOfQueryDropDownThisPageSearchCriteria;
        $ThisPageSearchCriteriaQueryDropDownDefault[$ThisPageSearchCriteriaIndex]=
                                            $ThisPageSearchCriteriaQueryDropDownMasterDefault[$NumberOfQueryDropDownThisPageSearchCriteria];
        $ThisPageSearchCriteriaQueryDropDownDefaultKey[$ThisPageSearchCriteriaIndex]=
                                            $ThisPageSearchCriteriaQueryDropDownMasterDefaultKey[$NumberOfQueryDropDownThisPageSearchCriteria];
    }
    if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='include') {
        $NumberOfIncludeThisPageSearchCriteria++;
        $ThisPageSearchCriteriaInclude[$ThisPageSearchCriteriaIndex]=$NumberOfIncludeThisPageSearchCriteria;
    }
}
//------------------------------------------------------------------------------
?>
<div class="form-group col-xs-8">
    <fieldset>
        <legend><?php echo htmlspecialchars(xl('Choose Criteria'), ENT_QUOTES) ?></legend>
        <div class="form-group col-xs-6">
          <label for="choose_this_page_criteria"><?php echo  xlt('Select list'); ?>:</label>
            
            <select name="choose_this_page_criteria" id="choose_this_page_criteria" title="Choose Criteria" class="text col-xs-12"   onChange="CriteriaVisible()" size='8' >
                <?php
                for ($ThisPageSearchCriteriaIndex=0; $ThisPageSearchCriteriaIndex<sizeof($ThisPageSearchCriteriaKey); $ThisPageSearchCriteriaIndex++) {
                    $optionValue = $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex];
                    echo "<option value='".attr($optionValue)."'";
                    $optionLabel = $ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex];
                    echo ">".text($optionLabel)."</option>\n";
                }
                ?>
            </select>
        </div>
        <!-- Below section comes as per the defined criteria arrays.Initially all are hidden.As per the click the corresponding items gets visible. -->
        <?php
        for ($ThisPageSearchCriteriaIndex=0; $ThisPageSearchCriteriaIndex<sizeof($ThisPageSearchCriteriaKey); $ThisPageSearchCriteriaIndex++) {
            if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='date' ||
            $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='datetime') {
                $DateNamePart=str_replace('.', '_', $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
            ?>
                <div class="form-group col-xs-6" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "col-xs-12">
                        <label for="choose_this_page_criteria"><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?></label> 
                        <?php echo generate_select_list(
                            "date_master_criteria_$DateNamePart",
                            "date_master_criteria",
                            $_REQUEST["date_master_criteria_$DateNamePart"],
                            "Date Criteria",
                            "",
                            "form-control",
                            'calendar_function(this.value,"master_from_date_'.$DateNamePart.'","master_to_date_'.$DateNamePart.'");
                                appendOptionDateCriteria("'.attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]).'","'.
                                $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex].'",this.options[this.selectedIndex].text,'.
                                'this.options[this.selectedIndex].value," = ","master_from_date_'.$DateNamePart.'","master_to_date_'.$DateNamePart.'",
                                "'.$ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex].'")'
                        );
                        ?>
                    </div>
                    <div class= "col-xs-12">
                        <label class="control-label" for="master_from_date_<?php echo $DateNamePart;?>'"><?php echo xlt('From'); ?>:</label> 
                        <input type='text' 
                                name='master_from_date_<?php echo $DateNamePart;?>'
                                id='master_from_date_<?php echo $DateNamePart;?>' class="text form-control datepicker"
                                value="<?php echo attr($_REQUEST["master_from_date_$DateNamePart"]) ?>"
                                onChange="SetDateCriteriaCustom('date_master_criteria_<?php echo $DateNamePart;?>');
                                appendOptionDateCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]);?>',
                                '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex];?>',
                                '<?php echo htmlspecialchars(xl('Custom'), ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars(xl('Custom'), ENT_QUOTES); ?>',
                                ' = ','master_from_date_<?php echo $DateNamePart;?>','master_to_date_<?php echo $DateNamePart;?>',
                                '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')" />
                    </div>
                    <div class= "col-xs-12">
                        <label class="control-label" for="check_date"><?php echo xlt('To'); ?>:</label> 
                        <input type='text' 
                                name='master_to_date_<?php echo $DateNamePart;?>'
                                id='master_to_date_<?php echo $DateNamePart;?>' class="text form-control datepicker"
                                value="<?php echo attr($_REQUEST["master_to_date_$DateNamePart"]) ?>"
                                onChange="SetDateCriteriaCustom('date_master_criteria_<?php echo $DateNamePart;?>');
                                appendOptionDateCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]);?>',
                                '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex];?>',
                                '<?php echo htmlspecialchars(xl('Custom'), ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars(xl('Custom'), ENT_QUOTES); ?>',
                                ' = ','master_from_date_<?php echo $DateNamePart;?>','master_to_date_<?php echo $DateNamePart;?>',
                                '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')" />
                    </div>
                </div>
            <?php
            } //end of if
            ?> 
            <?php
            if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='query_drop_down') {
                $array_query_drop_down=BuildArrayForReport($ThisPageSearchCriteriaQueryDropDownMaster[$ThisPageSearchCriteriaQueryDropDown[$ThisPageSearchCriteriaIndex]]);
                $QueryDropDownNamePart=str_replace('.', '_', $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
            ?>
                <div class="form-group col-xs-6" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "col-xs-12">
                        <label class="control-label" for="query_drop_down_master_<?php echo $QueryDropDownNamePart;?>"><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>:</label> 
                        <select  name="query_drop_down_master_<?php echo $QueryDropDownNamePart;?>"
                        id="query_drop_down_master_<?php echo $QueryDropDownNamePart;?>" onchange="appendOptionRadioCriteria(
                        '<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) ?>',
                        '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',this.options[this.selectedIndex].text,
                        this.options[this.selectedIndex].value,' = ',
                        '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')">
                            <option value="<?php echo attr($ThisPageSearchCriteriaQueryDropDownDefaultKey[$ThisPageSearchCriteriaIndex]) ?>" ><?php echo text($ThisPageSearchCriteriaQueryDropDownDefault[$ThisPageSearchCriteriaIndex]) ?></option>
                            <?php
                            foreach ($array_query_drop_down as $array_query_drop_down_key => $array_query_drop_down_value) {
                                if ($_REQUEST["query_drop_down_master_".$QueryDropDownNamePart]==$array_query_drop_down_key) {
                                    $Selected=' selected ';
                                } else {
                                    $Selected='';
                                }
                            ?>
                                <option value="<?php echo attr($array_query_drop_down_key) ?>" <?php echo $Selected ?>><?php echo text($array_query_drop_down_value) ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php
            } //end of if
            ?>
            <?php
            if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='include') {
                $IncludeNamePart=str_replace('.', '_', $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
            ?>
                <div class="form-group col-xs-6" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "col-xs-12">
                        <label class="control-label" for=""><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>:</label> 
                        <div <?php //Don't Use class =  'form-control'?>>
                            <?php $FunctionName=$ThisPageSearchCriteriaIncludeMaster[$ThisPageSearchCriteriaInclude[$ThisPageSearchCriteriaIndex]];
                            $FunctionName();?>
                        </div>
                    </div>
                </div>
            <?php
            } //end of if
            ?>
            <?php
            if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='text' ||
                $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='text_like') {
                $TextNamePart=str_replace('.', '_', $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
                if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='text') {
                    $TextSeperator=' = ';
                }
                if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='text_like') {
                    $TextSeperator=' like ';
                }
            ?>
                <div class="form-group col-xs-6" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "col-xs-12">
                        <label class="control-label" for="text_master_<?php echo attr($TextNamePart);?>"><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>:</label> 
                        <input type="text"  name="text_master_<?php echo attr($TextNamePart);?>"
                          id="text_master_<?php echo attr($TextNamePart);?>" value="<?php echo attr($_REQUEST["text_master_$TextNamePart"]) ?>"
                        onkeyup="appendOptionTextCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) ?>',
                        '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',this.value,this.value,'<?php echo $TextSeperator ?>',
                        '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')"
                        onchange="appendOptionTextCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) ?>',
                        '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',this.value,this.value,'<?php echo $TextSeperator ?>',
                        '<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')"
                        class = "form-control"  autocomplete="off" />
                    </div>
                </div>
            <?php
            } //end of if
            ?>
            <?php
            if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio' ||
                $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio_like') {
            ?>
                <div class="form-group col-xs-6" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "col-xs-12">
                        <label class="control-label" for="radio_<?php echo attr($RadioNamePart) ?>"><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>:</label> 
                        <?php
                        if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio') {
                            $RadioSeperator=' = ';
                        }
                        if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio_like') {
                            $RadioSeperator=' like ';
                        }
                        for ($ThisPageSearchCriteriaRadioIndex=0;
                            $ThisPageSearchCriteriaRadioIndex<sizeof($ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex]);
                            $ThisPageSearchCriteriaRadioIndex++) {
                            $RadioNamePart=str_replace('.', '_', $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
                            if ($_REQUEST["radio_".$RadioNamePart]==
                            $ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]) {
                                $Checked=' checked ';
                            } else {
                                $Checked='';
                            }
                        ?>
                        <div class="radio">
                        <input type="radio" name="radio_<?php echo attr($RadioNamePart) ?>"
                        id="radio_<?php echo attr($RadioNamePart.$ThisPageSearchCriteriaRadioIndex) ?>"
                        value="<?php echo attr($ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]) ?>"
                        <?php echo  $Checked;?>
                        onClick="appendOptionRadioCriteria('<?php echo attr($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) ?>',
                        '<?php echo $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex] ?>',
                        '<?php echo attr($ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]) ?>',
                        '<?php echo $ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex] ?>',
                        '<?php echo $RadioSeperator ?>','<?php echo $ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex] ?>')" />
                        <?php echo text($ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]) ?>
                        <?php echo "</div>";
                        } // end of for
                        ?>    
                    </div>
                </div>
            <?php
            } //end of if
            ?>
        <?php
        } //end of for
        ?>
    </fieldset>
</div>
<div class="form-group col-xs-4">
    <fieldset>
        <legend><?php echo htmlspecialchars(xl('Current Criteria'), ENT_QUOTES) ?></legend>
        <div class="form-group col-xs-12">
            <label for="final_this_page_criteria" class="col-xs-12"><?php echo htmlspecialchars(xl('Criteria'), ENT_QUOTES) ?>  <?php echo htmlspecialchars(xl('Set To'), ENT_QUOTES) ?>: <i class="fa fa-times-circle fa-2x text-danger pull-right" style="margin-top:-7px" aria-hidden="true" onclick="removeOptionSelected()" title="<?php echo xlt('Click here to delete the selection'); ?>"></i></label>
            <select name='final_this_page_criteria[]' id='final_this_page_criteria' size='8' class='text col-xs-12'  title='Criteria' multiple="multiple" >
                <?php
                for ($final_this_page_criteria_index=0; $final_this_page_criteria_index<sizeof($_REQUEST['final_this_page_criteria']);
                $final_this_page_criteria_index++) {
                ?>
                    <option value="<?php echo attr($_REQUEST['final_this_page_criteria'][$final_this_page_criteria_index]) ?>" >
                    <?php echo xlt($_REQUEST['final_this_page_criteria_text'][$final_this_page_criteria_index]) ?></option>
                <?php
                }
                ?>
                </select>
                <select name='final_this_page_criteria_text[]' id='final_this_page_criteria_text' style="display:none" multiple="multiple" >
                <?php
                for ($final_this_page_criteria_index=0; $final_this_page_criteria_index<sizeof($_REQUEST['final_this_page_criteria']);
                $final_this_page_criteria_index++) {
                ?>
                    <option value="<?php echo attr($_REQUEST['final_this_page_criteria_text'][$final_this_page_criteria_index]) ?>" >1</option>
                <?php
                }
                ?>
            </select>
        </div>
    </fieldset>
</div>
<?php //print_r($_REQUEST['final_this_page_criteria']); ?>
<!-- ============================================================================================================================================= -->
                                                        <!-- Criteria section Ends -->
<!-- ============================================================================================================================================= -->
