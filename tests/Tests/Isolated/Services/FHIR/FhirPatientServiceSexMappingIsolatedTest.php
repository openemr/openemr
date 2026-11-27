<?php

declare(strict_types=1);

/*
 * FhirPatientServiceSexMappingIsolatedTest.php
 *
 * Regression test for https://github.com/openemr/openemr/issues/15440:
 * parseFhirResource() stored the lowercase FHIR gender (male/female/unknown)
 * directly in patient_data.sex, which uses the sex list option ids
 * Male/Female/UNK, and extractBirthSex() mapped UNK to the list title
 * 'Unknown' instead of the option id 'UNK'.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\FHIR\FhirPatientService;
use PHPUnit\Framework\TestCase;

class FhirPatientServiceSexMappingIsolatedTest extends TestCase
{
    private const BIRTHSEX_URL = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-birthsex';

    /**
     * parseFhirResource() only maps the resource, so the database-backed
     * constructor is not needed (same pattern as the generalPractitioner test).
     *
     * @return array<mixed>
     */
    private function parsePatient(?string $gender, ?string $birthSex = null): array
    {
        $service = (new \ReflectionClass(FhirPatientService::class))->newInstanceWithoutConstructor();

        $patient = new FHIRPatient();
        $patient->setId(new FHIRId('11111111-2222-3333-4444-555555555555'));
        if ($gender !== null) {
            $patient->setGender($gender);
        }
        if ($birthSex !== null) {
            $extension = new FHIRExtension();
            $extension->setUrl(self::BIRTHSEX_URL);
            $extension->setValueCode($birthSex);
            $patient->addExtension($extension);
        }

        return $service->parseFhirResource($patient);
    }

    public function testMaleGenderMapsToListId(): void
    {
        $data = $this->parsePatient('male');
        $this->assertSame('Male', $data['sex']);
    }

    public function testFemaleGenderMapsToListId(): void
    {
        $data = $this->parsePatient('female');
        $this->assertSame('Female', $data['sex']);
    }

    public function testUnknownGenderMapsToUnkListId(): void
    {
        $data = $this->parsePatient('unknown');
        $this->assertSame('UNK', $data['sex']);
    }

    public function testOtherGenderIsLeftUnchangedPendingDecision(): void
    {
        // 'other' has no sex list entry; #15440 defers that mapping.
        $data = $this->parsePatient('other');
        $this->assertSame('other', $data['sex']);
    }

    public function testBirthSexMaleAndFemaleStillMap(): void
    {
        $this->assertSame('Male', $this->parsePatient('unknown', 'M')['sex']);
        $this->assertSame('Female', $this->parsePatient('unknown', 'F')['sex']);
    }

    public function testBirthSexUnknownMapsToUnkListIdNotTitle(): void
    {
        $data = $this->parsePatient('unknown', 'UNK');
        $this->assertSame('UNK', $data['sex']);
    }

    public function testBirthSexExtensionOverridesGender(): void
    {
        $data = $this->parsePatient('female', 'M');
        $this->assertSame('Male', $data['sex']);
    }

    public function testUnrecognizedBirthSexFallsBackToUnkListId(): void
    {
        // US Core 7 allows codes such as ASKU; they must land on a valid id.
        $data = $this->parsePatient('unknown', 'ASKU');
        $this->assertSame('UNK', $data['sex']);
    }
}