<?php
    /**
     * interface/super/rules/controllers/detail/view/view.php
     *
     * @package   OpenEMR
     * @link      https://www.open-emr.org
     * @author    Aron Racho <aron@mi-squared.com>
     * @author    Brady Miller <brady.g.miller@gmail.com>
     * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
     * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
     * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
     */
    
    require_once($GLOBALS["srcdir"] . "/options.inc.php");
?>
<div class="title hidden" style="display:none"><span
            <?php // this will display the TAB title
            echo xlt('CR{{Clinical Reminder abbreviation}}'); ?>:
    <?php
         if ($rule->title) {
             $in = text($rule->title);
             echo strlen($in) > 10 ? substr($in, 0, 10) . "..." : $in;
         } else { echo xlt('Manager');
         }
        ?></span>
</div>

<input type="hidden" id="ruleId" name="ruleId" value="<?php echo attr($rule->id); ?>">
<script language="javascript" src="<?php js_src('detail.js') ?>" xmlns="http://www.w3.org/1999/html"></script>
<script type="text/javascript">
    var detail = new rule_detail( {editable: <?php echo $rule->isEditable() ? "true":"false"; ?>});
    detail.init();
</script>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="header">
                <div class="title"><?php echo xlt('Clinical Reminder'); ?>: <i class="red"><?php echo xlt($rule->title); ?></i>  </div>
                <div id="show_summary_report" class="red">&nbsp;</div>
            </div>
        </div>
        <div class="col-6" id="show_summary">
            <!-- summary -->
            <div class="section text-center row" >
                <button class="btn-sm btn-primary icon_2"
                        id="edit_summary"
                        title="Edit this Rule."><i class="fa fa-pencil"></i>
                </button>
                <button class="btn-sm btn-primary icon_1"
                        data-toggle="modal"
                        data-backdrop="false"
                        data-target="#help_summary"
                        id="show_summary_help"
                        title="Open the Help:: Summary Modal"><i class="fa fa-question"></i>
                </button>
                <div class="col-12 text-left">
                    <span class="title "><?php echo xlt('Summary'); ?> </span>
                </div>
                <div id="show_summary_1" class="col-12">
                    <table class="table table-sm table-condensed text-left">
                        <tr>
                            <td class="text-right">
                                <span class="underline"><?php echo xlt('Name'); ?>:</span>
                            </td>

                            <td colspan="3" class="table-100"><?php echo xlt($rule->title); ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <span class="underline"><?php echo xlt('Alert Type'); ?>:</span></td>
                            <td colspan="3"><?php
                                    $intervals = $rule->reminderIntervals;
                                    $provider = $intervals->getDetailFor('provider');
                                    foreach (ReminderIntervalType::values() as $type) {
                                        foreach (ReminderIntervalRange::values() as $range) {
                                            $first = true;
                                            $detail = $intervals->getDetailFor($type, $range);
                                            $detail->timeUnit;
                                            $timings[$type->code][$range->code]['timeUnit'] =  $detail->timeUnit->code;
                                            $timings[$type->code][$range->code]['amount'] = $detail->amount;
                                            if ($timings[$type->code][$range->code]['amount'] >'1') {
                                                $timings[$type->code][$range->code]['timeUnit2'] =$timings[$type->code][$range->code]['timeUnit']."s";
                                            } else {
                                                $timings[$type->code][$range->code]['timeUnit2']= $timings[$type->code][$range->code]['timeUnit'];
                                            }
                                        }
                                    }
                                    
                                    $more='';
                                    $something='';
                                    
                                    foreach (RuleType::values() as $type) {
                                        if ($rule->hasRuleType(RuleType::from($type))) {
                                            $something=1;
                                        }
                                    }
                                    
                                    
                                    if ($something) {
                                        if ($rule->hasRuleType(RuleType::from('activealert')) || $rule->hasRuleType(RuleType::from('passivealert'))) {
                                            $timing = "<div><span class='bolder red'>This is a Clinical Alert!</span></div>";
                                        }
                                        if ($rule->hasRuleType(RuleType::from('activealert')) && $rule->hasRuleType(RuleType::from('passivealert'))) {
                                            $timing .= "<div class='indent10'>This CR has both an <span class='bold' data-toggle='popover' title='Active Alerts' data-content='A Pop-up will occur when the demographics page is opened.'>Active Alert</span> (pop-up) and a <span class='bold' data-toggle='popover' title='Passive Alerts' data-content='These alerts appear inside the CR widget.  There is a Global setting to create a Pop-up for Passive alerts also.'>Passive Alert</span>
                                                (in the <a href='#' data-toggle='popover' data-trigger='hover' data-placement='auto' title='Clinical Reminders Widget(CR)' data-content='The CR Widget is located on the demographics page.'>CR Widget</a>).";
                                            // Look at Custom input in actions to see if true.  How do we find that in this OOP goop?  Help Brady please...
                                            $timing .=" If any Treatment Goal in this CR needs to be marked 'Completed', a link in the CR widget will open a pop-up to do this and/or add a note.<br />";
                                            $timing .= "After ".$timings['clinical']['pre']['amount'] . " " . $timings['clinical']['pre']['timeUnit2'] . ", this CR is marked Due.<br />";
                                            $timing .= "After ".$timings['clinical']['post']['amount'] . " " . $timings['clinical']['post']['timeUnit2'] . ", this CR will be marked as Past Due.<br />";
                                            $timing .= "</div>";
                                        } elseif ($rule->hasRuleType(RuleType::from('activealert'))) {
                                            $timing .= "<div class='indent10'>An <span class='bold'>Active Alert</span> will pop-up when the chart is opened.</div>";
                                            if (empty($dueDate)) {
                                                $dueDate = " will no longer pop-up or appear in the the <a href='#'
                                                                    data-toggle='popover'
                                                                    data-trigger='hover'
                                                                    data-placement='auto'
                                                                    title='Clinical Reminders Widget(CR)'
                                                                    data-content='The CR Widget is located on the demographics page.'>CR Widget</a>";
                                                $timing .="<div class='indent10'>If not completed after ".$timings['clinical']['post']['amount']." ".$timings['clinical']['post']['timeUnit2'].", this pop-up will expire.</div>
                                                                <small>Note: If you also make it a Passive Alert, it will appear in the CR widget. You can then elect to have a pop-up note appear as part of your work-flow prompts after this CR fires.  It will allow you where to add a Note and mark this as 'Completed'.
                                                                When a Passive Alert goes 'Past due', instead of just expiring and no longer appearing, this alert will remain in the CR widget until the action you chose is completed, or you mark it completed..
                                                                 Remember also that an Alert can be limited to who receives the alert via <a href='../../usergroup/adminacl.php'>Access Control List Administration</a></small>";
                                            }
                                        } elseif ($rule->hasRuleType(RuleType::from('passivealert'))) {
                                            $timing .= "<div class='indent10'>A <span class='bold'>Passive Alert</span> will appear in the
                                                <a href='#' data-toggle='popover'
                                                            data-trigger='hover'
                                                            data-placement='auto'
                                                            title='Clinical Reminders Widget(CR)'
                                                            data-content='The CR Widget is located on the demographics page.'>CR Widget</a>.<BR />";
                                            
                                            $timing .="After ".$timings['clinical']['pre']['amount']." ".$timings['clinical']['pre']['timeUnit2'].", this CR will be marked as Due.<br />".
                                                "After ".$timings['clinical']['post']['amount']." ".$timings['clinical']['post']['timeUnit2']." it is marked as Past Due.<br />";
                                            $timing .="Alerts stop when their Treatment Goal is satisfied.  If any Treatment Goal in this CR needs to be marked 'Completed', a link in the CR widget will open a pop-up to do this and/or add a note.";
                                            $timing .= "</span></div>";
                                        }
                                        if ($rule->hasRuleType(RuleType::from('patientreminder'))) {
                                            $timing .= "<div><span class='bolder red'>This is a Patient Reminder!</span></div>";
                                            $timing .= "<div class='indent10'><span class='bold'>Patient Reminder</span>: A message will ".$more." be sent to the patient. ";
                                            if ($GLOBALS['medex_enable'] == '1') {
                                                $timing .="<br /><a href='https://medexbank.com/'>MedEx</a> will send an e-mail, SMS text and/or a voice message as requested.</div>";
                                            }
                                        }
                                        if ($rule->hasRuleType(RuleType::from('provideralert'))) {
                                            $timing .= "<div><span class='bolder red'>This CR has a Provider Alert!</span></div>";
                                            $timing .= "<div class='indent10'><span class='bold'>Provider Alert</span>: A message will be sent to the provider.";
                                            if ($GLOBALS['medex_enable'] == '1') {
                                                $timing .="<br /><a href='https://medexbank.com/'>MedEx</a> will send an e-mail, SMS text and/or a voice message as requested.</div>";
                                            }
                                        }
                                    } else {
                                        $timing = "<span class='bold'>".xlt('None.  Edit this CR to create an Alert!')."</span><br />";
                                    }
                                    
                                    
                                    
                                    
                                    
                                    echo $timing;  ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <span class="underline"><?php echo xlt('Developer'); ?>:</span></td>
                            <td><?php echo text($rule->developer); ?></td>
                            <td class="text-right">
                                <span class="underline"><?php echo xlt('Funding Source'); ?>:</span></td>
                            <td><?php echo text($rule->funding_source)?:"None"; ?></td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <span class="underline"><?php echo xlt('Release'); ?>:</span></td>
                            <td><?php echo text($rule->release); ?></td>
                            <td class="text-right">
                                <span data-toggle='popover'
                                      title='Reference'
                                      data-html="true"
                                      data-trigger='hover'
                                      data-placement='right'
                                      data-content='References appear in the Dashboard CR widget as <i class="fa fa-link"></i>.  This is clickable link, taking you to the url added here.
                                      It is suggested to link out to relevant clinical information, perhaps a government publication explaining why this CR exists.
                                      However, you can link to anything desired.
                                                    <img width="250px" src="<?php echo $GLOBAL['webroot'];?>/interface/super/rules/www/CR_widget.png">'>
                                    <span class="underline"> <i class="fa fa-link"></i> <?php echo xlt('Reference'); ?>:</span></span>
                            </td>
                            <td><a href="<?php echo attr($rule->web_ref); ?>"><?php echo text($rule->web_ref); ?></a></td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <span data-toggle='popover'
                                      title='Public Description'
                                      data-html="true"
                                      data-trigger='hover'
                                      data-placement='auto'
                                      data-content='The text here will be displayed in the CR widget via a tooltip.
                                        Use it to describe to your staff what this CR means.
                                        Note the text of the CR is also a link, either to a pop-up for a note/completion, or to an external link.
                                        This value is set separately for each Treatment Goal and is defined in the last step of this process (see PROMPTING YOU TO DO THIS below). '>
                                    <span class="underline"><?php echo xlt('Description'); ?></span>:
                                </span>
                            </td>
                            <td colspan="3">
                                <?php echo attr($rule->public_description); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-6" id="show_summary_edit" style="display: none;">
            <div class="section row">
                <button
                        class="btn-sm btn-primary icon_2"
                        id="show_intervals_help"
                        data-toggle="modal" data-target="#help_intervals"
                        title="Open the Help:: Actions Modal"><i class="fa fa-clock-o"></i>
                </button>

                <button class="btn-sm btn-primary icon_1"
                        id="save_summary"
                        title="<?php echo xla('Save this Rule'); ?>"><i class="fa fa-save"> <?php //echo xlt('Save'); ?></i>
                </button>
                <div class="col-12 text-left">
                    <span class="title "><?php echo xlt('Summary'); ?> </span>
                    <table class="table table-sm table-condensed text-left">
                        <tr>
                            <td class="text-right align-baseline">
                                *<span class="underline"><?php echo xlt('Name'); ?>:</span>
                            </td>
                            <td>
                                <input type="text" name="summary_title" class="field" id="fld_title" value="<?php echo attr($rule->title); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right align-baseline">
                                <span class="underline align-text-top"><?php echo xlt('Alert Type'); ?>:</span>
                            </td>
                            <td>
                                <?php
                                    foreach (RuleType::values() as $type) {?>
                                        <label><input name="fld_ruleTypes[]"
                                                      value="<?php echo attr($type); ?>"
                                                      type="checkbox" <?php echo $rule->hasRuleType(RuleType::from($type)) ? "CHECKED": "" ?>>
                                            <?php echo text(RuleType::from($type)->lbl); ?>
                                        </label>
                                    <?php }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right align-baseline">
                                <span class="text-right underline"><?php echo xlt('Developer'); ?>:</span>
                            </td>
                            <td class="text-left">
                                <input type="text" name="summary_developer" class="field" id="fld_developer" value="<?php echo attr($rule->developer); ?>" maxlength="255">
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <span class="underline align-middle"><?php echo xlt('Funding Source'); ?>:</span>
                            </td>
                            <td class="text-left">
                                <input type="text" name="summary_funding_source" class="FORM-CONTROL field" id="fld_funding_source" value="<?php echo attr($rule->funding_source); ?>" maxlength="255">
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <span class="underline align-middle"><?php echo xlt('Release'); ?>:</span>
                            </td>
                            <td class="text-left">
                                <input type="text" name="summary_release" class="field" id="fld_release" value="<?php echo attr($rule->release); ?>" maxlength="255">
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right"><span
                                        data-toggle='popover'
                                        title='Reference'
                                        data-html="true"
                                        data-trigger='hover'
                                        data-placement='auto'
                                        data-content='References appear in the Dashboard CR widget as <i class="fa fa-link"></i> and can link to anything desired.
                                                    <img width="250px" src="<?php echo $GLOBAL['webroot'];?>/interface/super/rules/www/CR_widget.png">'
                                        class="underline"><?php echo xlt('Reference'); ?><i class="fa fa-link"></i>:
                            </td>
                            <td class="text-left">
                                <input type="text" name="summary_web_reference" class="field" id="fld_web_reference" value="<?php echo attr($rule->web_ref); ?>" maxlength="255">
                            </td>
                        </tr>
                        <tr>
                            <td class="text-right">
                                <span data-toggle='popover'
                                      title='Public Description'
                                      data-html="true"
                                      data-trigger='hover'
                                      data-placement='auto'
                                      data-content='The text here will be displayed in the CR widget via a tooltip.  Use it to describe to your staff what this CR means.'>
                                    <span class="underline"><?php echo xlt('Description'); ?></span>:
                                </span>
                            </td>
                            <td>
                                <textarea class="form-control"
                                          id="fld_public_description"
                                          name="summary_public_description"><?php echo attr($rule->public_description); ?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="summary_report"></div>
                <div id="required_msg" class="col-11 offset-1">
                    * Developer, Funding Source, Release and Reference are MU2 requirements.

                </div>
            </div>
        </div>
        <div class="col-6">
            <!-- rule filter criteria -->
            <?php $filters = $rule->filters; if ($filters) { ?>
                <div class="section row" id="show_filters">
                    <button id="add_filters"
                            onclick="top.restoreSession()"
                            class="btn-sm btn-primary icon_2"
                            title='<?php echo xla('Refine the Target'); ?>'><i class="fa fa-plus"></i>
                    </button>
                    <button class="btn-sm btn-primary icon_1"
                            id="show_filters_help"
                            data-toggle="modal" data-target="#help_filters"
                            title="Open the Help:: Who will this CR affect?"><i class="fa fa-question"></i>
                    </button>

                    <div class="col-sm-12">
                        <span class="title text-left"><?php echo xlt('Step 1: Who are we targeting?'); ?> </span>

                        <table class="table table-hover table-sm table-condensed">
                            <thead>
                            <tr>
                                <th scope="col" class="text-center underline">
                                    <span class="underline"><?php echo xlt('Edit'); ?></span>
                                </th>
                                <th scope="col" class="text-center underline">
                                    <span>Delete</span></th>
                                <th scope="col" class="text-center underline">
                                    <?php echo xlt('Look at:'); ?></th>
                                <th scope="col" class="text-center underline">
                                    <?php echo xlt('Look For'); ?></th>
                                <th scope="col" class="text-center underline">
                                    <?php echo xlt('Possible Targets'); ?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                if ($filters->criteria) {
                                    
                                    foreach ($filters->criteria as $criteria) { ?>

                                        <tr>
                                            <td scope="row">
                                                <button id="edit_filter_<?php echo attr_url($criteria->uid); ?>"
                                                        title="Edit this Criteria."
                                                        class="btn btn-sm btn-primary"><i class="fa fa-pencil"></i>
                                                </button>
                                            </td>
                                            <td>
                                                <button onclick="top.restoreSession();location.href='index.php?action=edit!delete_filter&id=<?php echo attr_url($rule->id); ?>&rf_uid=<?php echo attr_url($criteria->uid); ?>'"
                                                        class="btn btn-sm btn-danger"
                                                        title="Remove this criterion."><i class="fa fa-trash-o"></i>
                                                </button>
                                            </td>
                                            <td class="text-center"><?php echo( text($criteria->getTitle()) ); ?></td>
                                            <td class="text-center"><?php echo( $criteria->getRequirements() ); ?></td>
                                            <td class="text-center"><?php echo( text($criteria->getCharacteristics()) ); ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td class="text-center text-middle" colspan="5"><?php echo xlt('All patients are targeted.  Please refine your selection criteria.'); ?></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php } ?>
            <div class="section row" id="show_filters_edit">
            </div>
        </div>
        <div class="section row col-12">
                <?php
                    $nextGroupId = 0;
                    //we cannot use count.  If two new groups are created, group 1&2, and group 1 is deleted
                    //newgroup will still be 3, not count(groups) + 1
                    foreach ($rule->groups as $group) {
                        $nextGroupId = $group->groupId + 1;
                    }
                ?>
                <div class="col-12">
                    <button type="button"
                            id="new_group_<?php echo (int)($nextGroupId);?>"
                            class="btn-sm btn-primary icon_2"
                            data-toggle='popover'
                            data-trigger="hover"
                            data-placement="auto left"
                            data-html="true"
                            data-title='Step 2: Add A New Group'
                            data-content="<span class='text-justify'>Having narrowed your target group of patients in <span class='bold'>Step 1</span>,
                            now in <span class='bold'>Step 2</span> you need to look for an item.
                            If present, an alert fires prompting you to do something, usually a Treatment Goal.
                            Most CRs only need to reference one Treatment Goal.
                            You can create multiple <span class='bold'>Step 2</span> criteria for a given group of patients identified in <span class='bold'>Step 1</span>.
                            Remember each Treatment Goal is displayed separately in the Dashboard's CR widget
                            and each can trigger a separate Active Alert.  Be wary of Alert Fatigue!
                            If you wish to fire multiple Alerts for a Targeted group, consider using Care Plans to combine Alerts.
                            Expert use only...</span>"
                            title='<?php echo xla('Add New Group'); ?>'><i class="fa fa-plus"></i>
                    </button>
                    <button type="button"
                            class="btn-sm btn-primary icon_1"
                            data-toggle="modal" data-target="#help_targets"
                            title="<?php echo xla('Open the Help Page').":: ".xla('When will this CR fire?'); ?>"><i class="fa fa-question"></i>
                    </button>

                    <span class="title text-left"><?php echo xlt('Step 2').": ".xlt('When will this CR fire?'); ?></span>
                </div>
                <?php
                    foreach ($rule->groups as $group) {
                        ?>
                        <div class="row" id="show_group_<?php echo xla($group->groupId); ?>">
                            <div class="col-6 inline">
                                <button type="button"
                                        id="add_criteria_target_<?php echo xla($group->groupId);?>"
                                        class="btn-sm btn-primary icon_2"
                                        title='<?php echo xla('Add New Target'); ?>'><i class="fa fa-plus"></i>
                                </button>
                                <button
                                        class="btn-sm btn-primary icon_1"
                                        id="show_actions_help"
                                        data-toggle="modal" data-target="#help_alerts"
                                        title="Open the Help:: Actions Modal"><i class="fa fa-question"></i>
                                </button>
                                <div class="col-12 title2"> <?php echo xlt('If we need this to happen'); ?>:</div>
                                <div class="col-12" id="show_targets_<?php echo xla($group->groupId); ?>">
                                    <table class="table table-sm table-condensed bgcolor2 section2">
                                        <thead>
                                        <tr>
                                            <td class="text-center underline">
                                                <?php echo xlt('Edit'); ?>
                                            </td>
                                            <td class="text-center underline">
                                                <?php echo xlt('Delete'); ?>
                                            </td>
                                            <td class="text-center underline" colspan="3">
                                                <?php echo xlt('Look at:'); ?>
                                            </td>
                                            <td class="text-center underline" colspan="3">
                                                <?php echo xlt('Look for:'); ?>
                                            </td>
                                            <td class="text-center underline" colspan="3">
                                                <?php echo xlt('Cohort:'); ?>
                                            </td>
                                        </tr>
                                        </thead>
                                        <!-- rule target criteria -->
                                        <?php
                                            //$groupId = $group->groupId;
                                            $targets = $group->ruleTargets;
                                            if ($targets) {
                                                if ($targets->criteria) {
                                                    foreach ($targets->criteria as $criteria) { ?>
                                                        <tr>
                                                            <td class="text-center">
                                                                <button id="edit_target_<?php echo attr_url($group->groupId); ?>_<?php echo attr_url($criteria->uid); ?>"
                                                                        class="btn btn-sm btn-primary indent10"
                                                                        title="Edit this Clinical Target."><i class="fa fa-pencil"></i>
                                                                </button>
                                                            </td>

                                                            <td class="text-center">
                                                                <button onclick="top.restoreSession();location.href='index.php?action=edit!delete_target&id=<?php echo attr_url($rule->id); ?>&group_id=<?php echo attr_url($group->groupId); ?>&rt_uid=<?php echo attr_url($criteria->uid); ?>';"
                                                                        class="btn btn-sm btn-danger indent10"
                                                                        title="Delete this Clinical Target."><i class="fa fa-trash-o"></i>
                                                                </button>
                                                            </td>
                                                            <td colspan="3" class="text-center"><?php echo(text($criteria->getTitle())); ?></td>
                                                            <td colspan="3" class="nowrap"><?php
                                                                    $show = str_replace('|','<br />', text($criteria->getRequirements()));
                                                                    echo $show; ?>
                                                                <?php echo is_null($criteria->getInterval()) ? "" : " <br /> " . xlt('Interval') . ": " . text($criteria->getInterval()); ?>
                                                            </td>
                                                            <td colspan="3" class="text-center"><?php echo(text($criteria->getCharacteristics())); ?></td>

                                                        </tr>
                                                    <?php }
                                                } else { ?>
                                                    <tr><td><?php echo xlt('None defined'); ?></td></tr>
                                                    <?php
                                                }
                                            } ?>
                                    </table>
                                </div>
                                <div id="show_targets_edit_<?php echo xla($group->groupId); ?>"></div>
                            </div>
                            <div class="col-2" class="display intervals">
                                <span class="title2 text-center"><?php echo xlt('This happens:'); ?></span>
                                <span class="title2 bold text-center">
                                <?php
    
                                    if (!$something) {
                                        echo "<br /><span class='bold'>".xlt('There are no Alerts selected!')."</span><br />";
                                    } else {
                                        if ($rule->hasRuleType(RuleType::from('activealert'))) {
                                            echo "Active alert<br />";
                                        }
                                        if ($rule->hasRuleType(RuleType::from('passivealert'))) {
                                            echo "<br />Passive alert<br />";
                                        }
                                        if ($rule->hasRuleType(RuleType::from('patientreminder'))) {
                                            echo "<br />Patient Reminder<br />";
                                        }
                                        if ($rule->hasRuleType(RuleType::from('provideralert'))) {
                                            echo "<br />Provider alert<br />";
                                        }
        
                                    }
                                    //echo $timing;
                                ?><br />
                                </span>
                            </div>
                            <div class="col-4 inline row">
                                <button type="button"
                                        class="btn-sm btn-primary icon_2"
                                        data-toggle='popover'
                                        data-trigger="hover"
                                        data-placement="auto left"
                                        data-html="true"
                                        data-content="<span class='text-justify'>Having narrowed your target group of patients in <span class='bold'>Step 1</span>,
                            now in <span class='bold'>Step 2</span> you need to look for an item.
                            If present, an alert fires prompting you to do something, usually a Treatment Goal.
                            Most CRs only need to reference one Treatment Goal.
                            You can create multiple <span class='bold'>Step 2</span> criteria for a given group of patients identified in <span class='bold'>Step 1</span>.
                            Remember each Treatment Goal is displayed separately in the Dashboard's CR widget
                            and each can trigger a separate Active Alert.  Be wary of Alert Fatigue!
                            If you wish to fire multiple Alerts for a Targeted group, consider using Care Plans to combine Alerts.
                            Expert use only...</span>"
                                        id="add_action_<?php echo (int)($group->groupId); ?>"
                                        title='<?php echo xla('Add New Treatment Goal'); ?>'><i class="fa fa-plus"></i>
                                </button>
                                <button
                                        class="btn-sm btn-primary icon_1"
                                        id="show_actions_help"
                                        data-toggle="modal" data-target="#help_intervals2"
                                        title="Open the Help:: Actions Modal"><i class="fa fa-question"></i>
                                </button>
                                <div class="title2 text-left col-12"><?php echo xlt('Prompting you to do this'); ?>:</div>
                                <div class="col-12" id="show_actions_<?php echo xla($group->groupId); ?>">
                                    <table class="table table-sm table-condensed bgcolor2 section2 text-center">
                                        <thead>
                                        <tr>
                                            <td class="underline">
                                                <?php echo xlt('Edit'); ?>
                                            </td>
                                            <td class="underline">
                                                <?php echo xlt('Delete'); ?>
                                            </td>
                                            <td class="underline" colspan="4">
                                                <?php echo xlt('Treatment Goal'); ?>
                                            </td>
                                            <td class="underline">
                                                Confirm pop-up?
                                            </td>
                                            <td class="underline">Link Out</td>
                                        </tr>
                                        </thead>
                                        <?php
                                            $actions = $group->ruleActions;
                                            
                                            if ($actions) {
                                            if ($actions->actions) {
                                                
                                                foreach ($actions->actions as $action) {   ?>
                                                    <tr class="baseboard">
                                                        <td>
                                                            <button id="edit_action_<?php echo attr_url($group->groupId); ?>_<?php echo attr_url($action->ra_uid); ?>"
                                                                    class="btn btn-sm btn-primary"
                                                                    title="Edit this Action."><i class="fa fa-pencil"></i>
                                                            </button>
                                                        </td>

                                                        <td>
                                                            <button onclick="top.restoreSession();location.href='index.php?action=edit!delete_action&id=<?php echo attr_url($rule->id); ?>&group_id=<?php echo attr_url($group->groupId); ?>&ra_uid=<?php echo attr_url($action->ra_uid); ?>';"
                                                                    class="btn btn-sm btn-danger"
                                                                    title="Delete this Clinical Target."><i class="fa fa-trash-o"></i>
                                                            </button>
                                                        </td>
                                                        <td colspan="4">
                                                            <?php echo text($action->getTitle()); ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                                if ($action->customRulesInput==1) {
                                                                    echo xlt('Yes');
                                                                } else {
                                                                    echo xlt('No');
                                                                } ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                                if (!empty($action->reminderLink)) {
                                                                    echo "<a href='".attr_url('$action->reminderLink')."' target='_blank'>".xlt('Yes')."</a>";
                                                                } else {
                                                                    echo xlt('No');
                                                                } ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else { ?>
                                                <tr>
                                                    <td class="text-center" colspan="6">
                                                        <?php echo xlt('None defined'); ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            }
                                        // iteration over groups ?>
                                    </table>
                                </div>
                                <div class="col-12" id="show_actions_edit_<?php echo xla($group->groupId); ?>"></div>
                            </div>
                        </div>
                        <?php
                    } ?>
                <div class="row col-12" id="show_group_<?php echo xla($nextGroupId); ?>">
                    <div class="col-6 inline row">
                        <button type="button"
                                id="add_criteria_target_<?php echo xla($nextGroupId);?>"
                                class="btn-sm btn-primary icon_2"
                                title='<?php echo xla('Add New Target'); ?>'><i class="fa fa-plus"></i>
                        </button>
                        <button
                                class="btn-sm btn-primary icon_1"
                                id="show_actions_help"
                                data-toggle="modal" data-target="#help_alerts"
                                title="Open the Help:: Actions Modal"><i class="fa fa-question"></i>
                        </button>
                        <span class="title2 text-left"><?php echo xlt('If we need this to happen:'); ?></span>
                        <div id="show_targets_<?php echo attr($nextGroupId); ?>"></div>
                        <div id="show_targets_edit_<?php echo attr($nextGroupId); ?>"></div>
                    </div>
                    <div class="col-2" class="display intervals">
                        <span class="title2 text-center"><?php echo xlt('This happens:'); ?></span>
                        <span class="title2 bold text-center">
                                <?php
    
                                    if (!$something) {
                                        echo "<br /><span class='bold'>".xlt('There are no Alerts selected!')."</span><br />";
                                    } else {
                                        if ($rule->hasRuleType(RuleType::from('activealert'))) {
                                            echo "Active alert<br />";
                                        }
                                        if ($rule->hasRuleType(RuleType::from('passivealert'))) {
                                            echo "<br />Passive alert<br />";
                                        }
                                        if ($rule->hasRuleType(RuleType::from('patientreminder'))) {
                                            echo "<br />Patient Reminder<br />";
                                        }
                                        if ($rule->hasRuleType(RuleType::from('provideralert'))) {
                                            echo "<br />Provider alert<br />";
                                        }
        
                                    }
                                    //echo $timing;
                                ?><br />
                            </span>
                    </div>

                    <div class="col-4 row">
                        <button type="button"
                                id="add_action_<?php echo $nextGroupId; ?>"
                                class="btn-sm btn-primary icon_A2"
                                title='<?php echo xla('Add New Action'); ?>'><i class="fa fa-plus"></i>
                        </button>
                        <button
                                class="btn-sm btn-primary icon_A1"
                                id="show_actions_help"
                                data-toggle="modal" data-target="#help_intervals2"
                                title="Open the Help:: Actions Modal"><i class="fa fa-question"></i>
                        </button>
                        <span class="title2 text-left"><?php echo xlt('Prompting you to do this'); ?>:</span>

                        <div id="show_actions_<?php echo attr($nextGroupId);?>" class="col-12" ></div>
                        <div id="show_actions_edit_<?php echo attr($nextGroupId);?>" class="col-12" ></div>
                    </div>
                </div>
            </div>

    </div>

    <!-- Help Modals -->
    <div id="help_alerts" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title title"><?php echo xlt('Step2: When will this CR Fire'); ?>:</h5>
                    <button type="button" class="close" data-dismiss="modal"  aria-label="Close">&times;</button>
                </div>
                <div class="modal-body row text-justify">
                    <div class="col-12">
                        <p>How does OpenEMR determine when to show/stop showing an Alert?
                        </p>
                        <table class="table table-hover text-center">
                            <tr>
                                <td class="bold text-center" rowspan="2">Alert Type</tdclass>
                                <td class="text-center" rowspan="2"><span class="title2">If This is True:</span><b>Completed</b></td>
                                <td class="text-center" rowspan="2"><span class="title2">Action<br />Prompted</span><br /><b>Custom Input</b></td>
                                <td colspan="3"> User Experiences:</td>
                            </tr>
                            <tr>
                                <td  class="bold text-center"><?php echo xlt('Due soon'); ?></td>
                                <td  class="bold text-center"><?php echo xlt('Due'); ?></td>
                                <td  class="bold text-center"><?php echo xlt('Pas Due'); ?></td>
                            </tr>
                            <tr>
                                <td>Active</td><td> No</td><td> No</td>
                                <td colspan="3">No Pop-up and no way to satisfy criteria.
                                    This stops popping-up when the Alert becomes "Due".</td>
                            </tr>
                            <tr>
                                <td></td><td>Yes</td><td>No</td>
                                <td colspan="3"> Since there is no custom input, setting "Completed" to Yes has no effect.</td>
                            </tr>
                            <tr>
                                <td></td><td>No</td><td>Yes</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                </td>
                                <td> Yes
                                </td>
                                <td> Yes
                                </td>
                                <td colspan="3"> A pop-up link will appear, allowing Notes and/or mark it as Completed
                                </td>
                            </tr>
                            <tr>
                                <td>Passive
                                </td>
                                <td> No
                                </td>
                                <td> No
                                </td>
                                <td>
                                </td>
                            </tr>
                            <tr>
                                <td></td><td>Yes</td><td>No</td>
                                <td colspan="3"> Since there is no custom input, setting "Completed" to Yes has no effect.</td>
                            </tr>
                            <tr>
                                <td></td><td>No</td><td>Yes</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                </td>
                                <td> Yes
                                </td>
                                <td> Yes
                                </td>
                                <td colspan="3"> A pop-up link will appear, allowing Notes and/or mark it as Completed
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="help_summary" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title title"><?php echo xlt('Completion Guidelines'); ?>:</h5>
                    <button type="button" class="close" data-dismiss="modal"  aria-label="Close">&times;</button>
                </div>
                <div class="modal-body row text-justify">
                    <div class="col-8 offset-2 text-center alert alert-info">
                        <span class="bold">If this patient has <i>XYZ</i>, an alert will fire, until <i>this</i> happens.</span>
                    </div>
                    <div class="col-12">
                        <p>A Clinical Reminder usually looks for one item and if found, triggers an alert asking you to perform one task.
                            For example, if they are diabetic, they need an eye exam annually.
                            Once you document that it occurred, the alert stops until next year.</p>
                        <p>A CR may also look for one item and if found, trigger an alert until something is documented in the patient's chart.
                            For example, on each visit the blood pressure must be documented.
                            Once the BPs, both systolic and diastolic, are added to the record, the Alert stops until next visit.</p>
                        <p>Expert users will find they can create very complex Clinical Reminders, sorting the target patient group
                            along highly specific criteria.  Even more complexity can be added because a single CR can trigger multiple
                            Alerts, each pointing to a different Treatment Goal.  While this approach is available, consider deploying Care Plan Sets instead.
                            They combine multiple Clinical Reminders into logical groups more effectively.</p>
                    </div>
                    <div class="col-12">
                        <span class="title2"><?php echo xlt('Alert Types'); ?>:</span>
                    </div>
                    <div class="col-12">
                        <ol>
                            <li> <span class="bold">Active alerts</span> generate a popup, but do not appear in the CR widget.</li>
                            <ul>
                                <li>A CR that is only an Active Alert will popup as requested, then stop when it is past due.</li>
                            </ul>
                            <li> <span class="bold">Passive alerts</span> only appear in the CR widget.</li>
                            <ul>
                                <li>The only way to mark a CR as complete is through the CR widget.</li>
                                <li>Ergo, If you want a popup alert that can be marked complete, it needs to be both active and passive.</li>
                                <li>If a simple pop-up is desired, enable the <b>Enable Clinical Passive New Reminder(s) Popup</b> Global.  Doing this for a CR that is both Active and Passive will result in <b>two</b> pop-up alerts back-to-back (not recommended).</b></li>
                            </ul>
                            <li> <span class="bold">Patient Reminders</span> -- If this CR is triggered, a reminder for the patient is queued based on the patient’s HIPAA preferences (found in the Contact tab of the Demographics page)</li>
                            <?php
                                if ($GLOBALS['medex_enable']==1) {?>
                                    <li> <span class="bold">Provider Alerts</span> -- If this CR is triggered, a message will be sent to a provider</li>
                                <?php } ?>
                        </ol>
                    </div>
                    <div class="col-12">
                        <span class="title2"><?php echo xlt('Reference'); ?>:</span>
                    </div>
                    <div class="col-10 offset-1">
                        <div class="indent10">References appears in the Dashboard CR widget as <i class="fa fa-link"></i> and can link to:</div>
                        <ul>
                            <li> a help file for this Clinical Reminder </li>
                            <li> a developer's/support website</li>
                            <li> an official published guideline</li>
                            <li> a mail program</li>
                            <li> anything you can imagine or develop</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
    <div id="help_filters" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo xlt('Who are we Targeting'); ?>?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Rule Targets:
                        <span class="underline">Define the cohort of patients that this CR affects</span></h5>
                    <span class="bold">Target patients by the value of any criterion eg. age, sex, demographics, diagnosis, etc.
                            <br />
                            Criteria adjectives:</span>
                    <ul>
                        <li>may be included: include patients matching this criterion (among others).  </li>
                        <ul>
                            <li>If there is more than one criteria, patients matching any of the criteria are included.</li>
                            <li>Used when there are <span class="bold">multiple optional inclusion</span> criteria to allow the inclusion of multiple sub groups of patients.</li>
                        </ul>
                        <li>must be included: targeted patients must meet this criterion</li>
                        <li>may be excluded: If there is more than one criteria, patients matching any of the criteria are excluded.</li>
                        <ul>
                            <li>If there is more than one exclusion criteria, patients matching any of these exclusion criteria are excluded.</li>
                        </ul></li>
                        <li>must be excluded: If this matches, exclude these patients no matter if any other criteria match</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div id="help_intervals2" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">When will this CR fire?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5>Reminder Intervals</h5>
                    When this Rule is active, how do we tell the provider it is time to think about doing this?

                    Say an A1c needs to be done yearly and it hasn't been done in 11 months.
                    Perhaps one month before it is due we fire this alert.
                    Then 1 month before it's actual due date, we trigger this CR and say "Dude it is time to do this!"


                    Now everyone failed and it is PAST DUE.
                    That could be another type of Reminder.  How long is too long?  When that is we are over-due.

                    But what if it was due and now it is "overdue".  Man you are late to the game!
                    How long are we going to let them slide before it is over due?

                    <b>Type:</b>
                    <ul>
                        <li>Clinical == if a clinical event occurs, then fire this rule</li>
                        <li>Patient == if a patient event matches this rule, fire it.</li>
                    </ul>
                    <b>Time Course/Detail:</b>
                    <ul>
                        <li> Number of months, weeks, days, hours</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div id="help_targets" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body row">
                    <div class="col-3">
                        <span class=" title2"><?php echo xlt('If this is true'); ?>:</span>
                        <br />
                    </div>
                    <div class="col-9">
                        <span class="text"><?php echo xlt('Define the items to trigger an alert'); ?></span>
                        <ul>
                            <li> <?php echo xlt('Social History: choose a "lifestyle" value'); ?> </li>
                            <li> <?php echo xlt('Did a specific Assessment occur?'); ?></li>
                            <li> <?php echo xlt('Is an Education event needed?'); ?></li>
                            <li> <?php echo xlt('Should a specific Examination occur?'); ?></li>
                            <li> <?php echo xlt('Do we need an Intervention?'); ?></li>
                            <li> <?php echo xlt('Was a specific Measurement noted?'); ?></li>
                            <li> <?php echo xlt('Did a Reminder occur?'); ?></li>
                            <li> <?php echo xlt('Did a specific Treatment happen?'); ?></li></select>
                            <li> <?php echo xlt('Custom Input:  link to any field in any table in the database'); ?></li>
                        </ul>
                    </div>
                    <div class="col-3">
                        <span class=" title2"><?php echo xlt('Prompting you to do this'); ?>:</span>
                        <br />
                    </div>
                    <div class="col-9">
                        <span class="text"><?php echo xlt('Define what needs to happen to satisfy this alert'); ?></span>

                        <ul>
                            <li> <?php echo xlt('Social History: enter a "lifestyle" value'); ?> </li>
                            <li> <?php echo xlt('Perform a specific Assessment'); ?></li>
                            <li> <?php echo xlt('Provide an Education event and mark "Completed"'); ?></li>
                            <li> <?php echo xlt('Perform a specific Examination'); ?></li>
                            <li> <?php echo xlt('Provide an Intervention and mark "Completed"'); ?></li>
                            <li> <?php echo xlt('Document a specific Measurement'); ?></li>
                            <li> <?php echo xlt('Update a specific database field'); ?></li>
                            <li> <?php echo xlt('Perform a specific Treatment'); ?></li></select>
                            <li> <?php echo xlt('Custom answer:  advanced users only...'); ?></li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-dismiss="modal"><?php echo xlt('Close'); ?></button>
                </div>
            </div>

        </div>
    </div>
    <div id="help_intervals" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title title"><?php echo xlt('When this Clinical Reminder is triggered'); ?>:</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body row">
                    <div class="col-12">

                        <table class="table-100 tight" cellpadding="2">
                            <thead>
                            <tr>
                                <td class="title3">
                                     <span data-toggle='popover'
                                           data-trigger="hover"
                                           data-placement="auto"
                                           data-container="body"
                                           data-html="true"
                                           title='Alert Intervals'
                                           data-content='There are three timing intervals:
                                           <ol>
                                                <li>CR is first triggered and extends until 2nd interval</li>
                                                <li>Period from interval 2 until interval 3</li>
                                                <li>Interval 3, time since CR began and beyond</li>
                                            </ol>'><?php echo xlt('Intervals'); ?></span>

                                </td>
                                <td class="title3">
                                    <span data-toggle='popover'
                                          data-trigger="hover"
                                          data-placement="auto"
                                          data-container="body"
                                          title='Active Alerts'
                                          data-content='Once triggered, these pop-op alerts begin firing until the first time interval is reached.'><?php echo xlt('Active Alert'); ?></span>
                                </td>
                                <td class="title3">
                                            <span data-toggle='popover'
                                                  data-trigger="hover"
                                                  data-placement="auto"
                                                  title='Passive Alerts'
                                                  data-container="body"

                                                  data-content="Once triggered, this Passive Alert appears in the Dashboard's CR widget as 'Due soon'.
                                                  After the first period of time has passed, the Clinical Reminder is considered 'Due'.
                                                  The second period of time (which should be the same or longer than the first) marks the Alert as Past due.
                                                  ">Passive Alert</span>
                                </td>
                            </tr>
                            </thead>
                            <tbody class="text-center tight">
                            <tr>
                                <td class="text-center"><br />
                                    <span class="title4"><?php echo xlt('CR is triggered'); ?>:<br /><?php echo xlt('Timer begins');?></span>
                                </td>
                                <td>
                                    <?php echo xlt('Begins firing'); ?>
                                </td>
                                <td>
                                    <?php echo xlt('Appears in widget as Due soon'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <i class="fa fa-plus"></i>
                                    <input data-grp-tgt="clinical"
                                           type="text"
                                           id="clinical-pre"
                                           value="<?php echo attr($timings['clinical']['pre']['amount']); ?>">
                                    <?php
                                        echo  generate_select_list(
                                            "clinical",
                                            "rule_reminder_intervals",
                                            $timings['clinical']['pre']['timeUnit']."",
                                            "clinical-pre-timeunit",
                                            '',
                                            'small',
                                            '',
                                            "clinical-pre-timeunit",
                                            array( "data-grp-tgt" => "clinical" ));
                                    ?>
                                </td>
                                <td class="text-center tight" nowrap>
                                    Pop-ups stop
                                </td>
                                <td class="text-center tight" nowrap>
                                    Marked as Due in CR widget
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center tight">
                                    <i class="fa fa-plus"></i>
                                    <input data-grp-tgt="clinical" type="text" id="clinical-post" value="<?php echo attr($timings['clinical']['pre']['amount']); ?>">
                                    <?php
                                        echo  generate_select_list(
                                            "clinical",
                                            "rule_reminder_intervals",
                                            $timings['clinical']['post']['timeUnit']."",
                                            'clinical-post-timeunit',
                                            '',
                                            'small',
                                            "",
                                            "clinical-post-timeunit",
                                            array( "data-grp-tgt" => "clinical" ));
                                    ?>
                                </td>
                                <td class="text-center">
                                    --
                                </td>
                                <td class="text-center">
                                    Marked as <span class="red">Past Due</span> in CR widget
                                </td>
                            </tr>
                            <tr class="title4">
                                <td>&nbsp;</td>
                            
                            </tr>
                            <tr class="">
                                <td></td>
                                <td class="title3">Patient Reminders</td>
                                <td class="title3">Provider Alerts</td>
                            </tr>
                            <tr>
                                <td class="text-center tight" nowrap>
                                    <i class="fa fa-plus"></i>
                                    <input data-grp-tgt="patient" type="text" id="patient-pre" value="<?php echo attr($timings['patient']['pre']['amount']); ?>">
                                    <?php
                                        
                                        /*generate_select_list(
                                            $tag_name,
                                            $list_id,
                                            $currvalue,
                                            $title,
                                            $empty_name = ' ',
                                            $class = '',
                                            $onchange = '',
                                            $tag_id = '',
                                            $custom_attributes = null,
                                            $multiple = false,
                                            $backup_list = ''
                                        */
                                        echo  generate_select_list(
                                            "patient",
                                            "rule_reminder_intervals",
                                            $timings['patient']['pre']['timeUnit']."",
                                            'patient-pre-timeunit',
                                            '',
                                            'small',
                                            "",
                                            "patient-pre-timeunit",
                                            array( "data-grp-tgt" => "patient" ));
                                    ?>
                                </td>
                                <td>Reminder is sent</td>
                                <td>Alert is sent</td>
                            </tr>
                            <tr>
                                <td class="text-center tight" nowrap>
                                    <i class="fa fa-plus"></i>
                                    <input data-grp-tgt="patient" type="text" id="patient-post" value="<?php echo attr($timings['patient']['post']['amount']); ?>">
                                    <?php echo $timings['patient']['post']['timeunit'];
                                        echo  generate_select_list(
                                            "patient",
                                            "rule_reminder_intervals",
                                            $timings['patient']['post']['timeUnit']."",
                                            "patient-post-timeunit",
                                            '',
                                            'small',
                                            '',
                                            "patient-post-timeunit",
                                            array( "data-grp-tgt" => "patient" ));
                                    ?>


                                </td>
                                <td class="text-center tight" nowrap>2nd reminder sent<br /> if still active
                                    <input type="hidden" data-grp-tgt="patient" id="patient-post" value="<?php echo attr($timings['patient']['post']['amount'])?:'1'; ?>">
                                    <input type="hidden" name="patient-post-timeunit" id="patient-post-timeunit"  data-grp-tgt="patient" value="<?php echo attr($timings['patient']['post']['amount'])?:'1'; ?>">
                                </td>
                                <td>--</td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                    <!--
                    <div id="required_msg" class="small">
                        <span class="required">*</span><?php echo xlt('Required fields'); ?>
                    </div>
                    <h5>Reminder Intervals</h5>
                    When this Clinical Reminder is active, how do we tell the provider it is time to think about doing this?

                    Say an A1c needs to be done yearly and it hasn't been done in 11 months.
                    Perhaps one month before it is due we fire this alert.
                    Then 1 month before it's actual due date, we trigger this CR and say "Dude it is time to do this!"


                    Now everyone failed and it is PAST DUE.
                    That could be another type of Reminder.  How long is too long?  When that is we are over-due.

                    But what if it was due and now it is "overdue".  Man you are late to the game!
                    How long are we going to let them slide before it is over due?

                    <b>Type:</b>
                    <ul>
                        <li>Clinical == if a clinical event occurs, then fire this rule</li>
                        <li>Patient == if a patient event matches this rule, fire it.</li>
                    </ul>
                    <b>Time Course/Detail:</b>
                    <ul>
                        <li> Number of months, weeks, days, hours</li>
                    </ul>-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div id="help_complete" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title title"><?php echo xlt('Once this CR becomes Active'); ?>:</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body row">
                    <div class="col-12">

                        <table class="table-100 tight">
                            <thead>
                            <tr>
                                <td class="text-center">
                                    <span class="bold"><?php echo xlt('After this time has passed'); ?>: <i class="fa fa-plus"></i></span>
                                </td>
                                <td class="text-center">
                                    <span class='bold' data-toggle='popover' data-trigger="hover" data-placement="auto" title='Alert Begins' data-content='Given a specific due date, an alert will fire this early before the due date.'>Active Alerts</span>
                                </td>
                                <td class="text-center">
                                            <span class='bold'
                                                  data-toggle='popover'
                                                  data-trigger="hover"
                                                  data-placement="auto"
                                                  title='Alert is Past Due'
                                                  data-content='Each Clinical Reminder has a specific "due date".
                                                  After a certain period of time has passed, the Clinical Reminder is considered late.
                                                  Active alerts stop showing up altogether but Passive alerts (in the CR widget on the demographics page) are labelled "Past Due" after this time period.
                                                  Patient Reminders that are past due can trigger a second follow-up e-mail if desired.
                                                  This setting has no effect on Provider Alerts.'>Passive Alerts</span>
                                </td>
                            </tr>
                            </thead>
                            <tbody class="text-center tight">
                            <tr>
                                <td>
                                    <input data-grp-tgt="clinical"
                                           type="text"
                                           id="clinical-pre"
                                           value="<?php echo attr($timings['clinical']['pre']['amount']); ?>">
                                    <?php
                                        echo  generate_select_list(
                                            "clinical",
                                            "rule_reminder_intervals",
                                            $timings['clinical']['pre']['timeUnit']."",
                                            "clinical-pre-timeunit",
                                            '',
                                            'small',
                                            '',
                                            "clinical-pre-timeunit",
                                            array( "data-grp-tgt" => "clinical" ));
                                    ?>
                                </td>
                                <td class="text-center tight" nowrap>
                                    Pop-ups stop
                                </td>
                                <td class="text-center tight" nowrap>
                                    Marked as Due in CR widget
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center tight">
                                    <input data-grp-tgt="clinical" type="text" id="clinical-post" value="<?php echo attr($timings['clinical']['pre']['amount']); ?>">
                                    <?php
                                        echo  generate_select_list(
                                            "clinical",
                                            "rule_reminder_intervals",
                                            $timings['clinical']['post']['timeUnit']."",
                                            'clinical-post-timeunit',
                                            '',
                                            'small',
                                            "",
                                            "clinical-post-timeunit",
                                            array( "data-grp-tgt" => "clinical" ));
                                    ?>
                                </td>
                                <td class="text-center">
                                
                                </td>
                                <td class="text-center">
                                    Marked as <span class="red">Past Due</span> in CR widget
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td class="bold">Patient Reminders</td>
                                <td class="bold">Provider Alerts</td>
                            </tr>
                            <tr>
                                <td class="text-center tight" nowrap>
                                    <input data-grp-tgt="patient" type="text" id="patient-pre" value="<?php echo attr($timings['patient']['pre']['amount']); ?>">
                                    <?php
                                        
                                        /*generate_select_list(
                                            $tag_name,
                                            $list_id,
                                            $currvalue,
                                            $title,
                                            $empty_name = ' ',
                                            $class = '',
                                            $onchange = '',
                                            $tag_id = '',
                                            $custom_attributes = null,
                                            $multiple = false,
                                            $backup_list = ''
                                        */
                                        echo  generate_select_list(
                                            "patient",
                                            "rule_reminder_intervals",
                                            $timings['patient']['pre']['timeUnit']."",
                                            'patient-pre-timeunit',
                                            '',
                                            'small',
                                            "",
                                            "patient-pre-timeunit",
                                            array( "data-grp-tgt" => "patient" ));
                                    ?>
                                </td>
                                <td>Reminder is sent</td>
                                <td>Alert is sent</td>
                            </tr>
                            <tr>
                                <td class="text-center tight" nowrap>
                                    <input data-grp-tgt="patient" type="text" id="patient-post" value="<?php echo attr($timings['patient']['post']['amount']); ?>">
                                    <?php echo $timings['patient']['post']['timeunit'];
                                        echo  generate_select_list(
                                            "patient",
                                            "rule_reminder_intervals",
                                            $timings['patient']['post']['timeUnit']."",
                                            "patient-post-timeunit",
                                            '',
                                            'small',
                                            '',
                                            "patient-post-timeunit",
                                            array( "data-grp-tgt" => "patient" ));
                                    ?>


                                </td>
                                <td class="text-center tight" nowrap>2nd reminder sent<br /> if still active
                                    <input type="hidden" data-grp-tgt="patient" id="patient-post" value="<?php echo attr($timings['patient']['post']['amount'])?:'1'; ?>">
                                    <input type="hidden" name="patient-post-timeunit" id="patient-post-timeunit"  data-grp-tgt="patient" value="day">
                                </td>
                            </tr>
                            </tbody>
                        </table>

                    </div>
                    <!--
                    <div id="required_msg" class="small">
                        <span class="required">*</span><?php echo xlt('Required fields'); ?>
                    </div>
                    <h5>Reminder Intervals</h5>
                    When this Clinical Reminder is active, how do we tell the provider it is time to think about doing this?

                    Say an A1c needs to be done yearly and it hasn't been done in 11 months.
                    Perhaps one month before it is due we fire this alert.
                    Then 1 month before it's actual due date, we trigger this CR and say "Dude it is time to do this!"


                    Now everyone failed and it is PAST DUE.
                    That could be another type of Reminder.  How long is too long?  When that is we are over-due.

                    But what if it was due and now it is "overdue".  Man you are late to the game!
                    How long are we going to let them slide before it is over due?

                    <b>Type:</b>
                    <ul>
                        <li>Clinical == if a clinical event occurs, then fire this rule</li>
                        <li>Patient == if a patient event matches this rule, fire it.</li>
                    </ul>
                    <b>Time Course/Detail:</b>
                    <ul>
                        <li> Number of months, weeks, days, hours</li>
                    </ul>-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    $("#show_summary_edit").hide();
    $("#show_filters_edit").hide();
    $("[id^='help_']").hide();
    $("#show_group_<?php echo attr_js($nextGroupId); ?>").hide();
    $("[id^='show_targets_edit_").hide();
    
    $(function() {
        $("#edit_summary").click(function () {
            $("#show_summary").hide();
            $("#show_summary_edit").show();
        });
        $("#save_summary").click(function () {
            top.restoreSession();
            location.href = 'index.php?action=detail!view&id=' + $("#ruleId").val();
        });
        
        $("[name^='summary_'],[name^='fld_ruleTypes'],[name^='intervals_']").change(function () {
            top.restoreSession();
            var url = "index.php?action=edit!submit_summary&id=" + $("#ruleId").val();
            var newTypes = [];
            $('input[name="fld_ruleTypes[]"]:checked').each(function () {
                newTypes.push($(this).val());
            });
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {
                           'fld_title'              : $("#fld_title").val(),
                           'fld_ruleTypes'          : newTypes,
                           'fld_developer'          : $("#fld_developer").val(),
                           'fld_funding_source'     : $("#fld_funding_source").val(),
                           'fld_release'            : $("#fld_release").val(),
                           'fld_web_reference'      : $("#fld_web_reference").val(),
                           'fld_public_description' : $("#fld_public_description").val(),
                           'clinical_pre'           : $("#clinical-pre").val(),
                           'clinical_post'          : $("#clinical-post").val(),
                           'show': '1'
                       }
                   }).done(function (data) {
                $("#show_summary_1").html(data);
                $("#show_summary_report").html('Summary updated successfully');
                setTimeout(function () {
                    $("#show_summary_report").html('&nbsp;');
                }, 2000);
            });
            
        });
        $("[id^='clinical-p'],[id^='patient-p'],[id^='provider-p']").change(function () {
            top.restoreSession();
            var url = "index.php?action=edit!submit_intervals&id=" + $("#ruleId").val();
            var newTypes = [];
            $('input[name="fld_ruleTypes[]"]:checked').each(function () {
                newTypes.push($(this).val());
            });
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {
                           'clinical-pre'           : $("#clinical-pre").val(),
                           'clinical-post'          : $("#clinical-post").val(),
                           'patient-pre'            : $("#patient-pre").val(),
                           'patient-post'           : $("#patient-post").val(),
                           'provider-pre'           : $("#provider-pre").val(),
                           'provider-post'          : $("#provider-post").val(),
                           'clinical-pre-timeunit'  : $("#clinical-pre-timeunit").val(),
                           'clinical-post-timeunit' : $("#clinical-post-timeunit").val(),
                           'patient-pre-timeunit'   : $("#patient-pre-timeunit").val(),
                           'patient-post-timeunit'  : $("#patient-post-timeunit").val(),
                           'provider-pre-timeunit'  : $("#provider-pre-timeunit").val(),
                           'provider-post-timeunit' : $("#provider-post-timeunit").val(),
                           'show': '1'
                       }
                   }).done(function (data) {
                $("#show_summary_1").html(data);
                $("#show_summary_report").html('Summary updated successfully');
                setTimeout(function () {
                    $("#show_summary_report").html('&nbsp;');
                }, 2000);
            });
            
        });
        <?php
        if (empty($timings['clinical']['pre']['amount'])) { ?>
        $("#clinical-pre").val('1');
        $("#clinical-post").val('1');
        $("#patient-pre").val('1');
        $("#patient-pre").val('1');
        $("#clinical-pre").trigger('change');
        <?php } ?>
    
        $("#add_filters").click(function () {
            top.restoreSession();
            var url = "index.php?action=edit!add_criteria&id=<?php echo attr_url($rule->id); ?>&criteriaType=filter";
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {}
                   }).done(function (data) {
                $("#show_filters_edit").html(data);
                $("#show_filters").hide();
                $("#show_filters_edit").show();
            });
        });
        
        $("[id^='edit_filter_']").click(function() {
            top.restoreSession();
            var rf_uid = this.id.match(/edit_filter_(.*)/)[1];
            var url = "index.php?action=edit!filter&id=<?php echo attr_url($rule->id); ?>&rf_uid="+rf_uid;
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: { type : 'filter' }
                   }).done(function (data) {
                $("#show_filters_edit").html(data);
                $("#show_filters").hide();
                $("#show_filters_edit").show();
            });
        });
        
        $("[id^='edit_target_']").click(function() {
            top.restoreSession();
            var group = this.id.match(/edit_target_(.*)_(.*)/)[1];
            var rt_uid = this.id.match(/edit_target_(.*)_(.*)/)[2];
            var url = "index.php?action=edit!target&id=<?php echo attr_url($rule->id); ?>&rt_uid="+rt_uid;
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {
                           type : 'target'
                       }
                   }).done(function (data) {
                $("#show_targets_edit_"+group).html(data);
                $("#show_targets_"+group).hide();
                $("#show_targets_edit_"+group).show();
            });
        });
        
        onclick="top.restoreSession();location.href='index.php?action=edit!target&id=<?php echo attr_url($rule->id); ?>&group_id=<?php echo attr_url($group->groupId); ?>&rt_uid=<?php echo attr_url($criteria->uid); ?>';"
        
        $("[id^='edit_action_'").click(function() {
            top.restoreSession();
            var action = this.id.match(/edit_action_(.*)_(.*)/)[2];
            var group = this.id.match(/edit_action_(.*)_(.*)/)[1];
            var url = 'index.php?action=edit!action&id=<?php echo attr_url($rule->id); ?>group_id='+group+'&ra_uid='+action;
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {}
                   }).done(function (data) {
                $("#show_actions_edit_"+group).html(data);
                $("#show_actions_"+group).hide();
                $("#show_actions_edit_"+group).show();
                //show_targets_edit_1
            });
            
        });
        
        $("[id^='add_criteria_target_").click(function() {
            top.restoreSession();
            var group = this.id.match(/add_criteria_target_(.*)/)[1];
            var url = 'index.php?action=edit!add_criteria&id=<?php echo attr_url($rule->id); ?>&group_id='+group+'&criteriaType=target';
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {}
                   }).done(function (data) {
                $("#show_targets_edit_"+group).html(data);
                $("#show_targets_"+group).hide();
                $("#show_targets_edit_"+group).show();
                // $("#show_group").html(data);
                //$("#show_targets_"+group).hide();
                //$("#show_targets_edit_"+group).show();
            });
        });
        $("[id^='add_action_']").click(function() {
            top.restoreSession();
            var group = this.id.match(/add_action_(.*)/)[1];
            var url = 'index.php?action=edit!add_action&id=<?php echo attr_url($rule->id); ?>&group_id='+group;
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {}
                   }).done(function (data) {
                $("#show_actions_edit_"+group).html(data);
                $("#show_actions_"+group).hide();
                //$("#show_targets_edit_"+group).show();
                // $("#show_group").html(data);
                //$("#show_targets_"+group).hide();
                //$("#show_targets_edit_"+group).show();
            });
        });
        $('#help_intervals').on('show.bs.modal', function () {
            $('#help_intervals').focus();
        });
        
        $('#help_summary').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var recipient = button.data('whatever') // Extract info from data-* attributes
            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this);
            
            //modal.find('.modal-title').text('New message to ' + recipient)
            //modal.find('.modal-body input').val(recipient)
            //$(".modal-backdrop").remove();
            
        });
        $("[id^='help_']").click(function () {
            
            $(".modal-dialog").draggable({
                                             handle: ".modal-header",
                                             cursor: 'move',
                                             revert: false,
                                             backdrop: false
                                         });
            
            $(this).css({
                            top: 0,
                            left: 0
                        });
            //$(".in").remove();
            
            
        });
        $("#timing_toggle").click(function() {
            $("#intervals_edit").toggle();
        });
        $("#new_group_<?php echo (int)($nextGroupId);?>").click(function () {
            $("#show_group_<?php echo (int)($nextGroupId);?>").show();
        });
    });

</script>
