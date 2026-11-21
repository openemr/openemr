<?php

/**
 * Isolated tests for mapping FHIR Patient.active onto patient_data.active.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirPatientService;
use PHPUnit\Framework\TestCase;

final class FhirPatientServiceActiveTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    private function parse(FHIRPatient $patient): array
    {
        // parseFhirResource() only maps the resource, so the database-backed constructor is not needed
        $service = (new \ReflectionClass(FhirPatientService::class))->newInstanceWithoutConstructor();
        $patient->setId(new FHIRId('11111111-2222-3333-4444-555555555555'));
        return $service->parseFhirResource($patient);
    }

    public function testActiveTrueMapsToOne(): void
    {
        $data = $this->parse((new FHIRPatient())->setActive(new FHIRBoolean(true)));

        $this->assertSame(1, $data['active'] ?? null);
    }

    public function testActiveFalseMapsToZero(): void
    {
        $data = $this->parse((new FHIRPatient())->setActive(new FHIRBoolean(false)));

        $this->assertSame(0, $data['active'] ?? null);
    }

    public function testMissingActiveLeavesTheColumnAlone(): void
    {
        $data = $this->parse(new FHIRPatient());

        $this->assertArrayNotHasKey('active', $data);
    }
}
