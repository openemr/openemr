<?php

/**
 * MedEx Calendar License Check
 *
 * Include this at the top of calendar/template pages to enforce licensing
 */

require_once(__DIR__ . '/../../src/Services/LicenseService.php');

use OpenEMR\Modules\MedEx\Services\LicenseService;

$licenseService = new LicenseService();

// Check if calendar feature is licensed
if (!$licenseService->canUseMedExCalendar()) {
    $reason = $licenseService->getCalendarUnavailableReason();

    // Redirect to OpenEMR calendar with message
    $_SESSION['medex_calendar_unavailable'] = $reason;

    header('Location: /interface/main/calendar/index.php');
    exit;
}
