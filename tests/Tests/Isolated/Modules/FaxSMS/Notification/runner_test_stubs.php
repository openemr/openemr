<?php

/**
 * Global-function stubs for AppointmentNotificationRunnerTest.
 *
 * The runner calls three procedural helpers at top of namespace
 * (`text`, `cron_InsertNotificationLogEntryFaxsms`,
 * `rc_sms_notification_cron_update_entry`). PHP resolves unqualified
 * function calls inside `OpenEMR\Modules\FaxSMS\Notification` first
 * to that namespace and then to global, so the stubs are declared
 * here in the runner's own namespace to short-circuit the lookup
 * with no DB dependency. The shared spy state lives in the global
 * namespace so the test class can read/reset it.
 *
 * Bracketed namespace syntax is required so this file can declare
 * symbols in two namespaces at once.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    if (!class_exists(\AppointmentNotificationRunnerTestSpy::class, false)) {
        class AppointmentNotificationRunnerTestSpy
        {
            /** @var list<array{type: string, row: array<mixed>, logData: array<mixed>}> */
            public static array $logCalls = [];
            /** @var list<array{channel: string, pid: int, eid: int, recur: string}> */
            public static array $markCalls = [];

            public static function reset(): void
            {
                self::$logCalls = [];
                self::$markCalls = [];
            }
        }
    }
}

namespace OpenEMR\Modules\FaxSMS\Notification {
    use AppointmentNotificationRunnerTestSpy;
    use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;

    if (!function_exists('OpenEMR\\Modules\\FaxSMS\\Notification\\text')) {
        function text(mixed $value): string
        {
            if ($value === null) {
                return '';
            }
            if (is_string($value)) {
                return htmlspecialchars($value, ENT_NOQUOTES);
            }
            if (is_int($value) || is_float($value) || is_bool($value)) {
                return htmlspecialchars((string) $value, ENT_NOQUOTES);
            }
            return '';
        }
    }

    if (!function_exists('OpenEMR\\Modules\\FaxSMS\\Notification\\cron_InsertNotificationLogEntryFaxsms')) {
        /**
         * Production helper signature is fully untyped; mirror that with
         * `array<mixed>` so the runner's loosely-shaped legacy arrays
         * pass static analysis without forcing extra narrowing.
         *
         * @param array<mixed> $row
         * @param array<mixed> $logData
         */
        function cron_InsertNotificationLogEntryFaxsms(string $type, array $row, array $logData): bool
        {
            AppointmentNotificationRunnerTestSpy::$logCalls[] = [
                'type' => $type,
                'row' => $row,
                'logData' => $logData,
            ];
            return true;
        }
    }

    if (!function_exists('OpenEMR\\Modules\\FaxSMS\\Notification\\rc_sms_notification_cron_update_entry')) {
        function rc_sms_notification_cron_update_entry(
            NotificationChannel $channel,
            int $pid,
            int $eid,
            string $recur,
        ): void {
            AppointmentNotificationRunnerTestSpy::$markCalls[] = [
                'channel' => $channel->value,
                'pid' => $pid,
                'eid' => $eid,
                'recur' => $recur,
            ];
        }
    }
}
