<?php

/*
 *
 *   package   OpenEMR
 *   link      http://www.open-emr.org
 *   author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)
 *   All rights reserved
 */

namespace Juggernaut\Module\Bamboo;


require_once dirname(__FILE__, 4) . '/globals.php';

use OpenEMR\Core\Header;
use Juggernaut\Module\Bamboo\Controllers\ResourcesConfig;

$resourcesConfig = new ResourcesConfig();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storeComplete = $resourcesConfig->storeConnectionData($_POST);
}
$hasCredentials = $resourcesConfig->getConnectionData();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt('Settings') ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12 mt-3">
            <span style="font-size: xx-large; padding-right: 20px"><?php echo xlt('Bamboo PMP') ?></span>
            <div>
                <p><strong><?php echo xlt("Follow these steps") ?>:</strong><br>
                1. <?php print xlt("Visit") ?>: <a href="https://connect.bamboohealth.com/" target="_blank">https://connect.bamboohealth.com/</a><br>
                2. <?php print xlt("Click ‘Create an Account' in the top right-hand corner")?>.<br>
                3. <?php print xlt("Once an account has been created, follow the prompts on each screen to complete the process")?>.<br>
                4. <?php print xlt("Return here and enter username and password to enable service")?>.</p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mt-3">
            <h1><?php echo xlt('Settings') ?></h1>
            <?php if (isset($storeComplete)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo xlt('Settings saved') ?>
                </div>
            <?php endif; ?>
            <?php if (!$hasCredentials['password']): ?>
            <form action="setup.php" method="post">
                <div class="form-group">
                    <label for="username"><?php echo xlt('Username') ?></label>
                    <input type="text" name="username" id="username" class="form-control">
                    <label for="password"><?php echo xlt('Password') ?></label>
                    <input type="password" name="password" id="password" class="form-control">
                    <button type="submit" class="btn btn-primary mt-2"><?php echo xlt('Submit') ?></button>
                </div>
            </form>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    <?php echo xlt('Credentials already saved') ?>
                </div>
                <div>
                    <p><?php echo xlt('To change credentials, remove completely and reinstall module') ?></p>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-6 mt-3">
            <h1><?php echo xlt('Price Structure') ?></h1>
            <p><?php echo xlt('Gateway with NarxCare Analytics report--$125 per prescriber per year') ?>. <br>
            <?php echo xlt('Implementation Fee: Per entity, not provider = $250') ?>. <br>
            <?php echo xlt('Note: Multi-State implementations could cost more. Please contact Bamboo for more information.') ?>. </p>
            <h1><?php echo xlt('Documentation') ?></h1>
            <p><a href="documentation/NarxCareApplicationOverview.pdf" target="_blank"><?php echo xlt("NarxCare Application Overview")?></a></p>
            <p><a href="documentation/NarxCareFactSheet.pdf" target="_blank"><?php echo xlt("NarxCare Fact Sheet")?></a></p>
            <p><a href="documentation/NarxCareScoresExplained.pdf" target="_blank"><?php echo xlt("NarxCare Scores Explained")?></a></p>
            <h1><?php echo xlt('Resources') ?></h1>
            <p><a href="https://status.bamboohealth.com/" target="_blank"><?php echo xlt("Service Status")?></a></p>
            <p><a href="https://pmpgateway.zendesk.com/hc/en-us/articles/27192277071891-State-Funding-Integration-Permissions-Map" target="_blank"><?php echo xlt("Integration States")?></a></p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mt-3">
            <h1><?php echo xlt('Support') ?></h1>
            <p><?php echo xlt('Purchase') ?>: <a href="https://buy.stripe.com/aEU5kJeIK7nC6nC9AF" target="_blank">$<?php echo text('45')?></a>
            <p><?php echo xlt('If technical support is needed, please make a purchase for support. The fee covers 3 hrs. of support to troubleshoot connection and code issues') ?></p>
        </div>
    </div>
</div>
</body>
</html>
