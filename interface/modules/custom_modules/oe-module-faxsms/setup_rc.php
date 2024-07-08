<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controllers;

require_once(__DIR__ . "/../../../globals.php");
use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
// kick off app endpoints controller
$serviceType = $_REQUEST['type'] ?? $_SESSION["oefax_current_module_type"] ?? '';
// kick off app endpoints controller
$clientApp = AppDispatch::getApiService($serviceType);
$service = $clientApp::getServiceType();
if (!$clientApp->verifyAcl()) {
    die("<h3>" . xlt("Not Authorised!") . "</h3>");
}
$c = $clientApp->getCredentials();
echo "<script>var pid=" . js_escape($pid) . "</script>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader();
    echo "<script>var Service=" . js_escape($service) . ";</script>";
    ?>
    <script>
        $(function () {
            $('#setup-form').on('submit', function (e) {
                if (!e.isDefaultPrevented()) {
                    $(window).scrollTop(0);
                    let wait = '<i class="fa fa-cog fa-spin fa-4x"></i>';
                    let url = 'saveSetup?type=' + encodeURIComponent(<?php echo js_escape($serviceType) ?>);
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
                                        <?php if (!$module_config) {
                                            ?>
                                        dlgclose();
                                        <?php } else {
                                            ?>
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
            if (Service === '2') {
                $(".ringcentral").hide();
            } else {
                $(".twilio").hide();
            }
        });

        function openJwtWindow() {
            var clientId = document.getElementById('form_key').value;
            if (clientId) {
                var url = 'https://developers.ringcentral.com/console/my-credentials/create?client_id=' + encodeURIComponent(clientId);
                window.open(url, '_blank', 'width=800,height=600');
            } else {
                alert('Please enter your Client ID first.');
            }
        }
    </script>
</head>
<body>
    <div class="container-fluid">
        <?php if ($module_config) {
            ?>
            <h4><?php echo xlt("Setup Credentials") ?></h4>
        <?php } ?>
        <form class="form" id="setup-form" role="form">
            <div class="messages"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="checkbox">
                        <label>
                            <input id="form_production" type="checkbox" name="production" <?php echo attr($c['production']) ? ' checked' : '' ?>>
                            <?php echo xlt("Production Check") ?>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("Username, Phone or Account Sid") ?> *</label>
                            <input id="form_username" type="text" name="username" class="form-control"
                                required="required" value='<?php echo attr($c['username']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_extension"><?php echo xlt("Phone Number or Extension") ?></label>
                            <input id="form_extension" type="text" name="extension" class="form-control"
                                required="required" value='<?php echo attr($c['extension']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_password"><?php echo xlt("Password or Auth Token") ?> *</label>
                            <input id="form_password" type="text" name="password" class="form-control"
                                required="required" value='<?php echo attr($c['password']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_smsnumber"><?php echo xlt("SMS Number") ?></label>
                            <input id="form_smsnumber" type="text" name="smsnumber" class="form-control"
                                value='<?php echo attr($c['smsNumber']) ?>'>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="form_key"><?php echo xlt("Client ID") ?> *</label>
                            <input id="form_key" type="text" name="key" class="form-control"
                                required="required" value='<?php echo attr($c['appKey']) ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_secret"><?php echo xlt("Client Secret") ?> *</label>
                            <input id="form_secret" type="text" name="secret" class="form-control"
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
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="form_message"><?php echo xlt("Message Template") ?> *</label>
                            <span style="font-size:12px;font-style: italic">&nbsp;
<?php echo xlt("Tags") ?>: ***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME***, ***ORG***</span>
                            <textarea id="form_message" type="text" rows="3" name="smsmessage" class="form-control"
                                required="required" value='<?php echo attr($c['smsMessage']) ?>'><?php echo attr($c['smsMessage']) ?></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="form_jwt"><?php echo xlt("Paste JWT") ?> *</label>
                        <span class="form-group">
                            <button type="button" class="btn btn-primary btn-download btn-sm mb-1 p-0 px-1" onclick="openJwtWindow()"><?php echo xlt("Create JWT") ?></button>
                        </span>
                        <textarea id="form_jwt" type="text" rows="3" name="jwt" class="form-control"
                            required="required"><?php echo attr($c['jwt']) ?></textarea>
                    </div>
                    <p class="text-muted"><strong>*</strong> <?php echo xlt("These fields are required.") ?> </p>
                </div>
            </div>
            <button type="submit" class="btn btn-success float-right" value=""><?php echo xlt("Save") ?></button>
    </div>
    </form>
    </div>
</body>
</html>
