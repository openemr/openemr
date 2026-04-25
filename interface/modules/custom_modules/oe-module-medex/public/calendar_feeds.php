<?php
/**
 * MedEx Calendar Feeds - User Self-Service Page
 *
 * Allows any OpenEMR user to create and manage their own calendar feed subscriptions.
 * Authentication uses OpenEMR credentials - password never transmitted externally.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once(__DIR__ . '/../../../../globals.php');
require_once(__DIR__ . '/../src/MedExConfig.php');
require_once(__DIR__ . '/../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Modules\MedEx\MedExConfig;

require_once($GLOBALS['incdir'] . "/../library/calendar.inc.php");
require_once($GLOBALS['incdir'] . "/../library/patient.inc.php");

function getOpenEmrServerUrl(): string
{
    $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        return '';
    }
    $proto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    if ($proto === '') {
        $https = (string)($_SERVER['HTTPS'] ?? '');
        $proto = (!empty($https) && strtolower($https) !== 'off') ? 'https' : 'http';
    } else {
        $proto = explode(',', $proto)[0];
        $proto = trim($proto);
    }
    if ($proto !== 'https' && $proto !== 'http') {
        $proto = 'https';
    }
    if ($proto === 'http') {
        // Calendar feed links should always be published as TLS URLs.
        $proto = 'https';
    }
    return $proto . '://' . $host;
}

function getCalendarFeedsAclPair(array $prefs): array
{
    $raw = strtolower(trim((string)($prefs['menu_acl'] ?? 'patients|appt')));
    if ($raw === 'admin|super') {
        return ['admin', 'super'];
    }
    return ['patients', 'appt'];
}

// Verify user is authenticated
if (!isset($_SESSION['authUserID'])) {
    die('Access denied. Please log in to OpenEMR.');
}

$currentUserId = $_SESSION['authUserID'];
$currentUsername = $_SESSION['authUser'];

$userInfo = sqlQuery(
    "SELECT id, username, fname, lname, authorized, calendar, see_auth FROM users WHERE id = ?",
    [$currentUserId]
);

// Verify MedEx is configured (practice has API credentials)
$practiceId = $GLOBALS['medex_practice_id'] ?? '';
$apiKey = $GLOBALS['medex_api_key'] ?? '';
if (empty($practiceId) || empty($apiKey)) {
    $prefs = sqlQuery("SELECT MedEx_id, ME_api_key FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
    if (empty($prefs['MedEx_id']) || empty($prefs['ME_api_key'])) {
        die('MedEx is not configured. Please contact your administrator to set up MedEx integration.');
    }
}

$calendarFeedPrefs = [];
try {
    $medexApi = new MedExAPI();
    $statusRaw = sqlQuery("SELECT status FROM medex_prefs ORDER BY MedEx_lastupdated DESC LIMIT 1");
    $enabledServices = [];
    if (!empty($statusRaw['status'])) {
        $decodedStatus = json_decode((string)$statusRaw['status'], true);
        if (is_array($decodedStatus) && !empty($decodedStatus['enabled_services']) && is_array($decodedStatus['enabled_services'])) {
            $enabledServices = $decodedStatus['enabled_services'];
        }
    }
    if (empty($enabledServices)) {
        $enabledServices = $medexApi->getEnabledServices(true);
    }
    $hasCalendarFeedsBundle = false;
    foreach (['calendar_ai', 'calendar_services', 'calendar_export'] as $serviceKey) {
        if ((isset($enabledServices[$serviceKey]) && ($enabledServices[$serviceKey] === true || $enabledServices[$serviceKey] === 1)) || in_array($serviceKey, $enabledServices, true)) {
            $hasCalendarFeedsBundle = true;
            break;
        }
    }
    if (!$hasCalendarFeedsBundle) {
        die('Access denied. Calendar Feeds require an active Calendar Services subscription.');
    }
    $calendarFeedPrefs = $medexApi->getServicePreferences('calendar_export');
} catch (\Throwable $e) {
    error_log('[MedEx Calendar Feeds] Preference lookup failed: ' . $e->getMessage());
}

$calendarFeedsAclPair = getCalendarFeedsAclPair($calendarFeedPrefs);
if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore($calendarFeedsAclPair[0], $calendarFeedsAclPair[1])) {
    die('Access denied. Your account does not match the configured Calendar Feeds ACL.');
}

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', 'default')) {
        echo json_encode(['success' => false, 'error' => 'Invalid security token']);
        exit;
    }
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'create_feed':
            createFeed();
            break;
        case 'delete_feed':
            deleteFeed();
            break;
        case 'list_feeds':
            listFeeds();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
    exit;
}

/**
 * Create a new calendar feed for current user
 */
