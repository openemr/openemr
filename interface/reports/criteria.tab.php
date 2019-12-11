<?php
/**
 * This displays the search criteria.The master processing is done here.This page
 * is included in the billing_report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Billing\BillingReport;

?>

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
<div class="flex-column">
    <fieldset>
        <legend><?php echo xlt('Choose Criteria'); ?></legend>
        <div class="form-group px-2">
          <label for="choose_this_page_criteria"><?php echo xlt('Select list'); ?>:</label>
            <select name="choose_this_page_criteria" id="choose_this_page_criteria" title="Choose Criteria" class="text col" onChange="CriteriaVisible()" size='8' >
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
                <div class="form-group px-2" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "">
                        <label for="choose_this_page_criteria"><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?></label>
                        <?php echo generate_select_list(
                            "date_master_criteria_$DateNamePart",
                            "date_master_criteria",
                            $_REQUEST["date_master_criteria_$DateNamePart"],
                            "Date Criteria",
                            "",
                            "form-control",
                            'calendar_function(this.value,' . attr_js('master_from_date_'.$DateNamePart) . ',' . attr_js('master_to_date_'.$DateNamePart) . ');
                                appendOptionDateCriteria(' . attr_js($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]) . ',' .
                                attr_js($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) . ',this.options[this.selectedIndex].text,' .
                                'this.options[this.selectedIndex].value," = ",' . attr_js('master_from_date_'.$DateNamePart) . ',' . attr_js('master_to_date_'.$DateNamePart) . ',
                                ' . attr_js($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]) . ')'
                        );
                        ?>
                    </div>
                    <div class= "">
                        <label class="control-label" for="master_from_date_<?php echo attr($DateNamePart); ?>'"><?php echo xlt('From'); ?>:</label>
                        <input type='text'
                                name='master_from_date_<?php echo attr($DateNamePart); ?>'
                                id='master_from_date_<?php echo attr($DateNamePart); ?>' class="text form-control datepicker"
                                value="<?php echo attr($_REQUEST["master_from_date_$DateNamePart"]) ?>"
                                onChange="SetDateCriteriaCustom(<?php echo attr_js('date_master_criteria_'.$DateNamePart); ?>);
                                appendOptionDateCriteria(<?php echo attr_js($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]);?>,
                                <?php echo attr_js($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);?>,
                                <?php echo attr(xlj('Custom')); ?>,
                                <?php echo attr(xlj('Custom')); ?>,
                                ' = ',<?php echo attr_js('master_from_date_'.$DateNamePart); ?>,<?php echo attr_js('master_to_date_'.$DateNamePart); ?>,
                                <?php echo attr_js($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]); ?>)" />
                    </div>
                    <div class= "">
                        <label class="control-label" for="check_date"><?php echo xlt('To{{Range}}'); ?>:</label>
                        <input type='text'
                                name='master_to_date_<?php echo attr($DateNamePart); ?>'
                                id='master_to_date_<?php echo attr($DateNamePart); ?>' class="text form-control datepicker"
                                value="<?php echo attr($_REQUEST["master_to_date_$DateNamePart"]) ?>"
                                onChange="SetDateCriteriaCustom(<?php echo attr_js('date_master_criteria_'.$DateNamePart); ?>);
                                appendOptionDateCriteria(<?php echo attr_js($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]);?>,
                                <?php echo attr_js($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]); ?>,
                                <?php echo attr(xlj('Custom')); ?>,
                                <?php echo attr(xlj('Custom')); ?>,
                                ' = ',<?php echo attr_js('master_from_date_'.$DateNamePart); ?>,<?php echo attr_js('master_to_date_'.$DateNamePart); ?>,
                                <?php echo attr_js($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]); ?>)" />
                    </div>
                </div>
                <?php
            } //end of if
            ?>
            <?php
            if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='query_drop_down') {
                $array_query_drop_down = BillingReport::BuildArrayForReport($ThisPageSearchCriteriaQueryDropDownMaster[$ThisPageSearchCriteriaQueryDropDown[$ThisPageSearchCriteriaIndex]]);
                $QueryDropDownNamePart=str_replace('.', '_', $ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]);
                ?>
                <div class="form-group px-2" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "">
                        <label class="control-label" for="query_drop_down_master_<?php echo attr($QueryDropDownNamePart); ?>"><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>:</label>
                        <select  name="query_drop_down_master_<?php echo attr($QueryDropDownNamePart); ?>"
                        id="query_drop_down_master_<?php echo attr($QueryDropDownNamePart); ?>" onchange="appendOptionRadioCriteria(
                        <?php echo attr_js($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>,
                        <?php echo attr_js($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]); ?>,this.options[this.selectedIndex].text,
                        this.options[this.selectedIndex].value,' = ',
                        <?php echo attr_js($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]); ?>)">
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
                <div class="form-group px-2" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "">
                        <label class="control-label" for=""><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>:</label>
                        <div <?php //Don't Use class =  'form-control'?>>
                            <?php $FunctionName = $ThisPageSearchCriteriaIncludeMaster[$ThisPageSearchCriteriaInclude[$ThisPageSearchCriteriaIndex]];
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
                <div class="form-group px-2" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "">
                        <label class="control-label" for="text_master_<?php echo attr($TextNamePart);?>"><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>:</label>
                        <input type="text"  name="text_master_<?php echo attr($TextNamePart);?>"
                          id="text_master_<?php echo attr($TextNamePart);?>" value="<?php echo attr($_REQUEST["text_master_$TextNamePart"]) ?>"
                        onkeyup="appendOptionTextCriteria(<?php echo attr_js($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>,
                        <?php echo attr_js($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]); ?>,this.value,this.value,<?php echo attr_js($TextSeperator); ?>,
                        <?php echo attr_js($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]); ?>)"
                        onchange="appendOptionTextCriteria(<?php echo attr_js($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>,
                        <?php echo attr_js($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]); ?>,this.value,this.value,<?php echo attr_js($TextSeperator); ?>,
                        <?php echo attr_js($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]); ?>)"
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
                <div class="form-group px-2" id="table_<?php echo attr($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]) ?>" style="display:none">
                    <div class= "">
                        <label class="control-label" for="radio_<?php echo attr($RadioNamePart) ?>"><?php echo text($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>:</label>
                        <?php
                        if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio') {
                            $RadioSeperator=' = ';
                        }
                        if ($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]=='radio_like') {
                            $RadioSeperator=' like ';
                        }
                        for ($ThisPageSearchCriteriaRadioIndex=0; $ThisPageSearchCriteriaRadioIndex<sizeof($ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex]); $ThisPageSearchCriteriaRadioIndex++) {
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
                        onClick="appendOptionRadioCriteria(<?php echo attr_js($ThisPageSearchCriteriaDisplay[$ThisPageSearchCriteriaIndex]); ?>,
                            <?php echo attr_js($ThisPageSearchCriteriaKey[$ThisPageSearchCriteriaIndex]); ?>,
                            <?php echo attr_js($ThisPageSearchCriteriaDisplayRadio[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]); ?>,
                            <?php echo attr_js($ThisPageSearchCriteriaRadioKey[$ThisPageSearchCriteriaIndex][$ThisPageSearchCriteriaRadioIndex]); ?>,
                            <?php echo attr_js($RadioSeperator); ?>,<?php echo attr_js($ThisPageSearchCriteriaDataType[$ThisPageSearchCriteriaIndex]); ?>)" />
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
<!-- Current Criteria -->
<div class="form-group flex-column">
    <fieldset>
        <legend><?php echo xlt('Current Criteria'); ?></legend>
        <div class="form-group px-2">
            <label for="final_this_page_criteria"><?php echo xlt('Criteria Actions') . ':'; ?></label>
            <span class="float-right">
                <i class="fa fa-trash fa-2x text-warning" aria-hidden="true" onclick="removeOptionSelected()" title="<?php echo xla('Click here to delete the selection'); ?>"></i>&nbsp;
                <i class="fa fa-trash fa-2x text-danger" aria-hidden="true" onclick="removeOptionsAll()" title="<?php echo xla('Click here to delete all options'); ?>"></i>
            </span>
            <select name='final_this_page_criteria[]' id='final_this_page_criteria' class='form-control' size="8" title='Criteria' multiple="multiple">
                <?php
                if ($_REQUEST['final_this_page_criteria']) {
                    for ($final_this_page_criteria_index = 0; $final_this_page_criteria_index < sizeof($_REQUEST['final_this_page_criteria']); $final_this_page_criteria_index++) {
                        ?>
                        <option value="<?php echo attr($_REQUEST['final_this_page_criteria'][$final_this_page_criteria_index]) ?>">
                            <?php echo xlt($_REQUEST['final_this_page_criteria_text'][$final_this_page_criteria_index]) ?></option>
                        <?php
                    }
                }
                ?>
            </select>
            <select name='final_this_page_criteria_text[]' id='final_this_page_criteria_text' style="display:none" multiple="multiple">
                <?php
                if ($_REQUEST['final_this_page_criteria']) {
                    for ($final_this_page_criteria_index = 0; $final_this_page_criteria_index < sizeof($_REQUEST['final_this_page_criteria']); $final_this_page_criteria_index++) {
                        ?>
                        <option value="<?php echo attr($_REQUEST['final_this_page_criteria_text'][$final_this_page_criteria_index]) ?>">
                            1
                        </option>
                        <?php
                    }
                }
                ?>
            </select>
        </div>
    </fieldset>
</div>
<div class="form-group flex-column">
    <fieldset>
        <legend><?php echo xlt('Select Action'); ?></legend>
        <div class="form-group">
            <ul>
                <li><a class="link_submit" href="#"
                        onclick="javascript:return SubmitTheScreen();"><strong><?php echo xlt('Update List') ?></strong>
                    </a><i id='update-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i></li>
                <li><a class='link_submit' href="#"
                        onclick="javascript:return SubmitTheScreenExportOFX();"><strong><?php echo xlt('Export OFX'); ?></strong></a>
                </li>
                <li><a class='link_submit' href="#" onclick="javascript:return SubmitTheScreenPrint();"><strong><?php echo xlt('View Printable Report'); ?></strong></a>
                </li>
                <span>
                <?php if ($daysheet) { ?>
                    <li><a class='link_submit' href="#"
                        onclick="javascript:return SubmitTheEndDayPrint();"><strong><?php echo xlt('End Of Day Report') . ' - ' ?></strong></a>
                    <?php if ($daysheet_total) { ?>
                        <span class="text"><strong><?php echo xlt('Totals'); ?></strong></span>
                        <input name="end_of_day_totals_only" type="checkbox" value="1"></li>
                    <?php } ?>
                    <?php if ($provider_run) { ?>
                        <span class="text"><strong><?php echo xlt('Provider'); ?></strong></span>
                        <input name="end_of_day_provider_only" type="checkbox" value="1"></li>
                    <?php } ?>
                <?php } ?>
                </span>
                <?php if (!file_exists($EXPORT_INC)) { ?>
                    <li><a href='#' id="view-log-link" data-toggle="modal" data-target="#myModal" class='link_submit'
                            title='<?php echo xla('See messages from the last set of generated claims'); ?>'><strong><?php echo xlt('View Log'); ?></strong></a>
                    </li>
                <?php } ?>
                <li><a href="<?php echo $webroot ?>/interface/billing/customize_log.php" rel="noopener" target="_blank" onclick="top.restoreSession()"><strong><?php echo xlt('Tab Log') ?></strong></a>
                </li>
                <li><a class="link_submit"
                        href="JavaScript:void(0);" onclick="select_all(); return false;"><strong><?php echo xlt('Select All'); ?></strong></a>
                </li>
                <li><a id="clear-log" href="#" title='<?php xla('Clear the log'); ?>'><strong><?php echo xlt('Clear Log') ?></strong></a>
                </li>
            </ul>
        </div>
    </fieldset>
</div>
<!-- Criteria section Ends -->
