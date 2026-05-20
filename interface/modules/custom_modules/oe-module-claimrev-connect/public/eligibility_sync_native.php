<?php

/**
 * AJAX endpoint to force-sync ClaimRev raw 271 into native OpenEMR eligibility tables.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once "../../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
use OpenEMR\Modules\ClaimRevConnector\EligibilityData;
use OpenEMR\Modules\ClaimRevConnector\EligibilityTransfer;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

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
$payerResponsibility = ModuleInput::postString('payer_responsibility');

if ($pid === '' || $payerResponsibility === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing pid or payer_responsibility']);
    exit;
}

$record = EligibilityData::getRaw271($pid, $payerResponsibility);
if ($record === null || $record['raw271'] === '') {
    echo json_encode(['success' => false, 'message' => 'No raw 271 data available for this eligibility check']);
    exit;
}

try {
    EligibilityTransfer::populateNativeEligibility($record['raw271'], $record['id']);
    echo json_encode(['success' => true, 'message' => 'Native eligibility updated']);
} catch (\RuntimeException | \LogicException) {
    echo json_encode(['success' => false, 'message' => 'Failed to parse 271 data']);
}
