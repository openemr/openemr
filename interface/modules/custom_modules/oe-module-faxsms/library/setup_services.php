<?php

/**
 * Config Module.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\BootstrapService;

$module_config = 1;

$boot = new BootstrapService();
if ($_POST['form_save'] ?? null) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $boot->saveVendorGlobals($_POST);
}

$vendors = $boot->getVendorGlobals();
?>
<!DOCTYPE HTML>
<html lang="eng">
<head>
    <title><?php echo xlt("Enable Vendors") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    if (count($vendors ?? []) === 0) {
        $boot->createVendorGlobals();
        $vendors = $boot->getVendorGlobals();
    }
    $isRCSMS = $vendors['oefax_enable_sms'] == 1 ? '1' : '0';
    $isRCFax = $vendors['oefax_enable_fax'] == 1 ? '1' : '0';
    $isEMAIL = $vendors['oe_enable_email'] == 4 ? '1' : '0';
    $setupUrl = './../setup.php';
    if ($isRCFax) {
        $setupUrl = './../setup_rc.php';
    }
    Header::setupHeader();
    ?>
    <script>
        let ServiceFax = <?php echo js_escape($isRCFax) ?>;
        let ServiceSMS = <?php echo js_escape($isRCSMS); ?>;
        let ServiceEmail = <?php echo js_escape($isEMAIL); ?>;

        function toggleSetup(id, type = 'single') {
            let url = './../setup.php';
            if (ServiceFax === '1') {
                url = './../setup_rc.php';
            }
            let dialog = $("#dialog").is(':checked');
            if (!dialog || id === 'set-service') {
                $(".frame").addClass("d-none");
                $("#" + id).toggleClass("d-none");
                return false;
            }
            if (id === 'set-fax') {
                let url = './../setup.php';
                if (ServiceSMS === '1') {
                    url = './../setup_rc.php';
                }
                let title = 'Fax Module Credentials';
                let params = {
                    buttons: [{text: 'Cancel', close: true, style: 'default btn-sm'}],
                    sizeHeight: 'full',
                    allowDrag: false,
                    type: 'iframe',
                    url: url + '?type=fax&module_config=-1'
                }
                return dlgopen('', '', 'modal-mlg', '', '', title, params);
            }
            if (id === 'set-sms') {
                let title = 'SMS Module Credentials';
                let params = {
                    buttons: [{text: 'Cancel', close: true, style: 'default btn-sm'}],
                    sizeHeight: 'full',
                    allowDrag: false,
                    type: 'iframe',
                    url: url + '?type=sms&module_config=-1'
                }
                return dlgopen('', '', 'modal-lg', '', '', title, params);
            }
            if (id === 'set-email') {
                let title = 'Email Module Credentials';
                let params = {
                    buttons: [{text: 'Cancel', close: true, style: 'default btn-sm'}],
                    sizeHeight: 'full',
                    allowDrag: false,
                    type: 'iframe',
                    url: './../setup_email.php?type=email&module_config=-1'
                }
                return dlgopen('', '', 'modal-lg', '', '', title, params);
            }
        }

        $(function () {
            const persistChange = document.querySelectorAll('.persist');
            persistChange.forEach(persist => {
                persist.addEventListener('change', (event) => {
                    $("#form_save").click();
                })
            });
        });
    </script>
</head>
<body>
    <div class="w-100 container-xl">
        <div class="form-group m-2 p-2 bg-dark">
            <button class="btn btn-outline-light" onclick="toggleSetup('set-service')"><?php echo xlt("Enable Accounts"); ?><i class="fa fa-caret"></i></button>
            <?php if (!empty($vendors['oefax_enable_sms'])) { ?>
                <button class="btn btn-outline-light" onclick="toggleSetup('set-sms')"><?php echo xlt("Setup SMS Account"); ?><span class="caret"></span></button>
            <?php }
            if (!empty($vendors['oefax_enable_fax'])) { ?>
                <button class="btn btn-outline-light" onclick="toggleSetup('set-fax')"><?php echo xlt("Setup Fax Account"); ?><span class="caret"></span></button>
            <?php } if (!empty($vendors['oe_enable_email'])) { ?>
                <button class="btn btn-outline-light" onclick="toggleSetup('set-email')"><?php echo xlt("Setup Email Account"); ?><span class="caret"></span></button>
            <?php } ?>
            <span class="checkbox text-light br-dark" title="Use Dialog or Panels">
                <label for="dialog"><?php echo xlt("Render in dialog."); ?></label>
                <input type="checkbox" class="checkbox" name="dialog" id="dialog" value="1">
            </span>
        </div>
        <!-- TODO refactor this to have vendor list a global array for future vendor additions -->
        <div class="frame col-12" id="set-service">
            <form id="set_form" name="set_form" class="form" role="form" method="post" action="">
                <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <div class="">
                    <div class="title text-center"><?php echo xlt("Available Modules"); ?></div>
                    <div class="small text-center mb-2"><span><?php echo xlt("This form auto saves."); ?></span></div>
                    <hr>
                    <div class="clearfix"></div>
                    <div class="row form-group">
                        <label for="sms_vendor" class="col-sm-6"><?php echo xlt("Enable SMS Module"); ?></label>
                        <div class="col-sm-6" title="Enable SMS Support. Remember to setup credentials.">
                            <select class="form-control persist" name="sms_vendor" id="sms_vendor">
                                <option value="0" <?php echo $vendors['oefax_enable_sms'] == '0' ? 'selected' : ''; ?>><?php echo xlt("Disabled"); ?></option>
                                <option value="1" <?php echo $vendors['oefax_enable_sms'] == '1' ? 'selected' : ''; ?>><?php echo xlt("RingCentral SMS"); ?></option>
                                <option value="2" <?php echo $vendors['oefax_enable_sms'] == '2' ? 'selected' : ''; ?>><?php echo xlt("Twilio SMS"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="fax_vendor" class="col-sm-6"><?php echo xlt("Enable Fax Module") ?></label>
                        <div class="col-sm-6" title="Enable Fax Support. Remember to setup credentials.">
                            <select class="form-control persist" name="fax_vendor" id="fax_vendor">
                                <option value="0" <?php echo $vendors['oefax_enable_fax'] == '0' ? 'selected' : ''; ?>><?php echo xlt("Disabled"); ?></option>
                                <option value="1" <?php echo $vendors['oefax_enable_fax'] == '1' ? 'selected' : ''; ?>><?php echo xlt("RingCentral Fax"); ?></option>
                                <option value="3" <?php echo $vendors['oefax_enable_fax'] == '3' ? 'selected' : ''; ?>><?php echo xlt("etherFAX"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="email_vendor" class="col-sm-6"><?php echo xlt("Enable Mail Client") ?></label>
                        <div class="col-sm-6" title="Enable Email Client Support.">
                            <select class="form-control persist" name="email_vendor" id="email_vendor">
                                <option value="0" <?php echo $vendors['oe_enable_email'] == '0' ? 'selected' : ''; ?>><?php echo xlt("Disabled"); ?></option>
                                <option value="4" <?php echo $vendors['oe_enable_email'] == '4' ? 'selected' : ''; ?>><?php echo xlt("Enabled"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="allow_dialog" class="col-sm-6"><?php echo xlt("Enable Send SMS Dialog"); ?></label>
                        <div class="col-sm-6" title="Enable Send SMS Dialog Support. Various opportunities in UI.">
                            <input type="checkbox" class="checkbox persist" name="allow_dialog" id="allow_dialog" value="1" <?php echo $vendors['oesms_send'] == '1' ? 'checked' : ''; ?>>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label for="restrict" class="col-sm-6"><?php echo xlt("Individual User Accounts"); ?></label>
                        <div class="col-sm-6" title="Restrict Users to their own account credentials. Usage accounting is tagged to username.">
                            <input type="checkbox" class="checkbox persist" name="restrict" id="restrict" value="1" <?php echo $vendors['oerestrict_users'] == '1' ? 'checked' : ''; ?>>
                        </div>
                    </div>
                    <div class="btn-group">
                        <button type="submit" id="form_save" name="form_save" class="btn btn-primary btn-save float-right d-none" value="Save"><?php echo xlt("Save"); ?></button>
                    </div>
                </div>
            </form>
        </div>
        <!-- iframes to hold setup account scripts. Dialogs replace these if requested in UI -->
        <?php if (!empty($vendors['oefax_enable_fax'])) { ?>
            <div id="set-fax" class="frame d-none">
                <h3 class="text-center"><?php echo xlt("Setup Fax Account"); ?></h3>
                <iframe src="<?php
                $setupUrl = './../setup.php';
                if ($isRCFax) {
                    $setupUrl = './../setup_rc.php';
                }
                echo attr($setupUrl . '?type=fax&module_config=1&mode=flat'); ?>" style="border:none;height:100vh;width:100%;"></iframe>
            </div>
        <?php }
        if (!empty($vendors['oefax_enable_sms'])) { ?>
            <div id="set-sms" class="frame d-none">
                <h3 class="text-center"><?php echo xlt("Setup SMS Account"); ?></h3>
                <iframe src="<?php
                $setupUrl = './../setup.php';
                if ($isRCSMS) {
                    $setupUrl = './../setup_rc.php';
                }
                echo attr($setupUrl . '?type=sms&module_config=1&mode=flat'); ?>" style="border:none;height:100vh;width:100%;"></iframe>
            </div>
        <?php } ?>
        <?php if (!empty($vendors['oe_enable_email'])) { ?>
            <div id="set-email" class="frame d-none">
                <h3 class="text-center"><?php echo xlt("Setup Email Account"); ?></h3>
                <iframe src="<?php echo attr('./../setup_email.php?type=email&module_config=1&mode=flat'); ?>" style="border:none;height:100vh;width:100%;"></iframe>
            </div>
        <?php } ?>
    </div>
</body>
</html>
