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
 * @license   There are segments of code in this file that have been generated via ChatGPT and are licensed as Public Domain, they are marked with a header and footer.
 */

use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\RuleType;
use OpenEMR\ClinicalDecisionRules\Interface\Common;

$rule = $viewBean->rule ?>

<script src="<?php Common::js_src('edit.js') ?>"></script>
<script>
    var edit = new rule_edit( {});
    edit.init();

    //This invokes the find-code popup.
    function sel_referential_cds() {
        top.restoreSession();
        dlgopen('../../patient_file/encounter/find_code_popup.php', '_blank', 500, 400);
    }
    // This is for callback by the find-code popup.
    // Only allows one entry.
    function set_related(codetype, code, selector, codedesc) {
        var f = document.forms[0];
        var s = '';
        if (code) {
            s = codetype + ':' + code;
        }
        f.fld_linked_referential_cds.value = s;
    }
</script>

<table class="table header">
  <tr>
        <td class="title"><?php echo $rule->id ? xlt('Rule Edit') : xlt('Rule Add'); ?></td>
        <td>
            <a href="index.php?action=detail!view&id=<?php echo attr_url($rule->id); ?>" class="iframe_medium btn btn-secondary" onclick="top.restoreSession()">
                <span><?php echo xlt('Cancel'); ?></span>
            </a>
            <a href="javascript:;" class="iframe_medium btn btn-primary" id="btn_save" onclick="top.restoreSession()"><span><?php echo xlt('Save'); ?></span></a>
        </td>
  </tr>
</table>

