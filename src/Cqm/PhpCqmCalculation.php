<?php

/**
 * Calculates a measure with the PHP CQL engine from the JSON payload that
 * the cqm-execution service took: QDM patients, the measure and its value
 * sets.
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
     * @param array<mixed> $values
     * @return list<array<mixed>>
     */
    private static function arrays(array $values): array
    {
        return array_values(array_filter($values, is_array(...)));
    }
}
