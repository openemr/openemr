<?php

/**
 * Problems, concerns and allergies read from the database as the eCQMs read them: one marked
 * Entered in Error or Refuted in its Verification Status is left out, by DiagnosisService,
 * SymptomService, AllergyIntoleranceService and the diagnoses EncounterService joins; any other
 * status, the column's default (empty) included, is kept.
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
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\QdmRequestOne;
use OpenEMR\Services\Qdm\Services\AbstractQdmService;
use OpenEMR\Services\Qdm\Services\AllergyIntoleranceService;
use OpenEMR\Services\Qdm\Services\DiagnosisService;
use OpenEMR\Services\Qdm\Services\EncounterService;
use OpenEMR\Services\Qdm\Services\SymptomService;
use PHPUnit\Framework\TestCase;

class ListEntryServicesDatabaseTest extends TestCase
{
    // A synthetic patient; no patient_data row is needed, and every row made here is removed.
    private const PID = 990615;
    private const ENCOUNTER_KEPT = 99061501;
    private const ENCOUNTER_REFUTED = 99061502;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patient before the test');
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `issue_encounter` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_encounter` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `lists` WHERE `pid` = ?', [self::PID]);
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
    private function entry(string $type, string $subtype, string $code, string $verification): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO `lists` (`date`, `type`, `subtype`, `title`, `begdate`, `diagnosis`, `activity`, `pid`, `user`, `groupname`, `verification`)"
            . " VALUES ('2026-03-02 09:00:00', ?, ?, 'test entry', '2026-01-05 00:00:00', ?, 1, ?, 'test', 'Default', ?)",
            [$type, $subtype, $code, self::PID, $verification],
        );
    }

    /** @param list<int> $issues */
    private function visit(int $encounter, array $issues): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `form_encounter` (`date`, `reason`, `pid`, `encounter`, `pc_catid`) VALUES ('2026-03-10 09:00:00', 'test visit', ?, ?, 5)",
            [self::PID, $encounter],
        );
        foreach ($issues as $issue) {
            QueryUtils::sqlInsert(
                'INSERT INTO `issue_encounter` (`pid`, `list_id`, `encounter`, `resolved`) VALUES (?, ?, ?, 0)',
                [self::PID, $issue, $encounter],
            );
        }
    }

    private function rowsLeft(): int
    {
        $count = 0;
        foreach (['lists', 'form_encounter', 'issue_encounter'] as $table) {
            $n = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM `$table` WHERE `pid` = ?", 'n', [self::PID]);
            $this->assertIsNumeric($n);
            $count += (int)$n;
        }
        return $count;
    }
}
