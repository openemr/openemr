<?php

/**
 * AJAX endpoint that wipes every cached eligibility record for a patient.
 *
 * Used by the "Reset" button next to "Check Now" so testers can re-run a
 * check from a clean slate without poking the database directly.
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
use OpenEMR\Modules\ClaimRevConnector\EligibilityData;
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

$pidRaw = ModuleInput::postString('pid');
$pid = (int) $pidRaw;
if ($pid <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing or invalid pid']);
    exit;
}

EligibilityData::removeAllEligibilityForPatient($pid);

echo json_encode(['success' => true]);
