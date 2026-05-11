<?php

/**
 * MedEx Module - Save Preferences
 *
 * Handles saving of MedEx preferences to database
 */

// Ensure site parameter exists to prevent "Site ID is missing" errors
if (empty($_GET['site'])) {
    $_GET['site'] = 'default';
}

require_once(__DIR__ . "/../../../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

// Set JSON response header
header('Content-Type: application/json');

// Check admin access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Verify CSRF token
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"] ?? '', 'default')) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

error_log('[MedEx save_preferences] Called with POST data: ' . json_encode($_POST));

try {
    $getGlobalPref = function (string $name, string $default = ''): string {
        $row = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
            "SELECT gl_value FROM globals WHERE gl_name = ? ORDER BY gl_index DESC LIMIT 1",
            [$name]
        );
        return isset($row['gl_value']) ? (string)$row['gl_value'] : $default;
    };

    // Handle global configuration settings
    if (isset($_POST['medex_enable'])) {
        $requestedEnable = ((string)$_POST['medex_enable'] === '1');
        if ($requestedEnable) {
            require_once(__DIR__ . '/../src/MedExAPI.php');
            $api = new \OpenEMR\Modules\MedEx\MedExAPI();
            $enabledServices = $api->getEnabledServices(true);
            if (empty($enabledServices)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Activate at least one service before enabling MedEx'
                ]);
                exit;
            }
        }
        \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException("REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_enable', 0, ?)", [$_POST['medex_enable']]);
    }

    $bill_notify_receipts_current = $getGlobalPref('medex_bill_notify_receipts', '1');
    $bill_notify_failures_current = $getGlobalPref('medex_bill_notify_failures', '1');
    $bill_notify_cancellations_current = $getGlobalPref('medex_bill_notify_cancellations', '1');
    $bill_notify_email_current = $getGlobalPref('medex_bill_notify_email', '');

    $bill_notify_receipts = isset($_POST['ME_bill_notify_receipts_present'])
        ? (isset($_POST['ME_bill_notify_receipts']) ? '1' : '0')
        : $bill_notify_receipts_current;
    $bill_notify_failures = isset($_POST['ME_bill_notify_failures_present'])
        ? (isset($_POST['ME_bill_notify_failures']) ? '1' : '0')
        : $bill_notify_failures_current;
    $bill_notify_cancellations = isset($_POST['ME_bill_notify_cancellations_present'])
        ? (isset($_POST['ME_bill_notify_cancellations']) ? '1' : '0')
        : $bill_notify_cancellations_current;
    $bill_notify_email = isset($_POST['ME_bill_notify_email'])
        ? trim((string)$_POST['ME_bill_notify_email'])
        : $bill_notify_email_current;

    if ($bill_notify_email !== '' && !filter_var($bill_notify_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Billing notification email is invalid']);
        exit;
    }

    \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_bill_notify_receipts', 0, ?)",
        [$bill_notify_receipts]
    );
    \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_bill_notify_failures', 0, ?)",
        [$bill_notify_failures]
    );
    \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_bill_notify_cancellations', 0, ?)",
        [$bill_notify_cancellations]
    );
    \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_bill_notify_email', 0, ?)",
        [$bill_notify_email]
    );

    if (isset($_POST['execute_interval'])) {
        $executeInterval = max(0, (int)$_POST['execute_interval']);
        try {
            $bgRow = \OpenEMR\Common\Database\QueryUtils::querySingleRow(
                "SELECT name FROM background_services WHERE name = 'MedEx' LIMIT 1",
                []
            );
            if ($bgRow) {
                \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
                    "UPDATE background_services SET execute_interval = ? WHERE name = 'MedEx'",
                    [$executeInterval]
                );
            }
        } catch (\Throwable $e) {
            error_log('[MedEx save_preferences] Failed to persist execute_interval: ' . $e->getMessage());
        }
    }

    // Optional policy: default which open template categories are considered reschedulable.
    $reschedDefaultsEnabled = isset($_POST['medex_resched_defaults_enabled'])
        ? (((string)$_POST['medex_resched_defaults_enabled'] === '0') ? '0' : '1')
        : $getGlobalPref('medex_resched_defaults_enabled', '1');
    $reschedDefaultCategories = isset($_POST['medex_resched_default_categories'])
        ? trim((string)$_POST['medex_resched_default_categories'])
        : $getGlobalPref('medex_resched_default_categories', 'new,est,established');
    if ($reschedDefaultCategories === '') {
        $reschedDefaultCategories = 'new,est,established';
    }
    
    \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_resched_defaults_enabled', 0, ?)",
        [$reschedDefaultsEnabled]
    );
    \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
        "REPLACE INTO globals (gl_name, gl_index, gl_value) VALUES ('medex_resched_default_categories', 0, ?)",
        [$reschedDefaultCategories]
    );

    // Get current preferences or create new row
    $existing = \OpenEMR\Common\Database\QueryUtils::querySingleRow("SELECT * FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1", []);

    // Prepare data
    $onboarding = isset($_POST['onboarding']) ? true : false;

    // Handle both array and pipe-separated string formats for facilities
    // PRESERVE existing value if not submitted (e.g., when only toggling medex_enable)
    if (isset($_POST['facilities'])) {
        if (is_array($_POST['facilities'])) {
            $facilities = implode('|', $_POST['facilities']);
        } else {
            $facilities = $_POST['facilities']; // Already pipe-separated string
        }
    } else {
        // Preserve existing value if not submitted
        $facilities = $existing['ME_facilities'] ?? '';
    }

    // If onboarding, we might have reminders_providers[] instead of providers[]
    if ($onboarding && isset($_POST['reminders_providers'])) {
        $providers = implode('|', $_POST['reminders_providers']);
    } else {
        // Handle both array and pipe-separated string formats
        // PRESERVE existing value if not submitted
        if (isset($_POST['providers'])) {
            if (is_array($_POST['providers'])) {
                $providers = implode('|', $_POST['providers']);
            } else {
                $providers = $_POST['providers']; // Already pipe-separated string
            }
        } else {
            // Preserve existing value if not submitted
            $providers = $existing['ME_providers'] ?? '';
        }
    }
    $hipaa_override = isset($_POST['ME_hipaa_default_override']) ? '1' : '0';
    $msgs_default = isset($_POST['MSGS_default_yes']) ? '1' : '0';
    $labels_local = isset($_POST['LABELS_local']) ? '1' : '0';
    $postcards_local = isset($_POST['POSTCARDS_local']) ? '1' : '0';
    $postcards_remote = isset($_POST['POSTCARDS_remote']) ? '1' : '0';
    $labels_choice = $_POST['LABELS_choice'] ?? '1';
    $postcard_top = $_POST['postcard_top'] ?? '';
    $phone_country = $_POST['PHONE_country_code'] ?? '1';
    $sms_style = $_POST['sms_bot_phone_style'] ?? 'S8';

    // Check if facilities or providers changed (need to trigger sync)
    $facilities_changed = false;
    $providers_changed = false;

    if ($existing) {
        $old_facilities = $existing['ME_facilities'] ?? '';
        $old_providers = $existing['ME_providers'] ?? '';

        $facilities_changed = ($old_facilities !== $facilities);
        $providers_changed = ($old_providers !== $providers);
    }

    if ($existing) {
        $existingId = (int)($existing['id'] ?? 0);
        // Update existing preferences
        if ($existingId > 0) {
            \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET
                    ME_facilities = ?,
                    ME_providers = ?,
                    ME_hipaa_default_override = ?,
                    MSGS_default_yes = ?,
                    LABELS_local = ?,
                    LABELS_choice = ?,
                    POSTCARDS_local = ?,
                    POSTCARDS_remote = ?,
                    postcard_top = ?,
                    PHONE_country_code = ?,
                    sms_bot_phone_style = ?,
                    MedEx_lastupdated = NOW()
                WHERE id = ?",
                [
                    $facilities,
                    $providers,
                    $hipaa_override,
                    $msgs_default,
                    $labels_local,
                    $labels_choice,
                    $postcards_local,
                    $postcards_remote,
                    $postcard_top,
                    $phone_country,
                    $sms_style,
                    $existingId
                ]
            );
        } else {
            \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
                "UPDATE medex_prefs SET
                    ME_facilities = ?,
                    ME_providers = ?,
                    ME_hipaa_default_override = ?,
                    MSGS_default_yes = ?,
                    LABELS_local = ?,
                    LABELS_choice = ?,
                    POSTCARDS_local = ?,
                    POSTCARDS_remote = ?,
                    postcard_top = ?,
                    PHONE_country_code = ?,
                    sms_bot_phone_style = ?,
                    MedEx_lastupdated = NOW()
                WHERE ME_username = ?",
                [
                    $facilities,
                    $providers,
                    $hipaa_override,
                    $msgs_default,
                    $labels_local,
                    $labels_choice,
                    $postcards_local,
                    $postcards_remote,
                    $postcard_top,
                    $phone_country,
                    $sms_style,
                    $existing['ME_username']
                ]
            );
        }
    } else {
        // Insert new preferences (shouldn't happen if registered, but handle it)
        \OpenEMR\Common\Database\QueryUtils::sqlStatementThrowException(
            "INSERT INTO medex_prefs SET
                ME_facilities = ?,
                ME_providers = ?,
                ME_hipaa_default_override = ?,
                MSGS_default_yes = ?,
                LABELS_local = ?,
                LABELS_choice = ?,
                POSTCARDS_local = ?,
                POSTCARDS_remote = ?,
                postcard_top = ?,
                PHONE_country_code = ?,
                sms_bot_phone_style = ?,
                MedEx_lastupdated = NOW()",
            [
                $facilities,
                $providers,
                $hipaa_override,
                $msgs_default,
                $labels_local,
                $labels_choice,
                $postcards_local,
                $postcards_remote,
                $postcard_top,
                $phone_country,
                $sms_style
            ]
        );
    }

    // Auto-sync if facilities or providers changed, OR if service_id is present (indicates service-specific flag update)
    $sync_triggered = false;
    $service_flag_update = !empty($_POST['service_id']);

    error_log('[MedEx save_preferences] facilities_changed=' . ($facilities_changed ? 'true' : 'false') . ', providers_changed=' . ($providers_changed ? 'true' : 'false') . ', service_flag_update=' . ($service_flag_update ? 'true' : 'false'));

    if ($facilities_changed || $providers_changed || $service_flag_update) {
        error_log('[MedEx save_preferences] Condition met, initializing API...');
        require_once(__DIR__ . '/../src/MedExAPI.php');
        require_once(__DIR__ . '/../src/Services/PracticeService.php');

        $api = new \OpenEMR\Modules\MedEx\MedExAPI();

        error_log('[MedEx save_preferences] API isConfigured=' . ($api->isConfigured() ? 'true' : 'false') . ', isEnabled=' . ($api->isEnabled() ? 'true' : 'false'));

        if ($api->isConfigured()) {
            error_log('[MedEx save_preferences] API is configured, proceeding...');
            // Only perform full sync if facilities or providers actually changed
            if ($facilities_changed || $providers_changed) {
                $practiceService = new \OpenEMR\Modules\MedEx\Services\PracticeService($api);
                $syncResult = $practiceService->performInitialSync();
                $sync_triggered = true;
            }

            // If we have a service_id, update service-specific provider flags
            error_log('[MedEx save_preferences] About to check service_flag_update: ' . ($service_flag_update ? 'true' : 'false'));
            if ($service_flag_update) {
                error_log('[MedEx save_preferences] INSIDE service_flag_update block');
                $serviceId = $_POST['service_id'];
                error_log('[MedEx save_preferences] Got serviceId: ' . $serviceId);
                $providersList = $_POST['providers'] ?? '';
                error_log('[MedEx save_preferences] Got providersList: ' . $providersList);
                $facilitiesList = $_POST['facilities'] ?? '';
                error_log('[MedEx save_preferences] Got facilitiesList: ' . $facilitiesList);
                error_log('[MedEx save_preferences] About to log update message...');
                error_log('[MedEx] Updating active providers for service: ' . $serviceId . ', providers: ' . $providersList . ', facilities: ' . $facilitiesList);
                error_log('[MedEx save_preferences] Logged update message');

                // Convert pipe-separated to array
                $providersArray = $providersList ? explode('|', $providersList) : [];
                $facilitiesArray = $facilitiesList ? explode('|', $facilitiesList) : [];

                // Build data for API call
                $updateData = [
                    'service_key' => $serviceId,
                    'providers' => $providersArray,
                    'provider_count' => count($providersArray),
                    'facilities' => $facilitiesArray
                ];

                error_log("[MedEx] Sending update data: " . json_encode($updateData));

                // Call MedEx API to update subscription provider/facility list
                try {
                    $updateResult = $api->makeRequest('/api/subscriptions/update_providers.php', $updateData, 'POST');
                    error_log("[MedEx] Update result: " . json_encode($updateResult));
                    if (!empty($updateResult['success'])) {
                        error_log("[MedEx] Successfully updated service providers/facilities");
                    } else {
                        error_log("[MedEx] Warning: Failed to update service providers: " . ($updateResult['error'] ?? 'Unknown error'));
                    }
                } catch (Exception $e) {
                    error_log("[MedEx] Error updating service providers: " . $e->getMessage());
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Settings saved successfully',
        'sync_triggered' => $sync_triggered
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
