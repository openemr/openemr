<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controllers;

require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

$serviceType = 'voice';
AppDispatch::setModuleType($serviceType);
// kick off app endpoints controller
$clientApp = AppDispatch::getApiService($serviceType);
if (!$clientApp->verifyAcl()) {
    die("<h3>" . xlt("Not Authorised!") . "</h3>");
}
$c = $clientApp->getCredentials();
$credEnableEvents = is_array($c) ? ($c['enable_events'] ?? null) : null;
$voiceEnabled = filter_var(OEGlobalsBag::getInstance()->get('oe_enable_voice'), FILTER_VALIDATE_BOOLEAN);
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$module_config = $_REQUEST['module_config'] ?? 1;
$pid = SessionWrapperFactory::getInstance()->getActiveSession()->get('pid') ?? 0;
echo "<script>var pid=" . js_escape($pid) . "</script>";
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("Setup") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader();
    echo "<script>let Service=" . js_escape($serviceType) . ";let csrfToken=" . js_escape(CsrfUtils::collectCsrfToken($session, 'contact-form')) . "</script>";
    ?>
    <script>
        $(function () {
            $('#setup-form').on('submit', function (e) {
                if (!e.isDefaultPrevented()) {
                    $(window).scrollTop(0);
                    let wait = '<i class="fa fa-cog fa-spin fa-4x"></i>';
                    let url = 'saveSetup?type=voice';
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: $(this).serialize(),
                        success: function (data) {
                            var err = (data.search(/Exception/) !== -1 ? 1 : 0);
                            if (!err) {
                                err = (data.search(/Error:/) !== -1 ? 1 : 0);
                            }
                            var messageAlert = 'alert-' + (err !== 0 ? 'danger' : 'success');
                            var messageText = data;
                            var alertBox = '<div class="alert ' + messageAlert + ' alert-dismissable"><button type="button" ' +
                                'class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' + messageText + '</div>';
                            if (messageAlert && messageText) {
                                // inject the alert to .messages div in our form
                                $('#setup-form').find('.messages').html(alertBox);
                                if (!err) {
                                    // empty the form
                                    $('#setup-form')[0].reset();
                                    setTimeout(function () {
                                        $('#setup-form').find('.messages').remove();
                                        <?php if (!$module_config) { ?>
                                        dlgclose();
                                        location.reload();
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
        });

        function openJwtWindow() {
            const clientId = document.getElementById('form_key').value;
            if (clientId) {
                const url = 'https://developers.ringcentral.com/console/my-credentials/create?client_id=' + encodeURIComponent(clientId);
                window.open(url, '_blank', 'width=1200,height=800');
            } else {
                let message = xl("Please enter Client ID first.");
                (async (message, time) => {
                    await asyncAlertMsg(message, time, 'warning', 'lg');
                })(message, 1500).then(res => {
                    console.log(res);
                });
            }
        }

        function togglePasswordVisibility(id) {
            var input = document.getElementById(id);
            var icon = document.querySelector(`#${id} + .toggle-password i`);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        // Explicitly register/refresh the RingCentral call-event webhook
        // subscription. Routes to VoiceClient::install(), which self-gates on
        // voice-enabled + the enable_events flag and returns a JSON status.
        function registerVoiceWebhook() {
            var $status = $('#rc-webhook-status');
            $status.html('<i class="fa fa-cog fa-spin"></i>');
            $.post('install?type=voice', {csrf_token_form: csrfToken}, null, 'json')
                .done(function (data) {
                    var text = (data && (data.status || data.msg))
                        ? ((data.status ? data.status + ': ' : '') + (data.msg || ''))
                        : <?php echo xlj('Done'); ?>;
                    $status.text(text);
                })
                .fail(function () {
                    $status.text(<?php echo xlj('Webhook registration request failed.'); ?>);
                });
        }
    </script>
</head>
<body>
    <div class="container-fluid">
        <?php if ($module_config) { ?>
            <h4><?php echo xlt("Setup Credentials") ?></h4>
        <?php } ?>
        <form class="form" id="setup-form" role="form">
            <div class="messages"></div>
            <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken($session, 'contact-form'); ?>" />
            <div class="row">
                <div class="col-md-12">
                    <div class="checkbox">
                        <button type="submit" class="btn btn-success float-right m-2" value=""><?php echo xlt("Save Settings") ?></button>
                        <label>
                            <input id="form_production" type="checkbox" name="production" <?php echo attr($c['production']) ? ' checked' : '' ?>>
                            <?php echo xlt("Production Check") ?>
                        </label>
                    </div>
                    <div class="form-group mt-2 p-2 border rounded">
                        <label class="d-block font-weight-bold"><?php echo xlt("Call Event Tracking (RingCentral Webhook)"); ?></label>
                        <div class="checkbox">
                            <label>
                                <input id="form_enable_events" type="checkbox" name="enable_events" value="1"
                                    <?php echo !in_array($credEnableEvents, [null, false, 0, 0.0, '', '0'], true) ? 'checked' : ''; ?>
                                    <?php echo $voiceEnabled ? '' : 'disabled'; ?> />
                                <?php echo xlt("Enable call event tracking"); ?>
                            </label>
                        </div>
                        <?php if ($voiceEnabled) { ?>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="registerVoiceWebhook()">
                                <?php echo xlt("Register / Refresh Webhook Subscription"); ?>
                            </button>
                            <span id="rc-webhook-status" class="ml-2 text-muted small"></span>
                            <div class="small text-muted mt-1"><?php echo xlt("Save settings first, then register. Re-register after changing credentials or the server URL."); ?></div>
                        <?php } else { ?>
                            <div class="small text-danger mt-1"><?php echo xlt("Enable Voice (RingCentral) before turning on call event tracking."); ?></div>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label for="form_extension"><?php echo xlt("Extension") ?> *</label>
                        <input id="form_extension" type="text" name="extension" class="form-control" value='<?php echo attr($c['extension']) ?>' required />
                    </div>
                    <div class="form-group">
                        <label for="form_phone"><?php echo xlt("Phone Number") ?> *</label>
                        <input type="tel" class="form-control" id="form_phone" name="phone" value='<?php echo attr($c['phone']) ?>' required />
                    </div>
                    <div class="form-group">
                        <label for="form_smsnumber"><?php echo xlt("Opt Phone Number") ?></label>
                        <input id="form_smsnumber" type="tel" name="smsnumber" class="form-control" value='<?php echo attr($c['smsNumber']) ?>' />
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group">
                        <label for="form_key"><?php echo xlt("Client ID") ?> *</label>
                        <div class="input-group">
                            <input id="form_key" type="password" name="key" class="form-control" value='<?php echo attr($c['appKey']) ?>' required />
                            <div class="input-group-append toggle-password" onclick="togglePasswordVisibility('form_key')">
                                <span class="input-group-text"><i class="fa fa-eye"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="form_secret"><?php echo xlt("Client Secret") ?> *</label>
                        <div class="input-group">
                            <input id="form_secret" type="password" name="secret" class="form-control"
                                value='<?php echo attr($c['appSecret']) ?>' required />
                            <div class="input-group-append toggle-password" onclick="togglePasswordVisibility('form_secret')">
                                <span class="input-group-text"><i class="fa fa-eye"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="form_jwt"><?php echo xlt("Copy and Paste JWT") ?> *</label>
                        <span class="form-group">
                                <button type="button" class="btn btn-primary btn-download btn-sm mb-1 p-0 px-1 float-right" onclick="openJwtWindow()"><?php echo xlt("Create JWT") ?></button>
                            </span>
                        <textarea id="form_jwt" type="text" rows="3" name="jwt" class="form-control small" required><?php echo attr($c['jwt']) ?></textarea>
                    </div>
                </div>
            </div>
            <p class="text-muted"><strong>*</strong> <?php echo xlt("These fields are required.") ?> </p>
            <button type="submit" class="btn btn-success float-right m-2" value=""><?php echo xlt("Save Settings") ?></button>
        </form>
    </div>
</body>
</html>
