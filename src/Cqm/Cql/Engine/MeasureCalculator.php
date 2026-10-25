<?php

/**
 * Calculates a QDM measure for patients, ported from cqm-execution 4.4.3's
 * Calculator with its CalculatorHelpers and ResultsHelpers: the
 * Measurement Period from the options, every population set and
 * stratification, population counts with their exclusion rules, and
 * statement results.
 *
 * The results have the shape the cqm-execution service returned (as
 * cqm-models IndividualResult documents): by patient id, then population set
 * or stratification id, the population counts, observation_values, the
 * population relevance, the statement results with their relevance and
 * final outcome, patient_id, measure_id and state. Pretty-printed statement
 * values and clause results are not included; nothing in OpenEMR reads them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Engine;

use OpenEMR\Cqm\Cql\Elm\ElmRepository;
use OpenEMR\Cqm\Cql\Qdm\QdmObject;
use OpenEMR\Cqm\Cql\Qdm\QdmPatient;
use OpenEMR\Cqm\Cql\Types\Code;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\Interval;
use OpenEMR\Cqm\Cql\Types\Tuple;

final readonly class MeasureCalculator
{
    private const POPULATIONS = ['STRAT', 'IPP', 'DENOM', 'NUMER', 'NUMEX', 'DENEX', 'DENEXCEP', 'MSRPOPL', 'MSRPOPLEX', 'OBSERV'];

    /**
     * @param array<mixed> $measure the measure as cqm-execution receives it
     * @param list<array<mixed>> $valueSets the measure's value sets
     */
    public function __construct(
        private array $measure,
        private array $valueSets,
    ) {
    }

    /**
     * @param list<array<mixed>> $patients QDM patients as cqm-execution receives them
     * @param ?string $effectiveDate the period start as YYYYMMDDHHmm[ss] in UTC; the measure's period when null
     * @param ?string $effectiveDateEnd the period end; the end of the start's year when null
     * @param ?CqlDateTime $executionDateTime "now" for the CQL; the current time when null
     * @return array<string, array<string, array<string, mixed>>> results by patient id and population set id
     */
    public function calculate(array $patients, ?string $effectiveDate, ?string $effectiveDateEnd = null, ?CqlDateTime $executionDateTime = null): array
    {
        [$start, $end] = $this->measurementPeriod($effectiveDate, $effectiveDateEnd);
        $parameters = ['Measurement Period' => new Interval($start, $end)];
        $executionDateTime ??= CqlDateTime::fromEpochMilliseconds((int) floor(microtime(true) * 1000), 0.0);

        $measure = $this->measure;
        $measure['cql_libraries'] = $this->withObservationFunctions($measure);
        $repository = ElmRepository::fromMeasure($measure);
        $codeService = new CodeService($this->valueSets);
        $populationSets = [...$this->populationSets(), ...$this->stratificationsAsPopulationSets()];

        $results = [];
        foreach ($patients as $patientData) {
            $patient = new QdmPatient($patientData);
            $context = new LibraryContext($repository->main, $repository, $patient, $codeService, $parameters, $executionDateTime);
            $statementResults = [];
            foreach ($repository->main->expressions as $name => $def) {
                if (($def['context'] ?? null) === 'Patient') {
                    $statementResults[$name] = Evaluator::statement($context, (string) $name);
                }
            }
            $localIds = $context->allLocalIds();
            foreach ($populationSets as $populationSet) {
                $populationResults = $this->patientPopulationValues($populationSet, $statementResults);
                $populationResults = self::handlePopulationValues($populationResults, $this->scoring());
                $relevance = self::populationRelevance($populationResults, $this->scoring());
                $result = $populationResults;
                // cqm-models' IndividualResult defaults observation_values to an empty list.
                $result['observation_values'] ??= [];
                $result['population_relevance'] = $relevance;
                $result['statement_results'] = $this->statementResults($localIds, $this->statementRelevance($relevance, $populationSet));
                $result['patient_id'] = $patient->id;
                $result['measure_id'] = $measure['_id'] ?? null;
                $result['state'] = 'complete';
                $results[$patient->id][self::text($populationSet['population_set_id'] ?? '')] = $result;
            }
        }
        return $results;
    }

    /**
     * @return array{CqlDateTime, CqlDateTime}
     */
    private function measurementPeriod(?string $effectiveDate, ?string $effectiveDateEnd): array
    {
        if ($effectiveDate !== null) {
            $start = self::parseTimeString($effectiveDate);
            $end = $effectiveDateEnd !== null
                ? self::parseTimeString($effectiveDateEnd)
                : $start->add(1, \OpenEMR\Cqm\Cql\Types\Precision::Year)?->add(-1, \OpenEMR\Cqm\Cql\Types\Precision::Second);
        } else {
            $start = self::parseTimeString(self::text(self::at($this->measure, 'measure_period', 'low', 'value')));
            $end = self::parseTimeString(self::text(self::at($this->measure, 'measure_period', 'high', 'value')));
        }
        return [$start, $end ?? throw new \UnexpectedValueException('Measurement Period has no end')];
    }

    /**
     * moment.utc(value, 'YYYYMDDHHmm'): year, month (one or two digits),
     * day, hours and minutes, at millisecond precision in UTC.
     */
    private static function parseTimeString(string $value): CqlDateTime
    {
        if (preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})?(\d{2})?/', $value, $m) !== 1) {
            throw new \UnexpectedValueException("Unreadable time $value");
        }
        return new CqlDateTime((int) $m[1], (int) $m[2], (int) $m[3], (int) ($m[4] ?? 0), (int) ($m[5] ?? 0), 0, 0, 0.0);
    }

    private function scoring(): string
    {
        return self::text($this->measure['measure_scoring'] ?? '');
    }

    /**
     * The measure's libraries, with cqm-execution's generated observation
     * functions added to the main one.
     *
     * @param array<mixed> $measure
     * @return list<mixed>
     */
    private function withObservationFunctions(array $measure): array
    {
        $libraries = is_array($measure['cql_libraries'] ?? null) ? array_values($measure['cql_libraries']) : [];
        $observations = $this->populationSets()[0]['observations'] ?? [];
        if (!is_array($observations) || $observations === []) {
            return $libraries;
        }
        if (($measure['calculation_method'] ?? null) !== 'PATIENT' || ($measure['composite'] ?? false)) {
            throw new \LogicException('Episode-of-care measures are not supported');
        }
        $result = [];
        foreach ($libraries as $library) {
            if (is_array($library) && ($library['library_name'] ?? null) === ($measure['main_cql_library'] ?? null)) {
                $defs = self::at($library, 'elm', 'library', 'statements', 'def');
                $defs = is_array($defs) ? array_values($defs) : [];
                foreach (array_values($observations) as $index => $observation) {
                    $function = self::text(self::at($observation, 'observation_function', 'statement_name'));
                    $defs[] = [
                        'name' => "obs_func_{$function}_{$index}",
                        'context' => 'Patient',
                        'accessLevel' => 'Public',
                        'expression' => ['name' => $function, 'type' => 'FunctionRef', 'operand' => []],
                    ];
                }
                $elm = is_array($library['elm'] ?? null) ? $library['elm'] : [];
                $elmLibrary = is_array($elm['library'] ?? null) ? $elm['library'] : [];
                $statements = is_array($elmLibrary['statements'] ?? null) ? $elmLibrary['statements'] : [];
                $statements['def'] = $defs;
                $elmLibrary['statements'] = $statements;
                $elm['library'] = $elmLibrary;
                $library['elm'] = $elm;
            }
            $result[] = $library;
        }
        return $result;
    }

    /**
     * @return list<array<mixed>>
     */
    private function populationSets(): array
    {
        $sets = [];
        foreach (is_array($this->measure['population_sets'] ?? null) ? $this->measure['population_sets'] : [] as $set) {
            if (is_array($set)) {
                $sets[] = $set;
            }
        }
        return $sets;
    }

    /**
     * Each stratification as a copy of its population set with a STRAT
     * population.
     *
     * @return list<array<mixed>>
     */
    private function stratificationsAsPopulationSets(): array
    {
        $sets = [];
        foreach ($this->populationSets() as $set) {
            foreach (is_array($set['stratifications'] ?? null) ? $set['stratifications'] : [] as $stratification) {
                if (!is_array($stratification)) {
                    continue;
                }
                $copy = $set;
                $copy['population_set_id'] = $stratification['stratification_id'] ?? null;
                $populations = is_array($set['populations'] ?? null) ? $set['populations'] : [];
                $copy['populations'] = ['STRAT' => $stratification['statement'] ?? null] + $populations;
                unset($copy['stratifications']);
                $sets[] = $copy;
            }
        }
        return $sets;
    }

    /**
     * @param array<mixed> $populationSet
     * @return array<string, int>
     */
    private static function populationCodes(array $populationSet): array
    {
        $codes = [];
        foreach (is_array($populationSet['populations'] ?? null) ? $populationSet['populations'] : [] as $code => $population) {
            if (in_array($code, self::POPULATIONS, true) && is_array($population)) {
                $codes[(string) $code] = 0;
            }
        }
        return $codes;
    }

    /**
     * @param array<mixed> $populationSet
     * @param array<string, mixed> $statementResults
     * @return array<string, mixed>
     */
    private function patientPopulationValues(array $populationSet, array $statementResults): array
    {
        $results = [];
        $populations = is_array($populationSet['populations'] ?? null) ? $populationSet['populations'] : [];
        foreach (array_keys(self::populationCodes($populationSet)) as $code) {
            $population = $populations[$code] ?? null;
            $statement = is_array($population) ? self::text($population['statement_name'] ?? '') : '';
            $value = $statementResults[$statement] ?? null;
            $results[$code] = match (true) {
                is_array($value) && $value !== [] => count($value),
                $value === true => 1,
                default => 0,
            };
        }
        $observations = is_array($populationSet['observations'] ?? null) ? $populationSet['observations'] : [];
        if ($observations !== []) {
            $results['observation_values'] = [];
            foreach (array_values($observations) as $index => $observation) {
                $function = is_array($observation) ? self::text(self::at($observation, 'observation_function', 'statement_name')) : '';
                $value = $statementResults["obs_func_{$function}_{$index}"] ?? null;
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $results['observation_values'][] = $item instanceof Tuple ? ($item->elements['observation'] ?? null) : $item;
                    }
                } else {
                    $results['observation_values'][] = $value;
                }
            }
        }
        return $results;
    }

    /**
     * cqm-execution's population rules: nothing counts outside the initial
     * population or a zero stratification, and exclusions clear the
     * populations they exclude from.
     *
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    public static function handlePopulationValues(array $results, string $scoring): array
    {
        $zero = static fn (string $code): bool => array_key_exists($code, $results) && $results[$code] === 0;
        $handled = $results;
        if ($scoring === 'RATIO') {
            if (isset($results['STRAT']) && $zero('STRAT')) {
                $handled = self::clearPopulations($handled, []);
            } elseif ($zero('IPP')) {
                $handled = self::clearPopulations($handled, ['STRAT']);
            }
            $observations = is_array($handled['observation_values'] ?? null) ? $handled['observation_values'] : null;
            if ($zero('DENOM')) {
                if (array_key_exists('DENEX', $results)) {
                    $handled['DENEX'] = 0;
                }
                if ($observations !== null) {
                    $observations[1] = $observations[0] ?? null;
                    $observations[0] = null;
                }
            }
            if (isset($results['DENEX']) && !$zero('DENEX') && $results['DENEX'] >= ($results['DENOM'] ?? null) && $observations !== null) {
                $observations[0] = null;
            }
            if ($zero('NUMER')) {
                if (array_key_exists('NUMEX', $results)) {
                    $handled['NUMEX'] = 0;
                }
                if ($observations !== null) {
                    $observations[1] = null;
                }
            }
            if (isset($results['NUMER']) && !$zero('NUMEX') && ($results['NUMEX'] ?? null) >= $results['NUMER'] && $observations !== null) {
                $observations[1] = null;
            }
            if ($observations !== null) {
                ksort($observations);
                $handled['observation_values'] = array_values($observations);
            }
            return $handled;
        }
        if (isset($results['STRAT']) && $zero('STRAT')) {
            return self::clearPopulations($handled, []);
        }
        if ($zero('IPP')) {
            return self::clearPopulations($handled, ['STRAT']);
        }
        if ($zero('DENOM') || $zero('MSRPOPL')) {
            return self::resetPopulations($handled, ['DENEX', 'DENEXCEP', 'NUMER', 'NUMEX', 'MSRPOPLEX', 'observation_values']);
        }
        if (isset($results['DENEX']) && !$zero('DENEX') && $results['DENEX'] >= ($results['DENOM'] ?? null)) {
            return self::resetPopulations($handled, ['NUMER', 'NUMEX', 'DENEXCEP', 'observation_values']);
        }
        if (isset($results['MSRPOPLEX']) && !$zero('MSRPOPLEX')) {
            return self::resetPopulations($handled, ['observation_values']);
        }
        if ($zero('NUMER')) {
            return self::resetPopulations($handled, ['NUMEX']);
        }
        return self::resetPopulations($handled, ['DENEXCEP']);
    }

    /**
     * Zeroes every population but those kept.
     *
     * @param array<string, mixed> $values
     * @param list<string> $keep
     * @return array<string, mixed>
     */
    private static function clearPopulations(array $values, array $keep): array
    {
        foreach (array_keys($values) as $key) {
            if (!in_array($key, $keep, true)) {
                $values[$key] = $key === 'observation_values' ? [] : 0;
            }
        }
        return $values;
    }

    /**
     * Zeroes the given populations where the result has them.
     *
     * @param array<string, mixed> $values
     * @param list<string> $codes
     * @return array<string, mixed>
     */
    private static function resetPopulations(array $values, array $codes): array
    {
        foreach ($codes as $code) {
            if (array_key_exists($code, $values)) {
                $values[$code] = $code === 'observation_values' ? [] : 0;
            }
        }
        return $values;
    }

    /**
     * Which populations count toward the result (cqm-execution's
     * buildPopulationRelevanceMap).
     *
     * @param array<string, mixed> $result
     * @return array<string, bool>
     */
    public static function populationRelevance(array $result, string $scoring): array
    {
        $hidden = [];
        $inner = ['NUMER', 'NUMEX', 'DENOM', 'DENEX', 'DENEXCEP', 'MSRPOPL', 'MSRPOPLEX', 'observation_values'];
        if (array_key_exists('STRAT', $result) && $result['STRAT'] === 0) {
            $hidden = [...$hidden, 'IPP', ...$inner];
        }
        if (($result['IPP'] ?? null) === 0) {
            $hidden = [...$hidden, ...$inner];
        }
        if (array_key_exists('DENOM', $result) && $result['DENOM'] === 0) {
            $hidden = [...$hidden, ...($scoring !== 'RATIO' ? ['NUMER', 'NUMEX', 'DENEX', 'DENEXCEP'] : ['DENEX', 'DENEXCEP'])];
        }
        if (array_key_exists('DENEX', $result) && $result['DENEX'] !== null && $result['DENEX'] >= ($result['DENOM'] ?? null)) {
            $hidden = [...$hidden, ...($scoring !== 'RATIO' ? ['NUMER', 'NUMEX', 'DENEXCEP'] : ['DENEXCEP'])];
        }
        if (array_key_exists('NUMER', $result) && $result['NUMER'] === 0) {
            $hidden[] = 'NUMEX';
        }
        if (array_key_exists('NUMER', $result) && $result['NUMER'] >= 1) {
            $hidden[] = 'DENEXCEP';
        }
        if (array_key_exists('MSRPOPL', $result) && $result['MSRPOPL'] === 0) {
            $hidden[] = 'observation_values';
            $hidden[] = 'MSRPOPLEX';
        }
        if (array_key_exists('MSRPOPLEX', $result) && $result['MSRPOPLEX'] !== null && $result['MSRPOPLEX'] >= ($result['MSRPOPL'] ?? null)) {
            $hidden[] = 'observation_values';
        }
        $shown = [];
        foreach (array_keys($result) as $code) {
            $shown[$code] = !in_array($code, $hidden, true);
        }
        return $shown;
    }

    /**
     * Statement relevance ('NA', 'TRUE', 'FALSE') by library and statement:
     * a population's statement and everything it depends on.
     *
     * @param array<string, bool> $populationRelevance
     * @param array<mixed> $populationSet
     * @return array<string, array<string, string>>
     */
    private function statementRelevance(array $populationRelevance, array $populationSet): array
    {
        $relevance = [];
        foreach ($this->libraries() as $library) {
            $name = self::text($library['library_name'] ?? '');
            $relevance[$name] = [];
            foreach (self::dependencies($library) as $statement) {
                $relevance[$name][self::text($statement['statement_name'] ?? '')] = 'NA';
            }
        }
        $main = self::text($this->measure['main_cql_library'] ?? '');
        if ($this->measure['calculate_sdes'] ?? false) {
            foreach (is_array($populationSet['supplemental_data_elements'] ?? null) ? $populationSet['supplemental_data_elements'] : [] as $sde) {
                $this->markRelevant($relevance, $main, is_array($sde) ? self::text($sde['statement_name'] ?? '') : '', true);
            }
        }
        $populations = is_array($populationSet['populations'] ?? null) ? $populationSet['populations'] : [];
        foreach ($populationRelevance as $population => $relevant) {
            if ($population === 'observation_values') {
                foreach (is_array($populationSet['observations'] ?? null) ? $populationSet['observations'] : [] as $observation) {
                    $this->markRelevant($relevance, $main, is_array($observation) ? self::text(self::at($observation, 'observation_function', 'statement_name')) : '', $relevant);
                }
            } else {
                $statement = $populations[$population] ?? null;
                $this->markRelevant($relevance, $main, is_array($statement) ? self::text($statement['statement_name'] ?? '') : '', $relevant);
            }
        }
        return $relevance;
    }

    /**
     * @param array<string, array<string, string>> $relevance
     */
    private function markRelevant(array &$relevance, string $libraryName, string $statementName, bool $relevant): void
    {
        $current = $relevance[$libraryName][$statementName] ?? null;
        if ($current !== 'NA' && $current !== 'FALSE') {
            return;
        }
        $relevance[$libraryName][$statementName] = $relevant ? 'TRUE' : 'FALSE';
        foreach ($this->libraries() as $library) {
            if (($library['library_name'] ?? null) !== $libraryName) {
                continue;
            }
            foreach (self::dependencies($library) as $statement) {
                if (($statement['statement_name'] ?? null) !== $statementName) {
                    continue;
                }
                foreach (is_array($statement['statement_references'] ?? null) ? $statement['statement_references'] : [] as $reference) {
                    if (is_array($reference)) {
                        $this->markRelevant(
                            $relevance,
                            self::text($reference['library_name'] ?? ''),
                            self::text($reference['statement_name'] ?? ''),
                            $relevant,
                        );
                    }
                }
                return;
            }
            return;
        }
    }

    /**
     * Each statement's final outcome: NA when not relevant or a
     * supplemental data element, UNHIT when not reached, else TRUE or
     * FALSE by its result.
     *
     * @param array<string, array<string, mixed>> $localIds
     * @param array<string, array<string, string>> $relevance
     * @return list<array<string, string>>
     */
    private function statementResults(array $localIds, array $relevance): array
    {
        $results = [];
        $sets = $this->populationSets();
        foreach ($this->libraries() as $library) {
            $libraryName = self::text($library['library_name'] ?? '');
            $defs = self::at($library, 'elm', 'library', 'statements', 'def');
            $defs = is_array($defs) ? $defs : [];
            foreach (self::dependencies($library) as $statement) {
                $name = self::text($statement['statement_name'] ?? '');
                $statementRelevance = $relevance[$libraryName][$name] ?? 'NA';
                $isSupplemental = self::listsStatement($sets, 'supplemental_data_elements', $name);
                $isRiskAdjustment = self::listsStatement($sets, 'risk_adjustment_variables', $name);
                if ((!($this->measure['calculate_sdes'] ?? false) && $isSupplemental)
                    || (!($this->measure['calculate_ravs'] ?? false) && $isRiskAdjustment)
                    || $statementRelevance === 'NA') {
                    $final = 'NA';
                } elseif ($statementRelevance === 'FALSE' || !isset($localIds[$libraryName])) {
                    $final = 'UNHIT';
                } else {
                    $localId = null;
                    foreach ($defs as $def) {
                        if (is_array($def) && ($def['name'] ?? null) === $name) {
                            $localId = $def['localId'] ?? null;
                            break;
                        }
                    }
                    $raw = is_string($localId) ? ($localIds[$libraryName][$localId] ?? null) : null;
                    $final = self::resultPasses($raw) ? 'TRUE' : 'FALSE';
                }
                $results[] = [
                    'library_name' => $libraryName,
                    'statement_name' => $name,
                    'relevance' => $statementRelevance,
                    'final' => $final,
                ];
            }
        }
        return $results;
    }

    /** cqm-execution's doesResultPass. */
    private static function resultPasses(mixed $result): bool
    {
        return match (true) {
            $result === true => true,
            $result === false, $result === null => false,
            is_array($result) => $result !== [] && !(count($result) === 1 && $result[0] === null),
            $result instanceof Interval => true,
            $result instanceof Code => $result->code !== '',
            default => true,
        };
    }

    /**
     * @param list<array<mixed>> $sets
     */
    private static function listsStatement(array $sets, string $key, string $name): bool
    {
        foreach ($sets as $set) {
            foreach (is_array($set[$key] ?? null) ? $set[$key] : [] as $entry) {
                if (is_array($entry) && ($entry['statement_name'] ?? null) === $name) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return list<array<mixed>>
     */
    private function libraries(): array
    {
        $libraries = [];
        foreach (is_array($this->measure['cql_libraries'] ?? null) ? $this->measure['cql_libraries'] : [] as $library) {
            if (is_array($library)) {
                $libraries[] = $library;
            }
        }
        return $libraries;
    }

    /**
     * @param array<mixed> $library
     * @return list<array<mixed>>
     */
    private static function dependencies(array $library): array
    {
        $statements = [];
        foreach (is_array($library['statement_dependencies'] ?? null) ? $library['statement_dependencies'] : [] as $statement) {
            if (is_array($statement)) {
                $statements[] = $statement;
            }
        }
        return $statements;
    }

    /** The value at a path of array keys, or null where the path breaks off. */
    private static function at(mixed $value, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (!is_array($value)) {
                return null;
            }
            $value = $value[$key] ?? null;
        }
        return $value;
    }

    private static function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /** Whether a value is a QDM data element (an episode, for episode-of-care measures). */
    public static function isDataElement(mixed $value): bool
    {
        return $value instanceof QdmObject && $value->isDataElement;
    }
}
