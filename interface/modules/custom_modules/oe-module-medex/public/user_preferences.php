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

// Add Font Awesome via CDN to fix missing icons
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'], $session)) {
        die('Invalid CSRF token');
    }

    // Get preferences
    $useFullCalendar = isset($_POST['use_full_calendar']) ? 1 : 0;
    $calendarDefaultView = $_POST['calendar_default_view'] ?? 'month';
    $showWeekends = isset($_POST['show_weekends']) ? 1 : 0;

    // Build preferences JSON
    $prefs = [
        'use_full_calendar' => $useFullCalendar,
        'calendar_default_view' => $calendarDefaultView,
        'show_weekends' => $showWeekends
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
    'show_weekends' => 1
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
            <input type="hidden" name="csrf_token" value="<?php echo attr(CsrfUtils::collectCsrfToken(session: $session)); ?>">

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
                    <label>
                        <input type="checkbox"
                               name="show_weekends"
                               value="1"
                               <?php echo $currentPrefs['show_weekends'] ? 'checked' : ''; ?>>
                        <?php echo xlt('Show Weekends'); ?>
                    </label>
                    <div class="pref-description">
                        <?php echo xlt('Display Saturday and Sunday in calendar views'); ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn">
                <i class="fa fa-save"></i> <?php echo xlt('Save Preferences'); ?>
            </button>
        </form>
    </div>
</body>
</html>
