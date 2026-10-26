<?php

/**
 * Database tests for the fee sheet's offer to set the visit's appointment status on Save.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\FeeSheetAppointmentPrompt;
use OpenEMR\Billing\FeeSheetAppointmentStatusPrompt;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FeeSheetAppointmentStatusPromptTest extends TestCase
{
    private const DATE = '2026-10-22';

    /** A pid no real patient has; the appointments are this test's only rows for it. */
    private int $pid = 0;

    private int $providerId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pid = random_int(900_000_000, 999_999_999);
        $this->providerId = QueryUtils::sqlInsert(
            "INSERT INTO users (username, password, authorized, active, fname, lname) VALUES (?, '', 1, 1, 'Ann', 'Coder')",
            ['coder' . $this->pid]
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM openemr_postcalendar_events WHERE pc_pid = ?', [$this->pid]);
        QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$this->providerId]);
        parent::tearDown();
    }

    public function testOffersTheStatusForTheVisitDatesLatestAppointment(): void
    {
        $this->appointment('09:00:00', '@');
        $latest = $this->appointment('10:30:00', '>');

        $prompt = (new FeeSheetAppointmentStatusPrompt('$'))->appointmentFor($this->pid, self::DATE);

        $this->assertEquals(new FeeSheetAppointmentPrompt($latest, self::DATE, '10:30', 'Ann Coder', '$', '$ Coding done'), $prompt);
        $this->assertNotNull($prompt);
        $this->assertSame(
            '/interface/main/calendar/index.php?module=PostCalendar&func=view&viewtype=day&Date=20261022',
            $prompt->calendarPath()
        );
        $date = oeFormatShortDate(self::DATE);
        $this->assertIsString($date);
        $this->assertSame('Set the 10:30 appointment on ' . $date . ' with Ann Coder to $ Coding done?', $prompt->question());
    }

    /**
     * @param string $setting the global's value
     * @param ?string $status the appointment's status, or null for no appointment that day
     */
    #[DataProvider('noQuestionProvider')]
    public function testNoQuestion(string $setting, ?string $status): void
    {
        if ($status !== null) {
            $this->appointment('10:30:00', $status);
        }

        $this->assertNull((new FeeSheetAppointmentStatusPrompt($setting))->appointmentFor($this->pid, self::DATE));
    }

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function noQuestionProvider(): array
    {
        return [
            'setting blank' => ['', '>'],
            'setting not a status' => ['NOPE', '>'],
            'no appointment that day' => ['$', null],
            'already has the status' => ['$', '$'],
            'canceled' => ['$', 'x'],
            'canceled < 24h' => ['$', '%'],
            'no show' => ['$', '?'],
        ];
    }

    private function appointment(string $startTime, string $status): int
    {
        return QueryUtils::sqlInsert(
            'INSERT INTO openemr_postcalendar_events (pc_catid, pc_multiple, pc_aid, pc_pid, pc_title, pc_eventDate,'
            . ' pc_startTime, pc_apptstatus, pc_recurrtype) VALUES (5, 0, ?, ?, ?, ?, ?, ?, 0)',
            [$this->providerId, $this->pid, 'Office Visit', self::DATE, $startTime, $status]
        );
    }
}
