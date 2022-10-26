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
    </style>
</head>
<body>
<div class="container-fluid">
    <div>
        <h1><?php echo xlt("Welcome"); ?></h1>
        <p><?php echo xlt("To get your telehealth configuration information"); ?>,
            <?php echo xlt("Please select the subscription button below."); ?></p>
        <p>
            <?php echo xlt("There is a 7 day trial period included with the subscription"); ?>
        </p>

    </div>
    <div id="paypal-button-container-P-25N86285GY8825203MMWZEIY"></div>
    <script src="https://www.paypal.com/sdk/js?client-id=AUQ1tRakVcTZ0wIOjQ0CicVxB8K47tXo4l8PucxwmmB1v_LIE4-_pJ-kEZf3fsk3uKZuhb_3WuDasVBC&vault=true&intent=subscription" data-sdk-integration-source="button-factory"></script>
    <script>
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
            onApprove: function(data, actions) {
                alert(data.subscriptionID); // You can add optional success message for the subscriber here
            }
        }).render('#paypal-button-container-P-25N86285GY8825203MMWZEIY'); // Renders the PayPal button
    </script>
    <div>
        <p><h3><a href="https://credentials.affordablecustomehr.com/customer"><?php echo xlt("Click Here to get credentials after subscribing"); ?></a></h3></p>
    </div>
    <div>
        <p><?php echo xlt('Questions') . ": ";
        echo " sherwin@affordablecustomehr.com"; ?></p>
    </div>
</div>

</body>
</html>