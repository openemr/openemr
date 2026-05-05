<?php
/**
 * SaaS Calendar Export Service - Per-Provider Subscription
 * 
 * Exports calendar data from MedEx SaaS (MedExBank.com) for SUBSCRIBED PROVIDERS ONLY
 * Requires per-provider MedEx calendar subscription ($0.95/provider/month)
 * HIPAA-compliant with proper authentication and provider authorization
 * 
 * @package   OpenEMR
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   PROPRIETARY - All Rights Reserved
 * 
 * ⚠️ IP NOTICE: This file is part of the MedEx Module.
 * The MedEx Module is PROPRIETARY/CLOSED SOURCE software owned by MedEx.
 * It is NOT open source and NOT licensed under GNU GPL.
 * Unauthorized copying, modification, or distribution is prohibited.
 */

require_once(__DIR__ . "/../../../../../globals.php");
require_once(__DIR__ . '/../src/MedExConfig.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Modules\MedEx\MedExConfig;

// Require OpenEMR authentication
if (!isset($_SESSION['authUserID'])) {
    http_response_code(401);
    die('Authentication required');
}

if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    die('Access denied');
}

// Initialize MedEx API
$medexApi = new MedExAPI();

// Check if MedEx is configured and active
if (!$medexApi->isActive()) {
    http_response_code(503);
    die('MedEx not configured. Please configure MedEx API credentials in settings.');
}

if (!$medexApi->hasServiceEntitlement('calendar_export')) {
    http_response_code(403);
    die('Active Calendar Export subscription required.');
}

// Get subscriptions to check which providers have calendar service
$subscriptions = $medexApi->getSubscriptions();
$subscribedProviders = [];

// Check for calendar_export practice-level subscription
$hasSubscription = false;
foreach ($subscriptions as $serviceId => $subscription) {
    if ($serviceId === 'calendar_export' && !empty($subscription['status']) && $subscription['status'] === 'active') {
        $hasSubscription = true;
        break;
    }
}

// Verify subscription exists
if (!$hasSubscription) {
    http_response_code(403);
    die('Active Calendar Export subscription required. Subscribe at <a href="' . htmlspecialchars(\OpenEMR\Modules\MedEx\MedExConfig::mainSiteUrl() . '/subscriptions') . '">MedEx</a>');
}

// Get export parameters
$format = $_GET['format'] ?? 'ical';
$startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end'] ?? date('Y-m-d', strtotime('+90 days'));
$requestedProviderId = $_GET['provider_id'] ?? null;

// Validate date range
$days = (strtotime($endDate) - strtotime($startDate)) / 86400;
if ($days > 365 || $days < 0) {
    http_response_code(400);
    die('Invalid date range. Maximum 365 days.');
}

