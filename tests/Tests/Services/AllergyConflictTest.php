<?php

/**
 * The drug-allergy alert, allergy_conflict(): an issue saved with no status (activity NULL, as the CCR import saves
 * allergies) is on the list; only activity 0 takes it off.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../library/clinical_rules.php';

final class AllergyConflictTest extends TestCase
{
    private int $pid;
    private string $title;
    private bool $previousAlertLog;

    protected function setUp(): void
    {
        // A new pid, so tearDown() removes only this test's rows (MAX is NULL on an empty table).
        $maxPid = QueryUtils::fetchSingleValue("SELECT MAX(`pid`) AS `max` FROM `patient_data`", 'max');
        $this->pid = (is_numeric($maxPid) ? (int) $maxPid : 0) + 1;
        $this->title = 'Phpunit Allergen ' . $this->pid;
        QueryUtils::sqlInsert(
            "INSERT INTO `patient_data` SET `pid` = ?, `uuid` = ?, `pubpid` = ?, `fname` = 'Allergy', "
            . "`lname` = 'Conflict', `DOB` = '1980-01-01', `sex` = 'Female'",
            [$this->pid, UuidRegistry::getRegistryForTable('patient_data')->createUuid(), 'test-allergy-' . $this->pid]
        );

        // With the alert log on, the alert also writes clinical_rules_log; these tests read only its answer.
        $globals = OEGlobalsBag::getInstance();
        $this->previousAlertLog = $globals->getBoolean('enable_alert_log');
        $globals->set('enable_alert_log', false);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('enable_alert_log', $this->previousAlertLog);

        $uuid = QueryUtils::fetchSingleValue("SELECT `uuid` FROM `patient_data` WHERE `pid` = ?", 'uuid', [$this->pid]);
        if ($uuid !== null) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `uuid_registry` WHERE `uuid` = ?", [$uuid]);
        }
        QueryUtils::sqlStatementThrowException("DELETE FROM `clinical_rules_log` WHERE `pid` = ?", [$this->pid]);
        QueryUtils::sqlStatementThrowException("DELETE FROM `prescriptions` WHERE `patient_id` = ?", [$this->pid]);
        QueryUtils::sqlStatementThrowException("DELETE FROM `lists` WHERE `pid` = ?", [$this->pid]);
        QueryUtils::sqlStatementThrowException("DELETE FROM `patient_data` WHERE `pid` = ?", [$this->pid]);
    }

    /**
     * @return iterable<string, array{?int, bool}> the issue's activity, and whether the alert reports the conflict
     */
    public static function statuses(): iterable
    {
        yield 'no status (NULL)' => [null, true];
        yield 'on the list (1)' => [1, true];
        yield 'taken off the list (0)' => [0, false];
    }

    #[Test]
    #[DataProvider('statuses')]
    public function anAllergyConflictsWithAPrescriptionUnlessTakenOffTheList(?int $activity, bool $alert): void
    {
        $this->saveIssue('allergy', $activity);
        QueryUtils::sqlInsert(
            "INSERT INTO `prescriptions` SET `patient_id` = ?, `drug` = ?, `active` = 1, `txDate` = CURDATE(), "
            . "`usage_category_title` = '', `request_intent_title` = ''",
            [$this->pid, $this->title]
        );

        $this->assertAlert($alert, allergy_conflict($this->pid, 'all', 'admin'));
    }

    #[Test]
    #[DataProvider('statuses')]
    public function aListMedicationConflictsWithAnAllergyUnlessTakenOffTheList(?int $activity, bool $alert): void
    {
        $this->saveIssue('allergy', 1);
        $this->saveIssue('medication', $activity);

        $this->assertAlert($alert, allergy_conflict($this->pid, 'all', 'admin'));
    }

    private function saveIssue(string $type, ?int $activity): void
    {
        $id = QueryUtils::sqlInsert(
            "INSERT INTO `lists` SET `pid` = ?, `type` = ?, `title` = ?, `date` = NOW(), `begdate` = '2025-01-01', "
            . "`activity` = ?",
            [$this->pid, $type, $this->title, $activity]
        );
        // The probe has to be able to fail: a NULL bound as '' would be saved as 0.
        $saved = QueryUtils::fetchSingleValue("SELECT `activity` FROM `lists` WHERE `id` = ?", 'activity', [$id]);
        $this->assertSame(
            $activity,
            is_numeric($saved) ? (int) $saved : $saved,
            "the $type was saved with the activity asked for"
        );
    }

    private function assertAlert(bool $alert, mixed $conflicts): void
    {
        if ($alert) {
            $this->assertSame([$this->title], $conflicts);
        } else {
            $this->assertFalse($conflicts);
        }
    }
}
