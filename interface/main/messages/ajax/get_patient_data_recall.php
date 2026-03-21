<?php

/**
 * AJAX endpoint to get patient data for recall form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@medfetch.com>
 * @copyright Copyright (c) 2026 OpenEMR Foundation
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\RecallService;

header('Content-Type: application/json');

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_REQUEST['csrf_token_form'] ?? '', session: $session)) {
    CsrfUtils::csrfNotVerified();
}

// Check authentication
if (!$session->get('authUserID')) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => xl('Unauthorized')]);
    exit;
}

// Check permissions
if (!AclMain::aclCheckCore('patients', 'demo', '', 'read')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => xl('Permission denied')]);
    exit;
}

$pid = (int) ($_POST['pid'] ?? $_GET['pid'] ?? 0);

if (!$pid) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => xl('Patient ID required')]);
    exit;
}

try {
    $recallService = new RecallService();
    $patient = $recallService->getPatientData($pid);

    if ($patient) {
        echo json_encode([
            'success' => true,
            'patient' => $patient
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => xl('Patient not found')
        ]);
    }
} catch (\Throwable $e) {
    (new SystemLogger())->error("Error fetching patient data", ['message' => $e->getMessage()]);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => xl('An error occurred while fetching patient data')
    ]);
}
