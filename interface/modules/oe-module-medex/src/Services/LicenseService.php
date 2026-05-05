<?php

/**
 * MedEx License Service
 *
 * Validates MedEx subscription status for premium features
 * Controls access to calendar, templates, and AI features
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MedEx <https://medexbank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\MedEx\Services;

class LicenseService
{
    private $medexBankUrl;
    private $apiKey;

    public function __construct()
    {
        $prefs = sqlQuery("SELECT ME_api_key FROM medex_prefs LIMIT 1");
        $this->apiKey = $prefs['ME_api_key'] ?? null;
        require_once __DIR__ . '/../MedExConfig.php';
        $this->medexBankUrl = MedExConfig::baseUrl();
    }

    /**
     * Check if MedEx is enabled in globals
     */
    public function isMedExEnabled(): bool
    {
        $setting = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'medex_enable'");
        return ($setting['gl_value'] ?? '0') === '1';
    }

    /**
     * Check if practice has active MedEx subscription
     *
     * @return array ['active' => bool, 'expires' => date, 'features' => array]
     */
    public function checkSubscription(): array
    {
        // If MedEx is disabled, subscription doesn't matter
        if (!$this->isMedExEnabled()) {
            return [
                'active' => false,
                'reason' => 'MedEx module disabled',
                'expires' => null,
                'features' => []
            ];
        }

        // No API key = no subscription
        if (!$this->apiKey) {
            return [
                'active' => false,
                'reason' => 'No API key configured',
                'expires' => null,
                'features' => []
            ];
        }

        // Use MedExAPI to get enabled services (uses session cache, avoids hammering server)
        try {
            require_once __DIR__ . '/../MedExAPI.php';
            $api = new \OpenEMR\Modules\MedEx\MedExAPI();
            $enabledServices = $api->getEnabledServices();

            if (!empty($enabledServices)) {
                $features = [];
                // Map service keys → feature names used by hasFeature()
                $serviceFeatureMap = [
                    'calendar_full'         => 'calendar',
                    'calendar_export'       => 'calendar',
                    'calendar_ai'           => 'calendar',
                    'appointment_reminders' => 'messaging',
                    'recall_campaigns'      => 'messaging',
                    'secure_chat'           => 'chat',
                    'pdf_management'        => 'pdf',
                    'telemedex'             => 'telemedex',
                ];
                foreach ($enabledServices as $svc) {
                    if (isset($serviceFeatureMap[$svc])) {
                        $features[] = $serviceFeatureMap[$svc];
                    }
                }
                $features = array_unique($features);

                return [
                    'active'   => true,
                    'expires'  => null,
                    'features' => $features,
                    'services' => $enabledServices,
                ];
            }
        } catch (\Exception $e) {
            error_log('[MedEx LicenseService] getEnabledServices failed: ' . $e->getMessage());
        }

        // Fall back to cached status in medex_prefs
        return $this->getCachedSubscriptionStatus();
    }

    /**
     * Check if specific feature is available in subscription
     *
     * @param string $feature 'calendar', 'templates', 'ai'
     * @return bool
     */
    public function hasFeature(string $feature): bool
    {
        $subscription = $this->checkSubscription();

        // Not active = no features
        if (!$subscription['active']) {
            return false;
        }

        // Check if feature is in subscription
        $features = $subscription['features'] ?? [];
        return in_array($feature, $features);
    }

    /**
     * Check if MedEx calendar should be shown
     *
     * Requirements:
     * 1. MedEx enabled in globals
     * 2. Active subscription
     * 3. Calendar feature included
     * 4. Calendar enabled in globals
     */
    public function canUseMedExCalendar(): bool
    {
        // Check if MedEx calendar is enabled in settings
        $calendarEnabled = sqlQuery(
            "SELECT gl_value FROM globals WHERE gl_name = 'medex_calendar_enabled'"
        );

        if (($calendarEnabled['gl_value'] ?? '0') !== '1') {
            return false; // Admin hasn't enabled it
        }

        // Check if MedEx module is enabled
        if (!$this->isMedExEnabled()) {
            return false; // MedEx disabled
        }

        // Check subscription and calendar feature
        return $this->hasFeature('calendar');
    }

    /**
     * Get cached subscription status (for when API is down)
     */
    private function getCachedSubscriptionStatus(): array
    {
        $cached = sqlQuery(
            "SELECT status FROM medex_prefs WHERE ME_username IS NOT NULL LIMIT 1"
        );

        if ($cached && !empty($cached['status'])) {
            $status = json_decode($cached['status'], true);

            // Check if cache is recent (within 24 hours)
            if (isset($status['cached_at'])) {
                $cacheAge = time() - strtotime($status['cached_at']);
                if ($cacheAge < 86400) {
                    return $status;
                }
            }
        }

        // No valid cache - assume inactive
        return [
            'active' => false,
            'reason' => 'Unable to verify subscription',
            'expires' => null,
            'features' => []
        ];
    }

    /**
     * Cache subscription status locally
     */
    public function cacheSubscriptionStatus(array $status): void
    {
        // Preserve existing enabled_services if not in new status
        $existing = sqlQuery("SELECT status FROM medex_prefs WHERE ME_username IS NOT NULL LIMIT 1");
        if (!empty($existing['status'])) {
            $existingStatus = json_decode($existing['status'], true);
            
            // Check if status is locked
            if (!empty($existingStatus['locked'])) {
                // Preserve enabled_services when locked
                if (!empty($existingStatus['enabled_services'])) {
                    $status['enabled_services'] = $existingStatus['enabled_services'];
                }
                $status['locked'] = 1;
            } else if (!empty($existingStatus['enabled_services']) && empty($status['enabled_services'])) {
                // Not locked but preserve if missing
                $status['enabled_services'] = $existingStatus['enabled_services'];
            }
        }
        
        $status['cached_at'] = date('Y-m-d H:i:s');

        sqlStatement(
            "UPDATE medex_prefs SET status = ? WHERE ME_username IS NOT NULL",
            [json_encode($status)]
        );
    }

    /**
     * Call MedExBank API
     */
    private function callMedExBank(string $endpoint, array $data): ?array
    {
        $ch = curl_init($this->medexBankUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 5
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);

            // Cache successful response
            if (isset($data['active'])) {
                $this->cacheSubscriptionStatus($data);
            }

            return $data;
        }

        return null;
    }

    /**
     * Get user-friendly message for why calendar isn't available
     */
    public function getCalendarUnavailableReason(): string
    {
        if (!$this->isMedExEnabled()) {
            return "MedEx module is disabled. Enable it in Administration > Globals.";
        }

        if (!$this->apiKey) {
            return "MedEx is not configured. Please register at MedExBank.com.";
        }

        $subscription = $this->checkSubscription();

        if (!$subscription['active']) {
            $reason = $subscription['reason'] ?? 'Unknown';

            if ($reason === 'subscription_expired') {
                return "Your MedEx subscription has expired. Please renew at MedExBank.com.";
            }

            if ($reason === 'payment_failed') {
                return "Payment failed. Please update your payment method at MedExBank.com.";
            }

            return "MedEx subscription is not active. Contact support@medexbank.com.";
        }

        if (!$this->hasFeature('calendar')) {
            return "Calendar feature is not included in your plan. Upgrade at MedExBank.com.";
        }

        return "Calendar feature is not available.";
    }
}
