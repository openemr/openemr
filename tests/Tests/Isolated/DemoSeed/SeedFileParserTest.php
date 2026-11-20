<?php

/**
 * Isolated tests for the demo seed file parser.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\DemoSeed;

use OpenEMR\DemoSeed\SeedFile\Gender;
use OpenEMR\DemoSeed\SeedFile\SeedFileException;
use OpenEMR\DemoSeed\SeedFile\SeedFileParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SeedFileParserTest extends TestCase
{
    private const MINIMAL = <<<'YAML'
        version: 1
        seed: 42
        synthea:
          count: 5
          seed: 7
          clinician_seed: 8
          reference_date: 2026-11-19
          years_of_history: 5
          plain_names: true
        YAML;

    public function testParsesMinimalFile(): void
    {
        $seedFile = (new SeedFileParser())->parse(self::MINIMAL);

        $this->assertSame(42, $seedFile->seed);
        $this->assertSame(5, $seedFile->synthea->count);
        $this->assertSame(7, $seedFile->synthea->seed);
        $this->assertSame(8, $seedFile->synthea->clinicianSeed);
        $this->assertSame('2026-11-19', $seedFile->synthea->referenceDate->format('Y-m-d'));
        $this->assertSame(5, $seedFile->synthea->yearsOfHistory);
        $this->assertTrue($seedFile->synthea->plainNames);
        $this->assertNull($seedFile->synthea->state);
        $this->assertNull($seedFile->synthea->ages);
        $this->assertNull($seedFile->synthea->gender);
        $this->assertSame([], $seedFile->sectionsNotLoaded);
    }

    public function testParsesOptionalSyntheaFilters(): void
    {
        $yaml = self::MINIMAL . "\n  state: Massachusetts\n  city: Northampton\n  ages: 0-90\n  gender: F\n";

        $synthea = (new SeedFileParser())->parse($yaml)->synthea;

        $this->assertSame('Massachusetts', $synthea->state);
        $this->assertSame('Northampton', $synthea->city);
        $this->assertNotNull($synthea->ages);
        $this->assertSame(0, $synthea->ages->min);
        $this->assertSame(90, $synthea->ages->max);
        $this->assertSame(Gender::Female, $synthea->gender);
    }

    public function testAcceptsQuotedReferenceDate(): void
    {
        $yaml = str_replace('reference_date: 2026-11-19', 'reference_date: "2026-11-19"', self::MINIMAL);

        $synthea = (new SeedFileParser())->parse($yaml)->synthea;

        $this->assertSame('2026-11-19', $synthea->referenceDate->format('Y-m-d'));
    }

    public function testListsSectionsNotLoadedYet(): void
    {
        $yaml = self::MINIMAL . "\nproviders: []\nappointments: {days: -7..21}\n";

        $seedFile = (new SeedFileParser())->parse($yaml);

        $this->assertSame(['providers', 'appointments'], $seedFile->sectionsNotLoaded);
    }

    #[DataProvider('invalidFileProvider')]
    public function testRejectsInvalidFile(string $yaml, string $expectedMessage): void
    {
        $this->expectException(SeedFileException::class);
        $this->expectExceptionMessage($expectedMessage);

        (new SeedFileParser())->parse($yaml);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidFileProvider(): array
    {
        return [
            'not yaml' => ["version: [1\n", 'not valid YAML'],
            'a list, not a map' => ["- 1\n- 2\n", '(file): expected a map'],
            'unknown top-level key' => [self::MINIMAL . "\npatients: 3\n", '(file): unknown key(s): patients'],
            'unknown synthea key' => [self::MINIMAL . "\n  import: ccda\n", 'synthea: unknown key(s): import'],
            'wrong version' => [str_replace('version: 1', 'version: 2', self::MINIMAL), 'version: this loader reads version 1'],
            'missing seed' => [str_replace("seed: 42\n", '', self::MINIMAL), 'seed: required'],
            'zero count' => [str_replace('count: 5', 'count: 0', self::MINIMAL), 'synthea.count: must be at least 1'],
            'count as text' => [str_replace('count: 5', 'count: five', self::MINIMAL), 'synthea.count: required, must be a whole number'],
            'negative history' => [str_replace('years_of_history: 5', 'years_of_history: -1', self::MINIMAL), 'synthea.years_of_history: must not be negative'],
            'plain names not boolean' => [str_replace('plain_names: true', 'plain_names: yes please', self::MINIMAL), 'synthea.plain_names: required, must be true or false'],
            'impossible date' => [str_replace('reference_date: 2026-11-19', 'reference_date: "2026-02-30"', self::MINIMAL), 'synthea.reference_date'],
            'city without state' => [self::MINIMAL . "\n  city: Northampton\n", 'synthea.city: needs synthea.state'],
            'bad gender' => [self::MINIMAL . "\n  gender: X\n", 'synthea.gender: must be M or F'],
            'bad ages' => [self::MINIMAL . "\n  ages: old\n", 'synthea.ages: expected an age range'],
            'reversed ages' => [self::MINIMAL . "\n  ages: 90-10\n", 'synthea.ages: the first age must not be larger'],
        ];
    }

    public function testRejectsMissingFile(): void
    {
        $this->expectException(SeedFileException::class);
        $this->expectExceptionMessage('Seed file not found');

        (new SeedFileParser())->parseFile(__DIR__ . '/does-not-exist.yaml');
    }
}
