<?php

/**
 * Database tests for filing a questionnaire that is in no encounter, such as one a patient
 * answered in the portal, into one of the patient's encounters.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Questionnaire;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Questionnaire\QuestionnaireEncounterFiling;
use PHPUnit\Framework\TestCase;

class QuestionnaireEncounterFilingDatabaseTest extends TestCase
{
    private const PUBPID = 'phpunit-questionnaire-encounter-filing';

    private int $pid;
    private QuestionnaireEncounterFiling $filing;

    /** @var list<int> encounter numbers these tests created, some for no test patient */
    private array $encounters = [];

    protected function setUp(): void
    {
        $this->removeTestRows();
        $patient = (new PatientService())->insert([
            'DOB' => '1975-04-05',
            'fname' => 'Filed',
            'lname' => 'Questionnaire',
            'sex' => 'Male',
            'pubpid' => self::PUBPID,
        ]);
        $this->assertTrue($patient->isValid(), 'test patient');
        $ids = $patient->getFirstDataResult();
        $this->assertIsArray($ids);
        $pid = self::id($ids['pid'] ?? null);
        $this->assertNotNull($pid, 'test patient pid');
        $this->pid = $pid;
        $this->filing = new QuestionnaireEncounterFiling();
    }

    protected function tearDown(): void
    {
        $this->removeTestRows();
    }

    private function removeTestRows(): void
    {
        foreach ($this->encounters as $encounter) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `uuid_registry` WHERE `uuid` IN (SELECT `uuid` FROM `form_encounter` WHERE `encounter` = ?)',
                [$encounter]
            );
            QueryUtils::sqlStatementThrowException('DELETE FROM `form_encounter` WHERE `encounter` = ?', [$encounter]);
        }
        $this->encounters = [];
        $pids = QueryUtils::fetchTableColumn('SELECT `pid` FROM `patient_data` WHERE `pubpid` = ?', 'pid', [self::PUBPID]);
        foreach ($pids as $pid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM `form_questionnaire_assessments` WHERE `pid` = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `pid` = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `questionnaire_response` WHERE `patient_id` = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `uuid_registry` WHERE `uuid` IN (SELECT `uuid` FROM `patient_data` WHERE `pid` = ?)', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM `patient_data` WHERE `pid` = ?', [$pid]);
        }
    }

    public function testAFormInNoEncounterIsFiledWithItsResponse(): void
    {
        $visit = $this->encounter('2026-11-20', 'Annual visit');
        $formId = $this->portalForm('resp-filed');

        $this->filing->fileForm($formId, $this->pid, $visit);

        $this->assertSame($visit, $this->formEncounter($formId));
        $this->assertSame($visit, $this->responseEncounter('resp-filed'));
    }

    public function testAnEncounterWithoutAUuidIsGivenOneWhenAFormIsFiledInIt(): void
    {
        $visit = $this->encounter('2026-11-20', 'Annual visit');
        QueryUtils::sqlStatementThrowException('UPDATE `form_encounter` SET `uuid` = NULL WHERE `encounter` = ?', [$visit]);
        $formId = $this->portalForm('resp-no-uuid');

        $this->filing->fileForm($formId, $this->pid, $visit);

        $uuid = QueryUtils::fetchSingleValue('SELECT `uuid` FROM `form_encounter` WHERE `encounter` = ?', 'uuid', [$visit]);
        $this->assertIsString($uuid);
        $this->assertSame(16, strlen($uuid));
        $this->assertSame($visit, $this->formEncounter($formId));
    }

    /**
     * Another patient's encounter, a form already in an encounter, or another patient's form is
     * refused, and nothing changes.
     */
    public function testWhatCannotBeFiledIsRefusedAndNothingChanges(): void
    {
        $own = $this->encounter('2026-11-20', 'Annual visit');
        $other = $this->encounter('2026-11-21', 'Someone else', $this->pid + 1000000);
        $waiting = $this->portalForm('resp-waiting');
        $filed = $this->portalForm('resp-in-visit', $own);

        $attempts = [
            "another patient's encounter" => [$waiting, $this->pid, $other],
            'a form already in an encounter' => [$filed, $this->pid, $own],
            "another patient's form" => [$waiting, $this->pid + 1000000, $other],
        ];
        foreach ($attempts as $label => [$formId, $pid, $encounter]) {
            try {
                $this->filing->fileForm($formId, $pid, $encounter);
                $this->fail('Expected a refusal: ' . $label);
            } catch (\InvalidArgumentException) {
                // expected
            }
        }

        $this->assertSame(0, $this->formEncounter($waiting));
        $this->assertSame(0, $this->responseEncounter('resp-waiting'));
        $this->assertSame($own, $this->formEncounter($filed));
    }

    public function testTheLatestEncountersAreListedNewestFirst(): void
    {
        $older = $this->encounter('2026-10-01', "Knee pain\nleft side");
        $newer = $this->encounter('2026-11-20', str_repeat('Long reason ', 10));
        $this->encounter('2026-11-22', 'Someone else', $this->pid + 1000000);

        $choices = $this->filing->latestEncounters($this->pid);

        $this->assertSame([$newer, $older], array_column($choices, 'encounter'));
        $this->assertSame('2026-10-01', $choices[1]['date']);
        $this->assertSame('Knee pain', $choices[1]['reason'], 'the first line of the reason');
        $this->assertStringEndsWith('…', $choices[0]['reason'], 'a long reason is cut short');
        $this->assertTrue($this->filing->isPatientEncounter($this->pid, $older));
        $this->assertFalse($this->filing->isPatientEncounter($this->pid + 1000000, $older));
    }

    private function encounter(string $date, string $reason, ?int $pid = null): int
    {
        $encounter = QueryUtils::generateId();
        $uuid = (new UuidRegistry(['table_name' => 'form_encounter']))->createUuid();
        QueryUtils::sqlInsert(
            'INSERT INTO `form_encounter` (`uuid`, `date`, `reason`, `pid`, `encounter`) VALUES (?, ?, ?, ?, ?)',
            [$uuid, $date . ' 09:00:00', $reason, $pid ?? $this->pid, $encounter]
        );
        $this->encounters[] = $encounter;

        return $encounter;
    }

    /**
     * A Questionnaire Assessment form and its response, as a portal document saves them.
     *
     * @return int the form_questionnaire_assessments id
     */
    private function portalForm(string $responseId, int $encounter = 0): int
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `questionnaire_response` (`response_id`, `questionnaire_name`, `patient_id`, `encounter`, `status`)'
            . " VALUES (?, 'PHPUnit filing', ?, ?, 'completed')",
            [$responseId, $this->pid, $encounter]
        );
        $formId = QueryUtils::sqlInsert(
            "INSERT INTO `form_questionnaire_assessments` (`response_id`, `pid`, `form_name`) VALUES (?, ?, 'PHPUnit filing')",
            [$responseId, $this->pid]
        );
        QueryUtils::sqlInsert(
            "INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `formdir`, `deleted`)"
            . " VALUES (NOW(), ?, 'PHPUnit filing', ?, ?, 'admin', 'questionnaire_assessments', 0)",
            [$encounter, $formId, $this->pid]
        );

        return $formId;
    }

    private function formEncounter(int $formId): ?int
    {
        $value = QueryUtils::fetchSingleValue(
            "SELECT `encounter` FROM `forms` WHERE `formdir` = 'questionnaire_assessments' AND `form_id` = ? AND `pid` = ?",
            'encounter',
            [$formId, $this->pid]
        );

        return self::id($value);
    }

    private function responseEncounter(string $responseId): ?int
    {
        $value = QueryUtils::fetchSingleValue(
            'SELECT `encounter` FROM `questionnaire_response` WHERE `response_id` = ?',
            'encounter',
            [$responseId]
        );

        return self::id($value);
    }

    /**
     * A database id: a native integer or a string of decimal digits, nothing else.
     */
    private static function id(mixed $value): ?int
    {
        return is_int($value) ? $value : (is_string($value) && ctype_digit($value) ? intval($value) : null);
    }
}
