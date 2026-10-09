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
     * set and population, at most $limit of them.
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
                    $otherValue = is_array($other) ? ($other[$key] ?? null) : null;
                    if ($result[$key] != $otherValue) {
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
     * @param array<mixed> $values
     * @return list<array<mixed>>
     */
    private static function arrays(array $values): array
    {
        return array_values(array_filter($values, is_array(...)));
    }
}
