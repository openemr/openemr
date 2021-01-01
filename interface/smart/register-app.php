<?php

/**
 * Login screen.
 *
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Scott Wakefield <scott.wakefield@gmail.com>
 * @author  ViCarePlus <visolve_emr@visolve.com>
 * @author  Julia Longtin <julialongtin@diasp.org>
 * @author  cfapress
 * @author  markleeds
 * @author  Tyler Wrenn <tyler@tylerwrenn.com>
 * @author  Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;
use OpenEMR\RestControllers\AuthorizationController;
use OpenEMR\Services\FacilityService;

// not sure if we need the site id or not...
$ignoreAuth = true;
require_once("../globals.php");

// exit if fhir api is not turned on
if (empty($GLOBALS['rest_fhir_api']) && empty($GLOBALS['rest_portal_fhir_api'])) {
    die(xlt("Not Authorized"));
}

// This code allows configurable positioning in the login page
$loginrow = "row login-row align-items-center m-5";

if ($GLOBALS['login_page_layout'] == 'left') {
    $logoarea = "col-md-6 login-bg-left py-3 px-5 py-md-login order-1 order-md-2";
    $formarea = "col-md-6 p-5 login-area-left order-2 order-md-1";
} else if ($GLOBALS['login_page_layout'] == 'right') {
    $logoarea = "col-md-6 login-bg-right py-3 px-5 py-md-login order-1 order-md-1";
    $formarea = "col-md-6 p-5 login-area-right order-2 order-md-2";
} else {
    $logoarea = "col-12 login-bg-center py-3 px-5 order-1";
    $formarea = "col-12 p-5 login-area-center order-2";
    $loginrow = "row login-row login-row-center align-items-center";
}

// TODO: adunsulag there's gotta be a better way for this url...
$fhirRegisterURL = AuthorizationController::getAuthBaseFullURL() . AuthorizationController::getRegistrationPath();

?>
<html>
<head>
    <?php Header::setupHeader(); ?>

    <title><?php echo xlt('OpenEMR App Registration'); ?></title>
    <style>
        .hidden {
            display: none;
        }
        .errorResponse {
            color: red;
        }
    </style>
    <script>
        (function(window, fhirRegistrationURL) {
            function registerApp() {
                let form = document.querySelector('form[name="app_form]');
                let appRegister = {
                    "application_type": "private"
                    ,"redirect_uris": []
                    ,"initiate_login_uri": ""
                    ,"post_logout_redirect_uris": []
                    ,"client_name": ""
                    ,"token_endpoint_auth_method": "client_secret_post"
                    ,"contacts": []
                    ,"scope": "openid email phone address api:oemr api:fhir api:port api:pofh launch"
                };
                appRegister.client_name = document.querySelector('#appName').value;
                let redirect_uri = document.querySelector("#redirectUri").value;
                appRegister.redirect_uris.push(redirect_uri);
                // not sure we need logout redirect right now
                appRegister.post_logout_redirect_uris.push(document.querySelector("#logoutURI").value);
                appRegister.initiate_login_uri = document.querySelector("#launchUri").value;
                appRegister.contacts.push(document.querySelector("#contactEmail").value);

                fetch(fhirRegistrationURL, {
                    method: 'POST', // *GET, POST, PUT, DELETE, etc.
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(appRegister) // body data type must match "Content-Type" header
                }).then(result => {
                    if (!result.ok) {
                        return result.json().then(json => { throw json });
                    }
                    return result.json();
                }).then(resultJSON => {
                    console.log(resultJSON);
                    document.querySelector(".apiResponse").classList.remove("hidden");
                    document.querySelector(".errorResponse").classList.add("hidden");
                    document.querySelector("#clientID").value = resultJSON.client_id;
                    document.querySelector("#clientSecretID").value = resultJSON.client_secret;
                })
                .catch(error => {
                    console.error(error);
                    let msgText = error.message;
                    if (!msgText) {
                        msgText = JSON.stringify(error);
                    }
                    document.querySelector(".apiResponse").classList.add("hidden");
                    document.querySelector(".errorResponse").classList.remove("hidden");
                    document.querySelector("#errorResponseContainer").textContent = msgText;
                });
                return false;
            }

            window.addEventListener('load', function() {
                document.querySelector('#submit').addEventListener('click', registerApp);
            });
        })(window, <?php echo js_escape($fhirRegisterURL); ?>);
    </script>
</head>
<body class="login">
<form id="app_form" method="POST" autocomplete="off">
    <div class="<?php echo $loginrow; ?>">
        <div class="<?php echo $formarea; ?>">
            <h3><?php echo xlt("App Registration Form"); ?></h3>
            <div class="form-group">
                <label for="appName" class="text-right"><?php echo xlt('App Name'); ?>:</label>
                <input type="text" class="form-control" id="appName" name="appName" placeholder="<?php echo xla('App Name'); ?>" />
            </div>
            <div class="form-group">
                <label for="contactEmail" class="text-right"><?php echo xlt('Contact Email'); ?>:</label>
                <input type="text" class="form-control" id="contactEmail" name="contactEmail" placeholder="<?php echo xla('Email'); ?>" />
            <div class="form-group">
                <label for="redirectUri" class="text-right"><?php echo xlt('App Redirect URI'); ?>:</label>
                <input type="text" class="form-control" id="redirectUri" name="redirectUri" placeholder="<?php echo xla('URI'); ?>" />
            </div>
            <div class="form-group">
                <label for="launchUri" class="text-right"><?php echo xlt('App Launch URI'); ?>:</label>
                <input type="text" class="form-control" id="launchUri" name="launchUri" placeholder="<?php echo xla('URI'); ?>" />
            </div>
            <div class="form-group">
                <label for="logoutURI" class="text-right"><?php echo xlt('App Logout URI'); ?>:</label>
                <input type="text" class="form-control" id="logoutURI" name="logoutURI" placeholder="<?php echo xla('URI'); ?>" />
            </div>
            <!-- TODO: adunsulag display the list of scopes that can be requested here -->
            <div class="form-group">
                <input type="button" class="form-control btn btn-primary" id="submit" name="submit" value="<?php echo xla('Submit'); ?>" (onClick)="registerApp();" />
            </div>
            <div class="apiResponse hidden">
                <div class="form-group">
                    <label for="clientID" class="text-right"><?php echo xlt('Client APP ID:'); ?></label>
                    <textarea class="form-control" id="clientID" name="clientID"></textarea>
                </div>
                <div class="form-group">
                    <label for="clientSecretID" class="text-right"><?php echo xlt('Client Secret APP ID:'); ?></label>
                    <textarea class="form-control" id="clientSecretID" name="clientSecretID"></textarea>
                </div>
            </div>
            <div class="form-group errorResponse hidden">
                <div id="errorResponseContainer">
                </div>
            </div>
            <p class="text-center lead font-weight-normal login-bg-text-color"><?php echo xlt('The most popular open-source Electronic Health Record and Medical Practice Management solution.'); ?></p>
            <p class="text-center small"><a href="../../acknowledge_license_cert.html" class="login-bg-text-color" target="main"><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></a></p>
        </div>

    </div>
</form>
</body>
</html>
