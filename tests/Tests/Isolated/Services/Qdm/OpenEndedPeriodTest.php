<?php

/**
 * A record saved without an end date must still count in eCQMs. In CQL a null
 * end that is open is unknown, and one that is closed is unbounded, so an
 * ongoing problem, allergy, symptom or medication gets a closed null end, and a
 * visit saved without an end time (the encounter form never sets one) ends when
 * it starts.
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
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Interfaces\QdmServiceInterface;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\MeasureService;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\AllergyIntoleranceService;
use OpenEMR\Services\Qdm\Services\DiagnosisService;
use OpenEMR\Services\Qdm\Services\EncounterService;
use OpenEMR\Services\Qdm\Services\MedicationActiveService;
use OpenEMR\Services\Qdm\Services\PhysicalExamService;
use OpenEMR\Services\Qdm\Services\SymptomService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OpenEndedPeriodTest extends TestCase
{
    private const MEASURES = __DIR__ . '/../../../../../vendor/openemr/oe-cqm-parsers/2025_reporting_period/json_measures/';

    private const SYSTEMS = [
        CodeTypesService::CODE_TYPE_CPT4 => '2.16.840.1.113883.6.12',
        CodeTypesService::CODE_TYPE_ICD10 => '2.16.840.1.113883.6.90',
        CodeTypesService::CODE_TYPE_LOINC => '2.16.840.1.113883.6.1',
        CodeTypesService::CODE_TYPE_RXNORM => '2.16.840.1.113883.6.88',
    ];

    /**
     * @return array<string, array{class-string<QdmServiceInterface>, array<string, mixed>, string}>
     */
    public static function ongoingRecords(): array
    {
        $issue = ['pid' => 1, 'begdate' => '2024-01-01', 'enddate' => null, 'date' => '2024-01-01 00:00:00', 'diagnosis' => 'ICD10:I10'];
        return [
            'problem' => [DiagnosisService::class, $issue, 'prevalencePeriod'],
            'allergy' => [AllergyIntoleranceService::class, $issue, 'prevalencePeriod'],
            'symptom' => [SymptomService::class, $issue, 'prevalencePeriod'],
            'medication' => [MedicationActiveService::class, [
                'pid' => 1, 'start_date' => '2024-01-01', 'end_date' => null, 'date_added' => '2024-01-01 00:00:00',
                'dosage' => '', 'drug_unit' => null, 'drug_interval' => '', 'rxnorm_drugcode' => '197361',
            ], 'relevantPeriod'],
        ];
    }

    /**
     * @param class-string<QdmServiceInterface> $class
     * @param array<string, mixed>              $record
     */
    #[DataProvider('ongoingRecords')]
    public function testARecordWithNoEndDateIsOngoing(string $class, array $record, string $period): void
    {
        $interval = $this->interval($class, $record, $period);
        $this->assertNull($interval['high']);
        $this->assertTrue($interval['highClosed'], 'a closed null end is unbounded; an open one would be unknown');
    }

    /**
     * @param class-string<QdmServiceInterface> $class
     * @param array<string, mixed>              $record
     */
    #[DataProvider('ongoingRecords')]
    public function testARecordWithAnEndDateKeepsIt(string $class, array $record, string $period): void
    {
        $record[$class === MedicationActiveService::class ? 'end_date' : 'enddate'] = '2025-06-30';
        $interval = $this->interval($class, $record, $period);
        $this->assertIsString($interval['high']);
        $this->assertStringStartsWith('2025-06-30', $interval['high']);
        $this->assertTrue($interval['highClosed']);
    }

    public function testAVisitWithNoEndTimeEndsWhenItStarts(): void
    {
        $interval = $this->interval(EncounterService::class, self::visit(null), 'relevantPeriod');
        $this->assertSame($interval['low'], $interval['high']);
        $this->assertTrue($interval['highClosed']);
    }

    public function testAVisitWithAnEndTimeKeepsIt(): void
    {
        $interval = $this->interval(EncounterService::class, self::visit('2025-03-10 10:30:00'), 'relevantPeriod');
        $this->assertIsString($interval['high']);
        $this->assertIsString($interval['low']);
        $this->assertStringStartsWith('2025-03-10T10:30:00', $interval['high']);
        $this->assertStringStartsWith('2025-03-10T10:00:00', $interval['low']);
        $this->assertTrue($interval['highClosed']);
    }

    /**
     * Controlling High Blood Pressure for a patient recorded as OpenEMR's screens
     * save it: a visit with no end time and a hypertension problem with no end
     * date. Before the fix the patient was in no population.
     */
    public function testControllingHighBloodPressureCountsRecordsAsOpenEmrSavesThem(): void
    {
        $patient = new Patient(['birthDatetime' => '1975-05-01 00:00:00']);
        $this->addRecord($patient, 1, EncounterService::class, self::visit(null));
        $this->addRecord($patient, 2, DiagnosisService::class, ['pid' => 1, 'begdate' => '2024-01-01', 'enddate' => null, 'date' => '2024-01-01 00:00:00', 'diagnosis' => 'ICD10:I10']);
        foreach ([3 => ['LOINC:8480-6', '128'], 4 => ['LOINC:8462-4', '78']] as $id => [$code, $value]) {
            $this->addRecord($patient, $id, PhysicalExamService::class, [
                'pid' => 1, 'date' => '2025-03-10 10:15:00', 'date_end' => null, 'code' => $code, 'ob_value' => $value,
                'ob_unit' => 'mm[Hg]', 'ob_reason_code' => null, 'ob_reason_status' => null,
            ]);
        }

        $path = self::MEASURES . 'CMS165v13';
        $measure = new Measure(MeasureService::fetchMeasureJson($path));
        $measure->measure_path = $path;
        $results = (new CqmCalculator())->calculateMeasure([$patient], $measure, '2025-01-01 00:00:00');

        $populations = array_values($results)[0]['PopulationSet_1'] ?? [];
        $this->assertSame(
            ['IPP' => 1, 'DENOM' => 1, 'NUMER' => 1],
            ['IPP' => $populations['IPP'] ?? null, 'DENOM' => $populations['DENOM'] ?? null, 'NUMER' => $populations['NUMER'] ?? null],
        );
    }

    /**
     * Adds the data element a service makes from a database row, as QdmBuilder does.
     *
     * @param class-string<QdmServiceInterface> $class
     * @param array<string, mixed>              $record
     */
    private function addRecord(Patient $patient, int $id, string $class, array $record): void
    {
        $patient->add_data_element($this->service($class)->makeQdmModel(new QdmRecord($record, 1, $id)));
    }

    /**
     * @return array<string, mixed>
     */
    private static function visit(?string $end): array
    {
        return [
            'pid' => 1, 'encounter' => 7, 'date' => '2025-03-10 10:00:00', 'date_end' => $end, 'encounter_type_code' => 'CPT4:99213',
            'discharge_disposition' => null, 'discharge_dispo_code' => null, 'diagnosis' => null, 'pc_duration' => null,
        ];
    }

    /**
     * The period the service makes for a record, as the JSON the calculator receives.
     *
     * @param class-string<QdmServiceInterface> $class
     * @param array<string, mixed>              $record
     * @return array{low: mixed, high: mixed, lowClosed: mixed, highClosed: mixed}
     */
    private function interval(string $class, array $record, string $period): array
    {
        $model = $this->service($class)->makeQdmModel(new QdmRecord($record, 1, 1));
        $json = json_decode(json_encode($model, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($json);
        $interval = $json[$period] ?? null;
        $this->assertIsArray($interval);
        return [
            'low' => $interval['low'] ?? null,
            'high' => $interval['high'] ?? null,
            'lowClosed' => $interval['lowClosed'] ?? null,
            'highClosed' => $interval['highClosed'] ?? null,
        ];
    }

    /**
     * @param class-string<QdmServiceInterface> $class
     */
    private function service(string $class): QdmServiceInterface
    {
        $codeTypes = $this->createMock(CodeTypesService::class);
        $codeTypes->method('getSystemForCodeType')->willReturnCallback(
            static fn (string $type): string => self::SYSTEMS[$type] ?? '',
        );
        return new $class(new QdmRequestSome([1]), $codeTypes);
    }
}
