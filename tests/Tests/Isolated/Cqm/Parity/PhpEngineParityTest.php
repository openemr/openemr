<?php

/**
 * Runs the PHP CQL engine, through the entry point eCQM reporting uses,
 * over every parity fixture and requires the results cqm-execution
 * recorded: each patient's population counts, observation values and the
 * final outcome of every statement, for every population set and
 * stratification.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Parity;

use OpenEMR\Cqm\PhpCqmCalculation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhpEngineParityTest extends TestCase
{
    private const REPORTING_YEAR = '2025';

    #[DataProvider('fixtureProvider')]
    public function testPhpEngineMatchesCqmExecution(string $path): void
    {
        $fixture = ParityFixture::fromFile($path);
        if ($fixture->engineError !== null) {
            $this->markTestSkipped("cqm-execution cannot run {$fixture->measure}, so there is nothing to match.");
        }
        $dir = self::measuresDir() . "/{$fixture->measure}";
        $results = (new PhpCqmCalculation())->calculate(
            json_encode($fixture->patients, JSON_THROW_ON_ERROR),
            self::readJson("$dir/{$fixture->measure}.json"),
            self::readFile("$dir/value_sets.json"),
            $fixture->reportingYear . '0101000000'
        );

        $differences = [];
        foreach ($fixture->results as $patientId => $byKey) {
            foreach ($byKey as $key => $expected) {
                $actual = $results[$patientId][$key] ?? null;
                if (!is_array($actual)) {
                    $differences[] = "$patientId $key: no result";
                    continue;
                }
                // The fields of cqm-models' IndividualResult the service returned
                if (($actual['patient_id'] ?? null) !== $patientId || !array_key_exists('measure_id', $actual) || !is_array($actual['observation_values'] ?? null) || ($actual['state'] ?? null) !== 'complete') {
                    $differences[] = "$patientId $key: result fields differ from the service's";
                }
                foreach ($expected->populations as $population => $count) {
                    if (($actual[$population] ?? null) !== $count) {
                        $differences[] = "$patientId $key $population: expected $count, got " . json_encode($actual[$population] ?? null);
                    }
                }
                $actualObservations = $actual['observation_values'] ?? [];
                if (($expected->observationValues !== [] || $actualObservations !== []) && $actualObservations != $expected->observationValues) {
                    $differences[] = "$patientId $key observation_values differ";
                }
                $finals = [];
                foreach (is_array($actual['statement_results'] ?? null) ? $actual['statement_results'] : [] as $statement) {
                    if (!is_array($statement)) {
                        continue;
                    }
                    $library = $statement['library_name'] ?? null;
                    $name = $statement['statement_name'] ?? null;
                    if (is_string($library) && is_string($name)) {
                        $finals[$library][$name] = $statement['final'] ?? null;
                    }
                }
                foreach ($expected->statements as $library => $byName) {
                    foreach ($byName as $name => $final) {
                        $got = $finals[$library][$name] ?? null;
                        if ($got !== $final->value) {
                            $differences[] = "$patientId $key $library.\"$name\": expected {$final->value}, got " . json_encode($got);
                        }
                    }
                }
            }
        }
        $this->assertSame([], array_slice($differences, 0, 20), "The PHP engine differs from cqm-execution on {$fixture->measure}");
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fixtureProvider(): array
    {
        $cases = [];
        foreach (glob(__DIR__ . '/fixtures/' . self::REPORTING_YEAR . '/*.json') ?: [] as $path) {
            $cases[basename($path, '.json')] = [$path];
        }
        return $cases;
    }

    /**
     * @return array<mixed>
     */
    private static function readJson(string $path): array
    {
        $data = json_decode(self::readFile($path), true, 512, JSON_THROW_ON_ERROR);
        return is_array($data) ? $data : throw new \RuntimeException("Unreadable $path");
    }

    private static function readFile(string $path): string
    {
        $json = file_get_contents($path);
        return $json === false ? throw new \RuntimeException("Unreadable $path") : $json;
    }

    private static function measuresDir(): string
    {
        return dirname(__DIR__, 5) . '/vendor/openemr/oe-cqm-parsers/' . self::REPORTING_YEAR . '_reporting_period/json_measures';
    }
}
