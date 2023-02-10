<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  All rights reserved
 */

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\AuthorizationService;
use Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller\ListAuthorizations;
use OpenEMR\Core\Header;

require_once dirname(__FILE__, 4) . "/globals.php";
require_once dirname(__FILE__) . '/vendor/autoload.php';

$clinic = AuthorizationService::registerFacility();
AuthorizationService::registration($clinic);
$listData = new ListAuthorizations();
$listData->insertMissingAuthsFromForm();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt("Welcome | Prior Authorization Manager"); ?></title>
    <?php Header::setupHeader() ?>
</head>
<body>
<div class="container">
    <div class="m-5">
        <h1><?php echo xlt("Prior Authorization Manager") ?></h1>
    </div>
    <div class="m-5">
        <p><?php echo xlt("Thank you for selecting our module to help your practice/clinic") ?></p>
        <p><?php echo xlt("This module auto registers the installation. By installing this module,
        you are entitled to a limited technical support via the community message board") ?>.</p>
        <p><strong><?php echo xlt("We are requesting $60/yr to support future development"); ?></strong>.</p>
        <p><a href="https://link.waveapps.com/9b4fs8-47wwtz" target="_blank">
                <?php echo xlt('Click here to pay') ?></a></p>
        <p><?php echo xlt("The module is fully functional") ?></p>
    </div>
    <div class="m-5">
        <p><?php echo xlt("This module was developed by") ?>
            <a href="https://affordablecustomehr.com/privacy"  target="_blank" >
                <?php echo xlt("Affordable Custom EHR") ?></a></p>
        <p>&copy; <?php echo date('Y')?> <?php echo xlt("Juggernaut Systems Express"); ?></p>
    </div></div>
</body>
</html>
