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
$isPrimaryRecord = $medication['is_primary_record'] ?? '1';
?>
<div class="form-group col-sm-12 col-md-6">
    <label class="col-form-label" for="medication[usage_category]"><?php echo xlt('Medication Usage'); ?>:</label>
    <?php
    generate_form_field(['data_type' => 1, 'field_id' => 'medication[usage_category]', 'list_id' => 'medication-usage-category', 'empty_title' => 'SKIP'], $usage_category); ?>
    <label class="col-form-label" for="medication[request_intent]"><?php echo xlt('Medication Request Intent'); ?>:</label>
    <?php
    generate_form_field(['data_type' => 1, 'field_id' => 'medication[request_intent]', 'list_id' => 'medication-request-intent'], $request_intent);
    ?>
</div>
<div class="form-group col-sm-12 col-md-6">
    <label class="col-form-label" for="form_medication[drug_dosage_instructions]"><?php echo xlt('Medication Dosage Instructions'); ?>:</label>
    <textarea class="form-control" name='form_medication[drug_dosage_instructions]' id='form_medication[drug_dosage_instructions]'
              rows="4"><?php echo text($medication['drug_dosage_instructions'] ?? '') ?></textarea>
</div>
<div class="form-group col-sm-12">
    <label class="col-form-label" for="form_medication[is_primary_record]"><?php echo xlt('Is Primary Record (not reported by secondary source)?'); ?>:</label>
    <radiogroup name="form_medication[is_primary_record]" id="form_medication[is_primary_record]">
        <label class="radio-inline">
            <input type="radio" class='medication-reported-option' name="form_medication[is_primary_record]" value="1" <?php if ($isPrimaryRecord == '1') {
                echo 'checked';
                                                                                                                       } ?>> <?php echo xlt('Yes'); ?>
        </label>
        <label class="radio-inline">
            <input type="radio" class='medication-reported-option' name="form_medication[is_primary_record]" value="0" <?php if ($isPrimaryRecord == '0') {
                echo 'checked';
                                                                                                                       } ?>> <?php echo xlt('No'); ?>
        </label>
    </radiogroup>
</div>
<div class="form-group col-sm-12 <?php if ($isPrimaryRecord) { ?>d-none<?php } ?>" id="medication-reported-by-container">
    <label class="col-form-label" for="medication[reported_by]"><?php echo xlt('Reported By (Address Book User)'); ?>:</label>
    <?php
    generate_form_field(['data_type' => 14, 'field_id' => 'medication[reporting_source_record_id]'], $medication['reporting_source_record_id'] ?? null);
    ?>
</div>
<script>
    $(function () {
       $("input.medication-reported-option").click(function () {
           var isPrimary = $(this).val();
           if (isPrimary === '1') {
               // Primary Record selected
               $("#medication-reported-by-container").addClass('d-none');

           } else {
               // Reported Record selected
               $("#medication-reported-by-container").removeClass('d-none');
           }
       });
    });
</script>
