<?php

/**
 * Shadow-mode comparison of the two eCQM engines' results, and the engine
 * global setting.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm;

use OpenEMR\Cqm\CqmCalculationEngine;
use OpenEMR\Cqm\PhpCqmCalculation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhpCqmCalculationTest extends TestCase
{
    public function testMatchingResultsHaveNoDifferences(): void
    {
        $results = ['p1' => ['PopulationSet_1' => ['IPP' => 1, 'DENOM' => 1, 'NUMER' => 0, 'statement_results' => ['ignored']]]];
        $this->assertSame([], PhpCqmCalculation::differences($results, $results));
    }

    public function testOnlyComparedPopulationsCount(): void
    {
        $expected = ['p1' => ['PopulationSet_1' => ['IPP' => 1, 'state' => 'complete', 'population_relevance' => ['IPP' => true]]]];
        $actual = ['p1' => ['PopulationSet_1' => ['IPP' => 1, 'state' => 'other', 'population_relevance' => []]]];
        $this->assertSame([], PhpCqmCalculation::differences($expected, $actual));
    }

    public function testReportsEachDifferingPopulation(): void
    {
        $expected = ['p1' => ['PopulationSet_1' => ['IPP' => 1, 'DENOM' => 1, 'NUMER' => 1, 'observation_values' => [30]]]];
        $actual = ['p1' => ['PopulationSet_1' => ['IPP' => 1, 'DENOM' => 0, 'NUMER' => 1, 'observation_values' => [31]]]];
        $this->assertSame([
            ['patient' => 'p1', 'populationSet' => 'PopulationSet_1', 'population' => 'DENOM', 'expected' => 1, 'actual' => 0],
            ['patient' => 'p1', 'populationSet' => 'PopulationSet_1', 'population' => 'observation_values', 'expected' => [30], 'actual' => [31]],
        ], PhpCqmCalculation::differences($expected, $actual));
    }

    public function testMissingPatientOrSetIsADifference(): void
    {
        $expected = [
            'p1' => ['PopulationSet_1' => ['IPP' => 1]],
            'p2' => ['PopulationSet_1' => ['IPP' => 0], 'PopulationSet_2' => ['IPP' => 1]],
        ];
        $actual = ['p2' => ['PopulationSet_1' => ['IPP' => 0]]];
        $this->assertSame([
            ['patient' => 'p1', 'populationSet' => 'PopulationSet_1', 'population' => 'IPP', 'expected' => 1, 'actual' => null],
            ['patient' => 'p2', 'populationSet' => 'PopulationSet_2', 'population' => 'IPP', 'expected' => 1, 'actual' => null],
        ], PhpCqmCalculation::differences($expected, $actual));
    }

    public function testDifferencesStopAtTheLimit(): void
    {
        $expected = [];
        $actual = [];
        for ($i = 0; $i < 10; $i++) {
            $expected["p$i"] = ['PopulationSet_1' => ['IPP' => 1]];
            $actual["p$i"] = ['PopulationSet_1' => ['IPP' => 0]];
        }
        $this->assertCount(3, PhpCqmCalculation::differences($expected, $actual, 3));
    }

    #[DataProvider('settingProvider')]
    public function testEngineFromSetting(string $setting, CqmCalculationEngine $engine): void
    {
        $this->assertSame($engine, CqmCalculationEngine::fromSetting($setting));
    }

    /**
     * @return array<string, array{string, CqmCalculationEngine}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function settingProvider(): array
    {
        return [
            'node' => ['node', CqmCalculationEngine::Node],
            'shadow' => ['shadow', CqmCalculationEngine::Shadow],
            'php' => ['php', CqmCalculationEngine::Php],
            'unset' => ['', CqmCalculationEngine::Node],
            'unknown' => ['other', CqmCalculationEngine::Node],
        ];
    }
}
