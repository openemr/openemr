<?php

/*
 * Billing process Program
 *
 * This program processes data for claims generation
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2014-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// Initialize billing processor with the post variables from the billing manager form
$billingProcessor = new \OpenEMR\Billing\BillingProcessor\BillingProcessor($_POST);
$logger = $billingProcessor->execute();
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <script>
        $(function () {
            $("#close-link").click(function () {
                window.close();
            });
        });
    </script>
</head>
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h3><?php echo xlt('Billing queue results'); ?>:</h3>
                <ul>
                    <?php  foreach ($logger->bill_info() as $infoline) { ?>
                        <li>
                            <?php echo nl2br($infoline); ?>
                        </li>
                    <?php } ?>
                </ul>
<!--                --><?php //if ($logger->showCloseButton()) { ?>
<!--                <button class="btn btn-secondary btn-sm btn-cancel" id="close-link">-->
<!--                    --><?php //echo xlt('Close'); ?>
<!--                </button>-->
<!--                --><?php //} ?>
            </div>
        </div>
    </div>
</html>
<?php
    $logger->onLogComplete();
?>
</body>
