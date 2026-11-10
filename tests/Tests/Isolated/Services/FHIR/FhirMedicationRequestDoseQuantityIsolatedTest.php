<?php

/**
 * MedicationRequest doseQuantity keeps fractional strengths and the unit that
 * describes prescriptions.size (#14703).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @copyright Copyright (c) 2026 Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedicationRequest;
use OpenEMR\Services\FHIR\FhirMedicationRequestService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class FhirMedicationRequestDoseQuantityIsolatedTest extends TestCase
{
    /**
     * @return array<string, array{mixed, mixed, float, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function fractionalStrengths(): array
    {
        return [
            'half a milligram' => ['0.5', 'mg', 0.5, 'mg'],
            'two and a half milligrams' => [2.5, 'mg', 2.5, 'mg'],
            'one capsule' => [1, 'capsule', 1.0, 'capsule'],
        ];
    }

    /**
     * @param mixed $size
     * @param mixed $unit
     */
    #[DataProvider('fractionalStrengths')]
    public function testDoseQuantityKeepsFractionalStrengths(mixed $size, mixed $unit, float $expectedValue, string $expectedUnit): void
    {
        $json = $this->doseQuantity([
            'drug_dosage_instructions' => 'Take as directed',
            'prescription_drug_size' => $size,
            'unit_title' => $unit,
        ]);

        $this->assertEquals($expectedValue, $json['value'] ?? null);
        $this->assertSame($expectedUnit, $json['unit'] ?? null);
    }

    public function testATruncatingCastWouldHaveSentZeroMilligrams(): void
    {
        $json = $this->doseQuantity([
            'drug_dosage_instructions' => 'Take as directed',
            'prescription_drug_size' => '0.5',
            'unit_title' => 'mg',
        ]);

        $this->assertEquals(0.5, $json['value'] ?? null);
        $this->assertNotEquals(0, $json['value'] ?? null);
        $this->assertSame('mg', $json['unit'] ?? null);
    }

    public function testAMissingSizeOmitsDoseQuantity(): void
    {
        $resource = new FHIRMedicationRequest();
        $this->service()->populateDosageInstruction($resource, [
            'drug_dosage_instructions' => 'Take as directed',
        ]);
        $instructions = $resource->getDosageInstruction();
        $this->assertNotEmpty($instructions);
        $this->assertSame([], $instructions[0]->getDoseAndRate());
    }

    /**
     * @param array<string, mixed> $record
     * @return array<mixed>
     */
    private function doseQuantity(array $record): array
    {
        $resource = new FHIRMedicationRequest();
        $this->service()->populateDosageInstruction($resource, $record);
        $instructions = $resource->getDosageInstruction();
        $this->assertNotEmpty($instructions);
        $rates = $instructions[0]->getDoseAndRate();
        $this->assertNotEmpty($rates);
        $json = json_decode((string)json_encode($rates[0]->getDoseQuantity()), true);
        $this->assertIsArray($json);
        return $json;
    }

    private function service(): FhirMedicationRequestService
    {
        // populateDosageInstruction() does not touch instance state.
        return (new \ReflectionClass(FhirMedicationRequestService::class))->newInstanceWithoutConstructor();
    }
}
