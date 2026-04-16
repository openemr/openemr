<?php
/**
 * Minimal MedEx overview for the stripped-down OpenEMR module.
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo '<div class="overview-card"><p style="color:#dc3545;">' . xlt('Access denied') . '</p></div>';
    exit;
}

require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

$isConfigured = $api->isConfigured();
$isActive = $api->isActive();
$siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));

try {
    $loginData = $api->login(true);
} catch (\Throwable $e) {
    $loginData = [];
}

$medexUsername = trim((string)($loginData['email'] ?? ''));
if ($medexUsername === '') {
    $prefs = sqlQuery("SELECT ME_username FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
    $medexUsername = trim((string)($prefs['ME_username'] ?? ''));
}
$isDemoCustomer = in_array((int)($loginData['customer_group_id'] ?? 0), [3, 7], true);

$lastSync = sqlQuery("SELECT MedEx_lastupdated FROM medex_prefs LIMIT 1");
$lastSyncTime = trim((string)($lastSync['MedEx_lastupdated'] ?? ''));
$lastSyncFormatted = $lastSyncTime !== '' ? date('M j, Y g:i A', strtotime($lastSyncTime)) : xlt('Never');

$subscriptionsData = $api->getSubscriptions();
$subscriptions = is_array($subscriptionsData['subscriptions'] ?? null) ? $subscriptionsData['subscriptions'] : [];
$activeServiceKeys = array_values((array)($subscriptionsData['active_services'] ?? []));

if (empty($activeServiceKeys)) {
    foreach ($subscriptions as $key => $sub) {
        if (($sub['active'] ?? false) === true && ($sub['status'] ?? '') === 'active') {
            $activeServiceKeys[] = (string)$key;
        }
    }
}

$livePricing = $api->getPricing(true);
$livePricingServices = is_array($livePricing['services'] ?? null) ? $livePricing['services'] : [];
$serviceNames = [];
foreach ($livePricingServices as $svcKey => $svcData) {
    $serviceNames[(string)$svcKey] = trim((string)($svcData['name'] ?? ''));
}

$activeSubscriptions = [];
$totalMonthlyCost = 0.0;
foreach ($activeServiceKeys as $key) {
    if (!array_key_exists($key, $livePricingServices)) {
        continue;
    }

    $sub = is_array($subscriptions[$key] ?? null) ? $subscriptions[$key] : ['service_key' => $key, 'status' => 'active', 'active' => true];
    $isAvailableFromApi = $livePricingServices[$key]['available'] ?? null;
    $isComingSoon = (bool)($livePricingServices[$key]['coming_soon'] ?? false);
    if (!$isDemoCustomer && ($isAvailableFromApi === false || ($isComingSoon && $isAvailableFromApi !== true))) {
        continue;
    }

    $livePrice = $livePricingServices[$key]['price'] ?? null;
    $resolvedPrice = ($livePrice !== null && $livePrice > 0) ? (float)$livePrice : (float)($sub['price'] ?? 0);
    $sub['resolved_price'] = $resolvedPrice;
    $activeSubscriptions[$key] = $sub;
    $totalMonthlyCost += $resolvedPrice;
}

$hasActiveSubscriptions = !empty($activeSubscriptions);
$activeServiceKeyLookup = array_fill_keys(array_keys($activeSubscriptions), true);
$hasMessagingSubscription = isset($activeServiceKeyLookup['appointment_reminders']) || isset($activeServiceKeyLookup['recall_campaigns']);
$campaignCount = 0;
if ($hasMessagingSubscription) {
    $campaigns = $api->getCampaigns();
    if (is_array($campaigns)) {
        foreach ($campaigns as $campaign) {
            if ((is_array($campaign) && !empty($campaign)) || (!is_array($campaign) && $campaign !== null && $campaign !== '' && $campaign !== false)) {
                $campaignCount++;
            }
        }
    }
}
$showCampaignStats = $hasMessagingSubscription && $campaignCount > 0;

$messagesSentCount = 0;
$confirmedCount = 0;
$confirmRate = 0;
$pendingCount = 0;
$eventsCount = 0;
if ($showCampaignStats) {
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    $messagesSent = sqlQuery("SELECT COUNT(*) AS count FROM medex_outgoing WHERE msg_date >= ?", [$thirtyDaysAgo]);
    $messagesSentCount = (int)($messagesSent['count'] ?? 0);

    $confirmedAppts = sqlQuery("SELECT COUNT(*) AS count FROM medex_outgoing WHERE msg_date >= ? AND msg_reply LIKE '%CONFIRM%'", [$thirtyDaysAgo]);
    $confirmedCount = (int)($confirmedAppts['count'] ?? 0);
    $confirmRate = $messagesSentCount > 0 ? round(($confirmedCount / $messagesSentCount) * 100) : 0;

    $pendingMessages = sqlQuery("SELECT COUNT(*) AS cnt FROM medex_outgoing WHERE msg_date > NOW() AND (msg_reply IS NULL OR msg_reply = '')");
    $pendingCount = (int)($pendingMessages['cnt'] ?? 0);

    $upcomingEvents = sqlQuery("SELECT COUNT(*) AS count FROM openemr_postcalendar_events WHERE pc_eventDate >= CURDATE() AND pc_eventDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
    $eventsCount = (int)($upcomingEvents['count'] ?? 0);
}
?>
<style>
.medex-overview-shell {
    display: grid;
    gap: 18px;
}
.medex-overview-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(320px, 1fr));
    gap: 18px;
}
@media (max-width: 980px) {
    .medex-overview-grid {
        grid-template-columns: 1fr;
    }
}
.medex-overview-card {
    background: #fff;
    border: 1px solid #dbe5ee;
    border-radius: 14px;
    padding: 22px;
    box-shadow: 0 10px 24px rgba(15, 75, 143, 0.08);
}
.medex-overview-card h3 {
    margin: 0 0 16px 0;
    font-size: 20px;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.medex-overview-card h3 i {
    color: #0f4b8f;
}
.medex-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 14px;
}
.medex-status-pill.connected {
    background: #dcfce7;
    color: #166534;
}
.medex-status-pill.pending {
    background: #fef3c7;
    color: #92400e;
}
.medex-meta-list {
    display: grid;
    gap: 12px;
}
.medex-meta-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    border-top: 1px solid #e2e8f0;
    padding-top: 12px;
}
.medex-meta-row:first-child {
    border-top: 0;
    padding-top: 0;
}
.medex-meta-label {
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
}
.medex-meta-value {
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
    text-align: right;
}
.medex-service-list {
    display: grid;
    gap: 12px;
}
.medex-service-row {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.medex-service-row:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}
.medex-service-name {
    color: #0f172a;
    font-weight: 700;
}
.medex-service-price {
    color: #0f4b8f;
    font-weight: 800;
    white-space: nowrap;
}
.medex-overview-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 18px;
}
.medex-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    border: 0;
    cursor: pointer;
}
.medex-btn-primary {
    background: #0f4b8f;
    color: #fff;
}
.medex-btn-secondary {
    background: #e2e8f0;
    color: #0f172a;
}
.medex-empty-note {
    color: #475569;
    font-size: 14px;
    line-height: 1.55;
    margin: 0;
}
.medex-activity-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(180px, 1fr));
    gap: 12px;
}
@media (max-width: 640px) {
    .medex-activity-grid {
        grid-template-columns: 1fr;
    }
}
.medex-activity-item {
    border: 1px solid #dbe5ee;
    border-radius: 12px;
    padding: 14px;
    background: #f8fbff;
}
.medex-activity-label {
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.medex-activity-value {
    color: #0f172a;
    font-size: 28px;
    font-weight: 800;
    margin-top: 8px;
}
.medex-activity-subtext {
    color: #16a34a;
    font-size: 12px;
    font-weight: 700;
    margin-top: 4px;
}
</style>
<?php if (!$isConfigured): ?>
<div class="medex-overview-card">
    <h3><i class="fa fa-exclamation-circle"></i> <?php echo xlt('Setup Required'); ?></h3>
    <p class="medex-empty-note"><?php echo xlt('MedEx is not configured yet. Complete onboarding to activate the module.'); ?></p>
    <div class="medex-overview-actions">
        <a href="splash.php?site=<?php echo attr_url($siteId); ?>" class="medex-btn medex-btn-primary"><i class="fa fa-rocket"></i> <?php echo xlt('Start Onboarding'); ?></a>
    </div>
</div>
<?php else: ?>
<div class="medex-overview-shell">
    <div class="medex-overview-grid">
        <section class="medex-overview-card">
            <h3><i class="fa fa-id-badge"></i> <?php echo xlt('Account'); ?></h3>
            <div class="medex-status-pill <?php echo $isActive ? 'connected' : 'pending'; ?>">
                <i class="fa <?php echo $isActive ? 'fa-check-circle' : 'fa-clock-o'; ?>"></i>
                <?php echo $isActive ? xlt('Connected') : xlt('Pending Activation'); ?>
            </div>
            <div class="medex-meta-list">
                <div class="medex-meta-row">
                    <div class="medex-meta-label"><?php echo xlt('Email'); ?></div>
                    <div class="medex-meta-value"><?php echo text($medexUsername !== '' ? $medexUsername : xlt('Not available')); ?></div>
                </div>
                <div class="medex-meta-row">
                    <div class="medex-meta-label"><?php echo xlt('Last Sync'); ?></div>
                    <div class="medex-meta-value"><?php echo text($lastSyncFormatted); ?></div>
                </div>
                <div class="medex-meta-row">
                    <div class="medex-meta-label"><?php echo xlt('Module Status'); ?></div>
                    <div class="medex-meta-value"><?php echo $hasActiveSubscriptions ? xlt('Active Services Ready') : xlt('No Active Services'); ?></div>
                </div>
            </div>
        </section>

        <section class="medex-overview-card">
            <h3><i class="fa fa-shopping-cart"></i> <?php echo xlt('Subscriptions'); ?></h3>
            <?php if (!$hasActiveSubscriptions): ?>
                <p class="medex-empty-note"><?php echo xlt('No recurring MedEx services are active for this practice yet.'); ?></p>
            <?php else: ?>
                <div class="medex-service-list">
                    <?php foreach ($activeSubscriptions as $key => $sub): ?>
                        <div class="medex-service-row">
                            <div class="medex-service-name"><?php echo xlt($serviceNames[$key] !== '' ? $serviceNames[$key] : ucwords(str_replace('_', ' ', (string)$key))); ?></div>
                            <div class="medex-service-price">$<?php echo number_format((float)($sub['resolved_price'] ?? 0), 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                    <div class="medex-service-row">
                        <div class="medex-service-name"><?php echo xlt('Current Total'); ?></div>
                        <div class="medex-service-price">$<?php echo number_format($totalMonthlyCost, 2); ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="medex-overview-actions">
                <a href="#" class="medex-btn medex-btn-primary" onclick="if (window.parent && typeof window.parent.switchToTab === 'function') { return window.parent.switchToTab('subscriptions'); } if (typeof switchToTab === 'function') { return switchToTab('subscriptions'); } window.location.href='index.php?site=<?php echo attr_js($siteId); ?>&tab=subscriptions'; return false;"><i class="fa fa-arrow-right"></i> <?php echo xlt('Open Services'); ?></a>
            </div>
        </section>
    </div>

    <?php if ($hasMessagingSubscription): ?>
        <section class="medex-overview-card">
            <h3><i class="fa fa-comments"></i> <?php echo xlt('Messaging Activity'); ?></h3>
            <?php if (!$showCampaignStats): ?>
                <p class="medex-empty-note"><?php echo xlt('Messaging activity appears after your first campaign is configured.'); ?></p>
            <?php else: ?>
                <div class="medex-activity-grid">
                    <div class="medex-activity-item">
                        <div class="medex-activity-label"><?php echo xlt('Messages Sent'); ?></div>
                        <div class="medex-activity-value"><?php echo number_format($messagesSentCount); ?></div>
                    </div>
                    <div class="medex-activity-item">
                        <div class="medex-activity-label"><?php echo xlt('Confirmed'); ?></div>
                        <div class="medex-activity-value"><?php echo number_format($confirmedCount); ?></div>
                        <div class="medex-activity-subtext"><?php echo text($confirmRate); ?>%</div>
                    </div>
                    <div class="medex-activity-item">
                        <div class="medex-activity-label"><?php echo xlt('Scheduled'); ?></div>
                        <div class="medex-activity-value"><?php echo number_format($pendingCount); ?></div>
                    </div>
                    <div class="medex-activity-item">
                        <div class="medex-activity-label"><?php echo xlt('Upcoming Appointments'); ?></div>
                        <div class="medex-activity-value"><?php echo number_format($eventsCount); ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>
<?php endif; ?>
