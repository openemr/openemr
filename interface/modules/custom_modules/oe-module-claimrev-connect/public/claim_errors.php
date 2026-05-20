<?php

/**
 * AJAX endpoint to fetch claim errors.
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
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevException;
use OpenEMR\Modules\ClaimRevConnector\ModuleInput;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('acct', 'bill')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$claimId = ModuleInput::getString('claimId');

if ($claimId === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing claimId']);
    exit;
}

try {
    $api = ClaimRevApi::makeFromGlobals();
    $errors = $api->getClaimErrors($claimId);
    echo json_encode(['success' => true, 'errors' => $errors]);
} catch (ClaimRevException) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch claim errors']);
}
