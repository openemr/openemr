<?php

/**
 * AJAX endpoint: sync a ClaimRev claim status to OpenEMR.
 *
 * The browser sends only the ClaimRev `claimrevObjectId`. The server
 * re-fetches the authoritative claim via SearchClaims and derives the
 * status fields from that response — never the browser-supplied JSON —
 * so a tampered request cannot mark an arbitrary OpenEMR claim denied.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\ClaimRevConnector\ClaimsPage;
use OpenEMR\Modules\ClaimRevConnector\ClaimStatusSyncService;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
use OpenEMR\Modules\ClaimRevConnector\TypeCoerce;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('acct', 'bill')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token'), 'claims')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$claimrevObjectId = ModuleInput::postString('claimrevObjectId');
if ($claimrevObjectId === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing claimrevObjectId']);
    exit;
}

$claim = ClaimsPage::getClaimByObjectId($claimrevObjectId);
if ($claim === null) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Claim not found']);
    exit;
}

$claimData = [
    'patientControlNumber' => TypeCoerce::asString($claim['patientControlNumber'] ?? ''),
    'statusId' => TypeCoerce::asInt($claim['statusId'] ?? 0),
    'statusName' => TypeCoerce::asString($claim['statusName'] ?? ''),
    'payerAcceptanceStatusId' => TypeCoerce::asInt($claim['payerAcceptanceStatusId'] ?? 0),
    'payerAcceptanceStatusName' => TypeCoerce::asString($claim['payerAcceptanceStatusName'] ?? ''),
    'errorMessage' => '',
];

$result = ClaimStatusSyncService::syncStatus($claimData);
echo json_encode($result);
