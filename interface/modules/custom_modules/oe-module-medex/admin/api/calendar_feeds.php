<?php
/**
 * Calendar Feeds Management API
 *
 * Create, list, and delete calendar feeds with provider/facility filters
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', $session)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'CSRF token validation failed']);
        exit;
    }
}

// Load MedEx API
require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

if (!$api->isConfigured()) {
    echo json_encode(['success' => false, 'error' => 'MedEx is not configured']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create_calendar_feed':
        createCalendarFeed($api);
        break;
        
    case 'delete_calendar_feed':
        deleteCalendarFeed($api);
        break;
        
    case 'list_calendar_feeds':
        listCalendarFeeds($api);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

function createCalendarFeed($api) {
    $name = trim($_POST['name'] ?? '');
    $providers = $_POST['providers'] ?? '';
    $facilities = $_POST['facilities'] ?? '';
    $feedPassword = $_POST['feed_password'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Feed name is required']);
        return;
    }
    
    if (empty($feedPassword) || strlen($feedPassword) < 8) {
        echo json_encode(['success' => false, 'error' => 'Feed password is required (minimum 8 characters)']);
        return;
    }
    
    // Parse provider and facility IDs
    $providerIds = !empty($providers) ? array_filter(explode(',', $providers)) : [];
    $facilityIds = !empty($facilities) ? array_filter(explode(',', $facilities)) : [];
    
    if (empty($providerIds) && empty($facilityIds)) {
        echo json_encode(['success' => false, 'error' => 'Select at least one provider or facility']);
        return;
    }
    
    // Get current OpenEMR user info for authentication
    $openemrUserId = $_SESSION['authUserID'] ?? 0;
    $openemrUsername = $_SESSION['authUser'] ?? '';
    
    if (empty($openemrUsername)) {
        echo json_encode(['success' => false, 'error' => 'OpenEMR user session not found']);
        return;
    }
    
    // Get provider and facility names for display
    $providerNames = [];
    $facilityNames = [];
    
    if (!empty($providerIds)) {
        $placeholders = implode(',', array_fill(0, count($providerIds), '?'));
        $result = sqlStatement(
            "SELECT id, fname, lname FROM users WHERE id IN ($placeholders)",
            $providerIds
        );
        while ($row = sqlFetchArray($result)) {
            $providerNames[$row['id']] = $row['lname'] . ', ' . $row['fname'];
        }
    }
    
    if (!empty($facilityIds)) {
        $placeholders = implode(',', array_fill(0, count($facilityIds), '?'));
        $result = sqlStatement(
            "SELECT id, name FROM facility WHERE id IN ($placeholders)",
            $facilityIds
        );
        while ($row = sqlFetchArray($result)) {
            $facilityNames[$row['id']] = $row['name'];
        }
    }
    
    // Create feed via MedEx API
    try {
        $result = $api->createCalendarFeed([
            'name' => $name,
            'providers' => $providerIds,
            'facilities' => $facilityIds,
            'provider_names' => array_values($providerNames),
            'facility_names' => array_values($facilityNames),
            'openemr_user_id' => $openemrUserId,
            'openemr_username' => $openemrUsername,
            'feed_password' => $feedPassword
        ]);
        
        if ($result && !empty($result['success'])) {
            echo json_encode([
                'success' => true,
                'feed' => $result['feed'] ?? null,
                'message' => 'Calendar feed created successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to create calendar feed'
            ]);
        }
    } catch (\Exception $e) {
        error_log('[calendar_feeds.php] Error creating feed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function deleteCalendarFeed($api) {
    $feedId = $_POST['feed_id'] ?? '';
    
    if (empty($feedId)) {
        echo json_encode(['success' => false, 'error' => 'Feed ID is required']);
        return;
    }
    
    try {
        $result = $api->deleteCalendarFeed($feedId);
        
        if ($result && !empty($result['success'])) {
            echo json_encode(['success' => true, 'message' => 'Calendar feed deleted']);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to delete calendar feed'
            ]);
        }
    } catch (\Exception $e) {
        error_log('[calendar_feeds.php] Error deleting feed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function listCalendarFeeds($api) {
    try {
        $result = $api->getCalendarFeeds();
        echo json_encode([
            'success' => true,
            'feeds' => $result['feeds'] ?? []
        ]);
    } catch (\Exception $e) {
        error_log('[calendar_feeds.php] Error listing feeds: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
