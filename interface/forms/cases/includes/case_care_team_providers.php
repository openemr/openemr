<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtcase.class.php');

$sc_referring_id_tmp = isset($dt['sc_referring_id']) ? $dt['sc_referring_id'] : "";
$sc_referring_id_tmp = explode("|",$sc_referring_id_tmp);
$sc_referring_id = json_encode($sc_referring_id_tmp);

?>
<!-- Care Team Providers -->
<div class="form-row mt-4 care-team-providers-container">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
              <h6 class="mb-0 d-inline-block"><?php echo xl('Care Team Providers'); ?></h6>
            </div>
            <div class="card-body px-2 py-2">
                <div class="form-row">
                    <div class="col-lg-6">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label><?php echo xl('Referring Provider'); ?>:</label>
                                <select class="form-control cs_referring" name="<?php echo $field_prefix; ?>referring_id" id="<?php echo $field_prefix; ?>referring_id">
                                    <?php wmtCase::referringSelect($dt[$field_prefix . 'referring_id'], '', '', array('Referral Source', 'external_provider')); ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label><?php echo xl('Additional Providers'); ?>:</label>

                              <div id="aprovider_wrapper" class="d-flex align-items-start m-main-wrapper">
                                    <div class="m-elements-wrapper mr-2 w-100">
                                        <!-- Input container -->
                                        <?php foreach ($sc_referring_id_tmp as $scrKey => $scrItem) { ?>
                                        <div class="m-element-wrapper mb-2">
                                            <!-- Field container -->
                                            <div class="input-group">
                                                <select class="form-control" data-field-id="sc_referring_id" name="tmp_<?php echo $field_prefix; ?>sc_referring_id[]">
                                                    <?php wmtCase::referringSelect($scrItem, '', '', array('Referral Source', 'external_provider')); ?>
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
                                    <button type="button" class="btn btn-primary m-btn-add" style="white-space: nowrap;"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo xl('Add more'); ?></button>
                                </div>

                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // Init multi elements
        $('#aprovider_wrapper').multielement();
    });
</script>