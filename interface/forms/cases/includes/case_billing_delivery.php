<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtcase.class.php');

$blStatus = wmtCase::manageInsData($pid);
//$trClasss = !$blStatus ? 'trHide' : '';
$bllogsData = wmtCase::fetchCaseAlertLogs($id, 5);

?>
<div id="lb_row" class="form-row mt-4 billing-delivery-status-container">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
              <h6 class="mb-0 d-inline-block"><?php echo xl('Billing/Collection Delivery Status'); ?></h6>
            </div>
            <div class="card-body px-2 py-2">
                <div class="form-row">
                    <div class="col-lg-6">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label><?php echo xl('Delivery Date'); ?>:</label>
                                <input type="text" class="form-control datepicker" name="<?php echo $field_prefix; ?>bc_date" id="<?php echo $field_prefix; ?>bc_date" placeholder="Delivery Date" value="<?php echo oeFormatShortDate($dt[$field_prefix . 'bc_date']); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label><?php echo xl('Billing Notes'); ?>:</label>
                                <select name="<?php echo $field_prefix; ?>bc_notes" id="<?php echo $field_prefix; ?>bc_notes" class='form-control'>
                                    <?php ListSel($dt[$field_prefix . 'bc_notes'], 'Case_Billing_Notes'); ?>
                                </select>
                            </div>
                          </div>

                          <div class="form-row">
                            <div class="form-group col-md-12">
                                <label><?php echo xl('Notes'); ?>:</label>
                                <textarea name="<?php echo $field_prefix; ?>bc_notes_dsc" id="<?php echo $field_prefix; ?>bc_notes_dsc" class="form-control" placeholder="Notes"><?php echo $dt[$field_prefix . 'bc_notes_dsc']; ?></textarea>
                                <textarea type="text" name="tmp_old_bc_value" id="tmp_old_bc_value" class="form-control hideElement"><?php echo oeFormatShortDate($dt[$field_prefix . 'bc_date']) . $dt[$field_prefix . 'bc_notes'] . $dt[$field_prefix . 'bc_notes_dsc'] ?></textarea>
                            </div>
                          </div>

                          <div class="form-row">
                            <div class="form-group col-md-12">
                                <div class="d-inline-block">
                                <div class="form-check">
                                  <input class="form-check-input" type="checkbox" name="<?php echo $field_prefix; ?>bc_stat" id="<?php echo $field_prefix; ?>bc_stat" class="bc_stat" value="1" <?php echo $dt[$field_prefix . 'bc_stat'] ? 'checked' : ''; ?>>
                                  <label class="form-check-label"><?php echo xl('Stat'); ?></label>
                                </div>
                              </div>
                            </div>
                          </div>

                    </div>
                    <div class="col-lg-6">

                        <div class="logContainer">
                            <div>
                                <div class="alert_log_table_container">
                                    <table class="alert_log_table text table table-sm table-bordered table-striped mb-1">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th><?php echo xl('Sr.'); ?></th>
                                                <th><?php echo xl('Delivery Date'); ?></th>
                                                <th><?php echo xl('Notes'); ?></th>
                                                <th><?php echo xl('Username'); ?></th>
                                                <th><?php echo xl('Created Time'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $ci = 1;
                                            foreach ($bllogsData as $key => $item) {
                                                ?>
                                                <tr>
                                                    <td><?php echo $ci; ?></td>
                                                    <td ><?php echo $item['delivery_date']; ?></td>
                                                    <td><?php echo $item['notes']; ?></td>
                                                    <td><?php echo $item['user_name']; ?></td>
                                                    <td><?php echo date('d-m-Y h:i:s',strtotime($item['created_date'])); ?></td>
                                                </tr>
                                                <?php
                                                $ci++;
                                            }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if(isset($id) && !empty($id)) { ?>
                                <a href="javascript:void(0)" onClick="caselibObj.open_notes_log('<?php echo $pid ?>', '<?php echo $id ?>')"><?php echo xl('View logs'); ?></a>
                                <?php } ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>