<div class="rule_detail edit summry text">
    <p class="header"><?php echo xlt('Summary'); ?> </p>

    <form action="index.php?action=edit!submit_summary" method="post" id="frm_submit" onsubmit="return top.restoreSession()">
    <input type="hidden" name="id" value="<?php echo attr($rule->id); ?>"/>

    <div class="form-row">
    <span class="left_col colhead req" data-fld="fld_title"><?php echo xlt('Title'); ?></span>
    <span class="end_col"><input type="text" name="fld_title" class="form-control field" id="fld_title" value="<?php echo attr($rule->title); ?>"></span>
    </div>

    <div class="form-row">
    <span class="left_col colhead" data-fld="fld_ruleTypes[]"><?php echo xlt('Type'); ?></span>
    <span class="end_col">
        <?php foreach (RuleType::values() as $type) {?>
        <input name="fld_ruleTypes[]" value="<?php echo attr($type); ?>" type="checkbox" <?php echo $rule->hasRuleType(RuleType::from($type)) ? "CHECKED" : "" ?>>
            <?php echo text(RuleType::from($type)->lbl); ?>
        <?php } ?>
    </span>
    </div>

    <div class="form-row">
    <span class="left_col colhead" data-fld="fld_bibliographic_citation"><?php echo xlt('Bibliographic Citation'); ?></span>
    <span class="end_col"><input type="text" name="fld_bibliographic_citation" class="form-control field" id="fld_bibliographic_citation" value="<?php echo attr($rule->bibliographic_citation); ?>" maxlength="255" /></span>
    </div>

    <div class="form-row">
    <span class="left_col colhead" data-fld="fld_developer"><?php echo xlt('Developer'); ?></span>
    <span class="end_col"><input type="text" name="fld_developer" class="form-control field" id="fld_developer" value="<?php echo attr($rule->developer); ?>" maxlength="255" /></span>
    </div>

    <div class="form-row">
    <span class="left_col colhead" data-fld="fld_funding_source"><?php echo xlt('Funding Source'); ?></span>
    <span class="end_col"><input type="text" name="fld_funding_source" class="form-control field" id="fld_funding_source" value="<?php echo attr($rule->funding_source); ?>" maxlength="255" /></span>
    </div>

    <div class="form-row">
    <span class="left_col colhead" data-fld="fld_release"><?php echo xlt('Date of Last Review or Update'); ?></span>
    <span class="end_col"><input type="text" name="fld_release" class="form-control field" id="fld_release" value="<?php echo attr($rule->release); ?>" maxlength="255" /></span>
    </div>

    <div class="form-row">
    <span class="left_col colhead" data-fld="fld_web_reference"><?php echo xlt('Web Reference'); ?></span>
    <span class="end_col"><input type="text" name="fld_web_reference" class="form-control field" id="fld_web_reference" value="<?php echo attr($rule->web_reference); ?>" maxlength="255" /></span>
    </div>

    <div class="form-row">
    <span class="left_col colhead" data-fld="fld_linked_referential_cds"><?php echo xlt('Referential CDS'); ?></span>
    <span class="end_col"><input type="text" name="fld_linked_referential_cds" class="form-control field" id="fld_linked_referential_cds" onclick="sel_referential_cds()" value="<?php echo attr($rule->linked_referential_cds); ?>" maxlength="50" /></span>
    </div>
    <?php
    /** Note the following code is in the Public Domain and was generated using ChatGPT */
    ?>
    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_race_usage"><?php echo xlt('Rule usage of Patient\'s Race'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_race_usage" class="form-control field" id="fld_patient_race_usage" value="<?php echo attr($rule->patient_race_usage); ?>" maxlength="2048"></span>
    </div>

    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_ethnicity_usage"><?php echo xlt('Rule usage of Patient\'s Ethnicity'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_ethnicity_usage" class="form-control field" id="fld_patient_ethnicity_usage" value="<?php echo attr($rule->patient_ethnicity_usage); ?>" maxlength="2048"></span>
    </div>

    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_language_usage"><?php echo xlt('Rule usage of Patient\'s Language'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_language_usage" class="form-control field" id="fld_patient_language_usage" value="<?php echo attr($rule->patient_language_usage); ?>" maxlength="2048"></span>
    </div>

    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_sexual_orientation_usage"><?php echo xlt('Rule usage of Patient\'s Sexual Orientation'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_sexual_orientation_usage" class="form-control field" id="fld_patient_sexual_orientation_usage" value="<?php echo attr($rule->patient_sexual_orientation_usage); ?>" maxlength="2048"></span>
    </div>
    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_gender_identity_usage"><?php echo xlt('Rule usage of Patient\'s Gender Identity'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_gender_identity_usage" class="form-control field" id="fld_patient_gender_identity_usage" value="<?php echo attr($rule->patient_gender_identity_usage); ?>" maxlength="2048"></span>
    </div>

    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_sex_usage"><?php echo xlt('Rule usage of Patient\'s Sex'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_sex_usage" class="form-control field" id="fld_patient_sex_usage" value="<?php echo attr($rule->patient_sex_usage); ?>" maxlength="2048"></span>
    </div>

    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_dob_usage"><?php echo xlt('Rule usage of Patient\'s Date of Birth'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_dob_usage" class="form-control field" id="fld_patient_dob_usage" value="<?php echo attr($rule->patient_dob_usage); ?>" maxlength="2048"></span>
    </div>

    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_sodh_usage"><?php echo xlt('Rule usage of Patient\'s Social Determinants of Health'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_sodh_usage" class="form-control field" id="fld_patient_sodh_usage" value="<?php echo attr($rule->patient_sodh_usage); ?>" maxlength="2048"></span>
    </div>

    <div class="form-row">
        <span class="left_col colhead" data-fld="fld_patient_health_status_usage"><?php echo xlt('Rule usage of Patient\'s Health Status Assessments'); ?></span>
        <span class="end_col"><input type="text" name="fld_patient_health_status_usage" class="form-control field" id="fld_patient_health_status_usage" value="<?php echo attr($rule->patient_health_status_usage); ?>" maxlength="2048"></span>
    </div>
    <?php
    /** End ChatGPT Public Domain Code */
    ?>
    </form>

</div>

<div id="required_msg" class="small">
    <span class="required">*</span><?php echo xlt('Required fields'); ?>
</div>
