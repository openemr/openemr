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
$c = $clientApp->getCredentials();
$title = $service == "1" ? xlt('RingCentral Fax SMS') : xlt('Twilio SMS');
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
                $(".ringcentral").hide();
                $(".etherfax").hide();
            } else if (currentService == '1') {
                $(".twilio").hide();
                $(".etherfax").hide();
            } else if (currentService == '3') {
                $(".twilio").hide();
                $(".ringcentral").hide();
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
                    <?php if ($service == '3') { ?>
                        <div class="checkbox">
                            <label>
                                <input id="form_production" type="checkbox" name="production" <?php echo attr($c['production']) ? ' checked' : '' ?>>
                                <?php echo xlt("Production Check") ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("Account Id") ?> *</label>
                            <input id="form_username" type="text" name="account" class="form-control" value='<?php echo attr($c['account']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("Account Username") ?></label>
                            <input id="form_username" type="text" name="username" class="form-control"
                                value='<?php echo attr($c['username']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_password"><?php echo xlt("Account Password") ?></label>
                            <input id="form_password" type="password" name="password" class="form-control"
                                value='<?php echo attr($c['password']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_extension"><?php echo xlt("Phone Number") ?></label>
                            <input id="form_extension" type="text" name="phone" class="form-control" value='<?php echo attr($c['phone']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_key"><?php echo xlt("API Key") ?></label>
                            <input id="form_key" type="password" name="key" class="form-control" value='<?php echo attr($c['appKey']) ?>'>
                        </div>
                    <?php } else { ?>
                        <div class="checkbox">
                            <label>
                                <input id="form_production" type="checkbox" name="production" <?php echo attr($c['production']) ? ' checked' : '' ?>>
                                <?php echo xlt("Production Check") ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("RC Username, Phone or Twilio Account Sid") ?> *</label>
                            <input id="form_username" type="text" name="username" class="form-control"
                                required="required" value='<?php echo attr($c['username']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_extension"><?php echo xlt("Phone Number or Extension") ?></label>
                            <input id="form_extension" type="text" name="extension" class="form-control"
                                required="required" value='<?php echo attr($c['extension']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_password"><?php echo xlt("RC Password or Twilio Auth Token") ?> *</label>
                            <input id="form_password" type="password" name="password" class="form-control"
                                required="required" value='<?php echo attr($c['password']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_smsnumber"><?php echo xlt("SMS Number") ?></label>
                            <input id="form_smsnumber" type="text" name="smsnumber" class="form-control"
                                value='<?php echo attr($c['smsNumber']) ?>' required>
                        </div>
                        <div class="form-group">
                            <label for="form_key"><?php echo xlt("RC Client ID or Twilio Api Sid") ?> *</label>
                            <input id="form_key" type="text" name="key" class="form-control"
                                required="required" value='<?php echo attr($c['appKey']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_secret"><?php echo xlt("RC Client Secret or Twilio Api Secret") ?> *</label>
                            <input id="form_secret" type="password" name="secret" class="form-control"
                                required="required" value='<?php echo attr($c['appSecret']) ?>'>
                        </div>
                        <div class="form-group">
                            <label class="ringcentral" for="form_redirect_url"><?php echo xlt("OAuth Redirect URI") ?></label>
                            <input id="form_redirect_url" type="text" name="redirect_url" class="form-control ringcentral"
                                placeholder="<?php echo xlt('From RingCentral Account') ?>"
                                value='<?php echo attr($c['redirect_url']) ?>'>
                        </div>
                        <div class=" form-group">
                            <label for="form_nhours"><?php echo xlt("Appointments Advance Notify (Hours)") ?> *</label>
                            <input id="form_nhours" type="text" name="smshours" class="form-control"
                                placeholder="<?php echo xlt('Please enter number of hours before appointment') ?> *"
                                required="required" value='<?php echo attr($c['smsHours']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_message"><?php echo xlt("Message Template") ?> *</label>
                            <span style="font-size:12px;font-style: italic">&nbsp;
                        <?php echo xlt("Tags") ?>: ***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME***, ***ORG***</span>
                            <textarea id="form_message" type="text" rows="3" name="smsmessage" class="form-control"
                                required="required" value='<?php echo attr($c['smsMessage']) ?>'><?php echo attr($c['smsMessage']) ?></textarea>
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
