<?php

declare(strict_types=1);

/*
 * FhirPatientServiceGeneralPractitionerTest.php
 *
 * Regression test for https://github.com/openemr/openemr/issues/11646:
 * parseFhirResource() read the reference type from a 'resourceType' key, but
 * UtilsService::parseReference() returns it as 'type', so a local Practitioner
 * in Patient.generalPractitioner was never mapped to provider_uuid.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirPatientService;
use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\TestCase;

class FhirPatientServiceGeneralPractitionerTest extends TestCase
{
    private const PRACTITIONER_UUID = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';
    private const ORGANIZATION_UUID = 'ffffffff-bbbb-cccc-dddd-eeeeeeeeeeee';

    /**
     * @param array{string, string} ...$references [resource type, uuid] pairs, in list order
     * @return array<mixed>
     */
    private function parsePatientWithGeneralPractitioners(array ...$references): array
    {
        // parseFhirResource() only maps the resource, so the database-backed constructor is not needed
        $service = (new \ReflectionClass(FhirPatientService::class))->newInstanceWithoutConstructor();

        $patient = new FHIRPatient();
        $patient->setId(new FHIRId('11111111-2222-3333-4444-555555555555'));
        foreach ($references as [$resourceType, $uuid]) {
            $patient->addGeneralPractitioner(UtilsService::createRelativeReference($resourceType, $uuid));
        }

        return $service->parseFhirResource($patient);
    }

    public function testLocalPractitionerIsMappedToProviderUuid(): void
    {
        $data = $this->parsePatientWithGeneralPractitioners(['Practitioner', self::PRACTITIONER_UUID]);

        $this->assertSame(self::PRACTITIONER_UUID, $data['provider_uuid'] ?? null);
    }

    public function testNonPractitionerReferenceIsIgnored(): void
    {
        $data = $this->parsePatientWithGeneralPractitioners(['Organization', self::ORGANIZATION_UUID]);

        $this->assertArrayNotHasKey('provider_uuid', $data);
    }

    public function testPractitionerListedAfterAnOrganizationIsMapped(): void
    {
        $data = $this->parsePatientWithGeneralPractitioners(
            ['Organization', self::ORGANIZATION_UUID],
            ['Practitioner', self::PRACTITIONER_UUID]
        );

        $this->assertSame(self::PRACTITIONER_UUID, $data['provider_uuid'] ?? null);
    }
}
