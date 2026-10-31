<?php

/**
 * Database tests for the Flow Board's appointment status colors.
 *
 * The Flow Board stopped with a TypeError on an appointment whose status had
 * no color set or was inactive: the lookup returned the status code itself,
 * and the board read ['color'] from that string. Seen when MedEx marked
 * appointments "AVM" (voice-confirmed) at a practice where that status was
 * inactive.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientTrackerService;
use PHPUnit\Framework\TestCase;

class PatientTrackerApptStatusSettingsTest extends TestCase
{
    /** @var array<string, array{notes: string, activity: int}> option_id => list entry */
    private const STATUSES = [
        'ZZA' => ['notes' => '#ABCDEF|15', 'activity' => 1],
        'ZZI' => ['notes' => '#CEFFBE|0', 'activity' => 0],
        'ZZN' => ['notes' => '', 'activity' => 1],
        'ZZC' => ['notes' => '#FF0000', 'activity' => 1],
        'ZZS' => ['notes' => ' #00FF00 | 5 ', 'activity' => 1],
        'ZZX' => ['notes' => '#0000FF|soon', 'activity' => 1],
    ];

    /** @var list<array<mixed>> list entries with the fixture ids that were there before the test */
    private array $saved = [];

    protected function setUp(): void
    {
        // Keep any real entries that happen to use the fixture ids, to put them back afterwards.
        $this->saved = QueryUtils::fetchRecords(
            "SELECT * FROM list_options WHERE list_id = 'apptstat' AND option_id IN ("
                . implode(', ', array_fill(0, count(self::STATUSES), '?')) . ")",
            array_keys(self::STATUSES)
        );
        $this->cleanUp();
        $seq = 9000;
        foreach (self::STATUSES as $id => $status) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO list_options (list_id, option_id, title, seq, notes, activity) VALUES ('apptstat', ?, ?, ?, ?, ?)",
                [$id, $id . ' test', $seq++, $status['notes'], $status['activity']]
            );
        }
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        foreach ($this->saved as $row) {
            $columns = array_filter(array_keys($row), is_string(...));
            if ($columns === []) {
                continue;
            }
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO list_options (`" . implode('`, `', $columns) . "`) VALUES ("
                    . implode(', ', array_fill(0, count($columns), '?')) . ")",
                array_values(array_intersect_key($row, array_flip($columns)))
            );
        }
    }

    public function testAnActiveStatusHasItsColorAndAlert(): void
    {
        $this->assertSame(['color' => 'ABCDEF', 'time_alert' => '15'], PatientTrackerService::collectApptStatusSettings('ZZA'));
    }

    public function testAnInactiveStatusStillHasItsColor(): void
    {
        $this->assertSame(['color' => 'CEFFBE', 'time_alert' => '0'], PatientTrackerService::collectApptStatusSettings('ZZI'));
    }

    public function testAStatusWithoutNotesHasNoColorOrAlert(): void
    {
        $this->assertSame(['color' => '', 'time_alert' => '0'], PatientTrackerService::collectApptStatusSettings('ZZN'));
    }

    public function testAnUnknownStatusHasNoColorOrAlert(): void
    {
        $this->assertSame(['color' => '', 'time_alert' => '0'], PatientTrackerService::collectApptStatusSettings('ZZ?'));
        $this->assertSame(['color' => '', 'time_alert' => '0'], PatientTrackerService::collectApptStatusSettings(''));
        $this->assertSame(['color' => '', 'time_alert' => '0'], PatientTrackerService::collectApptStatusSettings(null));
    }

    public function testAColorWithoutAnAlertTime(): void
    {
        $this->assertSame(['color' => 'FF0000', 'time_alert' => '0'], PatientTrackerService::collectApptStatusSettings('ZZC'));
    }

    public function testAnAlertTimeThatIsNotANumberIsNoAlert(): void
    {
        // The Flow Board Report raises an alert for any time_alert other than "0".
        $this->assertSame(['color' => '0000FF', 'time_alert' => '0'], PatientTrackerService::collectApptStatusSettings('ZZX'));
    }

    public function testSpacesAroundTheNotesAreIgnored(): void
    {
        $this->assertSame(['color' => '00FF00', 'time_alert' => '5'], PatientTrackerService::collectApptStatusSettings('ZZS'));
    }

    public function testTheFlowBoardHelperReturnsTheSameSettings(): void
    {
        require_once __DIR__ . '/../../../library/patient_tracker.inc.php';

        $this->assertSame(['color' => '', 'time_alert' => '0'], collectApptStatusSettings('ZZ?'));
        $this->assertSame(['color' => 'ABCDEF', 'time_alert' => '15'], collectApptStatusSettings('ZZA'));
    }

    private function cleanUp(): void
    {
        foreach (array_keys(self::STATUSES) as $id) {
            QueryUtils::sqlStatementThrowException("DELETE FROM list_options WHERE list_id = 'apptstat' AND option_id = ?", [$id]);
        }
    }
}
