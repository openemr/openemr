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

            <p><?php echo xlt("To get your telehealth configuration information"); ?>,
                <?php echo xlt("Please select the subscription button below."); ?></p>
            <p>
                <?php echo xlt("There is a 7 day trial period included with the subscription"); ?>
            </p>
            <div id="paypal-button-container-P-25N86285GY8825203MMWZEIY"></div>
            <script src="https://www.paypal.com/sdk/js?client-id=AUQ1tRakVcTZ0wIOjQ0CicVxB8K47tXo4l8PucxwmmB1v_LIE4-_pJ-kEZf3fsk3uKZuhb_3WuDasVBC&vault=true&intent=subscription" data-sdk-integration-source="button-factory"></script>
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

                    let payPalSection = document.querySelector('#paypal-button-container-P-25N86285GY8825203MMWZEIY');
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
                            plan_id: 'P-25N86285GY8825203MMWZEIY',
                            quantity: 1 // The quantity of the product for a subscription
                        });
                    },
                    onApprove: paypalOnApproveHandler
                }).render('#paypal-button-container-P-25N86285GY8825203MMWZEIY'); // Renders the PayPal button
            </script>
            <div class="alert alert-success d-none">
                <h1><?php echo xlt("Your subscription trial has been created."); ?></h1>
                <p><?php echo xlt("Your Subscription ID / Profile ID is the following"); ?></p>
                <h3><input type="text" disabled="disabled" id="paypal-subscription-id"></input> <i class="fa fa-copy" id="btnCopy"></i></h3>
                <p><?php echo xlt("Copy your subscription ID / Profile ID for obtaining your telehealth credentials"); ?></p>
                <p><small><?php echo xlt("You have been sent an email from Paypal with your subscription information"); ?></small></p>
            </div>
            <div>
                <p><h3><a id='signupLink' href="https://credentials.affordablecustomehr.com/customer"><?php echo xlt("Click Here to get credentials after subscribing"); ?></a></h3></p>
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
                        <?php echo xlt("Email support"); ?>: <a href="mailto:sherwin@affordablecustomehr.com">sherwin@affordablecustomehr.com</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>

</body>
</html>
