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
    private static bool $nativeReturnControlRegistered = false;
    private static ?int $nativeReturnControlBufferLevel = null;
    private static ?string $nativeReturnControlPreferenceUrl = null;

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

    private function isExplicitOpenEMRRequest(): bool
    {
        return strtolower(trim((string)($_GET['medex_prefer'] ?? ''))) === 'openemr';
    }

    private function hasSessionNativeCalendarPreference(): bool
    {
        $value = $_SESSION['medex_use_openemr_calendar'] ?? false;
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return ((int)$value) === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    private function buildMedExCalendarRedirectUrl(): string
    {
        $webroot = (string)($GLOBALS['webroot'] ?? '');
        $siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));

        $params = ['site' => $siteId];

        $jumpDate = trim((string)($_GET['jumpdate'] ?? ''));
        $date = trim((string)($_GET['Date'] ?? ''));
        if ($jumpDate !== '') {
            $params['date'] = $jumpDate;
        } elseif (preg_match('/^\d{8}$/', $date) === 1) {
            $params['date'] = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        }

        $viewType = trim((string)($_GET['viewtype'] ?? ''));
        if ($viewType !== '') {
            $params['view'] = $viewType;
        }

        $provider = trim((string)($_GET['pc_username'] ?? $_GET['providers'] ?? ''));
        if ($provider !== '') {
            $params['providers'] = $provider;
        }

        $facility = trim((string)($_GET['pc_facility'] ?? $_GET['facilities'] ?? ''));
        if ($facility !== '') {
            $params['facilities'] = $facility;
        }

        return $webroot
            . '/interface/modules/custom_modules/oe-module-medex/public/calendar/index.php?'
            . http_build_query($params);
    }

    private function registerNativeCalendarReturnControl(string $preferenceUrl): void
    {
        if (self::$nativeReturnControlRegistered) {
            return;
        }
        self::$nativeReturnControlRegistered = true;
        self::$nativeReturnControlPreferenceUrl = $preferenceUrl;
        self::$nativeReturnControlBufferLevel = ob_get_level();

        ob_start();
        register_shutdown_function(function (): void {
            $this->flushNativeCalendarReturnControl();
        });
    }

    private function flushNativeCalendarReturnControl(): void
    {
        if (!self::$nativeReturnControlRegistered) {
            return;
        }

        $preferenceUrl = self::$nativeReturnControlPreferenceUrl;
        $bufferLevel = self::$nativeReturnControlBufferLevel;
        if ($preferenceUrl === null || $bufferLevel === null) {
            return;
        }

        $html = '';
        while (ob_get_level() > $bufferLevel) {
            $chunk = ob_get_clean();
            if ($chunk === false) {
                break;
            }
            $html = $chunk . $html;
        }

        if ($html === '') {
            return;
        }

        echo $this->injectNativeCalendarReturnControl($html, $preferenceUrl);
    }

    private function injectNativeCalendarReturnControl(string $html, string $preferenceUrl): string
    {
        if ($html === '' || stripos($html, 'medex-native-calendar-return') !== false) {
            return $html;
        }

        $script = <<<HTML
<script id="medex-native-calendar-return">
(function () {
    var redirectUrl = "__MEDEX_REDIRECT_URL__";

    function goToMedExCalendar(event) {
        if (event) {
            event.preventDefault();
        }

        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
            top.restoreSession();
            window.setTimeout(function () {
                window.location.href = redirectUrl;
            }, 100);
            return false;
        }

        window.location.href = redirectUrl;
        return false;
    }

    function ensureReturnSwitcher() {
        if (document.getElementById('medex-native-calendar-switcher')) {
            return;
        }

        var style = document.createElement('style');
        style.id = 'medex-native-calendar-return-style';
        style.textContent = ''
            + '#medex-native-calendar-switcher{margin:10px 0 14px 0;padding:0;box-sizing:border-box;max-width:180px;}'
            + '#medex-native-calendar-switcher-label{font-size:10px;color:#666;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;}'
            + '#medex-native-calendar-switcher .view-selector{display:flex;flex-direction:column;gap:1px;border:1px solid #0099CC;border-radius:3px;overflow:hidden;background:#fff;}'
            + '#medex-native-calendar-switcher .view-option{padding:8px;font-size:11px;border:none;text-align:left;transition:background .2s;}'
            + '#medex-native-calendar-switcher .view-option.active{background:#0099CC;color:#fff;cursor:default;font-weight:500;border-bottom:1px solid #0099CC;}'
            + '#medex-native-calendar-switcher .view-option:not(.active){background:#fff;color:#0099CC;cursor:pointer;border-top:1px solid #0099CC;}'
            + '#medex-native-calendar-switcher .view-option:not(.active):hover{background:#e8f4f8 !important;}';
        document.head.appendChild(style);

        var host = document.createElement('div');
        host.id = 'medex-native-calendar-switcher';

        var label = document.createElement('div');
        label.id = 'medex-native-calendar-switcher-label';
        label.textContent = 'Calendar View';

        var selector = document.createElement('div');
        selector.className = 'view-selector';

        var medexButton = document.createElement('button');
        medexButton.type = 'button';
        medexButton.className = 'view-option';
        medexButton.textContent = 'Full Calendar';
        medexButton.addEventListener('click', goToMedExCalendar);

        var nativeButton = document.createElement('button');
        nativeButton.type = 'button';
        nativeButton.className = 'view-option active';
        nativeButton.textContent = 'OpenEMR Calendar';

        selector.appendChild(medexButton);
        selector.appendChild(nativeButton);
        host.appendChild(label);
        host.appendChild(selector);

        function findWidgetsAnchor() {
            var candidates = document.querySelectorAll('h1,h2,h3,h4,h5,h6,th,td,div,span,strong,label');
            for (var i = 0; i < candidates.length; i++) {
                var node = candidates[i];
                var text = (node.textContent || '').replace(/\s+/g, ' ').trim();
                if (/^widgets$/i.test(text)) {
                    return node;
                }
            }
            return null;
        }

        var widgetsAnchor = findWidgetsAnchor();
        if (widgetsAnchor && widgetsAnchor.parentNode) {
            if (widgetsAnchor.nextSibling) {
                widgetsAnchor.parentNode.insertBefore(host, widgetsAnchor.nextSibling);
            } else {
                widgetsAnchor.parentNode.appendChild(host);
            }
            return;
        }

        var fallbackAnchor = document.querySelector('table, form, #bigcal, #calendar_display, .calendar, .monthview');
        if (fallbackAnchor && fallbackAnchor.parentNode) {
            fallbackAnchor.parentNode.insertBefore(host, fallbackAnchor);
        } else {
            document.body.insertBefore(host, document.body.firstChild);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureReturnSwitcher);
    } else {
        ensureReturnSwitcher();
    }
})();
</script>
HTML;

        $markup = str_replace(
            '"__MEDEX_REDIRECT_URL__"',
            json_encode($preferenceUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
            $script
        );

        $count = 0;
        $updated = preg_replace('/<\/body>/i', $markup . "\n</body>", $html, 1, $count);
        if ($count > 0 && is_string($updated)) {
            return $updated;
        }

        return $html . $markup;
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
        $explicitOpenEMRRequest = $this->isExplicitOpenEMRRequest();

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
        $isDisabled = false;
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

            } catch (\Exception $e) {
                error_log('[MedEx Calendar] Error checking user preferences: ' . $e->getMessage());
                // Continue with injection if there's an error reading preferences
            }
        }

        $stayOnNativeCalendar = $explicitOpenEMRRequest
            || $this->hasSessionNativeCalendarPreference()
            || $isDisabled;

        if ($stayOnNativeCalendar) {
            error_log('[MedEx Calendar] Native calendar requested - injecting MedEx return control');
            $medexUrl = $this->buildMedExCalendarRedirectUrl();
            $webroot = (string)($GLOBALS['webroot'] ?? '');
            $siteId = (string)($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
            $preferenceUrl = $webroot
                . '/interface/modules/custom_modules/oe-module-medex/public/calendar/set_calendar_preference.php?'
                . http_build_query([
                    'site' => $siteId,
                    'preference' => 'medex',
                    'redirect' => $medexUrl,
                ]);
            $this->registerNativeCalendarReturnControl($preferenceUrl);
            return;
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
