<?php

/**
 * facilities_add.php
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

$alertmsg = '';
?>
<html>
<head>
    <?php Header::setupHeader(['opener']); ?>
    <script src="../main/calendar/modules/PostCalendar/pnincludes/AnchorPosition.js"></script>
    <script src="../main/calendar/modules/PostCalendar/pnincludes/PopupWindow.js"></script>
    <script src="../main/calendar/modules/PostCalendar/pnincludes/ColorPicker2.js"></script>

<!-- validation library -->
<!--//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation-->
<?php
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php");
//Gets validation rules from Page Validation list.
//Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
$collectthis = collectValidationPageRules("/interface/usergroup/facilities_add.php");
if (empty($collectthis)) {
    $collectthis = "undefined";
} else {
    $collectthis = json_sanitize($collectthis["facility-add"]["rules"]);
}

// Old Browser comp trigger on js

if (isset($_POST["mode"]) && $_POST["mode"] == "facility") {
    echo '
<script>
<!--
dlgclose();
//-->
</script>

	';
}
?>
<script>
/// todo, move this to a common library

var collectvalidation = <?php echo $collectthis; ?>;

function submitform() {

    var valid = submitme(1, undefined, 'facility-add', collectvalidation);
    if (!valid) return;

    <?php if ($GLOBALS['erx_enable']) { ?>
    alertMsg='';
    f=document.forms[0];
    for(i=0;i<f.length;i++){
        if(f[i].type=='text' && f[i].value)
        {
            if(f[i].name == 'facility' || f[i].name == 'Washington')
            {
                alertMsg += checkLength(f[i].name,f[i].value,35);
                alertMsg += checkFacilityName(f[i].name,f[i].value);
            }
            else if(f[i].name == 'street')
            {
                alertMsg += checkLength(f[i].name,f[i].value,35);
                alertMsg += checkAlphaNumeric(f[i].name,f[i].value);
            }
            else if(f[i].name == 'phone' || f[i].name == 'fax')
            {
                alertMsg += checkPhone(f[i].name,f[i].value);
            }
            else if(f[i].name == 'federal_ein')
            {
                alertMsg += checkLength(f[i].name,f[i].value,10);
                alertMsg += checkFederalEin(f[i].name,f[i].value);
            }
        }
    }
    if(alertMsg)
    {
        alert(alertMsg);
        return false;
    }
    <?php } ?>

    top.restoreSession();

    let post_url = $("#facility-add").attr("action");
    let request_method = $("#facility-add").attr("method");
    let form_data = $("#facility-add").serialize();

    $.ajax({
        url: post_url,
        type: request_method,
        data: form_data
    }).done(function (r) { //
        dlgclose('refreshme', false);
    });
    return false;
}

function toggle( target, div ) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "collapse" ) {
        $(target).find(".indicator").text( "expand" );
        $(div).hide();
    } else {
        $(target).find(".indicator").text( "collapse" );
        $(div).show();
    }

}

$(function () {
    $("#dem_view").click(function() {
        toggle($(this), "#DEM");
    });

});

$(function () {
    /**
     * add required/star sign to required form fields
     */
    for (var prop in collectvalidation) {
        //if (collectvalidation[prop].requiredSign)
        if (collectvalidation[prop].presence) {
            $("label[for='" + prop + "']").append('*');
        }
    }
});
var cp = new ColorPicker('window');
  // Runs when a color is clicked
function pickColor(color) {
    document.getElementById('ncolor').value = color;
}
var field;
function pick(anchorname,target) {
    var cp = new ColorPicker('window');
    field=target;
        cp.show(anchorname);
}
function displayAlert() {
    if(document.getElementById('primary_business_entity').checked==false) {
        alert(<?php echo xlj('Primary Business Entity tax id is used as account id for NewCrop ePrescription. Changing the facility will affect the working in NewCrop.'); ?>);
    } else if(document.getElementById('primary_business_entity').checked==true) {
        alert(<?php echo xlj('Once the Primary Business Facility is set, it should not be changed. Changing the facility will affect the working in NewCrop ePrescription.'); ?>);
    }
}
</script>

