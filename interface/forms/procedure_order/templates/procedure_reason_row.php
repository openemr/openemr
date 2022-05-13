<?php

/**
 * procedure_reason_row.php is a template file for the procedure order reason data elements.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<tr class="reasonCodeContainer reason_code <?php echo !empty($oprow['reason_code']) ? "" : "d-none"; ?>" id="reason_code_<?php echo attr($i); ?>">
    <td colspan="6" class="border-top-0">
        <div class="card mt-2 mb-4">
            <div class="card-header">
                <?php echo xlt("Procedure Order Reason Information"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label><?php echo xlt("Reason Code"); ?></label>
                        <input class="code-selector-popup form-control"
                               name="form_proc_reason_code[<?php echo attr($i); ?>]" type="text" value="<?php echo attr($oprow['reason_code'] ?? ""); ?>"
                               placeholder="<?php echo xla("Select a reason code"); ?>"
                        />
                        <p class="code-selector-text-display <?php echo empty($oprow['ob_reason_text']) ? "d-none" : ''; ?>"><?php echo text($oprow['reason_text'] ?? ""); ?></p>
                        <input type="hidden" name="form_proc_reason_code_text[<?php echo attr($i); ?>]" class="code-selector-text" value="<?php echo attr($oprow['reason_text'] ?? ""); ?>" />
                    </div>
                    <div class="col-md-6 form-group">
                        <label><?php echo xlt("Reason Status"); ?></label>
                        <select name="form_proc_reason_status[<?php echo attr($i); ?>]" class="form-control">
                            <?php foreach ($reasonCodeStatii as $code => $codeDesc) : ?>
                                <option value="<?php echo attr($code); ?>"
                                    <?php if (($oprow['reason_status'] ?? "") == $code) {
                                        echo "selected";} ?> >
                                    <?php echo text($codeDesc['description']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><?php echo xlt("Reason Start Date"); ?></label>
                        <input type='text' id="form_proc_reason_low_<?php echo attr($i) ?>"
                               name='form_proc_reason_date_low[<?php echo attr($i); ?>]'
                               class="form-control code_date datepicker"
                               value='<?php echo attr($oprow["reason_date_low"] ?? ''); ?>'
                               title='<?php echo xla('yyyy-mm-dd HH:MM Start date for reason'); ?>' />
                    </div>
                    <div class="col-md-6 form-group">
                        <label><?php echo xlt("Reason End Date (leave empty if reason does not end)"); ?></label>
                        <input type='text' id="form_proc_reason_high_<?php echo attr($i); ?>"
                               name='form_proc_reason_date_high[<?php echo attr($i); ?>]'
                               class="form-control code_date datepicker"
                               value='<?php echo attr($oprow["reason_date_high"] ?? ''); ?>'
                               title='<?php echo xla('yyyy-mm-dd HH:MM End date for reason'); ?>' />
                    </div>
                </div>
            </div>
        </div>
    </td>
</tr>