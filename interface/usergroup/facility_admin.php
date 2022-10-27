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
require_once("$srcdir/erx_javascript.inc.php");

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
    <?php Header::setupHeader(['opener']); ?>

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
        <h5 class="title"><?php echo xlt('Edit Facility'); ?></h5>
        <div class="py-3">
            <a class="btn btn-primary" name='form_save' id='form_save' onclick='submitform();' href='#'><?php echo xlt('Save'); ?></a>
            <a class="btn btn-secondary" id='cancel' onclick='dlgclose();' href='#'><?php echo xlt('Cancel'); ?></a>
        </div>

        <form name='facility-form' id="facility-form" method='post' action="facilities.php">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type=hidden name='mode' value="facility" />
            <input type=hidden name='newmode' value="admin_facility" />
            <!-- diffrentiate Admin and add post backs !-->
            <input type=hidden name=fid value="<?php echo attr($my_fid); ?>" />
            <?php $facility = $facilityService->getById($my_fid); ?>

            <div class="row">
                <div class="col-6">
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='facility' class='col-form-label col-form-label-sm'><?php echo xlt('Name'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' name='facility' size='20' value='<?php echo attr($facility['name']); ?>' />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='street' class='col-form-label col-form-label-sm'><?php echo xlt('Address'); ?>: </label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name="street" value="<?php echo attr($facility["street"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='city' class='col-form-label col-form-label-sm'><?php echo xlt('City'); ?>: </label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='city' value="<?php echo attr($facility["city"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='state' class='col-form-label col-form-label-sm'><?php echo xlt('State'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='state' value="<?php echo attr($facility["state"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='country_code' class='col-form-label col-form-label-sm'><?php echo xlt('Country'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='country_code' value="<?php echo attr($facility["country_code"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='website' class='col-form-label col-form-label-sm'><?php echo xlt('Website'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='website' value="<?php echo attr($facility["website"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='iban' class='col-form-label col-form-label-sm'><?php echo xlt('IBAN'); ?>: </label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='iban' value="<?php echo attr($facility["iban"]); ?>" />
                        </div>
                    </div>
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
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='ncolor' class='col-form-label col-form-label-sm'><?php echo xlt('Color'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' name='ncolor' id='ncolor' size='20' value="<?php echo attr($facility["color"]); ?>" />
                        </div>
                        <div class="col">
                            [<a href="javascript:void(0);" onClick="pick('pick','newcolor'); return false;" NAME="pick" ID="pick"><?php echo xlt('Pick'); ?></a>]
                        </div>
                    </div>
                    <div class="form-row my-2 d-flex align-items-center">
                        <div class="col-2">
                            <label for='pos_code' class='col-form-label col-form-label-sm'><?php echo xlt('POS Code'); ?>:</label>
                        </div>
                        <div class="col">
                            <select name="pos_code" class="form-control form-control-sm">
                                <?php
                                $pc = new POSRef();

                                foreach ($pc->get_pos_ref() as $pos) {
                                    echo "<option value=\"" . attr($pos["code"]) . "\" ";
                                    if ($facility['pos_code'] == $pos['code']) {
                                        echo "selected";
                                    }

                                    echo ">" . text($pos['code']) . ": " . text($pos['title']);
                                    echo "</option>\n";
                                }

                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row my-2 d-flex align-items-center">
                        <div class="col-2">
                            <label for="domain_identifier" class="col-form-label col-form-label-sm"><?php echo xlt('CLIA Number'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" name="domain_identifier" size="45" value="<?php echo attr($facility['domain_identifier']); ?>" />
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='phone' class='col-form-label col-form-label-sm'><?php echo xlt('Phone'); ?></label>
                        </div>
                        <div class="col">
                            <input type='text' class='form-control form-control-sm' name='phone' size='20' value='<?php echo attr($facility['phone']); ?>' />
                            <small class="form-text text-muted"><?php echo xlt('as'); ?> (000) 000-0000</small>
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='fax' class='col-form-label col-form-label-sm'><?php echo xlt('Fax'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' name="fax" size='20' value='<?php echo attr($facility['fax']); ?>' />
                            <small class="form-text text-muted"><?php echo xlt('as'); ?> (000) 000-0000</small>
                        </div>
                    </div>
                    <div class="form-row my-2 d-flex align-items-center">
                        <div class="col-2">
                            <label for='postal_code' class='col-form-label col-form-label-sm'><?php echo xlt('Zip Code'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='postal_code' value="<?php echo attr($facility["postal_code"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <?php
                            $ssn = '';
                            $ein = '';
                        if ($facility['tax_id_type'] == 'SY') {
                            $ssn = 'selected';
                        } else {
                            $ein = 'selected';
                        }
                        ?>
                        <div class="col-2">
                            <label for='federal_ein' class='col-form-label col-form-label-sm'><?php echo xlt('Tax ID'); ?>:</label>
                        </div>
                        <div class="col-3">
                            <select name='tax_id_type' class='form-control form-control-sm'>
                                <option value="EI" <?php echo $ein; ?>><?php echo xlt('EIN'); ?></option>
                                <option value="SY" <?php echo $ssn; ?>><?php echo xlt('SSN'); ?></option>
                            </select>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='11' name='federal_ein' value="<?php echo attr($facility["federal_ein"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2 d-flex align-items-center">
                        <div class="col-2">
                            <label for='facility_npi' class='col-form-label col-form-label-sm'><?php echo($GLOBALS['simplified_demographics'] ? xlt('Facility Code') : xlt('Facility NPI')); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='facility_npi' value="<?php echo attr($facility["facility_npi"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2 d-flex align-items-center">
                        <div class="col-2">
                            <label for='facility_taxonomy' class='col-form-label col-form-label-sm'><?php echo xlt('Facility Taxonomy'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='facility_taxonomy' value="<?php echo attr($facility["facility_taxonomy"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for='email' class='col-form-label col-form-label-sm'><?php echo xlt('Email'); ?>: </label>
                        </div>
                        <div class="col">
                            <input type='entry' class='form-control form-control-sm' size='20' name='email' value="<?php echo attr($facility["email"]); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2 d-flex align-items-center">
                        <div class="col-2">
                            <label for="attn" class="col-form-label col-form-label-sm"><?php echo xlt('Billing Attn'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" name="attn" size="45" value="<?php echo attr($facility['attn']); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2 d-flex align-items-center">
                        <div class="col-2">
                            <label for="facility_id" class="col-form-label col-form-label-sm"><?php echo xlt('Facility ID'); ?>:</label>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" name="facility_id" size="45" value="<?php echo attr($facility['facility_code']); ?>" />
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-2">
                            <label for="oid" class="col-form-label col-form-label-sm"><?php echo xlt('OID'); ?>: </label>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm" size="20" name="oid" value="<?php echo attr($facility["oid"]) ?>" />
                        </div>
                    </div>
                </div>
            </div>

            <hr />

            <div class="form-row my-2">
                <div class="col-2">
                    <label for="mail_street" class="col-form-label col-form-label-sm"><?php echo xlt('Mailing Address'); ?>:</label>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" size="20" name="mail_street" value="<?php echo attr($facility["mail_street"]) ?>" />
                </div>
            </div>

            <div class="form-row my-2">
                <div class="col-2">
                    <label for="mail_street2" class="col-form-label col-form-label-sm"><?php echo xlt('Suite'); ?>: </label>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" size="20" name="mail_street2" value="<?php echo attr($facility["mail_street2"]) ?>" />
                </div>
            </div>

            <div class="form-row my-2">
                <div class="col-2">
                    <label for="mail_city" class="col-form-label col-form-label-sm"><?php echo xlt('City'); ?>: </label>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" size="20" name="mail_city" value="<?php echo attr($facility["mail_city"]) ?>" />
                </div>
            </div>

            <div class="form-row my-2">
                <div class="col-2">
                    <label for="mail_state" class="col-form-label col-form-label-sm"><?php echo xlt('State'); ?>: </label>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" size="20" name="mail_state" value="<?php echo attr($facility["mail_state"]) ?>" />
                </div>
            </div>

            <div class="form-row my-2">
                <div class="col-2">
                    <label for="mail_zip" class="col-form-label col-form-label-sm"><?php echo xlt('Zip'); ?>:</label>
                </div>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" size="20" name="mail_zip" value="<?php echo attr($facility["mail_zip"]) ?>" />
                </div>
            </div>

            <div class="form-row my-2">
                <div class="col-2">
                    <label for="info" class="col-form-label col-form-label-sm"><?php echo xlt('Info'); ?>:</label>
                </div>
                <div class="col">
                    <textarea class="form-control form-control-sm" size="20" name="info"><?php echo attr($facility["info"]) ?></textarea>
                </div>
            </div>

            <p class="text"><span class="mandatory">*</span> <?php echo xlt('Required'); ?></p>
        </form>
        <div class="py-3">
            <a class="btn btn-primary" name='form_save' id='form_save' onclick='submitform();' href='#'><?php echo xlt('Save'); ?></a>
            <a class="btn btn-secondary" id='cancel' onclick='dlgclose();' href='#'><?php echo xlt('Cancel'); ?></a>
        </div>
    </div>
</body>
</html>
