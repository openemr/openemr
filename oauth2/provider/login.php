<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionUtil;

if ($oauthLogin !== true) {
    $message = xlt("Error. Not authorized");
    SessionUtil::oauthSessionCookieDestroy();
    echo $message;
    exit();
}

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<html>
<head>
    <title><?php echo xlt("OpenEMR Authorization"); ?></title>
    <?php Header::setupHeader(); ?>
    <script src="<?php echo $GLOBALS['webroot'] ?>/library/js/u2f-api.js"></script>
</head>
<body class="container-fluid bg-dark">
    <div class="row h-100 w-100 justify-content-center align-items-center">
        <div class="col-sm-6 bg-light text-dark">
            <div class="text-md-center">
                <?php if (empty($authorize) && empty($mfaRequired)) { ?>
                    <h4 class="mb-4 mt-1"><?php echo xlt("Sign In"); ?></h4>
                <?php } elseif (empty($authorize) && !empty($mfaRequired)) { ?>
                    <h4 class="mb-4 mt-1"><?php echo xlt('MFA Verification'); ?></h4>
                <?php } else { ?>
                    <h4 class="mb-4 mt-1"><?php echo xlt("Authorizing"); ?></h4>
                <?php } ?>
            </div>
            <hr />
            <?php if (!empty($authorize)) { ?>
                <div class="row w-100">
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-body pt-1">
                                <h5 class="card-title text-sm-center"><?php echo xlt("Scopes"); ?><hr /></h5>
                                <ul class="pl-2 mt-1">
                                <?php {
                                $scopes = explode(' ', $_SESSION['scopes']);
                                foreach ($scopes as $key) {
                                    echo "<li class='col-text'><strong>" . text($key) . "</strong>  " . "</li>";
                                }
                                } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card">
                            <div class="card-body pt-1">
                                <h5 class="card-title text-sm-center"><?php echo xlt("Claims"); ?><hr /></h5>
                                <ul class="pl-2 mt-1">
                                <?php {
                                foreach ($_SESSION['claims'] as $key => $value) {
                                    $key_n = explode('_', $key);
                                    if (stripos($_SESSION['scopes'], $key_n[0]) === false) {
                                        continue;
                                    }
                                    if ((int)$value === 1) {
                                        $value = 'True';
                                    }
                                    $key = ucwords(str_replace("_", " ", $key));
                                    echo "<li class='col-text'><strong>" . text($key) . ":</strong>  " . text($value) . "</li>";
                                }
                                } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <form method="post" name="userLogin" id="userLogin" action="<?php echo $redirect ?>">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('oauth2')); ?>" />
                <?php if (empty($authorize) && empty($mfaRequired)) { ?>
                    <div class="form-group">
                        <input class="form-control" placeholder="<?php echo xla("Email if required"); ?>" type="email" name="email">
                    </div>
                    <div class="form-group"><!-- TODO: remove test values -->
                        <input class="form-control" placeholder="<?php echo xla("Registered username"); ?>" type="text" name="username" value="">
                    </div>
                    <div class="form-group">
                        <input class="form-control" placeholder="******" type="password" name="password" value="">
                    </div>
                <?php } ?>

                <?php if (empty($authorize) && !empty($mfaRequired)) { ?>
                    <?php if (in_array($TOTP, $mfaType)) { ?>
                        <fieldset>
                        <legend><?php echo xlt('Provide TOTP code') ?></legend>
                        </fieldset>
                        <div class="form-group">
                            <input class="form-control" id="totp_token" autocomplete="false" placeholder="<?php echo xlt("Enter required authentication code"); ?>" type="text" name="mfa_token">
                        </div>
                        <div class="form-group">
                            <button type="submit" name="user_role" class="btn btn-primary btn-save" value="api"><?php echo xlt("Authenticate TOTP"); ?></button>
                        </div>
                    <?php } ?>

                    <?php if (in_array($U2F, $mfaType)) { ?>
                        <div class="form-group">
                            <fieldset>
                            <legend><?php echo xlt('Insert U2F Key') ?></legend>
                                <div>
                                    <ul>
                                        <li><?php echo xlt('Insert your key into a USB port and click the Authenticate button below.') ?></li>
                                        <li><?php echo xlt('Then press the flashing button on your key within 1 minute.') ?></li>
                                    </ul>
                                </div>
                            </fieldset>
                            <button type="button" id="authutf" class="btn btn-primary btn-save" onclick="doAuth()"><?php echo xlt('Authenticate U2F') ?></button>
                            <input type="hidden" name="form_requests" value="<?php echo attr($requests ?? '') ?>" />
                            <input type="hidden" name="user_role" value="api">
                        </div>
                    <?php } ?>

                    <div class="form-group">
                        <input class="form-control" type="hidden" value="<?php echo attr($_POST['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group"><!-- TODO: remove test values -->
                        <input class="form-control" type="hidden" name="username" value="<?php echo attr($_POST['username']); ?>">
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="hidden" name="password" value="<?php echo attr($_POST['password']); ?>">
                    </div>
                    <input class="form-control" type="hidden" name="mfa_type" value="TOTP">
                <?php } ?>
                <hr />
                <div class="row">
                    <div class="col-md-12">
                        <?php if (!empty($authorize)) { ?>
                            <div class="btn-group">
                                <button type="submit" name="proceed" value="1" class="btn btn-primary"><?php echo xlt("Authorize"); ?></button>
                            </div>
                        <?php } else { ?>
                            <div class="btn-group">
                                <?php if (empty($mfaRequired)) { ?>
                                    <button type="submit" name="user_role" class="btn btn-outline-primary" value="api"><?php echo xlt("OpenEMR Login"); ?> <i class="fa fa-sign-in-alt"></i></button>
                                    <?php if (!empty($patientRoleSupport)) { ?>
                                        <button type="submit" name="user_role" class="btn btn-outline-info" value="portal-api"><?php echo xlt("Patient Login"); ?> <i class="fa fa-sign-in-alt"></i></button>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <div class="form-check-inline float-right">
                                <input class="form-check-input" type="checkbox" name="persist_login" id="persist_login" value="1">
                                <label for="persist_login" class="form-check-label"><?php echo xlt("Remember Me"); ?></label>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>

<script>
    function doAuth() {
        var f = document.getElementById("userLogin");
        var requests = JSON.parse(f.form_requests.value);
        // The server's getAuthenticateData() repeats the same challenge in all requests.
        var challenge = requests[0].challenge;
        var registeredKeys = new Array();
        for (var i = 0; i < requests.length; ++i) {
            registeredKeys[i] = {"version": requests[i].version, "keyHandle": requests[i].keyHandle};
        }
        u2f.sign(
            <?php echo js_escape($appId ?? ''); ?>,
            challenge,
            registeredKeys,
            function (data) {
                if (data.errorCode && data.errorCode != 0) {
                    alert(<?php echo xlj("Key access failed with error"); ?> +' ' + data.errorCode);
                    return;
                }
                //hide totp input if both on used
                if (document.getElementById('totp_token')) {
                    document.getElementById('totp_token').style.display = 'none';
                }
                //create new mfa_token input
                var elInput = document.createElement('input');
                elInput.setAttribute('type', 'hidden');
                elInput.setAttribute('name', 'mfa_token');
                elInput.setAttribute('value', JSON.stringify(data));
                f.appendChild(elInput);
                f.mfa_type.value = 'U2F';
                f.submit();
            },
            60
        );
    }

</script>

</html>
