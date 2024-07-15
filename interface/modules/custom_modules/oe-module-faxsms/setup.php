<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

$serviceType = $_REQUEST['type'] ?? $_SESSION["oefax_current_module_type"] ?? '';
// kick off app endpoints controller
$clientApp = AppDispatch::getApiService($serviceType);
$service = $clientApp::getServiceType();
if (!$clientApp->verifyAcl()) {
    die("<h3>" . xlt("Not Authorised!") . "</h3>");
}
$c = $clientApp->getCredentials();
$title = $service == "2" ? xlt('Twilio SMS') : xlt('SMS');
$title = $service == "3" ? xlt('etherFAX') : $title;
$module_config = $_REQUEST['module_config'] ?? 0;
$mode = $_REQUEST['mode'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>><?php echo xlt("Setup") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader();
    echo "<script>let currentService=" . js_escape($service) . "</script>";
    ?>
    <script>
        $(function () {
            $('#setup-form').on('submit', function (e) {
                if (!e.isDefaultPrevented()) {
                    $(window).scrollTop(0);
                    let wait = '<i class="fa fa-cog fa-spin fa-4x"></i>';
                    let actionUrl = 'saveSetup?type=' + encodeURIComponent(<?php echo js_escape($serviceType) ?>);
                    $.ajax({
                        type: "POST",
                        url: actionUrl,
                        data: $(this).serialize(),
                        success: function (data) {
                            let err = (data.search(/Exception/) !== -1 ? 1 : 0);
                            if (!err) {
                                err = (data.search(/Error:/) !== -1 ? 1 : 0);
                            }
                            const messageAlert = 'alert-' + (err !== 0 ? 'danger' : 'success');
                            const messageText = data;
                            const alertBox = '<div class="alert ' + messageAlert + ' alert-dismissable"><button type="button" ' +
                                'class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + messageText + '</div>';
                            if (messageAlert && messageText) {
                                $(window).scrollTop(0);
                                // inject the alert to .messages div in our form
                                $('#setup-form').find('.messages').html(alertBox);
                                if (!err) {
                                    setTimeout(function () {
                                        $('#setup-form').find('.messages').remove();
                                        <?php if (!$mode == 'flat') { ?>
                                        dlgclose();
                                        <?php } else { ?>
                                        location.reload();
                                        <?php } ?>
                                    }, 2000);
                                }
                            }
                        }
                    });
                    return false;
                }
            });

            if (currentService == '2') {
                $(".etherfax").hide();
            } else if (currentService == '3') {
                $(".twilio").hide();
                $(".etherfax").show();
            }
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <?php if ($module_config) { ?>
            <h4><?php echo xlt("Setup Credentials") . ' ' . $title ?></h4>
        <?php } ?>
        <form class="form" id="setup-form" role="form">
            <div class="messages"></div>
            <div class="row">
                <div class="col">
                    <?php if ($service == '3') {
                        ?> <!-- etherFAX -->
                        <div class="checkbox">
                            <label>
                                <input id="form_production" type="checkbox" name="production" <?php echo attr($c['production']) ? ' checked' : '' ?> />
                                <?php echo xlt("Demo Mode") ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("Account Id") ?> *</label>
                            <input id="form_username" type="text" name="account" class="form-control" value='<?php echo attr($c['account']) ?>' required />
                        </div>
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("Account Username") ?></label>
                            <input id="form_username" type="text" name="username" class="form-control"
                                value='<?php echo attr($c['username']) ?>' placeholder="<?php echo xla('Optional if not using Account API Key') ?>" />
                        </div>
                        <div class="form-group">
                            <label for="form_password"><?php echo xlt("Account Password") ?></label>
                            <input id="form_password" type="password" name="password" class="form-control"
                                value='<?php echo attr($c['password']) ?>' placeholder="<?php echo xla('Optional if not using Account API Key') ?>" /'>
                        </div>
                        <div class="form-group">
                            <label for="form_extension"><?php echo xlt("Account Fax Number") ?> *</label>
                            <input id="form_extension" type="text" name="phone" class="form-control" value='<?php echo attr($c['phone']) ?>' placeholder="<?php echo xla('Number where you will receive faxes.') ?>" required />
                        </div>
                        <div class="form-group">
                            <label for="form_key"><?php echo xlt("Account API Key - Recommended") ?></label>
                            <input id="form_key" type="password" name="key" class="form-control" value='<?php echo attr($c['appKey']) ?>' placeholder="<?php echo xla('Most secure! Use only your API Key and Account Id.') ?>" />
                        </div>
                    <?php } elseif ($service == '2') {
                        ?> <!-- Twilio -->
                        <div class="checkbox">
                            <label>
                                <input id="form_production" type="checkbox" name="production" <?php echo attr($c['production']) ? ' checked' : '' ?>>
                                <?php echo xlt("Production Check") ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("Twilio Account Sid") ?> *</label>
                            <input id="form_username" type="text" name="username" class="form-control"
                                required="required" value='<?php echo attr($c['username']) ?>' />
                        </div>
                        <div class="form-group">
                            <label for="form_password"><?php echo xlt("Twilio Auth Token") ?> *</label>
                            <input id="form_password" type="password" name="password" class="form-control"
                                required="required" value='<?php echo attr($c['password']) ?>' />
                        </div>
                        <div class="form-group">
                            <label for="form_smsnumber"><?php echo xlt("SMS Number") ?></label>
                            <input id="form_smsnumber" type="text" name="smsnumber" class="form-control"
                                value='<?php echo attr($c['smsNumber']) ?>' required />
                        </div>
                        <div class="form-group">
                            <label for="form_key"><?php echo xlt("Twilio Api Sid") ?> *</label>
                            <input id="form_key" type="text" name="key" class="form-control"
                                required="required" value='<?php echo attr($c['appKey']) ?>' />
                        </div>
                        <div class="form-group">
                            <label for="form_secret"><?php echo xlt("Twilio Api Secret") ?> *</label>
                            <input id="form_secret" type="password" name="secret" class="form-control"
                                required="required" value='<?php echo attr($c['appSecret']) ?>' />
                        </div>
                        <div class=" form-group">
                            <label for="form_nhours"><?php echo xlt("Appointments Advance Notify (Hours)") ?> *</label>
                            <input id="form_nhours" type="text" name="smshours" class="form-control"
                                placeholder="<?php echo xla('Please enter number of hours before appointment') ?> *"
                                required="required" value='<?php echo attr($c['smsHours']) ?>' />
                        </div>
                        <div class="form-group">
                            <label for="form_message"><?php echo xlt("Message Template") ?> *</label>
                            <span style="font-size:12px;font-style: italic">&nbsp;
                                <?php echo xlt("Replace Tags") ?>: <?php echo text("***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME***, ***ORG***"); ?>
                            </span>
                            <textarea id="form_message" type="text" rows="3" name="smsmessage" class="form-control"
                                value='<?php echo attr($c['smsMessage']) ?>'><?php echo text($c['smsMessage']) ?></textarea>
                        </div>
                    <?php } elseif ($service == '1') {
                        ?> <!-- RC -->
                        <div class="checkbox">
                            <label>
                                <input id="form_production" type="checkbox" name="production" <?php echo attr($c['production']) ? ' checked' : '' ?>>
                                <?php echo xlt("Production Check") ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("Twilio Account Sid") ?> *</label>
                            <input id="form_username" type="text" name="username" class="form-control"
                                required="required" value='<?php echo attr($c['username']) ?>' />
                        </div>
                        <div class="form-group">
                            <label for="form_password"><?php echo xlt("Twilio Auth Token") ?> *</label>
                            <input id="form_password" type="password" name="password" class="form-control"
                                required="required" value='<?php echo attr($c['password']) ?>' />
                        </div>
                        <div class="form-group">
                            <label for="form_smsnumber"><?php echo xlt("SMS Number") ?></label>
                            <input id="form_smsnumber" type="text" name="smsnumber" class="form-control"
                                value='<?php echo attr($c['smsNumber']) ?>' required />
                        </div>
                        <div class="form-group">
                            <label for="form_key"><?php echo xlt("Twilio Api Sid") ?> *</label>
                            <input id="form_key" type="text" name="key" class="form-control"
                                required="required" value='<?php echo attr($c['appKey']) ?>' />
                        </div>
                        <div class="form-group">
                            <label for="form_secret"><?php echo xlt("Twilio Api Secret") ?> *</label>
                            <input id="form_secret" type="password" name="secret" class="form-control"
                                required="required" value='<?php echo attr($c['appSecret']) ?>' />
                        </div>
                        <div class=" form-group">
                            <label for="form_nhours"><?php echo xlt("Appointments Advance Notify (Hours)") ?> *</label>
                            <input id="form_nhours" type="text" name="smshours" class="form-control"
                                placeholder="<?php echo xla('Please enter number of hours before appointment') ?> *"
                                required="required" value='<?php echo attr($c['smsHours']) ?>' />
                        </div>
                        <div class="form-group">
                            <label for="form_message"><?php echo xlt("Message Template") ?> *</label>
                            <span style="font-size:12px;font-style: italic">&nbsp;
                                <?php echo xlt("Replace Tags") ?>: <?php echo text("***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME***, ***ORG***"); ?>
                            </span>
                            <textarea id="form_message" type="text" rows="3" name="smsmessage" class="form-control"
                                value='<?php echo attr($c['smsMessage']) ?>'><?php echo text($c['smsMessage']) ?></textarea>
                        </div>
                    <?php } ?>
                    <div>
                        <span class="text-muted"><strong>*</strong> <?php echo xlt("These fields are required.") ?> </span>
                        <button type="submit" class="btn btn-success float-right" value=""><?php echo xlt("Save") ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
