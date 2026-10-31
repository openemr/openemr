<?php

/**
 * Database tests for the Flow Board's appointment status colours.
 *
 * The Flow Board stopped with a TypeError on an appointment whose status had
 * no colour set or was inactive: the lookup returned the status code itself,
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
    ];

    protected function setUp(): void
    {
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
    }

    public function testAnActiveStatusHasItsColourAndAlert(): void
    {
        $this->assertSame(['color' => 'ABCDEF', 'time_alert' => '15'], PatientTrackerService::collectApptStatusSettings('ZZA'));
    }

    public function testAnInactiveStatusStillHasItsColour(): void
    {
        $this->assertSame(['color' => 'CEFFBE', 'time_alert' => '0'], PatientTrackerService::collectApptStatusSettings('ZZI'));
    }

    public function testAStatusWithoutNotesHasNoColourOrAlert(): void
    {
        $this->assertSame(['color' => '', 'time_alert' => ''], PatientTrackerService::collectApptStatusSettings('ZZN'));
    }

    public function testAnUnknownStatusHasNoColourOrAlert(): void
    {
        $this->assertSame(['color' => '', 'time_alert' => ''], PatientTrackerService::collectApptStatusSettings('ZZ?'));
        $this->assertSame(['color' => '', 'time_alert' => ''], PatientTrackerService::collectApptStatusSettings(''));
        $this->assertSame(['color' => '', 'time_alert' => ''], PatientTrackerService::collectApptStatusSettings(null));
    }

    public function testAColourWithoutAnAlertTime(): void
    {
        $this->assertSame(['color' => 'FF0000', 'time_alert' => ''], PatientTrackerService::collectApptStatusSettings('ZZC'));
    }

    public function testSpacesAroundTheNotesAreIgnored(): void
    {
        $this->assertSame(['color' => '00FF00', 'time_alert' => '5'], PatientTrackerService::collectApptStatusSettings('ZZS'));
    }

    public function testTheFlowBoardHelperReturnsTheSameSettings(): void
    {
        require_once __DIR__ . '/../../../library/patient_tracker.inc.php';

        $this->assertSame(['color' => '', 'time_alert' => ''], collectApptStatusSettings('ZZ?'));
        $this->assertSame(['color' => 'ABCDEF', 'time_alert' => '15'], collectApptStatusSettings('ZZA'));
    }

    private function cleanUp(): void
    {
        foreach (array_keys(self::STATUSES) as $id) {
            QueryUtils::sqlStatementThrowException("DELETE FROM list_options WHERE list_id = 'apptstat' AND option_id = ?", [$id]);
        }
    }
}
