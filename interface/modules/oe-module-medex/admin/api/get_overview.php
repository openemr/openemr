<?php
/**
 * Get Overview Dashboard Data
 *
 * Returns system status, subscription summary, and stats
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
    // Force fresh login so overview uses the same subscription truth
    // as the Services tab (avoids stale enabled_services in session cache).
    $loginData = $api->login(true);
} catch (\Exception $e) {
    $loginData = []; // Not configured or invalid credentials - proceed gracefully
}
$medexUsername = $loginData['email'] ?? null;
$creditBalance = (float)($loginData['credit_balance'] ?? 0.00);
$rechargeData = is_array($loginData['recharge'] ?? null) ? $loginData['recharge'] : [];
$rechargeOn = (int)($rechargeData['recharge_on'] ?? 0) === 1;
$rechargePoint = (float)($rechargeData['recharge_point'] ?? 20.00);
$rechargeAmount = (float)($rechargeData['recharge_amt'] ?? 0.00);
$isDemoCustomer = in_array((int)($loginData['customer_group_id'] ?? 0), [3, 7], true);

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
$activeServiceKeys = array_values((array)($subscriptionsData['active_services'] ?? []));
$hasActiveSubscriptions = !empty($activeServiceKeys);

// Pull live prices from DB (same source as Services page) — force=true bypasses cache.
$livePricing = $api->getPricing(true);
$livePricingServices = $livePricing['services'] ?? [];

$activeSubscriptions = [];
$totalMonthlyCost = 0;

// Fallback for older payloads missing active_services.
if (empty($activeServiceKeys)) {
    foreach ($subscriptions as $key => $sub) {
        if (($sub['active'] ?? false) === true && ($sub['status'] ?? '') === 'active') {
            $activeServiceKeys[] = $key;
        }
    }
}
$hasActiveSubscriptions = !empty($activeServiceKeys);

foreach ($activeServiceKeys as $key) {
    // Keep Overview aligned with Services tab: only display/count services
    // that exist in the current pricing/service catalog payload.
    if (!array_key_exists($key, $livePricingServices)) {
        continue;
    }

    $sub = is_array($subscriptions[$key] ?? null) ? $subscriptions[$key] : ['service_key' => $key, 'status' => 'active', 'active' => true];

    // Mirror Services tab filtering so counts/labels stay consistent.
    $isAvailableFromApi = $livePricingServices[$key]['available'] ?? null;
    $isComingSoon = (bool)($livePricingServices[$key]['coming_soon'] ?? false);
    if (!$isDemoCustomer && ($isAvailableFromApi === false || ($isComingSoon && $isAvailableFromApi !== true))) {
        continue;
    }

    // Prefer live price from oc_product_recurring; fall back to subscription's own price field.
    $livePrice = $livePricingServices[$key]['price'] ?? null;
    $resolvedPrice = ($livePrice !== null && $livePrice > 0) ? $livePrice : (float)($sub['price'] ?? 0);
    $sub['resolved_price'] = $resolvedPrice;
    $activeSubscriptions[$key] = $sub;
    $totalMonthlyCost += $resolvedPrice;
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

// Scheduled/pending messages waiting to go out
$pendingMessages = sqlQuery("SELECT COUNT(*) as cnt FROM medex_outgoing WHERE msg_date > NOW() AND (msg_reply IS NULL OR msg_reply = '')");
$pendingCount = (int)($pendingMessages['cnt'] ?? 0);

// Calendar events (from integrated calendar) - next 7 days
$upcomingEvents = sqlQuery("SELECT COUNT(*) as count FROM openemr_postcalendar_events WHERE pc_eventDate >= CURDATE() AND pc_eventDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$eventsCount = $upcomingEvents['count'] ?? 0;

// Service names from live API (OpenCart product descriptions) — no hardcoding.
// Falls back to slug-formatted key when API is unavailable.
$serviceNames = [];
foreach ($livePricingServices as $svcKey => $svcData) {
    if (!empty($svcData['name'])) {
        $serviceNames[$svcKey] = $svcData['name'];
    }
}

?>

<style>
.ov-toggle-switch input[type="checkbox"] {
    position: relative;
    width: 40px;
    height: 22px;
    appearance: none;
    -webkit-appearance: none;
    background: #cbd5e1;
    border: 1px solid #94a3b8;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.ov-toggle-switch input[type="checkbox"]:checked {
    background: #4ade80;
    border-color: #22c55e;
}
.ov-toggle-switch input[type="checkbox"]::before {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    top: 2px;
    left: 2px;
    transition: left 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}
.ov-toggle-switch input[type="checkbox"]:checked::before {
    left: 20px;
}
.ov-account-box {
    margin-top: 10px;
    padding: 12px;
    background: #f8fbff;
    border-radius: 8px;
    border: 1px solid #c7dff3;
}
.ov-actions-row {
    margin-top: 10px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.ov-btn-link {
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
}
.ov-btn-link.primary {
    background: #0f4b8f;
    color: #fff;
}
.ov-btn-link.neutral {
    background: #475569;
    color: #fff;
}
.ov-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}
.ov-recharge-box {
    margin-top: 12px;
    background: #f8fbff;
    border: 1px solid #dbe5ee;
    border-radius: 8px;
    padding: 10px;
}
.ov-field-row {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
}
.ov-field-row label {
    font-size: 12px;
    color: #475569;
    min-width: 80px;
    font-weight: 600;
}
.ov-field-row input {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 7px 8px;
}
.ov-quick-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.ov-system-controls {
    margin-top: 12px;
    background: #f8fbff;
    border: 1px solid #dbe5ee;
    border-radius: 8px;
    padding: 10px;
}
.ov-system-controls-head {
    font-size: 12px;
    font-weight: 700;
    color: #1c4568;
    margin-bottom: 8px;
}
.ov-system-controls-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}
.ov-enable-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
}
.ov-sync-btn {
    border: 1px solid #0f4b8f;
    background: #0f4b8f;
    color: #fff;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}
.ov-sync-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
</style>

<div class="overview-grid">
    <!-- System Information Card -->
    <div class="overview-card">
        <h3><i class="fa fa-server"></i> <?php echo xlt('System Information'); ?></h3>

        <?php if (!$isActive): ?>
            <div class="status-badge error">
                <i class="fa fa-exclamation-circle"></i>
                <?php echo xlt('Not Connected'); ?>
            </div>
            <?php if ($medexUsername): ?>
                <div class="ov-actions-row">
                    <a href="reconnect.php" class="ov-btn-link primary">
                        <i class="fa fa-refresh"></i> <?php echo xlt('Reconnect Account'); ?>
                    </a>
                    <a href="manual_config.php" class="ov-btn-link neutral">
                        <i class="fa fa-wrench"></i> <?php echo xlt('Manual Config'); ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($medexUsername): ?>
            <div class="ov-account-box">
                <i class="fa fa-user" style="color: #0f4b8f;"></i>
                <strong><?php echo xlt('Account:'); ?></strong>
                <span style="margin-left: 5px;"><?php echo text($medexUsername); ?></span>
            </div>
        <?php endif; ?>

        <div class="ov-system-controls">
            <div class="ov-system-controls-head"><?php echo xlt('System Controls'); ?></div>
            <div class="ov-system-controls-row">
                <label class="ov-enable-label">
                    <input type="checkbox" id="medex_enable_overview" value="1" <?php echo ($GLOBALS['medex_enable'] ?? '0') == '1' ? 'checked' : ''; ?>
                        title="<?php echo !$hasActiveSubscriptions ? xla('Activate at least one service before enabling MedEx') : xla('Enable or disable MedEx module'); ?>"
                        onchange="if (window.parent && typeof window.parent.toggleMedExEnable === 'function') { window.parent.toggleMedExEnable(this); } else if (typeof toggleMedExEnable === 'function') { toggleMedExEnable(this); }">
                    <span><?php echo xlt('MedEx Enabled'); ?></span>
                </label>
                <button class="ov-sync-btn" id="sync-button-overview"
                    onclick="if (window.parent && typeof window.parent.triggerSync === 'function') { window.parent.triggerSync(this); } else if (typeof triggerSync === 'function') { triggerSync(this); }"
                    <?php echo (!$isActive || !$hasActiveSubscriptions) ? 'disabled' : ''; ?>
                    title="<?php echo !$hasActiveSubscriptions ? xla('No active subscriptions to sync') : xla('Sync data with MedEx server'); ?>">
                    <i class="fa fa-sync-alt"></i>
                    <?php echo xlt('Sync Now'); ?>
                </button>
            </div>
        </div>

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

    <!-- Billing Snapshot Card -->
    <div class="overview-card">
        <h3><i class="fa fa-receipt"></i> <?php echo xlt('Billing Snapshot'); ?></h3>

        <?php if (empty($activeSubscriptions)): ?>
            <p style="color: #999; font-size: 14px; margin-bottom: 20px;">
                <?php echo xlt('No recurring subscriptions'); ?>
            </p>
            <a href="?tab=subscriptions" style="text-decoration: none; color: #0f4b8f; font-weight: 500;">
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
            <div style="padding-top: 15px; border-top: 2px solid #dbe5ee; display: flex; justify-content: space-between; align-items: center;">
                <strong><?php echo xlt('Total'); ?>:</strong>
                <strong style="font-size: 18px; color: #0f4b8f;">$<?php echo number_format($totalMonthlyCost, 2); ?>/mo</strong>
            </div>
            <a href="?tab=subscriptions" style="display: inline-block; margin-top: 15px; text-decoration: none; color: #0f4b8f; font-weight: 500;">
                <?php echo xlt('Manage Billing'); ?> <i class="fa fa-arrow-right"></i>
            </a>
        <?php endif; ?>

        <div style="margin-top:15px; padding-top:15px; border-top:2px solid #dbe5ee;">
            <div class="ov-header-row" style="margin-bottom:8px;">
                <strong style="color:#1c4568;"><i class="fa fa-coins"></i> <?php echo xlt('A La Carte Credits'); ?></strong>
                <div style="display:flex; align-items:center; gap:8px;">
                    <?php if (!$isDemoCustomer): ?>
                        <label class="ov-toggle-switch" title="<?php echo xla('Toggle auto-refill on or off'); ?>" style="margin:0;">
                            <input type="checkbox" id="ov-recharge-on-toggle" <?php echo $rechargeOn ? 'checked' : ''; ?> onchange="ovToggleRechargeFields()">
                        </label>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-item">
                <span class="stat-label"><?php echo xlt('Current Balance (as of last sync)'); ?></span>
                <span class="stat-value" id="ov-credit-balance">$<?php echo number_format($creditBalance, 2); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php echo xlt('Auto-Refill Threshold'); ?></span>
                <span class="stat-value">$<?php echo number_format($rechargePoint, 2); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php echo xlt('Auto-Refill Amount'); ?></span>
                <span class="stat-value">$<?php echo number_format($rechargeAmount, 2); ?></span>
            </div>

            <?php if ($isDemoCustomer): ?>
                <div style="margin-top: 12px; font-size: 12px; color: #0c5460; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; padding: 8px;">
                    <i class="fa fa-info-circle"></i> <?php echo xlt('Demo account: billing and auto-refill are bypassed.'); ?>
                </div>
            <?php else: ?>
                <div class="ov-recharge-box">
                    <div id="ov-recharge-fields" style="display: <?php echo $rechargeOn ? 'block' : 'none'; ?>;">
                        <div class="ov-field-row">
                            <label for="ov-recharge-point"><?php echo xlt('Threshold'); ?></label>
                            <input type="number" id="ov-recharge-point" min="1" max="9999" step="0.01" value="<?php echo attr($rechargePoint); ?>">
                        </div>
                        <div class="ov-field-row" style="margin-bottom:10px;">
                            <label for="ov-recharge-amt"><?php echo xlt('Amount'); ?></label>
                            <input type="number" id="ov-recharge-amt" min="10" max="9999" step="1" value="<?php echo attr($rechargeAmount ?: 50); ?>">
                        </div>
                    </div>

                    <button type="button" onclick="ovSaveRechargeSettings()" style="width:100%; padding:8px 10px; background:#0f4b8f; color:#fff; border:none; border-radius:4px; font-weight:600;">
                        <i class="fa fa-save"></i> <?php echo xlt('Save Auto-Refill'); ?>
                    </button>
                    <div id="ov-recharge-status-msg" style="display:none; margin-top:8px; font-size:12px;"></div>
                </div>
            <?php endif; ?>
        </div>
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
                <span class="stat-value" style="color: <?php echo $pendingCount > 0 ? '#0f4b8f' : '#999'; ?>;"><?php echo number_format($pendingCount); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php echo xlt('Upcoming Appts'); ?> <span style="font-size:10px;color:#999;">(7d)</span></span>
                <span class="stat-value"><?php echo number_format($eventsCount); ?></span>
            </div>
        </div>

    </div>

</div>

<script>
function ovToggleRechargeFields() {
    const on = document.getElementById('ov-recharge-on-toggle').checked;
    const fields = document.getElementById('ov-recharge-fields');
    if (fields) {
        fields.style.display = on ? 'block' : 'none';
    }
}

function ovSaveRechargeSettings() {
    const msg = document.getElementById('ov-recharge-status-msg');
    const on = document.getElementById('ov-recharge-on-toggle') ? (document.getElementById('ov-recharge-on-toggle').checked ? 1 : 0) : 0;
    const point = document.getElementById('ov-recharge-point') ? parseFloat(document.getElementById('ov-recharge-point').value) || 0 : 0;
    const amt = document.getElementById('ov-recharge-amt') ? parseInt(document.getElementById('ov-recharge-amt').value) || 0 : 0;

    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
        top.restoreSession();
    }

    fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/admin/update_recharge.php?site=<?php echo urlencode($_SESSION['site_id'] ?? 'default'); ?>', {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'recharge_on=' + encodeURIComponent(on) +
              '&recharge_point=' + encodeURIComponent(point) +
              '&recharge_amt=' + encodeURIComponent(amt)
    })
    .then(r => r.json())
    .then(data => {
        if (!msg) {
            return;
        }
        msg.style.display = 'block';
        if (data.success) {
            msg.style.color = '#28a745';
            msg.innerHTML = '<i class="fa fa-check-circle"></i> <?php echo xlt('Saved.'); ?>';
            if (data.credit_balance !== undefined) {
                const balEl = document.getElementById('ov-credit-balance');
                if (balEl) {
                    balEl.textContent = '$' + parseFloat(data.credit_balance).toFixed(2);
                }
            }
            setTimeout(() => { msg.style.display = 'none'; }, 2500);
        } else {
            msg.style.color = '#dc3545';
            msg.innerHTML = '<i class="fa fa-exclamation-circle"></i> ' + (data.error || '<?php echo xlt('Save failed.'); ?>');
        }
    })
    .catch(() => {
        if (!msg) {
            return;
        }
        msg.style.display = 'block';
        msg.style.color = '#dc3545';
        msg.innerHTML = '<i class="fa fa-exclamation-circle"></i> <?php echo xlt('Network error.'); ?>';
    });
}
</script>

<!-- Quick Actions -->
<div class="overview-card" style="margin-top: 20px;">
    <h3><i class="fa fa-bolt"></i> <?php echo xlt('Quick Actions'); ?></h3>
    <div class="ov-quick-actions">
        <a href="?tab=subscriptions" style="padding: 12px 24px; background: #0f4b8f; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa fa-plus-circle"></i>
            <?php echo xlt('Add Services'); ?>
        </a>
        <a href="?tab=settings" style="padding: 12px 24px; background: white; color: #0f4b8f; text-decoration: none; border-radius: 6px; font-weight: 500; border: 2px solid #0f4b8f; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa fa-cog"></i>
            <?php echo xlt('Configure Settings'); ?>
        </a>
        <a href="../public/dashboard.php" target="_blank" style="padding: 12px 24px; background: white; color: #0f4b8f; text-decoration: none; border-radius: 6px; font-weight: 500; border: 2px solid #0f4b8f; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa fa-external-link-alt"></i>
            <?php echo xlt('Full Dashboard'); ?>
        </a>
    </div>
</div>
