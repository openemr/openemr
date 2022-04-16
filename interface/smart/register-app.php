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
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\RestControllers\AuthorizationController;

// not sure if we need the site id or not...
$ignoreAuth = true;
require_once("../globals.php");
require_once("./../../_rest_config.php");

// exit if fhir api is not turned on
if (empty($GLOBALS['rest_fhir_api'])) {
    die(xlt("Not Authorized"));
}

// This code allows configurable positioning in the login page
$logoarea = "py-2 px-2 py-md-3 px-md-5 order-1 bg-primary";
$formarea = "py-3 px-2 p-sm-5 bg-white order-2";
$loginrow = "row login-row bg-white shadow-lg align-items-center my-sm-5";

// Apply these classes to the logo area if the login page is left or right
$lrArr = ['left', 'right'];
$logoarea .= (in_array($GLOBALS['login_page_layout'], $lrArr)) ? " col-md-6" : " col-md-12";
$formarea .= (in_array($GLOBALS['login_page_layout'], $lrArr)) ? " col-md-6" : " col-md-12";

// More finite control on a per-setting basis
switch ($GLOBALS['login_page_layout']) {
    case 'left':
        $logoarea .= " order-md-2";
        $formarea .= " order-md-1";
        break;

    case 'right':
        $logoarea .= " order-md-1";
        $formarea .= " order-md-2";
        break;

    default:
        $logoarea .= " order-1";
        $formarea .= " col-12";
        $loginrow .= " login-row-center";
        break;
}

// TODO: adunsulag find out where our openemr name comes from
$openemr_name = $openemr_name ?? '';

