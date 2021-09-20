<?php

/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once "../../../../globals.php";
require_once "../controller/Container.php";

use OpenEMR\Modules\LifeMesh\Container;
use OpenEMR\Core\Header;

$getcredentals = sqlQuery("select username, password from lifemesh_account");
if ($getcredentals['username'] == '') {
    die('You are not logged in');
}
$getaccountsummary = new Container();
$password = $getaccountsummary->getDatabase();
$pass = $password->cryptoGen->decryptStandard($getcredentals['password']);
$summaryurl = $getaccountsummary->getAppDispatch();
$url = 'accountSummary';
$data = $summaryurl->apiRequest($getcredentals['username'], $pass, $url);

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
        <?php echo $getcredentals['username']?></p>
        <p></p>
        <p></p>
        <p><strong>Billed Telehealth Sessions this Billing Cycle</strong><br>
        <?php print $j_data['session_count']; ?></p>
        <p></p>
        <p><strong>Billing Cycle Ends</strong><br>
        <?php print gmdate("Y-m-d TH:i:s\Z", $j_data['billing_period_end']); ?></p>
    </div>
    <div id="plans">
        <p><strong>Telehealth Pricing Tiers</strong><br>
        First 100 Telehealth Sessions costs $99.00<br>
        Next 101 - 200 costs $119.00<br>
        Next 201 - 300 costs $159.00<br>
        Next 301 - 500 costs $279.00<br>
        Next 501 - 750 costs $249.00<br>
        Next 751 sessions and beyond costs $0.75/session</p>
    </div>
    <div id="acctmgr">
        <p></p>
        <p>Reset account password <button class="btn btn-primary" onclick="resetPassword()">Click Here</button></p>
        <p>Do you want to cancel your subscription? <button class="btn btn-primary" onclick="cancelSubscription()">Click Here</button></p>
    </div>
</div>
</body>
<script>
    function createAuthorization() {
        const username = "<?php echo $getcredentals['username']; ?>";
        const password = '<?php echo $pass; ?>';
        const auth = btoa(username + ':' + password);
        return auth;
    }
    function cancelSubscription() {

        const myHeaders = new Headers();
        myHeaders.append("Authorization", "Basic " + createAuthorization());

        const raw = "";

        const requestOptions = {
            method: 'POST',
            headers: myHeaders,
            body: raw,
            redirect: 'follow'
        };

        fetch("https://api.telehealth.lifemesh.ai/cancel_subscription", requestOptions)
            .then(response => response.text())
            .then(result => console.log(result))
            .catch(error => console.log('error', error));
        alert('Account Canceled');
    }


    function resetPassword() {
        const myHeaders = new Headers();
        myHeaders.append("Authorization", "Basic " + createAuthorization());

        const raw = "";

        const requestOptions = {
            method: 'POST',
            headers: myHeaders,
            body: raw,
            redirect: 'follow'
        };

        fetch("https://api.telehealth.lifemesh.ai/reset_password", requestOptions)
            .then(response => response.text())
            .then(result => console.log(result))
            .catch(error => console.log('error', error));

        fetch("wipeaccount.php")
            .then(response => response.text())
            .then(result => console.log(result))
            .catch(error => console.log('error', error));
        alert('Close account page and check your email for new password');
    }
</script>
</html>


