<?php

/**
 * CalendarInjectionListener - Injects MedEx Calendar when subscription is active
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx\Listeners;

class CalendarInjectionListener
{
    /**
     * Inject MedEx calendar replacement
     * Called from bootstrap shutdown function when calendar page is detected
     */
    public function injectCalendar(): void
    {
        // Only inject when module is truly active (enabled + configured).
        try {
            $api = new \OpenEMR\Modules\MedEx\MedExAPI();
            if (!$api->isActive()) {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        // Check for Calendar subscription
        $hasCalendarSubscription = false;
        try {
            $statusRecord = sqlQuery("SELECT status FROM medex_prefs LIMIT 1");
            if (!empty($statusRecord['status'])) {
                $status = json_decode($statusRecord['status'], true);
                $services = $status['enabled_services'] ?? [];
                // enabled_services is an assoc array {"calendar_full": true} — use isset, not in_array
                $svcOn = function($k) use ($services) {
                    return (isset($services[$k]) && $services[$k]) || in_array($k, $services);
                };
                // Full calendar redirect requires explicit calendar_full entitlement.
                $hasCalendarSubscription = $svcOn('calendar_full');
            }
        } catch (\Exception $e) {
            error_log('[MedEx Calendar] Error checking calendar subscription: ' . $e->getMessage());
            return;
        }

        if (!$hasCalendarSubscription) {
            error_log('[MedEx Calendar] No calendar subscription - skipping injection');
            return;
        }

        // Check user preferences - allow individual users to opt out
        $userId = $_SESSION['authUserID'] ?? null;
        if ($userId) {
            try {
                $userPrefs = sqlQuery(
                    "SELECT setting_value FROM user_settings WHERE setting_user = ? AND setting_label = 'medex_preferences'",
                    [$userId]
                );

                if (!empty($userPrefs['setting_value'])) {
                    $prefs = json_decode($userPrefs['setting_value'], true);
                    // If user explicitly disabled Full Calendar, skip injection
                    if (isset($prefs['use_full_calendar']) && !$prefs['use_full_calendar']) {
                        error_log('[MedEx Calendar] User ' . $userId . ' has disabled Full Calendar - skipping injection');
                        return;
                    }
                }
            } catch (\Exception $e) {
                error_log('[MedEx Calendar] Error checking user preferences: ' . $e->getMessage());
                // Continue with injection if there's an error reading preferences
            }
        }

        error_log('[MedEx Calendar] Calendar subscription detected - injecting redirect');

        // Redirect to MedEx calendar
        $webroot = $GLOBALS['webroot'] ?? '';
        $siteId = $_SESSION['site_id'] ?? 'default';
        $redirectUrl = $webroot . '/interface/modules/custom_modules/oe-module-medex/public/calendar/index.php?site=' . urlencode($siteId);

        // Preserve any query parameters
        if (!empty($_GET)) {
            $params = $_GET;
            unset($params['site']); // Don't duplicate site param
            if (!empty($params)) {
                $redirectUrl .= '&' . http_build_query($params);
            }
        }

        header('Location: ' . $redirectUrl);
        exit;
    }
}
