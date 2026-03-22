<?php
/**
 * Get Overview Dashboard Data
 *
 * Returns system status, subscription summary, stats, and recent activity
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo '<div class="overview-card"><p style="color: #dc3545;">' . xlt('Access denied') . '</p></div>';
    exit;
}

// Load MedEx API
require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Get system status
$isConfigured = $api->isConfigured();
$isActive = $api->isActive();
try {
    $loginData = $api->login(false); // Get cached login data
} catch (\Exception $e) {
    $loginData = []; // Not configured or invalid credentials - proceed gracefully
}
$medexUsername = $loginData['email'] ?? null;

// Fallback to ME_username from medex_prefs if login data not available
if (!$medexUsername) {
    $prefs = sqlQuery("SELECT ME_username FROM medex_prefs WHERE ME_username IS NOT NULL LIMIT 1");
    $medexUsername = $prefs['ME_username'] ?? null;
}

// Get last sync time
$lastSync = sqlQuery("SELECT MedEx_lastupdated FROM medex_prefs LIMIT 1");
$lastSyncTime = $lastSync['MedEx_lastupdated'] ?? null;
$lastSyncFormatted = $lastSyncTime ? date('M j, Y g:i A', strtotime($lastSyncTime)) : 'Never';
$minutesAgo = $lastSyncTime ? round((time() - strtotime($lastSyncTime)) / 60) : null;

// Fallback catalog prices removed — prices come from oc_product_recurring via getPricing().
// Hardcoded prices go stale whenever OpenCart admin changes a recurring plan.

// Get subscriptions — getSubscriptions() returns a wrapper array with keys
// 'subscriptions', 'active_services', 'pricing', 'customer_group_id'.
// Iterate over the nested 'subscriptions' map, not the top-level wrapper.
$subscriptionsData = $api->getSubscriptions();
$subscriptions = $subscriptionsData['subscriptions'] ?? [];

// Pull live prices from DB (same source as Services page) — force=true bypasses cache.
$livePricing = $api->getPricing(true);
$livePricingServices = $livePricing['services'] ?? [];

$activeSubscriptions = [];
$totalMonthlyCost = 0;

foreach ($subscriptions as $key => $sub) {
    if ($sub['active'] === true && $sub['status'] === 'active') {
        // Prefer live price from oc_product_recurring; fall back to subscription's own price field.
        $livePrice = $livePricingServices[$key]['price'] ?? null;
        $resolvedPrice = ($livePrice !== null && $livePrice > 0) ? $livePrice : (float)($sub['price'] ?? 0);
        $sub['resolved_price'] = $resolvedPrice;
        $activeSubscriptions[$key] = $sub;
        $totalMonthlyCost += $resolvedPrice;
    }
}

// Get stats (last 30 days)
$thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

// Messages sent
$messagesSent = sqlQuery("SELECT COUNT(*) as count FROM medex_outgoing WHERE msg_date >= ?", [$thirtyDaysAgo]);
$messagesSentCount = $messagesSent['count'] ?? 0;

// Confirmed appointments (replies with 'CONFIRMED' status)
$confirmedAppts = sqlQuery("SELECT COUNT(*) as count FROM medex_outgoing WHERE msg_date >= ? AND msg_reply LIKE '%CONFIRM%'", [$thirtyDaysAgo]);
$confirmedCount = $confirmedAppts['count'] ?? 0;
$confirmRate = $messagesSentCount > 0 ? round(($confirmedCount / $messagesSentCount) * 100) : 0;

// Campaign counts by type - query API which uses hipaa_campaigns
$campaignCounts = [
    'reminder' => 0,
    'recall' => 0,
    'gogreen' => 0,
    'announce' => 0,
    'clinical' => 0
];
try {
    $campaigns = $api->getCampaigns();
    if (!empty($campaigns)) {
        foreach ($campaigns as $campaign) {
            $type = strtolower($campaign['type'] ?? 'reminder');
            // Normalize type names
            if ($type === 'gogreens') $type = 'gogreen';
            if ($type === 'announcements' || $type === 'announcement') $type = 'announce';
            if ($type === 'clinical_reminder') $type = 'clinical';
            if (isset($campaignCounts[$type])) {
                $campaignCounts[$type]++;
            }
        }
    }
} catch (\Exception $e) {
    // Fallback: check local status JSON
    $prefsResult = sqlQuery("SELECT status FROM medex_prefs LIMIT 1");
    if ($prefsResult && !empty($prefsResult['status'])) {
        $status = json_decode($prefsResult['status'], true);
        if (isset($status['status']['campaigns']) && is_array($status['status']['campaigns'])) {
            foreach ($status['status']['campaigns'] as $campaign) {
                $type = strtolower($campaign['type'] ?? 'reminder');
                if ($type === 'gogreens') $type = 'gogreen';
                if ($type === 'announcements' || $type === 'announcement') $type = 'announce';
                if (isset($campaignCounts[$type])) {
                    $campaignCounts[$type]++;
                }
            }
        }
    }
}
$totalCampaigns = array_sum($campaignCounts);

// Clinical reminders (patient reminders due)
$clinicalDueCount = sqlQuery("SELECT COUNT(DISTINCT pid) as cnt FROM patient_reminders WHERE active = 1 AND due_status IN ('soon', 'due', 'past_due')");
$patientsDueClinical = (int)($clinicalDueCount['cnt'] ?? 0);

// Service status - Base package includes SMS/Email
// appointment_reminders is the base package
$basePackage = $activeSubscriptions['appointment_reminders'] ?? null;
$baseActive = $basePackage && ($basePackage['status'] ?? '') === 'active';
$baseProviderCount = $basePackage['provider_count'] ?? 0;
$baseProviderIds = [];
if (!empty($basePackage['provider_ids'])) {
    $baseProviderIds = is_array($basePackage['provider_ids']) ? $basePackage['provider_ids'] : json_decode($basePackage['provider_ids'], true) ?? [];
}

// Count total providers in practice
$totalProviders = sqlQuery("SELECT COUNT(*) as cnt FROM users WHERE authorized = 1 AND active = 1");
$totalProviderCount = (int)($totalProviders['cnt'] ?? 0);
if ($totalProviderCount < 1) $totalProviderCount = 3; // Default assumption

// Check if Dial 0 is enabled (requires base package AND dial0 campaign configured)
$dial0Enabled = false;
if ($baseActive && !empty($campaigns)) {
    foreach ($campaigns as $campaign) {
        if (strtolower($campaign['type'] ?? '') === 'dial0') {
            $dial0Enabled = true;
            break;
        }
    }
}

// Add-on services status
$serviceStatus = [
    'base_active' => $baseActive,
    'base_providers' => count($baseProviderIds),
    'total_providers' => $totalProviderCount,
    'dial_0' => $dial0Enabled, // Dial 0 requires activation within base package
    'vfax' => isset($activeSubscriptions['vfax']) || isset($activeSubscriptions['virtual_fax']),
    'ai_rescheduler' => isset($activeSubscriptions['calendar_ai']),
    'secure_chat' => isset($activeSubscriptions['secure_chat']),
    'whatsapp' => isset($activeSubscriptions['whatsapp']),
    'calendar_view' => isset($activeSubscriptions['calendar_view']) || isset($activeSubscriptions['calendar_export']),
    'pdf_management' => isset($activeSubscriptions['pdf_management'])
];

// Usage stats for active services
$usageStats = [];

// Secure Chat count (if active)
if ($serviceStatus['secure_chat']) {
    $chatCount = sqlQuery("SELECT COUNT(*) as cnt FROM medex_outgoing WHERE msg_type = 'CHAT' AND msg_date >= ?", [$thirtyDaysAgo]);
    $usageStats['secure_chats'] = (int)($chatCount['cnt'] ?? 0);
}

// AI Rescheduler events (if active)
if ($serviceStatus['ai_rescheduler']) {
    $aiEvents = sqlQuery("SELECT COUNT(*) as cnt FROM medex_outgoing WHERE msg_type IN ('AI_RESCHEDULE','RESCHEDULE') AND msg_date >= ?", [$thirtyDaysAgo]);
    $usageStats['ai_reschedules'] = (int)($aiEvents['cnt'] ?? 0);
}

// Scheduled/pending messages waiting to go out
$pendingMessages = sqlQuery("SELECT COUNT(*) as cnt FROM medex_outgoing WHERE msg_date > NOW() AND (msg_reply IS NULL OR msg_reply = '')");
$pendingCount = (int)($pendingMessages['cnt'] ?? 0);

// Calendar events (from integrated calendar) - next 7 days
$upcomingEvents = sqlQuery("SELECT COUNT(*) as count FROM openemr_postcalendar_events WHERE pc_eventDate >= CURDATE() AND pc_eventDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$eventsCount = $upcomingEvents['count'] ?? 0;

// Get recent activity
$recentActivity = [];

// Recent sync
if ($lastSyncTime) {
    $recentActivity[] = [
        'icon' => 'fa-sync-alt',
        'text' => 'Practice data synced',
        'time' => $lastSyncFormatted
    ];
}

// Recent campaign activity - campaigns are stored in medex_prefs.status JSON
// Show when prefs were last updated as a proxy for campaign changes
if ($lastSyncTime) {
    $prefsUpdate = sqlQuery("SELECT MedEx_lastupdated FROM medex_prefs LIMIT 1");
    if ($prefsUpdate && !empty($prefsUpdate['MedEx_lastupdated'])) {
        $updateTime = strtotime($prefsUpdate['MedEx_lastupdated']);
        $now = time();
        $diffHours = floor(($now - $updateTime) / 3600);

        // Only show if updated in last 7 days
        if ($diffHours < 168) {
            $recentActivity[] = [
                'icon' => 'fa-bullhorn',
                'text' => 'Campaign settings updated',
                'time' => date('M j, Y g:i A', $updateTime)
            ];
        }
    }
}

// Recent messages
$todayMessages = sqlQuery("SELECT COUNT(*) as count FROM medex_outgoing WHERE DATE(msg_date) = CURDATE()");
$todayCount = $todayMessages['count'] ?? 0;
if ($todayCount > 0) {
    $recentActivity[] = [
        'icon' => 'fa-paper-plane',
        'text' => $todayCount . ' reminder' . ($todayCount != 1 ? 's' : '') . ' sent today',
        'time' => 'Today'
    ];
}

// Service names from live API (OpenCart product descriptions) — no hardcoding.
// Falls back to slug-formatted key when API is unavailable.
$serviceNames = [];
foreach ($livePricingServices as $svcKey => $svcData) {
    if (!empty($svcData['name'])) {
        $serviceNames[$svcKey] = $svcData['name'];
    }
}

?>

<div class="overview-grid">
    <!-- System Status Card -->
    <div class="overview-card">
        <h3><i class="fa fa-server"></i> <?php echo xlt('System Status'); ?></h3>

        <?php if ($isActive): ?>
            <div class="status-badge success">
                <i class="fa fa-check-circle"></i>
                <?php echo xlt('Connected to MedEx'); ?>
            </div>
        <?php else: ?>
            <div class="status-badge error">
                <i class="fa fa-exclamation-circle"></i>
                <?php echo xlt('Not Connected'); ?>
            </div>
            <?php if ($medexUsername): ?>
                <div style="margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="reconnect.php" class="btn btn-primary btn-sm" style="text-decoration: none; display: inline-block; padding: 8px 16px; background: #667eea; color: white; border-radius: 6px; font-size: 14px; white-space: nowrap;">
                        <i class="fa fa-refresh"></i> <?php echo xlt('Reconnect Account'); ?>
                    </a>
                    <a href="manual_config.php" class="btn btn-secondary btn-sm" style="text-decoration: none; display: inline-block; padding: 8px 16px; background: #6c757d; color: white; border-radius: 6px; font-size: 14px; white-space: nowrap;">
                        <i class="fa fa-wrench"></i> <?php echo xlt('Manual Config'); ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($medexUsername): ?>
            <div style="margin-top: 10px; padding: 10px; background: #f8f9ff; border-radius: 6px; border: 1px solid #667eea;">
                <i class="fa fa-user" style="color: #667eea;"></i>
                <strong><?php echo xlt('Account:'); ?></strong>
                <span style="margin-left: 5px;"><?php echo text($medexUsername); ?></span>
            </div>
        <?php endif; ?>

        <div style="margin-top: 15px;">
            <div class="stat-item">
                <span class="stat-label"><i class="fa fa-clock"></i> <?php echo xlt('Last Sync'); ?></span>
                <span class="stat-value" style="font-size: 14px; color: #666;">
                    <?php
                    if ($minutesAgo !== null) {
                        if ($minutesAgo < 1) {
                            echo xlt('Just now');
                        } elseif ($minutesAgo < 60) {
                            echo $minutesAgo . ' ' . xlt('min ago');
                        } else {
                            $hours = round($minutesAgo / 60);
                            echo $hours . ' ' . xlt('hr') . ($hours != 1 ? 's' : '') . ' ' . xlt('ago');
                        }
                    } else {
                        echo xlt('Never');
                    }
                    ?>
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><i class="fa fa-calendar"></i> <?php echo xlt('Next Sync'); ?></span>
                <span class="stat-value" style="font-size: 14px; color: #666;">
                    <?php
                    if ($minutesAgo !== null && $minutesAgo < 60) {
                        echo (60 - $minutesAgo) . ' ' . xlt('min');
                    } else {
                        echo xlt('On next change');
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Active Subscriptions Card -->
    <div class="overview-card">
        <h3><i class="fa fa-shopping-cart"></i> <?php echo xlt('Active Subscriptions'); ?></h3>

        <?php if (empty($activeSubscriptions)): ?>
            <p style="color: #999; font-size: 14px; margin-bottom: 20px;">
                <?php echo xlt('No active subscriptions'); ?>
            </p>
            <a href="?tab=subscriptions" style="text-decoration: none; color: #667eea; font-weight: 500;">
                <i class="fa fa-plus-circle"></i> <?php echo xlt('Add Services'); ?>
            </a>
        <?php else: ?>
            <div style="margin-bottom: 15px;">
                <?php foreach ($activeSubscriptions as $key => $sub): ?>
                    <div class="stat-item">
                        <span class="stat-label">
                            <i class="fa fa-check" style="color: #28a745; font-size: 12px;"></i>
                            <?php echo xlt($serviceNames[$key] ?? ucwords(str_replace('_', ' ', $key))); ?>
                            <?php if (!empty($sub['provider_count']) && $sub['provider_count'] > 0): ?>
                                <span style="font-size: 12px; color: #999;">(<?php echo $sub['provider_count']; ?> provider<?php echo $sub['provider_count'] != 1 ? 's' : ''; ?>)</span>
                            <?php endif; ?>
                        </span>
                        <span class="stat-value" style="font-size: 14px;">
                            $<?php echo number_format($sub['resolved_price'] ?? 0, 2); ?>/mo
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="padding-top: 15px; border-top: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                <strong><?php echo xlt('Total'); ?>:</strong>
                <strong style="font-size: 18px; color: #667eea;">$<?php echo number_format($totalMonthlyCost, 2); ?>/mo</strong>
            </div>
            <a href="?tab=subscriptions" style="display: inline-block; margin-top: 15px; text-decoration: none; color: #667eea; font-weight: 500;">
                <?php echo xlt('Manage Subscriptions'); ?> <i class="fa fa-arrow-right"></i>
            </a>
        <?php endif; ?>
    </div>

    <!-- Quick Stats Card -->
    <div class="overview-card">
        <h3><i class="fa fa-chart-line"></i> <?php echo xlt('Quick Stats'); ?></h3>

        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label"><?php echo xlt('Messages Sent'); ?> <span style="font-size:10px;color:#999;">(30d)</span></span>
                <span class="stat-value"><?php echo number_format($messagesSentCount); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php echo xlt('Confirmed'); ?></span>
                <span class="stat-value">
                    <?php echo number_format($confirmedCount); ?>
                    <span style="font-size: 12px; color: #28a745; font-weight: normal;">(<?php echo $confirmRate; ?>%)</span>
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php echo xlt('Scheduled'); ?></span>
                <span class="stat-value" style="color: <?php echo $pendingCount > 0 ? '#667eea' : '#999'; ?>;"><?php echo number_format($pendingCount); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php echo xlt('Upcoming Appts'); ?> <span style="font-size:10px;color:#999;">(7d)</span></span>
                <span class="stat-value"><?php echo number_format($eventsCount); ?></span>
            </div>
        </div>

        <!-- Campaign Breakdown -->
        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #e0e0e0;">
            <div style="font-size: 11px; font-weight: 600; color: #333; margin-bottom: 6px;">
                <i class="fa fa-bullhorn" style="color: #667eea;"></i> <?php echo xlt('Campaigns'); ?>
                <?php if ($totalCampaigns > 0): ?>
                    <span style="font-weight: normal; color: #28a745;">(<?php echo $totalCampaigns; ?> <?php echo xlt('active'); ?>)</span>
                <?php endif; ?>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 11px;">
                <span style="background: <?php echo $campaignCounts['reminder'] > 0 ? '#d4edda' : '#f8f9fa'; ?>; color: <?php echo $campaignCounts['reminder'] > 0 ? '#155724' : '#999'; ?>; padding: 2px 8px; border-radius: 10px;">
                    <i class="fa fa-bell"></i> <?php echo $campaignCounts['reminder']; ?> <?php echo xlt('Reminder'); ?>
                </span>
                <span style="background: <?php echo $campaignCounts['recall'] > 0 ? '#cce5ff' : '#f8f9fa'; ?>; color: <?php echo $campaignCounts['recall'] > 0 ? '#004085' : '#999'; ?>; padding: 2px 8px; border-radius: 10px;">
                    <i class="fa fa-redo"></i> <?php echo $campaignCounts['recall']; ?> <?php echo xlt('Recall'); ?>
                </span>
                <span style="background: <?php echo $campaignCounts['gogreen'] > 0 ? '#c3e6cb' : '#f8f9fa'; ?>; color: <?php echo $campaignCounts['gogreen'] > 0 ? '#155724' : '#999'; ?>; padding: 2px 8px; border-radius: 10px;">
                    <i class="fa fa-leaf"></i> <?php echo $campaignCounts['gogreen']; ?> <?php echo xlt('GoGreen'); ?>
                </span>
                <span style="background: <?php echo $campaignCounts['announce'] > 0 ? '#e2d5f1' : '#f8f9fa'; ?>; color: <?php echo $campaignCounts['announce'] > 0 ? '#6f42c1' : '#999'; ?>; padding: 2px 8px; border-radius: 10px;">
                    <i class="fa fa-bullhorn"></i> <?php echo $campaignCounts['announce']; ?> <?php echo xlt('Announce'); ?>
                </span>
                <span style="background: <?php echo $campaignCounts['clinical'] > 0 ? '#fff3cd' : '#f8f9fa'; ?>; color: <?php echo $campaignCounts['clinical'] > 0 ? '#856404' : '#999'; ?>; padding: 2px 8px; border-radius: 10px;">
                    <i class="fa fa-heartbeat"></i> <?php echo $campaignCounts['clinical']; ?> <?php echo xlt('Clinical'); ?>
                </span>
            </div>
            <?php if ($patientsDueClinical > 0): ?>
            <div style="font-size: 10px; color: #856404; margin-top: 4px;">
                <i class="fa fa-exclamation-circle"></i> <?php echo $patientsDueClinical; ?> <?php echo xlt('patients due for clinical reminders'); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Service Status -->
        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e0e0e0;">
            <div style="font-size: 11px; font-weight: 600; color: #333; margin-bottom: 6px;">
                <i class="fa fa-plug" style="color: #667eea;"></i> <?php echo xlt('Services'); ?>
            </div>
            
            <!-- Base Package -->
            <div style="margin-bottom: 6px; font-size: 11px;">
                <?php if ($serviceStatus['base_active']): ?>
                    <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 3px;">
                        <i class="fa fa-check"></i> <?php echo xlt('Base Package'); ?>
                    </span>
                    <span style="color: #666; margin-left: 4px;"><?php echo $serviceStatus['base_providers']; ?>/<?php echo $serviceStatus['total_providers']; ?> <?php echo xlt('providers'); ?></span>
                <?php else: ?>
                    <span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 3px;">
                        <i class="fa fa-times"></i> <?php echo xlt('Base Package'); ?>
                    </span>
                    <span style="color: #999; margin-left: 4px;"><?php echo xlt('Not subscribed'); ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Add-on Services -->
            <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 10px;">
                <?php 
                    $dial0Title = $serviceStatus['dial_0'] ? xla('Active') : ($serviceStatus['base_active'] ? xla('Enable in MedEx Messaging settings') : xla('Requires Base Package'));
                ?>
                <span style="background: <?php echo $serviceStatus['dial_0'] ? '#28a745' : ($serviceStatus['base_active'] ? '#ffc107' : '#6c757d'); ?>; color: <?php echo $serviceStatus['dial_0'] ? 'white' : ($serviceStatus['base_active'] ? '#212529' : 'white'); ?>; padding: 2px 6px; border-radius: 3px;" title="<?php echo $dial0Title; ?>">
                    <i class="fa fa-<?php echo $serviceStatus['dial_0'] ? 'check' : ($serviceStatus['base_active'] ? 'exclamation' : 'times'); ?>"></i> Dial 0
                </span>
                <span style="background: <?php echo $serviceStatus['ai_rescheduler'] ? '#28a745' : '#6c757d'; ?>; color: white; padding: 2px 6px; border-radius: 3px;">
                    <i class="fa fa-<?php echo $serviceStatus['ai_rescheduler'] ? 'check' : 'times'; ?>"></i> AI Rescheduler
                    <?php if ($serviceStatus['ai_rescheduler'] && isset($usageStats['ai_reschedules'])): ?>
                        <span style="background: rgba(255,255,255,0.3); padding: 0 4px; border-radius: 2px; margin-left: 2px;"><?php echo $usageStats['ai_reschedules']; ?></span>
                    <?php endif; ?>
                </span>
                <span style="background: <?php echo $serviceStatus['secure_chat'] ? '#28a745' : '#6c757d'; ?>; color: white; padding: 2px 6px; border-radius: 3px;">
                    <i class="fa fa-<?php echo $serviceStatus['secure_chat'] ? 'check' : 'times'; ?>"></i> Secure Chat
                    <?php if ($serviceStatus['secure_chat'] && isset($usageStats['secure_chats'])): ?>
                        <span style="background: rgba(255,255,255,0.3); padding: 0 4px; border-radius: 2px; margin-left: 2px;"><?php echo $usageStats['secure_chats']; ?></span>
                    <?php endif; ?>
                </span>
                <span style="background: <?php echo $serviceStatus['vfax'] ? '#28a745' : '#6c757d'; ?>; color: white; padding: 2px 6px; border-radius: 3px;">
                    <i class="fa fa-<?php echo $serviceStatus['vfax'] ? 'check' : 'times'; ?>"></i> vFax
                </span>
                <span style="background: <?php echo $serviceStatus['whatsapp'] ? '#28a745' : '#6c757d'; ?>; color: white; padding: 2px 6px; border-radius: 3px;">
                    <i class="fa fa-<?php echo $serviceStatus['whatsapp'] ? 'check' : 'times'; ?>"></i> WhatsApp
                </span>
                <span style="background: <?php echo $serviceStatus['calendar_view'] ? '#28a745' : '#6c757d'; ?>; color: white; padding: 2px 6px; border-radius: 3px;">
                    <i class="fa fa-<?php echo $serviceStatus['calendar_view'] ? 'check' : 'times'; ?>"></i> Calendar
                </span>
            </div>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="overview-card">
        <h3><i class="fa fa-history"></i> <?php echo xlt('Recent Activity'); ?></h3>

        <?php if (empty($recentActivity)): ?>
            <p style="color: #999; font-size: 14px;">
                <?php echo xlt('No recent activity'); ?>
            </p>
        <?php else: ?>
            <ul class="activity-list">
                <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                    <li class="activity-item">
                        <div class="activity-icon">
                            <i class="fa <?php echo $activity['icon']; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-text"><?php echo text($activity['text']); ?></p>
                            <p class="activity-time"><?php echo text($activity['time']); ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="overview-card" style="margin-top: 20px;">
    <h3><i class="fa fa-bolt"></i> <?php echo xlt('Quick Actions'); ?></h3>
    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <a href="?tab=subscriptions" style="padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa fa-plus-circle"></i>
            <?php echo xlt('Add Services'); ?>
        </a>
        <a href="?tab=settings" style="padding: 12px 24px; background: white; color: #667eea; text-decoration: none; border-radius: 6px; font-weight: 500; border: 2px solid #667eea; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa fa-cog"></i>
            <?php echo xlt('Configure Settings'); ?>
        </a>
        <a href="../public/dashboard.php" target="_blank" style="padding: 12px 24px; background: white; color: #667eea; text-decoration: none; border-radius: 6px; font-weight: 500; border: 2px solid #667eea; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa fa-external-link-alt"></i>
            <?php echo xlt('Full Dashboard'); ?>
        </a>
    </div>
</div>
