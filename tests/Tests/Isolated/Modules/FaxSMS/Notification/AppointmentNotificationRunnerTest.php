<?php

/**
 * Isolated tests for the pure helpers on AppointmentNotificationRunner.
 *
 * The runner's `run()` orchestrator and per-channel delivery methods need a
 * live `AppDispatch` client and pull data via the global SQL helpers, so
 * those paths are exercised by E2E tests. The static helpers below are pure
 * — pull them out and pin behavior independently of the rest of the
 * scan-and-send pipeline so refactoring the orchestrator can't quietly
 * change phone normalization or the cron-window math.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\Notification;

use OpenEMR\Modules\FaxSMS\Notification\AppointmentNotificationRunner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Notification/AppointmentNotificationRunner.php';

// `renderMessage()` calls the global `text()` HTML-escape helper. The
// production helper is loaded via interface/globals.php — define a
// minimally-equivalent stub so renderMessage is testable in isolation.
if (!function_exists('text')) {
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

class AppointmentNotificationRunnerTest extends TestCase
{
    #[DataProvider('remainingHoursProvider')]
    public function testComputeRemainingHours(
        int $appointmentTimestamp,
        int $nowTimestamp,
        int $notificationHours,
        int $expected,
    ): void {
        $this->assertSame(
            $expected,
            AppointmentNotificationRunner::computeRemainingHours(
                $appointmentTimestamp,
                $nowTimestamp,
                $notificationHours,
            ),
        );
    }

    /**
     * @return array<string, array{int, int, int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function remainingHoursProvider(): array
    {
        $now = 1_745_000_000; // arbitrary anchor
        return [
            'appointment 24h out, lead time 24h, send now' => [$now + 86_400, $now, 24, 0],
            'appointment 25h out, lead time 24h, 1h early' => [$now + 90_000, $now, 24, 1],
            'appointment 23h out, lead time 24h, 1h late'  => [$now + 82_800, $now, 24, -1],
            'lead time 0, appointment 5h out'              => [$now + 18_000, $now, 0, 5],
            'lead time 48h, appointment 24h out'           => [$now + 86_400, $now, 48, -24],
        ];
    }

    #[DataProvider('phoneProvider')]
    public function testNormalizePhone(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, AppointmentNotificationRunner::normalizePhone($input));
    }

    /**
     * @return array<string, array{?string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function phoneProvider(): array
    {
        return [
            'plain ten digits'             => ['2125551234', '2125551234'],
            'leading 1 stripped'           => ['12125551234', '2125551234'],
            'formatted with parens dashes' => ['(212) 555-1234', '2125551234'],
            'plus-prefixed E.164'          => ['+12125551234', '2125551234'],
            'too short'                    => ['555-1234', null],
            'eleven-digit not starting 1'  => ['22125551234', null],
            'empty string'                 => ['', null],
            'null input'                   => [null, null],
            'letters only'                 => ['no-digits-here', null],
        ];
    }

    public function testRenderMessageSubstitutesAllTokens(): void
    {
        $template = '***NAME*** has an appointment with ***PROVIDER*** at ***ORG*** on ***DATE*** from ***STARTTIME*** to ***ENDTIME***.';
        $row = [
            'title' => 'Mr.',
            'fname' => 'Pat',
            'mname' => 'Q',
            'lname' => 'Patient',
            'utitle' => 'Dr.',
            'ufname' => 'Alex',
            'ulname' => 'Provider',
            'name' => 'Acme Clinic',
            'pc_eventDate' => '2026-04-15',
            'pc_startTime' => '10:30:00',
            'pc_endTime' => '11:00:00',
        ];

        $rendered = AppointmentNotificationRunner::renderMessage($template, $row);

        $this->assertStringContainsString('Mr. Pat Q Patient', $rendered);
        $this->assertStringContainsString('Dr. Alex Provider', $rendered);
        $this->assertStringContainsString('Acme Clinic', $rendered);
        $this->assertStringContainsString('Wednesday April 15, 2026', $rendered);
        $this->assertStringContainsString('10:30 AM', $rendered);
        $this->assertStringContainsString('11:00:00', $rendered);
    }

    public function testRenderMessageHandlesMissingFields(): void
    {
        // Missing optional fields should render as empty rather than
        // surface "Notice: undefined index" warnings.
        $template = 'Hello ***NAME***, see you ***DATE***.';
        $rendered = AppointmentNotificationRunner::renderMessage($template, []);

        $this->assertStringContainsString('Hello', $rendered);
        $this->assertStringContainsString('see you', $rendered);
    }
}
