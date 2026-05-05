<?php
/**
 * Get Settings Tab Content - Compact 4-card layout
 *
 * @package   OpenEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!AclMain::aclCheckCore('admin', 'super')) {
    http_response_code(403);
    echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' . xlt('Access denied') . '</div>';
    exit;
}

require_once(__DIR__ . '/../../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

if (!$api->isConfigured()) {
    echo '<div class="panel"><h3>' . xlt('Setup Required') . '</h3>';
    echo '<p>' . xlt('MedEx is not configured. Please complete registration first.') . '</p>';
    echo '<a href="splash.php" class="btn btn-primary">' . xlt('Get Started') . '</a></div>';
    exit;
}

$prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT * FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1", []);
if (!$prefs) $prefs = [];

$globalConfig = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT
    MAX(CASE WHEN gl_name = 'medex_api_key' THEN gl_value END) as medex_api_key,
    MAX(CASE WHEN gl_name = 'medex_practice_id' THEN gl_value END) as medex_practice_id,
    MAX(CASE WHEN gl_name = 'medex_bill_notify_receipts' THEN gl_value END) as medex_bill_notify_receipts,
    MAX(CASE WHEN gl_name = 'medex_bill_notify_failures' THEN gl_value END) as medex_bill_notify_failures,
    MAX(CASE WHEN gl_name = 'medex_bill_notify_cancellations' THEN gl_value END) as medex_bill_notify_cancellations,
    MAX(CASE WHEN gl_name = 'medex_bill_notify_email' THEN gl_value END) as medex_bill_notify_email
    FROM globals
    WHERE gl_name IN (
        'medex_api_key',
        'medex_practice_id',
        'medex_bill_notify_receipts',
        'medex_bill_notify_failures',
        'medex_bill_notify_cancellations',
        'medex_bill_notify_email'
    )", []);

$medex_api_key = $globalConfig['medex_api_key'] ?? '';
$medex_practice_id = $globalConfig['medex_practice_id'] ?? '';
$billingNotifyReceipts = (($globalConfig['medex_bill_notify_receipts'] ?? '1') !== '0');
$billingNotifyFailures = (($globalConfig['medex_bill_notify_failures'] ?? '1') !== '0');
$billingNotifyCancellations = (($globalConfig['medex_bill_notify_cancellations'] ?? '1') !== '0');
$billingNotifyEmail = trim((string)($globalConfig['medex_bill_notify_email'] ?? ($prefs['ME_username'] ?? '')));
$moduleWebRoot = rtrim((string)($GLOBALS['webroot'] ?? ''), '/')
    . '/interface/modules/custom_modules/oe-module-medex';
$disconnectUrl = $moduleWebRoot . '/admin/disconnect.php';
?>

<style>
.settings-grid { display: grid; grid-template-columns: repeat(2, minmax(320px, 1fr)); gap: 16px; margin-bottom: 18px; }
@media (max-width: 1080px) { .settings-grid { grid-template-columns: 1fr; } }
.settings-card { background: #ffffff; border: 1px solid #dbe5ee; border-radius: 12px; padding: 18px; box-shadow: 0 10px 22px rgba(15,75,143,0.08); }
.settings-card h4 { margin: 0 0 14px 0; font-size: 15px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
.settings-card h4 i { color: #0f4b8f; font-size: 13px; }
.form-check { margin-bottom: 10px; }
.form-check-label { font-size: 13px; margin-left: 4px; cursor: pointer; color: #334155; }
.form-group { margin-bottom: 12px; }
.form-group label { display: block; font-size: 12px; color: #64748b; margin-bottom: 4px; font-weight: 600; }
.form-control-sm { font-size: 13px; padding: 8px 10px; border-radius: 8px; border: 1px solid #cbd5e1; }
.help-text { font-size: 11px; color: #64748b; margin-top: 4px; }
.sync-slider { width: 100%; }
.sync-display { font-size: 12px; margin-top: 6px; color: #334155; }
.sync-display strong { color: #0f4b8f; }
.btn-advanced { width: 100%; background: #f8fafc; border: 1px solid #cbd5e1; color: #475569; font-size: 12px; padding: 8px 12px; border-radius: 8px; cursor: pointer; margin-top: 10px; font-weight: 600; }
.btn-advanced:hover { background: #f1f5f9; }
.btn-save-settings { background: #0f4b8f; color: white; border: none; padding: 12px 28px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
.btn-save-settings:hover { background: #0a3460; }
.btn-save-settings:disabled { background: #ccc; cursor: not-allowed; }
.settings-modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 10001; }
.settings-modal-overlay.show { display: flex; }
.settings-modal { background: white; border-radius: 8px; max-width: 400px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
.settings-modal-header { background: #dc3545; color: white; padding: 12px 16px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center; }
.settings-modal-header h5 { margin: 0; font-size: 14px; }
.settings-modal-close { background: none; border: none; color: white; font-size: 18px; cursor: pointer; opacity: 0.8; }
.settings-modal-body { padding: 16px; }
.danger-zone { background: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 4px; margin-top: 12px; }
.danger-zone p { margin: 8px 0 0 0; font-size: 12px; color: #856404; }
</style>

<form id="settings-form">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

    <div class="settings-grid">
        <!-- HIPAA Defaults -->
        <div class="settings-card">
            <h4><i class="fa fa-shield-alt"></i> <?php echo xlt('HIPAA Defaults'); ?></h4>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="ME_hipaa_default_override" id="ME_hipaa_default_override" value="1" <?php echo ($prefs['ME_hipaa_default_override'] ?? '1') == '1' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ME_hipaa_default_override"><?php echo xlt('Assume HIPAA received'); ?></label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="MSGS_default_yes" id="MSGS_default_yes" value="1" <?php echo ($prefs['MSGS_default_yes'] ?? '0') == '1' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="MSGS_default_yes"><?php echo xlt('Assume new patients opted-in'); ?></label>
            </div>
        </div>

        <!-- SMS Settings -->
        <div class="settings-card">
            <h4><i class="fa fa-mobile-alt"></i> <?php echo xlt('SMS Settings'); ?></h4>
            <div class="form-group">
                <label for="PHONE_country_code"><?php echo xlt('Country Code'); ?></label>
                <input type="number" class="form-control form-control-sm" name="PHONE_country_code" id="PHONE_country_code" value="<?php echo attr($prefs['PHONE_country_code'] ?? '1'); ?>" min="1" max="999" style="width: 80px;">
                <div class="help-text"><?php echo xlt('1 = US/Canada'); ?></div>
            </div>
            <div class="form-group">
                <label for="sms_bot_phone_style"><?php echo xlt('SMS Bot Display'); ?></label>
                <select class="form-control form-control-sm" name="sms_bot_phone_style" id="sms_bot_phone_style">
                    <option value="S8" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'S8' ? 'selected' : ''; ?>>Samsung S8</option>
                    <option value="iPhone14" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'iPhone14' ? 'selected' : ''; ?>>iPhone 14</option>
                    <option value="iPhone4" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'iPhone4' ? 'selected' : ''; ?>>iPhone 4s</option>
                    <option value="Pixel8" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'Pixel8' ? 'selected' : ''; ?>>Pixel 8</option>
                    <option value="minimal" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'minimal' ? 'selected' : ''; ?>>Minimal</option>
                </select>
            </div>
        </div>

        <!-- Sync Settings -->
        <div class="settings-card">
            <h4><i class="fa fa-sync"></i> <?php echo xlt('Sync Settings'); ?></h4>
            <div class="form-group">
                <label for="execute_interval"><?php echo xlt('Sync Frequency'); ?></label>
                <input type="range" class="sync-slider" name="execute_interval" id="execute_interval" min="0" max="360" step="1" value="<?php echo attr($prefs['execute_interval'] ?? '29'); ?>">
                <div class="sync-display" id="sync_display">
                    <?php if (($prefs['execute_interval'] ?? '29') == '0'): ?>
                        <span style="color: #dc3545;"><?php echo xlt('Sync paused'); ?></span>
                    <?php else: ?>
                        <?php echo xlt('Every'); ?> <strong><?php echo text($prefs['execute_interval'] ?? '29'); ?></strong> <?php echo xlt('min'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Advanced Settings -->
        <div class="settings-card">
            <h4><i class="fa fa-cog"></i> <?php echo xlt('Advanced'); ?></h4>
            <div style="font-size: 11px; color: #666;">
                <div><strong><?php echo xlt('Practice ID'); ?>:</strong> <?php echo text($medex_practice_id); ?></div>
                <div style="margin-top: 4px;"><strong><?php echo xlt('API Key'); ?>:</strong> <code style="font-size: 9px;"><?php echo text(substr($medex_api_key, 0, 12) . '...'); ?></code></div>
            </div>
            <button type="button" class="btn-advanced" onclick="showAdvancedModal()">
                <i class="fa fa-exclamation-triangle"></i> <?php echo xlt('Disconnect Account'); ?>
            </button>
        </div>

        <!-- Billing Notifications -->
        <div class="settings-card">
            <h4><i class="fa fa-receipt"></i> <?php echo xlt('Billing Notifications'); ?></h4>
            <input type="hidden" name="ME_bill_notify_receipts_present" value="1">
            <input type="hidden" name="ME_bill_notify_failures_present" value="1">
            <input type="hidden" name="ME_bill_notify_cancellations_present" value="1">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="ME_bill_notify_receipts" id="ME_bill_notify_receipts" value="1" <?php echo $billingNotifyReceipts ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ME_bill_notify_receipts"><?php echo xlt('Send payment receipts'); ?></label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="ME_bill_notify_failures" id="ME_bill_notify_failures" value="1" <?php echo $billingNotifyFailures ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ME_bill_notify_failures"><?php echo xlt('Send payment failure alerts'); ?></label>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="ME_bill_notify_cancellations" id="ME_bill_notify_cancellations" value="1" <?php echo $billingNotifyCancellations ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ME_bill_notify_cancellations"><?php echo xlt('Send cancellation/proration receipts'); ?></label>
            </div>
            <div class="form-group" style="margin-top: 8px;">
                <label for="ME_bill_notify_email"><?php echo xlt('Billing notification email'); ?></label>
                <input type="email" class="form-control form-control-sm" name="ME_bill_notify_email" id="ME_bill_notify_email" value="<?php echo attr($billingNotifyEmail); ?>" placeholder="support@medexbank.com">
                <div class="help-text"><?php echo xlt('Practice-wide destination for MedEx billing notices.'); ?></div>
            </div>
        </div>
    </div>

    <div style="text-align: center;">
        <button type="submit" class="btn-save-settings" id="save-settings-btn">
            <i class="fa fa-save"></i> <?php echo xlt('Save Settings'); ?>
        </button>
    </div>
</form>

<!-- Advanced Modal -->
<div class="settings-modal-overlay" id="advanced-modal">
    <div class="settings-modal">
        <div class="settings-modal-header">
            <h5><i class="fa fa-exclamation-triangle"></i> <?php echo xlt('Disconnect MedEx'); ?></h5>
            <button class="settings-modal-close" onclick="hideAdvancedModal()">&times;</button>
        </div>
        <div class="settings-modal-body">
            <p style="font-size: 13px; margin: 0;"><?php echo xlt('Are you sure you want to disconnect from MedEx?'); ?></p>
            <div class="danger-zone">
                <strong style="color: #856404;"><i class="fa fa-exclamation-triangle"></i> <?php echo xlt('Warning'); ?></strong>
                <p><?php echo xlt('This will permanently remove all MedEx credentials. You will need to re-register to use MedEx services again.'); ?></p>
                <button type="button" class="btn btn-danger btn-sm" style="margin-top: 8px;" onclick="disconnectMedEx()">
                    <i class="fa fa-trash"></i> <?php echo xlt('Disconnect'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showAdvancedModal() { document.getElementById('advanced-modal').classList.add('show'); }
function hideAdvancedModal() { document.getElementById('advanced-modal').classList.remove('show'); }
document.getElementById('advanced-modal').addEventListener('click', function(e) { if (e.target === this) hideAdvancedModal(); });

document.getElementById('execute_interval').addEventListener('input', function() {
    var val = this.value;
    var display = document.getElementById('sync_display');
    if (val == '0') {
        display.innerHTML = '<span style="color: #dc3545;"><?php echo xla("Sync paused"); ?></span>';
    } else {
        display.innerHTML = '<?php echo xla("Every"); ?> <strong>' + val + '</strong> <?php echo xla("min"); ?>';
    }
});

document.getElementById('settings-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('save-settings-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    fetch('../admin/save_preferences.php', { method: 'POST', body: new FormData(this) })
    .then(r => r.json())
    .then(data => {
        if (data.success && window.showToast) window.showToast('Settings saved', 'success');
        else if (window.showToast) window.showToast('Error: ' + (data.error || ''), 'error');
    })
    .catch(() => { if (window.showToast) window.showToast('Error saving settings', 'error'); })
    .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fa fa-save"></i> Save Settings'; });
});

function disconnectMedEx() {
    if (!confirm('This will remove all MedEx settings. Are you absolutely sure?')) return;
    if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
    fetch('<?php echo attr_js($disconnectUrl); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token_form=' + encodeURIComponent('<?php echo attr(CsrfUtils::collectCsrfToken()); ?>')
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { if (window.showToast) window.showToast('Disconnected', 'success'); setTimeout(() => { window.location.href = data.redirect || 'reconnect.php'; }, 1500); }
        else if (window.showToast) window.showToast('Error disconnecting', 'error');
    });
}
</script>
