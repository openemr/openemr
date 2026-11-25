<?php

/**
 * Isolated tests for the demo seed loader's patient manifest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\DemoSeed;

use OpenEMR\DemoSeed\Import\ImportedPatient;
use OpenEMR\DemoSeed\Import\ImportException;
use OpenEMR\DemoSeed\Import\PatientManifest;
use OpenEMR\DemoSeed\Synthea\SyntheaPatient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class PatientManifestTest extends TestCase
{
    private Filesystem $filesystem;
    private string $path;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->path = $this->filesystem->tempnam(sys_get_temp_dir(), 'demo-seed-manifest-', '.json');
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->path);
    }

    public function testReadsBackWhatItWroteForTheSameSite(): void
    {
        $manifest = new PatientManifest($this->filesystem);
        $manifest->write($this->path, 'default', [$this->patient(7)]);

        $patients = $manifest->read($this->path, 'default');

        $this->assertCount(1, $patients);
        $this->assertSame(7, $patients[0]->pid);
        $this->assertSame('00000000-0000-4000-8000-000000000007', $patients[0]->uuid);
        $this->assertSame('Al', $patients[0]->synthea->firstName);
        $this->assertSame('1960-01-10', $patients[0]->synthea->birthDate->format('Y-m-d'));
    }

    public function testRefusesAManifestWrittenForAnotherSite(): void
    {
        $manifest = new PatientManifest($this->filesystem);
        $manifest->write($this->path, 'clinic2', [$this->patient(7)]);

        $this->expectException(ImportException::class);
        $this->expectExceptionMessage('was written for site "clinic2", not "default"');

        $manifest->read($this->path, 'default');
    }

    public function testRefusesAnOlderManifestWithoutASite(): void
    {
        $row = ['pid' => 3, ...$this->patient(3)->synthea->toArray()];
        $this->filesystem->dumpFile($this->path, json_encode([$row], JSON_THROW_ON_ERROR));

        $this->expectException(ImportException::class);
        $this->expectExceptionMessage('is from an older loader');

        (new PatientManifest($this->filesystem))->read($this->path, 'default');
    }

    public function testRefusesARowWithoutAUuid(): void
    {
        $row = ['pid' => 3, ...$this->patient(3)->synthea->toArray()];
        $this->filesystem->dumpFile(
            $this->path,
            json_encode(['site' => 'default', 'patients' => [$row]], JSON_THROW_ON_ERROR),
        );

        $this->expectException(ImportException::class);
        $this->expectExceptionMessage('has no pid or uuid');

        (new PatientManifest($this->filesystem))->read($this->path, 'default');
    }

    public function testReportsABadRowAsAnImportError(): void
    {
        $row = ['pid' => 0, 'uuid' => '00000000-0000-4000-8000-000000000000', ...$this->patient(3)->synthea->toArray()];
        $this->filesystem->dumpFile(
            $this->path,
            json_encode(['site' => 'default', 'patients' => [$row]], JSON_THROW_ON_ERROR),
        );

        $this->expectException(ImportException::class);
        $this->expectExceptionMessage('Patient 1 in the manifest at ' . $this->path . ' is not valid: Patient pid must be positive');

        (new PatientManifest($this->filesystem))->read($this->path, 'default');
    }

    public function testAnEmptyManifestStartsANewImport(): void
    {
        $manifest = new PatientManifest($this->filesystem);
        $manifest->write($this->path, 'default', [$this->patient(1), $this->patient(2)]);
        $manifest->write($this->path, 'default', []);

        $this->assertSame([], $manifest->read($this->path, 'default'));
    }

    private function patient(int $pid): ImportedPatient
    {
        return new ImportedPatient($pid, sprintf('00000000-0000-4000-8000-%012d', $pid), new SyntheaPatient(
            'synthea-' . $pid,
            'ccda-' . $pid . '.xml',
            'fhir-' . $pid . '.json',
            'Al',
            'Able',
            new \DateTimeImmutable('1960-01-10'),
            'male',
            'M',
            null,
        ));
    }
}
