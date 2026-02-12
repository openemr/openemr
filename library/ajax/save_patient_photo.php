<?php

/**
 * AJAX endpoint for saving patient photo from webcam capture
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    AI-Generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PatientPhotoService;

header('Content-Type: application/json');

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF token validation failed']);
    exit;
}

// Check ACL - user must have write access to patient demographics
if (!AclMain::aclCheckCore('patients', 'demo', '', 'write')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Get parameters
$pid = (int)($_POST['pid'] ?? 0);
$photoData = $_POST['photo_data'] ?? '';

if ($pid <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid patient ID']);
    exit;
}

if (empty($photoData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No photo data provided']);
    exit;
}

// Save the photo using the service
$photoService = new PatientPhotoService();
$result = $photoService->saveFromBase64($pid, $photoData);

if ($result['success']) {
    echo json_encode($result);
} else {
    http_response_code(500);
    echo json_encode($result);
}
