<?php

/*
 *
 * @package     OpenEMR Telehealth Module
 * @link        https://lifemesh.ai/telehealth/
 *
 * @author      Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright   Copyright (c) 2021 Lifemesh Corp <telehealth@lifemesh.ai>
 * @license     GNU General Public License 3
 *
 */

require_once dirname(__FILE__, 5) . "/globals.php";
require_once dirname(__FILE__, 2) . "/controller/Container.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\LifeMesh\Container;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
    echo xlt('Not Authorized');
    exit;
}

$getcredentals = sqlQuery("select `username`, `password` from `lifemesh_account`");
if ($getcredentals['username'] == '') {
    die('You are not logged in');
}
$getaccountsummary = new Container();
$password = $getaccountsummary->getDatabase();
$pass = $password->cryptoGen->decryptStandard($getcredentals['password']);
$summaryurl = $getaccountsummary->getAppDispatch();
$url = 'accountSummary';
$data = $summaryurl->apiRequest($getcredentals['username'], $pass, $url);
$url = 'wipeaccount.php';
$reset_cancel_url = 'account_reset_cancel.php';
$setup = '../moduleConfig.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt('Account Summary') ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body>
<div class="container">
<h2><?php echo xlt('Account Summary') ?></h2>
    <?php
        $j_data = json_decode($data, true);
    ?>
    <div id="summary">
        <p><strong>Account</strong><br>
        <?php echo text($getcredentals['username']); ?></p>
        <p></p>
        <p><strong>Account Status</strong><br>
        <?php echo ($j_data['status'] == "active") ? "Subscription is active" : "Subscription is not active"; ?></p>
        <p></p>
        <p><strong>Billed Telehealth Sessions this Billing Cycle</strong><br>
        <?php print text($j_data['session_count']); ?></p>
        <p></p>
        <p><strong>Billing Cycle Ends</strong><br>
        <?php print text(gmdate("Y-m-d TH:i:s\Z", $j_data['billing_period_end'])); ?></p>
    </div>
    <div id="plans">
        <p><strong>Telehealth Pricing Tiers</strong><br>
        Our pricing starts at US$99.00 for the first 50 Telehealth sessions.<br>
        Every subsequent bundle of 50 sessions will be charged with a US$5.00 <br>
        discount until a maximum discount of US$50.00 is reached. For example, <br>
        a monthly usage of 121 sessions would cost US$282.00 (eg. US$99.00 + <br>
        US$94.00 + US$89.00).<br>
        <br>
        First 50 Telehealth Sessions costs $99.00<br>
        Next 51 - 100 costs USD$94.00<br>
        Next 101 - 150 costs USD$89.00<br>
        ...<br>
        Next 451 - 500 costs USD$54.00<br>
        Next 501 - 550 costs USD$50.00<br>
        Next 551 - 600 costs USD$50.00<br>
        etc.</p>
    </div>
    <div id="acctmgr">
        <p><strong>Account Management</strong><br>
        <p>Do you want to reset your account password? <button class="btn btn-primary" onclick="resetPassword()" style="background-color: #C24511; border-color: #C24511;">Click Here</button></p>
        <?php if ($j_data['status'] == "active") { ?>
            <p>Do you want to cancel your subscription? <button class="btn btn-primary" onclick="cancelSubscription()" style="background-color: #C24511; border-color: #C24511;">Click Here</button></p>
        <?php } else { ?>
            <form method="post" action="../stripe/server/create-checkout-session.php" target="_blank">
                <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken('lifemesh')); ?>" />
                <input name="email" type="hidden" value="<?php echo text($getcredentals['username']); ?>">
                <p>Don't have an active subscription? <button class="btn btn-primary" style="background-color: #C24511; border-color: #C24511;">Click Here</button></p>
            </form>
        <?php } ?>
        <p>Do you want to sign out? <button class="btn btn-primary" onclick="signOut()" style="background-color: #C24511; border-color: #C24511;">Click Here</button></p>
    </div>
</div>
</body>
<script>
    const token = <?php echo js_escape(CsrfUtils::collectCsrfToken('lifemesh')); ?>;
    const reset_url = <?php echo js_escape($reset_cancel_url . '?acct=reset&token='); ?>;
    const cancel_url = <?php echo js_escape($reset_cancel_url . '?acct=cancel&token='); ?>;
    const url = <?php echo js_escape($url . '?token='); ?>;
    const redirect = <?php echo js_escape($setup); ?>;

    async function cancelSubscription() {
        let response = await fetch(cancel_url + encodeURIComponent(token));
        let result = await response.text();
        if (result != '404') {
            $.ajax({
                url: url + encodeURIComponent(token),
                type: 'GET',
                success: function (response) {
                    alert('Your current subscription was successfully cancelled. Please note that while your current subscription status is no longer active, you can still use the Telehealth service until the end of this billing cycle. Additionally, your account credentials are still valid should you wish to start a new subscription.');
                    window.location = redirect;
                }
            })
        } else {
            alert('Your request to cancel your account has failed: ' + result);
        }
    }

    async function signOut() {
        $.ajax({
            url: url + encodeURIComponent(token),
            type: 'GET',
            success: function (response) {
                alert('You have successfully signed out of your Telehealth account.');
                window.location = redirect;
            }
        })
    }

    async function resetPassword() {
        let response = await fetch(reset_url + encodeURIComponent(token));
        let result = await response.text();
        if (result == 'complete') {
                $.ajax({
                    url: url + encodeURIComponent(token),
                    type: 'GET',
                    success:function(response){
                        alert('Please close this account page and check your email for the new password.');
                    window.location = redirect;
                    }
                })
        } else {
            alert(response.statusText + ' Account Reset Failed')
        }
    }
</script>
</html>


