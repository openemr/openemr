<?php

/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2024-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . "/../../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Modules\Dorn\ConnectorApi;
use OpenEMR\Modules\Dorn\models\CustomerPrimaryInfoView;

if (!empty($_REQUEST)) {
    if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit/Add Procedure Provider")]);
    exit;
}

if (!empty($_POST)) {
    if (isset($_POST['SubmitButton'])) { //check if form was submitted
        $saveData = CustomerPrimaryInfoView::loadByPost($_POST);
        $response = ConnectorApi::savePrimaryInfo($saveData);
        $npi = $_POST["form_npi"];
        if ($response !== true) {
            echo "<span class='alert alert-danger mx-3'>" . xlt("Error saving primary information: ") . text($response->message) . "</span>";
        } else {
            echo "<span class='alert alert-success mx-3'>" . xlt("Primary information saved successfully") . "</span>";
        }
    }
} else {
    $npi = $_REQUEST['npi'] ?? "";
}

if ($npi) {
    $data = ConnectorApi::getPrimaryInfoByNpi($npi);
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['opener']); ?>
    <title><?php echo xlt("Primary Config Edit"); ?></title>
    <style>
      .required-field {
        color: red;
      }
    </style>
</head>
<body>
    <div class="container-fluid">
        <form class="form" method='post' name='theform' action="primary_config_edit.php?npi=<?php echo attr_url($data->npi); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_primaryId"><?php echo xlt('Primary ID'); ?>:</label>
                        </div>
                        <input type='text' readonly name='form_primaryId' id='form_primaryId' value='<?php echo attr($data->primaryId ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_npi"><?php echo xlt('NPI'); ?><span class="required-field"> *</span>:</label>
                        </div>
                        <input type='text' required name='form_npi' id='form_npi' maxlength='10' value='<?php echo attr($data->npi ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_name"><?php echo xlt('Name'); ?><span class="required-field"> *</span>:</label>
                        </div>
                        <input type='text' required name='form_name' id='form_name' value='<?php echo attr($data->primaryName ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_phone"><?php echo xlt('Phone'); ?><span class="required-field"> *</span>:</label>
                        </div>
                        <input type='text' required name='form_phone' id='form_phone' value='<?php echo attr($data->primaryPhone ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_email"><?php echo xlt('Email'); ?><span class="required-field"> *</span>:</label>
                        </div>
                        <input type='text' required name='form_email' id='form_email' value='<?php echo attr($data->primaryEmail ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_address1"><?php echo xlt('Address 1'); ?><span class="required-field"> *</span>:</label>
                        </div>
                        <input type='text' required name='form_address1' id='form_address1' value='<?php echo attr($data->primaryAddress1 ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_address2"><?php echo xlt('Address 2'); ?>:</label>
                        </div>
                        <input type='text' name='form_address2' id='form_address2' value='<?php echo attr($data->primaryAddress2 ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_city"><?php echo xlt('City'); ?><span class="required-field"> *</span>:</label>
                        </div>
                        <input type='text' required name='form_city' id='form_city' value='<?php echo attr($data->primaryCity ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_state"><?php echo xlt('State'); ?><span class="required-field"> *</span>:</label>
                        </div>
                        <input type='text' required name='form_state' id='form_state' value='<?php echo attr($data->primaryState ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <div class="clearfix">
                        <div class="label-div">
                            <label class="col-form-label" for="form_zip"><?php echo xlt('Zip Code'); ?><span class="required-field"> *</span>:</label>
                        </div>
                        <input type='text' required name='form_zip' id='form_zip' value='<?php echo attr($data->primaryZipCode ?? ''); ?>' class='form-control' />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <button type="submit" name="SubmitButton" class="btn btn-primary my-2"><?php echo xlt("Save") ?></button>
                </div>
            </div>
        </form>
    </div>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = document.querySelectorAll('input[required], select[required]');
            let hasErrors = false;

            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    hasErrors = true;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (hasErrors) {
                e.preventDefault();
                alert('<?php echo xlt("Please fill in all required fields") ?>');
            }
        });
    </script>
</body>
</html>
