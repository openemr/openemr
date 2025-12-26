<?php

/**
 * Config Module.
 *
 * @package   OpenEMR Module
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\BootstrapService;
use OpenEMR\Modules\FaxSMS\Controller\NotificationTaskManager;

$module_config = 1;

$boot = new BootstrapService();
$taskManager = new NotificationTaskManager();
$services = ['sms', 'email'];
$actions = ['create', 'enable', 'disable', 'delete'];

if (($_POST['action'] ?? null) || ($_POST['selected_service'] ?? null)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $selectedService = $_POST['selected_service'] ?? null;
    $selectedAction = $_POST['action'] ?? null;
    $status = $taskManager->getServiceStatus($selectedService);

    $period = $_POST['period'] ?? null;
    if (empty($period)) {
        $period = $taskManager->getTaskHours($selectedService);
    }

    if ($selectedService && $selectedAction) {
        if ($selectedAction === 'create') {
            if ($period) {
                $taskManager->manageService($selectedService, $period);
            }
        }
        switch ($selectedAction) {
            case 'create':
                break;
            case 'enable':
                $taskManager->enableService($selectedService, $period);
                break;
            case 'disable':
                $taskManager->disableService($selectedService);
                break;
            case 'delete':
                $taskManager->deleteService($selectedService);
                break;
            default:
                throw new Exception("Invalid action selected.");
        }
    }
}

$currentStatus = $selectedService ? $taskManager->getServiceStatus($selectedService) : null;

if ($_POST['form_save'] ?? null) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $_SESSION['editingUser'] = ($_POST['editingUser'] ?? 0);
    $boot->saveVendorGlobals($_POST);
}

// Handle user permissions form submission
if ($_POST['form_save_permissions'] ?? null) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    // Get all active users
    $users_query = "SELECT id, username FROM users WHERE active = 1 AND username IS NOT NULL AND fname IS NOT NULL";
    $users_result = sqlStatement($users_query);

    $services = ['fax', 'sms', 'email', 'voice'];
    $primary_user_id = $_POST['primary_user'] ?? null;

    // Handle primary user reset (when value is "0" or empty)
    $reset_primary = (in_array($primary_user_id, ['0', '', null], true));

    while ($user = sqlFetchArray($users_result)) {
        $user_id = $user['id'];

        // Handle service permissions
        foreach ($services as $service) {
            $permission_value = $_POST["user_{$user_id}_{$service}"] ?? '0';
            $setting_label = "module_faxsms_{$service}_permission";

            // Check if setting already exists
            $existing_query = "SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = ?";
            $existing_result = sqlQuery($existing_query, [$user_id, $setting_label]);

            if ($existing_result) {
                // Update existing setting
                $update_query = "UPDATE user_settings SET setting_value = ? WHERE setting_user = ? AND setting_label = ?";
                sqlStatement($update_query, [$permission_value, $user_id, $setting_label]);
            } else {
                // Insert new setting
                $insert_query = "INSERT INTO user_settings (setting_user, setting_label, setting_value) VALUES (?, ?, ?)";
                sqlStatement($insert_query, [$user_id, $setting_label, $permission_value]);
            }
        }

        // Handle Use Primary checkbox
        $use_primary_value = $_POST["user_{$user_id}_use_primary"] ?? '0';
        $use_primary_label = "module_faxsms_use_primary";

        $existing_primary_query = "SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = ?";
        $existing_primary_result = sqlQuery($existing_primary_query, [$user_id, $use_primary_label]);

        if ($existing_primary_result) {
            $update_primary_query = "UPDATE user_settings SET setting_value = ? WHERE setting_user = ? AND setting_label = ?";
            sqlStatement($update_primary_query, [$use_primary_value, $user_id, $use_primary_label]);
        } else {
            $insert_primary_query = "INSERT INTO user_settings (setting_user, setting_label, setting_value) VALUES (?, ?, ?)";
            sqlStatement($insert_primary_query, [$user_id, $use_primary_label, $use_primary_value]);
        }

        // Handle Primary user designation with reset capability
        $primary_label = "module_faxsms_primary_user";

        if ($reset_primary) {
            // Reset: Set all users to '0' (no primary user)
            $is_primary_value = '0';
        } else {
            // Normal operation: Set selected user to '1', others to '0'
            $is_primary_value = ($primary_user_id == $user_id) ? '1' : '0';
        }

        $existing_primary_user_query = "SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = ?";
        $existing_primary_user_result = sqlQuery($existing_primary_user_query, [$user_id, $primary_label]);

        if ($existing_primary_user_result) {
            $update_primary_user_query = "UPDATE user_settings SET setting_value = ? WHERE setting_user = ? AND setting_label = ?";
            sqlStatement($update_primary_user_query, [$is_primary_value, $user_id, $primary_label]);
        } else {
            $insert_primary_user_query = "INSERT INTO user_settings (setting_user, setting_label, setting_value) VALUES (?, ?, ?)";
            sqlStatement($insert_primary_user_query, [$user_id, $primary_label, $is_primary_value]);
        }
    }

    $permissions_saved = true;

    // Set appropriate success message based on action
    if ($reset_primary) {
        $permissions_message = xlt("User permissions saved and primary user designation cleared successfully!");
        $permissions_message_type = "warning"; // Use warning color for reset action
    } else {
        $permissions_message = xlt("User permissions have been saved successfully!");
        $permissions_message_type = "success";
    }
}

// Get all active users for the form
$users_query = "SELECT id, username, fname, lname, authorized FROM users WHERE active = 1 AND username IS NOT NULL AND fname IS NOT NULL ORDER BY lname, fname";
$users_result = sqlStatement($users_query);
$active_users = [];
while ($user = sqlFetchArray($users_result)) {
    $active_users[] = $user;
}

// Get current primary user
$current_primary_user = BootstrapService::getPrimaryUser();

$vendors = $boot->getVendorGlobals();
?>
<!DOCTYPE HTML>
<html lang="eng">
<head>
    <title><?php echo xlt("Enable Services") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    if (count($vendors ?? []) === 0) {
        $boot->createVendorGlobals();
        $vendors = $boot->getVendorGlobals();
    }
    $isSmsEnabled = $vendors['oefax_enable_sms'] > 0 ? 'sms' : '';
    $isEmailEnable = $vendors['oe_enable_email'] > 0 ? 'email' : '';
    $isVoiceEnable = $vendors['oe_enable_voice'] > 0 ? 'voice' : '';
    $services = [$isSmsEnabled, $isEmailEnable];

    $isRCSMS = $vendors['oefax_enable_sms'] == 1 ? '1' : '0';
    $isEMAIL = $vendors['oe_enable_email'] == 4 ? '1' : '0';
    $isRCFax = $vendors['oefax_enable_fax'] == 1 ? '1' : '0';
    $isVOICE = $vendors['oe_enable_voice'] == 6 ? '1' : '0';
    $setupUrl = './../setup.php';
    if ($isRCFax || $isRCSMS) {
        $setupUrl = './../setup_rc.php';
    }
    Header::setupHeader();
    ?>
    <script>
        function toggleUserPermissions() {
            $(".frame").addClass("d-none");
            $("#set-user-permissions").toggleClass("d-none");
            return false;
        }

        function toggleAllPermissions(service, checked) {
            const checkboxes = document.querySelectorAll(`input[name*="_${service}"]`);
            checkboxes.forEach(checkbox => {
                // Skip the use_primary checkboxes
                if (!checkbox.name.includes('_use_primary')) {
                    checkbox.checked = checked;
                }
            });
        }

        function toggleAllUsePrimary(checked) {
            const checkboxes = document.querySelectorAll('input[name*="_use_primary"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = checked;
            });
        }

        function toggleUserAllServices(userId, checked) {
            const services = ['fax', 'sms', 'email', 'voice'];
            services.forEach(service => {
                const checkbox = document.getElementById(`user_${userId}_${service}`);
                if (checkbox) {
                    checkbox.checked = checked;
                }
            });
        }
    </script>
    <script>
        let ServiceFax = <?php echo js_escape($isRCFax) ?>;
        let ServiceSMS = <?php echo js_escape($isRCSMS); ?>;
        let ServiceEmail = <?php echo js_escape($isEMAIL); ?>;
        let ServiceVoice = <?php echo js_escape($isVOICE); ?>;

        function toggleHelpCard() {
            const helpCard = document.getElementById('helpCard');
            helpCard.style.display = helpCard.style.display === 'none' ? 'block' : 'none';
        }

        function toggleSetup(id, type = 'single') {
            let url = '../setup.php';
            if (ServiceFax === '1') {
                url = '../setup_rc.php';
            }
            if (ServiceVoice === '6') {
                url = '../setup_voice.php';
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
            if (id === 'set-voice') {
                let title = 'Voice Module Credentials';
                let params = {
                    buttons: [{text: 'Cancel', close: true, style: 'default btn-sm'}],
                    sizeHeight: 'full',
                    allowDrag: false,
                    type: 'iframe',
                    url: './../setup_voice.php?type=email&module_config=-1'
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
            document.querySelectorAll('input[name="selected_service"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    $("#form_action").submit();
                });
            });
        });
    </script>
</head>
<body>
    <div class="w-100 container-xl">
        <div class="form-group m-2 p-2 bg-dark">
            <button class="btn btn-outline-light" onclick="toggleSetup('set-service')"><?php echo xlt("Enable Accounts"); ?><i class="fa fa-caret"></i></button>
            <?php if (empty($current_primary_user) || $current_primary_user == $_SESSION['authUserID']) { ?>
                <button class="btn btn-outline-light" onclick="toggleUserPermissions()"><?php echo xlt("User Permissions"); ?><span class="caret"></span></button>
            <?php } ?>
            <?php if (!empty($vendors['oefax_enable_sms'])) { ?>
                <button class="btn btn-outline-light" onclick="toggleSetup('set-sms')"><?php echo xlt("Setup SMS"); ?><span class="caret"></span></button>
            <?php }
            if (!empty($vendors['oefax_enable_fax'])) { ?>
                <button class="btn btn-outline-light" onclick="toggleSetup('set-fax')"><?php echo xlt("Setup Fax"); ?><span class="caret"></span></button>
            <?php }
            if (!empty($vendors['oe_enable_voice'])) { ?>
                <button class="btn btn-outline-light" onclick="toggleSetup('set-voice')"><?php echo xlt("Setup Voice"); ?><span class="caret"></span></button>
            <?php }
            if (!empty($vendors['oe_enable_email'])) { ?>
                <button class="btn btn-outline-light" onclick="toggleSetup('set-email')"><?php echo xlt("Setup Email"); ?><span class="caret"></span></button>
            <?php } ?>
            <span class="checkbox text-light br-dark" title="Use Dialog or Panels">
                <label for="dialog"><?php echo xlt("Use Dialog"); ?></label>
                <input type="checkbox" class="checkbox" name="dialog" id="dialog" value="1">
            </span>
        </div>
        <div class="frame col-12" id="set-service">
            <form id="set_form" name="set_form" class="form" role="form" method="post" action="">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <div class="form-group">
                    <?php if (isset($permissions_saved) && $permissions_saved) { ?>
                    <div class="alert alert-<?php echo attr($permissions_message_type ?? 'success'); ?> text-center alert-dismissible fade show" role="alert">
                        <strong>
                            <?php if (($permissions_message_type ?? '') === 'warning') { ?>
                                <i class="fa fa-exclamation-triangle"></i>
                            <?php } else { ?>
                                <i class="fa fa-check-circle"></i>
                            <?php } ?>
                        </strong>
                        <?php echo text($permissions_message ?? xlt("User permissions have been saved successfully!")); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php } ?>
                    <?php if (empty($current_primary_user) || $current_primary_user == $_SESSION['authUserID']) { ?>
                        <?php if (!empty($current_primary_user)) { ?>
                    <div class="alert alert-success text-center" role="alert">
                        <i class="fa fa-user-check"></i>
                            <?php echo xlt("You are the current primary user. You can manage all settings."); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php } else { ?>
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="fa fa-user-times"></i>
                            <?php echo xlt("No primary user set. Any authorized user can manage settings."); ?>
                        <br><small><?php echo xlt("Consider setting a primary user for better security control."); ?></small>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php } ?>
                    <div class="row col form-group">
                        <label for="editingUser" class="form-inline"><?php echo xlt("Editing Service Credentials for User"); ?></label>
                        <div class="ml-2" title="User to setup credentials.">
                            <select class="form-control persist" name="editingUser" id="editingUser">
                                <option value="0"><?php echo xlt("Default (You)"); ?></option>
                                <?php foreach ($active_users as $user) {
                                    $user_id = $user['id'];
                                    if ($_SESSION['authUserID'] == $user_id) {
                                        continue;
                                    }
                                    $display_name = trim($user['fname'] . ' ' . $user['lname']);
                                    if (empty($display_name)) {
                                        $display_name = $user['username'];
                                    }
                                    ?>
                                    <option value="<?php echo attr($user_id); ?>" <?php echo ($_SESSION['editingUser'] == $user_id) ? 'selected' : ''; ?>>
                                        <?php echo text($display_name); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>

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
                                <option value="5" <?php echo $vendors['oefax_enable_sms'] == '5' ? 'selected' : ''; ?>><?php echo xlt("Clickatell"); ?></option>
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
                        <label for="voice_vendor" class="col-sm-6"><?php echo xlt("Enable Voice Widgets") ?></label>
                        <div class="col-sm-6" title="Enable Voice Widgets Support.">
                            <select class="form-control persist" name="voice_vendor" id="voice_vendor">
                                <option value="0" <?php echo $vendors['oe_enable_voice'] == '0' ? 'selected' : ''; ?>><?php echo xlt("Disabled"); ?></option>
                                <option value="6" <?php echo $vendors['oe_enable_voice'] == '6' ? 'selected' : ''; ?>><?php echo xlt("Enabled"); ?></option>
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

            <form class="form w-100" id="form_action" method="POST" action="setup_services.php">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <fieldset>
                    <legend><?php echo xlt('Select Background Service to Manage');
                        $showFlag = false; ?></legend>
                    <div id="helpCard" class="help-card card" style="display: none;">
                        <div class="card-header text-center"><h5><?php echo xlt('Managing Background Services Help'); ?></h5></div>
                        <div class="card-body"><?php echo nl2br(text('Select a background service to manage.
                        You may specify an required interval in hours when creating the task. The default is 24 hours
                        Use the action buttons to create the task, enable or disable its execution, or delete it.
                        When Create is used the task is created but disabled.
                        If Enable is selected, the task is created if not already installed and enabled as a step saver.
                        Whichever one is used, Create or Enable, and the task already exists, it will be updated with the execute interval input value and enabled if Enable or the task last state if Create.
                        Whenever a new service task is created and enabled, the task will run initial notifications within 2 minutes so, be prepared.')); ?></div>
                    </div>
                    <div class="pl-2 form-group clearfix">
                        <?php foreach ($services as $service) {
                            if (empty($service)) {
                                continue;
                            }
                            $showFlag = true;
                            ?>
                            <label>
                                <input type="radio" name="selected_service" value="<?php echo attr($service); ?>" <?php echo ($selectedService === $service) ? 'checked' : ''; ?> />
                                <?php echo text(ucfirst($service)); ?>
                            </label>
                        <?php }
                        if ($showFlag) { ?>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" name="action" value="create"><?php echo xlt('Create and Run'); ?></button>
                                <?php echo xlt('Every'); ?> <input type="text" name="period" id="period_input" class="" style="display:inline-block; max-width: 125px;" maxlength="4"
                                    placeholder="<?php echo xla('Execute Interval'); ?>"
                                    value="<?php echo attr($period ?? ''); ?>" /> <?php echo xlt('Hours'); ?>
                                <span class="button-group">
                                <button type="submit" class="btn btn-success" name="action" value="enable"><?php echo xlt('Enable'); ?></button>
                                <button type="submit" class="btn btn-warning" name="action" value="disable"><?php echo xlt('Disable'); ?></button>
                                <button type="submit" class="btn btn-danger" name="action" value="delete"><?php echo xlt('Delete'); ?></button>
                                <button type="button" class="btn btn-info" onclick="toggleHelpCard()"><?php echo xlt('Help'); ?></button>
                            </span>
                            </div>
                            <?php if ($currentStatus !== null && isset($currentStatus[$selectedService])) { ?>
                                <span><strong><?php echo xlt('Status of'); ?> <?php echo text(ucfirst((string) $selectedService)); ?> <?php echo xlt('Service'); ?>:</strong></span>
                                <ul>
                                    <li><strong><?php echo xlt('Service Status'); ?>: </strong><?php echo text($currentStatus[$selectedService]['active']) ? xlt('Enabled to Run.') : xlt('Disabled or not Created.'); ?></li>
                                    <li><strong><?php echo xlt('Execution Run Interval'); ?>: </strong><?php echo text($currentStatus[$selectedService]['execute_interval']) . ' ' . xlt('Minutes'); ?></li>
                                    <li><strong><?php echo xlt('Next Run Time'); ?>: </strong><?php echo text($currentStatus[$selectedService]['next_run']); ?></li>
                                </ul>
                            <?php }
                        } else { ?>
                            <h5><?php echo xlt('To enable Background Services setup an SMS vendor, enable the Mail Client or Both.'); ?></h5>
                        <?php } ?>
                    </div>
                </fieldset>
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
        <?php if (!empty($vendors['oe_enable_voice'])) { ?>
            <div id="set-voice" class="frame d-none">
                <h3 class="text-center"><?php echo xlt("Setup Voice Account"); ?></h3>
                <iframe src="<?php echo attr('./../setup_voice.php?type=voice&module_config=1&mode=flat'); ?>" style="border:none;height:100vh;width:100%;"></iframe>
            </div>
        <?php } ?>
        <div class="frame col-12 d-none" id="set-user-permissions">
            <form id="user_permissions_form" name="user_permissions_form" class="form" role="form" method="post" action="">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <div class="container-fluid">
                    <div class="title text-center"><?php echo xlt("User Service Permissions"); ?></div>
                    <div class="small text-center mb-2">
                        <span><?php echo xlt("Set individual user permissions for Fax, SMS, Email, and Voice services."); ?></span>
                    </div>

                    <?php if (isset($permissions_saved) && $permissions_saved) { ?>
                        <div class="alert alert-success text-center" role="alert">
                            <?php echo xlt("User permissions have been saved successfully!"); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php } ?>
                    <hr>
                    <!-- User Permissions Table Section -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="thead-dark">
                            <tr>
                                <th><?php echo xlt("User"); ?></th>
                                <th><?php echo xlt("Username"); ?></th>
                                <th class="text-center">
                                    <?php echo xlt("Fax"); ?>
                                    <br>
                                    <input type="checkbox" onchange="toggleAllPermissions('fax', this.checked)" title="<?php echo xla('Toggle all Fax permissions'); ?>">
                                </th>
                                <th class="text-center">
                                    <?php echo xlt("SMS"); ?>
                                    <br>
                                    <input type="checkbox" onchange="toggleAllPermissions('sms', this.checked)" title="<?php echo xla('Toggle all SMS permissions'); ?>">
                                </th>
                                <th class="text-center">
                                    <?php echo xlt("Email"); ?>
                                    <br>
                                    <input type="checkbox" onchange="toggleAllPermissions('email', this.checked)" title="<?php echo xla('Toggle all Email permissions'); ?>">
                                </th>
                                <th class="text-center">
                                    <?php echo xlt("Voice"); ?>
                                    <br>
                                    <input type="checkbox" onchange="toggleAllPermissions('voice', this.checked)" title="<?php echo xla('Toggle all Voice permissions'); ?>">
                                </th>
                                <th class="text-center">
                                    <?php echo xlt("Use Primary"); ?>
                                    <br>
                                    <input type="checkbox" onchange="toggleAllUsePrimary(this.checked)" title="<?php echo xla('Toggle all Use Primary settings'); ?>">
                                </th>
                                <th class="text-center">
                                    <?php echo xlt("Primary User"); ?>
                                    <br>
                                    <small class="text-muted"><?php echo xlt("(One Only)"); ?></small>
                                    <br>
                                    <input type="radio"
                                        name="primary_user"
                                        value="0"
                                        id="reset_primary_user"
                                        title="<?php echo xla('Clear primary user selection'); ?>"
                                        style="accent-color: #dc3545;">
                                    <label for="reset_primary_user" class="small text-danger" title="<?php echo xla('Clear primary user selection'); ?>">
                                        <?php echo xlt("Reset"); ?>
                                    </label>
                                </th>
                                <th class="text-center">
                                    <?php echo xlt("All Services"); ?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($active_users as $user) {
                                $user_id = $user['id'];
                                $display_name = trim($user['fname'] . ' ' . $user['lname']);
                                if (empty($display_name)) {
                                    $display_name = $user['username'];
                                }
                                ?>
                                <tr>
                                    <td><?php echo text($display_name); ?></td>
                                    <td><?php echo text($user['username']); ?></td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            name="user_<?php echo attr($user_id); ?>_fax"
                                            id="user_<?php echo attr($user_id); ?>_fax"
                                            value="1"
                                            <?php echo BootstrapService::getUserPermission($user_id, 'fax') == '1' ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            name="user_<?php echo attr($user_id); ?>_sms"
                                            id="user_<?php echo attr($user_id); ?>_sms"
                                            value="1"
                                            <?php echo BootstrapService::getUserPermission($user_id, 'sms') == '1' ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            name="user_<?php echo attr($user_id); ?>_email"
                                            id="user_<?php echo attr($user_id); ?>_email"
                                            value="1"
                                            <?php echo BootstrapService::getUserPermission($user_id, 'email') == '1' ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            name="user_<?php echo attr($user_id); ?>_voice"
                                            id="user_<?php echo attr($user_id); ?>_voice"
                                            value="1"
                                            <?php echo BootstrapService::getUserPermission($user_id, 'voice') == '1' ? 'checked' : ''; ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            name="user_<?php echo attr($user_id); ?>_use_primary"
                                            id="user_<?php echo attr($user_id); ?>_use_primary"
                                            value="1"
                                            <?php echo BootstrapService::usePrimaryAccount($user_id) == '1' ? 'checked' : ''; ?>
                                            title="<?php echo xla('Allow this user to use primary account credentials'); ?>">
                                    </td>
                                    <td class="text-center">
                                        <input type="radio"
                                            name="primary_user"
                                            value="<?php echo attr($user_id); ?>"
                                            <?php echo ($current_primary_user == $user_id) ? 'checked' : ''; ?>
                                            title="<?php echo xla('Set as primary user for all services'); ?>">
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox"
                                            onchange="toggleUserAllServices(<?php echo attr($user_id); ?>, this.checked)"
                                            title="<?php echo xla('Toggle all services for this user'); ?>">
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" name="form_save_permissions" class="btn btn-primary btn-save" value="1">
                            <?php echo xlt("Save User Permissions"); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
