<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtpatient.class.php');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtcase.class.php');

//Load PI Case Manager Data
$piCaseData = wmtCase::piCaseManagerFormData($id, $field_prefix);
$dt = array_merge($dt, $piCaseData);

$rehabField2List = array(
    'PT' => 'PT',
    'LD' => 'LD',
    'CD' => 'CD',
    'DD' => 'DD'
);

$cslogsData = array();
if(!empty($id)) {
    $cslogsData = wmtCase::fetchAlertLogsByParam(array(
        'field_id' => 'rehab_field',
        'form_name' => 'form_cases',
        'pid' => $pid,
        'form_id' => $id
    ), 5);
}

$piCaseStatus = wmtCase::isInsLiableForPiCase($pid);
//$trClasss = !$piCaseStatus ? 'trHide' : '';

$rehab_field_1_val = isset($dt['tmp_'.$field_prefix.'rehab_field_1']) ? $dt['tmp_'.$field_prefix.'rehab_field_1'] : array();
$rehab_field_2_val = isset($dt['tmp_'.$field_prefix.'rehab_field_2']) ? $dt['tmp_'.$field_prefix.'rehab_field_2'] : array();
$rfieldCount = (count($rehab_field_1_val) == count($rehab_field_2_val)) ? count($rehab_field_1_val) : 1;
$rfieldCount = ($rfieldCount > 0) ? $rfieldCount : 1;

// Lawyer/Paralegal Contacts
$lp_contact_val = isset($dt['tmp_'.$field_prefix.'lp_contact']) ? $dt['tmp_'.$field_prefix.'lp_contact'] : array();

