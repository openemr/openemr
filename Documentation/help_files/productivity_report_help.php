<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt('Productivity Report Help') ?></title>
    <?php Header::setupHeader() ?>
</head>
<body>
<div class="container">
    <h2><?php echo xlt('Productivity Report Help') ?></h2>
    <div class="row">
        <div class="col col-md-12">
            <p><?php echo xlt('The productivity report is a report that shows the productivity of the providers in the system.
            The report shows the total charges, payments, and adjustments for each provider.') ?></p>
            <p><?php
                    echo xlt('The Productivity Report is a fusion of the Day Sheet and Financial Summary by Service Code reports.  The ability to export it and then sort by date, rendering provider, insurance company or patient truly makes it the ultimate report!')
            ?></p>
            <p><?php
                echo xlt('For example, if the practice needed to know, how much are we getting paid for the 64486 code?  Which insurance carrier reimburses us the best for this code?  No report in the OpenEMR could really tell me (Report - Financial Summary by Service Code was too vague)')
            ?></p>
            <p><?php
                echo xlt('Our providers with XYZ Services do postoperative pain block procedures (64486). These providers are incentivized to perform them, however it is a notoriously denied procedure. The hospital CEO needs to be able to evaluate the cost-effectiveness of the procedures.')
            ?></p>
            <p><?php echo xlt('The report can be exported to a CSV file or printed. At the end of the year, I can run a report, extract specific codes and be able to tell how many each provider did and how much was paid for them by the insurance carrier') ?></p>
    </div>
</div>
</body>
</html>
