<?php

/**
 * Checks the CQM parity fixtures against the installed measures, so the
 * reference results stay usable for the PHP engine: every installed measure
 * has a fixture, each fixture belongs to the measure version installed, and
 * the recorded results are complete and internally consistent.
 *
 * A failure after updating openemr/oe-cqm-parsers means the fixtures need to
 * be captured again with capture.js.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Cqm\Parity;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ParityFixturesTest extends TestCase
{
    /**
     * Population => the population it is a subset of, for proportion and
     * continuous variable measures. Ratio measures draw the numerator from the
     * initial population instead; see parentOf().
     */
    private const PARENT = [
        'DENOM' => 'IPP',
        'NUMER' => 'DENOM',
        'NUMEX' => 'NUMER',
        'DENEX' => 'DENOM',
        'DENEXCEP' => 'DENOM',
        'MSRPOPL' => 'IPP',
        'MSRPOPLEX' => 'MSRPOPL',
    ];

    #[DataProvider('yearProvider')]
    public function testEveryInstalledMeasureHasAFixture(string $year): void
    {
        $installed = array_map(basename(...), glob(self::measuresDir($year) . '/*', GLOB_ONLYDIR) ?: []);
        $captured = array_map(
            static fn (string $path): string => basename($path, '.json'),
            glob(self::fixturesDir($year) . '/*.json') ?: []
        );
        sort($installed);
        sort($captured);

        $this->assertNotEmpty($installed, 'Install openemr/oe-cqm-parsers with Composer to run this test.');
        $this->assertSame($installed, $captured, 'The parity fixtures do not match the installed measures; capture them again.');
    }

    #[DataProvider('fixtureProvider')]
    public function testFixtureBelongsToTheInstalledMeasure(string $path): void
    {
        $fixture = ParityFixture::fromFile($path);
        $measure = self::installedMeasure($fixture->reportingYear, $fixture->measure);

        $this->assertSame(basename(dirname($path)), $fixture->reportingYear, 'The fixture is filed under another reporting year.');
        $this->assertSame($measure['hqmf_id'] ?? null, $fixture->hqmfId, 'The fixture was captured from another version of the measure.');
        if ($fixture->engineError !== null) {
            $this->assertSame([], $fixture->patients, 'A measure the engine cannot run has no reference results.');
            $this->assertSame([], $fixture->results);
            return;
        }
        $this->assertNotEmpty($fixture->patients);
    }

    #[DataProvider('fixtureProvider')]
    public function testEveryPatientHasAResultForEveryPopulationSet(string $path): void
    {
        $fixture = ParityFixture::fromFile($path);
        $expectedKeys = self::resultKeys(self::installedMeasure($fixture->reportingYear, $fixture->measure));

        $patientIds = array_map(ParityFixture::patientId(...), $fixture->patients);
        $this->assertCount(count($patientIds), array_unique($patientIds), 'Patient ids repeat.');
        $this->assertEqualsCanonicalizing($patientIds, array_keys($fixture->results));

        foreach ($fixture->results as $patientId => $byKey) {
            $keys = array_keys($byKey);
            sort($keys);
            $this->assertSame($expectedKeys, $keys, "Patient $patientId is missing population set results.");
        }
    }

    /**
     * Seeds exist because random patients miss a measure's populations, so a
     * seeded fixture must carry every seed and reach its initial population.
     */
    #[DataProvider('seedProvider')]
    public function testSeedsReachTheirMeasureInitialPopulation(string $year, string $measure, int $seedCount): void
    {
        $fixture = ParityFixture::fromFile(self::fixturesDir($year) . "/$measure.json");

        $seeds = array_filter(
            $fixture->patients,
            static fn (array $patient): bool => str_contains(self::pubpid($patient), '-seed-')
        );
        $this->assertCount($seedCount, $seeds, "$measure is missing seed patients; capture it again.");

        $inPopulation = 0;
        foreach ($seeds as $seed) {
            foreach ($fixture->results[ParityFixture::patientId($seed)] as $result) {
                if (($result->populations['IPP'] ?? 0) > 0) {
                    $inPopulation++;
                    break;
                }
            }
        }
        $this->assertSame($seedCount, $inPopulation, "Every $measure seed should reach the initial population.");
    }

    #[DataProvider('fixtureProvider')]
    public function testPopulationResultsRespectTheHierarchy(string $path): void
    {
        $fixture = ParityFixture::fromFile($path);
        $scoring = self::installedMeasure($fixture->reportingYear, $fixture->measure)['measure_scoring'] ?? null;
        $this->assertIsString($scoring);

        foreach ($fixture->results as $patientId => $byKey) {
            foreach ($byKey as $key => $result) {
                $where = "$fixture->measure $key patient $patientId";
                $this->assertNotEmpty($result->statements, "$where has no statement results.");
                foreach ($result->populations as $population => $count) {
                    // Every installed measure is flagged patient based, yet
                    // cqm-execution counts the members of a population that
                    // evaluates to a list (the qualifying encounters, say), so
                    // a count can exceed 1.
                    $this->assertGreaterThanOrEqual(0, $count, "$where: $population");
                    $parent = self::parentOf($population, $scoring);
                    // Membership, not magnitude: one population may count list
                    // members while its parent is a boolean counted once.
                    if ($count > 0 && $parent !== null && array_key_exists($parent, $result->populations)) {
                        $this->assertGreaterThan(0, $result->populations[$parent], "$where: $population outside $parent");
                    }
                }
                if (($result->populations['STRAT'] ?? 1) === 0) {
                    $this->assertSame(0, $result->populations['IPP'] ?? 0, "$where: IPP outside the stratum");
                }
            }
        }
    }

    /**
     * The reporting years that have fixtures.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function yearProvider(): array
    {
        $cases = [];
        foreach (glob(__DIR__ . '/fixtures/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $cases[basename($dir)] = [basename($dir)];
        }
        return $cases;
    }

    /**
     * Every fixture of every reporting year, as "2025/CMS122v13".
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fixtureProvider(): array
    {
        $cases = [];
        foreach (glob(__DIR__ . '/fixtures/*/*.json') ?: [] as $path) {
            $cases[basename(dirname($path)) . '/' . basename($path, '.json')] = [$path];
        }
        return $cases;
    }

    /**
     * @return array<string, array{string, string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function seedProvider(): array
    {
        $cases = [];
        foreach (glob(__DIR__ . '/seeds/*/*.json') ?: [] as $path) {
            $year = basename(dirname($path));
            $json = file_get_contents($path);
            $data = $json === false ? null : json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $patients = is_array($data) ? ($data['patients'] ?? null) : null;
            if (!is_array($patients)) {
                throw new \UnexpectedValueException("Seed file $path has no patient list");
            }
            $cases[$year . '/' . basename($path, '.json')] = [$year, basename($path, '.json'), count($patients)];
        }
        return $cases;
    }

    /**
     * @param array<mixed> $patient
     */
    private static function pubpid(array $patient): string
    {
        $extended = $patient['extendedData'] ?? null;
        $pubpid = is_array($extended) ? ($extended['pubpid'] ?? null) : null;
        return is_string($pubpid) ? $pubpid : '';
    }

    private static function parentOf(string $population, string $scoring): ?string
    {
        if ($population === 'NUMER' && $scoring === 'RATIO') {
            return 'IPP';
        }
        return self::PARENT[$population] ?? null;
    }

    /**
     * The keys cqm-execution files results under: each population set's id
     * and each of its stratifications' ids.
     *
     * @param array<mixed> $measure
     * @return list<string>
     */
    private static function resultKeys(array $measure): array
    {
        $keys = [];
        $populationSets = $measure['population_sets'] ?? [];
        if (!is_array($populationSets)) {
            throw new \UnexpectedValueException('The measure has no population sets');
        }
        foreach ($populationSets as $populationSet) {
            if (!is_array($populationSet) || !is_string($populationSet['population_set_id'] ?? null)) {
                throw new \UnexpectedValueException('A population set has no id');
            }
            $keys[] = $populationSet['population_set_id'];
            $stratifications = $populationSet['stratifications'] ?? [];
            if (!is_array($stratifications)) {
                throw new \UnexpectedValueException('Malformed stratifications');
            }
            foreach ($stratifications as $stratification) {
                if (!is_array($stratification) || !is_string($stratification['stratification_id'] ?? null)) {
                    throw new \UnexpectedValueException('A stratification has no id');
                }
                $keys[] = $stratification['stratification_id'];
            }
        }
        sort($keys);
        return $keys;
    }

    /**
     * @return array<mixed>
     */
    private static function installedMeasure(string $year, string $name): array
    {
        $path = self::measuresDir($year) . "/$name/$name.json";
        $json = file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException("Measure $name is not installed");
        }
        $measure = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($measure)) {
            throw new \UnexpectedValueException("Measure $name is not an object");
        }
        return $measure;
    }

    private static function measuresDir(string $year): string
    {
        return dirname(__DIR__, 5) . "/vendor/openemr/oe-cqm-parsers/{$year}_reporting_period/json_measures";
    }

    private static function fixturesDir(string $year): string
    {
        return __DIR__ . "/fixtures/$year";
    }
}
