<?php

/**
 * MedEx User Preferences
 *
 * Allows individual users to configure their personal MedEx settings
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;

error_log('[MedEx User Prefs] Page loaded');

// Get current user ID
$userId = $_SESSION['authUserID'] ?? null;
error_log('[MedEx User Prefs] User ID: ' . ($userId ?? 'NULL'));

if (!$userId) {
    error_log('[MedEx User Prefs] ERROR: Not authenticated');
    die('Not authenticated');
}

// Build available provider/facility lists for default calendar filter preferences
$availableProviders = [];
$providerStmt = sqlStatement(
    "SELECT username, fname, lname
       FROM users
      WHERE active = 1
        AND authorized = 1
        AND calendar = 1
   ORDER BY lname ASC, fname ASC, username ASC"
);
while ($row = sqlFetchArray($providerStmt)) {
    $username = (string)($row['username'] ?? '');
    if ($username === '') {
        continue;
    }
    $availableProviders[] = [
        'username' => $username,
        'name' => trim(((string)($row['lname'] ?? '')) . ', ' . ((string)($row['fname'] ?? '')), ', ')
    ];
}

$availableFacilities = [];
$facilityStmt = sqlStatement(
    "SELECT id, name
       FROM facility
      WHERE (inactive IS NULL OR inactive = 0)
   ORDER BY name ASC"
);
while ($row = sqlFetchArray($facilityStmt)) {
    $id = (string)($row['id'] ?? '');
    if ($id === '') {
        continue;
    }
    $availableFacilities[] = [
        'id' => $id,
        'name' => (string)($row['name'] ?? ('Facility ' . $id))
    ];
}
$providerOptions = array_map(static function ($p) {
    return (string)$p['username'];
}, $availableProviders);
$facilityOptions = array_map(static function ($f) {
    return (string)$f['id'];
}, $availableFacilities);

// Add Font Awesome via CDN to fix missing icons
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'], 'default')) {
        die('Invalid CSRF token');
    }

    // Get preferences
    $useFullCalendar = isset($_POST['use_full_calendar']) ? 1 : 0;
    $calendarDefaultView = $_POST['calendar_default_view'] ?? 'month';
    $calendarTheme = (string)($_POST['calendar_theme'] ?? 'classic');
    $themeMode = (string)($_POST['theme_mode'] ?? '');
    if ($themeMode === 'inherit') {
        $inheritOpenEmrTheme = 1;
    } elseif ($themeMode === 'custom') {
        $inheritOpenEmrTheme = 0;
    } else {
        // Backward compatibility for older clients posting checkbox state.
        $inheritOpenEmrTheme = isset($_POST['inherit_openemr_theme']) ? 1 : 0;
    }
    $defaultProviders = isset($_POST['default_providers']) && is_array($_POST['default_providers']) ? array_values(array_unique(array_map('strval', $_POST['default_providers']))) : [];
    $defaultFacilities = isset($_POST['default_facilities']) && is_array($_POST['default_facilities']) ? array_values(array_unique(array_map('strval', $_POST['default_facilities']))) : [];
    $defaultProviders = array_values(array_intersect($defaultProviders, $providerOptions));
    $defaultFacilities = array_values(array_intersect($defaultFacilities, $facilityOptions));
    $allowedThemes = ['classic', 'compact', 'high_contrast', 'ocean', 'sunrise', 'forest', 'slate'];
    if (!in_array($calendarTheme, $allowedThemes, true)) {
        $calendarTheme = 'classic';
    }

    // Build preferences JSON
    $prefs = [
        'use_full_calendar' => $useFullCalendar,
        'calendar_default_view' => $calendarDefaultView,
        'calendar_theme' => $calendarTheme,
        'inherit_openemr_theme' => $inheritOpenEmrTheme,
        'default_provider_usernames' => $defaultProviders,
        'default_facility_ids' => $defaultFacilities
    ];

    // Save to user settings
    $prefsJson = json_encode($prefs);

    // Check if user already has medex preferences
    $existing = QueryUtils::querySingleRow("SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = 'medex_preferences'",
        [$userId]
    );

    if ($existing) {
        QueryUtils::sqlStatementThrowException(
            "UPDATE user_settings SET setting_value = ? WHERE setting_user = ? AND setting_label = 'medex_preferences'",
            [$prefsJson, $userId]
        );
    } else {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO user_settings (setting_user, setting_label, setting_value) VALUES (?, 'medex_preferences', ?)",
            [$userId, $prefsJson]
        );
    }

    $successMessage = xl('Preferences saved successfully');
}

// Load current preferences
$currentPrefs = [
    'use_full_calendar' => 1,
    'calendar_default_view' => 'month',
    'calendar_theme' => 'classic',
    'inherit_openemr_theme' => 0,
    'default_provider_usernames' => [],
    'default_facility_ids' => []
];

$existing = QueryUtils::querySingleRow("SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = 'medex_preferences'",
    [$userId]
);

if ($existing && !empty($existing['setting_value'])) {
    $saved = json_decode($existing['setting_value'], true);
    if ($saved) {
        $currentPrefs = array_merge($currentPrefs, $saved);
    }
}

// Get user info
$userInfo = QueryUtils::querySingleRow("SELECT username, fname, lname FROM users WHERE id = ?",
    [$userId]
);

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx User Preferences'); ?></title>
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/public/themes/style_light.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/public/assets/font-awesome/css/font-awesome.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .pref-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
        }
        .pref-section h3 {
            margin-top: 0;
            color: #007bff;
        }
        .pref-item {
            margin-bottom: 20px;
        }
        .pref-item label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #555;
        }
        .pref-item input[type="checkbox"] {
            margin-right: 8px;
        }
        .pref-item select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .option-list {
            max-height: 180px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            background: #fafafa;
        }
        .option-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 14px;
        }
        .option-item:last-child {
            margin-bottom: 0;
        }
        .pref-description {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fa fa-cog"></i> <?php echo xlt('MedEx User Preferences'); ?></h1>

        <div class="user-info">
            <strong><?php echo xlt('User'); ?>:</strong>
            <?php echo text($userInfo['fname'] . ' ' . $userInfo['lname']); ?>
            (<?php echo text($userInfo['username']); ?>)
        </div>

        <?php if (!empty($successMessage)): ?>
        <div class="success-message">
            <i class="fa fa-check-circle"></i> <?php echo text($successMessage); ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>">

            <!-- Calendar Preferences -->
            <div class="pref-section">
                <h3><i class="fa fa-calendar"></i> <?php echo xlt('Calendar Preferences'); ?></h3>

                <div class="pref-item">
                    <label>
                        <input type="checkbox"
                               name="use_full_calendar"
                               value="1"
                               <?php echo $currentPrefs['use_full_calendar'] ? 'checked' : ''; ?>>
                        <?php echo xlt('Use Enhanced Full Calendar View'); ?>
                    </label>
                    <div class="pref-description">
                        <?php echo xlt('Enable the modern FullCalendar interface with drag-and-drop scheduling (requires active subscription)'); ?>
                    </div>
                </div>

                <div class="pref-item">
                    <label><?php echo xlt('Theme Source'); ?></label>
                    <label style="font-weight:400; margin-bottom:6px;">
                        <input type="radio"
                               name="theme_mode"
                               id="theme_mode_inherit"
                               value="inherit"
                               <?php echo !empty($currentPrefs['inherit_openemr_theme']) ? 'checked' : ''; ?>>
                        <?php echo xlt('Inherit OpenEMR Theme'); ?>
                    </label>
                    <label style="font-weight:400; margin-bottom:6px;">
                        <input type="radio"
                               name="theme_mode"
                               id="theme_mode_custom"
                               value="custom"
                               <?php echo empty($currentPrefs['inherit_openemr_theme']) ? 'checked' : ''; ?>>
                        <?php echo xlt('Use Full Calendar Theme'); ?>
                    </label>
                    <div class="pref-description">
                        <?php echo xlt('Choose one: inherit your active OpenEMR theme, or use a Full Calendar theme below.'); ?>
                    </div>
                </div>

                <div class="pref-item">
                    <label><?php echo xlt('Calendar Theme'); ?></label>
                    <select name="calendar_theme" id="calendar_theme">
                        <option value="classic" <?php echo $currentPrefs['calendar_theme'] === 'classic' ? 'selected' : ''; ?>>
                            <?php echo xlt('Classic'); ?>
                        </option>
                        <option value="compact" <?php echo $currentPrefs['calendar_theme'] === 'compact' ? 'selected' : ''; ?>>
                            <?php echo xlt('Compact'); ?>
                        </option>
                        <option value="high_contrast" <?php echo $currentPrefs['calendar_theme'] === 'high_contrast' ? 'selected' : ''; ?>>
                            <?php echo xlt('High Contrast'); ?>
                        </option>
                        <option value="ocean" <?php echo $currentPrefs['calendar_theme'] === 'ocean' ? 'selected' : ''; ?>>
                            <?php echo xlt('Ocean'); ?>
                        </option>
                        <option value="sunrise" <?php echo $currentPrefs['calendar_theme'] === 'sunrise' ? 'selected' : ''; ?>>
                            <?php echo xlt('Sunrise'); ?>
                        </option>
                        <option value="forest" <?php echo $currentPrefs['calendar_theme'] === 'forest' ? 'selected' : ''; ?>>
                            <?php echo xlt('Forest'); ?>
                        </option>
                        <option value="slate" <?php echo $currentPrefs['calendar_theme'] === 'slate' ? 'selected' : ''; ?>>
                            <?php echo xlt('Slate'); ?>
                        </option>
                    </select>
                    <div class="pref-description">
                        <?php echo xlt('Choose how the Full Calendar looks for your account when OpenEMR theme inheritance is off.'); ?>
                    </div>
                </div>

                <div class="pref-item">
                    <label><?php echo xlt('Default Calendar View'); ?></label>
                    <select name="calendar_default_view">
                        <option value="month" <?php echo $currentPrefs['calendar_default_view'] === 'month' ? 'selected' : ''; ?>>
                            <?php echo xlt('Month View'); ?>
                        </option>
                        <option value="week" <?php echo $currentPrefs['calendar_default_view'] === 'week' ? 'selected' : ''; ?>>
                            <?php echo xlt('Week View'); ?>
                        </option>
                        <option value="day" <?php echo $currentPrefs['calendar_default_view'] === 'day' ? 'selected' : ''; ?>>
                            <?php echo xlt('Day View'); ?>
                        </option>
                        <option value="list" <?php echo $currentPrefs['calendar_default_view'] === 'list' ? 'selected' : ''; ?>>
                            <?php echo xlt('List View'); ?>
                        </option>
                    </select>
                    <div class="pref-description">
                        <?php echo xlt('Choose which view loads by default when you open the calendar'); ?>
                    </div>
                </div>

                <div class="pref-item">
                    <label><?php echo xlt('Default Providers To Show'); ?></label>
                    <div class="option-list">
                        <?php if (!empty($availableProviders)): ?>
                            <?php foreach ($availableProviders as $provider): ?>
                                <?php $selected = in_array((string)$provider['username'], $currentPrefs['default_provider_usernames'] ?? [], true); ?>
                                <label class="option-item">
                                    <input type="checkbox" name="default_providers[]" value="<?php echo attr($provider['username']); ?>" <?php echo $selected ? 'checked' : ''; ?>>
                                    <span><?php echo text($provider['name'] !== '' ? $provider['name'] : $provider['username']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="pref-description"><?php echo xlt('No calendar-enabled providers found.'); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="pref-description">
                        <?php echo xlt('If selected, Full Calendar opens with these providers checked by default.'); ?>
                    </div>
                </div>

                <div class="pref-item">
                    <label><?php echo xlt('Default Facilities To Show'); ?></label>
                    <div class="option-list">
                        <?php if (!empty($availableFacilities)): ?>
                            <?php foreach ($availableFacilities as $facility): ?>
                                <?php $selected = in_array((string)$facility['id'], $currentPrefs['default_facility_ids'] ?? [], true); ?>
                                <label class="option-item">
                                    <input type="checkbox" name="default_facilities[]" value="<?php echo attr($facility['id']); ?>" <?php echo $selected ? 'checked' : ''; ?>>
                                    <span><?php echo text($facility['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="pref-description"><?php echo xlt('No active facilities found.'); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="pref-description">
                        <?php echo xlt('If selected, Full Calendar opens with these facilities checked by default.'); ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn">
                <i class="fa fa-save"></i> <?php echo xlt('Save Preferences'); ?>
            </button>
        </form>
    </div>
    <script>
        (function () {
            var inheritRadio = document.getElementById('theme_mode_inherit');
            var customRadio = document.getElementById('theme_mode_custom');
            var themeSelect = document.getElementById('calendar_theme');
            if (!inheritRadio || !customRadio || !themeSelect) return;
            function syncState() {
                themeSelect.disabled = inheritRadio.checked;
            }
            inheritRadio.addEventListener('change', syncState);
            customRadio.addEventListener('change', syncState);
            syncState();
        })();
    </script>
</body>
</html>