function createFeed(): void
{
    global $currentUserId, $currentUsername;
    
    $name = trim($_POST['name'] ?? '');
    $providers = $_POST['providers'] ?? '';
    $facilities = $_POST['facilities'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'error' => 'Feed name is required']);
        return;
    }
    
    // Validate user has access to selected providers/facilities
    $providerIds = array_filter(explode(',', $providers));
    $facilityIds = array_filter(explode(',', $facilities));
    
    // For now, allow any provider/facility the user can see in the calendar
    // Future: Add more granular ACL checking if needed
    
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    
    // Build provider/facility names for display
    $providerNames = [];
    $facilityNames = [];
    
    if (!empty($providerIds)) {
        foreach ($providerIds as $pid) {
            $provider = sqlQuery("SELECT fname, lname FROM users WHERE id = ?", [(int)$pid]);
            if ($provider) {
                $providerNames[] = $provider['lname'] . ', ' . $provider['fname'];
            }
        }
    }
    
    if (!empty($facilityIds)) {
        foreach ($facilityIds as $fid) {
            $facility = sqlQuery("SELECT name FROM facility WHERE id = ?", [(int)$fid]);
            if ($facility) {
                $facilityNames[] = $facility['name'];
            }
        }
    }
    
    // Create local feed record (OpenEMR side)
    try {
        ensureFeedsTableExists();
        
        // Check for duplicate feed name for this user
        $existingFeed = sqlQuery(
            "SELECT id FROM medex_calendar_feeds WHERE openemr_user_id = ? AND name = ?",
            [$currentUserId, $name]
        );
        if (!empty($existingFeed)) {
            echo json_encode(['success' => false, 'error' => 'A feed with this name already exists']);
            return;
        }
        
        sqlStatement(
            "INSERT INTO medex_calendar_feeds 
             (token, name, openemr_user_id, openemr_username, providers, facilities, provider_names, facility_names, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $token,
                $name,
                $currentUserId,
                $currentUsername,
                implode(',', $providerIds),
                implode(',', $facilityIds),
                json_encode($providerNames),
                json_encode($facilityNames)
            ]
        );
        
        $feedId = sqlInsertClean_audit("SELECT LAST_INSERT_ID()");
        
        // Also create on MedEx side for actual calendar data
        createMedExFeed($token, $name, $providerIds, $facilityIds, $providerNames, $facilityNames);
        
        // Build subscription URL (points to OpenEMR, not MedEx!)
        $baseUrl = $GLOBALS['webroot'] ?? '';
        $serverUrl = getOpenEmrServerUrl();
        $feedUrl = $serverUrl . $baseUrl . '/interface/modules/custom_modules/oe-module-medex/public/calendar_feed.php?feed=' . $token;
        
        echo json_encode([
            'success' => true,
            'feed' => [
                'id' => $feedId,
                'token' => $token,
                'name' => $name,
                'url' => $feedUrl,
                'provider_names' => $providerNames,
                'facility_names' => $facilityNames
            ]
        ]);
        
    } catch (\Exception $e) {
        error_log('[MedEx Calendar] Failed to create feed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to create feed']);
    }
}

/**
 * Create feed record on MedEx side
 */
