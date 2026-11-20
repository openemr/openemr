<?php

/**
 * Isolated tests for reading a Synthea output directory.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\DemoSeed;

use OpenEMR\DemoSeed\Synthea\SyntheaException;
use OpenEMR\DemoSeed\Synthea\SyntheaOutputReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class SyntheaOutputReaderTest extends TestCase
{
    private const FIXTURES = __DIR__ . '/fixtures/synthea-output';

    private ?string $scratch = null;

    protected function tearDown(): void
    {
        if ($this->scratch !== null) {
            (new Filesystem())->remove($this->scratch);
        }
    }

    public function testReadsPatientsSortedByCcdaFileName(): void
    {
        $patients = (new SyntheaOutputReader())->read(self::FIXTURES);

        $this->assertCount(2, $patients);
        [$ann, $bob] = $patients;

        $this->assertSame('11111111-1111-1111-1111-111111111111', $ann->syntheaId);
        $this->assertSame('Ann', $ann->firstName);
        $this->assertSame('Zed', $ann->lastName);
        $this->assertSame('1960-03-15', $ann->birthDate->format('Y-m-d'));
        $this->assertSame('female', $ann->sex);
        $this->assertSame('M', $ann->maritalStatus);
        $this->assertStringEndsWith('/ccda/Ann_Zed_11111111-1111-1111-1111-111111111111.xml', $ann->ccdaFile);
        $this->assertStringEndsWith('/fhir/Ann_Zed_11111111-1111-1111-1111-111111111111.json', $ann->fhirFile);

        $this->assertSame('22222222-2222-2222-2222-222222222222', $bob->syntheaId);
        $this->assertSame('Bob', $bob->firstName, 'a name with no use counts as official');
        $this->assertNull($bob->maritalStatus);
    }

    public function testPayerComesFromTheMostRecentClaim(): void
    {
        [$ann, $bob] = (new SyntheaOutputReader())->read(self::FIXTURES);

        $this->assertNull($ann->payer, 'NO_INSURANCE on the latest claim means no payer');
        $this->assertSame('Humana', $bob->payer, 'claims are not in date order in the bundle');
    }

    public function testAgeOnReferenceDate(): void
    {
        [$ann, $bob] = (new SyntheaOutputReader())->read(self::FIXTURES);
        $referenceDate = new \DateTimeImmutable('2026-11-19');

        $this->assertSame(66, $ann->ageOn($referenceDate));
        $this->assertSame(7, $bob->ageOn($referenceDate));
    }

    public function testSerializesForTheManifest(): void
    {
        [$ann] = (new SyntheaOutputReader())->read(self::FIXTURES);

        $row = $ann->toArray();

        $this->assertSame('11111111-1111-1111-1111-111111111111', $row['synthea_id']);
        $this->assertSame('1960-03-15', $row['birth_date']);
        $this->assertSame('M', $row['marital_status']);
        $this->assertNull($row['payer']);
    }

    public function testFailsWhenThereAreNoCcdaFiles(): void
    {
        $this->expectException(SyntheaException::class);
        $this->expectExceptionMessage('no CCDA files');

        (new SyntheaOutputReader())->read($this->makeScratch());
    }

    public function testFailsWhenTheFhirFileIsMissing(): void
    {
        $scratch = $this->makeScratch();
        (new Filesystem())->copy(
            self::FIXTURES . '/ccda/Ann_Zed_11111111-1111-1111-1111-111111111111.xml',
            $scratch . '/ccda/Ann_Zed_11111111-1111-1111-1111-111111111111.xml',
        );

        $this->expectException(SyntheaException::class);
        $this->expectExceptionMessage('Expected one FHIR file');

        (new SyntheaOutputReader())->read($scratch);
    }

    public function testFailsWhenTheCcdaHasNoSyntheaId(): void
    {
        $scratch = $this->makeScratch();
        (new Filesystem())->dumpFile(
            $scratch . '/ccda/Nobody.xml',
            '<?xml version="1.0"?><ClinicalDocument xmlns="urn:hl7-org:v3"><recordTarget><patientRole>'
            . '<id root="1.2.3" extension="abc"/></patientRole></recordTarget></ClinicalDocument>',
        );

        $this->expectException(SyntheaException::class);
        $this->expectExceptionMessage('No Synthea patient id');

        (new SyntheaOutputReader())->read($scratch);
    }

    private function makeScratch(): string
    {
        $this->scratch = sys_get_temp_dir() . '/demo-seed-test-' . bin2hex(random_bytes(6));
        (new Filesystem())->mkdir([$this->scratch . '/ccda', $this->scratch . '/fhir']);
        return $this->scratch;
    }
}
