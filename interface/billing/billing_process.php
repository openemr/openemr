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
 * @copyright Copyright (c) 2018-2021 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Billing\BillingProcessor\BillingProcessor;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// Initialize billing processor with the post variables from the billing manager form
$billingProcessor = new BillingProcessor($_POST);
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
                            <?php echo nl2br(text($infoline)); ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</html>
<?php
$logger->onLogComplete();
?>
</body>
