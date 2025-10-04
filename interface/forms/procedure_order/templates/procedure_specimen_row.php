<?php

/**
 * Per-procedure-item Specimen panel (multi-row)
 * $i is the procedure line index in the parent form.
 * Uses list_options: specimen_type, specimen_location, specimen_condition
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure $i and optional $specimens are in scope
$line = (int)$i;
$rows = $specimen_by_seq[(int)($oprow['procedure_order_seq'] ?? 0)] ?? [];
?>
<tr class="specimenContainer collapse" id="specimen_code_<?php echo attr($i); ?>">
    <td colspan="6" class="bg-light">
        <div class="card my-2">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="font-weight-bold"><?php echo xlt('Specimens for this Test'); ?></span>
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
                            <!--<th><?php /*echo xlt('Identifier'); */?></th>
                            <th><?php /*echo xlt('Accession'); */?></th>-->
                            <th><?php echo xlt('Type'); ?></th>
                            <th><?php echo xlt('Method'); ?></th>
                            <th><?php echo xlt('Site'); ?></th>
                            <!--<th><?php /*echo xlt('Collected'); */?></th>-->
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
                            <tr>
                                <!--<td>
                                    <input type="text" class="form-control"
                                        name="form_proc_specimen_identifier[<?php /*echo $i; */?>][]"
                                        value="<?php /*echo attr($sp['specimen_identifier'] ?? ''); */?>"
                                        placeholder="<?php /*echo xla('Tube barcode / internal id'); */?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control"
                                        name="form_proc_accession_identifier[<?php /*echo $i; */?>][]"
                                        value="<?php /*echo attr($sp['accession_identifier'] ?? ''); */?>"
                                        placeholder="<?php /*echo xla('Lab accession'); */?>">
                                </td>-->
                                <td>
                                    <input type="hidden"
                                        name="form_proc_specimen_type_code[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['specimen_type_code'] ?? ''); ?>">
                                    <?php
                                    ob_start();
                                    generate_form_field([
                                        'data_type' => 1,
                                        'field_id'  => "proc_specimen_type[$i][]",
                                        'list_id'   => 'specimen_type'
                                    ], $sp['specimen_type'] ?? '');
                                    $typeField = ob_get_clean();
                                    echo $typeField;
                                    ?>
                                </td>
                                <td>
                                    <input type="hidden"
                                        name="form_proc_collection_method_code[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['collection_method_code'] ?? ''); ?>">
                                    <?php
                                    ob_start();
                                    generate_form_field([
                                        'data_type' => 1,
                                        'field_id'  => "proc_collection_method[$i][]",
                                        'list_id'   => 'specimen_collection_method'
                                    ], $sp['collection_method'] ?? '');
                                    $methodField = ob_get_clean();
                                    echo $methodField;
                                    ?>
                                </td>
                                <td>
                                    <input type="hidden"
                                        name="form_proc_specimen_location_code[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['specimen_location_code'] ?? ''); ?>">
                                    <?php
                                    ob_start();
                                    generate_form_field([
                                        'data_type' => 1,
                                        'field_id'  => "proc_specimen_location[$i][]",
                                        'list_id'   => 'specimen_location'
                                    ], $sp['specimen_location'] ?? '');
                                    $siteField = ob_get_clean();
                                    echo $siteField;
                                    ?>
                                </td>
                                <!--<td>
                                    <input type="text" class="form-control datetimepicker"
                                        name="form_proc_specimen_collected[<?php /*echo $i; */?>][]"
                                        value="<?php /*echo attr($sp['collected_date'] ?? ''); */?>"
                                        placeholder="<?php /*echo xla('Single instant'); */?>">
                                </td>-->
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
                                    <input type="hidden"
                                        name="form_proc_specimen_condition_code[<?php echo $i; ?>][]"
                                        value="<?php echo attr($sp['condition_code'] ?? ''); ?>">
                                    <?php
                                    ob_start();
                                    generate_form_field([
                                        'data_type' => 1,
                                        'field_id'  => "proc_specimen_condition[$i][]",
                                        'list_id'   => 'specimen_condition'
                                    ], $sp['condition'] ?? '');
                                    $condField = ob_get_clean();
                                    echo $condField;
                                    ?>
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
            <tr>
                <!--<td>
                    <input type="text" class="form-control"
                        name="form_proc_specimen_identifier[<?php /*echo $i; */?>][]"
                        placeholder="<?php /*echo xla('Tube barcode / internal id'); */?>">
                </td>
                <td>
                    <input type="text" class="form-control"
                        name="form_proc_accession_identifier[<?php /*echo $i; */?>][]"
                        placeholder="<?php /*echo xla('Lab accession'); */?>">
                </td>-->
                <td>
                    <input type="hidden" name="form_proc_specimen_type_code[<?php echo $i; ?>][]" value="">
                    <?php
                    ob_start();
                    generate_form_field([
                        'data_type' => 1,
                        'field_id'  => "proc_specimen_type[$i][]",
                        'list_id'   => 'specimen_type'
                    ], '');
                    $typeField = ob_get_clean();
                    echo $typeField;
                    ?>
                </td>
                <td>
                    <input type="hidden" name="form_proc_collection_method_code[<?php echo $i; ?>][]" value="">
                    <?php
                    ob_start();
                    generate_form_field([
                        'data_type' => 1,
                        'field_id'  => "proc_collection_method[$i][]",
                        'list_id'   => 'specimen_collection_method'
                    ], '');
                    $methodField = ob_get_clean();
                    echo $methodField;
                    ?>
                </td>
                <td>
                    <input type="hidden" name="form_proc_specimen_location_code[<?php echo $i; ?>][]" value="">
                    <?php
                    ob_start();
                    generate_form_field([
                        'data_type' => 1,
                        'field_id'  => "proc_specimen_location[$i][]",
                        'list_id'   => 'specimen_location'
                    ], '');
                    $siteField = ob_get_clean();
                    echo $siteField;
                    ?>
                </td>
                <!--<td>
                    <input type="text" class="form-control datetimepicker"
                        name="form_proc_specimen_collected[<?php /*echo $i; */?>][]"
                        placeholder="<?php /*echo xla('Single instant'); */?>">
                </td>-->
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
                    <input type="hidden" name="form_proc_specimen_condition_code[<?php echo $i; ?>][]" value="">
                    <?php
                    ob_start();
                    generate_form_field([
                        'data_type' => 1,
                        'field_id'  => "proc_specimen_condition[$i][]",
                        'list_id'   => 'specimen_condition'
                    ], '');
                    $condField = ob_get_clean();
                    echo $condField;
                    ?>
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
