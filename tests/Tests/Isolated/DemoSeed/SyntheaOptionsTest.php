<?php

/**
 * Isolated tests for the Synthea command line built from a seed file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\DemoSeed;

use OpenEMR\DemoSeed\SeedFile\AgeRange;
use OpenEMR\DemoSeed\SeedFile\Gender;
use OpenEMR\DemoSeed\SeedFile\SyntheaOptions;
use PHPUnit\Framework\TestCase;

final class SyntheaOptionsTest extends TestCase
{
    public function testBuildsArgumentsWithoutFilters(): void
    {
        $options = new SyntheaOptions(
            count: 50,
            seed: 20261119,
            clinicianSeed: 99,
            referenceDate: new \DateTimeImmutable('2026-11-19'),
            yearsOfHistory: 5,
            plainNames: true,
        );

        $this->assertSame(
            [
                '-p', '50',
                '-s', '20261119',
                '-cs', '99',
                '-r', '20261119',
                '--exporter.baseDirectory=/tmp/demo-seed/output/',
                '--exporter.years_of_history=5',
                '--exporter.ccda.export=true',
                '--exporter.fhir.export=true',
                '--exporter.hospital.fhir.export=false',
                '--exporter.practitioner.fhir.export=false',
                '--exporter.metadata.export=false',
                '--generate.only_alive_patients=true',
                '--generate.append_numbers_to_person_names=false',
            ],
            $options->toArguments('/tmp/demo-seed/output/'),
        );
    }

    public function testAddsFiltersAndTrailingStateAndCity(): void
    {
        $options = new SyntheaOptions(
            count: 3,
            seed: 1,
            clinicianSeed: 2,
            referenceDate: new \DateTimeImmutable('2025-01-31'),
            yearsOfHistory: 0,
            plainNames: false,
            state: 'Massachusetts',
            city: 'Buzzards Bay',
            ages: new AgeRange(18, 65),
            gender: Gender::Male,
        );

        $arguments = $options->toArguments('/out');

        $this->assertSame(['-p', '3', '-s', '1', '-cs', '2', '-r', '20250131', '-a', '18-65', '-g', 'M'], array_slice($arguments, 0, 12));
        $this->assertContains('--generate.append_numbers_to_person_names=true', $arguments);
        $this->assertSame(['Massachusetts', 'Buzzards Bay'], array_slice($arguments, -2));
    }

    public function testWithCountKeepsEverythingElse(): void
    {
        $options = new SyntheaOptions(50, 1, 2, new \DateTimeImmutable('2026-11-19'), 5, true, 'Massachusetts');

        $changed = $options->withCount(3);

        $this->assertSame(3, $changed->count);
        $this->assertSame(50, $options->count);
        $this->assertSame('Massachusetts', $changed->state);
    }

    public function testRejectsCityWithoutState(): void
    {
        $this->expectException(\DomainException::class);

        new SyntheaOptions(5, 1, 2, new \DateTimeImmutable('2026-11-19'), 5, true, null, 'Boston');
    }
}