$scopeRepo = new ScopeRepository(RestConfig::GetInstance());
$scopes = $scopeRepo->getCurrentSmartScopes();
// TODO: adunsulag there's gotta be a better way for this url...
$fhirRegisterURL = AuthorizationController::getAuthBaseFullURL() . AuthorizationController::getRegistrationPath();
$audienceUrl = (new ServerConfig())->getFhirUrl();
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
                    ,"scope": []
                    ,"jwks_uri": ""
                    ,"jwks": ""
                };
                appRegister.client_name = document.querySelector('#appName').value;
                let redirect_uri = document.querySelector("#redirectUri").value;
                appRegister.redirect_uris.push(redirect_uri);
                // not sure we need logout redirect right now
                appRegister.post_logout_redirect_uris.push(document.querySelector("#logoutURI").value);
                appRegister.initiate_login_uri = document.querySelector("#launchUri").value;
                appRegister.contacts.push(document.querySelector("#contactEmail").value);
                appRegister.jwks_uri = document.querySelector("#jwksUri").value;
                appRegister.jwks = document.querySelector("#jwks").value;
                appRegister.application_type = document.querySelector("input[name='appType']:checked").value || "private";

                if (appRegister.jwks.trim() != "") {
                    try {
                        appRegister.jwks = JSON.parse(appRegister.jwks);
                    }
                    catch (error) {
                        console.error(error);
                        alert(<?php echo xlj("Your JWKS is invalid"); ?>);
                        return;
                    }
                }

                let scopes = [];
                let scopeInputs =  document.querySelectorAll('input.app-scope:checked');
                for (let scope of scopeInputs) {
                    if (appRegister.application_type != 'private')
                    {
                        // if we are not a private app don't let offline_access or system scopes be granted
                        // NOTE: this is just a convenience as the server prevents it too.
                        if (scope.value == 'offline_access'
                            || scope.value.match(/^system\//)) {
                            continue;
                        }
                    }
                    scopes.push(scope.value);
                }
                appRegister.scope = scopes.join(" "); // combine the scopes selected.

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

            function toggleSelectAll(evt) {
                let target = evt.target;
                let hiddenClass = 'd-none';
                let alternateSelector = target.classList.contains('toggle-on') ? 'toggle-off' : 'toggle-on';
                let alternate = document.querySelector('.select-all-toggle.' + alternateSelector);

                if (!alternate) {
                    throw new Error("Alternate dom element missing for id '.select-all-toggle." + alternateSelector + "'");
                }

                if (target.classList.contains(hiddenClass)) {
                    target.classList.remove(hiddenClass);
                    alternate.classList.add(hiddenClass);
                } else {
                    target.classList.add(hiddenClass);
                    alternate.classList.remove(hiddenClass);
                }

                let inputs = document.querySelectorAll('input.app-scope');
                let isChecked = target.classList.contains('toggle-on') ? true : false;
                for (let scope of inputs) {
                    scope.checked = isChecked;
                }
            }
            function hideNodeFunction(node)
            {
                if (node.checked !== undefined)
                {
                    node.checked = false;
                }
                node.parentNode.classList.add("d-none");
            }
            function showNodeFunction(node)
            {
                if (node.checked !== undefined)
                {
                    node.checked = true;
                }

                if (node.parentNode.classList.contains('d-none')) {
                    node.parentNode.classList.remove("d-none");
                }
            }
            function togglePatientTypeFields(event) {
                if (!event.target)
                {
                    return;
                }
                let val = event.target.value;
                if (val == 'single') {
                    document.querySelectorAll("input[value^='user/']").forEach(hideNodeFunction);
                    document.querySelectorAll("input[value^='patient/']").forEach(showNodeFunction);
                    toggleSystemFunctionality(false);
                } else if (val == 'multiple') {
                    toggleSystemFunctionality(false);
                    document.querySelectorAll("input[value^='user/']").forEach(showNodeFunction);
                    document.querySelectorAll("input[value^='patient/']").forEach(hideNodeFunction);
                } else if (val == 'client') {
                    document.querySelectorAll("input[value^='user/']").forEach(hideNodeFunction);
                    document.querySelectorAll("input[value^='patient/']").forEach(hideNodeFunction);
                    toggleSystemFunctionality(true);
                } else if (val == 'all') {

                    let selected =document.querySelector("input[name='appType']:checked");
                    if (selected && selected.value == "private") {
                        toggleSystemFunctionality(true);
                    }
                    document.querySelectorAll("input[value^='user/']").forEach(showNodeFunction);
                    document.querySelectorAll("input[value^='patient/']").forEach(showNodeFunction);
                }
            }
            function toggleAppTypeFields(event)
            {
                if (!event.target)
                {
                    return;
                }
                let val = event.target.value;

                if (val === 'private')
                {
                    toggleSystemFunctionality(true);
                    document.querySelectorAll("input[value='offline_access']").forEach(showNodeFunction);
                    document.querySelectorAll("#clientSecretID").forEach(showNodeFunction);
                    document.querySelectorAll("#patientTypeClient").forEach(showNodeFunction);
                    document.querySelectorAll("label[for='patientTypeClient']").forEach(showNodeFunction);

                }
                else if (val == 'public')
                {
                    toggleSystemFunctionality(false);
                    document.querySelectorAll("input[value='offline_access']").forEach(hideNodeFunction);
                    document.querySelectorAll("#clientSecretID").forEach(hideNodeFunction);
                    document.querySelectorAll("#patientTypeClient").forEach(hideNodeFunction);
                    document.querySelectorAll("label[for='patientTypeClient']").forEach(hideNodeFunction);
                }
            }

            function toggleSystemFunctionality(enabled) {
                if (enabled) {
                    document.getElementById('systemSetup').classList.remove("d-none");
                    document.querySelectorAll("input[value^='system/']").forEach(showNodeFunction);
                } else {
                    document.querySelectorAll("input[value^='system/']").forEach(hideNodeFunction);
                    document.getElementById('systemSetup').classList.add("d-none");
                }
            }

            window.addEventListener('load', function() {
                var scopeSelectAll = document.querySelectorAll('.select-all-toggle');
                for (var element of scopeSelectAll) {
                    element.addEventListener('click', toggleSelectAll);
                }

                var appTypes = document.querySelectorAll("input[name='appType']");
                for (var element of appTypes)
                {
                    element.addEventListener('click', toggleAppTypeFields);
                }

                var patientTypes = document.querySelectorAll("input[name='patientType']");
                for (var element of patientTypes)
                {
                    element.addEventListener('click', togglePatientTypeFields);
                }

                document.querySelector('#submit').addEventListener('click', registerApp);
            });
        })(window, <?php echo js_escape($fhirRegisterURL); ?>);
    </script>
