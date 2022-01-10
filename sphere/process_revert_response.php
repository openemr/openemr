<?php

/**
 * process_revert_response.php
 *
 * Receives the middleman (initial_response.php) script from Sphere to avoid cross origin breakage.
 * Csrf prevention is maintained.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\PaymentProcessing\PaymentProcessing;
use OpenEMR\PaymentProcessing\Sphere\SphereRevert;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token"], 'sphere_revert')) {
    CsrfUtils::csrfNotVerified();
}

if ($GLOBALS['payment_gateway'] != 'Sphere') {
    die(xlt("Feature not activated"));
}

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    die(xlt("Unauthorized access."));
}
?>

<html>
<head>
    <?php
    Header::setupHeader('opener');

    $auditData = ['get' => $_GET, 'post' => $_POST];

    if (!empty($_POST['status']) && (($_POST['status'] == 'baddata') || ($_POST['status'] == 'error'))) {
        PaymentProcessing::saveRevertAudit($_POST['uuid_tx'], $_POST['action'], $auditData, 0);
        echo "<script>opener.sphereRevertNotSuccess(" . js_escape(xl("Aborted since unable to submit transaction") . ": " . $_POST['status'] . " " . $_POST['error'] . " " . $_POST['offenders']) . ");dlgclose();</script>";
    } else if (!empty($_POST['hash']) && !empty($_POST['token'])) {
        $sphereRevert = new SphereRevert($_GET['front']);

        // verify that the querystring hash from sphere is authentic and not modified
        if (!$sphereRevert->checkQuerystringHash($_POST['hash'], $_POST['querystring'])) {
            $auditData['check_querystring_hash'] = false;
            PaymentProcessing::saveRevertAudit($_POST['uuid_tx'], $_POST['action'], $auditData, 0);
            echo "<script>opener.sphereRevertNotSuccess(" . xlj("Aborted since querystring hash was invalid") . ");dlgclose();</script></head><body></body></html>";
            exit;
        }
        $auditData['check_querystring_hash'] = true;

        // complete the transaction
        $completeRevert = $sphereRevert->completeTransaction($_POST['token']);
        $auditData['complete_transaction'] = $completeRevert;

        if ($completeRevert['status'] != 'accepted') {
            PaymentProcessing::saveRevertAudit($_POST['uuid_tx'], $_POST['action'], $auditData, 0, $completeRevert['transid']);
            $completeRevertToString = "\n";
            foreach ($completeRevert as $key => $value) {
                if (!empty($key) || !empty($value)) {
                    $completeRevertToString .= $key . ": " . $value . "\n";
                }
            }
            echo "<script>opener.sphereRevertNotSuccess(" . js_escape(xl("Aborted since unable to complete transaction") . ": " . $completeRevertToString) . ");dlgclose();</script></head><body></body></html>";
            exit;
        }

        // Successful revert
        PaymentProcessing::saveRevertAudit($_POST['uuid_tx'], $_POST['action'], $auditData, 1, $completeRevert['transid']);
        echo "<script>opener.sphereRevertSuccess(" . js_escape(xl("Successful") . " " . $_POST['action']) . ");dlgclose();</script>";
    } else {
        // catch all for errors that are not caught above
        $auditData['error_custom'] = "Unclear revert error with following querystring: " . $_POST['querystring'];
        PaymentProcessing::saveRevertAudit($_POST['uuid_tx'], $_POST['action'], $auditData, 0);
        echo "<script>opener.sphereRevertNotSuccess(" . js_escape(xl("Revert Error") . ": " . $_POST['querystring']) . ");dlgclose();</script>";
    }
    ?>
</head>
<body>
</body>
</html>