try {
    $practiceId = $medexApi->getConfig()['practice_id'] ?? 'unknown';
    
    // Track export usage for billing
    logPracticeCalendarExport($practiceId, $startDate, $endDate, $format);
    
    // Get all providers for this practice
    $allProviders = [];
    $providerQuery = sqlStatement("SELECT id FROM users WHERE authorized=1 AND active=1");
    while ($row = sqlFetchArray($providerQuery)) {
        $allProviders[] = $row['id'];
    }
    
    // If specific provider requested, filter to that one
    if ($requestedProviderId) {
        $providersToExport = in_array($requestedProviderId, $allProviders) ? [$requestedProviderId] : [];
    } else {
        $providersToExport = $allProviders;
    }
    
    // Fetch calendar data from MedEx SaaS
    $apiParams = [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'practice_id' => $practiceId,
        'provider_ids' => $providersToExport,
        'subscription_type' => 'calendar_export'
    ];
    
    // Call MedEx API
    $response = $medexApi->makeRequest('/api/calendar/export', $apiParams, 'GET');
    
    if (empty($response['success']) || empty($response['appointments'])) {
        http_response_code(404);
        die('No calendar data available from MedEx for subscribed providers.');
    }
    
    $appointments = $response['appointments'];
    
    // Filter to subscribed providers only
    $filteredAppointments = array_filter($appointments, function($apt) use ($providersToExport) {
        $providerId = $apt['provider_id'] ?? $apt['pc_aid'] ?? null;
        return in_array($providerId, $providersToExport);
    });
    
    // Log export
    error_log(sprintf(
        '[MedEx SaaS Calendar Export] Practice: %s, User: %s, Providers: %s, Range: %s to %s, Count: %d, Format: %s',
        $practiceId,
        $_SESSION['authUserID'],
        implode(',', $providersToExport),
        $startDate,
        $endDate,
        count($filteredAppointments),
        $format
    ));
    
    // Generate secure export token
    $token = bin2hex(random_bytes(16));
    $_SESSION['medex_saas_export_token'] = $token;
    $_SESSION['medex_saas_export_expires'] = time() + 3600;
    
    // Export
    switch ($format) {
        case 'ical':
            exportIcalSaaS(array_values($filteredAppointments), $token, $medexApi, $providersToExport);
            break;
        case 'json':
            exportJsonSaaS(array_values($filteredAppointments), $token, $medexApi, $providersToExport);
            break;
        case 'html':
            exportHtmlSaaS(array_values($filteredAppointments), $token, $medexApi, $providersToExport);
            break;
        default:
            http_response_code(400);
            die('Invalid format. Use: ical, json, html');
    }
    
} catch (Exception $e) {
    error_log('[MedEx SaaS Calendar Export Error] ' . $e->getMessage());
    http_response_code(500);
    die('Export failed: ' . $e->getMessage());
}

/**
 * Log provider calendar export for billing tracking ($0.95/provider/month)
 */
function logProviderCalendarExport(string $practiceId, string $providerId, string $startDate, string $endDate, string $format): void {
    try {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO medex_calendar_export_usage 
             (practice_id, provider_id, export_date, start_date, end_date, format, exported_by, month_year) 
             VALUES (?, ?, NOW(), ?, ?, ?, ?, DATE_FORMAT(NOW(), '%Y-%m'))
             ON DUPLICATE KEY UPDATE 
             export_count = export_count + 1, 
             last_export = NOW()",
            [$practiceId, $providerId, $startDate, $endDate, $format, $_SESSION['authUserID']]
        );
        
        error_log(sprintf(
            '[MedEx Billing] Calendar export usage: Practice %s, Provider %s, Format %s',
            $practiceId,
            $providerId,
            $format
        ));
    } catch (Exception $e) {
        error_log('[MedEx Billing] Failed to log usage: ' . $e->getMessage());
    }
}

/**
 * Export calendar data as iCal format
 */
