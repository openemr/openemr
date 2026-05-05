<?php

/**
 * MedEx Calendar Events API
 *
 * REST endpoint for calendar event CRUD operations
 * Returns data in FullCalendar format
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MedEx <https://medexbank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../../../globals.php");
require_once(__DIR__ . "/../../../src/Services/CalendarService.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\MedEx\Services\CalendarService;

// Set JSON response header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For AJAX calls
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check calendar access
if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$calendarService = new CalendarService();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Get events for calendar view
            $start = $_GET['start'] ?? date('Y-m-d', strtotime('-1 month'));
            $end = $_GET['end'] ?? date('Y-m-d', strtotime('+2 months'));
            $providerId = !empty($_GET['provider_id']) ? (int)$_GET['provider_id'] : null;
            $facilityId = !empty($_GET['facility_id']) ? (int)$_GET['facility_id'] : null;

            $events = $calendarService->getEvents($start, $end, $providerId, $facilityId);
            echo json_encode($events);
            break;

        case 'POST':
            // Create new event
            if ($session && empty($session->get('csrf_private_key', null))) {
                CsrfUtils::setupCsrfKey($session);
            }
            if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '', session: $session)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $result = $calendarService->createEvent($data);
            echo json_encode($result);
            break;

        case 'PUT':
            // Update event (drag-drop reschedule)
            $data = json_decode(file_get_contents('php://input'), true);

            if ($session && empty($session->get('csrf_private_key', null))) {
                CsrfUtils::setupCsrfKey($session);
            }
            if (!CsrfUtils::verifyCsrfToken($data['csrf_token'] ?? '', session: $session)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }

            $eventId = (int)($data['id'] ?? $_GET['id'] ?? 0);
            if (!$eventId) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID required']);
                exit;
            }

            $result = $calendarService->updateEvent($eventId, $data);
            echo json_encode($result);
            break;

        case 'DELETE':
            // Delete event
            parse_str(file_get_contents('php://input'), $data);

            if ($session && empty($session->get('csrf_private_key', null))) {
                CsrfUtils::setupCsrfKey($session);
            }
            if (!CsrfUtils::verifyCsrfToken($data['csrf_token'] ?? '', session: $session)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }

            $eventId = (int)($_GET['id'] ?? 0);
            if (!$eventId) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID required']);
                exit;
            }

            $result = $calendarService->deleteEvent($eventId);
            echo json_encode($result);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
