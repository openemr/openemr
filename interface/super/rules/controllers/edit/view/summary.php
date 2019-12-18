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

<div class="title" style="display:none"><a href="https://www.oculoplasticsllc.com/openemr/interface/super/rules/index.php?action=detail!view&id=<?php echo attr($rule->id); ?>"><?php
            // this will display the TAB title
            echo xlt('CR{{Clinical Reminder abbreviation}} Builder'); ?><?php
            $in = xlt($rule->title);
            echo strlen($in) > 10 ? substr($in,0,10)."..." : $in;
        ?></a>
</div>

<form action="index.php?action=edit!createCR" method="post" onsubmit="return top.restoreSession()">
    <div class="container">
        <div class="row" id="show_summary_edit" >

            <div class="col-6 offset-3 text-left section2">
                <span class="title "><?php echo xlt('New Clinical Reminder'); ?> </span>

                <button class="btn btn-sm btn-primary icon_2"
                        id="save_summary"

                        title="<?php echo xla('Create this Clinical Reminer'); ?>"><i class="fa fa-save"> <?php echo xlt('Create'); ?></i>
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
                                    data-content='References appear in the Dashboard CR widget as <i class="fa fa-link"></i> and can link to anything desired.
                                                    <img width="250px" src="<?php echo $GLOBAL['webroot'];?>/public/images/CR_widget.png">'
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
                                      data-content='The text here will be displayed in the patients Dashboard CR widget via a tooltip.  Use it to describe to your staff what this CR means.'>
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
        <div id="help_summary" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title title"><?php echo xlt('Creation Guidelines'); ?>:</h5>
                        <button type="button" class="close" data-dismiss="modal"  aria-label="Close">&times;</button>
                    </div>
                    <div class="modal-body row text-justify">
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
            
                            <p>So now you have your basic Clinical Reminder started and its Alerts outlined. Let's move on to the two
                                important Steps needed to deploy your CR.</p>
                            <p>In building a CR, you can deploy filters to limit the patients this may apply to
                            (eg. just men for Prostate screening).  In Step 2, you will define what you are actually looking for
                            to trigger the Alerts associated with this CR.  Using the Prostate example, you may want to see that a
                            Prostate screening exam has been performed?  If you have a Form that you use clinically to note this,
                            you can dive into the Database to retrieve this value.  Don't worry if you can't check a database value for your answer
                            because this Alert Manager can just pop-up a "Reminder" for you to check "<b>Yes, Completed</b>",
                            and perhaps add a note if you desire.  </p>
                            
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
</form>