function createMedExFeed(string $token, string $name, array $providerIds, array $facilityIds, array $providerNames, array $facilityNames): bool
{
    $practiceId = $GLOBALS['medex_practice_id'] ?? '';
    $apiKey = $GLOBALS['medex_api_key'] ?? '';
    
    if (empty($practiceId) || empty($apiKey)) {
        $prefs = sqlQuery("SELECT MedEx_id, ME_api_key FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
        if (!empty($prefs['MedEx_id'])) {
            $practiceId = (string)$prefs['MedEx_id'];
        }
        if (!empty($prefs['ME_api_key'])) {
            $apiKey = (string)$prefs['ME_api_key'];
        }
    }
    
    if (empty($practiceId) || empty($apiKey)) {
        error_log('[MedEx Calendar] Cannot create MedEx feed - API not configured');
        return false;
    }
    
    $medexUrl = MedExConfig::baseUrl();
    // Ensure /cart/upload path is included for API endpoint
    if (!str_ends_with($medexUrl, '/cart/upload')) {
        $medexUrl .= '/cart/upload';
    }
    $apiUrl = $medexUrl . '/api/calendar_feeds.php';
    error_log('[MedEx Calendar] Creating feed via: ' . $apiUrl);
    
    $postData = [
        'action' => 'create',
        'practice_id' => $practiceId,
        'api_key' => $apiKey,
        'token' => $token,  // Pass our token so both sides match
        'name' => $name,
        'providers' => implode(',', $providerIds),
        'facilities' => implode(',', $facilityIds),
        'provider_names' => json_encode($providerNames),
        'facility_names' => json_encode($facilityNames),
        // No password needed - auth happens on OpenEMR side!
        'auth_mode' => 'proxy'
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return !empty($data['success']);
    }
    
    error_log('[MedEx Calendar] MedEx feed creation failed: HTTP ' . $httpCode);
    return false;
}

/**
 * Delete a calendar feed (current user only)
 */
function deleteFeed(): void
{
    global $currentUserId, $currentUsername;
    
    $feedId = (int)($_POST['feed_id'] ?? 0);
    
    if ($feedId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid feed ID']);
        return;
    }
    
    // Verify ownership with backward compatibility:
    // - primary: matching openemr_user_id
    // - legacy fallback: matching openemr_username (case-insensitive)
    // - admin super can delete any feed
    $feed = sqlQuery(
        "SELECT id, token, openemr_user_id, openemr_username
         FROM medex_calendar_feeds
         WHERE id = ?
         LIMIT 1",
        [$feedId]
    );
    if (empty($feed)) {
        echo json_encode(['success' => false, 'error' => 'Feed not found']);
        return;
    }
    $ownerId = (int)($feed['openemr_user_id'] ?? 0);
    $ownerUsername = trim((string)($feed['openemr_username'] ?? ''));
    $isOwnerById = ($ownerId > 0 && $ownerId === (int)$currentUserId);
    $isOwnerByUsername = ($ownerId <= 0 && $ownerUsername !== '' && strcasecmp($ownerUsername, (string)$currentUsername) === 0);
    $isAdmin = AclMain::aclCheckCore('admin', 'super');
    if (!$isOwnerById && !$isOwnerByUsername && !$isAdmin) {
        echo json_encode(['success' => false, 'error' => 'Feed not found or access denied']);
        return;
    }
    
    try {
        // Delete locally (owner has already been validated)
        sqlStatement("DELETE FROM medex_calendar_feeds WHERE id = ?", [$feedId]);
        
        // Delete on MedEx side
        deleteMedExFeed($feed['token']);
        
        echo json_encode(['success' => true]);
        
    } catch (\Exception $e) {
        error_log('[MedEx Calendar] Failed to delete feed: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Failed to delete feed']);
    }
}

/**
 * Delete feed on MedEx side
 */
function deleteMedExFeed(string $token): bool
{
    $practiceId = $GLOBALS['medex_practice_id'] ?? '';
    $apiKey = $GLOBALS['medex_api_key'] ?? '';
    
    if (empty($practiceId) || empty($apiKey)) {
        $prefs = sqlQuery("SELECT MedEx_id, ME_api_key FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
        if (!empty($prefs['MedEx_id'])) {
            $practiceId = (string)$prefs['MedEx_id'];
        }
        if (!empty($prefs['ME_api_key'])) {
            $apiKey = (string)$prefs['ME_api_key'];
        }
    }
    
    if (empty($practiceId) || empty($apiKey)) {
        return false;
    }
    
    $medexUrl = MedExConfig::baseUrl();
    // Ensure /cart/upload path is included for API endpoint
    if (!str_ends_with($medexUrl, '/cart/upload')) {
        $medexUrl .= '/cart/upload';
    }
    $apiUrl = $medexUrl . '/api/calendar_feeds.php';
    
    $postData = [
        'action' => 'delete',
        'practice_id' => $practiceId,
        'api_key' => $apiKey,
        'token' => $token
    ];
    
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_TIMEOUT => 30
    ]);
    
    curl_exec($ch);
    curl_close($ch);
    
    return true;
}

/**
 * List feeds for current user
 */
function listFeeds(): void
{
    global $currentUserId;
    
    try {
        ensureFeedsTableExists();
        
        $feeds = sqlStatement(
            "SELECT id, token, name, providers, facilities, provider_names, facility_names, created_at 
             FROM medex_calendar_feeds 
             WHERE openemr_user_id = ? 
             ORDER BY created_at DESC",
            [$currentUserId]
        );
        
        $feedList = [];
        $baseUrl = $GLOBALS['webroot'] ?? '';
        $serverUrl = getOpenEmrServerUrl();
        
        while ($row = sqlFetchArray($feeds)) {
            $feedList[] = [
                'id' => $row['id'],
                'token' => $row['token'],
                'name' => $row['name'],
                'url' => $serverUrl . $baseUrl . '/interface/modules/custom_modules/oe-module-medex/public/calendar_feed.php?feed=' . $row['token'],
                'provider_names' => json_decode($row['provider_names'] ?: '[]', true),
                'facility_names' => json_decode($row['facility_names'] ?: '[]', true),
                'created_at' => $row['created_at']
            ];
        }
        
        echo json_encode(['success' => true, 'feeds' => $feedList]);
        
    } catch (\Exception $e) {
        error_log('[MedEx Calendar] Failed to list feeds: ' . $e->getMessage());
        echo json_encode(['success' => true, 'feeds' => []]); // Return empty on error
    }
}

