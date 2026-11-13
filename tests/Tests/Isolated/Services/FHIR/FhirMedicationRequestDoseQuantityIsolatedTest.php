<?php

/**
 * MedicationRequest doseQuantity is prescriptions.dosage + form_title as a
 * decimal, matching QDM (#14703). Strength (size + unit_title) is not this field.
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
    public static function fractionalDosages(): array
    {
        return [
            'half a tablet' => ['0.5', 'tablet', 0.5, 'tablet'],
            'two and a half capsules' => [2.5, 'capsule', 2.5, 'capsule'],
            'one tablet' => [1, 'tablet', 1.0, 'tablet'],
        ];
    }

    /**
     * Fractional prescriptions.dosage values must stay decimal with the form unit.
     *
     * @param mixed $dosage
     * @param mixed $form
     */
    #[DataProvider('fractionalDosages')]
    public function testDoseQuantityKeepsFractionalDosages(mixed $dosage, mixed $form, float $expectedValue, string $expectedUnit): void
    {
        $json = $this->doseQuantity([
            'drug_dosage_instructions' => 'Take as directed',
            'dosage' => $dosage,
            'form_title' => $form,
            'prescription_drug_size' => '50',
            'unit_title' => 'mg',
        ]);

        $this->assertEquals($expectedValue, $json['value'] ?? null);
        $this->assertSame($expectedUnit, $json['unit'] ?? null);
        $this->assertNotSame('mg', $json['unit']);
    }

    /**
     * 0.5 tablet must not become 0 the way intval() used to, and must not use size (mg).
     */
    public function testATruncatingCastWouldHaveSentZeroTablets(): void
    {
        $json = $this->doseQuantity([
            'drug_dosage_instructions' => 'Take as directed',
            'dosage' => '0.5',
            'form_title' => 'tablet',
            'prescription_drug_size' => '50',
            'unit_title' => 'mg',
        ]);

        $this->assertEquals(0.5, $json['value'] ?? null);
        $this->assertNotEquals(0, $json['value'] ?? null);
        $this->assertNotEquals(50, $json['value'] ?? null);
        $this->assertSame('tablet', $json['unit'] ?? null);
    }

    /**
     * A free-text SIG in dosage is not numeric, so doseAndRate is omitted.
     */
    public function testASigOmitsDoseQuantity(): void
    {
        $resource = new FHIRMedicationRequest();
        $this->service()->populateDosageInstruction($resource, [
            'drug_dosage_instructions' => 'Take as directed',
            'dosage' => 'Take 1 tablet daily',
            'form_title' => 'tablet',
        ]);
        $instructions = $resource->getDosageInstruction();
        $this->assertNotEmpty($instructions);
        $this->assertSame([], $instructions[0]->getDoseAndRate());
    }

    /**
     * No numeric dosage means no doseAndRate, matching the omit-empty rule.
     */
    public function testAMissingDosageOmitsDoseQuantity(): void
    {
        $resource = new FHIRMedicationRequest();
        $this->service()->populateDosageInstruction($resource, [
            'drug_dosage_instructions' => 'Take as directed',
            'prescription_drug_size' => '50',
            'unit_title' => 'mg',
        ]);
        $instructions = $resource->getDosageInstruction();
        $this->assertNotEmpty($instructions);
        $this->assertSame([], $instructions[0]->getDoseAndRate());
    }

    /**
     * Encode populateDosageInstruction() doseQuantity as JSON for assertions.
     *
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

    /**
     * populateDosageInstruction() does not touch instance state.
     */
    private function service(): FhirMedicationRequestService
    {
        // populateDosageInstruction() does not touch instance state.
        return (new \ReflectionClass(FhirMedicationRequestService::class))->newInstanceWithoutConstructor();
    }
}
