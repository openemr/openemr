<?php

/**
 * Importing a patient's vitals entry again (same external_id) updates the stored row, and keeps
 * each stored reading the new document doesn't carry rather than clearing it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Cda;

use Carecoordination\Model\CarecoordinationTable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Cda\CdaTemplateImportDispose;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CdaImportVitalsAgainTest extends TestCase
{
    // A synthetic patient; no patient_data row is needed, and every row made here is removed.
    private const PID = 990641;
    private const NPI = '9990641001';
    private const ORGANIZATION = 'Vitals Again Test Clinic';

    private CdaTemplateImportDispose $dispose;
    private CarecoordinationTable&MockObject $table;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patient before the test');
        // An existing provider and organization, so importing the encounter adds no users of its own.
        QueryUtils::sqlInsert("INSERT INTO `users` (`username`, `fname`, `lname`, `npi`, `active`) VALUES ('', 'Again', 'Provider', ?, 1)", [self::NPI]);
        QueryUtils::sqlInsert("INSERT INTO `users` (`username`, `organization`, `active`, `abook_type`) VALUES ('', ?, 1, 'external_org')", [self::ORGANIZATION]);
        $this->dispose = new CdaTemplateImportDispose();
        $this->table = $this->createMock(CarecoordinationTable::class);
        $this->dispose->InsertEncounter([$this->encounter()], self::PID, $this->table, 0);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_vital_details` WHERE `form_id` IN (SELECT `id` FROM `form_vitals` WHERE `pid` = ?)', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_vitals` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_encounter` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `external_encounters` WHERE `ee_pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `users` WHERE `npi` = ? OR (`abook_type` = ? AND `organization` = ?)', [self::NPI, 'external_org', self::ORGANIZATION]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testADocumentWithoutAReadingKeepsTheStoredOne(): void
    {
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);
        // The same entry again with only blood pressure, as the import hands it over: null for each reading it lacks.
        $again = array_fill_keys(['height', 'weight', 'temperature', 'pulse', 'respiration', 'head_circ', 'oxygen_saturation', 'BMI'], null);
        $this->dispose->InsertVitals([['extension' => 'vitals-again-1', 'date' => '20260301093000', 'bps' => '130', 'bpd' => '85'] + $again], self::PID, $this->table, 0);

        $row = $this->stored();
        $this->assertSame(['130', '85'], [$row['bps'], $row['bpd']], 'the readings the document carries are updated');
        $this->assertSame(
            ['65.000000', '150.000000', '98.600000', '70.000000', '16.000000', '98.00', '25.000000'],
            [$row['height'], $row['weight'], $row['temperature'], $row['pulse'], $row['respiration'], $row['oxygen_saturation'], $row['BMI']],
            'the readings it lacks keep their stored values'
        );
        $this->assertSame(1, $this->vitalsForms(), 'the reading is listed once in its encounter');
    }

    public function testApprovingAReviewedDocumentWithoutAReadingKeepsTheStoredOne(): void
    {
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);
        // As the review-and-approve form sends it: text fields, blank for a reading the document lacks, and no BMI.
        $this->dispose->InsertVitals([[
            'extension' => 'vitals-again-1',
            'date' => '01/03/2026',
            'temperature' => '',
            'bpd' => '85',
            'bps' => '130',
            'head_circ' => '',
            'pulse' => '72',
            'height' => '',
            'oxygen_saturation' => '',
            'respiration' => '',
            'weight' => '',
        ]], self::PID, $this->table, 1);

        $row = $this->stored();
        $this->assertSame(['130', '85', '72.000000'], [$row['bps'], $row['bpd'], $row['pulse']]);
        $this->assertSame(
            ['65.000000', '150.000000', '98.600000', '16.000000', '98.00', '25.000000'],
            [$row['height'], $row['weight'], $row['temperature'], $row['respiration'], $row['oxygen_saturation'], $row['BMI']]
        );
        $this->assertSame(1, $this->vitalsForms(), 'the reading is listed once in its encounter');
    }

    public function testANewWeightWithoutABmiClearsTheStoredBmi(): void
    {
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);
        // The review-and-approve form never sends BMI; the stored one came from the old weight.
        $this->dispose->InsertVitals([[
            'extension' => 'vitals-again-1',
            'date' => '01/03/2026',
            'bps' => '130',
            'bpd' => '85',
            'weight' => '160',
            'height' => '',
        ]], self::PID, $this->table, 1);

        $row = $this->stored();
        $this->assertSame(['160.000000', '65.000000'], [$row['weight'], $row['height']]);
        $this->assertNull($row['BMI']);
    }

    public function testAReasonImportedAgainIsStoredOnce(): void
    {
        $reason = ['reason_code' => 'SNOMED-CT:183932001', 'reason_code_text' => 'Procedure contraindicated', 'reason_status' => 'negated', 'vital_column' => 'bps'];
        $this->dispose->InsertVitals([$reason + $this->vitals()], self::PID, $this->table, 0);
        $this->dispose->InsertVitals([$reason + $this->vitals()], self::PID, $this->table, 0);

        $count = QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS n FROM `form_vital_details` WHERE `form_id` IN (SELECT `id` FROM `form_vitals` WHERE `pid` = ?)',
            'n',
            [self::PID]
        );
        $this->assertSame(1, (int)self::number($count));
        $this->assertSame(1, $this->vitalsForms());
    }

    public function testAReasonImportedAgainWithANewStatusIsUpdated(): void
    {
        $reason = ['reason_code' => 'SNOMED-CT:183932001', 'reason_code_text' => 'Procedure contraindicated', 'reason_status' => 'negated', 'vital_column' => 'bps'];
        $this->dispose->InsertVitals([$reason + $this->vitals()], self::PID, $this->table, 0);
        $this->dispose->InsertVitals([['reason_status' => 'active', 'reason_code_text' => 'Procedure not done'] + $reason + $this->vitals()], self::PID, $this->table, 0);

        $rows = QueryUtils::fetchRecords(
            'SELECT `reason_description`, `reason_status` FROM `form_vital_details` WHERE `form_id` IN (SELECT `id` FROM `form_vitals` WHERE `pid` = ?)',
            [self::PID]
        );
        $this->assertSame([['reason_description' => 'Procedure not done', 'reason_status' => 'active']], $rows);
    }

    public function testAReadingImportedAgainStaysInItsEncounter(): void
    {
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);
        $filed = $this->vitalsEncounters();
        // A visit added since, closer to the reading's time; the reading's first encounter may be signed.
        $this->dispose->InsertEncounter([[
            'extension' => 'visit-again-2',
            'date' => '20260301093000',
            'provider_npi' => self::NPI,
            'provider_name' => 'Again',
            'provider_family' => 'Provider',
            'represented_organization_name' => self::ORGANIZATION,
        ]], self::PID, $this->table, 0);
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);

        $this->assertCount(1, $filed);
        $this->assertSame($filed, $this->vitalsEncounters());
    }

    public function testAReadingImportedAgainWithItsEncounterStaysInAnEncounter(): void
    {
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);
        // The same document again: both import paths take its encounter before its vitals, and
        // an encounter imported again gets a new encounter number.
        $this->dispose->InsertEncounter([$this->encounter()], self::PID, $this->table, 0);
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);

        $listed = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM `forms` AS f JOIN `form_encounter` AS fe ON fe.`encounter` = f.`encounter` AND fe.`pid` = f.`pid`"
            . " WHERE f.`pid` = ? AND f.`formdir` = 'vitals' AND f.`deleted` = 0",
            'n',
            [self::PID]
        );
        $this->assertSame(1, (int)self::number($listed), 'the reading is listed in an encounter that exists');
    }

    public function testAReadingWhoseFormWasDeletedIsListedAgain(): void
    {
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);
        QueryUtils::sqlStatementThrowException("UPDATE `forms` SET `deleted` = 1 WHERE `pid` = ? AND `formdir` = 'vitals'", [self::PID]);
        $this->dispose->InsertVitals([$this->vitals()], self::PID, $this->table, 0);

        $listed = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM `forms` WHERE `pid` = ? AND `formdir` = 'vitals' AND `deleted` = 0",
            'n',
            [self::PID]
        );
        $this->assertSame(1, (int)self::number($listed));
    }

    /**
     * The document's encounter, as the parser hands it to InsertEncounter.
     *
     * @return array<string, string>
     */
    private function encounter(): array
    {
        return [
            'extension' => 'visit-again-1',
            'date' => '20260301090000',
            'provider_npi' => self::NPI,
            'provider_name' => 'Again',
            'provider_family' => 'Provider',
            'represented_organization_name' => self::ORGANIZATION,
        ];
    }

    /**
     * The entry's first import, as the parser hands it to InsertVitals.
     *
     * @return array<string, ?string>
     */
    private function vitals(): array
    {
        return [
            'extension' => 'vitals-again-1',
            'date' => '20260301093000',
            'bps' => '120',
            'bpd' => '80',
            'height' => '65',
            'weight' => '150',
            'temperature' => '98.6',
            'pulse' => '70',
            'respiration' => '16',
            'head_circ' => null,
            'oxygen_saturation' => '98',
            'BMI' => '25',
        ];
    }

    /** @return array<mixed> */
    private function stored(): array
    {
        $rows = QueryUtils::fetchRecords('SELECT * FROM `form_vitals` WHERE `pid` = ?', [self::PID]);
        $this->assertCount(1, $rows, 'the second import updates the first one\'s row');
        return $rows[0];
    }

    private function vitalsForms(): int
    {
        return (int)self::number(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM `forms` WHERE `pid` = ? AND `formdir` = 'vitals'",
            'n',
            [self::PID]
        ));
    }

    /** @return list<mixed> */
    private function vitalsEncounters(): array
    {
        return array_column(
            QueryUtils::fetchRecords("SELECT `encounter` FROM `forms` WHERE `pid` = ? AND `formdir` = 'vitals' ORDER BY `id`", [self::PID]),
            'encounter'
        );
    }

    private function rowsLeft(): int
    {
        $count = 0;
        foreach (['forms' => 'pid', 'form_vitals' => 'pid', 'form_encounter' => 'pid', 'external_encounters' => 'ee_pid'] as $table => $column) {
            $count += (int)self::number(QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM `$table` WHERE `$column` = ?", 'n', [self::PID]));
        }
        $count += (int)self::number(QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS n FROM `form_vital_details` WHERE `form_id` IN (SELECT `id` FROM `form_vitals` WHERE `pid` = ?)',
            'n',
            [self::PID]
        ));
        return $count + (int)self::number(QueryUtils::fetchSingleValue(
            'SELECT COUNT(*) AS n FROM `users` WHERE `npi` = ? OR (`abook_type` = ? AND `organization` = ?)',
            'n',
            [self::NPI, 'external_org', self::ORGANIZATION]
        ));
    }

    private static function number(mixed $value): int|float|string
    {
        self::assertIsNumeric($value);
        return $value;
    }
}
