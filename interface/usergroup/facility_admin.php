<?php

/**
 * facility_admin.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

if (isset($_GET["fid"])) {
    $my_fid = $_GET["fid"];
}
?>
<html>
<head>
    <?php Header::setupHeader(['opener', 'erx']); ?>

    <script src="../main/calendar/modules/PostCalendar/pnincludes/AnchorPosition.js"></script>
    <script src="../main/calendar/modules/PostCalendar/pnincludes/PopupWindow.js"></script>
    <script src="../main/calendar/modules/PostCalendar/pnincludes/ColorPicker2.js"></script>

    <!-- validation library -->
    <!--//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation-->
    <?php $use_validate_js = 1; ?>
    <?php require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>
    <?php
    //Gets validation rules from Page Validation list.
    //Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
    $collectthis = collectValidationPageRules("/interface/usergroup/facility_admin.php");
    if (empty($collectthis)) {
        $collectthis = "undefined";
    } else {
        $collectthis = json_sanitize($collectthis["facility-form"]["rules"]);
    }
    ?>

    <script>

        /*
         * validation on the form with new client side validation (using validate.js).
         * this enable to add new rules for this form in the pageValidation list.
         * */
        var collectvalidation = <?php echo $collectthis; ?>;

        function submitform() {

            var valid = submitme(1, undefined, 'facility-form', collectvalidation);
            if (!valid) return;

            <?php if ($GLOBALS['erx_enable']) { ?>
            alertMsg = '';
            f = document.forms[0];
            for (i = 0; i < f.length; i++) {
                if (f[i].type == 'text' && f[i].value) {
                    if (f[i].name == 'facility' || f[i].name == 'Washington') {
                        alertMsg += checkLength(f[i].name, f[i].value, 35);
                        alertMsg += checkFacilityName(f[i].name, f[i].value);
                    } else if (f[i].name == 'street') {
                        alertMsg += checkLength(f[i].name, f[i].value, 35);
                        alertMsg += checkAlphaNumeric(f[i].name, f[i].value);
                    } else if (f[i].name == 'phone' || f[i].name == 'fax') {
                        alertMsg += checkPhone(f[i].name, f[i].value);
                    } else if (f[i].name == 'federal_ein') {
                        alertMsg += checkLength(f[i].name, f[i].value, 10);
                        alertMsg += checkFederalEin(f[i].name, f[i].value);
                    }
                }
            }
            if (alertMsg) {
                alert(alertMsg);
                return false;
            }
            <?php } ?>

            top.restoreSession();

            let post_url = $("#facility-form").attr("action");
            let request_method = $("#facility-form").attr("method");
            let form_data = $("#facility-form").serialize();

            $.ajax({
                url: post_url,
                type: request_method,
                data: form_data
            }).done(function (r) { //
                dlgclose('refreshme', false);
            });
            return false;
        }

        $(function () {
            /**
             * add required/star sign to required form fields
             */
            for (var prop in collectvalidation) {
                //if (collectvalidation[prop].requiredSign)
                if (collectvalidation[prop].presence)
                    $("label[for='" + prop + "']").append('*');
            }
        });
        var cp = new ColorPicker('window');

        // Runs when a color is clicked
        function pickColor(color) {
            document.getElementById('ncolor').value = color;
        }

        var field;

        function pick(anchorname, target) {
            var cp = new ColorPicker('window');
            field = target;
            cp.show(anchorname);
        }

        function displayAlert() {
            if (document.getElementById('primary_business_entity').checked == false)
                alert(<?php echo xlj('Primary Business Entity tax id is used as the account id for NewCrop ePrescription.'); ?>);
            else if (document.getElementById('primary_business_entity').checked == true)
                alert(<?php echo xlj('Once the Primary Business Facility is set, changing the facility id will affect NewCrop ePrescription.'); ?>);
        }
    </script>