/**
 * Ensure the feeds table exists locally in OpenEMR
 */
function ensureFeedsTableExists(): void
{
    sqlStatement("
        CREATE TABLE IF NOT EXISTS medex_calendar_feeds (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(64) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            openemr_user_id INT,
            openemr_username VARCHAR(255),
            providers TEXT,
            facilities TEXT,
            provider_names TEXT,
            facility_names TEXT,
            created_at DATETIME,
            INDEX idx_token (token),
            INDEX idx_user_id (openemr_user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

// Get available providers and facilities for the dropdowns
$providers = [];
$facilities = [];
$userAuthorized = (int)($_SESSION['userauthorized'] ?? ($userInfo['authorized'] ?? 0));
$restrictUserFacility = !empty($GLOBALS['restrict_user_facility']);

// Match OpenEMR calendar visibility rules for facilities and providers.
$userFacilities = getUserFacilities($currentUserId);
if (!is_array($userFacilities)) {
    $userFacilities = [];
}

if (!$userAuthorized && $restrictUserFacility) {
    foreach ($userFacilities as $facility) {
        $facilityId = (int)($facility['id'] ?? 0);
        if ($facilityId <= 0) {
            continue;
        }
        $facilities[] = [
            'id' => $facilityId,
            'name' => (string)($facility['name'] ?? ('Facility ' . $facilityId)),
        ];
    }
} else {
    $facilityResult = sqlStatement("SELECT id, name FROM facility WHERE inactive = 0 ORDER BY name");
    while ($row = sqlFetchArray($facilityResult)) {
        $facilities[] = $row;
    }
}

$visibleProviders = [];
if (!$userAuthorized && $restrictUserFacility) {
    foreach ($facilities as $facility) {
        $facilityId = (int)($facility['id'] ?? 0);
        if ($facilityId <= 0) {
            continue;
        }
        $facilityProviders = getProviderInfo('%', true, $facilityId) ?? [];
        foreach ($facilityProviders as $provider) {
            $providerId = (int)($provider['id'] ?? 0);
            if ($providerId <= 0) {
                continue;
            }
            $visibleProviders[$providerId] = [
                'id' => $providerId,
                'fname' => (string)($provider['fname'] ?? ''),
                'lname' => (string)($provider['lname'] ?? ''),
            ];
        }
    }
} else {
    foreach ((getProviderInfo('%', true) ?? []) as $provider) {
        $providerId = (int)($provider['id'] ?? 0);
        if ($providerId <= 0) {
            continue;
        }
        $visibleProviders[$providerId] = [
            'id' => $providerId,
            'fname' => (string)($provider['fname'] ?? ''),
            'lname' => (string)($provider['lname'] ?? ''),
        ];
    }
}

foreach ($visibleProviders as $provider) {
    $provider['is_self'] = ((int)$provider['id'] === (int)$currentUserId);
    $providers[] = $provider;
}
usort($providers, static function (array $left, array $right): int {
    $leftKey = strtolower(trim((string)($left['lname'] ?? '') . ' ' . (string)($left['fname'] ?? '')));
    $rightKey = strtolower(trim((string)($right['lname'] ?? '') . ' ' . (string)($right['fname'] ?? '')));
    return $leftKey <=> $rightKey;
});

// Get user's existing feeds
$existingFeeds = [];
ensureFeedsTableExists();
$feedResult = sqlStatement(
    "SELECT id, token, name, provider_names, facility_names, created_at 
     FROM medex_calendar_feeds 
     WHERE openemr_user_id = ? 
     ORDER BY created_at DESC",
    [$currentUserId]
);
while ($row = sqlFetchArray($feedResult)) {
    $baseUrl = $GLOBALS['webroot'] ?? '';
    $serverUrl = getOpenEmrServerUrl();
    $row['url'] = $serverUrl . $baseUrl . '/interface/modules/custom_modules/oe-module-medex/public/calendar_feed.php?feed=' . $row['token'];
    $row['provider_names'] = json_decode($row['provider_names'] ?: '[]', true);
    $row['facility_names'] = json_decode($row['facility_names'] ?: '[]', true);
    $existingFeeds[] = $row;
}

$csrfToken = CsrfUtils::collectCsrfToken();
$providerCount = count($providers);
$facilityCount = count($facilities);
$providerCountLabel = $providerCount . ' ' . ($providerCount === 1 ? xlt('provider') : xlt('providers'));
$facilityCountLabel = $facilityCount . ' ' . ($facilityCount === 1 ? xlt('facility') : xlt('facilities'));
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Calendar Feeds'); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <link rel="stylesheet" href="<?php echo attr(($GLOBALS['webroot'] ?? '') . '/interface/modules/custom_modules/oe-module-medex/public/css/medex_monterey_calendar_feeds.css'); ?>">
</head>
<body>
<div class="calendar-feeds-container">
    <section class="page-hero">
        <div class="hero-topbar">
            <div>
                <h1 class="hero-title"><?php echo xlt('Calendar Feeds'); ?></h1>
                <p class="hero-sub"><?php echo xlt('Create authenticated iCal subscriptions for OpenEMR calendars. Each feed stays tied to your OpenEMR account and follows the Monterey-style MedEx workspace language.'); ?></p>
            </div>
            <div class="hero-chip-row">
                <div class="hero-chip">
                    <span class="hero-chip-label"><?php echo xlt('OpenEMR User'); ?></span>
                    <span class="hero-chip-value"><?php echo text($currentUsername); ?></span>
                </div>
                <div class="hero-chip">
                    <span class="hero-chip-label"><?php echo xlt('Existing Feeds'); ?></span>
                    <span class="hero-chip-value" id="existing-feed-count"><?php echo (int)count($existingFeeds); ?></span>
                </div>
            </div>
        </div>
    </section>

    <div class="page-grid">
        <div>
            <div class="section-card">
                <div class="section-title">
                    <i class="fa fa-rss"></i>
                    <?php echo xlt('Your Calendar Feeds'); ?>
                </div>

                <div id="feeds-list">
            <?php if (empty($existingFeeds)): ?>
            <div class="empty-state">
                <i class="fa fa-calendar-plus"></i>
                <p><?php echo xlt('No calendar feeds yet. Create one below to subscribe to your OpenEMR calendar.'); ?></p>
            </div>
            <?php else: ?>
            <?php foreach ($existingFeeds as $feed): ?>
            <div class="feed-item" data-feed-id="<?php echo attr($feed['id']); ?>">
                <div class="feed-header">
                    <div>
                        <div class="feed-name"><?php echo text($feed['name']); ?></div>
                        <div class="feed-filters">
                            <?php 
                            $filters = [];
                            if (!empty($feed['provider_names'])) {
                                $filters[] = implode(', ', $feed['provider_names']);
                            }
                            if (!empty($feed['facility_names'])) {
                                $filters[] = implode(', ', $feed['facility_names']);
                            }
                            echo text(implode(' | ', $filters) ?: xlt('All providers & facilities'));
                            ?>
                        </div>
                    </div>
                    <button onclick="deleteFeed(<?php echo attr($feed['id']); ?>)" class="btn btn-sm btn-danger" title="<?php echo xla('Delete'); ?>">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                <div class="feed-url"><?php echo text($feed['url']); ?></div>
                <div class="feed-actions">
                    <button onclick="copyUrl('<?php echo attr($feed['url']); ?>')" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-copy"></i> <?php echo xlt('Copy URL'); ?>
                    </button>
                    <span style="font-size: 12px; color: #666;">
                        <i class="fa fa-user"></i> <?php echo xlt('Username'); ?>: <strong><?php echo text($currentUsername); ?></strong>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">
                    <i class="fa fa-plus-circle"></i>
                    <?php echo xlt('Create New Calendar Feed'); ?>
                </div>
                <div class="setup-summary" style="display:flex; flex-direction:column; gap:4px; margin:-4px 0 16px 34px;">
                    <div style="white-space:nowrap;"><?php echo text($providerCountLabel); ?></div>
                    <div style="white-space:nowrap;"><?php echo text($facilityCountLabel); ?></div>
                </div>

                <div class="create-form">
                    <div>
                        <label class="meta-label"><?php echo xlt('Feed Name'); ?></label>
                        <input type="text" id="feed-name" class="form-control" value="<?php echo attr($userInfo['fname'] . ' ' . $userInfo['lname'] . ' - Calendar'); ?>" placeholder="<?php echo xla('e.g., My Work Calendar'); ?>" style="max-width: 430px;">
                    </div>

                    <div class="form-row">
                        <div>
                            <label class="meta-label">
                                <i class="fa fa-user-md"></i> <?php echo xlt('Select Providers'); ?>
                                <?php if (count($providers) === 1): ?>
                                <span class="meta-hint">(<?php echo xlt('Your calendar'); ?>)</span>
                                <?php endif; ?>
                            </label>
                            <?php if (count($providers) > 1): ?>
                            <div class="btn-select-group">
                                <button type="button" onclick="selectAll('provider')" class="btn btn-sm btn-outline-secondary"><?php echo xlt('All'); ?></button>
                                <button type="button" onclick="selectNone('provider')" class="btn btn-sm btn-outline-secondary"><?php echo xlt('None'); ?></button>
                            </div>
                            <?php endif; ?>
                            <div class="checkbox-grid">
                                <?php foreach ($providers as $provider): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" class="provider-checkbox" id="prov_<?php echo attr($provider['id']); ?>" value="<?php echo attr($provider['id']); ?>"<?php echo ($provider['is_self'] ?? false) ? ' checked' : ''; ?>>
                                    <label for="prov_<?php echo attr($provider['id']); ?>">
                                        <?php echo text($provider['lname'] . ', ' . $provider['fname']); ?>
                                        <?php if ($provider['is_self'] ?? false): ?><strong>(<?php echo xlt('You'); ?>)</strong><?php endif; ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div>
                            <label class="meta-label">
                                <i class="fa fa-hospital"></i> <?php echo xlt('Select Facilities'); ?>
                            </label>
                            <div class="btn-select-group">
                                <button type="button" onclick="selectAll('facility')" class="btn btn-sm btn-outline-secondary"><?php echo xlt('All'); ?></button>
                                <button type="button" onclick="selectNone('facility')" class="btn btn-sm btn-outline-secondary"><?php echo xlt('None'); ?></button>
                            </div>
                            <div class="checkbox-grid">
                                <?php foreach ($facilities as $facility): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" class="facility-checkbox" id="fac_<?php echo attr($facility['id']); ?>" value="<?php echo attr($facility['id']); ?>">
                                    <label for="fac_<?php echo attr($facility['id']); ?>">
                                        <?php echo text($facility['name']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="createFeed()" class="btn btn-success" style="width: fit-content;">
                        <i class="fa fa-plus"></i> <?php echo xlt('Create Calendar Feed'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div>
            <div class="section-card">
                <div class="section-title">
                    <i class="fa fa-compass"></i>
                    <?php echo xlt('Setup Guide'); ?>
                </div>

                <div class="instructions-section" style="margin-top:0;">
                    <h4><i class="fa fa-apple"></i> <?php echo xlt('Apple Calendar / macOS / iOS'); ?></h4>
                    <ol>
                        <li><?php echo xlt('Copy the feed URL after creating it'); ?></li>
                        <li><?php echo xlt('On Mac: Calendar → File → New Calendar Subscription...'); ?></li>
                        <li><?php echo xlt('On iPhone/iPad: Settings → Calendar → Accounts → Add Account → Other → Add Subscribed Calendar'); ?></li>
                        <li><?php echo xlt('Paste the URL and enter your OpenEMR login credentials when prompted'); ?></li>
                    </ol>
                </div>

                <div class="instructions-section" style="background:#fff8eb; border-color: rgba(245, 215, 145, 0.9);">
                    <h4><i class="fa fa-google"></i> <?php echo xlt('Google Calendar'); ?></h4>
                    <ol>
                        <li><?php echo xlt('Google Calendar does NOT support authenticated calendar subscriptions'); ?></li>
                        <li><?php echo xlt('Use Apple Calendar, Outlook, or another calendar app that supports HTTP Basic Auth'); ?></li>
                    </ol>
                </div>

                <div class="instructions-section">
                    <h4><i class="fa fa-windows"></i> <?php echo xlt('Outlook'); ?></h4>
                    <ol>
                        <li><?php echo xlt('Copy the feed URL'); ?></li>
                        <li><?php echo xlt('In Outlook: File → Account Settings → Internet Calendars → New'); ?></li>
                        <li><?php echo xlt('Paste the URL and enter your OpenEMR credentials'); ?></li>
                    </ol>
                </div>
            </div>

            <div class="hipaa-notice">
                <h4><i class="fa fa-shield-alt"></i> <?php echo xlt('HIPAA Compliance & Security'); ?></h4>
                <p><i class="fa fa-lock"></i> <strong><?php echo xlt('Password stays local'); ?></strong> - <?php echo xlt('Your OpenEMR password is never transmitted externally. Authentication happens entirely within OpenEMR.'); ?></p>
                <p><i class="fa fa-clipboard-list"></i> <strong><?php echo xlt('All access is logged'); ?></strong> - <?php echo xlt('Every calendar access is recorded for HIPAA compliance audit trail.'); ?></p>
                <p><i class="fa fa-user-lock"></i> <strong><?php echo xlt('Your feeds only'); ?></strong> - <?php echo xlt('Each feed is tied to your OpenEMR account. Only you can access your feeds.'); ?></p>
                <p><i class="fa fa-sync-alt"></i> <strong><?php echo xlt('If compromised'); ?></strong> - <?php echo xlt('Delete the feed immediately and create a new one. Consider changing your OpenEMR password.'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="toast-container"></div>

<script>
const csrfToken = <?php echo json_encode($csrfToken); ?>;
const currentUsername = <?php echo json_encode($currentUsername); ?>;

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function buildFeedFilterText(feed) {
    const filters = [];
    if (Array.isArray(feed.provider_names) && feed.provider_names.length) {
        filters.push(feed.provider_names.join(', '));
    }
    if (Array.isArray(feed.facility_names) && feed.facility_names.length) {
        filters.push(feed.facility_names.join(', '));
    }
    return filters.length ? filters.join(' | ') : '<?php echo xla('All providers & facilities'); ?>';
}

function buildEmptyFeedState() {
    const empty = document.createElement('div');
    empty.className = 'empty-state';
    empty.innerHTML = `
        <i class="fa fa-calendar-plus"></i>
        <p><?php echo xla('No calendar feeds yet. Create one below to subscribe to your OpenEMR calendar.'); ?></p>
    `;
    return empty;
}

function updateFeedListState() {
    const list = document.getElementById('feeds-list');
    const countEl = document.getElementById('existing-feed-count');
    if (!list) {
        return;
    }
    const itemCount = list.querySelectorAll('.feed-item').length;
    if (countEl) {
        countEl.textContent = String(itemCount);
    }
    const empty = list.querySelector('.empty-state');
    if (itemCount === 0) {
        if (!empty) {
            list.appendChild(buildEmptyFeedState());
        }
    } else if (empty) {
        empty.remove();
    }
}

function buildFeedItem(feed) {
    const row = document.createElement('div');
    row.className = 'feed-item';
    row.dataset.feedId = String(feed.id || '');
    row.innerHTML = `
        <div class="feed-header">
            <div>
                <div class="feed-name">${escapeHtml(feed.name || '')}</div>
                <div class="feed-filters">${escapeHtml(buildFeedFilterText(feed))}</div>
            </div>
            <button class="btn btn-sm btn-danger" title="<?php echo xla('Delete'); ?>">
                <i class="fa fa-trash"></i>
            </button>
        </div>
        <div class="feed-url">${escapeHtml(feed.url || '')}</div>
        <div class="feed-actions">
            <button class="btn btn-sm btn-outline-primary">
                <i class="fa fa-copy"></i> <?php echo xlt('Copy URL'); ?>
            </button>
            <span style="font-size: 12px; color: #666;">
                <i class="fa fa-user"></i> <?php echo xlt('Username'); ?>: <strong>${escapeHtml(currentUsername)}</strong>
            </span>
        </div>
    `;
    const deleteBtn = row.querySelector('.btn-danger');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            deleteFeed(feed.id);
        });
    }
    const copyBtn = row.querySelector('.btn-outline-primary');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            copyUrl(feed.url || '');
        });
    }
    return row;
}

// Toast notification system
function showToast(message, type = 'info', duration = null) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = 'toast ' + type;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <i class="fa ${icons[type] || icons.info}"></i>
        <span>${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()"><i class="fa fa-times"></i></button>
    `;
    
    container.appendChild(toast);

    if (duration === null) {
        duration = (type === 'success') ? 7000 : ((type === 'warning') ? 8500 : 10000);
    }
    
    if (duration > 0) {
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.45s ease forwards';
            setTimeout(() => toast.remove(), 450);
        }, duration);
    }
}

// Modal confirmation system
function showConfirmModal(title, message, confirmText, cancelText, onConfirm) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-header danger">
                <i class="fa fa-exclamation-triangle fa-lg"></i>
                <h4>${title}</h4>
            </div>
            <div class="modal-body">${message}</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">${cancelText}</button>
                <button class="btn btn-danger" data-confirm="true">${confirmText}</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    overlay.querySelector('[data-dismiss="modal"]').onclick = () => overlay.remove();
    overlay.querySelector('[data-confirm="true"]').onclick = () => {
        overlay.remove();
        onConfirm();
    };
    overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
}

function selectAll(type) {
    document.querySelectorAll('.' + type + '-checkbox').forEach(cb => cb.checked = true);
}

function selectNone(type) {
    document.querySelectorAll('.' + type + '-checkbox').forEach(cb => cb.checked = false);
}

function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('<?php echo xla('URL copied.'); ?>', 'success');
    }).catch(() => {
        // Fallback: select the URL text in the feed-url div
        const urlDiv = event.target.closest('.feed-item').querySelector('.feed-url');
        const range = document.createRange();
        range.selectNodeContents(urlDiv);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        showToast('<?php echo xla('Copy the selected URL manually (Cmd+C).'); ?>', 'info');
    });
}

function clearValidation() {
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

function createFeed() {
    clearValidation();
    
    const nameInput = document.getElementById('feed-name');
    const name = nameInput.value.trim();
    
    if (!name) {
        nameInput.classList.add('is-invalid');
        nameInput.focus();
        showToast('<?php echo xla('Enter a feed name.'); ?>', 'warning');
        return;
    }
    
    const providers = Array.from(document.querySelectorAll('.provider-checkbox:checked')).map(cb => cb.value);
    const facilities = Array.from(document.querySelectorAll('.facility-checkbox:checked')).map(cb => cb.value);
    
    if (providers.length === 0 && facilities.length === 0) {
        document.querySelectorAll('.checkbox-grid').forEach(el => el.classList.add('is-invalid'));
        showToast('<?php echo xla('Select at least one provider or facility.'); ?>', 'warning');
        return;
    }
    
    // Show loading state
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> <?php echo xla('Creating...'); ?>';
    
    const formData = new FormData();
    formData.append('csrf_token_form', csrfToken);
    formData.append('action', 'create_feed');
    formData.append('name', name);
    formData.append('providers', providers.join(','));
    formData.append('facilities', facilities.join(','));
    
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            const feedsList = document.getElementById('feeds-list');
            if (feedsList && data.feed) {
                const existingEmpty = feedsList.querySelector('.empty-state');
                if (existingEmpty) {
                    existingEmpty.remove();
                }
                feedsList.prepend(buildFeedItem(data.feed));
                updateFeedListState();
            }
            showToast('<?php echo xla('Feed created. Copy the URL below.'); ?>', 'success');
        } else {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            showToast(data.error || '<?php echo xla('Feed creation failed.'); ?>', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        showToast('<?php echo xla('Could not create the feed. Try again.'); ?>', 'error');
    });
}

function deleteFeed(feedId) {
    showConfirmModal(
        '<?php echo xla('Delete Calendar Feed'); ?>',
        '<?php echo xla('Are you sure you want to delete this calendar feed? Any calendar apps using it will stop syncing.'); ?>',
        '<?php echo xla('Delete Feed'); ?>',
        '<?php echo xla('Cancel'); ?>',
        () => {
            const feedItem = document.querySelector(`.feed-item[data-feed-id="${feedId}"]`);
            if (feedItem) {
                feedItem.style.opacity = '0.5';
                feedItem.style.pointerEvents = 'none';
            }
            
            const formData = new FormData();
            formData.append('csrf_token_form', csrfToken);
            formData.append('action', 'delete_feed');
            formData.append('feed_id', feedId);
            
            if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('<?php echo xla('Feed deleted.'); ?>', 'success');
                    if (feedItem) {
                        feedItem.style.transition = 'all 0.3s ease';
                        feedItem.style.transform = 'translateX(100%)';
                        feedItem.style.opacity = '0';
                        setTimeout(() => {
                            feedItem.remove();
                            updateFeedListState();
                        }, 300);
                    } else {
                        updateFeedListState();
                    }
                } else {
                    if (feedItem) {
                        feedItem.style.opacity = '1';
                        feedItem.style.pointerEvents = 'auto';
                    }
                    showToast(data.error || '<?php echo xla('Feed deletion failed.'); ?>', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                if (feedItem) {
                    feedItem.style.opacity = '1';
                    feedItem.style.pointerEvents = 'auto';
                }
                showToast('<?php echo xla('Could not delete the feed. Try again.'); ?>', 'error');
            });
        }
    );
}

// Clear validation on input
document.getElementById('feed-name').addEventListener('input', function() {
    this.classList.remove('is-invalid');
});
document.querySelectorAll('.provider-checkbox, .facility-checkbox').forEach(cb => {
    cb.addEventListener('change', clearValidation);
});
</script>
</body>
</html>
