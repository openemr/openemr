<?php
/**
 * facility_admin.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
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
    <?php Header::setupHeader(['opener', 'jquery-ui']); ?>

    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/AnchorPosition.js"></script>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/PopupWindow.js"></script>
    <script type="text/javascript" src="../main/calendar/modules/PostCalendar/pnincludes/ColorPicker2.js"></script>

    <!-- validation library -->
    <!--//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation-->
    <?php    $use_validate_js = 1;?>
    <?php  require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>
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

    <script type="text/javascript">

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
                    }
                    else if (f[i].name == 'street') {
                        alertMsg += checkLength(f[i].name, f[i].value, 35);
                        alertMsg += checkAlphaNumeric(f[i].name, f[i].value);
                    }
                    else if (f[i].name == 'phone' || f[i].name == 'fax') {
                        alertMsg += checkPhone(f[i].name, f[i].value);
                    }
                    else if (f[i].name == 'federal_ein') {
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

        $(function(){
            $("#cancel").click(function() {
                dlgclose();
            });

            /**
             * add required/star sign to required form fields
             */
            for (var prop in collectvalidation) {
                //if (collectvalidation[prop].requiredSign)
                if (collectvalidation[prop].presence)
                    jQuery("input[name='" + prop + "']").after('*');
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
        function displayAlert()
        {
            if(document.getElementById('primary_business_entity').checked==false)
                alert(<?php echo xlj('Primary Business Entity tax id is used as the account id for NewCrop ePrescription.'); ?>);
            else if(document.getElementById('primary_business_entity').checked==true)
                alert(<?php echo xlj('Once the Primary Business Facility is set, changing the facility id will affect NewCrop ePrescription.'); ?>);
        }
    </script>

</head>
<body class="body_top" style="width:600px;height:330px !important;">

<table>
    <tr>
        <td>
            <span class="title"><?php echo xlt('Edit Facility'); ?></span>&nbsp;&nbsp;&nbsp;</td><td>
            <a class="css_button large_button" name='form_save' id='form_save' onclick='submitform()' href='#' >
                <span class='css_button_span large_button_span'><?php echo xlt('Save');?></span>
            </a>
            <a class="css_button large_button" id='cancel' href='#'>
                <span class='css_button_span large_button_span'><?php echo xlt('Cancel');?></span>
            </a>
        </td>
    </tr>
</table>

<form name='facility-form' id="facility-form" method='post' action="facilities.php">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <input type=hidden name=mode value="facility">
    <input type=hidden name=newmode value="admin_facility"> <!--    Diffrentiate Admin and add post backs -->
    <input type=hidden name=fid value="<?php echo attr($my_fid); ?>">
    <?php $facility = $facilityService->getById($my_fid); ?>

    <table border=0 cellpadding=0 cellspacing=1 style="width:630px;">
        <tr>
            <td width='150px'><span class='text'><?php echo xlt('Name'); ?>: </span></td>
            <td width='220px'><input type='entry' name='facility' size='20' value='<?php echo attr($facility['name']); ?>'></td>
            <td width='200px'><span class='text'><?php echo xlt('Phone'); ?> <?php echo xlt('as'); ?> (000) 000-0000:</span></td>
            <td width='220px'><input type='entry' name='phone' size='20' value='<?php echo attr($facility['phone']); ?>'></td>
        </tr>
        <tr>
            <td><span class=text><?php echo xlt('Address'); ?>: </span></td><td><input type=entry size=20 name="street" value="<?php echo attr($facility["street"]); ?>"></td>
            <td><span class='text'><?php echo xlt('Fax'); ?> <?php echo xlt('as'); ?> (000) 000-0000:</span></td>
            <td><input type='entry' name="fax" size='20' value='<?php echo attr($facility['fax']); ?>'></td>
        </tr>
        <tr>

            <td><span class=text><?php echo xlt('City'); ?>: </span></td>
            <td><input type=entry size=20 name=city value="<?php echo attr($facility["city"]); ?>"></td>
            <td><span class=text><?php echo xlt('Zip Code'); ?>: </span></td><td><input type=entry size=20 name=postal_code value="<?php echo attr($facility["postal_code"]); ?>"></td>
        </tr>
        <?php
        $ssn='';
        $ein='';
        if ($facility['tax_id_type']=='SY') {
            $ssn='selected';
        } else {
            $ein='selected';
        }
        ?>
        <tr>
            <td><span class=text><?php echo xlt('State'); ?>: </span></td><td><input type=entry size=20 name=state value="<?php echo attr($facility["state"]); ?>"></td>
            <td><span class=text><?php echo xlt('Tax ID'); ?>: </span></td><td><select name=tax_id_type><option value="EI" <?php echo $ein;?>><?php echo xlt('EIN'); ?></option><option value="SY" <?php echo $ssn;?>><?php echo xlt('SSN'); ?></option></select><input type=entry size=11 name=federal_ein value="<?php echo attr($facility["federal_ein"]); ?>"></td>
        </tr>
        <tr>
            <td><span class=text><?php echo xlt('Country'); ?>: </span></td><td><input type=entry size=20 name=country_code value="<?php echo attr($facility["country_code"]); ?>"></td>
            <td width="21"><span class=text><?php echo ($GLOBALS['simplified_demographics'] ? xlt('Facility Code') : xlt('Facility NPI')); ?>:
          </span></td><td><input type=entry size=20 name=facility_npi value="<?php echo attr($facility["facility_npi"]); ?>"></td>
        </tr>
        <tr>
            <td>&nbsp;</td><td>&nbsp;</td><td><span class=text><?php echo xlt('Facility Taxonomy'); ?>:</span></td>
            <td><input type=entry size=20 name=facility_taxonomy value="<?php echo attr($facility["facility_taxonomy"]); ?>"></td>
        </tr>


        <tr>
        <td><span class=text><?php echo xlt('Website'); ?>: </span></td><td><input type=entry size=20 name=website value="<?php echo attr($facility["website"]); ?>"></td>
            <td><span class=text><?php echo xlt('Email'); ?>: </span></td><td><input type=entry size=20 name=email value="<?php echo attr($facility["email"]); ?>"></td>
        </tr>

        <tr>
            <td><span class='text'><?php echo xlt('Billing Location'); ?>: </span></td>
            <td><input type='checkbox' name='billing_location' value='1' <?php echo ($facility['billing_location'] != 0) ? 'checked' : ''; ?>></td>
            <td rowspan='2'><span class='text'><?php echo xlt('Accepts Assignment'); ?><br>(<?php echo xlt('only if billing location'); ?>): </span></td>
            <td><input type='checkbox' name='accepts_assignment' value='1' <?php echo ($facility['accepts_assignment'] == 1) ? 'checked' : ''; ?>></td>
        </tr>
        <tr>
            <td><span class='text'><?php echo xlt('Service Location'); ?>: </span></td>
            <td><input type='checkbox' name='service_location' value='1' <?php echo ($facility['service_location'] == 1) ? 'checked' : ''; ?>></td>
            <td>&nbsp;</td>
        </tr>
        <?php
        $disabled='';
        $resPBE = $facilityService->getPrimaryBusinessEntity(array("excludedId" => $my_fid));
        if ($resPBE) {
            $disabled='disabled';
        }
        ?>
        <tr>
            <td><span class='text'><?php echo xlt('Primary Business Entity'); ?>: </span></td>
            <td><input type='checkbox' name='primary_business_entity' id='primary_business_entity' value='1' <?php echo ($facility['primary_business_entity'] == 1) ? 'checked' : ''; ?>
                <?php if ($GLOBALS['erx_enable']) { ?>
                    onchange='return displayAlert()'
                <?php } ?> <?php echo $disabled;?>></td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td><span class='text'><?php echo xlt('Color'); ?>: </span></td> <td><input type=entry name=ncolor id=ncolor size=20 value="<?php echo attr($facility["color"]); ?>"></td>
            <td>[<a href="javascript:void(0);" onClick="pick('pick','newcolor');return false;" NAME="pick" ID="pick"><?php echo xlt('Pick'); ?></a>]</td><td>&nbsp;</td>

        <tr>
            <td><span class=text><?php echo xlt('POS Code'); ?>: </span></td>
            <td colspan="6">
                <select name="pos_code">
                    <?php
                    $pc = new POSRef();

                    foreach ($pc->get_pos_ref() as $pos) {
                        echo "<option value=\"" . attr($pos["code"]) . "\" ";
                        if ($facility['pos_code'] == $pos['code']) {
                            echo "selected";
                        }

                        echo ">" . text($pos['code'])  . ": ". text($pos['title']);
                        echo "</option>\n";
                    }

                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><span class="text"><?php echo xlt('Billing Attn'); ?>:</span></td>
            <td colspan="4"><input type="entry" name="attn" size="45" value="<?php echo attr($facility['attn']); ?>"></td>
        </tr>
        <tr>
            <td><span class="text"><?php echo xlt('CLIA Number'); ?>:</span></td>
            <td colspan="4"><input type="entry" name="domain_identifier" size="45" value="<?php echo attr($facility['domain_identifier']); ?>"></td>
        </tr>
        <tr>
            <td><span class="text"><?php echo xlt('Facility ID'); ?>:</span></td>
            <td colspan="4"><input type="entry" name="facility_id" size="45" value="<?php echo attr($facility['facility_code']); ?>"></td>
        </tr>
        <tr>
            <td>
                <span class="text"><?php echo xlt('OID'); ?>: </span>
            </td>
            <td>
                <input type="entry" size="20" name="oid" value="<?php echo attr($facility["oid"]) ?>">
            </td>
        </tr>
        <tr>
            <td>
                <hr>
            </td>
        </tr>
        <tr>
            <td>
                <label><?php echo xlt('Mailing Address'); ?>: </label>
            </td>
            <td>
                <input type="entry" size="20" name="mail_street" value="<?php echo attr($facility["mail_street"]) ?>">
            </td>
        </tr>

        <tr>
            <td>
                <label><?php echo xlt('Suite'); ?>: </label>
            </td>
            <td>
                <input type="entry" size="20" name="mail_street2" value="<?php echo attr($facility["mail_street2"]) ?>">
            </td>
        </tr>

        <tr>
            <td>
                <label><?php echo xlt('City'); ?>: </label>
            </td>
            <td>
                <input type="entry" size="20" name="mail_city" value="<?php echo attr($facility["mail_city"]) ?>">
            </td>
        </tr>
        <tr>
            <td>
                <label><?php echo xlt('State'); ?>: </label>
            </td>
            <td>
                <input type="entry" size="20" name="mail_state" value="<?php echo attr($facility["mail_state"]) ?>">
            </td>
        </tr>
        <tr>
            <td>
                <label><?php echo xlt('Zip'); ?>: </label>
            </td>
            <td>
                <input type="entry" size="20" name="mail_zip" value="<?php echo attr($facility["mail_zip"]) ?>">
            </td>
        </tr>
        <tr height="20" valign="bottom">
            <td colspan=2><span class="text"><font class="mandatory">*</font> <?php echo xlt('Required'); ?></span></td>
        </tr>
    </table>
</form>

</body>
</html>
