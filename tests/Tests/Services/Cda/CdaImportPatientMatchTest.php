<?php

/**
 * A C-CDA import updates only the importing patient's own records. Two documents from
 * different senders can carry the same entry id (OpenEMR's export builds it from the
 * site name and the form's id), so a vitals or encounter row found by that id alone can
 * belong to someone else.
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

class CdaImportPatientMatchTest extends TestCase
{
    // Synthetic patients; no patient_data rows are needed, and every row made here is removed.
    private const PID = 990631;
    private const OTHER_PID = 990632;
    private const NPI = '9990631001';
    private const ORGANIZATION = 'Patient Match Test Clinic';

    private CdaTemplateImportDispose $dispose;
    private CarecoordinationTable&MockObject $table;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patients before the test');
        // An existing provider and organization, so importing an encounter adds no users of its own.
        QueryUtils::sqlInsert("INSERT INTO `users` (`username`, `fname`, `lname`, `npi`, `active`) VALUES ('', 'Match', 'Provider', ?, 1)", [self::NPI]);
        QueryUtils::sqlInsert("INSERT INTO `users` (`username`, `organization`, `active`, `abook_type`) VALUES ('', ?, 1, 'external_org')", [self::ORGANIZATION]);
        $this->dispose = new CdaTemplateImportDispose();
        $this->table = $this->createMock(CarecoordinationTable::class);
    }

    protected function tearDown(): void
    {
        $pids = [self::PID, self::OTHER_PID];
        QueryUtils::sqlStatementThrowException('DELETE FROM `forms` WHERE `pid` IN (?, ?)', $pids);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_vitals` WHERE `pid` IN (?, ?)', $pids);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_encounter` WHERE `pid` IN (?, ?)', $pids);
        QueryUtils::sqlStatementThrowException('DELETE FROM `external_encounters` WHERE `ee_pid` IN (?, ?)', $pids);
        QueryUtils::sqlStatementThrowException('DELETE FROM `users` WHERE `npi` = ? OR (`abook_type` = ? AND `organization` = ?)', [self::NPI, 'external_org', self::ORGANIZATION]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testAnotherPatientsVitalsWithTheSameIdStayTheirs(): void
    {
        $this->encounter(self::PID, 'visit-1');
        $this->encounter(self::OTHER_PID, 'visit-2');

        $this->dispose->InsertVitals([$this->vitals('shared-id', '150')], self::PID, $this->table, 0);
        $this->dispose->InsertVitals([$this->vitals('shared-id', '210')], self::OTHER_PID, $this->table, 0);

        $this->assertSame(['150.000000'], $this->weights(self::PID), 'the first patient keeps their vitals');
        $this->assertSame(['210.000000'], $this->weights(self::OTHER_PID), 'the second patient gets their own');
        // Each patient's Vitals form opens their own row.
        foreach ([self::PID, self::OTHER_PID] as $pid) {
            $rows = QueryUtils::fetchRecords(
                "SELECT v.`pid` FROM `forms` AS f JOIN `form_vitals` AS v ON v.`id` = f.`form_id` WHERE f.`formdir` = 'vitals' AND f.`pid` = ?",
                [$pid]
            );
            $this->assertSame([$pid], array_map(static fn(array $row): int => (int)self::number($row['pid']), $rows));
        }
    }

    public function testImportingAPatientsVitalsAgainUpdatesTheirOwnCopy(): void
    {
        $this->encounter(self::PID, 'visit-1');

        $this->dispose->InsertVitals([$this->vitals('vitals-1', '150')], self::PID, $this->table, 0);
        $this->dispose->InsertVitals([$this->vitals('vitals-1', '151')], self::PID, $this->table, 0);

        $this->assertSame(['151.000000'], $this->weights(self::PID));
    }

    public function testImportingAnEncounterAgainLinksItsFormToThisPatientsEncounter(): void
    {
        $this->encounter(self::PID, 'shared-visit');
        $this->encounter(self::OTHER_PID, 'shared-visit');
        // Imported again for the second patient: the encounter row is updated, and a form is added for it.
        $this->encounter(self::OTHER_PID, 'shared-visit');

        $own = QueryUtils::fetchSingleValue('SELECT `id` FROM `form_encounter` WHERE `pid` = ?', 'id', [self::OTHER_PID]);
        $linked = QueryUtils::fetchRecords(
            "SELECT `form_id` FROM `forms` WHERE `pid` = ? AND `formdir` = 'newpatient' ORDER BY `id`",
            [self::OTHER_PID]
        );
        $this->assertCount(2, $linked);
        $this->assertSame((int)self::number($own), (int)self::number($linked[1]['form_id']), "the form added on the second import opens this patient's encounter");
    }

    private function encounter(int $pid, string $externalId): void
    {
        $this->dispose->InsertEncounter([[
            'extension' => $externalId,
            'date' => '20260301090000',
            'provider_npi' => self::NPI,
            'provider_name' => 'Match',
            'provider_family' => 'Provider',
            'represented_organization_name' => self::ORGANIZATION,
        ]], $pid, $this->table, 0);
    }

    /**
     * One vitals entry as the parser hands it to InsertVitals.
     *
     * @return array<string, ?string>
     */
    private function vitals(string $externalId, string $weight): array
    {
        return [
            'extension' => $externalId,
            'date' => '20260301093000',
            'bps' => '120',
            'bpd' => '80',
            'height' => '65',
            'weight' => $weight,
            'temperature' => '98.6',
            'pulse' => '70',
            'respiration' => '16',
            'head_circ' => null,
            'oxygen_saturation' => '98',
            'BMI' => '25',
        ];
    }

    /** @return list<string> */
    private function weights(int $pid): array
    {
        $rows = QueryUtils::fetchRecords('SELECT `weight` FROM `form_vitals` WHERE `pid` = ? ORDER BY `id`', [$pid]);
        return array_map(static fn(array $row): string => (string)self::number($row['weight']), $rows);
    }

    private function rowsLeft(): int
    {
        $pids = [self::PID, self::OTHER_PID];
        $count = 0;
        foreach (['forms' => 'pid', 'form_vitals' => 'pid', 'form_encounter' => 'pid', 'external_encounters' => 'ee_pid'] as $table => $column) {
            $count += (int)self::number(QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM `$table` WHERE `$column` IN (?, ?)", 'n', $pids));
        }
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
