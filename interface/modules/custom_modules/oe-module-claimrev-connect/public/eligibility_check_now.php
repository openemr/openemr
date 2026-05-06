<?php

/**
 * AJAX endpoint for real-time eligibility checking.
 *
 * Sends the eligibility request to ClaimRev immediately and returns the result.
 * Works for any product: Eligibility, Demographics, Coverage Discovery, MBI Finder.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\EligibilityTransfer;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

// Coverage Discovery polls for retryLater results for up to ~60s, plus the
// ClaimRev API host runs on Cloud Run where a cold start adds another ~60s.
// Default max_execution_time of 30s is not enough to cover that worst case.
set_time_limit(180);

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('acct', 'bill')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'eligibility')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$pid = ModuleInput::postString('pid');
$responsibility = ModuleInput::postString('responsibility');

if ($pid === '' || $responsibility === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing pid or responsibility']);
    exit;
}

// Collect selected products
$selectedProducts = [];
if (ModuleInput::postString('product_1') !== '') {
    $selectedProducts[] = 1;
}
if (ModuleInput::postString('product_2') !== '') {
    $selectedProducts[] = 2;
}
if (ModuleInput::postString('product_3') !== '') {
    $selectedProducts[] = 3;
}
if (ModuleInput::postString('product_5') !== '') {
    $selectedProducts[] = 5;
}
if ($selectedProducts === []) {
    $selectedProducts = [1];
}

$eventDateRaw = ModuleInput::postString('eventDate');
$facilityIdRaw = ModuleInput::postString('facilityId');
$providerIdRaw = ModuleInput::postString('providerId');
$eventDate = $eventDateRaw !== '' ? $eventDateRaw : null;
$facilityId = $facilityIdRaw !== '' ? $facilityIdRaw : null;
$providerId = $providerIdRaw !== '' ? $providerIdRaw : null;

$result = EligibilityTransfer::sendImmediate(
    $pid,
    $responsibility,
    $selectedProducts,
    $eventDate,
    $facilityId,
    $providerId
);

echo json_encode($result);
