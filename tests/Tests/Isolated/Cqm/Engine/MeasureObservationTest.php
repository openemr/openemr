<?php

/**
 * The measure observation path of MeasureCalculator: MSRPOPL, MSRPOPLEX and
 * observation_values for continuous-variable measures, and the observation
 * rules of ratio measures.
 *
 * No measure in the 2023-2025 bundles exercises this path: every one with
 * observations (CMS871, CMS986, CMS1017, CMS111) fails in cqm-execution and in
 * this engine alike, because cqm-execution calls the observation function
 * with no arguments for patient-based measures and those functions take the
 * episode. So the parity fixtures never reach it. These tests use small
 * hand-written measures whose observation functions take no arguments.
 * Every expected value was worked out from cqm-execution 4.4.3's
 * handlePopulationValues and buildPopulationRelevanceMap, then confirmed by
 * running the same measures, patients and population values through
 * cqm-execution 4.4.3 itself.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Engine;

use OpenEMR\Cqm\Cql\Engine\MeasureCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MeasureObservationTest extends TestCase
{
    private const LIBRARY = 'ObservationTest';

    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('continuousVariableProvider')]
    public function testContinuousVariable(int $encounters, bool $diagnosis, array $expected): void
    {
        $measure = self::measure('CONTINUOUS_VARIABLE', [
            'IPP' => 'Initial Population',
            'MSRPOPL' => 'Measure Population',
            'MSRPOPLEX' => 'Measure Population Exclusions',
        ], ['Encounter Count']);

        $result = self::calculate($measure, $encounters, $diagnosis);

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key] ?? null, $key);
        }
    }

    /**
     * @return array<string, array{int, bool, array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function continuousVariableProvider(): array
    {
        return [
            'observed' => [3, false, [
                'IPP' => 1, 'MSRPOPL' => 1, 'MSRPOPLEX' => 0, 'observation_values' => [3],
            ]],
            'excluded from the measure population' => [2, true, [
                'IPP' => 1, 'MSRPOPL' => 1, 'MSRPOPLEX' => 1, 'observation_values' => [],
            ]],
            'not in the measure population' => [0, true, [
                'IPP' => 1, 'MSRPOPL' => 0, 'MSRPOPLEX' => 0, 'observation_values' => [],
            ]],
        ];
    }

    public function testObservationRelevanceFollowsTheMeasurePopulation(): void
    {
        $measure = self::measure('CONTINUOUS_VARIABLE', [
            'IPP' => 'Initial Population',
            'MSRPOPL' => 'Measure Population',
            'MSRPOPLEX' => 'Measure Population Exclusions',
        ], ['Encounter Count']);

        $observed = self::calculate($measure, 3, false);
        $excluded = self::calculate($measure, 2, true);

        $this->assertSame(['IPP' => true, 'MSRPOPL' => true, 'MSRPOPLEX' => true, 'observation_values' => true], $observed['population_relevance']);
        $this->assertFalse($excluded['population_relevance']['observation_values']);
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('ratioProvider')]
    public function testRatio(int $encounters, bool $diagnosis, array $expected): void
    {
        $measure = self::measure('RATIO', [
            'IPP' => 'Initial Population',
            'DENOM' => 'Measure Population',
            'NUMER' => 'Has Diagnosis',
        ], ['Encounter Count', 'Diagnosis Count']);

        $result = self::calculate($measure, $encounters, $diagnosis);

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $result[$key] ?? null, $key);
        }
    }

    /**
     * @return array<string, array{int, bool, array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function ratioProvider(): array
    {
        return [
            // Both observations count
            'denominator and numerator' => [2, true, [
                'IPP' => 1, 'DENOM' => 1, 'NUMER' => 1, 'observation_values' => [2, 1],
            ]],
            // NUMER 0 clears the numerator observation
            'denominator only' => [4, false, [
                'IPP' => 1, 'DENOM' => 1, 'NUMER' => 0, 'observation_values' => [4, null],
            ]],
            // DENOM 0 moves the first observation to the second slot
            'numerator only' => [0, true, [
                'IPP' => 1, 'DENOM' => 0, 'NUMER' => 1, 'observation_values' => [null, 0],
            ]],
        ];
    }

    /**
     * cqm-execution's handlePopulationValues for continuous variables, case
     * by case.
     *
     * @param array<string, mixed> $values
     * @param array<string, mixed> $expected
     */
    #[DataProvider('populationValuesProvider')]
    public function testHandlePopulationValues(string $scoring, array $values, array $expected): void
    {
        $this->assertSame($expected, MeasureCalculator::handlePopulationValues($values, $scoring));
    }

    /**
     * @return array<string, array{string, array<string, mixed>, array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function populationValuesProvider(): array
    {
        return [
            'not in the initial population clears everything' => [
                'CONTINUOUS_VARIABLE',
                ['IPP' => 0, 'MSRPOPL' => 1, 'MSRPOPLEX' => 1, 'observation_values' => [5]],
                ['IPP' => 0, 'MSRPOPL' => 0, 'MSRPOPLEX' => 0, 'observation_values' => []],
            ],
            'outside the measure population drops observations' => [
                'CONTINUOUS_VARIABLE',
                ['IPP' => 1, 'MSRPOPL' => 0, 'MSRPOPLEX' => 1, 'observation_values' => [5]],
                ['IPP' => 1, 'MSRPOPL' => 0, 'MSRPOPLEX' => 0, 'observation_values' => []],
            ],
            'an exclusion keeps the populations but drops observations' => [
                'CONTINUOUS_VARIABLE',
                ['IPP' => 1, 'MSRPOPL' => 2, 'MSRPOPLEX' => 1, 'observation_values' => [5, 6]],
                ['IPP' => 1, 'MSRPOPL' => 2, 'MSRPOPLEX' => 1, 'observation_values' => []],
            ],
            'a zero stratification clears everything' => [
                'CONTINUOUS_VARIABLE',
                ['STRAT' => 0, 'IPP' => 1, 'MSRPOPL' => 1, 'observation_values' => [5]],
                ['STRAT' => 0, 'IPP' => 0, 'MSRPOPL' => 0, 'observation_values' => []],
            ],
            'ratio: denominator excluded drops the denominator observation' => [
                'RATIO',
                ['IPP' => 1, 'DENOM' => 1, 'DENEX' => 1, 'NUMER' => 1, 'NUMEX' => 0, 'observation_values' => [5, 6]],
                ['IPP' => 1, 'DENOM' => 1, 'DENEX' => 1, 'NUMER' => 1, 'NUMEX' => 0, 'observation_values' => [null, 6]],
            ],
            'ratio: numerator excluded drops the numerator observation' => [
                'RATIO',
                ['IPP' => 1, 'DENOM' => 1, 'DENEX' => 0, 'NUMER' => 1, 'NUMEX' => 1, 'observation_values' => [5, 6]],
                ['IPP' => 1, 'DENOM' => 1, 'DENEX' => 0, 'NUMER' => 1, 'NUMEX' => 1, 'observation_values' => [5, null]],
            ],
        ];
    }

    /**
     * The first population set's result for one patient.
     *
     * @param array<mixed> $measure
     * @return array<string, mixed>
     */
    private static function calculate(array $measure, int $encounters, bool $diagnosis): array
    {
        $results = (new MeasureCalculator($measure, []))->calculate([self::patient($encounters, $diagnosis)], '202501010000');
        $result = $results['000000000000000000000001']['PopulationSet_1'] ?? null;
        self::assertIsArray($result);
        return $result;
    }

    /**
     * A patient-based measure on one library: "Initial Population" is true,
     * "Measure Population" is having an encounter, "Measure Population
     * Exclusions" and "Has Diagnosis" are having a diagnosis, and the
     * observation functions "Encounter Count" and "Diagnosis Count" take no
     * arguments.
     *
     * @param array<string, string> $populations population code => statement
     * @param list<string> $observationFunctions
     * @return array<mixed>
     */
    private static function measure(string $scoring, array $populations, array $observationFunctions): array
    {
        $retrieve = static fn (string $type): array => [
            'type' => 'Retrieve',
            'dataType' => "{urn:healthit-gov:qdm:v5_6}Positive$type",
            'templateId' => "Positive$type",
        ];
        $ref = static fn (string $name): array => ['type' => 'ExpressionRef', 'name' => $name];
        $define = static fn (string $name, array $expression): array => [
            'name' => $name, 'context' => 'Patient', 'accessLevel' => 'Public', 'expression' => $expression,
        ];
        $function = static fn (string $name, array $expression): array => [
            'name' => $name, 'context' => 'Patient', 'accessLevel' => 'Public', 'type' => 'FunctionDef',
            'operand' => [], 'expression' => $expression,
        ];
        $defs = [
            $define('Patient', ['type' => 'SingletonFrom', 'operand' => [
                'type' => 'Retrieve', 'dataType' => '{urn:healthit-gov:qdm:v5_6}Patient', 'templateId' => 'Patient',
            ]]),
            $define('Encounters', $retrieve('EncounterPerformed')),
            $define('Diagnoses', $retrieve('Diagnosis')),
            $define('Initial Population', ['type' => 'Literal', 'valueType' => '{urn:hl7-org:elm-types:r1}Boolean', 'value' => 'true']),
            $define('Measure Population', ['type' => 'Exists', 'operand' => $ref('Encounters')]),
            $define('Measure Population Exclusions', ['type' => 'Exists', 'operand' => $ref('Diagnoses')]),
            $define('Has Diagnosis', ['type' => 'Exists', 'operand' => $ref('Diagnoses')]),
            $function('Encounter Count', ['type' => 'Count', 'source' => $ref('Encounters')]),
            $function('Diagnosis Count', ['type' => 'Count', 'source' => $ref('Diagnoses')]),
        ];
        $statement = static fn (string $name): array => ['library_name' => self::LIBRARY, 'statement_name' => $name];
        $populationRefs = [];
        foreach ($populations as $code => $name) {
            $populationRefs[$code] = $statement($name);
        }
        return [
            '_id' => 'observation-test',
            'measure_scoring' => $scoring,
            'calculation_method' => 'PATIENT',
            'main_cql_library' => self::LIBRARY,
            'cql_libraries' => [[
                'library_name' => self::LIBRARY,
                'library_version' => '1.0.000',
                'is_main_library' => true,
                'statement_dependencies' => [],
                'elm' => ['library' => [
                    'identifier' => ['id' => self::LIBRARY, 'version' => '1.0.000'],
                    'usings' => ['def' => [['localIdentifier' => 'QDM', 'uri' => 'urn:healthit-gov:qdm:v5_6', 'version' => '5.6']]],
                    'statements' => ['def' => $defs],
                ]],
            ]],
            'population_sets' => [[
                'population_set_id' => 'PopulationSet_1',
                'populations' => $populationRefs,
                'observations' => array_map(static fn (string $name): array => [
                    'observation_function' => $statement($name),
                    'observation_parameter' => $statement('Measure Population'),
                ], $observationFunctions),
            ]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function patient(int $encounters, bool $diagnosis): array
    {
        $elements = [];
        for ($i = 1; $i <= $encounters; $i++) {
            $elements[] = [
                '_type' => 'QDM::EncounterPerformed',
                'qdmVersion' => '5.6',
                'dataElementCodes' => [['code' => '99213', 'system' => '2.16.840.1.113883.6.12']],
                'relevantPeriod' => ['low' => "2025-0{$i}-01T08:00:00.000+00:00", 'high' => "2025-0{$i}-01T09:00:00.000+00:00"],
            ];
        }
        if ($diagnosis) {
            $elements[] = [
                '_type' => 'QDM::Diagnosis',
                'qdmVersion' => '5.6',
                'dataElementCodes' => [['code' => '44054006', 'system' => '2.16.840.1.113883.6.96']],
                'prevalencePeriod' => ['low' => '2025-02-01T00:00:00.000+00:00'],
            ];
        }
        return [
            '_id' => '000000000000000000000001',
            'id' => '000000000000000000000001',
            '_type' => 'QDM::Patient',
            'qdmVersion' => '5.6',
            'birthDatetime' => '1960-01-01T00:00:00.000+00:00',
            'dataElements' => $elements,
        ];
    }
}