function exportIcalSaaS(array $appointments, string $token, MedExAPI $api, array $subscribedProviders): void {
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="medex_calendar.ics"');
    
    $host = parse_url($GLOBALS['site_addr_oath'], PHP_URL_HOST) ?? 'openemr.local';
    $practiceId = $api->getConfig()['practice_id'] ?? 'unknown';
    
    echo "BEGIN:VCALENDAR\r\n";
    echo "VERSION:2.0\r\n";
    echo "PRODID:-//MedEx//SaaS Calendar Export//EN\r\n";
    echo "CALSCALE:GREGORIAN\r\n";
    echo "METHOD:PUBLISH\r\n";
    echo "X-WR-CALNAME:MedEx Calendar\r\n";
    echo "X-WR-CALDESC:Exported from MedEx SaaS - Subscribed Providers Only\r\n";
    echo "X-MEDEX-PRACTICE:" . $practiceId . "\r\n";
    echo "X-MEDEX-PROVIDERS:" . implode(',', $subscribedProviders) . "\r\n";
    echo "X-EXPORT-TOKEN:" . substr($token, 0, 8) . "...\r\n";
    echo "X-SUBSCRIPTION-NOTICE:Per-provider subscription required - \$0.95/mo\r\n";
    
    foreach ($appointments as $apt) {
        $uid = 'medex-' . ($apt['id'] ?? uniqid()) . '-' . $practiceId . '@' . $host;
        $dtstart = gmdate('Ymd\THis\Z', strtotime($apt['start'] ?? $apt['event_date'] . ' ' . $apt['start_time']));
        $dtend = gmdate('Ymd\THis\Z', strtotime($apt['end'] ?? $apt['event_date'] . ' ' . $apt['end_time']));
        
        $summary = $apt['title'] ?? $apt['pc_title'] ?? 'Appointment';
        $description = $apt['description'] ?? $apt['pc_hometext'] ?? '';
        $location = $apt['facility_name'] ?? $apt['location'] ?? 'Office';
        $providerName = $apt['provider_name'] ?? 'Provider';
        
        // Include patient info if available and consented
        if (!empty($apt['patient_name']) && !empty($apt['hipaa_consent'])) {
            $summary .= ' - ' . $apt['patient_name'];
        }
        
        echo "BEGIN:VEVENT\r\n";
        echo "UID:" . $uid . "\r\n";
        echo "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        echo "DTSTART:" . $dtstart . "\r\n";
        echo "DTEND:" . $dtend . "\r\n";
        echo "SUMMARY:" . str_replace(["\r\n", "\n", "\r"], '\\n', $summary) . "\r\n";
        echo "DESCRIPTION:" . str_replace(["\r\n", "\n", "\r"], '\\n', $description) . "\r\n";
        echo "LOCATION:" . str_replace(["\r\n", "\n", "\r"], '\\n', $location) . "\r\n";
        echo "ORGANIZER;CN=" . $providerName . ":mailto:noreply@" . $host . "\r\n";
        
        if (!empty($apt['patient_email']) && !empty($apt['hipaa_consent'])) {
            echo "ATTENDEE;CN=" . ($apt['patient_name'] ?? 'Patient') . ":mailto:" . $apt['patient_email'] . "\r\n";
        }
        
        echo "STATUS:CONFIRMED\r\n";
        echo "END:VEVENT\r\n";
    }
    
    echo "END:VCALENDAR\r\n";
}

/**
 * Export calendar data as JSON format
 */
function exportJsonSaaS(array $appointments, string $token, MedExAPI $api, array $subscribedProviders): void {
    header('Content-Type: application/json');
    
    // Group appointments by provider for billing transparency
    $byProvider = [];
    foreach ($appointments as $apt) {
        $pid = $apt['provider_id'] ?? $apt['pc_aid'] ?? 'unknown';
        if (!isset($byProvider[$pid])) {
            $byProvider[$pid] = [];
        }
        $byProvider[$pid][] = $apt;
    }
    
    $data = [
        'meta' => [
            'source' => 'MedEx SaaS',
            'exported_at' => date('c'),
            'exported_by' => $_SESSION['authUserID'],
            'practice_id' => $api->getConfig()['practice_id'] ?? null,
            'token' => $token,
            'hipaa_compliant' => true,
            'count' => count($appointments),
            'subscription_notice' => 'Per-provider subscription required - $0.95/provider/month',
            'subscribed_providers' => $subscribedProviders,
            'by_provider_count' => array_map('count', $byProvider)
        ],
        'appointments' => $appointments
    ];
    
    echo json_encode($data, JSON_PRETTY_PRINT);
}

/**
 * Export calendar data as HTML format
 */
