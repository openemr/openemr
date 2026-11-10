<?php

/**
 * procedure_reason_row.php is a template file for the procedure order reason data elements.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** @var int $i (loop index from including file procedure_order/common.php) */
/** @var array<string, array{code: string, description: string}> $reasonCodeStatii (set in including file) */

// The order line, or none for a new line (the including file can leave $oprow false).
$reasonRow = isset($oprow) && is_array($oprow) ? $oprow : [];
$reasonField = static fn (string $key): string => is_string($reasonRow[$key] ?? null) ? $reasonRow[$key] : '';
$reasonCode = $reasonField('reason_code');
// The saved description, posted back with the form so a save that changes nothing keeps it.
$reasonDescription = $reasonField('reason_description');
$reasonStatus = $reasonField('reason_status');
$reasonDateLow = $reasonField('reason_date_low');
$reasonDateHigh = $reasonField('reason_date_high');

?>
<tr class="reasonCodeContainer reason_code <?php echo $reasonCode !== '' ? "" : "d-none"; ?>" id="reason_code_<?php echo attr((string) $i); ?>">
    <td colspan="6" class="border-top-0">
        <div class="card mt-2 mb-4 container-xl mt-0">
            <div class="card-header">
                <?php echo xlt("Procedure Order Reason Information"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label><?php echo xlt("Reason Code"); ?></label>
                        <input class="code-selector-popup form-control"
                               name="form_proc_reason_code[<?php echo attr((string) $i); ?>]" type="text" value="<?php echo attr($reasonCode); ?>"
                               placeholder="<?php echo xla("Select a reason code"); ?>"
                               aria-label="<?php echo xla("Reason Code"); ?>"
                               readonly
                               onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); this.click(); }"
                        />
                        <p class="code-selector-text-display <?php echo $reasonDescription === '' ? "d-none" : ''; ?>"><?php echo text($reasonDescription); ?></p>
                        <input type="hidden" name="form_proc_reason_code_text[<?php echo attr((string) $i); ?>]" class="code-selector-text" value="<?php echo attr($reasonDescription); ?>" />
                    </div>
                    <div class="col-md-6 form-group">
                        <label><?php echo xlt("Reason Status"); ?></label>
                        <select name="form_proc_reason_status[<?php echo attr((string) $i); ?>]" class="form-control">
                            <?php foreach ($reasonCodeStatii as $code => $codeDesc) : ?>
                                <option value="<?php echo attr($code); ?>"
                                    <?php if ($reasonStatus === $code) {
                                        echo "selected";} ?> >
                                    <?php echo text($codeDesc['description']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><?php echo xlt("Reason Start Date"); ?></label>
                        <input type='text' id="form_proc_reason_low_<?php echo attr((string) $i) ?>"
                               name='form_proc_reason_date_low[<?php echo attr((string) $i); ?>]'
                               class="form-control code_date datepicker"
                               value='<?php echo attr($reasonDateLow); ?>'
                               title='<?php echo xla('yyyy-mm-dd HH:MM Start date for reason'); ?>' />
                    </div>
                    <div class="col-md-6 form-group">
                        <label><?php echo xlt("Reason End Date (leave empty if reason does not end)"); ?></label>
                        <input type='text' id="form_proc_reason_high_<?php echo attr((string) $i); ?>"
                               name='form_proc_reason_date_high[<?php echo attr((string) $i); ?>]'
                               class="form-control code_date datepicker"
                               value='<?php echo attr($reasonDateHigh); ?>'
                               title='<?php echo xla('yyyy-mm-dd HH:MM End date for reason'); ?>' />
                    </div>
                </div>
            </div>
        </div>
    </td>
</tr>
