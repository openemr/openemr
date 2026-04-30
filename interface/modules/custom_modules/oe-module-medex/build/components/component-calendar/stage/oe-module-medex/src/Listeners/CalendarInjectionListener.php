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
    private function normalizeServiceKey(string $serviceKey): string
    {
        $normalized = strtolower(str_replace([' ', '-'], '_', trim($serviceKey)));
        if ($normalized === 'calendar_service' || $normalized === 'calendar_services') {
            return 'calendar_ai';
        }
        if ($normalized === 'fullcalendar') {
            return 'calendar_full';
        }
        return $normalized;
    }

    private function hasDemoCalendarEntitlement(array $status): bool
    {
        $pricingCache = is_array($status['pricing_cache'] ?? null) ? $status['pricing_cache'] : [];
        $pricingTier = is_array($pricingCache['pricing_tier'] ?? null) ? $pricingCache['pricing_tier'] : [];
        $customerGroupId = (int)($pricingTier['customer_group_id'] ?? ($pricingCache['customer_group_id'] ?? 0));
        if (!in_array($customerGroupId, [3, 7], true)) {
            return false;
        }
        foreach ((array)($pricingCache['services'] ?? []) as $serviceKey => $serviceMeta) {
            if (!is_array($serviceMeta) || empty($serviceMeta['available'])) {
                continue;
            }
            $normalized = $this->normalizeServiceKey((string)$serviceKey);
            if (in_array($normalized, ['calendar_full', 'calendar_ai', 'calendar_services', 'calendar_export'], true)) {
                return true;
            }
        }
        return false;
    }

    private function isNativeCalendarEntryRequest(string $requestUri): bool
    {
        if ($requestUri === '') {
            return false;
        }

        if (strpos($requestUri, '/interface/modules/custom_modules/oe-module-medex/public/calendar/') !== false) {
            return false;
        }

        return strpos($requestUri, '/interface/main/calendar/index.php') !== false
            || preg_match('#/interface/main/calendar/?(?:\?|$)#', $requestUri) === 1;
    }

    /**
     * Inject MedEx calendar replacement
     * Called from bootstrap shutdown function when calendar page is detected
     */
    public function injectCalendar(): void
    {
        $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '');
        if (!$this->isNativeCalendarEntryRequest($requestUri)) {
            return;
        }
        if (strtolower(trim((string)($_GET['medex_prefer'] ?? ''))) === 'openemr') {
            return;
        }

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
                if (!$hasCalendarSubscription && is_array($status)) {
                    $hasCalendarSubscription = $this->hasDemoCalendarEntitlement($status);
                }
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
                $userPrefRows = sqlStatement(
                    "SELECT setting_label, setting_value
                       FROM user_settings
                      WHERE setting_user = ?
                        AND (setting_label = 'global:medex_use_full_calendar' OR setting_label = 'medex_preferences')",
                    [$userId]
                );
                $nativePref = null;
                $legacyPref = null;
                while ($row = sqlFetchArray($userPrefRows)) {
                    if (($row['setting_label'] ?? '') === 'global:medex_use_full_calendar') {
                        $nativePref = (string)($row['setting_value'] ?? '');
                    } elseif (($row['setting_label'] ?? '') === 'medex_preferences') {
                        $decoded = json_decode((string)($row['setting_value'] ?? ''), true);
                        if (is_array($decoded) && array_key_exists('use_full_calendar', $decoded)) {
                            $legacyPref = $decoded['use_full_calendar'];
                        }
                    }
                }

                $isDisabled = false;
                if ($nativePref !== null) {
                    $normalized = strtolower(trim($nativePref));
                    $isDisabled = in_array($normalized, ['0', 'false', 'off', 'no', 'n', 'disabled'], true);
                } elseif ($legacyPref !== null) {
                    if (is_bool($legacyPref)) {
                        $isDisabled = ($legacyPref === false);
                    } elseif (is_int($legacyPref) || is_float($legacyPref)) {
                        $isDisabled = ((int)$legacyPref === 0);
                    } elseif (is_string($legacyPref)) {
                        $normalized = strtolower(trim($legacyPref));
                        $isDisabled = in_array($normalized, ['0', 'false', 'off', 'no', 'n', 'disabled'], true);
                    } else {
                        $isDisabled = empty($legacyPref);
                    }
                }

                if ($isDisabled) {
                    error_log('[MedEx Calendar] User ' . $userId . ' has disabled Full Calendar - skipping injection');
                    return;
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