</head>
<body class="register-app">
<form id="app_form" method="POST" autocomplete="off">
    <div class="<?php echo $loginrow; ?> card m-5">
        <div class="<?php echo attr($logoarea); ?>">
            <?php $extraLogo = $GLOBALS['extra_logo_login']; ?>
            <?php if ($extraLogo) { ?>
                <div class="text-center">
                    <span class="d-inline-block w-40">
                        <?php echo file_get_contents($GLOBALS['images_static_absolute'] . "/login-logo.svg"); ?>
                    </span>
                    <span class="d-inline-block w-15 login-bg-text-color"><i class="fas fa-plus fa-2x"></i></span>
                    <span class="d-inline-block w-40">
                        <?php echo $logocode; ?>
                    </span>
                </div>
            <?php } else { ?>
                <div class="mx-auto m-4 w-75">
                    <?php echo file_get_contents($GLOBALS['images_static_absolute'] . "/login-logo.svg"); ?>
                </div>
            <?php } ?>
            <?php if ($GLOBALS['show_label_login']) { ?>
            <div class="text-center login-title-label">
                    <?php echo text($openemr_name); ?>
            </div>
            <?php } ?>
            <?php
            // Figure out how to display the tiny logos
            $t1 = $GLOBALS['tiny_logo_1'];
            $t2 = $GLOBALS['tiny_logo_2'];
            if ($t1 && !$t2) {
                echo $tinylogocode1;
            } if ($t2 && !$t1) {
                echo $tinylogocode2;
            } if ($t1 && $t2) { ?>
                <div class="row mb-3">
                    <div class="col-sm-6"><?php echo $tinylogocode1;?></div>
                    <div class="col-sm-6"><?php echo $tinylogocode2;?></div>
                </div>
            <?php } ?>
            <p class="text-center lead font-weight-normal login-bg-text-color text-white"><?php echo xlt('The most popular open-source Electronic Health Record and Medical Practice Management solution.'); ?></p>
            <p class="text-center small"><a href="../../acknowledge_license_cert.html" class="login-bg-text-color text-white" target="main"><?php echo xlt('Acknowledgments, Licensing and Certification'); ?></a></p>
        </div>
        <div class="<?php echo $formarea; ?>">
            <h3 class="card-title text-center"><?php echo xlt("App Registration Form"); ?></h3>
            <div>
                <div class="row">
                    <div class="col">
                        <h2><?php echo xlt("Application Type"); ?></h2>
                        <p><?php echo xlt("Confidential clients must be able to securely safeguard a secret."); ?></p>
                        <p><?php echo xlt("If your application cannot keep a secret (such as an application that runs in a web browser) you should use the public application type."); ?></p>
                    </div>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" id="appTypeConfidential" name="appType" value="private" checked="checked"/>
                    <label for="appTypeConfidential" class="form-check-label pr-2"><?php echo xlt('Confidential'); ?></label>
                    <input type="radio" class="form-check-input" id="appTypePublic" name="appType" value="public"/>
                    <label for="appTypePublic" class="form-check-label"><?php echo xlt('Public'); ?></label>
                </div>
                <div class="row">
                    <div class="col">
                        <h2><?php echo xlt("Application Context"); ?></h2>
                    </div>
                </div>
                <div class="row pl-3 pr-3">
                    <div class="col">
                        <input type="radio" class="form-check-input" id="patientTypeSingle" name="patientType" value="single"/>
                        <label for="patientTypeSingle" class="form-check-label pr-3"><?php echo xlt('Single Patient Application'); ?></label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="patientTypeMultiple" name="patientType" value="multiple"/>
                        <label for="patientTypeMultiple" class="form-check-label pr-3"><?php echo xlt('Multiple Patients Application'); ?></label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="patientTypeClient" name="patientType" value="client"/>
                        <label for="patientTypeClient" class="form-check-label pr-3"><?php echo xlt('System Client Application'); ?></label>
                    </div>
                    <div class="col">
                        <input type="radio" class="form-check-input" id="patientTypeAll" name="patientType" value="all" checked="checked"/>
                        <label for="patientTypeAll" class="form-check-label"><?php echo xlt('Multipurpose Application'); ?></label>
                    </div>
                </div>
                <div class="row">
                    <div class="col alert alert-info">
                        <p><?php echo xlt("system, user, and offline_access scopes require confidential app permissions."); ?></p>
                        <p><?php echo xlt("Confidential apps are applications that are able to safely and securely store a secret. Browser based and many mobile applications do not satisfy this security constraint"); ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="appName" class="text-right"><?php echo xlt('App Name'); ?>:</label>
                    <input type="text" class="form-control" id="appName" name="appName" placeholder="<?php echo xla('App Name'); ?>" />
                </div>
                <div class="form-group">
                    <label for="contactEmail" class="text-right"><?php echo xlt('Contact Email'); ?>:</label>
                    <input type="text" class="form-control" id="contactEmail" name="contactEmail" placeholder="<?php echo xla('Email'); ?>" />
                </div>
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
                    <?php echo xlt("Scopes Requested"); ?>:
                    <input type="button" class="select-all-toggle toggle-on btn btn-secondary d-none" value="<?php echo xlt('Select all'); ?>" />
                    <input type="button" class="select-all-toggle toggle-off btn btn-secondary" value="<?php echo xlt('Unselect all'); ?>" />
                    <input type="button" class="select-single-patient btn btn-secondary d-none" value="<?php echo xlt('Single Patient Application'); ?>" />
                    <input type="button" class="select-multi-patient btn btn-secondary d-none" value="<?php echo xlt('Multiple Patients Application'); ?>" />
                    <div class="list-group">
                    <?php foreach ($scopes as $scope) : ?>
                        <label class="list-group-item m-0">
                            <input type="checkbox" class='app-scope' name="scope[<?php echo attr($scope); ?>]" value="<?php echo attr($scope); ?>" checked>
                            <?php echo xlt($scope); ?>
                        </label>
                    <?php endforeach; ?>
                    </div>
                </div>
                <div class="row" id="systemSetup">
                    <div class="col">
                        <h3 class="text-center"><?php echo xlt("The following items are required for System Scopes"); ?></h3>
                        <hr />
                        <div class="form-group">
                            <label for="jwksUri" class="text-right"><?php echo xlt('JSON Web Key Set URI'); ?>:</label>
                            <input type="text" class="form-control" id="jwksUri" name="jwksUri" placeholder="<?php echo xla('URI'); ?>" />
                        </div>
                        <div class="form-group">
                            <label for="jwks" class="text-right"><?php echo xlt('JSON Web Key Set (Note a hosted web URI is preferred and this feature may be removed in future SMART versions)'); ?>:</label>
                            <textarea class="form-control" id="jwks" name="jwks" rows="5"></textarea>
                        </div>
                    </div>
                </div>

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
                    <div class="form-group">
                        <label for="audURL" class="text-right"><?php echo xlt('Aud URI (use this in the "aud" claim of your JWT)'); ?></label>
                        <input type="text" disabled class="form-control" id="audURL" name="audURL" value="<?php echo attr($audienceUrl); ?>" />
                    </div>
                </div>
                <div class="form-group errorResponse hidden">
                    <div id="errorResponseContainer">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
</body>
</html>
