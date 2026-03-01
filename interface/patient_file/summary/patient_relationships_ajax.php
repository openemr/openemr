<?php

/**
 * Patient Relationships AJAX Handler
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com> AI-generated
 * @copyright Copyright (c) 2024
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PatientRelationshipService;
use OpenEMR\Services\PatientService;
use OpenEMR\Entity\PatientRelationship;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

if (!AclMain::aclCheckCore('patients', 'demo')) {
    die("Access denied");
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? 'create';
$patient_id = $_SESSION['pid'] ?? '';

if (!$patient_id) {
    echo json_encode(['success' => false, 'error' => 'No patient selected']);
    exit;
}

$relationshipService = new PatientRelationshipService(new PatientService());

try {
    switch ($action) {
        case 'create':
            $relatedPatientId = $_POST['related_patient_id'] ?? '';
            $relationshipType = $_POST['relationship_type'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if (!$relatedPatientId || !$relationshipType) {
                echo json_encode(['success' => false, 'error' => 'Related patient ID and relationship type are required']);
                exit;
            }

            $relationship = new PatientRelationship(
                $patient_id,
                (int)$relatedPatientId,
                $relationshipType,
                $_SESSION['authUserID'],
                $notes
            );

            $result = $relationshipService->createRelationship($relationship);

            if ($result->hasErrors()) {
                echo json_encode(['success' => false, 'error' => implode(', ', $result->getValidationMessages())]);
            } else {
                $data = $result->getData();
                // Convert relationship entity to array for JSON response
                if (!empty($data)) {
                    foreach ($data as &$item) {
                        if (isset($item['relationship']) && is_object($item['relationship'])) {
                            $item['relationship'] = $item['relationship']->toArray();
                        }
                    }
                }
                echo json_encode(['success' => true, 'data' => $data]);
            }
            break;

        case 'delete':
            $relationshipId = $_POST['relationship_id'] ?? '';
            if (!$relationshipId) {
                echo json_encode(['success' => false, 'error' => 'Relationship ID is required']);
                exit;
            }

            $result = $relationshipService->deleteRelationship((int)$relationshipId);

            if ($result->hasErrors()) {
                echo json_encode(['success' => false, 'error' => implode(', ', $result->getValidationMessages())]);
            } else {
                echo json_encode(['success' => true, 'data' => $result->getData()]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
            break;
    }
} catch (Exception) {
    echo json_encode(['success' => false, 'error' => 'An error occurred while processing your request']);
}
