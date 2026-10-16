<?php

/**
 * CqmCalculator reports a measure the engine cannot calculate as a
 * MeasureCalculationException naming the measure, so QRDA exports can tell
 * the user which measure to leave out instead of failing with the engine's
 * internal error.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\MeasureCalculationException;
use OpenEMR\Services\Qdm\MeasureService;
use PHPUnit\Framework\TestCase;

class CqmCalculatorTest extends TestCase
{
    private const MEASURES = __DIR__ . '/../../../../../vendor/openemr/oe-cqm-parsers/2025_reporting_period/json_measures/';

    public function testAMeasureTheEngineCannotRunNamesTheMeasure(): void
    {
        // CMS871v4 calls a function with arguments no overload accepts; cqm-execution fails on it too
        try {
            (new CqmCalculator())->calculateMeasure([self::patient()], self::measure('CMS871v4'), '2025-01-01 00:00:00');
            $this->fail('CMS871v4 calculated although the engine cannot run it');
        } catch (MeasureCalculationException $e) {
            $this->assertSame('CMS871v4', $e->measureId);
            $this->assertSame('Measure CMS871v4 cannot be calculated by the eCQM calculation engine', $e->getMessage());
            $this->assertInstanceOf(\UnexpectedValueException::class, $e->getPrevious());
        }
    }

    public function testASupportedMeasureCalculates(): void
    {
        $results = (new CqmCalculator())->calculateMeasure([self::patient()], self::measure('CMS122v13'), '2025-01-01 00:00:00');

        $this->assertCount(1, $results);
        $this->assertSame(0, array_values($results)[0]['PopulationSet_1']['IPP'] ?? null);
    }

    public function testOneInvalidPatientDoesNotDiscardOtherPatientResults(): void
    {
        $validPatient = new Patient(['id' => 'valid-patient', 'extendedData' => ['pid' => 1]]);
        $invalidPatient = new Patient(['id' => 'invalid-patient', 'extendedData' => ['pid' => 2]]);
        $calculator = new PatientIsolationCqmCalculator();

        $results = $calculator->calculatePatients([$validPatient, $invalidPatient]);

        $this->assertSame([
            'valid-patient' => ['population-1' => ['IPP' => 1]],
        ], $results);
        $this->assertSame([2, 1, 1], $calculator->batchSizes);
    }

    public function testMeasureLevelErrorsAreNotRetriedForEveryPatient(): void
    {
        $calculator = new PatientIsolationCqmCalculator();
        $calculator->throwMeasureLevelError = true;

        try {
            $calculator->calculatePatients([self::patient(), self::patient()]);
            $this->fail('Unsupported measure logic should still fail the calculation');
        } catch (MeasureCalculationException $exception) {
            $this->assertInstanceOf(\UnexpectedValueException::class, $exception->getPrevious());
        }

        $this->assertSame([2], $calculator->batchSizes);
    }

    private static function measure(string $name): Measure
    {
        $path = self::MEASURES . $name;
        $measure = new Measure(MeasureService::fetchMeasureJson($path));
        $measure->measure_path = $path;
        return $measure;
    }

    private static function patient(): Patient
    {
        return new Patient(['birthDatetime' => '1950-01-01 00:00:00']);
    }
}

final class PatientIsolationCqmCalculator extends CqmCalculator
{
    /** @var list<int> */
    public array $batchSizes = [];

    public bool $throwMeasureLevelError = false;

    /**
     * @param list<Patient> $patients
     * @return array<string, array<string, array<string, mixed>>>
     * @throws \JsonException
     */
    public function calculatePatients(array $patients): array
    {
        return $this->calculateWithPatientIsolation(
            $patients,
            json_encode($patients, JSON_THROW_ON_ERROR),
            [],
            '[]',
            '20260101000000',
            'CMS122v14'
        );
    }

    /**
     * @param array<mixed, mixed> $measureData
     * @return array<string, array<string, array<string, mixed>>>
     */
    protected function runEngine(
        string $patientsJson,
        array $measureData,
        string $valueSetsJson,
        string $effectiveDate
    ): array {
        $decodedPatients = json_decode($patientsJson, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decodedPatients) || !array_is_list($decodedPatients)) {
            throw new \UnexpectedValueException('Patient payload must be a JSON list');
        }

        $patients = $decodedPatients;
        $this->batchSizes[] = count($patients);
        if ($this->throwMeasureLevelError) {
            throw new \UnexpectedValueException('Unsupported measure logic');
        }

        if (count($patients) > 1) {
            throw new \InvalidArgumentException('Invalid unit in one patient record');
        }

        $patient = $patients[0] ?? null;
        if (!is_array($patient)) {
            throw new \UnexpectedValueException('Patient payload must contain an object');
        }

        $extendedData = $patient['extendedData'] ?? null;
        $pid = is_array($extendedData) ? ($extendedData['pid'] ?? null) : null;
        if ($pid === 2) {
            throw new \InvalidArgumentException('Invalid unit in patient record');
        }

        $patientId = $patient['id'] ?? null;
        if (!is_string($patientId)) {
            throw new \UnexpectedValueException('Patient payload must contain a string id');
        }

        return [$patientId => ['population-1' => ['IPP' => 1]]];
    }
}
