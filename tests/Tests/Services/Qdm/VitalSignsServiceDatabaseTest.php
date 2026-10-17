<?php

/**
 * Vitals from the Vitals form, read from the database as the eCQMs read them: only the
 * requested patients, not from deleted forms, and each QRDA-imported exam counted once.
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
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\PhysicalExamService;
use OpenEMR\Services\Qdm\Services\VitalSignsService;
use PHPUnit\Framework\TestCase;

class VitalSignsServiceDatabaseTest extends TestCase
{
    // Synthetic patients; no patient_data rows are needed, and every row made here is removed.
    private const PID = 990611;
    private const OTHER_PID = 990612;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patients before the test');
    }

    protected function tearDown(): void
    {
        $pids = [self::PID, self::OTHER_PID];
        QueryUtils::sqlStatementThrowException("DELETE FROM `forms` WHERE `formdir` = 'vitals' AND `pid` IN (?, ?)", $pids);
        QueryUtils::sqlStatementThrowException("DELETE FROM `form_vitals` WHERE `pid` IN (?, ?)", $pids);
        QueryUtils::sqlStatementThrowException("DELETE FROM `form_observation` WHERE `pid` IN (?, ?)", $pids);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testOnlyTheRequestedPatientsVitalsAreRead(): void
    {
        $this->vitals(self::PID, '2026-03-02 09:00:00', ['bps' => '128', 'bpd' => '82']);
        $this->vitals(self::OTHER_PID, '2026-03-02 09:00:00', ['bps' => '150', 'bpd' => '95']);

        $rows = $this->read(new QdmRequestOne((string)self::PID));
        $this->assertSame([[self::PID, 'LOINC:8480-6', 128.0], [self::PID, 'LOINC:8462-4', 82.0]], $rows);
        $both = $this->read(new QdmRequestSome([self::PID, self::OTHER_PID]));
        $this->assertCount(4, $both);
    }

    public function testAVitalsFormThatWasDeletedIsNotRead(): void
    {
        $this->vitals(self::PID, '2026-03-02 09:00:00', ['bps' => '128'], deleted: true);
        $this->vitals(self::PID, '2026-03-09 09:00:00', ['bps' => '131']);

        $this->assertSame([[self::PID, 'LOINC:8480-6', 131.0]], $this->read(new QdmRequestOne((string)self::PID)));
    }

    public function testADifferentReadingTheSameDayIsKept(): void
    {
        // CMS165 takes the lowest blood pressure of the most recent day: a second reading must not be lost.
        $this->observation(self::PID, '2026-03-02 08:15:00', '8480-6', '150', 'mm[Hg]');
        $this->vitals(self::PID, '2026-03-02 09:00:00', ['bps' => '128']);

        $this->assertSame([[self::PID, 'LOINC:8480-6', 128.0]], $this->read(new QdmRequestOne((string)self::PID)));
        $exams = $this->service(PhysicalExamService::class, new QdmRequestOne((string)self::PID))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $exams);
        $values = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($exams))) {
            $values[] = self::text($row['ob_value']);
        }
        $this->assertSame(['150'], $values, 'and the earlier reading is still the exam service\'s');
    }

    public function testAQrdaImportedExamIsCountedOnce(): void
    {
        // The import writes each exam to form_observation and to a Vitals form; the date can differ in form,
        // and weight arrives as 3141-9 or 29463-7.
        $this->observation(self::PID, '2026-03-02 09:00:00', '8480-6', '128', 'mm[Hg]');
        $this->observation(self::PID, '2026-03-02 00:00:00', '3141-9', '70.3', 'kg');
        $this->vitals(self::PID, '2026-03-02 09:00:00', ['bps' => '128', 'weight' => '70.30', 'BMI' => '18.7']);

        $this->assertSame([[self::PID, 'LOINC:39156-5', 18.7]], $this->read(new QdmRequestOne((string)self::PID)),
            'blood pressure and weight are the imported exams\' copies; the BMI is only in the Vitals form');
    }

    public function testReadingsAreComparedAtTheirStoredPrecision(): void
    {
        // form_vitals keeps BMI, height and weight to six places: two BMIs either side of 25 that round
        // to the same hundredth are two readings, while an imported copy of the same value still matches.
        $this->observation(self::PID, '2026-03-02 08:15:00', '39156-5', '25.004', 'kg/m2');
        $this->vitals(self::PID, '2026-03-02 09:00:00', ['BMI' => '24.996']);
        $this->observation(self::PID, '2026-03-03 09:00:00', '3141-9', '70.123456', 'kg');
        $this->vitals(self::PID, '2026-03-03 09:00:00', ['weight' => '70.123456']);

        $this->assertSame([[self::PID, 'LOINC:39156-5', 24.996]], $this->read(new QdmRequestOne((string)self::PID)));
    }

    public function testAnExamWhoseValueIsNotANumberDoesNotHideAReading(): void
    {
        // PhysicalExamService reads "128abc" as no result; CAST alone would read it as 128 and drop the reading.
        $this->observation(self::PID, '2026-03-02 09:00:00', '8480-6', '128abc', 'mm[Hg]');
        $this->vitals(self::PID, '2026-03-02 09:00:00', ['bps' => '128']);

        $this->assertSame([[self::PID, 'LOINC:8480-6', 128.0]], $this->read(new QdmRequestOne((string)self::PID)));
    }

    /** @return list<array{int, string, float}> pid, code and value of each vital read, in the query's order */
    private function read(QdmRequestOne|QdmRequestSome $request): array
    {
        $result = $this->service(VitalSignsService::class, $request)->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $rows = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $rows[] = [(int)self::number($row['pid']), self::text($row['code']), (float)self::number($row['value'])];
        }
        return $rows;
    }

    /**
     * @template T of VitalSignsService|PhysicalExamService
     * @param class-string<T> $class
     * @return T
     */
    private function service(string $class, QdmRequestOne|QdmRequestSome $request): VitalSignsService|PhysicalExamService
    {
        return new $class($request, new CodeTypesService());
    }

    /** @param array<string, string> $values form_vitals column => value */
    private function vitals(int $pid, string $date, array $values, bool $deleted = false): void
    {
        $columns = array_keys($values);
        $id = QueryUtils::sqlInsert(
            'INSERT INTO `form_vitals` (`pid`, `date`, `activity`, ' . implode(', ', array_map(static fn(string $c): string => "`$c`", $columns)) . ') VALUES (?, ?, 1'
            . str_repeat(', ?', count($columns)) . ')',
            array_merge([$pid, $date], array_values($values)),
        );
        QueryUtils::sqlInsert(
            "INSERT INTO `forms` (`date`, `encounter`, `form_name`, `form_id`, `pid`, `user`, `groupname`, `authorized`, `deleted`, `formdir`)"
            . " VALUES (?, 0, 'Vitals', ?, ?, 'test', 'Default', 1, ?, 'vitals')",
            [$date, $id, $pid, $deleted ? 1 : 0],
        );
    }

    private function observation(int $pid, string $date, string $loinc, string $value, string $unit): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `form_observation` (`form_id`, `date`, `pid`, `encounter`, `user`, `groupname`, `authorized`, `activity`,"
            . " `code`, `observation`, `ob_value`, `ob_unit`, `ob_type`) VALUES (0, ?, ?, 0, 'test', 'Default', 1, 1, ?, 'test exam', ?, ?, ?)",
            [$date, $pid, "LOINC:$loinc", $value, $unit, PhysicalExamService::OB_TYPE_PHYSICAL_EXAM],
        );
    }

    private function rowsLeft(): int
    {
        $pids = [self::PID, self::OTHER_PID];
        return (int)self::number(QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM `forms` WHERE `formdir` = 'vitals' AND `pid` IN (?, ?)", 'n', $pids))
            + (int)self::number(QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM `form_vitals` WHERE `pid` IN (?, ?)', 'n', $pids))
            + (int)self::number(QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM `form_observation` WHERE `pid` IN (?, ?)', 'n', $pids));
    }

    private static function number(mixed $value): int|float|string
    {
        self::assertIsNumeric($value);
        return $value;
    }

    private static function text(mixed $value): string
    {
        self::assertIsString($value);
        return $value;
    }
}
