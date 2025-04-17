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
 * @license   There are segments of code in this file that have been generated via ChatGPT and are licensed as Public Domain, they are marked with a header and footer.
 */

use OpenEMR\ClinicalDecisionRules\Interface\Common;
use OpenEMR\ClinicalDecisionRules\Interface\Controller\ControllerReview;
use OpenEMR\Common\Csrf\CsrfUtils;

$rule = $viewBean->rule ?>
<div class="card">
    <!--         -->
    <!-- summary -->
    <!--         -->
    <div class="card-header">
        <p><b><?php echo xlt($rule->title); ?></b>
            (<?php echo Common::implode_funcs(", ", $rule->getRuleTypeLabels(), array('xlt')); ?>)
            <?php if ($viewBean->canEdit) : ?>
                <input type="button" class="btn btn-sm btn-primary btn-edit-cdr-source"
                       data-rule-id="<?php echo attr($rule->id); ?>"
                       value="<?php echo xlt("Edit Rule Source Attributes"); ?>"/>
            <?php endif; ?>
        </p>
    </div>
    <div class="card-body">
        <?php if ($viewBean->message == ControllerReview::ERROR_MESSAGE_SUCCESS) : ?>
            <div class="alert alert-success">
                <?php echo xlt('Feedback submitted successfully.'); ?>
            </div>
        <?php elseif ($viewBean->message == ControllerReview::ERROR_MESSAGE_FAILED) : ?>
            <div class="alert alert-danger">
                <?php echo xlt('Feedback submission failed.'); ?>
            </div>
        <?php elseif ($viewBean->message == ControllerReview::ERROR_MESSAGE_INVALID) : ?>
        <div class="alert alert-danger">
            <?php echo xlt('Feedback submission was invalid as feedback message was too long.'); ?>
        </div>
        <?php endif; ?>
        <p><b><?php echo xlt('Bibliographic Citation'); ?>:</b>&nbsp;<?php echo text($rule->bibliographic_citation); ?></p>
        <p><b><?php echo xlt('Developer'); ?>:</b>&nbsp;<?php echo text($rule->developer); ?></p>
        <p><b><?php echo xlt('Funding Source'); ?>:</b>&nbsp;<?php echo text($rule->funding_source); ?></p>
        <p><b><?php echo xlt('Release'); ?>:</b> <?php echo text($rule->release); ?></p>
        <p><b><?php echo xlt('Web Reference'); ?>:</b>&nbsp;
            <?php if ($rule->web_reference == xl("Unknown")) : ?>
                <?php echo xlt("Unknown"); ?>
            <?php else : ?>
                <a href='<?php echo attr($rule->web_reference) ?>' class="btn btn-link" rel='noopener' target='_blank'><?php echo text($rule->web_reference); ?></a>
            <?php endif; ?>
        </p>
        <p><b><?php echo xlt('Referential CDS (codetype:code)'); ?>:</b>&nbsp;<?php echo text($rule->linked_referential_cds); ?></p>
        <?php
        /** Note the following code is in the Public Domain and was generated using ChatGPT */
        ?>
        <p><b><?php echo xlt('Rule usage of Patient\'s Race'); ?>:</b>&nbsp;<?php echo text($rule->patient_race_usage); ?></p>
        <p><b><?php echo xlt('Rule usage of Patient\'s Ethnicity'); ?>:</b>&nbsp;<?php echo text($rule->patient_ethnicity_usage); ?></p>
        <p><b><?php echo xlt('Rule usage of Patient\'s Language'); ?>:</b>&nbsp;<?php echo text($rule->patient_language_usage); ?></p>
        <p><b><?php echo xlt('Rule usage of Patient\'s Sexual Orientation'); ?>:</b>&nbsp;<?php echo text($rule->patient_sexual_orientation_usage); ?></p>
        <p><b><?php echo xlt('Rule usage of Patient\'s Gender Identity'); ?>:</b>&nbsp;<?php echo text($rule->patient_gender_identity_usage); ?></p>
        <p><b><?php echo xlt('Rule usage of Patient\'s Sex'); ?>:</b>&nbsp;<?php echo text($rule->patient_sex_usage); ?></p>
        <p><b><?php echo xlt('Rule usage of Patient\'s Date of Birth'); ?>:</b>&nbsp;<?php echo text($rule->patient_dob_usage); ?></p>
        <p><b><?php echo xlt('Rule usage of Patient\'s Social Determinants of Health'); ?>:</b>&nbsp;<?php echo text($rule->patient_sodh_usage); ?></p>
        <p><b><?php echo xlt('Rule usage of Patient\'s Health Status Assessments'); ?>:</b>&nbsp;<?php echo text($rule->patient_health_status_usage); ?></p>
        <?php
        /** End ChatGPT Public Domain Code */
        ?>
    </div>
</div>
<div class="card mt-2">
    <div class="card-header">
        <p><b><?php echo xlt('Rule Feedback'); ?></b></p>
    </div>
    <div class="card-body">
        <form method="POST" action="index.php?action=review!submit_feedback">
            <input type="hidden" name="rule_id" value="<?php echo attr($rule->id); ?>"/>
            <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="row">
                <div class="col">
                    <textarea class="form-control" name="feedback" rows="5" cols="50" maxlength="2048"></textarea>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col">
                    <button type="submit" class="btn btn-primary"><?php echo xlt('Submit Feedback'); ?></button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col">
                    <p class="alert alert-info">
                        <?php echo xlt("Feedback may be reviewed by the rule's developer and may be included in future releases."); ?>
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    (function() {
        window.document.addEventListener("DOMContentLoaded", function() {
            var editBtns = document.querySelectorAll('.btn-edit-cdr-source');
            editBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var ruleId = btn.dataset['ruleId'];
                    if (window.parent) {
                        window.parent.postMessage({type: 'cdr-edit-source', ruleId: ruleId}, window.location.origin);
                    } else {
                        console.error("Failed to send message to parent window");
                        alert(window.top.xl("A system error occurred"));
                    }
                });
            });
        });
    })(window);
</script>
