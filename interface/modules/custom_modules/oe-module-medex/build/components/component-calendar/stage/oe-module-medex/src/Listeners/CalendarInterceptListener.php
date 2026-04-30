<?php

/**
 * Calendar Intercept Listener
 *
 * Intercepts navigation to OpenEMR calendar and redirects to MedEx calendar
 * when MedEx calendar is enabled AND subscription is active
 *
 * BUSINESS LOGIC:
 * - MedEx disabled → OpenEMR calendar
 * - Subscription expired → OpenEMR calendar
 * - Payment failed → OpenEMR calendar
 * - Feature not in plan → OpenEMR calendar
 * - Everything active → MedEx calendar
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MedEx <https://medexbank.com>
 * @copyright Copyright (c) 2026 MedEx
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\MedEx\Listeners;

use OpenEMR\Modules\MedEx\Services\LicenseService;

class CalendarInterceptListener
{
    /**
     * Check if MedEx calendar should be used
     *
     * THREE GATES:
     * 1. MedEx enabled?
     * 2. Subscription active?
     * 3. Calendar feature paid for?
     */
    public static function shouldUseMedExCalendar(): bool
    {
        require_once(__DIR__ . '/../Services/LicenseService.php');

        $licenseService = new LicenseService();

        // This checks all three gates
        return $licenseService->canUseMedExCalendar();
    }

    /**
     * Intercept calendar requests and redirect if licensed
     *
     * GRACEFUL FALLBACK:
     * - If any check fails → OpenEMR calendar shows
     * - No error messages (unless requested)
     * - Seamless user experience
     */
    public static function interceptCalendar(): void
    {
        // Honor bypass flag set when MedEx network is unavailable
        if (!empty($_SESSION['medex_calendar_skip'])) {
            unset($_SESSION['medex_calendar_skip']);
            return; // Let native OpenEMR calendar show
        }

        if (strtolower(trim((string)($_GET['medex_prefer'] ?? ''))) === 'openemr') {
            return;
        }

        // Check if we should use MedEx calendar
        if (!self::shouldUseMedExCalendar()) {
            // Fall back to OpenEMR calendar (do nothing)
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        // Only intercept the calendar list/view pages, NOT sub-pages like
        // add_edit_event.php, find_appt_popup.php, find_patient_popup.php, etc.
        // Intercepting those causes an infinite redirect loop because
        // edit_event_wrapper.php iframes add_edit_event.php which is under
        // /interface/main/calendar and would be intercepted again.
        $calendarEntryPoints = [
            '/interface/main/calendar/index.php',
            '/interface/main/calendar/',
        ];
        $isCalendarEntry = false;
        foreach ($calendarEntryPoints as $entry) {
            if (strpos($requestUri, $entry) !== false) {
                $isCalendarEntry = true;
                break;
            }
        }

        if ($isCalendarEntry && strpos($requestUri, '/oe-module-medex/') === false) {

            // Preserve query parameters
            $queryString = $_SERVER['QUERY_STRING'] ?? '';

            // Redirect to MedEx calendar
            $medexCalendarUrl = '/interface/modules/custom_modules/oe-module-medex/public/calendar/index.php';
            if ($queryString) {
                $medexCalendarUrl .= '?' . $queryString;
            }

            header('Location: ' . $medexCalendarUrl);
            exit;
        }
    }

    /**
     * Get reason why MedEx calendar isn't available
     * (for admin panel / error messages)
     */
    public static function getUnavailableReason(): string
    {
        require_once(__DIR__ . '/../Services/LicenseService.php');

        $licenseService = new LicenseService();
        return $licenseService->getCalendarUnavailableReason();
    }
}
