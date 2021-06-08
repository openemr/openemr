<?php

/**
 * This displays the search criteria. The master processing is done here. This page
 * is included in the billing_report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Billing\BillingReport;

?>

<?php

// TPS = This Page Search

$TPSCriteriaKey = array();
$TPSCriteriaDataType = array();
$TPSCriteriaDisplay = array();
$TPSCriteriaRadioKey = array();
$TPSCriteriaDisplayRadio = array();
$TPSCriteriaQueryDropDown = array();
$TPSCriteriaQueryDropDownDefault = array();
$TPSCriteriaQueryDropDownDefaultKey = array();
$TPSCriteriaInclude = array();
// Filling the input array.
$TPSCriteriaDisplay = $TPSCriteriaDisplayMaster;
$TPSCriteriaKey = explode(',', $TPSCriteriaKeyMaster);
$TPSCriteriaDataType = explode(',', $TPSCriteriaDataTypeMaster);
// --------------------------------------------------------------
// Filling the input array.
// --------------------------------------------------------------
$NumberOfRadioTPSCriteria = 0;
$NumberOfQueryDropDownTPSCriteria = 0;
$NumberOfIncludeTPSCriteria = 0;
for ($TPSCriteriaIndex = 0; $TPSCriteriaIndex < sizeof($TPSCriteriaDataType); $TPSCriteriaIndex++) {
    if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'radio' || $TPSCriteriaDataType[$TPSCriteriaIndex] == 'radio_like') {
        $NumberOfRadioTPSCriteria++;
        $TPSCriteriaDisplayRadio[$TPSCriteriaIndex] = $TPSCriteriaDisplayRadioMaster[$NumberOfRadioTPSCriteria];
        $TPSCriteriaRadioKey[$TPSCriteriaIndex] = explode(',', $TPSCriteriaRadioKeyMaster[$NumberOfRadioTPSCriteria]);
    }
    if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'query_drop_down') {
        $NumberOfQueryDropDownTPSCriteria++;
        $TPSCriteriaQueryDropDown[$TPSCriteriaIndex] = $NumberOfQueryDropDownTPSCriteria;
        $TPSCriteriaQueryDropDownDefault[$TPSCriteriaIndex] = $TPSCriteriaQueryDropDownMasterDefault[$NumberOfQueryDropDownTPSCriteria];
        $TPSCriteriaQueryDropDownDefaultKey[$TPSCriteriaIndex] = $TPSCriteriaQueryDropDownMasterDefaultKey[$NumberOfQueryDropDownTPSCriteria];
    }
    if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'include') {
        $NumberOfIncludeTPSCriteria++;
        $TPSCriteriaInclude[$TPSCriteriaIndex] = $NumberOfIncludeTPSCriteria;
    }
}
//------------------------------------------------------------------------------
?>
<div class="row">
    <div class="col-md">
        <div class="card bg-light">
            <div class="card-header pb-0"><h4><?php echo xlt('Choose Criteria'); ?></h4></div>
            <div class="card-body">
                <div class="form-group px-2">
                    <label for="choose_this_page_criteria"><?php echo xlt('Select list'); ?>:</label>
                    <select name="choose_this_page_criteria" id="choose_this_page_criteria" title="Choose Criteria" class="form-control" onChange="CriteriaVisible()" size='8'>
                        <?php
                        for ($TPSCriteriaIndex = 0; $TPSCriteriaIndex < sizeof($TPSCriteriaKey); $TPSCriteriaIndex++) {
                            $optionValue = $TPSCriteriaKey[$TPSCriteriaIndex];
                            echo "<option value='" . attr($optionValue) . "'";
                            $optionLabel = $TPSCriteriaDisplay[$TPSCriteriaIndex];
                            echo ">" . text($optionLabel) . "</option>\n";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="card-footer">
            <!-- Below section comes as per the defined criteria arrays. Initially all are hidden. As per the click the corresponding items gets visible. -->
            <?php
            for ($TPSCriteriaIndex = 0; $TPSCriteriaIndex < sizeof($TPSCriteriaKey); $TPSCriteriaIndex++) {
                if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'date' || $TPSCriteriaDataType[$TPSCriteriaIndex] == 'datetime') {
                    $DateNamePart = str_replace('.', '_', $TPSCriteriaKey[$TPSCriteriaIndex]);
                    ?>
                    <div class="form-group px-2" id="table_<?php echo attr($TPSCriteriaKey[$TPSCriteriaIndex]) ?>" style="display: none">
                        <label for="choose_this_page_criteria"><?php echo text($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?></label>
                        <?php echo generate_select_list(
                            "date_master_criteria_$DateNamePart",
                            "date_master_criteria",
                            ($_REQUEST["date_master_criteria_$DateNamePart"] ?? ''),
                            "Date Criteria",
                            "",
                            "form-control",
                            'calendar_function(this.value,' . attr_js('master_from_date_' . $DateNamePart) . ',' . attr_js('master_to_date_' . $DateNamePart) . ');
                                appendOptionDateCriteria(' . attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]) . ',' .
                                attr_js($TPSCriteriaKey[$TPSCriteriaIndex]) . ',this.options[this.selectedIndex].text,' .
                                'this.options[this.selectedIndex].value," = ",' . attr_js('master_from_date_' . $DateNamePart) . ',' . attr_js('master_to_date_' . $DateNamePart) . ',
                                ' . attr_js($TPSCriteriaDataType[$TPSCriteriaIndex]) . ')'
                        );
                        ?>
                        <label class="control-label" for="master_from_date_<?php echo attr($DateNamePart); ?>'"><?php echo xlt('From'); ?>:</label>
                        <input type='text' name='master_from_date_<?php echo attr($DateNamePart); ?>' id='master_from_date_<?php echo attr($DateNamePart); ?>' class="text form-control datepicker" value="<?php echo attr($_REQUEST["master_from_date_$DateNamePart"] ?? '') ?>"
                            onChange="SetDateCriteriaCustom(<?php echo attr_js('date_master_criteria_' . $DateNamePart); ?>); appendOptionDateCriteria(<?php echo attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]);?>, <?php echo attr_js($TPSCriteriaKey[$TPSCriteriaIndex]);?>, <?php echo attr(xlj('Custom')); ?>, <?php echo attr(xlj('Custom')); ?>, ' = ', <?php echo attr_js('master_from_date_' . $DateNamePart); ?>, <?php echo attr_js('master_to_date_' . $DateNamePart); ?>, <?php echo attr_js($TPSCriteriaDataType[$TPSCriteriaIndex]); ?>)" />

                        <label class="control-label" for="check_date"><?php echo xlt('To{{Range}}'); ?>:</label>
                        <input type='text' name='master_to_date_<?php echo attr($DateNamePart); ?>' id='master_to_date_<?php echo attr($DateNamePart); ?>' class="text form-control datepicker" value="<?php echo attr($_REQUEST["master_to_date_$DateNamePart"] ?? '') ?>"
                            onChange="SetDateCriteriaCustom(<?php echo attr_js('date_master_criteria_' . $DateNamePart); ?>); appendOptionDateCriteria(<?php echo attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]);?>, <?php echo attr_js($TPSCriteriaKey[$TPSCriteriaIndex]); ?>, <?php echo attr(xlj('Custom')); ?>, <?php echo attr(xlj('Custom')); ?>, ' = ', <?php echo attr_js('master_from_date_' . $DateNamePart); ?>, <?php echo attr_js('master_to_date_' . $DateNamePart); ?>, <?php echo attr_js($TPSCriteriaDataType[$TPSCriteriaIndex]); ?>)" />
                    </div>
                    <?php
                } //end of if
                ?>
                <?php
                if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'query_drop_down') {
                    $array_query_drop_down = BillingReport::buildArrayForReport($TPSCriteriaQueryDropDownMaster[$TPSCriteriaQueryDropDown[$TPSCriteriaIndex]]);
                    $QueryDropDownNamePart = str_replace('.', '_', $TPSCriteriaKey[$TPSCriteriaIndex]);
                    ?>
                    <div class="form-group px-2" id="table_<?php echo attr($TPSCriteriaKey[$TPSCriteriaIndex]) ?>" style="display: none">
                        <label class="control-label" for="query_drop_down_master_<?php echo attr($QueryDropDownNamePart); ?>"><?php echo text($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?>:</label>
                        <select class="form-control" name="query_drop_down_master_<?php echo attr($QueryDropDownNamePart); ?>" id="query_drop_down_master_<?php echo attr($QueryDropDownNamePart); ?>"
                            onchange="appendOptionRadioCriteria(<?php echo attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?>, <?php echo attr_js($TPSCriteriaKey[$TPSCriteriaIndex]); ?>,this.options[this.selectedIndex].text, this.options[this.selectedIndex].value,' = ', <?php echo attr_js($TPSCriteriaDataType[$TPSCriteriaIndex]); ?>)">
                            <option value="<?php echo attr($TPSCriteriaQueryDropDownDefaultKey[$TPSCriteriaIndex]) ?>" ><?php echo text($TPSCriteriaQueryDropDownDefault[$TPSCriteriaIndex]) ?></option>
                            <?php
                            foreach ($array_query_drop_down as $array_query_drop_down_key => $array_query_drop_down_value) {
                                if ($_REQUEST["query_drop_down_master_" . $QueryDropDownNamePart] == $array_query_drop_down_key) {
                                    $Selected = ' selected ';
                                } else {
                                    $Selected = '';
                                }
                                ?>
                                <option value="<?php echo attr($array_query_drop_down_key) ?>" <?php echo $Selected ?>><?php echo text($array_query_drop_down_value) ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <?php
                } //end of if
                ?>
                <?php
                if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'include') {
                    $IncludeNamePart = str_replace('.', '_', $TPSCriteriaKey[$TPSCriteriaIndex]);
                    ?>
                    <div class="form-group px-2" id="table_<?php echo attr($TPSCriteriaKey[$TPSCriteriaIndex]) ?>" style="display: none">
                        <label class="control-label" for=""><?php echo text($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?>:</label>
                        <div <?php //Don't Use class =  'form-control'?>>
                            <?php $FunctionName = $TPSCriteriaIncludeMaster[$TPSCriteriaInclude[$TPSCriteriaIndex]];
                            $FunctionName();?>
                        </div>
                    </div>
                    <?php
                } //end of if
                ?>
                <?php
                if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'text' || $TPSCriteriaDataType[$TPSCriteriaIndex] == 'text_like') {
                    $TextNamePart = str_replace('.', '_', $TPSCriteriaKey[$TPSCriteriaIndex]);
                    if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'text') {
                        $TextSeperator = ' = ';
                    }
                    if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'text_like') {
                        $TextSeperator = ' like ';
                    }
                    ?>
                    <div class="form-group px-2" id="table_<?php echo attr($TPSCriteriaKey[$TPSCriteriaIndex]) ?>" style="display: none">
                        <label class="control-label" for="text_master_<?php echo attr($TextNamePart);?>"><?php echo text($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?>:</label>
                        <input type="text" name="text_master_<?php echo attr($TextNamePart);?>" id="text_master_<?php echo attr($TextNamePart);?>" value="<?php echo attr($_REQUEST["text_master_$TextNamePart"] ?? '') ?>" onkeyup="appendOptionTextCriteria(<?php echo attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?>, <?php echo attr_js($TPSCriteriaKey[$TPSCriteriaIndex]); ?>, this.value, this.value, <?php echo attr_js($TextSeperator); ?>, <?php echo attr_js($TPSCriteriaDataType[$TPSCriteriaIndex]); ?>)" onchange="appendOptionTextCriteria(<?php echo attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?>, <?php echo attr_js($TPSCriteriaKey[$TPSCriteriaIndex]); ?>,this.value,this.value,<?php echo attr_js($TextSeperator); ?>, <?php echo attr_js($TPSCriteriaDataType[$TPSCriteriaIndex]); ?>)" class="form-control" autocomplete="off" />
                    </div>
                    <?php
                } //end of if
                ?>
                <?php
                if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'radio' || $TPSCriteriaDataType[$TPSCriteriaIndex] == 'radio_like') {
                    ?>
                    <div class="form-group px-2" id="table_<?php echo attr($TPSCriteriaKey[$TPSCriteriaIndex]) ?>" style="display: none">
                        <label class="control-label" for="radio_<?php echo attr($RadioNamePart ?? ''); ?>"><?php echo text($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?>:</label>
                        <?php
                        if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'radio') {
                            $RadioSeperator = ' = ';
                        }
                        if ($TPSCriteriaDataType[$TPSCriteriaIndex] == 'radio_like') {
                            $RadioSeperator = ' like ';
                        }
                        for ($TPSCriteriaRadioIndex = 0; $TPSCriteriaRadioIndex < sizeof($TPSCriteriaDisplayRadio[$TPSCriteriaIndex]); $TPSCriteriaRadioIndex++) {
                            $RadioNamePart = str_replace('.', '_', $TPSCriteriaKey[$TPSCriteriaIndex]);
                            if (!empty($_REQUEST["radio_" . $RadioNamePart]) && ($_REQUEST["radio_" . $RadioNamePart] == $TPSCriteriaRadioKey[$TPSCriteriaIndex][$TPSCriteriaRadioIndex])) {
                                $Checked = ' checked ';
                            } else {
                                $Checked = '';
                            }
                            ?>
                        <div class="radio">
                            <input type="radio" name="radio_<?php echo attr($RadioNamePart) ?>" id="radio_<?php echo attr($RadioNamePart . $TPSCriteriaRadioIndex) ?>" value="<?php echo attr($TPSCriteriaRadioKey[$TPSCriteriaIndex][$TPSCriteriaRadioIndex]) ?>" <?php echo $Checked;?> onClick="appendOptionRadioCriteria(<?php echo attr_js($TPSCriteriaDisplay[$TPSCriteriaIndex]); ?>, <?php echo attr_js($TPSCriteriaKey[$TPSCriteriaIndex]); ?>, <?php echo attr_js($TPSCriteriaDisplayRadio[$TPSCriteriaIndex][$TPSCriteriaRadioIndex]); ?>, <?php echo attr_js($TPSCriteriaRadioKey[$TPSCriteriaIndex][$TPSCriteriaRadioIndex]); ?>, <?php echo attr_js($RadioSeperator); ?>, <?php echo attr_js($TPSCriteriaDataType[$TPSCriteriaIndex]); ?>)" />
                            <?php echo text($TPSCriteriaDisplayRadio[$TPSCriteriaIndex][$TPSCriteriaRadioIndex]);
                            echo "</div>";
                        } // end of for
                        ?>
                    </div>
                    <?php
                } //end of if
            } //end of for
            ?>
        </div>
        </div>
    </div>
    <!-- Current Criteria -->
    <div class="col-md">
        <div class="card bg-light">
            <div class="card-header pb-0"><h4><?php echo xlt('Current Criteria'); ?></h4></div>
            <div class="card-body">
                <div class="form-group px-2">
                    <label for="final_this_page_criteria"><?php echo xlt('Criteria Actions') . ':'; ?></label>
                    <span class="float-right">
                        <i class="fa fa-trash fa-2x text-warning" aria-hidden="true" onclick="removeOptionSelected()" title="<?php echo xla('Click here to delete the selection'); ?>"></i>&nbsp;
                        <i class="fa fa-trash fa-2x text-danger" aria-hidden="true" onclick="removeOptionsAll()" title="<?php echo xla('Click here to delete all options'); ?>"></i>
                    </span>
                    <select name='final_this_page_criteria[]' id='final_this_page_criteria' class='form-control' size="8" title='Criteria' multiple="multiple">
                        <?php
                        if (!empty($_REQUEST['final_this_page_criteria'])) {
                            for ($final_this_page_criteria_index = 0; $final_this_page_criteria_index < sizeof($_REQUEST['final_this_page_criteria']); $final_this_page_criteria_index++) {
                                ?>
                                <option value="<?php echo attr($_REQUEST['final_this_page_criteria'][$final_this_page_criteria_index]) ?>">
                                    <?php echo xlt($_REQUEST['final_this_page_criteria_text'][$final_this_page_criteria_index]) ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                    <select name='final_this_page_criteria_text[]' id='final_this_page_criteria_text' style="display: none" multiple="multiple">
                        <?php
                        if (!empty($_REQUEST['final_this_page_criteria'])) {
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
            </div>
        </div>
    </div>
<div class="col-md">
    <div class="card bg-light">
        <div class="card-header pb-0"><h4><?php echo xlt('Select Action'); ?></h4></div>
        <div class="card-body">
            <div class="form-group">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item bg-light d-flex justify-content-between align-items-center">
                        <a class="link_submit" href="#" onclick="javascript:return SubmitTheScreen();"><strong><?php echo xlt('Update List') ?></strong></a><i id='update-tooltip' class="fa fa-info-circle fa-lg text-primary" aria-hidden="true"></i>
                    </li>
                    <?php if (file_exists("$webserver_root/custom/BillingExport.php")) { ?>
                        <li class="list-group-item bg-light">
                            <a class='link_submit' href="#" onclick="javascript:return SubmitTheScreenExportOFX();"><strong><?php echo xlt('Export OFX'); ?></strong></a>
                        </li>
                    <?php } ?>
                    <li class="list-group-item bg-light">
                        <a class='link_submit' href="#" onclick="javascript:return SubmitTheScreenPrint();"><strong><?php echo xlt('View Printable Report'); ?></strong></a>
                    </li>
                    <span>
                    <?php if ($daysheet) { ?>
                        <li class="list-group-item bg-light"><a class='link_submit' href="#" onclick="javascript:return SubmitTheEndDayPrint();"><strong><?php echo xlt('End Of Day Report') . ' - ' ?></strong></a>
                        <?php if ($daysheet_total) { ?>
                            <span class="text"><strong><?php echo xlt('Totals'); ?></strong></span>
                            <input name="end_of_day_totals_only" type="checkbox" value="1">
                        <?php } ?>
                        <?php if ($provider_run) { ?>
                            <span class="text"><strong><?php echo xlt('Provider'); ?></strong></span>
                            <input name="end_of_day_provider_only" type="checkbox" value="1">
                        <?php } ?>
                        </li>
                    <?php } ?>
                    </span>
                    <?php if (!file_exists($EXPORT_INC)) { ?>
                        <li class="list-group-item bg-light"><a href='#' id="view-log-link" data-toggle="modal" data-target="#myModal" class='link_submit' title='<?php echo xla('See messages from the last set of generated claims'); ?>'><strong><?php echo xlt('View Log'); ?></strong></a>
                        </li>
                    <?php } ?>
                    <li class="list-group-item bg-light"><a href="<?php echo $webroot ?>/interface/billing/customize_log.php" rel="noopener" target="_blank" onclick="top.restoreSession()"><strong><?php echo xlt('Tab Log') ?></strong></a>
                    </li>
                    <li class="list-group-item bg-light"><a class="link_submit" href="JavaScript:void(0);" onclick="select_all(); return false;"><strong><?php echo xlt('Select All'); ?></strong></a>
                    </li>
                    <li class="list-group-item bg-light"><a id="clear-log" href="#" title='<?php xla('Clear the log'); ?>'><strong><?php echo xlt('Clear Log') ?></strong></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
<!-- Criteria section Ends -->
