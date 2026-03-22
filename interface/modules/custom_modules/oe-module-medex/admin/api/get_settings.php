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
    MAX(CASE WHEN gl_name = 'medex_practice_id' THEN gl_value END) as medex_practice_id
    FROM globals WHERE gl_name IN ('medex_api_key', 'medex_practice_id')", []);

$medex_api_key = $globalConfig['medex_api_key'] ?? '';
$medex_practice_id = $globalConfig['medex_practice_id'] ?? '';
?>

<style>
.settings-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 15px; }
@media (max-width: 1200px) { .settings-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 700px) { .settings-grid { grid-template-columns: 1fr; } }
.settings-card { background: #f8f9ff; border: 2px solid #667eea; border-radius: 8px; padding: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
.settings-card h4 { margin: 0 0 10px 0; font-size: 13px; font-weight: 600; color: #333; display: flex; align-items: center; gap: 6px; }
.settings-card h4 i { color: #667eea; font-size: 12px; }
.form-check { margin-bottom: 8px; }
.form-check-label { font-size: 12px; margin-left: 4px; cursor: pointer; }
.form-group { margin-bottom: 10px; }
.form-group label { display: block; font-size: 11px; color: #666; margin-bottom: 3px; }
.form-control-sm { font-size: 12px; padding: 4px 8px; }
.help-text { font-size: 10px; color: #888; margin-top: 2px; }
.sync-slider { width: 100%; }
.sync-display { font-size: 11px; margin-top: 4px; }
.sync-display strong { color: #0f4b8f; }
.btn-advanced { width: 100%; background: #f8f9fa; border: 1px solid #ddd; color: #666; font-size: 11px; padding: 6px 10px; border-radius: 4px; cursor: pointer; margin-top: 8px; }
.btn-advanced:hover { background: #e9ecef; }
.btn-save-settings { background: #667eea; color: white; border: none; padding: 10px 24px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-save-settings:hover { background: #5568d3; }
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
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>" />

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
    fetch('../admin/disconnect.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'csrf_token_form=' + encodeURIComponent('<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>')
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { if (window.showToast) window.showToast('Disconnected', 'success'); setTimeout(() => { window.location.href = data.redirect || 'reconnect.php'; }, 1500); }
        else if (window.showToast) window.showToast('Error disconnecting', 'error');
    });
}
</script>
