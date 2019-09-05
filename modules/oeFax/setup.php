<?php
/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Core\Header;

// kick off app endpoints controller
$clientApp = AppDispatch::getApiService();
$c = $clientApp->getCredentials();

echo "<script>var pid=" . js_escape($pid) . "</script>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader(); ?>
    <script>
        $(document).ready(function () {
            $(function () {
                $('#setup-form').on('submit', function (e) {
                    if (!e.isDefaultPrevented()) {
                        let wait = '<i class="fa fa-cog fa-spin fa-4x"></i>';
                        let url = 'saveSetup';
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
                                            dlgclose();
                                        }, 2000);
                                    }
                                }
                            }
                        });
                        return false;
                    }
                })
            });
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <form class="form" id="setup-form" role="form">
            <div class="messages"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="checkbox">
                        <label>
                            <input id="form_production" type="checkbox" name="production" <?php echo $c['production'] ? ' checked' : '' ?>>
                            <?php echo xlt("Production Check") ?>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="form_username"><?php echo xlt("Username, Phone or Account Sid") ?> *</label>
                            <input id="form_username" type="text" name="username" class="form-control"
                                required="required" value='<?php echo $c['username'] ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_extension"><?php echo xlt("Phone Number or Extension") ?></label>
                            <input id="form_extension" type="text" name="extension" class="form-control"
                                required="required" value='<?php echo $c['extension'] ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_password"><?php echo xlt("Password") ?> *</label>
                            <input id="form_password" type="text" name="password" class="form-control"
                                required="required" value='<?php echo $c['password'] ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_smsnumber"><?php echo xlt("SMS Number") ?></label>
                            <input id="form_smsnumber" type="text" name="smsnumber" class="form-control"
                                value='<?php echo $c['smsNumber'] ?>'>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="form_key"><?php echo xlt("Client ID") ?> *</label>
                            <input id="form_key" type="text" name="key" class="form-control"
                                required="required" value='<?php echo $c['appKey'] ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_secret"><?php echo xlt("Client Secret") ?> *</label>
                            <input id="form_secret" type="text" name="secret" class="form-control"
                                required="required" value='<?php echo $c['appSecret'] ?>'>
                        </div>
                        <div class="form-group">
                            <label for="form_redirect_url"><?php echo xlt("OAuth Redirect URI") ?></label>
                            <input id="form_redirect_url" type="text" name="redirect_url" class="form-control"
                                placeholder="<?php echo xlt('From RingCentral Account') ?>"
                                value='<?php echo $c['redirect_url'] ?>'>
                        </div>
                        <div class=" form-group">
                            <label for="form_nhours"><?php echo xlt("Appointments Advance Notify (Hours)") ?> *</label>
                            <input id="form_nhours" type="text" name="smshours" class="form-control"
                                placeholder="<?php echo xlt('Please enter number of hours before appointment') ?> *"
                                required="required" value='<?php echo $c['smsHours'] ?>'>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="form_message"><?php echo xlt("Message Template") ?> *</label>
                            <span style="font-size:12px;font-style: italic">&nbsp;
<?php echo xlt("Tags") ?>: ***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME***, ***ORG***</span>
                            <textarea id="form_message" type="text" rows="3" name="smsmessage" class="form-control"
                                required="required" value='<?php echo $c['smsMessage'] ?>'><?php echo $c['smsMessage'] ?></textarea>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted"><strong>*</strong> <?php echo xlt("These fields are required.") ?> </p>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm pull-right" value=""><?php echo xlt("Save") ?></button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
