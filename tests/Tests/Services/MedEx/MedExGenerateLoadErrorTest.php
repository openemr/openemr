<?php

/**
 * Tests that MedEx Events::generate() records a failed loadAppts upload in its results.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\MedEx;

use MedExApi\Events;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/MedEx/API.php';

class MedExGenerateLoadErrorTest extends TestCase
{
    private const TEST_PID = 9914256;

    /** A facility id no real appointment uses, so the announcement matches only the fixture. */
    private const TEST_FACILITY = 99914256;

    private const APPT_DATE = '2001-02-03';

    protected function setUp(): void
    {
        $this->removeFixtures();
        QueryUtils::sqlInsert(
            'INSERT INTO openemr_postcalendar_events (pc_pid, pc_facility, pc_eventDate, pc_startTime, pc_recurrtype, pc_apptstatus, pc_multiple) VALUES (?, ?, ?, ?, 0, ?, 0)',
            [self::TEST_PID, self::TEST_FACILITY, self::APPT_DATE, '09:00:00', '-']
        );
    }

    protected function tearDown(): void
    {
        $this->removeFixtures();
    }

    #[Test]
    public function recordsTheErrorWhenMedExRejectsTheAppointments(): void
    {
        $responses = $this->generate(['error' => 'appointments rejected']);

        $this->assertIsArray($responses);
        $this->assertSame('appointments rejected', $responses['load_error'] ?? null);
        $this->assertSame(1, $responses['count_announcements'] ?? null);
        // a batch failure must not look like a login error, which disables the background service
        $this->assertArrayNotHasKey('error', $responses);
    }

    #[Test]
    public function doesNotReportAnEarlierRequestsErrorAsTheUploadError(): void
    {
        // e.g. left by process_deletes() when recall deletion failed earlier in generate()
        $responses = $this->generate([], 'recall deletion rejected');

        $this->assertIsArray($responses);
        $this->assertSame('MedEx did not accept the appointments', $responses['load_error'] ?? null);
    }

    #[Test]
    public function recordsNoErrorWhenMedExAcceptsTheAppointments(): void
    {
        $responses = $this->generate(['success' => 'ok']);

        $this->assertIsArray($responses);
        $this->assertArrayNotHasKey('load_error', $responses);
        $this->assertSame(1, $responses['count_announcements'] ?? null);
    }

    /**
     * Runs generate() with one announcement campaign that matches the fixture appointment,
     * failing on any PHP warning or notice it raises; phpunit.xml only reports those.
     *
     * @param array<string, string> $loadApptsReply
     */
    private function generate(array $loadApptsReply, string $earlierError = ''): mixed
    {
        $curl = new class ($loadApptsReply) {
            /**
             * @param array<string, string> $reply
             */
            public function __construct(private readonly array $reply)
            {
            }

            public function setUrl(string $url): void
            {
            }

            public function setData(mixed $data): void
            {
            }

            public function makeRequest(): void
            {
            }

            /**
             * @return array<string, string>
             */
            public function getResponse(): array
            {
                return $this->reply;
            }
        };
        $medex = new class ($curl) {
            public function __construct(public object $curl)
            {
            }

            public function getUrl(string $path): string
            {
                return 'https://medex.invalid/' . $path;
            }

            /**
             * Every appointment is reachable by SMS.
             *
             * @return array{string, string}
             */
            public function checkModality(mixed $event, mixed $appt, mixed $icon = ''): array
            {
                return ['', '8025550100'];
            }
        };

        $announcement = [
            'M_group' => 'ANNOUNCE',
            'M_type' => 'SMS',
            'C_UID' => '1',
            'E_language' => '',
            'start_date' => '2001-01-01',
            'appts_start' => self::APPT_DATE,
            'appts_end' => '',
            'facilities' => (string) self::TEST_FACILITY,
        ];

        $raised = [];
        set_error_handler(function (int $errno, string $message, string $file, int $line) use (&$raised): bool {
            $raised[] = $message . ' at ' . basename($file) . ':' . $line;
            return true;
        });
        $events = new Events($medex);
        $events->lastError = $earlierError;
        try {
            $responses = $events->generate('phpunit-token', [$announcement]);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $raised);
        return $responses;
    }

    private function removeFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM openemr_postcalendar_events WHERE pc_pid = ? AND pc_facility = ?',
            [self::TEST_PID, self::TEST_FACILITY]
        );
    }
}
