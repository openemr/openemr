<?php
    /**
     * interface/super/rules/controllers/edit/view/summary.php
     *
     * @package   OpenEMR
     * @link      https://www.open-emr.org
     * @author    Aron Racho <aron@mi-squared.com>
     * @author    Brady Miller <brady.g.miller@gmail.com>
     * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
     * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
     * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
     */
    
    $rule = $viewBean->rule ?>

<div class="title" style="display:none;">
    <?php
        // this will display the TAB title
        echo xlt('CR{{Clinical Reminder abbreviation}} Builder'); ?><?php
        $in = xlt($rule->title);
        echo strlen($in) > 10 ? substr($in,0,10)."..." : $in; ?>
</div>

<form action="index.php?action=edit!createCR" method="post" onsubmit="return top.restoreSession()">
    <div class="container-fluid">
        <div class="row" id="show_summary_edit" >
            <div class="col-6 offset-3 text-left">
                <div class="header">
                <span class="title"><?php echo xlt('New Clinical Reminder'); ?> </span>
                <button class="btn btn-sm btn-primary icon_2"
                        id="save_summary"

                        title="<?php echo xla('Create this Clinical Reminder'); ?>"> <i class="fa fa-save heavy"> <?php echo xlt('Create'); ?></i>
                </button>
                <button class="btn-sm btn-primary icon_1"
                        type="button"
                        data-toggle="modal" data-backdrop="false" data-target="#help_summary" id="show_summary_help" title="Open the Help:: Summary Modal"><i class="fa fa-question"></i>
                </button>
                <table class="table table-sm table-condensed text-left">
                    <tr>
                        <td class="text-right align-baseline">
                            *<span class="underline"><?php echo xlt('Name'); ?>:</span>
                        </td>
                        <td>
                            <input type="text" name="fld_title" class="field" id="fld_title" value="<?php echo attr($rule->title); ?>">
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
                            <input type="text" name="fld_developer" class="field" id="fld_developer" value="<?php echo attr($rule->developer); ?>" maxlength="255">
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            <span class="underline align-middle"><?php echo xlt('Funding Source'); ?>:</span>
                        </td>
                        <td class="text-left">
                            <input type="text" name="fld_funding_source" class="form-control" id="fld_funding_source" value="<?php echo attr($rule->funding_source); ?>" maxlength="255">
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                            <span class="underline align-middle"><?php echo xlt('Release'); ?>:</span>
                        </td>
                        <td class="text-left">
                            <input type="text" name="fld_release" class="field" id="fld_release" value="<?php echo attr($rule->release); ?>" maxlength="255">
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right"><span
                                    data-toggle='popover'
                                    title='Reference'
                                    data-html="true"
                                    data-trigger='hover'
                                    data-placement='auto'
                                    data-content='References appear in the Dashboard::CR widget (only Passive Alerts) as <i class="fa fa-link"></i> and can link to anything desired.
                                                    <img width="250px" src="<?php echo $GLOBAL['webroot'];?>/interface/super/rules/www/CR_widget.png">'
                                    class="underline"><?php echo xlt('Reference'); ?><i class="fa fa-link"></i>:
                        </td>
                        <td class="text-left">
                            <input type="text" name="fld_web_reference" class="field" id="fld_web_reference" value="<?php echo attr($rule->web_ref); ?>" maxlength="255">
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right">
                                <span data-toggle='popover'
                                      title='Public Description'
                                      data-html="true"
                                      data-trigger='hover'
                                      data-placement='auto'
                                      data-content='The text here will be displayed in the Dashboard::CR widget (only Passive Alerts) via a tooltip.  Use it to describe to your staff what this CR means.'>
                                    <span class="underline"><?php echo xlt('Description'); ?></span>:
                                </span>
                        </td>
                        <td>
                                <textarea class="form-control"
                                          id="fld_public_description"
                                          name="fld_public_description"><?php echo attr($rule->public_description); ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>

        </div>
        </div>
        <div id="help_summary" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title title"><?php echo xlt('Creation Guidelines'); ?>:</h5>
                        <button type="button" class="close" data-dismiss="modal"  aria-label="Close">&times;</button>
                    </div>
                    <div class="container">
                    <div class="modal-body row text-justify">
                        <div class="col-12">
                            <span class="title2"><?php echo xlt('Alert Types'); ?>:</span>
                        </div>
                        <div class="col-12">
                            <p>Clinical Reminders in general help us improve patient care by reminding the care team and/or the patient
                            that a Treatment Goal needs to be reached.  Maybe it is a screening exam, maybe it is a Flu Shot.
                            The possibilities are endless.  Overall, a Clinical Reminder targets a group of patients, looks for an item
                            and if the conditions are right, an alert is triggered. An alert's job is to inform the The basic alert types are:
                            </p>
                            <ol>
                                <li> Alert the current user when viewing the patient Dashboard</li>
                                <li> Alert the patient (e-mail, SMS, etc.)</li>
                                <?php if ($GLOBALS['medex_enable'] =='1') { ?>
                                <li> Alert a 3rd party (e-mail, SMS, etc.)</li>
                                <?php  } ?>
                            </ol>
                            <h6 class="underline">Dashboard Alerts</h6>
                            <ul>
                                <li> Clinical Reminders that only carry an <span class="bold">Active alert</span> will generate a pop-up until satisfied or until they expire (past due).  You cannot manually shut them off.</li>
                                <li> Clinical Reminders that only carry a <span class="bold">Passive alert</span> only appear in the Dashboard::CR widget.</li>
                                <ul>
                                    <li>You can manually shut them off via a "Yes, I completed this" pop-up - you will add this option when building the CR on the next page.</li>
                                    <li>OpenEMR can shut-off the alert automatically when a value in the patient's chart changes.</li>
                                </ul>
                                <li> Clinical Reminders that carry both an <span class="bold">Active alert</span> and a <span class="bold">Passive alert</span>
                                will show up in the CR widget, pop-up an Alert and can be shut-off automatically or manually if you desire.</li>
                                <li> Clinical Reminders usually have one Treatment Goal attached, but they are not limited to one.  Each Goal will result in a separate alert...  Be aware of Alert Fatigue!</li>
                            </ul>
                            <h6 class="underline">Patient Reminders</h6>
                            <ul>
                                <li> If a CR containing a <span class="bold">Patient Reminder</span> is triggered,
                                    a reminder message for the patient is queued, if allowed by patient’s HIPAA preferences (found in the Contact tab of the Demographics page).
                                    <?php if ($GLOBALS['medex_enable']==1) {?>
                                    You can use the internal messaging functions in OpenEMR or enable this CR on MedEx where you will build the
                                    desired message templates.
                                    E-mail, SMS, and voice (text-to-speech or pre-recorded audio) templates are available.
                                    <?php } ?>
                                </li>
                            </ul>
                            <?php
                                if ($GLOBALS['medex_enable']==1) {?>
                            <h6 class="underline">Provider Alerts</h6>
                            <ul>
                                <li> If a CR containing a <span class="bold">Provider Alert</span> is triggered,
                                    a message will be sent to a provider.  These are customizable on the MedEx website
                                    and include e-mail, SMS, or voice messages (text-to-speech or pre-recorded audio messages).
                                </li>
                            </ul>
                                <?php } ?>
                            
                            
                        </div>
                        <div class="col-12">
                            <span class="title2"><?php echo xlt('Reference'); ?>:</span>
                        </div>
                        <div class="col-8 offset-1">
                            <div class="indent10">References appear in the Dashboard CR widget as <i class="fa fa-link"></i> and can link to:</div>
                            <ul>
                                <li> a help file for this Clinical Reminder </li>
                                <li> a developer's/support website</li>
                                <li> an official published guideline</li>
                                <li> a mail program</li>
                                <li> anything you can imagine or develop</li>
                            </ul>
                        </div>
                        <div class="col-3"><img width="250px" src="<?php echo $GLOBAL['webroot'];?>/interface/super/rules/www/CR_widget.png"></div>
                        <div class="col-12">
                            <div class="col-8 offset-2 text-center alert alert-info">
                                <span class="bold">If this patient has <i>XYZ</i>, an alert will fire, until <i>this</i> happens.</span>
                            </div>

                            <span class="">As you build a new Clinical Reminder, you will specify criteria to determine:
                            <ul>
                                <li> Who this CR will affect </li>
                                <li> When it will fire</li>
                                <li> How it will fire</li>
                                <li> when and how it stops firing</li>
                            </ul>
            
                            <p>So now you have your basic Clinical Reminder started and its Alerts outlined. Once you save this new Clinical Reminder, we will move on to complete two
                                more Steps needed to deploy your CR.</p>
                            <p>In the first step of building a CR, you can fine tune who the alert applies to.
                                For example, in building a Prostate screening CR you might limit the target group to men over 50 yerars of age.
                                In Step 2, you will define what you are actually looking for that will trigger the Alert(s) associated with this CR.
                                Using the Prostate example, you may want to see that a Prostate screening exam has been performed?
                                If you have a Form that you use clinically to note this, you can dive into the Database to retrieve this value.
                                Don't worry if you can't check a database value for your answer because CRs
                                can also pop-up a "<b>Yes, Completed</b>" window to stop the alert, and add a note if you desire.
                            </p>
                            
                            <div class="col-8 offset-2 text-center alert alert-warning">
                                <span class="bold">So much fun in one place!</span>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>

                    </div>
                    </div>
                </div>
            </div>
</form>
