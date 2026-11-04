<?php

/**
 * Problems, concerns and allergies read from the database as the eCQMs read them: one marked
 * Entered in Error or Refuted in its Verification Status is left out, by DiagnosisService,
 * SymptomService, AllergyIntoleranceService and the diagnoses EncounterService joins; any other
 * status, the column's default (empty) included, is kept. A visit is one encounter, carrying the
 * diagnoses of every problem linked to it that counts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Qdm;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\DiagnosisComponent;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\QdmRequestOne;
use OpenEMR\Services\Qdm\Services\AbstractQdmService;
use OpenEMR\Services\Qdm\Services\AllergyIntoleranceService;
use OpenEMR\Services\Qdm\Services\DiagnosisService;
use OpenEMR\Services\Qdm\Services\EncounterService;
use OpenEMR\Services\Qdm\Services\SymptomService;
use PHPUnit\Framework\TestCase;

class ListEntryServicesDatabaseTest extends TestCase
{
    // Synthetic patients; no patient_data rows are needed, and every row made here is removed.
    private const PID = 990615;
    private const OTHER_PID = 990622;
    private const ENCOUNTER_KEPT = 99061501;
    private const ENCOUNTER_REFUTED = 99061502;
    private const ENCOUNTER_OTHER_PATIENT = 99062201;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patient before the test');
    }

    protected function tearDown(): void
    {
        $pids = [self::PID, self::OTHER_PID];
        QueryUtils::sqlStatementThrowException('DELETE FROM `issue_encounter` WHERE `pid` IN (?, ?)', $pids);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_encounter` WHERE `pid` IN (?, ?)', $pids);
        QueryUtils::sqlStatementThrowException('DELETE FROM `lists` WHERE `pid` IN (?, ?)', $pids);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testAProblemEnteredInErrorOrRefutedIsNotADiagnosis(): void
    {
        $this->entry('medical_problem', 'diagnosis', 'ICD10:I11', '');
        $this->entry('medical_problem', 'diagnosis', 'ICD10:I12', 'confirmed');
        $this->entry('medical_problem', 'diagnosis', 'ICD10:I13', 'unconfirmed');
        $this->entry('medical_problem', 'diagnosis', 'ICD10:I15', 'refuted');
        $this->entry('medical_problem', 'diagnosis', 'ICD10:I16', 'entered-in-error');

        $this->assertSame(
            ['ICD10:I11', 'ICD10:I12', 'ICD10:I13'],
            $this->diagnoses(new DiagnosisService(new QdmRequestOne((string)self::PID), new CodeTypesService())),
            'Refuted and Entered in Error left out; empty (the default), Confirmed and Unconfirmed kept',
        );
    }

    public function testAConcernEnteredInErrorIsNotASymptom(): void
    {
        $this->entry('medical_problem', 'concern', 'ICD10:R51.9', 'confirmed');
        $this->entry('medical_problem', 'concern', 'ICD10:R53.83', 'entered-in-error');
        $this->entry('medical_problem', 'concern', 'ICD10:R05.9', 'refuted');

        $this->assertSame(
            ['ICD10:R51.9'],
            $this->diagnoses(new SymptomService(new QdmRequestOne((string)self::PID), new CodeTypesService())),
        );
    }

    public function testAnAllergyEnteredInErrorOrRefutedIsNotRead(): void
    {
        $this->entry('allergy', '', 'RXCUI:7980', '');
        $this->entry('allergy', '', 'RXCUI:2670', 'confirmed');
        $this->entry('allergy', '', 'RXCUI:1191', 'refuted');
        $this->entry('allergy', '', 'RXCUI:7052', 'entered-in-error');

        $this->assertSame(
            ['RXCUI:2670', 'RXCUI:7980'],
            $this->diagnoses(new AllergyIntoleranceService(new QdmRequestOne((string)self::PID), new CodeTypesService())),
        );
    }

    public function testAVisitKeepsItsOwnDiagnosesAndLosesOnlyTheWrongOnes(): void
    {
        // A visit linked to a confirmed and an erroneous problem; another linked only to a refuted one.
        $kept = $this->entry('medical_problem', 'diagnosis', 'ICD10:I10', 'confirmed');
        $wrong = $this->entry('medical_problem', 'diagnosis', 'ICD10:E11.9', 'entered-in-error');
        $refuted = $this->entry('medical_problem', 'diagnosis', 'ICD10:J45.909', 'refuted');
        $this->visit(self::ENCOUNTER_KEPT, [$kept, $wrong]);
        $this->visit(self::ENCOUNTER_REFUTED, [$refuted]);
        // Control: another patient's visit is not this patient's, whatever the query's shape.
        $this->visit(self::ENCOUNTER_OTHER_PATIENT, [], self::OTHER_PID);

        $result = (new EncounterService(new QdmRequestOne((string)self::PID), new CodeTypesService()))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $rows = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $encounter = $row['encounter'] ?? null;
            $this->assertIsNumeric($encounter);
            $diagnosis = $row['diagnosis'] ?? null;
            $this->assertTrue($diagnosis === null || is_string($diagnosis));
            $rows[] = $encounter . ' ' . ($diagnosis ?? '-');
        }
        sort($rows);

        $this->assertSame(
            [self::ENCOUNTER_KEPT . ' ICD10:I10', self::ENCOUNTER_REFUTED . ' -'],
            $rows,
            'each visit once, with only the problems that count; a visit is never dropped for its problems',
        );
    }

    public function testAVisitLinkedToSeveralProblemsIsOneEncounterWithEachDiagnosis(): void
    {
        // Two problems that count, the second coded twice, and one entered in error, all linked to one visit.
        $first = $this->entry('medical_problem', 'diagnosis', 'ICD10:I10', 'confirmed');
        $second = $this->entry('medical_problem', 'diagnosis', 'ICD10:E11.9;SNOMED-CT:44054006', '');
        $wrong = $this->entry('medical_problem', 'diagnosis', 'ICD10:J45.909', 'entered-in-error');
        $this->visit(self::ENCOUNTER_KEPT, [$first, $second, $wrong]);

        $service = new EncounterService(new QdmRequestOne((string)self::PID), new CodeTypesService());
        $result = $service->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $encounters = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $encounters[] = $service->makeQdmModel(new QdmRecord($row, self::PID, 10000));
        }

        $this->assertCount(1, $encounters, 'the visit once, not once per linked problem');
        $diagnoses = $encounters[0]->diagnoses;
        $this->assertIsArray($diagnoses);
        $codes = [];
        foreach ($diagnoses as $diagnosis) {
            $this->assertInstanceOf(DiagnosisComponent::class, $diagnosis);
            $this->assertInstanceOf(Code::class, $diagnosis->code);
            $codes[] = $diagnosis->code->code;
        }
        $this->assertSame(['I10', 'E11.9', '44054006'], $codes, 'every diagnosis that counts, on the one encounter');
    }

    public function testAnotherPatientsProblemLinkedUnderTheVisitsNumberIsNotItsDiagnosis(): void
    {
        // A link names its patient as well as the problem and the encounter; one that names another
        // patient under this visit's encounter number doesn't give this visit that patient's problem.
        $own = $this->entry('medical_problem', 'diagnosis', 'ICD10:I10', 'confirmed');
        $others = $this->entry('medical_problem', 'diagnosis', 'ICD10:E11.9', 'confirmed', self::OTHER_PID);
        $this->visit(self::ENCOUNTER_KEPT, [$own]);
        QueryUtils::sqlInsert(
            'INSERT INTO `issue_encounter` (`pid`, `list_id`, `encounter`, `resolved`) VALUES (?, ?, ?, 0)',
            [self::OTHER_PID, $others, self::ENCOUNTER_KEPT],
        );

        $result = (new EncounterService(new QdmRequestOne((string)self::PID), new CodeTypesService()))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $diagnoses = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $diagnoses[] = $row['diagnosis'] ?? null;
        }

        $this->assertSame(['ICD10:I10'], $diagnoses);
    }

    public function testAnotherPatientsProblemLinkedToTheVisitIsNotItsDiagnosis(): void
    {
        // A link under the visit's own patient and encounter that names another patient's problem (its
        // writers take a posted issue id without checking whose it is) doesn't give the visit that problem.
        $own = $this->entry('medical_problem', 'diagnosis', 'ICD10:I10', 'confirmed');
        $others = $this->entry('medical_problem', 'diagnosis', 'ICD10:E11.9', 'confirmed', self::OTHER_PID);
        $this->visit(self::ENCOUNTER_KEPT, [$own, $others]);

        $result = (new EncounterService(new QdmRequestOne((string)self::PID), new CodeTypesService()))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $diagnoses = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $diagnoses[] = $row['diagnosis'] ?? null;
        }

        $this->assertSame(['ICD10:I10'], $diagnoses);
    }

    /** @return list<string> the diagnosis column of each row the service reads, sorted */
    private function diagnoses(AbstractQdmService $service): array
    {
        $result = $service->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $values = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $value = $row['diagnosis'] ?? null;
            $this->assertIsString($value);
            $values[] = $value;
        }
        sort($values);
        return $values;
    }

    /** @return int the new lists row's id */
    private function entry(string $type, string $subtype, string $code, string $verification, int $pid = self::PID): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO `lists` (`date`, `type`, `subtype`, `title`, `begdate`, `diagnosis`, `activity`, `pid`, `user`, `groupname`, `verification`)"
            . " VALUES ('2026-03-02 09:00:00', ?, ?, 'test entry', '2026-01-05 00:00:00', ?, 1, ?, 'test', 'Default', ?)",
            [$type, $subtype, $code, $pid, $verification],
        );
    }

    /** @param list<int> $issues */
    private function visit(int $encounter, array $issues, int $pid = self::PID): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `form_encounter` (`date`, `reason`, `pid`, `encounter`, `pc_catid`) VALUES ('2026-03-10 09:00:00', 'test visit', ?, ?, 5)",
            [$pid, $encounter],
        );
        foreach ($issues as $issue) {
            QueryUtils::sqlInsert(
                'INSERT INTO `issue_encounter` (`pid`, `list_id`, `encounter`, `resolved`) VALUES (?, ?, ?, 0)',
                [$pid, $issue, $encounter],
            );
        }
    }

    private function rowsLeft(): int
    {
        $count = 0;
        foreach (['lists', 'form_encounter', 'issue_encounter'] as $table) {
            $n = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM `$table` WHERE `pid` IN (?, ?)", 'n', [self::PID, self::OTHER_PID]);
            $this->assertIsNumeric($n);
            $count += (int)$n;
        }
        return $count;
    }
}
