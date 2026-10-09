<?php

/**
 * Calculates a measure with the PHP CQL engine from the same payload the
 * cqm-execution service receives, and compares results of the two engines
 * for shadow mode.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm;

use OpenEMR\Cqm\Cql\Engine\MeasureCalculator;

final class PhpCqmCalculation
{
    /** The result keys both engines must agree on. */
    private const COMPARED = ['STRAT', 'IPP', 'DENOM', 'NUMER', 'NUMEX', 'DENEX', 'DENEXCEP', 'MSRPOPL', 'MSRPOPLEX', 'observation_values'];

    /**
     * @param string $patientsJson the QDM patients as JSON, as sent to the service
     * @param array<mixed> $measure the measure as sent to the service
     * @param string $valueSetsJson the measure's value sets as JSON
     * @param string $effectiveDate the period start as YYYYMMDDHHmmss
     * @return array<string, array<string, array<string, mixed>>> results by patient id and population set id
     */
    public function calculate(string $patientsJson, array $measure, string $valueSetsJson, string $effectiveDate): array
    {
        $patients = json_decode($patientsJson, true, 512, JSON_THROW_ON_ERROR);
        $valueSets = json_decode($valueSetsJson, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($patients) || !is_array($valueSets)) {
            throw new \UnexpectedValueException('Patients and value sets must be JSON lists');
        }
        $calculator = new MeasureCalculator($measure, self::arrays($valueSets));
        return $calculator->calculate(self::arrays($patients), $effectiveDate);
    }

    /**
     * Where two engines' results differ: one line per patient, population
     * set and population, at most $limit of them. A population the other
     * engine has no result for is a difference.
     *
     * @param array<mixed> $expected the authoritative engine's results
     * @param array<mixed> $actual the other engine's results
     * @return list<array{patient: string, populationSet: string, population: string, expected: mixed, actual: mixed}>
     */
    public static function differences(array $expected, array $actual, int $limit = 25): array
    {
        $differences = [];
        foreach ($expected as $patientId => $sets) {
            if (!is_array($sets)) {
                continue;
            }
            foreach ($sets as $setId => $result) {
                if (!is_array($result)) {
                    continue;
                }
                $otherSets = $actual[$patientId] ?? null;
                $other = is_array($otherSets) ? ($otherSets[$setId] ?? null) : null;
                foreach (self::COMPARED as $key) {
                    if (!array_key_exists($key, $result)) {
                        continue;
                    }
                    $present = is_array($other) && array_key_exists($key, $other);
                    $otherValue = $present ? $other[$key] : null;
                    if (!$present || !self::same($result[$key], $otherValue)) {
                        $differences[] = [
                            'patient' => (string) $patientId,
                            'populationSet' => (string) $setId,
                            'population' => $key,
                            'expected' => $result[$key],
                            'actual' => $otherValue,
                        ];
                        if (count($differences) >= $limit) {
                            return $differences;
                        }
                    }
                }
            }
        }
        return $differences;
    }

    /**
     * How many differences each population set and population has, without
     * the patients they belong to.
     *
     * @param list<array{patient: string, populationSet: string, population: string, expected: mixed, actual: mixed}> $differences
     * @return array<string, array<string, int>>
     */
    public static function countByPopulation(array $differences): array
    {
        $counts = [];
        foreach ($differences as $difference) {
            $set = $difference['populationSet'];
            $population = $difference['population'];
            $counts[$set][$population] = ($counts[$set][$population] ?? 0) + 1;
        }
        return $counts;
    }

    /**
     * Strict equality, except that an integer and a float of the same value
     * match (JSON decoding can give either).
     */
    private static function same(mixed $a, mixed $b): bool
    {
        if ((is_int($a) || is_float($a)) && (is_int($b) || is_float($b))) {
            return (float) $a === (float) $b;
        }
        if (is_array($a) && is_array($b)) {
            if (array_keys($a) !== array_keys($b)) {
                return false;
            }
            foreach ($a as $key => $value) {
                if (!self::same($value, $b[$key])) {
                    return false;
                }
            }
            return true;
        }
        return $a === $b;
    }

    /**
     * @param array<mixed> $values
     * @return list<array<mixed>>
     */
    private static function arrays(array $values): array
    {
        return array_values(array_filter($values, is_array(...)));
    }
}
