<?php

declare(strict_types=1);

namespace OpenEMR\Modules\MedEx\ComponentCalendar;

use OpenEMR\Modules\MedEx\Listeners\CalendarInjectionListener;

require_once __DIR__ . '/../../src/Listeners/CalendarInjectionListener.php';

if (!function_exists(__NAMESPACE__ . '\\medex_calendar_component_bootstrap')) {
    function medex_calendar_component_bootstrap(): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        try {
            $listener = new CalendarInjectionListener();
            $listener->injectCalendar();
        } catch (\Throwable $e) {
            error_log('[MedEx Component Calendar] Bootstrap skipped: ' . $e->getMessage());
        }
    }

    medex_calendar_component_bootstrap();
}
