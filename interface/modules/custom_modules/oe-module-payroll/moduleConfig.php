<?php

/*
 * Provider Payroll
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * author Sherwin Gaddis <sherwingaddis@gmail.com>
 * All Rights Reserved
 * @copyright Copyright (c) 2023.
 */

$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../globals.php");
$module_config = 1;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2><?php echo xlt("Provider Payroll") ?></h2>
            <p><?php echo xlt("This module allows you to view and update the payroll information for each provider.") ?></p>
            <h2><?php echo xlt("Instructions") ?></h2>
            <p><?php echo xlt("Now that the modules has been actived, go to the main menu and select Admin. Under
            Admin, there should be a selection for 'Set Providers Rate'. Once that is selected, you will see the form to
            enter the rate for each provider.
            ") ?></p>
            <p><?php echo xlt("After the providers rates have been set, go to Reports from the main menu. There at
            the bottom of the menu you will see Provider Earnings Admin and Provider Payroll Self. The Admin page will
            display the payroll information for each provider based on the time frame selected.
            The Self page will display the providers to the provider that is logged in. The provider should only see the
            Self option. The Admin menu item should only be visible to the Admin users.
            ") ?></p>
            <p><?php echo xlt("The system allows you to enter a fixed rate for the provider or a percentage rate.
            It will not allow you to enter both.
            ") ?></p>
            <p><?php echo xlt("If you would like to sponsor further development, please contact sherwingaddis@gmail.com.
            ") ?></p>

        </div>
    </div>
    <div class="row">
        &copy; 2021-<?php echo date("Y") . " " . xlt("Juggernaut Systems Express and Med Boss Consulting") ?>
    </div>
</div>
</body>
</html>



