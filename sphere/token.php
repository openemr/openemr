<?php

/**
 * token.php
 *
 * Collect a token for Sphere.
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
use OpenEMR\PaymentProcessing\PaymentProcessing;
use OpenEMR\PaymentProcessing\Sphere\SphereRevert;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"], 'sphere_revert_token')) {
    CsrfUtils::csrfNotVerified();
}

if ($GLOBALS['payment_gateway'] != 'Sphere') {
    die(xlt("Feature not activated"));
}

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    die("Unauthorized access.");
}

$confirmPinPost = $_POST['pin_code'] ?? null;
$action = $_POST['action'] ?? null;
$front = $_POST['front'] ?? null;
$transid = $_POST['trans_id'] ?? null;
$uuidTx = $_POST['uuid_tx'] ?? null;

if (empty($confirmPinPost) || empty($action) || empty($front) || empty($transid) || empty($uuidTx)) {
    die("Missing data.");
}

header('Content-Type: application/json');

try {
    $token = (new SphereRevert($front))->getToken($action, $transid, $confirmPinPost, $uuidTx);
    echo json_encode(['success' => $token]);
} catch (Exception $e) {
    $errorAudit = [];
    $errorAudit['token_request_error'] = $e->getMessage();
    $errorAudit['get']['front'] = $front;
    PaymentProcessing::saveRevertAudit($uuidTx, $action, $errorAudit, 0);
    echo json_encode(['error' => $e->getMessage()]);
}
exit;
