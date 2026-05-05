<?php

require_once(__DIR__ . "/../../../../../../globals.php");
require_once(__DIR__ . "/../../../src/Services/TemplateService.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\MedEx\Services\TemplateService;

header('Content-Type: application/json');

if (!AclMain::aclCheckCore('patients', 'appt', '', 'write')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$templateService = new TemplateService();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['provider_id'])) {
                $templates = $templateService->getTemplates((int)$_GET['provider_id']);
                echo json_encode($templates);
            } else {
                echo json_encode(['error' => 'Provider ID required']);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (isset($data['action']) && $data['action'] === 'apply') {
                $result = $templateService->applyTemplate(
                    (int)$data['template_id'],
                    $data['start_date'],
                    $data['end_date']
                );
                echo json_encode($result);
            } else {
                $result = $templateService->createTemplate($data);
                echo json_encode($result);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
