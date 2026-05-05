<?php

/**
 * MedEx Module - Settings
 *
 * Unified settings page combining:
 * - Connection settings (API key, Practice ID, Server URL)
 * - Facility selection
 * - Provider selection
 * - HIPAA defaults
 * - Label and postcard settings
 * - SMS/Voice settings
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo "<html><body>" . xlt('Access denied') . "</body></html>";
    exit;
}

// DEBUG: Check medex_prefs directly
$debug_prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT ME_api_key, MedEx_id, ME_username FROM medex_prefs WHERE ME_username IS NOT NULL AND ME_api_key IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1", []);
$debug_info = [
    'prefs_query' => $debug_prefs,
    'api_key_length' => strlen($debug_prefs['ME_api_key'] ?? ''),
    'practice_id' => $debug_prefs['MedEx_id'] ?? 'NULL',
    'globals_api_key' => isset($GLOBALS['medex_api_key']) && !empty($GLOBALS['medex_api_key']) ? 'SET' : 'EMPTY',
    'globals_practice_id' => $GLOBALS['medex_practice_id'] ?? 'NULL'
];

// Load MedEx API
require_once(__DIR__ . '/../src/MedExAPI.php');
$api = new \OpenEMR\Modules\MedEx\MedExAPI();

// Check if user is registered - if not, redirect to registration splash page
if (!$api->isConfigured()) {
    header('Location: splash.php');
    exit;
}

// Settings page should always be accessible to admins regardless of subscription status

// Get current preferences
$prefs = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT * FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1", []);
if (!$prefs) {
    $prefs = []; // Initialize empty array if no prefs exist
}

// Get current global config values
$globalConfig = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT
    MAX(CASE WHEN gl_name = 'medex_enable' THEN gl_value END) as medex_enable,
    MAX(CASE WHEN gl_name = 'medex_api_key' THEN gl_value END) as medex_api_key,
    MAX(CASE WHEN gl_name = 'medex_practice_id' THEN gl_value END) as medex_practice_id
    FROM globals
    WHERE gl_name IN ('medex_enable', 'medex_api_key', 'medex_practice_id')", []);

$medex_enable = $globalConfig['medex_enable'] ?? '0';
$medex_api_key = $globalConfig['medex_api_key'] ?? '';
$medex_practice_id = $globalConfig['medex_practice_id'] ?? '';

// Background services removed; default to 29-minute sync frequency when needed by documentation/UI
$prefs['execute_interval'] = $prefs['execute_interval'] ?? '29';

// Get connection status - only test if module is enabled
$connectionStatus = null;
if ($medex_enable === '1') {
    $connectionStatus = $api->testConnection();
}
$debug_info['is_configured'] = $api->isConfigured();
$debug_info['last_error'] = $api->getLastError();

// Check for updates
require_once(__DIR__ . '/../src/UpdateManager.php');
$updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();
$updateInfo = $updateManager->checkForUpdates();

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Settings'); ?></title>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <style>
        body { font-size: 13px; }
        .medex-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            margin: -8px -8px 15px -8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .medex-header h2 { margin: 0; font-size: 20px; }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
        }
        .status-online { background: #28a745; color: white; }
        .status-offline { background: #dc3545; color: white; }
        .section-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title i { color: #667eea; }
        .practice-name {
            color: #000;
            font-weight: 700;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .toggle-switch {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: auto;
            font-size: 11px;
            color: #555;
        }
        .toggle-switch input { display: none; }
        .toggle-slider {
            width: 36px;
            height: 18px;
            background: #c9c9c9;
            border-radius: 999px;
            position: relative;
            transition: background 0.2s ease;
        }
        .toggle-slider::after {
            content: "";
            width: 14px;
            height: 14px;
            background: #fff;
            border-radius: 50%;
            position: absolute;
            top: 2px;
            left: 2px;
            transition: transform 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        .toggle-switch input:checked + .toggle-slider {
            background: #28a745;
        }
        .toggle-switch input:checked + .toggle-slider::after {
            transform: translateX(18px);
        }
        .toggle-label {
            font-weight: 600;
        }
        .form-group { margin-bottom: 10px; }
        .inline-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .inline-row label {
            margin: 0;
            min-width: 110px;
        }
        .inline-row .form-control,
        .inline-row input[type="range"] {
            flex: 1 1 auto;
        }
        .inline-help {
            margin-left: 120px;
        }
        .form-check { margin-bottom: 6px; }
        .form-check-label { font-size: 13px; margin-left: 4px; }
        .checkbox-group {
            height: 120px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            padding: 8px;
            background: #fafafa;
            border-radius: 4px;
        }
        .list-controls {
            display: flex;
            gap: 5px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }
        .list-controls .btn-xs {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .help-text {
            font-size: 11px;
            color: #888;
            margin-top: 2px;
            line-height: 1.3;
        }
        .btn { font-size: 13px; padding: 6px 12px; }
        .compact-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        @media (max-width: 1200px) {
            .compact-row { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .compact-row { grid-template-columns: 1fr; }
        }
        .action-bar {
            position: sticky;
            top: 0;
            background: white;
            padding: 10px 0;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 15px;
            z-index: 10;
        }
    </style>
</head>
<body class="body_top">
    <div class="container-fluid" style="max-width: 100%; padding: 20px 30px;">
        <?php if ($medex_enable != '1'): ?>
            <div class="alert alert-warning" style="margin-bottom: 15px;">
                <strong><i class="fa fa-exclamation-triangle"></i> <?php echo xlt('MedEx is Disabled'); ?></strong><br>
                <?php echo xlt('MedEx services are currently disabled globally. Enable MedEx in the Global Configuration section below to activate services.'); ?>
            </div>
        <?php endif; ?>

        <?php if ($updateInfo && $updateInfo['update_available']): ?>
            <?php
            $alertClass = 'alert-info';
            $icon = 'fa-info-circle';
            if ($updateInfo['priority'] === 'CRITICAL') {
                $alertClass = 'alert-danger';
                $icon = 'fa-exclamation-circle';
            } elseif ($updateInfo['priority'] === 'SECURITY') {
                $alertClass = 'alert-warning';
                $icon = 'fa-shield-alt';
            } elseif ($updateInfo['priority'] === 'IMPORTANT') {
                $alertClass = 'alert-warning';
                $icon = 'fa-exclamation-triangle';
            }
            ?>
            <div class="alert <?php echo attr($alertClass); ?>" style="margin-bottom: 15px;">
                <strong><i class="fa <?php echo attr($icon); ?>"></i> <?php echo xlt('Update Available'); ?>: v<?php echo text($updateInfo['latest_version']); ?></strong>
                <span style="float: right; font-size: 11px;"><?php echo xlt('Current'); ?>: v<?php echo text($updateInfo['current_version']); ?></span>
                <br>
                <?php if (!empty($updateInfo['critical_message'])): ?>
                    <div style="margin-top: 8px; padding: 8px; background: rgba(255,255,255,0.3); border-radius: 4px;">
                        <?php echo text($updateInfo['critical_message']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($updateInfo['changelog'])): ?>
                    <div style="margin-top: 8px; font-size: 12px;">
                        <?php echo nl2br(text($updateInfo['changelog'])); ?>
                    </div>
                <?php endif; ?>
                <div style="margin-top: 10px;">
                    <a href="backups.php" class="btn btn-sm btn-primary">
                        <i class="fa fa-download"></i> <?php echo xlt('View Updates & Backups'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" id="prefs-form" action="save_preferences.php">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

            <div class="compact-row">
                <!-- Column 1: Connection & Configuration -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fa fa-plug"></i>
                        <span class="practice-name"><?php echo text($connectionStatus['practice_name'] ?? xlt('Practice')); ?></span>
                        <label class="toggle-switch" title="<?php echo xla('Enable MedEx'); ?>">
                            <input type="checkbox" id="medex_enable_toggle" <?php echo ($medex_enable == '1') ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                            <span class="toggle-label" id="medex_enable_label">
                                <?php echo ($medex_enable == '1') ? xlt('Enabled') : xlt('Disabled'); ?>
                            </span>
                        </label>
                    </div>

                    <input type="hidden" name="medex_enable" id="medex_enable" value="<?php echo attr($medex_enable); ?>">

                    <?php if ($medex_enable != '1'): ?>
                        <div style="font-size: 11px; color: #856404; background: #fff3cd; padding: 6px; border-radius: 3px; margin-bottom: 8px;">
                            <?php echo xlt('Enable MedEx to connect'); ?>
                        </div>
                    <?php elseif ($connectionStatus['success']): ?>
                    <?php else: ?>
                        <div style="font-size: 11px; color: #721c24; background: #f8d7da; padding: 6px; border-radius: 3px; margin-bottom: 8px;">
                            <strong><?php echo xlt('Offline'); ?>:</strong> <?php echo text($connectionStatus['error'] ?? 'Unknown error'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($medex_api_key) || empty($medex_practice_id)): ?>
                        <a href="register.php" class="btn btn-primary btn-sm btn-block" style="font-size: 11px; padding: 6px 12px;">
                            <i class="fa fa-user-plus"></i> <?php echo xlt('Register'); ?>
                        </a>
                    <?php else: ?>
                        <button type="button" id="advanced-settings-btn" class="btn btn-link btn-sm btn-block" style="font-size: 12px; color: #6c757d; padding: 6px 12px;">
                            <i class="fa fa-cog"></i> <?php echo xlt('Advanced Settings'); ?>
                        </button>
                    <?php endif; ?>

                    <hr style="margin: 10px 0;">

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="ME_hipaa_default_override" id="ME_hipaa_default_override" value="1" <?php echo ($prefs['ME_hipaa_default_override'] ?? '1') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="ME_hipaa_default_override">
                            <?php echo xlt('Assume HIPAA received'); ?>
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="MSGS_default_yes" id="MSGS_default_yes" value="1" <?php echo ($prefs['MSGS_default_yes'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="MSGS_default_yes">
                            <?php echo xlt('Assume new patients have opted-in'); ?>
                        </label>
                    </div>
                </div>

                <!-- Column 3: Facilities -->
                <div class="section-card">
                    <div class="section-title"><i class="fa fa-building"></i> <?php echo xlt('Facilities'); ?></div>
                    <div class="list-controls">
                        <button type="button" class="btn btn-xs btn-primary" onclick="sortCheckedFirst('facilities-group')">
                            <i class="fa fa-check"></i> <?php echo xlt('Checked First'); ?>
                        </button>
                        <button type="button" class="btn btn-xs btn-secondary" onclick="sortAlpha('facilities-group', true)">
                            <i class="fa fa-sort-alpha-asc"></i> <?php echo xlt('A→Z'); ?>
                        </button>
                        <button type="button" class="btn btn-xs btn-secondary" onclick="sortAlpha('facilities-group', false)">
                            <i class="fa fa-sort-alpha-desc"></i> <?php echo xlt('Z→A'); ?>
                        </button>
                        <button type="button" class="btn btn-xs btn-success" onclick="selectAll('facilities-group')">
                            <?php echo xlt('Select All'); ?>
                        </button>
                        <button type="button" class="btn btn-xs btn-warning" onclick="deselectAll('facilities-group')">
                            <?php echo xlt('Deselect All'); ?>
                        </button>
                    </div>
                    <div class="checkbox-group" id="facilities-group">
                        <?php
                        $facilities = sqlStatement("SELECT * FROM facility ORDER BY name");
                        $enabled_facs = isset($prefs['ME_facilities']) ? explode('|', $prefs['ME_facilities']) : [];
                        while ($fac = sqlFetchArray($facilities)) {
                            $checked = in_array($fac['id'], $enabled_facs) ? 'checked' : '';
                        ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="facilities[]" id="facility_<?php echo attr($fac['id']); ?>" value="<?php echo attr($fac['id']); ?>" <?php echo attr($checked); ?>>
                                <label class="form-check-label" for="facility_<?php echo attr($fac['id']); ?>">
                                    <?php echo text($fac['name']); ?>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Column 4: Providers -->
                <div class="section-card">
                    <div class="section-title"><i class="fa fa-user-md"></i> <?php echo xlt('Providers'); ?></div>
                    <div class="list-controls">
                        <button type="button" class="btn btn-xs btn-primary" onclick="sortCheckedFirst('providers-group')">
                            <i class="fa fa-check"></i> <?php echo xlt('Checked First'); ?>
                        </button>
                        <button type="button" class="btn btn-xs btn-secondary" onclick="sortAlpha('providers-group', true)">
                            <i class="fa fa-sort-alpha-asc"></i> <?php echo xlt('A→Z'); ?>
                        </button>
                        <button type="button" class="btn btn-xs btn-secondary" onclick="sortAlpha('providers-group', false)">
                            <i class="fa fa-sort-alpha-desc"></i> <?php echo xlt('Z→A'); ?>
                        </button>
                        <button type="button" class="btn btn-xs btn-success" onclick="selectAll('providers-group')">
                            <?php echo xlt('Select All'); ?>
                        </button>
                        <button type="button" class="btn btn-xs btn-warning" onclick="deselectAll('providers-group')">
                            <?php echo xlt('Deselect All'); ?>
                        </button>
                    </div>
                    <div class="checkbox-group" id="providers-group">
                        <?php
                        $providers = sqlStatement("SELECT id, fname, lname FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname");
                        $enabled_provs = isset($prefs['ME_providers']) ? explode('|', $prefs['ME_providers']) : [];
                        while ($prov = sqlFetchArray($providers)) {
                            $checked = in_array($prov['id'], $enabled_provs) ? 'checked' : '';
                            $name = $prov['lname'] . ', ' . $prov['fname'];
                        ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="providers[]" id="provider_<?php echo attr($prov['id']); ?>" value="<?php echo attr($prov['id']); ?>" <?php echo attr($checked); ?>>
                                <label class="form-check-label" for="provider_<?php echo attr($prov['id']); ?>">
                                    <?php echo text($name); ?>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Labels and Postcards -->
                <div class="section-card">
                    <div class="section-title"><i class="fa fa-print"></i> <?php echo xlt('Labels & Postcards'); ?></div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="LABELS_local" id="LABELS_local" value="1" <?php echo ($prefs['LABELS_local'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="LABELS_local"><?php echo xlt('Local labels'); ?></label>
                    </div>
                    <div class="form-group mt-2">
                        <label for="LABELS_choice" style="font-size:12px;"><?php echo xlt('Template'); ?></label>
                        <select class="form-control form-control-sm" name="LABELS_choice" id="LABELS_choice">
                            <option value="1" <?php echo ($prefs['LABELS_choice'] ?? '1') == '1' ? 'selected' : ''; ?>><?php echo xlt('Avery 5160'); ?></option>
                            <option value="2" <?php echo ($prefs['LABELS_choice'] ?? '1') == '2' ? 'selected' : ''; ?>><?php echo xlt('Avery 5161'); ?></option>
                            <option value="3" <?php echo ($prefs['LABELS_choice'] ?? '1') == '3' ? 'selected' : ''; ?>><?php echo xlt('Avery 5162'); ?></option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="POSTCARDS_local" id="POSTCARDS_local" value="1" <?php echo ($prefs['POSTCARDS_local'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="POSTCARDS_local"><?php echo xlt('Local postcards'); ?></label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="POSTCARDS_remote" id="POSTCARDS_remote" value="1" <?php echo ($prefs['POSTCARDS_remote'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="POSTCARDS_remote"><?php echo xlt('Remote postcards'); ?></label>
                    </div>
                    <div class="form-group mt-2">
                        <label for="postcard_top" style="font-size:12px;"><?php echo xlt('Postcard Message'); ?></label>
                        <input type="text" class="form-control form-control-sm" name="postcard_top" id="postcard_top" value="<?php echo attr($prefs['postcard_top'] ?? ''); ?>">
                    </div>
                </div>

                <!-- SMS & Sync Settings -->
                <div class="section-card">
                    <div class="section-title"><i class="fa fa-mobile"></i> <?php echo xlt('SMS & Sync'); ?></div>
                    <div class="form-group">
                        <div class="inline-row">
                            <label for="PHONE_country_code" style="font-size:12px;"><?php echo xlt('Country Code'); ?></label>
                            <input type="number" class="form-control form-control-sm" name="PHONE_country_code" id="PHONE_country_code" value="<?php echo attr($prefs['PHONE_country_code'] ?? '1'); ?>" min="1" max="999">
                        </div>
                        <div class="help-text inline-help"><?php echo xlt('Default: 1 (US/Canada)'); ?></div>
                    </div>
                    <div class="form-group">
                        <div class="inline-row">
                            <label for="sms_bot_phone_style" style="font-size:12px;"><?php echo xlt('SMS Bot Display'); ?></label>
                            <select class="form-control form-control-sm" name="sms_bot_phone_style" id="sms_bot_phone_style">
                            <option value="S8" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'S8' ? 'selected' : ''; ?>><?php echo xlt('Samsung Galaxy S8'); ?></option>
                            <option value="iPhone14" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'iPhone14' ? 'selected' : ''; ?>><?php echo xlt('iPhone 14'); ?></option>
                            <option value="iPhone4" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'iPhone4' ? 'selected' : ''; ?>><?php echo xlt('iPhone 4s'); ?></option>
                            <option value="Pixel8" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'Pixel8' ? 'selected' : ''; ?>><?php echo xlt('Google Pixel 8'); ?></option>
                            <option value="minimal" <?php echo ($prefs['sms_bot_phone_style'] ?? 'S8') == 'minimal' ? 'selected' : ''; ?>><?php echo xlt('Minimal'); ?></option>
                            </select>
                        </div>
                        <div class="help-text inline-help">
                            <?php echo xlt('Reload'); ?>
                            <a href="<?php echo attr($GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-medex/public/sms_bot_list.php'); ?>" target="_blank" rel="noopener">
                                <?php echo xlt('SMS Bot'); ?>
                            </a>
                            <?php echo xlt('to see changes.'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="inline-row">
                            <label for="execute_interval" style="font-size:12px;"><?php echo xlt('Sync Frequency'); ?></label>
                            <input type="range" name="execute_interval" id="execute_interval" min="0" max="360" step="1" value="<?php echo attr($prefs['execute_interval'] ?? '29'); ?>" style="width: 100%;">
                        </div>
                        <div class="help-text inline-help" style="margin-top: 5px;">
                            <span id="sync_display">
                                <?php if (($prefs['execute_interval'] ?? '29') == '0'): ?>
                                    <strong style="color: #dc3545;"><?php echo xlt('Sync paused'); ?></strong>
                                <?php else: ?>
                                    <?php echo xlt('Sync every'); ?> <strong id="interval_value" style="color: #0f4b8f;"><?php echo text($prefs['execute_interval'] ?? '29'); ?></strong> <?php echo xlt('minutes'); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save & Sync Button -->
            <div class="text-center mb-3">
                <div id="save-result" style="margin-bottom: 15px;"></div>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fa fa-save"></i> <?php echo xlt('Save & Sync'); ?>
                </button>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('#prefs-form').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'save_preferences.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var message = '<strong><i class="fa fa-check-circle"></i> <?php echo xla('Settings saved successfully'); ?></strong>';
                            if (response.sync_triggered) {
                                message += '<br><?php echo xla('Server updated with new facilities/providers'); ?>';
                            }
                            $('#save-result').html('<div class="alert alert-success">' + message + '</div>');

                            // Auto-hide after 5 seconds
                            setTimeout(function() {
                                $('#save-result').fadeOut(function() {
                                    $(this).html('').show();
                                });
                            }, 5000);
                        } else {
                            $('#save-result').html('<div class="alert alert-danger"><strong><i class="fa fa-exclamation-circle"></i> <?php echo xla('Error'); ?></strong><br>' + response.error + '</div>');
                        }
                    },
                    error: function() {
                        $('#save-result').html('<div class="alert alert-danger"><strong><i class="fa fa-exclamation-circle"></i> <?php echo xla('Error'); ?></strong><br><?php echo xla('An error occurred while saving settings'); ?></div>');
                    }
                });
            });

            // Update sync frequency display
            $('#execute_interval').on('input', function() {
                var value = $(this).val();
                if (value == 0) {
                    $('#sync_display').html('<strong style="color: #dc3545;"><?php echo xla('Sync paused'); ?></strong>');
                } else {
                    $('#sync_display').html('<?php echo xla('Sync every'); ?> <strong id="interval_value" style="color: #0f4b8f;">' + value + '</strong> <?php echo xla('minutes'); ?>');
                }
            });

            // Advanced settings button - opens modal
            $('#advanced-settings-btn').on('click', function() {
                $('#advanced-modal').modal('show');
            });

            // Toggle MedEx enable state
            $('#medex_enable_toggle').on('change', function() {
                var enabled = $(this).is(':checked');
                $('#medex_enable').val(enabled ? '1' : '0');
                $('#medex_enable_label').text(enabled ? '<?php echo xla('Enabled'); ?>' : '<?php echo xla('Disabled'); ?>');
            });

            // Reset connection button in modal
            $('#confirm-disconnect-btn').on('click', function() {
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo xla('Disconnecting...'); ?>');

                top.restoreSession();
                $.ajax({
                    url: 'reset_connection.php',
                    type: 'POST',
                    data: {
                        csrf_token_form: $('input[name="csrf_token_form"]').val()
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            window.location.href = 'register.php';
                        } else {
                            alert(<?php echo xlj('Disconnect failed'); ?> + ': ' + (response.error || <?php echo xlj('Unknown error'); ?>));
                            $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> <?php echo xla('Disconnect'); ?>');
                        }
                    },
                    error: function() {
                        alert(<?php echo xlj('An error occurred during disconnect'); ?>);
                        $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> <?php echo xla('Disconnect'); ?>');
                    }
                });
            });
        });

        // Checkbox group sorting and selection functions
        function sortCheckedFirst(groupId) {
            var container = document.getElementById(groupId);
            var items = Array.from(container.querySelectorAll('.form-check'));

            items.sort(function(a, b) {
                var aChecked = a.querySelector('input[type="checkbox"]').checked;
                var bChecked = b.querySelector('input[type="checkbox"]').checked;
                if (aChecked === bChecked) return 0;
                return aChecked ? -1 : 1;
            });

            items.forEach(function(item) {
                container.appendChild(item);
            });
        }

        function sortAlpha(groupId, ascending) {
            var container = document.getElementById(groupId);
            var items = Array.from(container.querySelectorAll('.form-check'));

            items.sort(function(a, b) {
                var aText = a.querySelector('label').textContent.trim().toLowerCase();
                var bText = b.querySelector('label').textContent.trim().toLowerCase();
                if (ascending) {
                    return aText.localeCompare(bText);
                } else {
                    return bText.localeCompare(aText);
                }
            });

            items.forEach(function(item) {
                container.appendChild(item);
            });
        }

        function selectAll(groupId) {
            var container = document.getElementById(groupId);
            container.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        }

        function deselectAll(groupId) {
            var container = document.getElementById(groupId);
            container.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.checked = false;
            });
        }
    </script>

    <!-- Advanced Settings Modal -->
    <div class="modal fade" id="advanced-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: #dc3545; color: white;">
                    <h5 class="modal-title"><i class="fa fa-exclamation-triangle"></i> <?php echo xlt('Advanced Settings'); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 0.8;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div style="margin-bottom: 15px;">
                        <strong><?php echo xlt('Connection Details'); ?></strong>
                        <div style="font-size: 12px; margin-top: 8px;">
                            <div style="margin-bottom: 4px;">
                                <span style="color: #666;"><?php echo xlt('Practice ID'); ?>:</span>
                                <strong><?php echo text($medex_practice_id); ?></strong>
                            </div>
                            <div style="margin-bottom: 4px;">
                                <span style="color: #666;"><?php echo xlt('API Key'); ?>:</span>
                                <code style="font-size: 10px;"><?php echo text(substr($medex_api_key, 0, 20) . '...'); ?></code>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 4px;">
                        <strong style="color: #856404;"><i class="fa fa-exclamation-triangle"></i> <?php echo xlt('Danger Zone'); ?></strong>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #856404;">
                            <?php echo xlt('Disconnecting will permanently remove all MedEx credentials and settings. You will need to re-register to use MedEx services again.'); ?>
                        </p>
                        <button type="button" id="confirm-disconnect-btn" class="btn btn-danger btn-sm" style="margin-top: 10px;">
                            <i class="fa fa-trash"></i> <?php echo xlt('Disconnect'); ?>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <?php echo xlt('Close'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
