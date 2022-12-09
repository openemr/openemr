<?php

$showAuthReqStyle = $dt[$field_prefix . 'auth_req'] ? "display:block;" : "display:none;";

?>
<!-- Authorization Details -->
<div id="case_header_auth_req_container" class="form-row mt-4 authorization-container" style="<?php echo $showAuthReqStyle; ?>">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
              <h6 class="mb-0 d-inline-block"><?php echo xl('Authorization Details'); ?></h6>
            </div>
            <div class="card-body px-2 py-2">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label><?php echo xl('Start Date'); ?>:</label>
                        <input type="text" name="<?php echo $field_prefix; ?>auth_start_date" id="<?php echo $field_prefix; ?>auth_start_date" class="form-control auth_date auth_start_date datepicker" placeholder="Start Date" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'auth_start_date'])); ?>" title="Enter as <?php echo $date_title_fmt; ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label><?php echo xl('End Date'); ?></label>
                        <input type="text" name="<?php echo $field_prefix; ?>auth_end_date" id="<?php echo $field_prefix; ?>auth_end_date" class="form-control auth_date auth_end_date datepicker" placeholder="End Date" value="<?php echo attr(oeFormatShortDate($dt[$field_prefix . 'auth_end_date'])); ?>" title="Enter as <?php echo $date_title_fmt; ?>">
                    </div>

                    <div class="form-group col-md-3">
                        <label><?php echo xl('Authorized Number of Visits'); ?>:</label>
                        <input type="number" min="1" max="999" name="<?php echo $field_prefix; ?>auth_num_visit" id="<?php echo $field_prefix; ?>auth_num_visit" class="form-control auth_num_visit" placeholder="0" value="<?php echo attr($dt[$field_prefix . 'auth_num_visit']); ?>">
                    </div>

                    <div class="form-group col-md-3">
                        <label ><?php echo xl('Authorized Provider'); ?>:</label>
                        <select name="<?php echo $field_prefix; ?>auth_provider" id="<?php echo $field_prefix; ?>auth_provider" class="form-control auth_provider">
                            <?php ProviderSelect($dt[$field_prefix . 'auth_provider']); ?>
                        </select>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-lg-6">
                        <label for="validationCustom01"><?php echo xl('Authorization Notes'); ?>:</label>
                        <textarea name="<?php echo $field_prefix; ?>auth_notes" id="<?php echo $field_prefix; ?>auth_notes" class="form-control" placeholder="Authorization Notes"><?php echo attr($dt[$field_prefix . 'auth_notes']); ?></textarea>
                    </div>
                    <div class="col-lg-6">
                    </div>
                  </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    // Validation Function
    window.formScriptValidations.push(() => caselibObj.validate_Auth());
</script>