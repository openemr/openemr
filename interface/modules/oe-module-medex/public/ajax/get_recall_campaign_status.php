<?php

/**
 * AJAX endpoint for fetching MedEx campaign status for recalls
 *
 * Returns campaign status icons HTML for a specific recall
 * Uses the same icon building logic as patient tracker appointments
 *
 * @package   MedEx
 * @link      https://www.medexbank.com
 * @author    MedEx <support@medexbank.com>
 * @copyright Copyright (c) 2017-2026 MedEx
 * @license   Proprietary
 */

require_once(__DIR__ . '/../../../../../globals.php');
require_once(__DIR__ . '/../../src/MedExAPI.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\MedEx\MedExAPI;

$globalsBag = OEGlobalsBag::getInstance();

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '', 'default')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// Verify user has access to patient data
if (!AclMain::aclCheckCore('patients', 'appt')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

$entitlementApi = new MedExAPI();
if (!$entitlementApi->hasAnyServiceEntitlement(['appointment_reminders', 'medex_messages'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Appointment reminders service is not enabled']);
    exit;
}

/**
 * Format campaign type name for display
 * SMS -> Text Message, EMAIL -> E-mail, AVM -> Voice Call
 */
function formatCampaignType(string $msgType): string
{
    $names = [
        'SMS' => 'Text Message',
        'EMAIL' => 'E-mail',
        'AVM' => 'Voice Call',
        'POSTCARD' => 'Postcard'
    ];
    return $names[$msgType] ?? ucfirst(strtolower($msgType));
}

// Get parameters
$pid = $_POST['pid'] ?? null;

if (!$pid) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Check if MedEx is enabled and configured
$medexEnabled = $globalsBag->get('medex_enable') ?? '0';
if ($medexEnabled != '1') {
    echo json_encode(['success' => false, 'error' => 'MedEx not enabled']);
    exit;
}

// Load MedEx icons from database
$icons = [];
$query2 = "SELECT * FROM medex_icons";
$iconRows = QueryUtils::fetchRecords($query2);
foreach ($iconRows as $icon) {
    $icons[$icon['msg_type']][$icon['msg_status']]['html'] = $icon['i_html'];
}

// Get campaign configuration from medex_prefs to know what campaigns are configured
$prefsQuery = "SELECT status FROM medex_prefs LIMIT 1";
$prefsRow = QueryUtils::querySingleRow($prefsQuery, []);
$recallCampaigns = [];
if ($prefsRow && !empty($prefsRow['status'])) {
    $statusData = json_decode($prefsRow['status'], true);
    error_log('[MedEx] Recall campaign - status JSON structure: ' . print_r($statusData, true));

    if (isset($statusData['campaigns']['events'])) {
        foreach ($statusData['campaigns']['events'] as $event) {
            // Only process RECALL campaigns
            if ($event['M_group'] === 'RECALL') {
                $recallCampaigns[] = $event;
            }
        }
    }
    error_log('[MedEx] Recall campaign - found ' . count($recallCampaigns) . ' RECALL campaigns');
}

// Get patient's recall date and HIPAA preferences to calculate when campaigns will fire
$recallQuery = "SELECT pr.r_eventDate, pd.hipaa_voice, pd.hipaa_allowsms, pd.hipaa_allowemail
                FROM patient_recalls pr
                INNER JOIN patient_data pd ON pd.pid = pr.r_pid
                WHERE pr.r_pid = ?";
$recallRow = QueryUtils::querySingleRow($recallQuery, [$pid]);
$recallDate = $recallRow['r_eventDate'] ?? null;
$hipaaVoice = $recallRow['hipaa_voice'] ?? 'YES'; // Default to YES if not set
$hipaaAllowSMS = $recallRow['hipaa_allowsms'] ?? 'YES'; // SMS permission
$hipaaAllowEmail = $recallRow['hipaa_allowemail'] ?? 'YES'; // Email permission

// Query MedEx outgoing messages for this recall (using recall_pid as eid)
$query = "SELECT * FROM medex_outgoing WHERE msg_pc_eid = ? ORDER BY medex_uid ASC";
$medexRows = QueryUtils::fetchRecords($query, ['recall_' . $pid]);

// Initialize variables
$icon_here = [];
$icon2_here = '';
$icon_extra = '';
$appointment = [];
$campaignScheduled = [];

// Build icons using same logic as appointments
$patientMessages = []; // Collect patient messages for speech bubble
foreach ($medexRows as $row) {
    if ($row['msg_reply'] == 'Other') {
        // Collect patient message for speech bubble display
        $patientMessages[] = [
            'date' => oeFormatShortDate($row['msg_date']),
            'text' => $row['msg_extra_text']
        ];
        continue;
    } elseif ($row['msg_reply'] == 'CANCELLED') {
        $appointment[$row['msg_type']]['stage'] = "CANCELLED";
        $icon_here[$row['msg_type']] = '';
    } elseif ($row['msg_reply'] == "FAILED") {
        $appointment[$row['msg_type']]['stage'] = "FAILED";
        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['FAILED']['html'] ?? '';
    } elseif (($row['msg_reply'] == "CONFIRMED") || ($appointment[$row['msg_type']]['stage'] ?? '') == "CONFIRMED") {
        $appointment[$row['msg_type']]['stage'] = "CONFIRMED";
        $icon_here[$row['msg_type']]  = $icons[$row['msg_type']]['CONFIRMED']['html'] ?? '';
    } elseif (($row['msg_reply'] == "READ") || ($appointment[$row['msg_type']]['stage'] ?? '') == "READ") {
        $appointment[$row['msg_type']]['stage'] = "READ";
        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['READ']['html'] ?? '';
    } elseif (($row['msg_reply'] == "SENT") || ($appointment[$row['msg_type']]['stage'] ?? '') == "SENT") {
        $appointment[$row['msg_type']]['stage'] = "SENT";
        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SENT']['html'] ?? '';
    } elseif (($row['msg_reply'] == "To Send") || empty($appointment[$row['msg_type']]['stage'] ?? '')) {
        if (!in_array($appointment[$row['msg_type']]['stage'] ?? '', ["CONFIRMED", "READ", "SENT", "FAILED"])) {
            $appointment[$row['msg_type']]['stage'] = "QUEUED";
            $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SCHEDULED']['html'] ?? '';

            // Track scheduled campaign info for tooltip
            $campaignScheduled[] = [
                'type' => formatCampaignType($row['msg_type']),
                'date' => oeFormatShortDate($row['msg_date'])
            ];
        }
    }

    // Additional icons
    if ($row['msg_reply'] == "CALL") {
        $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['CALL']['html'] ?? '';
    } elseif ($row['msg_reply'] == "STOP") {
        $icon2_here .= $icons[$row['msg_type']]['STOP']['html'] ?? '';
    } elseif ($row['msg_reply'] == "Other") {
        $icon2_here .= $icons[$row['msg_type']]['Other']['html'] ?? '';
    }
}

// Build patient message speech bubble if there are any messages
if (!empty($patientMessages)) {
    $messageText = '';
    foreach ($patientMessages as $msg) {
        $messageText .= '📅 ' . $msg['date'] . "\n";
        $messageText .= $msg['text'] . "\n\n";
    }
    $messageText = trim($messageText);

    // Create speech bubble tooltip icon
    $icon_extra = '<span class="medex-patient-message-icon" data-tooltip="' . attr($messageText) . '" style="margin-left:5px;position:relative;">
                        <i class="fas fa-comment-dots fa-2x" style="color:#9C27B0;"></i>
                   </span>';
}

// If no medex_outgoing rows exist yet, calculate from campaign configuration
if (empty($medexRows) && !empty($recallCampaigns) && $recallDate) {
    // Calculate when each campaign will fire based on recall date
    foreach ($recallCampaigns as $campaign) {
        $daysBefor = (int)($campaign['E_fire_time'] ?? 0);
        $msgType = $campaign['M_type'] ?? '';

        if ($daysBefor > 0 && $msgType) {
            // Calculate fire date: recall_date - days_before
            $fireDate = date('Y-m-d', strtotime($recallDate . ' -' . $daysBefor . ' days'));
            $formattedDate = oeFormatShortDate($fireDate);

            // Use the actual MedEx SCHEDULED icon from medex_icons table for this modality
            $iconHtml = $icons[$msgType]['SCHEDULED']['html'] ?? '';

            // Check patient HIPAA preferences and add ban icon if declined
            $isDeclined = false;
            if ($msgType === 'AVM' && $hipaaVoice === 'NO') {
                $isDeclined = true;
            } elseif ($msgType === 'SMS' && $hipaaAllowSMS === 'NO') {
                $isDeclined = true;
            } elseif ($msgType === 'EMAIL' && $hipaaAllowEmail === 'NO') {
                $isDeclined = true;
            }

            // Add tooltip showing when campaign fires
            if ($iconHtml) {
                $campaignName = formatCampaignType($msgType);
                $tooltipText = $campaignName . ' Recall Campaign scheduled for ' . $formattedDate;
                if ($isDeclined) {
                    $tooltipText .= ' (Patient declined automated ' . strtolower($campaignName) . ')';
                }

                // Remove default title and add data-tooltip only (JavaScript will init Bootstrap tooltip)
                $iconWithTooltip = str_replace(
                    'title="' . $msgType . ' scheduled"',
                    'data-tooltip="' . attr($tooltipText) . '"',
                    $iconHtml
                );

                // Add ban icon overlay if patient declined this modality
                if ($isDeclined) {
                    // Make button position:relative and add ban icon centered over it
                    $iconWithTooltip = str_replace(
                        '<span class="btn',
                        '<span class="btn medex-declined',
                        $iconWithTooltip
                    );
                    $iconWithTooltip = str_replace(
                        '</i></span>',
                        '</i><i class="fa fa-ban" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);font-size:20px;color:#dc3545;opacity:0.8;pointer-events:none;"></i></span>',
                        $iconWithTooltip
                    );
                }

                $icon_here[$msgType] = $iconWithTooltip;
            }
        }
    }
}

// Combine icons
$html = implode('', $icon_here) . $icon2_here . $icon_extra;

// Return JSON response with HIPAA preferences for Contact column icons
echo json_encode([
    'success' => true,
    'html' => $html,
    'campaigns' => $campaignScheduled,
    'modalities' => [
        'ALLOWED' => [
            'AVM' => $hipaaVoice === 'YES' ? 'YES' : 'NO',
            'SMS' => $hipaaAllowSMS === 'YES' ? 'YES' : 'NO',
            'EMAIL' => $hipaaAllowEmail === 'YES' ? 'YES' : 'NO'
        ]
    ]
]);
