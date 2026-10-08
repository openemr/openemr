<?php

/**
 * Engine results reach IndividualResult with far more keys than it reads
 * (statement and clause results, relevance maps, ids). It has to keep the
 * population counts and ignore the rest, and ResultsCalculator has to total
 * them per population set.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Cqm\Qdm\BaseTypes\Code;
use OpenEMR\Cqm\Qdm\Identifier;
use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Cqm\Qdm\PatientCharacteristicSex;
use OpenEMR\Services\Qdm\IndividualResult;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\ResultsCalculator;
use PHPUnit\Framework\TestCase;

class IndividualResultTest extends TestCase
{
    public function testKeepsThePopulationCountsOfAnEngineResult(): void
    {
        $result = new IndividualResult(
            self::engineResult('PopulationSet_1', '00000000000000000000002a', 1, 1, 0),
            self::installedMeasure('CMS122v13')
        );

        $this->assertSame(1, $result->IPP);
        $this->assertSame(1, $result->DENOM);
        $this->assertSame(0, $result->NUMER);
        $this->assertNull($result->DENEXCEP, 'A population the measure does not define stays unset.');
        $this->assertSame('PopulationSet_1', $result->population_set_key);
        $this->assertSame(42, $result->patient_id->value);
        $this->assertSame([], $result->observation_values);
    }

    public function testTotalsEngineResultsPerPopulationSet(): void
    {
        $measure = self::installedMeasure('CMS122v13');
        $patients = [self::patient(42, 'F'), self::patient(43, 'M')];
        $results = [
            new IndividualResult(self::engineResult('PopulationSet_1', '00000000000000000000002a', 1, 1, 1), $measure),
            new IndividualResult(self::engineResult('PopulationSet_1', '00000000000000000000002b', 1, 1, 0), $measure),
        ];

        $calculator = new ResultsCalculator($patients, null, null);
        // ExportCat3Service files each measure's results under its hqmf_id.
        $totals = $calculator->aggregate_results_for_measures([$measure], [$measure->hqmf_id => $results]);

        $set = self::arrayAt($totals, $measure->hqmf_id, 'PopulationSet_1');
        $this->assertSame(2, $set['IPP']);
        $this->assertSame(2, $set['DENOM']);
        $this->assertSame(1, $set['NUMER']);
        $this->assertSame(0, $set['DENEX']);
        $this->assertSame(['F' => 1], self::arrayAt($set, 'supplemental_data', 'NUMER', 'SEX'));
    }

    /**
     * A per-patient result shaped as cqm-execution returns it, after
     * ExportCat3Service adds the population set key and patient id.
     *
     * @return array<string, mixed>
     */
    private static function engineResult(string $populationSetKey, string $patientId, int $ipp, int $denom, int $numer): array
    {
        return [
            'IPP' => $ipp,
            'DENOM' => $denom,
            'NUMER' => $numer,
            'DENEX' => 0,
            'clause_results' => [['library_name' => 'Example', 'statement_name' => 'Initial Population', 'localId' => '1', 'final' => 'TRUE']],
            'statement_results' => [['library_name' => 'Example', 'statement_name' => 'Initial Population', 'final' => 'TRUE', 'relevance' => 'TRUE']],
            'population_relevance' => ['IPP' => true, 'DENOM' => true, 'NUMER' => true, 'DENEX' => true],
            'observation_values' => [],
            'state' => 'complete',
            'measure_id' => '68923b59a83e15671c2c7cac',
            '_id' => '6ac6db70a2f5421ab95d874a',
            'population_set_key' => $populationSetKey,
            'patient_id' => $patientId,
        ];
    }

    /**
     * Walks nested arrays, failing the test where a level is missing.
     *
     * @return array<mixed>
     */
    private static function arrayAt(mixed $value, string ...$keys): array
    {
        foreach ($keys as $key) {
            self::assertIsArray($value);
            self::assertArrayHasKey($key, $value);
            $value = $value[$key];
        }
        self::assertIsArray($value);
        return $value;
    }

    private static function patient(int $id, string $sex): Patient
    {
        $patient = new Patient(['id' => new Identifier(['namingSystem' => 'System', 'value' => $id])]);
        $patient->add_data_element(new PatientCharacteristicSex([
            'dataElementCodes' => [new Code(['code' => $sex, 'system' => '2.16.840.1.113883.5.1'])],
        ]));
        return $patient;
    }

    private static function installedMeasure(string $name): Measure
    {
        $path = dirname(__DIR__, 5) . "/vendor/openemr/oe-cqm-parsers/2025_reporting_period/json_measures/$name/$name.json";
        $json = file_get_contents($path);
        if ($json === false) {
            self::fail("Install openemr/oe-cqm-parsers with Composer to run this test; $name is missing.");
        }
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            self::fail("$name is not a measure");
        }
        return new Measure($data);
    }
}
