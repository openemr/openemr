<?php

/**
 * add_edit_issue_medication_fragment.php  Represents the medication fields used for the medication type issue list
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * @global $irow The issues list record row we are working with
 */
if (empty($irow)) {
    return;
}
$medication = $irow['medication'] ?? [];
$usage_category = $medication['usage_category'] ?? null;
$request_intent = $medication['request_intent'] ?? null;
?>
<div class="form-group col-12">
    <label class="col-form-label" for="medication[usage_category]"><?php echo xlt('Medication Usage'); ?>:</label>
    <?php
    generate_form_field(array('data_type' => 1, 'field_id' => 'medication[usage_category]', 'list_id' => 'medication-usage-category', 'empty_title' => 'SKIP'), $usage_category);
    ?>
</div>
<div class="form-group col-12">
    <label class="col-form-label" for="medication[request_intent]"><?php echo xlt('Medication Request Intent'); ?>:</label>
    <?php
    generate_form_field(array('data_type' => 1, 'field_id' => 'medication[request_intent]', 'list_id' => 'medication-request-intent'), $request_intent);
    ?>
</div>
<div class="form-group col-12">
    <label class="col-form-label" for="form_medication[drug_dosage_instructions]"><?php echo xlt('Medication Dosage Instructions'); ?>:</label>
    <textarea class="form-control" name='form_medication[drug_dosage_instructions]' id='form_medication[drug_dosage_instructions]'
              rows="4"><?php echo text($medication['drug_dosage_instructions'] ?? '') ?></textarea>
</div>