?>
<!-- Pi Case Management Section -->
<div id="pi_case_row" class="form-row mt-4 pi-case-management-container sec_row <?php echo !$piCaseStatus ? 'trHide' : ''; ?>">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
              <h6 class="mb-0 d-inline-block"><?php echo xl('PI Case Management'); ?></h6>
            </div>
            <div class="card-body px-2 py-2">
                <!-- Case Manager -->
                <div class="form-row">
                    <div class="col-lg-6">
                            <!-- Case manager -->
                            <div class="form-row">
                                <div class="form-group col-lg-4">
                                  <label for="case_id"><?php echo xl('Case Manager'); ?>:</label>
                                  <!-- hidden input -->
                                  <input type="hidden" name="<?php echo $field_prefix; ?>liability_payer_exists" class="liability_payer_exists" value="<?php echo $piCaseStatus === true ? 1 : 0 ?>">
                                  <input type="hidden" name="tmp_<?php echo $field_prefix; ?>casemanager_hidden_sec" class="hidden_sec_input tmp_casemanager_hidden_sec" value="<?php echo $piCaseStatus ? $piCaseStatus : 0 ?>">
                                  <select name="tmp_<?php echo $field_prefix; ?>case_manager" class="case_manager form-control makedisable" id="<?php echo $field_prefix; ?>case_manager">
                                    <?php wmtCase::getUsersBy($dt['tmp_' . $field_prefix . 'case_manager'], '', array('physician_type' => array('chiropractor_physician', 'case_manager_232321'))); ?>
                                  </select>
                                </div>
                                <div class="col-lg-8">
                                    <label for="case_id"><?php echo xl('Rehab Plan'); ?>:</label>
                                    <div id="reahab_wrapper" class="d-flex align-items-start m-main-wrapper">
                                        <div class="m-elements-wrapper mr-2">
                                            <?php for ($fi=0; $fi < $rfieldCount; $fi++) { ?>
                                            <!-- Input container -->
                                            <div class="m-element-wrapper mb-2">
                                                <!-- Field container -->
                                                <div class="input-group">
                                                    <select name="tmp_<?php echo $field_prefix; ?>rehab_field_1[]" class="form-control makedisable" data-field-id="rehab_field_1" >
                                                        <option value=""></option>
                                                        <?php
                                                            for ($i=1; $i <= 20 ; $i++) {
                                                                $isSelected = ($i == $rehab_field_1_val[$fi]) ? "selected" : ""; 
                                                                ?>
                                                                    <option value="<?php echo $i ?>" <?php echo $isSelected ?>><?php echo $i ?></option>
                                                                <?php
                                                            }
                                                        ?>
                                                      </select>
                                                      <select name="tmp_<?php echo $field_prefix; ?>rehab_field_2[]" class="form-control makedisable" data-field-id="rehab_field_2">
                                                        <option value=""></option>
                                                        <?php
                                                            foreach ($rehabField2List as $rbk => $rbItem) {
                                                                $isSelected = ($rbk == $rehab_field_2_val[$fi]) ? "selected" : ""; 
                                                                ?>
                                                                    <option value="<?php echo $rbk ?>" <?php echo $isSelected ?>><?php echo $rbItem ?></option>
                                                                <?php
                                                            }
                                                        ?>
                                                      </select>
                                                      <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary m-btn-remove"><i class="fa fa-times" aria-hidden="true"></i></button>
                                                        </div>
                                                </div>
                                                <!-- Remove Button -->
                                                <!-- <button type="button" class="btn btn-primary m-btn-remove"><i class="fa fa-times" aria-hidden="true"></i></button> -->
                                            </div>
                                            <?php } ?>
                                        </div>

                                        <!-- Add more item btn -->
                                        <button type="button" class="btn btn-primary m-btn-add" style="white-space: nowrap;"><i class="fa fa-plus" aria-hidden="true"></i> Add more</button>
                                    </div>

                                </div>
                            </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="alert_log_table_container">
                            <table class="alert_log_table text text table table-sm table-bordered table-striped mb-1">
                                <thead class="thead-dark">
                                    <tr>
                                        <th><?php echo xl('Sr.'); ?></th>
                                        <th><?php echo xl('New Value'); ?></th>
                                        <th><?php echo xl('Old Value'); ?></th>
                                        <th><?php echo xl('Username'); ?></th>
                                        <th><?php echo xl('DateTime'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $ci = 1;
                                    foreach ($cslogsData as $key => $item) {
                                        ?>
                                        <tr>
                                            <td><?php echo $ci; ?></td>
                                            <td style="vertical-align: text-top;"><div style="white-space: pre;"><?php echo $item['new_value']; ?></div></td>
                                            <td style="vertical-align: text-top;"><div style="white-space: pre;"><?php echo $item['old_value']; ?></div></td>
                                            <td><?php echo $item['user_name']; ?></td>
                                            <td><?php echo date('d-m-Y h:i:s',strtotime($item['date'])); ?></td>
                                        </tr>
                                        <?php
                                        $ci++;
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if(isset($id) && !empty($id)) { ?>
                        <a href="javascript:void(0)" onClick="caselibObj.open_field_log('<?php echo $pid ?>', '<?php echo $id ?>', 'rehab_field', 'form_cases')"><?php echo xl('View logs'); ?></a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Lawyer/Paralegal Contacts -->
                <div class="form-row mt-2">
                    <div class="col-lg-6">
                        <label for="case_id"><?php echo xl('Lawyer/Paralegal Contacts'); ?>:</label>
                            <div id="lpc_ele_container" class="d-flex align-items-start m-main-wrapper">
                                <div class="m-elements-wrapper mr-2 w-100">
                                    <?php foreach ((!empty($lp_contact_val) ? $lp_contact_val : array('')) as $lpk => $lpItem) { ?>
                                    <!-- Input container -->
                                    <div class="m-element-wrapper jumbotron jumbotron-fluid px-2 py-2 mb-2 mb-2">
                                        <!-- Field container -->
                                        <div>
                                        <div class="input-group">
                                          <select name="tmp_<?php echo $field_prefix; ?>lp_contact[]" class="form-control" data-field-id="lp_contact">
                                                <?php wmtCase::referringSelect($lpItem, '', '', array('Attorney'), '', true, true); ?>
                                          </select>
                                          <div class="input-group-append">
                                            <button type="button" class="btn btn-primary search_user_btn" href='<?php echo $GLOBALS['webroot']. '/interface/forms/cases/php/find_user_popup.php?abook_type=Attorney'; ?>'><i class="fa fa-search" aria-hidden="true"></i></button>
                                            </div>
                                        </div>
                                        <span class="field-text-info c-font-size-sm ipc_info_container c-text-info"></span>
                                    </div>
                                        <!-- Remove Button -->
                                        <button type="button" class="btn btn-primary m-btn-remove"><i class="fa fa-times" aria-hidden="true"></i></button>
                                    </div>
                                    <?php } ?>
                                </div>

                                <!-- Add more item btn -->
                                <button type="button" class="btn btn-primary m-btn-add" style="white-space: nowrap;"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo xl('Add more'); ?></button>
                            </div>
                    </div>
                    <div class="col-lg-6">
                    </div>
                </div>

                <!-- Email Addresses -->
                <div class="form-row mt-4">
                    <div class="form-group col-lg-6">
                        <label for="case_id" style="width: 100%;"><?php echo xl('Email Addresses'); ?>: <i style="float:right">**  <?php echo xl('Please use a comma to separate multiple addresses'); ?></i></label>
                        <textarea name="<?php echo $field_prefix; ?>notes" id="<?php echo $field_prefix; ?>notes" class="form-control" rows="3" placeholder="Email Addresses" <?php echo \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super') === false ? "readonly" : ""; ?>><?php echo attr($dt[$field_prefix . 'notes']); ?></textarea>
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
    window.formScriptValidations.push(() => caselibObj.validate_CaseForm());

    $(document).ready(function(){
        // Init multi elements
        $('#reahab_wrapper').multielement();
        $('#lpc_ele_container').multielement();

        //Init check
        caselibObj.piCaseManagerSet('#pi_case_row');

        // Lawyer/Paralegal Contacts Set Info
        $('#lpc_ele_container').on('change', 'select[data-field-id="lp_contact"]', function() {
            caselibObj.setLawyerParalegalContacts(this);
        });

        $('#lpc_ele_container select[data-field-id="lp_contact"]').each(function(i, ele) {
          caselibObj.setLawyerParalegalContacts(ele);
        });
    });
</script>