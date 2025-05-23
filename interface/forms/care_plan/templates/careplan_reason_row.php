<?php

/**
 * careplan_reason_row.php is a template file for the careplan reason data elements.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<div class="form-row reasonCodeContainer reason_code <?php echo !empty($obj['reason_code']) ? "" : "d-none"; ?>" id="reason_code_<?php echo attr($key); ?>">
    <div class="card mt-2 mb-4">
        <div class="card-header">
            <?php echo xlt("Care Plan Reason Information"); ?>
        </div>
        <div class="card-body">
            <div class="row">
                <p class="col">
                    <?php echo xlt("When recording a reason for the value (or absence of a value) of an observation both the reason code and status of the reason are required"); ?>
                </p>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label><?php echo xlt("Reason Code"); ?></label>
                    <input class="code-selector-popup form-control"
                           name="reasonCode[]" type="text" value="<?php echo attr($obj['reason_code'] ?? ""); ?>"
                           placeholder="<?php echo xlt("Select a reason code"); ?>"
                    />
                    <p class="code-selector-text-display <?php echo empty($obj['reason_description']) ? "d-none" : ''; ?>"><?php echo text($obj['reason_description'] ?? ""); ?></p>
                    <input type="hidden" name="reasonCodeText[]" class="code-selector-text" value="<?php echo attr($obj['reason_description'] ?? ""); ?>" />
                </div>
                <div class="col-md-6 form-group">
                    <label><?php echo xlt("Reason Status"); ?></label>
                    <select name="reasonCodeStatus[]" class="form-control">
                        <?php foreach ($reasonCodeStatii as $code => $codeDesc) : ?>
                            <option value="<?php echo attr($code); ?>"
                                <?php if (($obj['reason_status'] ?? "") == $code) {
                                    echo "selected";} ?> >
                                <?php echo text($codeDesc['description']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label><?php echo xlt("Reason Recording Date"); ?></label>
                    <input type='text' name='reasonDateLow[]' class="form-control reason_start_date datepicker" value='<?php echo attr($obj["reason_date_low"] ?? ''); ?>' title='<?php echo xla('yyyy-mm-dd HH:MM Reason Recording Date'); ?>' />
                </div>
                <div class="col-md-6 form-group">
                    <label><?php echo xlt("Reason End Date (Leave empty if there is no end date)"); ?></label>
                    <input type='text' name='reasonDateHigh[]' class="form-control reason_end_date datepicker" value='<?php echo attr($obj["reason_date_high"] ?? ''); ?>' title='<?php echo xla('yyyy-mm-dd HH:MM Reason End Date'); ?>' />
                </div>
            </div>
        </div>
    </div>
</div>