</head>
<body class="body_top">
    <div class="container">
        <h5 class="title"><?php echo xlt('Add Facility'); ?></h5>
        <div class="py-3">
            <div class="btn-group">
                <button class="btn btn-primary btn-save" name='form_save' id='form_save' onclick="submitform();"><?php echo xlt('Save'); ?></button>
                <button class="btn btn-secondary btn-cancel" onclick="dlgclose();"><?php echo xlt('Cancel'); ?></button>
            </div>
        </div>

        <form name='facility-add' id='facility-add' method='post' action="facilities.php">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="hidden" name="mode" value="facility" />

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="facility"><?php echo xlt('Name'); ?>:</label>
                        <input class="form-control" type="text" name="facility" size="20" value="" required />
                    </div>
                    <div class="form-group">
                        <label for="street"><?php echo xlt('Address'); ?>:</label>
                        <input class="form-control" type="text" size="20" name="street" value="" />
                    </div>
                    <div class="form-group">
                        <label for="city"><?php echo xlt('City'); ?>:</label>
                        <input class="form-control" type="text" size="20" name="city" value="" />
                    </div>
                    <div class="form-group">
                        <label for="state"><?php echo xlt('State'); ?>:</label>
                        <input class="form-control" type="text" size="20" name="state" value="" />
                    </div>
                    <div class="form-group">
                        <label for="country_code"><?php echo xlt('Country'); ?>:</label>
                        <input class="form-control" type="text" size="20" name="country_code" value="" />
                    </div>
                    <div class="form-group">
                        <label for="website"><?php echo xlt('Website'); ?>: </label>
                        <input class="form-control" type="text" size="20" name="website" value="" />
                    </div>
                    <div class="form-group">
                        <label for="iban"><?php echo xlt('IBAN'); ?>: </label>
                        <input class="form-control" type="text" size="20" name="iban" value="" />
                    </div>
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                            <input type="checkbox" class='custom-control-input' name="billing_location" id="billing_location" value="1" />
                            <label for="billing_location" class='custom-control-label'><?php echo xlt('Billing Location'); ?></label>
                        </div>
                    </div>
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                            <input type="checkbox" class='custom-control-input' name="accepts_assignment" id="accepts_assignment" value="1" aria-describedby="assignmentHelp">
                            <label for="accepts_assignment" class='custom-control-label'><?php echo xlt('Accepts Assignment'); ?></label>
                        </div>
                        <div class="col">
                            <small id="assignmentHelp" class="text-muted">
                                (<?php echo xlt('only if billing location'); ?>)
                            </small>
                        </div>
                    </div>
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                            <input type="checkbox" class='custom-control-input' name="service_location" id="service_location" value="1" />
                            <label for="service_location" class='custom-control-label'><?php echo xlt('Service Location'); ?></label>
                        </div>
                    </div>
                    <div class="form-row custom-control custom-switch my-2">
                        <div class="col">
                        <?php
                        $disabled = '';
                        $resPBE = $facilityService->getPrimaryBusinessEntity(array("excludedId" => ($my_fid ?? null)));
                        if (!empty($resPBE) && sizeof($resPBE) > 0) {
                            $disabled = 'disabled';
                        }
                        ?>
                        <input type='checkbox' class='custom-control-input' name='primary_business_entity' id='primary_business_entity' value='1' <?php echo (!empty($facility['primary_business_entity']) && ($facility['primary_business_entity'] == 1)) ? 'checked' : ''; ?>
                                        <?php if ($GLOBALS['erx_enable']) { ?>
                                            onchange='return displayAlert()'
                                        <?php } ?> <?php echo $disabled;?>>
                        <label for="primary_business_entity" class='custom-control-label'><?php echo xlt('Primary Business Entity'); ?></label>

                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ncolor"><?php echo xlt('Color'); ?>: </label>
                        <input class="form-control" type="text" name="ncolor" id="ncolor" size="20" value="" />
                        <span>[<a href="javascript:void(0);" onClick="pick('pick','newcolor');return false;" NAME="pick" ID="pick"><?php echo xlt('Pick'); ?></a>]</span>
                    </div>
                    <div class="form-group">
                        <label for="pos_code"><?php echo xlt('POS Code'); ?>: </label>
                        <select class="form-control" name="pos_code">
                        <?php
                        $pc = new POSRef();

                        foreach ($pc->get_pos_ref() as $pos) {
                            echo "<option value=\"" . attr($pos["code"]) . "\" ";
                            echo ">" . text($pos['code'])  . ": " . text($pos['title']);
                            echo "</option>\n";
                        }

                        ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="domain_identifier"><?php echo xlt('CLIA Number'); ?>:</label>
                        <input class="form-control" type="text" name="domain_identifier" size="45" />
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="phone"><?php echo xlt('Phone'); ?>:</label>
                        <input class="form-control" type="text" name="phone" size="20" value="" />
                    </div>
                    <div class="form-group">
                        <label for="fax"><?php echo xlt('Fax'); ?>:</label>
                        <input class="form-control" type="text" name="fax" size="20" value="" />
                    </div>
                    <div class="form-group">
                        <label for="postal_code"><?php echo xlt('Zip Code'); ?>:</label>
                        <input class="form-control" type="text" size="20" name="postal_code" value="" />
                    </div>
                    <div class="form-group">
                        <label for="tax_id_type"><?php echo xlt('Tax ID'); ?>:</label>
                        <span class="form-inline">
                            <select class="form-control" name="tax_id_type">
                                <option value="EI"><?php echo xlt('EIN'); ?></option>
                                <option value="SY"><?php echo xlt('SSN'); ?></option>
                            </select>
                            <input class="form-control" type="text" size="11" name="federal_ein" value="" />
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="facility_npi"><?php echo ($GLOBALS['simplified_demographics'] ? xlt('Facility Code') : xlt('Facility NPI')); ?>:</label>
                        <input class="form-control" type="text" size="20" name="facility_npi" value="" />
                    </div>
                    <div class="form-group">
                        <label for="facility_taxonomy"><?php echo xlt('Facility Taxonomy'); ?>:</label>
                        <input class="form-control" type="text" size="20" name="facility_taxonomy" value="" />
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo xlt('Email'); ?>: </label>
                        <input class="form-control" type="text" size="20" name="email" value="" />
                    </div>
                    <div class="form-group">
                        <label for="attn"><?php echo xlt('Billing Attn'); ?>:</label>
                        <input class="form-control" type="text" name="attn" size="45" />
                    </div>
                    <div class="form-group">
                        <label for="facility_id"><?php echo xlt('Facility ID'); ?>:</label>
                        <input class="form-control" type="text" name="facility_id" size="20" />
                    </div>
                    <div class="form-group">
                        <label for="oid"><?php echo xlt('OID'); ?>: </label>
                        <input class="form-control" type="text" size="20" name="oid" value="<?php echo attr($facility["oid"] ?? '') ?>" />
                    </div>

                </div>
            </div>
            <hr />
            <div class="form-group">
                <label for="mail_stret"><?php echo xlt('Mailing Address'); ?>: </label>
                <input class="form-control" type="text" size="20" name="mail_street" value="<?php echo attr($facility["mail_street"] ?? '') ?>" />
            </div>
            <div class="form-group">
                <label for="mail_street2"><?php echo xlt('Dept'); ?>: </label>
                <input class="form-control" type="text" size="20" name="mail_street2" value="<?php echo attr($facility["mail_street2"] ?? '') ?>" />
            </div>
            <div class="form-group">
                <label for="mail_city"><?php echo xlt('City'); ?>: </label>
                <input class="form-control" type="text" size="20" name="mail_city" value="<?php echo attr($facility["mail_city"] ?? '') ?>" />
            </div>
            <div class="form-group">
                <label for="mail_state"><?php echo xlt('State'); ?>: </label>
                <input class="form-control" type="text" size="20" name="mail_state" value="<?php echo attr($facility["mail_state"] ?? '') ?>" />
            </div>
            <div class="form-group">
                <label for="mail_zip"><?php echo xlt('Zip'); ?>: </label>
                <input class="form-control" type="text" size="20" name="mail_zip" value="<?php echo attr($facility["mail_zip"] ?? '') ?>" />
            </div>
            <div class="form-group">
                <label for="info"><?php echo xlt('Info'); ?>: </label>
                <textarea class="form-control" size="20" name="info" ><?php echo attr($facility["info"] ?? '') ?></textarea>
            </div>
            <p class="text"><span class="mandatory">*</span> <?php echo xlt('Required'); ?></p>
        </form>
        <div class="py-3">
            <div class="btn-group">
                <button class="btn btn-primary btn-save" name='form_save' id='form_save' onclick="submitform();"><?php echo xlt('Save'); ?></button>
                <button class="btn btn-secondary btn-cancel" onclick="dlgclose();"><?php echo xlt('Cancel'); ?></button>
            </div>
        </div>
    </div>
    <script>
    <?php
    if ($alertmsg = trim($alertmsg)) {
        echo "alert(" . js_escape($alertmsg) . ");\n";
    }
    ?>
    </script>

</body>
</html>
