<?php

/**
 * Vitals entered in the Vitals form reach the eCQMs as Physical Exam, Performed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Cqm\Qdm\PhysicalExamPerformed;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmBuilder;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\DiagnosisService;
use OpenEMR\Services\Qdm\Services\EncounterService;
use OpenEMR\Services\Qdm\Services\VitalSignsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VitalSignsServiceTest extends TestCase
{
    private const MEASURES = __DIR__ . '/../../../../../vendor/openemr/oe-cqm-parsers/2025_reporting_period/json_measures/';

    private const LOINC = '2.16.840.1.113883.6.1';

    private const SYSTEMS = [
        CodeTypesService::CODE_TYPE_CPT4 => '2.16.840.1.113883.6.12',
        CodeTypesService::CODE_TYPE_ICD10 => '2.16.840.1.113883.6.90',
        CodeTypesService::CODE_TYPE_LOINC => self::LOINC,
    ];

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function vitals(): array
    {
        $cases = [];
        foreach (VitalSignsService::VITALS as $column => [$loinc, $unit]) {
            $cases[$column] = [$loinc, $unit, '18.7'];
        }
        return $cases;
    }

    #[DataProvider('vitals')]
    public function testEachVitalIsAPhysicalExamWithItsLoincCodeAndUnit(string $loinc, string $unit, string $value): void
    {
        $json = $this->json($this->vital($loinc, $unit, $value));
        $this->assertSame('QDM::PhysicalExamPerformed', $json['_type'] ?? null);
        $codes = $json['dataElementCodes'] ?? null;
        $this->assertIsArray($codes);
        $this->assertSame([['code' => $loinc, 'system' => self::LOINC]], array_map(
            static fn (mixed $code): array => is_array($code) ? ['code' => $code['code'] ?? null, 'system' => $code['system'] ?? null] : [],
            $codes,
        ));
        $result = $json['result'] ?? null;
        $this->assertIsArray($result);
        $this->assertSame(['value' => 18.7, 'unit' => $unit], array_intersect_key($result, ['value' => 1, 'unit' => 1]));
        $this->assertIsString($json['relevantDatetime'] ?? null);
        $this->assertStringStartsWith('2025-03-10T10:15:00', $json['relevantDatetime']);
        $this->assertSame($json['relevantDatetime'], $json['authorDatetime'] ?? null);
    }

    public function testTheBuilderRunsTheService(): void
    {
        $services = (new \ReflectionProperty(QdmBuilder::class, 'services'))->getValue(new QdmBuilder());
        $this->assertIsArray($services);
        $this->assertContains(VitalSignsService::class, $services);
    }

    /**
     * @return array<string, array{string, int, string, list<array{string, string, string}>, array{int, int, int}}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function measures(): array
    {
        $bp = [['8480-6', 'mm[Hg]', '128'], ['8462-4', 'mm[Hg]', '78']];
        return [
            'CMS165 blood pressure from Vitals' => ['CMS165v13', 1975, 'PopulationSet_1', $bp, [1, 1, 1]],
            'CMS165 without it' => ['CMS165v13', 1975, 'PopulationSet_1', [], [1, 1, 0]],
            // 18.7 is a normal BMI; truncated to 18 it would be low, with no follow-up.
            'CMS69 BMI 18.7 from Vitals' => ['CMS69v13', 1975, 'PopulationSet_1', [['39156-5', 'kg/m2', '18.7']], [1, 1, 1]],
            'CMS69 without it' => ['CMS69v13', 1975, 'PopulationSet_1', [], [1, 1, 0]],
            'CMS155 BMI percentile, height and weight from Vitals' => ['CMS155v13', 2015, 'PopulationSet_1', [
                ['59576-9', '%', '60'], ['8302-2', '[in_i]', '55'], ['29463-7', '[lb_av]', '80'],
            ], [1, 1, 1]],
            'CMS155 without height' => ['CMS155v13', 2015, 'PopulationSet_1', [['59576-9', '%', '60'], ['29463-7', '[lb_av]', '80']], [1, 1, 0]],
        ];
    }

    /**
     * A patient seen once in 2025 with a hypertension problem, and the given vitals taken
     * at that visit. The visit has an end time and the problem an end date, so this test
     * doesn't depend on how open-ended periods are read.
     *
     * @param list<array{string, string, string}> $vitals [LOINC, unit, value]
     * @param array{int, int, int}                $expected IPP, DENOM, NUMER
     */
    #[DataProvider('measures')]
    public function testMeasuresCountVitals(string $measureName, int $born, string $set, array $vitals, array $expected): void
    {
        $populations = $this->populations($measureName, $born, $set, $vitals);
        $this->assertSame(
            ['IPP' => $expected[0], 'DENOM' => $expected[1], 'NUMER' => $expected[2]],
            ['IPP' => $populations['IPP'] ?? null, 'DENOM' => $populations['DENOM'] ?? null, 'NUMER' => $populations['NUMER'] ?? null],
        );
    }

    public function testCms22CountsANormalBloodPressureFromVitals(): void
    {
        // CMS22 screens adults without hypertension: a diagnosis of it is a denominator exclusion,
        // so this patient has none. A normal reading entered only in Vitals now meets the numerator.
        $only = static fn (array $p): array => array_intersect_key($p, ['IPP' => 1, 'DENOM' => 1, 'DENEX' => 1, 'NUMER' => 1]);
        $normal = [['8480-6', 'mm[Hg]', '118'], ['8462-4', 'mm[Hg]', '76']];
        $this->assertSame(['DENEX' => 0, 'DENOM' => 1, 'IPP' => 1, 'NUMER' => 1], self::sorted($only($this->populations('CMS22v13', 1975, 'PopulationSet_1', $normal, false))));
        $this->assertSame(['DENEX' => 0, 'DENOM' => 1, 'IPP' => 1, 'NUMER' => 0], self::sorted($only($this->populations('CMS22v13', 1975, 'PopulationSet_1', [], false))));
    }

    public function testAVitalThatIsNotANumberIsNoResult(): void
    {
        // A Quantity with no value would stop the measure from being calculated at all.
        $this->assertNull($this->vital('8480-6', 'mm[Hg]', 'high')->result);
    }

    /**
     * @param array<string, mixed> $populations
     * @return array<string, mixed>
     */
    private static function sorted(array $populations): array
    {
        ksort($populations);
        return $populations;
    }

    /**
     * One patient's populations for a measure: a 2025 office visit, hypertension on the problem
     * list unless left out, and these vitals from the Vitals form.
     *
     * @param list<array{string, string, string}> $vitals
     * @return array<string, mixed>
     */
    private function populations(string $measureName, int $born, string $set, array $vitals, bool $hypertension = true): array
    {
        $patient = new Patient(['birthDatetime' => "$born-05-01 00:00:00"]);
        $this->add($patient, 1, EncounterService::class, [
            'pid' => 1, 'encounter' => 7, 'date' => '2025-03-10 10:00:00', 'date_end' => '2025-03-10 10:30:00',
            'encounter_type_code' => 'CPT4:99213', 'discharge_disposition' => null, 'discharge_dispo_code' => null,
            'diagnosis' => null, 'pc_duration' => null,
        ]);
        if ($hypertension) {
            $this->add($patient, 2, DiagnosisService::class, [
                'pid' => 1, 'begdate' => '2024-01-01', 'enddate' => '2026-12-31', 'date' => '2024-01-01 00:00:00', 'diagnosis' => 'ICD10:I10',
            ]);
        }
        foreach ($vitals as $i => [$loinc, $unit, $value]) {
            $patient->add_data_element($this->vitalSigns()->makeQdmModel(new QdmRecord(
                ['pid' => 1, 'date' => '2025-03-10 10:15:00', 'code' => "LOINC:$loinc", 'value' => $value, 'unit' => $unit],
                1,
                3 + $i,
            )));
        }

        $path = self::MEASURES . $measureName;
        $measure = new Measure(MeasureService::fetchMeasureJson($path));
        $measure->measure_path = $path;
        $results = (new CqmCalculator())->calculateMeasure([$patient], $measure, '2025-01-01 00:00:00');
        return array_values($results)[0][$set] ?? [];
    }

    private function vital(string $loinc, string $unit, string $value): PhysicalExamPerformed
    {
        return $this->vitalSigns()->makeQdmModel(new QdmRecord(
            ['pid' => 1, 'date' => '2025-03-10 10:15:00', 'code' => "LOINC:$loinc", 'value' => $value, 'unit' => $unit],
            1,
            1,
        ));
    }

    /**
     * @param class-string<QdmServiceInterface> $class
     * @param array<string, mixed>              $record
     */
    private function add(Patient $patient, int $id, string $class, array $record): void
    {
        $patient->add_data_element($this->service($class)->makeQdmModel(new QdmRecord($record, 1, $id)));
    }

    /**
     * @return array<mixed>
     */
    private function json(object $model): array
    {
        $json = json_decode(json_encode($model, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json);
        return $json;
    }

    private function vitalSigns(): VitalSignsService
    {
        return new VitalSignsService(new QdmRequestSome([1]), $this->codeTypes());
    }

    private function codeTypes(): CodeTypesService
    {
        $codeTypes = $this->createMock(CodeTypesService::class);
        $codeTypes->method('getSystemForCodeType')->willReturnCallback(
            static fn (string $type): string => self::SYSTEMS[$type] ?? '',
        );
        return $codeTypes;
    }

    /**
     * @param class-string<QdmServiceInterface> $class
     */
    private function service(string $class): QdmServiceInterface
    {
        return new $class(new QdmRequestSome([1]), $this->codeTypes());
    }
}
