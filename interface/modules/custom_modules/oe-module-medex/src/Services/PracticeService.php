<?php

/**
 * MedEx Module - Practice Service
 *
 * Handles syncing practice data (facilities, providers, categories) to MedEx server
 */

namespace OpenEMR\Modules\MedEx\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;

class PracticeService
{
    private \OpenEMR\Modules\MedEx\MedExAPI $medexApi;
    private const EXCLUDED_PROVIDER_USERNAMES = ['admin', 'oe-system', 'phimail-service', 'portal-user'];
    private const EXCLUDED_PROVIDER_NAMES = ['admin', 'administrator', 'system operation user', 'patient portal user'];

    public function __construct(\OpenEMR\Modules\MedEx\MedExAPI $medexApi)
    {
        $this->medexApi = $medexApi;
    }

    private function resolveCallbackBaseUrl(string $siteAddr): string
    {
        if (method_exists(\OpenEMR\Modules\MedEx\MedExConfig::class, 'callbackBaseUrl')) {
            return \OpenEMR\Modules\MedEx\MedExConfig::callbackBaseUrl($siteAddr);
        }

        $fallback = trim($siteAddr);
        if ($fallback === '') {
            $fallback = trim((string)($GLOBALS['site_addr_oath'] ?? ''));
        }
        if ($fallback === '') {
            $host = trim((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
            $fallback = $host !== '' ? ('https://' . $host) : '';
        }

        return rtrim($fallback, '/');
    }

    /**
     * Persist enabled services returned by MedEx so the next module assembly/menu load
     * sees the same service truth without waiting for another successful login refresh.
     *
     * @param array<string,mixed> $response
     */
    private function persistEnabledServicesFromResponse(array $response): void
    {
        $enabledServices = [];
        foreach ([
            $response['enabled_services'] ?? null,
            $response['practice']['enabled_services'] ?? null,
        ] as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            foreach ($candidate as $serviceKey => $serviceValue) {
                if (is_int($serviceKey)) {
                    $normalized = trim((string)$serviceValue);
                    if ($normalized !== '') {
                        $enabledServices[$normalized] = $normalized;
                    }
                    continue;
                }
                if ($serviceValue === true || $serviceValue === 1 || $serviceValue === '1') {
                    $normalized = trim((string)$serviceKey);
                    if ($normalized !== '') {
                        $enabledServices[$normalized] = $normalized;
                    }
                }
            }
            if (!empty($enabledServices)) {
                break;
            }
        }

        if (empty($enabledServices)) {
            return;
        }

        $statusRow = QueryUtils::querySingleRow(
            "SELECT status FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1",
            []
        );
        $status = [];
        if (!empty($statusRow['status'])) {
            $decoded = json_decode((string)$statusRow['status'], true);
            if (is_array($decoded)) {
                $status = $decoded;
            }
        }

        $services = array_values($enabledServices);
        $status['enabled_services'] = $services;
        if (empty($status['practice']) || !is_array($status['practice'])) {
            $status['practice'] = [];
        }
        $status['practice']['enabled_services'] = $services;
        $status['last_services_result'] = $services;
        $status['last_services_check_ts'] = time();

        QueryUtils::sqlStatementThrowException(
            "UPDATE medex_prefs SET status = ? WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1",
            [json_encode($status)]
        );
    }

    /**
     * Sync practice data to MedEx server
     * This should be called after registration or when practice settings change
     * Authentication is handled automatically via makeRequest()
     */
    public function syncToMedEx(): array
    {
        try {
            // Get medex_prefs settings
            $prefsRecords = QueryUtils::fetchRecords("SELECT * FROM medex_prefs WHERE ME_username IS NOT NULL ORDER BY MedEx_lastupdated DESC LIMIT 1");
            $prefs = $prefsRecords[0] ?? null;

            if (!$prefs) {
                return [
                    'success' => false,
                    'error' => 'No MedEx preferences found. Please configure preferences first.'
                ];
            }

            // Build practice data payload
            $practiceData = $this->buildPracticeData($prefs);

            // Send to MedEx server
            // Note: makeRequest() already adds the encrypted API key as 'token' param
            // No need to add token to URL - it's handled by makeRequest()
            $response = $this->medexApi->makeRequest(
                'index.php?route=api/custom/addpractice',
                $practiceData,
                'POST'
            );

            if (!empty($response['success'])) {
                $this->persistEnabledServicesFromResponse((array)$response);
                // Update last sync time — second arg is required (even if empty)
                QueryUtils::sqlStatementThrowException("UPDATE medex_prefs SET MedEx_lastupdated = NOW() WHERE ME_username IS NOT NULL", []);

                return [
                    'success' => true,
                    'message' => 'Practice data synced successfully'
                ];
            }

            $errorMsg = $response['error'] ?? 'Sync failed';
            if (is_array($errorMsg)) {
                $errorMsg = json_encode($errorMsg);
            }

            return [
                'success' => false,
                'error' => $errorMsg
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => 'Sync error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build practice data payload for MedEx
     */
    private function buildPracticeData(array $prefs): array
    {
        $data = [];

        // Callback URL for MedEx to send data back to OpenEMR
        $callback_token = QueryUtils::fetchSingleValue("SELECT gl_value FROM globals WHERE gl_name = ?", 'gl_value', ['medex_callback_token']) ?? '';
        $site_addr = $GLOBALS['site_addr_oath'] ?? $GLOBALS['webroot'] ?? '';
        $siteId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default')));
        if ($siteId === '') {
            $siteId = 'default';
        }
        $callback_base = $this->resolveCallbackBaseUrl((string)$site_addr);
        $callback_url = $callback_base . '/interface/modules/custom_modules/oe-module-medex/public/callback.php?token=' . $callback_token . '&site=' . rawurlencode($siteId);
        $data['callback_url'] = $callback_url;

        // Get enabled providers
        $data['providers'] = [];
        if (!empty($prefs['ME_providers'])) {
            // Use configured providers
            $providers = array_filter(array_map('trim', explode('|', (string)$prefs['ME_providers'])));
            $providerSeen = [];
            foreach ($providers as $provider_id) {
                if (empty($provider_id)) continue;

                $providerRecords = QueryUtils::fetchRecords(
                    "SELECT id, fname, lname, username, specialty, npi, email, phonecell, suffix, active, facility_id, facility
                     FROM users
                     WHERE id = ?",
                    [$provider_id]
                );
                $provider = $providerRecords[0] ?? null;
                if ($provider && $this->shouldIncludeProvider($provider, $providerSeen)) {
                    $data['providers'][] = $provider;
                }
            }
        } else {
            // Default to all active providers, filtered and deduped by username.
            $providerCandidates = QueryUtils::fetchRecords(
                "SELECT id, fname, lname, username, specialty, npi, email, phonecell, suffix, active, facility_id, facility
                 FROM users
                 WHERE calendar = 1 AND active = 1
                 ORDER BY username, id DESC"
            );
            $providerSeen = [];
            foreach ($providerCandidates as $provider) {
                if ($this->shouldIncludeProvider((array)$provider, $providerSeen)) {
                    $data['providers'][] = $provider;
                }
            }
        }

        // Get enabled facilities
        $data['facilities'] = [];
        if (!empty($prefs['ME_facilities'])) {
            $facilities = explode('|', $prefs['ME_facilities']);
            $allFacilities = QueryUtils::fetchRecords("SELECT * FROM facility WHERE service_location = '1'");

            foreach ($allFacilities as $facility) {
                if (in_array($facility['id'], $facilities)) {
                    $facility['messages_active'] = '1';
                    $data['facilities'][] = $facility;
                }
            }
        }

        // Get appointment categories
        $data['categories'] = QueryUtils::fetchRecords(
            "SELECT pc_catid, pc_catname, pc_catdesc, pc_catcolor, pc_seq
             FROM openemr_postcalendar_categories
             WHERE pc_active = 1 AND pc_cattype = '0'
             ORDER BY pc_catid"
        );

        // Get appointment statuses
        $data['apptstats'] = QueryUtils::fetchRecords(
            "SELECT * FROM list_options
             WHERE list_id = 'apptstat' AND activity = '1'"
        );

        // Get checked-out statuses
        $data['checkedOut'] = QueryUtils::fetchRecords(
            "SELECT option_id FROM list_options
             WHERE toggle_setting_2 = '1' AND list_id = 'apptstat' AND activity = '1'"
        );

        // Get clinical reminders
        $sql = "SELECT * FROM clinical_rules, list_options, rule_action, rule_action_item
                WHERE clinical_rules.pid = 0
                AND clinical_rules.patient_reminder_flag = 1
                AND clinical_rules.id = list_options.option_id
                AND clinical_rules.id = rule_action.id
                AND list_options.option_id = clinical_rules.id
                AND rule_action.category = rule_action_item.category
                AND rule_action.item = rule_action_item.item";

        $data['clinical_reminders'] = QueryUtils::fetchRecords($sql);

        // Sanitize all data to ensure valid UTF-8 encoding
        return $this->sanitizeUtf8($data);
    }

    private function shouldIncludeProvider(array $provider, array &$providerSeen): bool
    {
        $username = strtolower(trim((string)($provider['username'] ?? '')));
        $fname = trim((string)($provider['fname'] ?? ''));
        $lname = trim((string)($provider['lname'] ?? ''));
        $displayName = strtolower(trim((string)(preg_replace('/\s+/', ' ', trim($fname . ' ' . $lname)) ?? trim($fname . ' ' . $lname))));
        if (
            $username === ''
            || in_array($username, self::EXCLUDED_PROVIDER_USERNAMES, true)
            || $displayName === ''
            || in_array($displayName, self::EXCLUDED_PROVIDER_NAMES, true)
        ) {
            return false;
        }
        if (isset($providerSeen[$username])) {
            return false;
        }
        $providerSeen[$username] = true;
        return true;
    }

    /**
     * Recursively sanitize data to ensure valid UTF-8 encoding
     * Prevents json_encode errors with malformed characters from database
     */
    private function sanitizeUtf8($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeUtf8'], $data);
        }
        if (is_string($data)) {
            // Convert to UTF-8, replacing invalid sequences
            $cleaned = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            // Remove any remaining invalid characters
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cleaned);
        }
        return $data;
    }

    /**
     * Perform initial sync after registration
     * This uses the credentials that were just saved during registration
     */
    public function performInitialSync(): array
    {
        // Sync practice data - authentication is handled by makeRequest()
        // which automatically adds the encrypted API key
        return $this->syncToMedEx();
    }
}