</head>
<body class="body_top">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-sm-12 d-flex justify-content-between align-items-center">
                <h5 class="m-0"><?php echo xlt('Edit Facility'); ?></h5>
                <div>
                    <a class="btn btn-text" id='cancel' onclick='dlgclose();' href='#'><?php echo xlt('Cancel'); ?></a>
                    <a class="btn btn-primary" name='form_save' id='form_save' onclick='submitform();' href='#'><?php echo xlt('Save'); ?></a>
                </div>
            </div>
        </div>
        <form name='facility-form' id="facility-form" method='post' action="facilities.php">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type=hidden name='mode' value="facility" />
            <input type=hidden name='newmode' value="admin_facility" />
            <!-- diffrentiate Admin and add post backs !-->
            <input type=hidden name=fid value="<?php echo attr($my_fid); ?>" />
            <?php $facility = $facilityService->getById($my_fid); ?>

            <div class="form-row">
                <div class="form-group w-100">
                    <label for='facility' class="sr-only"><?php echo xlt('Name'); ?>:</label>
                    <input type='text' class='form-control form-control-lg' name='facility' value='<?php echo attr($facility['name']); ?>'>
                </div>
            </div>
            <div class="form-row">
                <div class="col-sm-12 col-md-7">
                    <div class="bg-light border">
                        <ul class="nav nav-pills mt-2" id="addressTab" role="tablist">
                            <li class="nav-item py-1 px-2" role="presentation">
                                <button type="button" class="nav-link py-1 px-2 active" id="physical-address-tab" data-toggle="tab" data-target="#physicalAddress" role="tab" aria-controls="physicalAddress" aria-selected="true"><?php echo xlt("Physical Address"); ?></button>
                            </li>
                            <li class="nav-item py-1 px-2" role="presentation">
                                <button type="button" class="nav-link py-1 px-2" id="mailing-address-tab" data-toggle="tab" data-target="#mailingAddress" role="tab" aria-controls="mailingAddress" aria-selected="true"><?php echo xlt("Mailing Address"); ?></button>
                            </li>
                        </ul>
                        <div class="tab-content m-2" id="addressTabContent">
                            <div class="tab-pane show active" id="physicalAddress" role="tabpanel" aria-labelledby="physical-address-tab">
                                <div class="form-row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for='street' class='col-form-label sr-only'><?php echo xlt('Address'); ?>: </label>
                                            <input type='text' class='form-control' name="street" placeholder="<?php echo xla("Address"); ?>" value="<?php echo attr($facility["street"]); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for='city' class='col-form-label sr-only'><?php echo xlt('City'); ?>: </label>
                                            <input type='text' class='form-control' name='city' placeholder="<?php echo xla("City"); ?>" value="<?php echo attr($facility["city"]); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for='state' class='col-form-label sr-only'><?php echo xlt('State'); ?>:</label>
                                            <input type='text' class='form-control' name='state' placeholder="<?php echo xla("State"); ?>" value="<?php echo attr($facility["state"]); ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label for='postal_code' class='col-form-label sr-only'><?php echo xlt('Zip Code'); ?>:</label>
                                            <input type='entry' class='form-control' placeholder="<?php echo xla("Zip Code"); ?>" name='postal_code' value="<?php echo attr($facility["postal_code"]); ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for='country_code' class='col-form-label sr-only'><?php echo xlt('Country'); ?>:</label>
                                            <input type='text' class='form-control' placeholder="<?php echo xla("Country"); ?>" name='country_code' value="<?php echo attr($facility["country_code"]); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="mailingAddress" role="tabpanel" aria-labelledby="mailing-address-tab">
                                <div class="form-row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="mail_street" class="col-form-label sr-only"><?php echo xlt('Mailing Address'); ?>:</label>
                                            <input type="text" class="form-control" placeholder="<?php echo xla("Street Addres"); ?>" name="mail_street" value="<?php echo attr($facility["mail_street"]) ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="mail_street2" class="col-form-label sr-only"><?php echo xlt('Suite'); ?>: </label>
                                            <input type="text" class="form-control" placeholder="<?php echo xla("Address Line Two"); ?>" name="mail_street2" value="<?php echo attr($facility["mail_street2"]) ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-sm-4">
                                        <label for="mail_city" class="col-form-label sr-only"><?php echo xlt('City'); ?>: </label>
                                        <input type="text" class="form-control" placeholder="<?php echo xla("City"); ?>" name="mail_city" value="<?php echo attr($facility["mail_city"]) ?>" />
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="mail_state" class="col-form-label sr-only"><?php echo xlt('State'); ?>: </label>
                                        <input type="text" class="form-control" placeholder="<?php echo xla("State"); ?>" name="mail_state" value="<?php echo attr($facility["mail_state"]) ?>" />
                                    </div>
                                    <div class="col-sm-4">
                                        <label for="mail_zip" class="col-form-label sr-only"><?php echo xlt('Zip'); ?>:</label>
                                        <input type="text" class="form-control" placeholder="<?php echo xla("Zip Code"); ?>" name="mail_zip" value="<?php echo attr($facility["mail_zip"]) ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-5">
                    <div class="p-2 bg-light border">
                        <div class="form-group row">
                            <label class="col-form-label col-sm-2" for='phone'><?php echo xlt('Phone'); ?></label>
                            <div class="col-sm-10">
                                <input type='text' class='form-control' name='phone' placeholder="<?php echo xla("Phone"); ?>" value='<?php echo attr($facility['phone']); ?>'>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-sm-2" for='fax'><?php echo xlt('Fax'); ?>:</label>
                            <div class="col-sm-10">
                                <input type='entry' class='form-control' name="fax" placeholder="<?php echo xla("Fax"); ?>" value='<?php echo attr($facility['fax']); ?>' />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-sm-2" for='website'><?php echo xlt('Website'); ?>:</label>
                            <div class="col-sm-10"><input type='entry' class='form-control' placeholder="<?php echo xla("Website"); ?>" name='website' value="<?php echo attr($facility["website"]); ?>" /></div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-sm-2" for='email'><?php echo xlt('Email'); ?>: </label>
                            <div class="col-sm-10"><input type='entry' class='form-control' placeholder="<?php echo xla("Email"); ?>" name='email' value="<?php echo attr($facility["email"]); ?>" /></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row mt-2">
            <div class="col-sm-12 col-md-7">
                <div class="border p-2 bg-light d-flex">
                    <div class="pr-1">
                        <div class="form-group">
                            <label for='ncolor'><?php echo xlt('Color'); ?>:</label>
                            <div class="input-group">
                                <input type='entry' class='form-control form-control-sm' name='ncolor' id='ncolor' size='20' value="<?php echo attr($facility["color"]); ?>" />
                                <div class="input-group-append">
                                    <a href="javascript:void(0);" onClick="pick('pick','newcolor'); return false;" class="btn btn-outline-secondary" NAME="pick" ID="pick"><i class="fa fa-eye-dropper"></i><span class="sr-only"><?php echo xlt('Pick'); ?></span></a>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for='pos_code'><?php echo xlt('POS Code'); ?>:</label>
                            <select name="pos_code" class="form-control form-control-sm">
                                <?php
                                $pc = new POSRef();
                                $_t = '<option value="%s"%s>%s</option>';
                                foreach ($pc->get_pos_ref() as $pos) {
                                    $_a = [
                                        attr($pos['code']),
                                        ($facility['pos_code'] == $pos['code']) ? " selected" : "",
                                        text($pos['code']) . ": " . text($pos['title'])
                                    ];
                                    echo vsprintf($_t, $_a);
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="domain_identifier"><?php echo xlt('CLIA Number'); ?>:</label>
                            <input type="text" class="form-control form-control-sm" name="domain_identifier" size="45" value="<?php echo attr($facility['domain_identifier']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for='federal_ein'><?php echo xlt('Tax ID'); ?>:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <select name='tax_id_type' class='form-control form-control-sm'>
                                        <option value="EI" <?php ($facility['tax_id_type'] != 'SY') ? " selected" : ""; ?>><?php echo xlt('EIN'); ?></option>
                                        <option value="SY" <?php ($facility['tax_id_type'] == 'SY') ? " selected" : ""; ?>><?php echo xlt('SSN'); ?></option>
                                    </select>
                                </div>
                                <input type='text' class='form-control form-control-sm' name='federal_ein' value="<?php echo attr($facility["federal_ein"]); ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for='facility_npi'><?php echo($GLOBALS['simplified_demographics'] ? xlt('Facility Code') : xlt('Facility NPI')); ?>:</label>
                            <input type='entry' class='form-control form-control-sm' size='20' name='facility_npi' value="<?php echo attr($facility["facility_npi"]); ?>" />
                        </div>
                    </div>
                    <div class="pl-1">
                        <div class="form-group">
                            <label for='iban'><?php echo xlt('IBAN'); ?>: </label>
                            <input type='entry' class='form-control form-control-sm' size='20' name='iban' value="<?php echo attr($facility["iban"]); ?>" />
                        </div>
                        <div class="form-group">
                            <label for='facility_taxonomy'><?php echo xlt('Facility Taxonomy'); ?>:</label>
                            <input type='entry' class='form-control form-control-sm' size='20' name='facility_taxonomy' value="<?php echo attr($facility["facility_taxonomy"]); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="attn"><?php echo xlt('Billing Attn'); ?>:</label>
                            <input type="text" class="form-control form-control-sm" name="attn" size="45" value="<?php echo attr($facility['attn']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="facility_id"><?php echo xlt('Facility ID'); ?>:</label>
                            <input type="text" class="form-control form-control-sm" name="facility_id" size="45" value="<?php echo attr($facility['facility_code']); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="oid"><?php echo xlt('OID'); ?>: </label>
                            <input type="text" class="form-control form-control-sm" size="20" name="oid" value="<?php echo attr($facility["oid"]) ?>" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-5">
                <div class="border p-2 bg-light">
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                            <input type='checkbox' class='custom-control-input' name='billing_location' id='billing_location' value='1' <?php echo ($facility['billing_location'] != 0) ? 'checked' : ''; ?> />
                            <label for='billing_location' class='custom-control-label'><?php echo xlt('Billing Location'); ?></label>
                        </div>
                    </div>
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                            <input type='checkbox' class='custom-control-input' name='accepts_assignment' id='accepts_assignment' value='1' <?php if ($facility['accepts_assignment'] == 1) {
                                echo 'checked="checked"';
                                                                                                                                            }; ?> />
                            <label for='accepts_assignment' class='custom-control-label'><?php echo xlt('Accepts Assignment'); ?></label>
                        </div>
                        <div class="col">
                            <small class="form-text text-muted mt-0">(<?php echo xlt('only if billing location'); ?>)</small>
                        </div>
                    </div>
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                            <input type='checkbox' class='custom-control-input' name='service_location' id='service_location' value='1' <?php echo ($facility['service_location'] == 1) ? 'checked' : ''; ?> />
                            <label for='service_location' class='custom-control-label'><?php echo xlt('Service Location'); ?></label>
                        </div>
                    </div>
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                            <?php
                                $disabled = '';
                                $resPBE = $facilityService->getPrimaryBusinessEntity(array("excludedId" => $my_fid));
                            if ($resPBE && ($GLOBALS['erx_enable'] ?? null)) {
                                $disabled = 'disabled';
                            }
                            ?>
                            <input type='checkbox' class='custom-control-input' name='primary_business_entity' id='primary_business_entity' value='1' <?php echo ($facility['primary_business_entity'] == 1) ? 'checked' : ''; ?> <?php if ($GLOBALS['erx_enable']) {
                                ?> onchange='return displayAlert()' <?php } ?> <?php echo $disabled; ?> />
                            <label for='primary_business_entity' class='custom-control-label'><?php echo xlt('Primary Business Entity'); ?></label>
                        </div>
                    </div>
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                            <input type='checkbox' class='custom-control-input' name='inactive' id='inactive' value='1' <?php echo ($facility['inactive'] != 0) ? 'checked' : ''; ?> />
                            <label for='inactive' class='custom-control-label'><?php echo xlt('Facility Inactive'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="border p-2 bg-light mt-2">
                    <div class="form-group">
                        <label for="info"><?php echo xlt('Info'); ?>:</label>
                        <textarea class="form-control form-control-sm" rows="5" name="info"><?php echo attr($facility["info"]) ?></textarea>
                    </div>
                </div>
                <div class="p-2">
                    <p class="text"><span class="mandatory">*</span> <?php echo xlt('Required'); ?></p>
                </div>
            </div>
        </div>
        </form>
    </div>
</body>
</html>
