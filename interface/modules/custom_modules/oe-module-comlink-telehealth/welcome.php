<?php

/**
 * package   OpenEMR
 *  link      http://www.open-emr.org
 *  author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

use OpenEMR\Core\Header;

require_once dirname(__FILE__, 4) . "/globals.php";

use Comlink\OpenEMR\Modules\TeleHealthModule\Bootstrap;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;

$kernel = $GLOBALS['kernel'];
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);
$globalConfig = $bootstrap->getGlobalConfig();
$subscriptionId = $globalConfig->getGlobalSetting(TelehealthGlobalConfig::COMLINK_TELEHEALTH_PAYMENT_SUBSCRIPTION_ID) ?? '';
$isCoreConfigured = $globalConfig->isTelehealthCoreSettingsConfigured() === true;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo xlt("Welcome to the module"); ?></title>
    <?php echo Header::setupHeader(); ?>
    <style>
        .note {
            color: #942a25;
            font-size: medium;
            font-weight: bold;
        }
        .nav-item:active {
            background-color: var(--primary);
        }
    </style>
    <script>
        window.document.addEventListener("DOMContentLoaded", function() {

            document.querySelectorAll('.nav-link').forEach(li => {
                li.addEventListener('click', function(evt) {
                    document.querySelectorAll('.nav-link').forEach(li => li.classList.remove('active'));
                    evt.currentTarget.classList.add('active');
                    if (!evt.currentTarget.hash) {
                        console.error("failed to find card id");
                        return;
                    }
                    let panelId = evt.currentTarget.hash;
                    panelId = panelId.replace("#", "");
                    let panels = document.querySelectorAll('.card');
                    panels.forEach(p => p.classList.add('d-none'));
                    let panel = document.getElementById(panelId);
                    panel.classList.remove('d-none');
                });
            });
        })
    </script>
</head>
<body>
<div class="container-fluid">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#setup">Setup</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#docs"><?php echo xlt("Docs"); ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#support"><?php echo xlt("Support"); ?></a>
        </li>
    </ul>
    <section id="setup" class="card">
        <div class="card-header">
            <h4><?php echo xlt("Welcome"); ?></h4>
        </div>
        <div class="card-body">

            <p><?php echo xlt("To get your telehealth configuration information you must first signup for a subscription trial and then setup your telehealth credentials"); ?>,
                <?php echo xlt("Please select the subscription button below."); ?></p>
            <div id="step-1-subscription-signup" class="<?php if (!empty($subscriptionId)) {
                echo 'd-none';} ?>">
                <h2><?php echo xlt("Step 1 - Subscription Signup"); ?></h2>
                <p>
                    <?php echo xlt("There is a 14 day free trial period included with the subscription"); ?>
                </p>
                <div id="paypal-button-container-P-4FU17140CF274883DMREHHFA"></div>
                <script src="https://www.paypal.com/sdk/js?client-id=AU0Ql21fQp5jd-Vn2jPU1dCdTse_DFpGiBjfBAsrBW9lEeNNBmEm7NpKim6W3vt3RxOVH-Wa_VFwz3mw&vault=true&intent=subscription" data-sdk-integration-source="button-factory"></script>
            </div>
            <div id="step-1-subscription-signup-complete" class="<?php if (empty($subscriptionId)) {
                echo 'd-none';} ?>">
                <h2><?php echo xlt("Step 1 - Subscription Signup"); ?> - <span class="text-success"><?php echo xlt("Complete"); ?></span></h2>
                <div class="alert alert-success <?php if (empty($subscriptionId)) {
                    echo 'd-none';} ?>">
                    <h3><?php echo xlt("Your payment subscription has been created."); ?></h3>
                    <p><?php echo xlt("Your Subscription ID / Profile ID is the following"); ?></p>
                    <h3><input type="text" disabled="disabled" id="paypal-subscription-id" value="<?php echo attr($subscriptionId); ?>" /><i class="ml-2 fa fa-copy" id="btnCopy"></i></h3>
                    <p><?php echo xlt("Copy your subscription ID / Profile ID for obtaining your telehealth credentials"); ?></p>
                    <p><small><?php echo xlt("You have been sent an email from Paypal with your subscription information"); ?></small></p>
                </div>
            </div>
            <script>
                // handles the copying of the subscription id for the client's reference.
                function btnCopy() {
                    try {
                        let el = document.querySelector('#paypal-subscription-id');
                        if (el) {
                            el.select();
                        }
                        let text = el.value;
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(() => {
                                alert(<?php echo xlj("Copied to clipboard"); ?>);
                            }, (error) => {
                                console.error(error);
                                alert(<?php echo xlj("Failed to copy to clipboard"); ?>);
                            });
                        } else {
                            document.execCommand("copy");
                            alert(<?php echo xlj("Copied to clipboard"); ?>);
                        }
                    } catch (error) {
                        console.error(error);
                        alert(<?php echo xlj("Failed to copy to clipboard"); ?>);
                    }
                }
                function paypalOnApproveHandler(data, actions) {
                    let el = document.querySelector('.alert-success');
                    el.classList.remove("d-none");
                    let el2 = document.querySelector('#paypal-subscription-id');
                    el2.value = data.subscriptionID;

                    let el3 = document.querySelector('#step-1-subscription-signup-complete');
                    el3.classList.remove("d-none");

                    let payPalSection = document.querySelector('#step-1-subscription-signup');
                    payPalSection.classList.add('d-none');

                    let sinupLink = document.querySelector('#signupLink');
                    // if we want to pass along the subscription_id we can do that, right now that causes the signup page
                    // to fail.
                    // sinupLink.href = sinupLink.href + "?subscription_id=" + encodeURIComponent(data.subscriptionID);
                }
                window.addEventListener("DOMContentLoaded", function() {
                    let btnCopyElement = document.querySelector('#btnCopy');
                    btnCopyElement.addEventListener('click', btnCopy);
                    // uncomment for testing
                    // paypalOnApproveHandler({subscriptionID: 'testingId1'}, {});
                });
                paypal.Buttons({
                    style: {
                        shape: 'rect',
                        color: 'gold',
                        layout: 'vertical',
                        label: 'subscribe'
                    },
                    createSubscription: function(data, actions) {
                        return actions.subscription.create({
                            /* Creates the subscription */
                            plan_id: 'P-4FU17140CF274883DMREHHFA'
                        });
                    },
                    onApprove: paypalOnApproveHandler
                }).render('#paypal-button-container-P-4FU17140CF274883DMREHHFA'); // Renders the PayPal button
            </script>
            <div class="<?php if ($isCoreConfigured) {
                echo 'd-none';} ?>">
                <h2><?php echo xlt("Step 2 - Credentials Signup"); ?></h2>
                <p><h3><a id='signupLink' href="https://credentials.affordablecustomehr.com/customer"><?php echo xlt("Click Here to get credentials after subscribing"); ?></a></h3></p>
            </div>
            <div class="<?php if (!$isCoreConfigured) {
                echo 'd-none';} ?>">
                <h2><?php echo xlt("Step 2 - Credentials Signup"); ?> - <span class="text-success"><?php echo xlt("Complete"); ?></span></h2>
                <p><?php echo xlt("Your credentials have been setup and saved in the Telehealth configuration"); ?></p>
            </div>
            <div>
                <h3><?php echo xlt("Step 3 - Complete Telehealth Configuration"); ?></h3>
                <p><?php echo xlt("Finish the telehealth configuration and verify your setup is fully functionining in the Admin -> Config -> Telehealth settings section"); ?></p>
            </div>
        </div>
    </section>
    <section id="docs" class="card d-none">
        <div class="card-header">
            <h3><?php echo xlt("Module Instructions"); ?></h3>
        </div>
        <div class="card-body">
            <div class="row mb-5">
                <div class="col">
                    <h4><?php echo xlt("Access the documentation by clicking the link below"); ?></h4>
                    <a href="https://www.open-emr.org/wiki/index.php/Comlink_Telehealth" target="_blank"><?php echo xlt("Comlink Telehealth Module Instructions"); ?></a>
                    <p><?php echo xlt("The documentation will open in a new tab"); ?></p>
                </div>
            </div>
        </div>
    </section>
    <section id="support" class="card d-none">
        <div class="card-header">
            <h3><?php echo xlt("Module Support"); ?></h3>
        </div>
        <div class="card-body">
            <div class="row mb-5">
                <div class="col">
                    <h4><?php echo xlt('Questions'); ?></h4>
                    <p>
                        <?php echo xlt("Email support"); ?>: <a href="mailto:support@ehrcommunityhelpdesk.com">EHR Community Help Desk</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>

</body>
</html>