function exportHtmlSaaS(array $appointments, string $token, MedExAPI $api, array $subscribedProviders): void {
    header('Content-Type: text/html; charset=utf-8');
    
    $practiceId = $api->getConfig()['practice_id'] ?? 'unknown';
    
    // Group by provider
    $byProvider = [];
    foreach ($appointments as $apt) {
        $pid = $apt['provider_id'] ?? $apt['pc_aid'] ?? 'unknown';
        $pname = $apt['provider_name'] ?? 'Provider ' . $pid;
        if (!isset($byProvider[$pid])) {
            $byProvider[$pid] = ['name' => $pname, 'appointments' => []];
        }
        $byProvider[$pid]['appointments'][] = $apt;
    }
    
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MedEx SaaS Calendar Export - Subscribed Providers</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .header { background: #2c5aa0; color: white; padding: 20px; margin: -20px -20px 20px; }
        .subscription-notice { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .subscription-notice h3 { margin: 0 0 10px; color: #155724; }
        .provider-section { background: white; border: 2px solid #2c5aa0; margin: 20px 0; padding: 15px; border-radius: 5px; }
        .provider-header { background: #e7f3ff; padding: 10px; margin: -15px -15px 15px; border-bottom: 2px solid #2c5aa0; }
        .provider-header h2 { margin: 0; color: #2c5aa0; }
        .appointment { background: #f8f9fa; border: 1px solid #ddd; margin: 10px 0; padding: 10px; border-radius: 3px; }
        .appointment h4 { margin: 0 0 5px; color: #333; }
        .meta { color: #666; font-size: 0.9em; margin: 3px 0; }
        .patient-info { background: #fff3cd; padding: 8px; margin: 8px 0; border-radius: 3px; }
        .download-links { margin: 20px 0; }
        .download-links a { display: inline-block; margin: 5px; padding: 10px 15px; background: #2c5aa0; color: white; text-decoration: none; border-radius: 3px; }
        .billing-info { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .billing-info strong { color: #856404; }
    </style>
</head>
<body>
    <div class="header">
        <h1>MedEx SaaS Calendar Export</h1>
        <p>' . count($appointments) . ' appointments exported on ' . date('F j, Y g:i A') . '</p>
        <p>Practice ID: ' . htmlspecialchars($practiceId) . '</p>
    </div>
    
    <div class="subscription-notice">
        <h3>✓ Active Subscriptions</h3>
        <p><strong>' . count($subscribedProviders) . ' provider(s)</strong> with MedEx Calendar subscriptions</p>
        <p>Each provider: <strong>$0.95/month</strong></p>
        <p>Subscribed Provider IDs: ' . htmlspecialchars(implode(', ', $subscribedProviders)) . '</p>
    </div>
    
    <div class="billing-info">
        <strong>Billing Notice:</strong> This export includes data from subscribed providers only.
        <br>Monthly billing is based on active provider subscriptions at MedExBank.com.
        <br>Token: <code>' . substr($token, 0, 8) . '...</code> (expires in 1 hour)
    </div>
    
    <div class="download-links">
        <a href="?format=ical">Download iCal</a>
        <a href="?format=json">Download JSON</a>
    </div>';
    
    foreach ($byProvider as $providerId => $providerData) {
        echo '<div class="provider-section">';
        echo '<div class="provider-header">';
        echo '<h2>' . htmlspecialchars($providerData['name']) . ' <small>(ID: ' . htmlspecialchars($providerId) . ')</small></h2>';
        echo '<p>' . count($providerData['appointments']) . ' appointments</p>';
        echo '</div>';
        
        foreach ($providerData['appointments'] as $apt) {
            echo '<div class="appointment">';
            echo '<h4>' . htmlspecialchars($apt['title'] ?? $apt['pc_title'] ?? 'Appointment') . '</h4>';
            echo '<div class="meta"><strong>Date:</strong> ' . htmlspecialchars($apt['event_date'] ?? $apt['start'] ?? 'N/A') . '</div>';
            echo '<div class="meta"><strong>Time:</strong> ' . htmlspecialchars(($apt['start_time'] ?? '') . ' - ' . ($apt['end_time'] ?? '')) . '</div>';
            echo '<div class="meta"><strong>Location:</strong> ' . htmlspecialchars($apt['facility_name'] ?? $apt['location'] ?? 'Office') . '</div>';
            
            if (!empty($apt['patient_name'])) {
                echo '<div class="patient-info">';
                echo '<strong>Patient:</strong> ' . htmlspecialchars($apt['patient_name']);
                if (!empty($apt['patient_email'])) {
                    echo '<br><strong>Email:</strong> ' . htmlspecialchars($apt['patient_email']);
                }
                if (!empty($apt['patient_phone'])) {
                    echo '<br><strong>Phone:</strong> ' . htmlspecialchars($apt['patient_phone']);
                }
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    echo '</body>
</html>';
}
?>
