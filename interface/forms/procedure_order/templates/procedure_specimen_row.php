<?php

/**
 * Template for rendering specimen rows within a procedure order form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/** @var array<int, array<string, mixed>> $specimen_by_seq */
/** @var array<string, mixed>|null $oprow */
/** @var int|null $i */

$i = isset($i) ? (int)$i : 0;
$oprow ??= null;

$seq = (int)($oprow['procedure_order_seq'] ?? 0);
$rows = $specimen_by_seq[$seq];
?>
<tr class="specimenContainer collapse" id="specimen_code_<?php echo attr($i); ?>">
    <td colspan="6" class="bg-light">
        <div class="card my-2">
            <div class="card-header py-1 d-flex justify-content-between align-items-center bg-info m-1">
                <span class="font-weight-bold"><?php echo xlt('Specimens for Test') . ' => ' . text($oprow['procedure_name']); ?></span>
                <button type="button"
                    class="btn btn-sm btn-secondary add-specimen-row"
                    data-specimen-line="<?php echo attr($i); ?>">
                    <i class="fa fa-plus mr-1"></i><?php echo xlt('Add Specimen'); ?>
                </button>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th><?php echo xlt('Identifier'); ?></th>
                            <th><?php echo xlt('Accession'); ?></th>
                            <th><?php echo xlt('Type'); ?></th>
                            <th><?php echo xlt('Method'); ?></th>
                            <th><?php echo xlt('Site'); ?></th>
                            <th><?php echo xlt('Period Start'); ?></th>
                            <th><?php echo xlt('Period End'); ?></th>
                            <th><?php echo xlt('Volume'); ?></th>
                            <th><?php echo xlt('Condition'); ?></th>
                            <th><?php echo xlt('Comments'); ?></th>
                            <th class="text-right"><?php echo xlt('Actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody id="specimen_rows_<?php echo attr($i); ?>">
                        <?php foreach ($rows as $sp) : ?>
                            <tr class="bg-info">
                                <!-- CRITICAL: Hidden field to track specimen ID -->
                                <input type="hidden"
                                    name="form_proc_specimen_id[<?php echo $i; ?>][]"
                                    value="<?php echo attr($sp['procedure_specimen_id']); ?>">
                                <td>
                                    <input type="text" class="form-control"
                                        name="form_proc_specimen_identifier[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['specimen_identifier']); ?>"
                                        placeholder="<?php echo xla('Tube barcode / internal id'); ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control"
                                        name="form_proc_accession_identifier[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['accession_identifier']); ?>"
                                        placeholder="<?php echo xla('Lab accession'); ?>">
                                </td>
                                <td>
                                    <?php
                                    ob_start();
                                    generate_form_field([
                                        'data_type' => 1,
                                        'field_id' => "proc_specimen_type_code[$i][]",
                                        'list_id' => 'specimen_type'
                                    ], $sp['specimen_type_code'] ?? '');
                                    echo ob_get_clean();
                                    ?>
                                    <input type="hidden"
                                        name="form_proc_specimen_type[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['specimen_type'] ?? ''); ?>">
                                </td>
                                <td>
                                    <?php
                                    ob_start();
                                    generate_form_field([
                                        'data_type' => 1,
                                        'field_id' => "proc_collection_method_code[$i][]",
                                        'list_id' => 'specimen_collection_method'
                                    ], $sp['collection_method_code'] ?? '');
                                    echo ob_get_clean();
                                    ?>
                                    <input type="hidden"
                                        name="form_proc_collection_method[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['collection_method'] ?? ''); ?>">
                                </td>
                                <td>
                                    <?php
                                    ob_start();
                                    generate_form_field([
                                        'data_type' => 1,
                                        'field_id' => "proc_specimen_location_code[$i][]",
                                        'list_id' => 'specimen_location'
                                    ], $sp['specimen_location_code'] ?? '');
                                    echo ob_get_clean();
                                    ?>
                                    <input type="hidden"
                                        name="form_proc_specimen_location[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['specimen_location'] ?? ''); ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control datetimepicker"
                                        name="form_proc_specimen_date_low[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['collection_date_low'] ?? ''); ?>"
                                        placeholder="<?php echo xla('Start'); ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control datetimepicker"
                                        name="form_proc_specimen_date_high[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['collection_date_high'] ?? ''); ?>"
                                        placeholder="<?php echo xla('End (optional)'); ?>">
                                </td>
                                <td class="d-flex">
                                    <input type="number" step="0.1" min="0" class="form-control"
                                        name="form_proc_specimen_volume_value[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['volume_value'] ?? ''); ?>"
                                        placeholder="<?php echo xla('mL'); ?>">
                                    <input type="hidden"
                                        name="form_proc_specimen_volume_unit[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['volume_unit'] ?? 'mL'); ?>">
                                </td>
                                <td>
                                    <?php
                                    ob_start();
                                    generate_form_field([
                                        'data_type' => 1,
                                        'field_id' => "proc_specimen_condition_code[$i][]",
                                        'list_id' => 'specimen_condition'
                                    ], $sp['condition_code'] ?? '');
                                    echo ob_get_clean();
                                    ?>
                                    <input type="hidden"
                                        name="form_proc_specimen_condition[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['specimen_condition'] ?? ''); ?>">
                                </td>
                                <td>
                                <textarea class="form-control" rows="1"
                                    name="form_proc_specimen_comments[<?php echo $i; ?>][]"
                                    placeholder="<?php echo xla('Notes'); ?>"><?php echo text($sp['comments'] ?? ''); ?></textarea>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-sm btn-link text-danger remove-specimen-row" title="<?php echo xla('Remove'); ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Template for adding new rows -->
        <template id="specimen_row_template_<?php echo attr($i); ?>">
            <tr class="bg-info">
                <!-- CRITICAL: Empty hidden field for new specimens (no ID yet) -->
                <input type="hidden"
                    name="form_proc_specimen_id[<?php echo $i; ?>][]"
                    value="">
                <td>
                    <input type="text" class="form-control"
                        name="form_proc_specimen_identifier[<?php echo $i; ?>][]"
                        placeholder="<?php echo xla('Tube barcode / internal id'); ?>">
                </td>
                <td>
                    <input type="text" class="form-control"
                        name="form_proc_accession_identifier[<?php echo $i; ?>][]"
                        placeholder="<?php echo xla('Lab accession'); ?>">
                </td>

                <td>
                    <?php
                    ob_start();
                    generate_form_field([
                        'data_type' => 1,
                        'field_id' => "proc_specimen_type_code[$i][]",
                        'list_id' => 'specimen_type'
                    ], '');
                    echo ob_get_clean();
                    ?>
                    <input type="hidden" name="form_proc_specimen_type[<?php echo $i; ?>][]" value="">
                </td>
                <td>
                    <?php
                    ob_start();
                    generate_form_field([
                        'data_type' => 1,
                        'field_id' => "proc_collection_method_code[$i][]",
                        'list_id' => 'specimen_collection_method'
                    ], '');
                    echo ob_get_clean();
                    ?>
                    <input type="hidden" name="form_proc_collection_method[<?php echo $i; ?>][]" value="">
                </td>
                <td>
                    <?php
                    ob_start();
                    generate_form_field([
                        'data_type' => 1,
                        'field_id' => "proc_specimen_location_code[$i][]",
                        'list_id' => 'specimen_location'
                    ], '');
                    echo ob_get_clean();
                    ?>
                    <input type="hidden" name="form_proc_specimen_location[<?php echo $i; ?>][]" value="">
                </td>
                <td>
                    <input type="text" class="form-control datetimepicker"
                        name="form_proc_specimen_date_low[<?php echo $i; ?>][]"
                        placeholder="<?php echo xla('Start'); ?>">
                </td>
                <td>
                    <input type="text" class="form-control datetimepicker"
                        name="form_proc_specimen_date_high[<?php echo $i; ?>][]"
                        placeholder="<?php echo xla('End (optional)'); ?>">
                </td>
                <td class="d-flex">
                    <input type="number" step="0.1" min="0" class="form-control"
                        name="form_proc_specimen_volume_value[<?php echo $i; ?>][]"
                        placeholder="<?php echo xla('mL'); ?>">
                    <input type="hidden" name="form_proc_specimen_volume_unit[<?php echo $i; ?>][]" value="mL">
                </td>
                <td>
                    <?php
                    ob_start();
                    generate_form_field([
                        'data_type' => 1,
                        'field_id' => "proc_specimen_condition_code[$i][]",
                        'list_id' => 'specimen_condition'
                    ], '');
                    echo ob_get_clean();
                    ?>
                    <input type="hidden" name="form_proc_specimen_condition[<?php echo $i; ?>][]" value="">
                </td>
                <td>
                    <textarea class="form-control" rows="1"
                        name="form_proc_specimen_comments[<?php echo $i; ?>][]"
                        placeholder="<?php echo xla('Notes'); ?>"></textarea>
                </td>
                <td class="text-right">
                    <button type="button" class="btn btn-sm btn-link text-danger remove-specimen-row" title="<?php echo xla('Remove'); ?>">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        </template>
    </td>
</tr>
