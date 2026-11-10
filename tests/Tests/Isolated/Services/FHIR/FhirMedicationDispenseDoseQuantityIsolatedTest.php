<?php

/**
 * MedicationDispense doseQuantity is prescriptions.dosage + form as a decimal,
 * matching MedicationRequest (#14703).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @copyright Copyright (c) 2026 Subramani Nagaraj <yuvamani.mani@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRResource\FHIRDosage;
use OpenEMR\Services\FHIR\MedicationDispense\FhirMedicationDispenseLocalDispensaryService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

#[Group('isolated')]
class FhirMedicationDispenseDoseQuantityIsolatedTest extends TestCase
{
    /**
     * MedicationDispense doseQuantity must keep 0.5 tablet, not size 50 mg or intval() 0.
     */
    public function testDoseQuantityKeepsHalfATablet(): void
    {
        $method = new ReflectionMethod(FhirMedicationDispenseLocalDispensaryService::class, 'mapDosageInstruction');
        $service = (new ReflectionClass(FhirMedicationDispenseLocalDispensaryService::class))->newInstanceWithoutConstructor();
        $dosage = $method->invoke($service, [
            'dosage' => '0.5',
            'prescription_form_title' => 'tablet',
            'prescription_drug_size' => '50',
            'unit_title' => 'mg',
            'route_title' => 'Oral',
        ]);
        $this->assertInstanceOf(FHIRDosage::class, $dosage);
        $rates = $dosage->getDoseAndRate();
        $this->assertNotEmpty($rates);
        $json = json_decode((string)json_encode($rates[0]->getDoseQuantity()), true);
        $this->assertIsArray($json);
        $this->assertEquals(0.5, $json['value'] ?? null);
        $this->assertNotEquals(0, $json['value'] ?? null);
        $this->assertNotEquals(50, $json['value'] ?? null);
        $this->assertSame('tablet', $json['unit'] ?? null);
    }
}
