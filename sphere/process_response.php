<?php

/**
 * process_response.php
 *
 * Receives the middleman (initial_response.php) script from Sphere to avoid cross origin breakage.
 * Csrf prevention is maintained.
 * Works in both core and portal.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Will start the (patient) portal OpenEMR session/cookie.
require_once(__DIR__ . "/../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

$isPortal = false;
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth_onsite_portal = true;
    $isPortal = true;
    require_once(__DIR__ . "/../interface/globals.php");
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    $ignoreAuth = false;
    require_once(__DIR__ . "/../interface/globals.php");
}

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\PaymentProcessing\PaymentProcessing;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token"], 'sphere')) {
    CsrfUtils::csrfNotVerified();
}

if ($GLOBALS['payment_gateway'] != 'Sphere') {
    die(xlt("Feature not activated"));
}
?>

<html>
<head>
    <?php
    Header::setupHeader('opener');

    $auditData = ['get' => $_GET, 'post' => $_POST];

    if ($_GET['front'] == 'patient') {
        // will not show error descriptions in patient front
        $description = '';
    } else { // $_GET['front'] == 'clinic-phone' || $_GET['front'] == 'clinic-retail'
        $description = (!empty($_POST['description'])) ? ' (' . $_POST['description'] . ')' : '';
    }

    if (!empty($_GET['cancel']) && ($_GET['cancel'] == 'cancel')) {
        PaymentProcessing::saveAudit('sphere', $_GET['patient_id_cc'], 0, $auditData, $_POST['ticket']);
        echo "<script>opener.sphereNotSuccess(" . xlj("Transaction Cancelled") . ");dlgclose();</script>";
    } elseif (($_POST['status_name'] == 'baddata') || ($_POST['status_name'] == 'error')) {
        PaymentProcessing::saveAudit('sphere', $_GET['patient_id_cc'], 0, $auditData, $_POST['ticket'], $_POST['transid'] ?? null, $_POST['action_name'] ?? null, $_POST['amount'] ?? null);
        echo "<script>opener.sphereNotSuccess(" . js_escape(xl("Transaction Error") . $description) . ");dlgclose();</script>";
    } elseif ($_POST['status_name'] == 'decline') {
        PaymentProcessing::saveAudit('sphere', $_GET['patient_id_cc'], 0, $auditData, $_POST['ticket'], $_POST['transid'], $_POST['action_name'], $_POST['amount']);
        echo "<script>opener.sphereNotSuccess(" . js_escape(xl("Transaction Declined") . $description) . ");dlgclose();</script>";
    } elseif ($_POST['status_name'] == 'approved') {
        // Success!
        PaymentProcessing::saveAudit('sphere', $_GET['patient_id_cc'], 1, $auditData, $_POST['ticket'], $_POST['transid'], $_POST['action_name'], $_POST['amount']);
        if ($_GET['front'] == 'patient') {
            echo "<script>opener.sphereSuccess(" . js_escape((new CryptoGen())->encryptStandard(json_encode($auditData))) . ");dlgclose();</script>";
        } else { // $_GET['front'] == 'clinic-phone' || $_GET['front'] == 'clinic-retail'
            echo "<script>opener.sphereSuccess(" . js_escape($_POST['transid']) . ");dlgclose();</script>";
        }
    } else {
        PaymentProcessing::saveAudit('sphere', $_GET['patient_id_cc'], 0, $auditData, $_POST['ticket'], $_POST['transid'] ?? null, $_POST['action_name'] ?? null, $_POST['amount'] ?? null);
        echo "<script>opener.sphereNotSuccess(" . js_escape(xl("Transaction Not Successful") . $description) . ");dlgclose();</script>";
    }
    ?>
</head>
<body>
</body>
</html>
