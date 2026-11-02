<?php

/**
 * One patient's reference result for one population set or stratification.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Parity;

final readonly class PopulationSetResult
{
    public const POPULATIONS = ['STRAT', 'IPP', 'DENOM', 'NUMER', 'NUMEX', 'DENEX', 'DENEXCEP', 'MSRPOPL', 'MSRPOPLEX', 'OBSERV'];

    /**
     * @param array<string, int> $populations population code => count, for the populations the measure defines
     * @param list<int|float|null> $observationValues
     * @param array<string, array<string, StatementFinal>> $statements library name => statement name => final
     */
    public function __construct(
        public array $populations,
        public array $observationValues,
        public array $statements,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $populations = [];
        foreach (self::POPULATIONS as $population) {
            if (!array_key_exists($population, $data)) {
                continue;
            }
            $count = $data[$population];
            if (!is_int($count)) {
                throw new \UnexpectedValueException("Population $population is not an integer");
            }
            $populations[$population] = $count;
        }

        $observationValues = [];
        $observations = $data['observation_values'] ?? [];
        if (!is_array($observations)) {
            throw new \UnexpectedValueException('observation_values is not a list');
        }
        foreach ($observations as $value) {
            if (!is_int($value) && !is_float($value) && $value !== null) {
                throw new \UnexpectedValueException('An observation value is not a number');
            }
            $observationValues[] = $value;
        }

        if (array_key_exists('episode_results', $data)) {
            // Every supported measure is patient based. An episode-of-care
            // measure needs this class extended before its fixture can load.
            throw new \UnexpectedValueException('Episode results are not supported');
        }

        $statements = [];
        $libraries = $data['statements'] ?? null;
        if (!is_array($libraries)) {
            throw new \UnexpectedValueException('statements is missing');
        }
        foreach ($libraries as $library => $byName) {
            if (!is_string($library) || !is_array($byName)) {
                throw new \UnexpectedValueException('statements is not keyed by library');
            }
            foreach ($byName as $name => $final) {
                if (!is_string($name) || !is_string($final)) {
                    throw new \UnexpectedValueException("A statement in $library is malformed");
                }
                $statements[$library][$name] = StatementFinal::from($final);
            }
        }

        return new self($populations, $observationValues, $statements);
    }
}
