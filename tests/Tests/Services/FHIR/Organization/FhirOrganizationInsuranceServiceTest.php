<?php

/**
 * FhirOrganizationInsuranceService::parseFhirResource() on both shapes a resource arrives in.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\FHIR\Organization;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPointSystem;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\Organization\FhirOrganizationInsuranceService;
use OpenEMR\Services\FHIR\Serialization\FhirOrganizationSerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A database test because the service's constructors read the database (InsuranceCompanyService
 * registers missing uuids, BaseService reads the table's columns); parsing itself reads and
 * writes nothing.
 */
class FhirOrganizationInsuranceServiceTest extends TestCase
{
    private const NAME = 'test-fixture-Parsed Insurer';

    /**
     * The record both shapes map to: the payer ids, the first phone, and the address keyed as
     * InsuranceCompanyService::insert() reads it, with the third line joined to the second.
     */
    private const EXPECTED = [
        'uuid' => null,
        'name' => self::NAME,
        'cms_id' => 'PAYER1',
        'alt_cms_id' => 'PAYER2',
        'inactive' => 0,
        'line1' => '4 Workbench Way',
        'line2' => 'Suite 200, Floor 3',
        'zip' => '90001',
        'city' => 'Testville',
        'state' => 'CA',
        'country' => 'US',
        'fax' => '(555) 200-9999',
        'phone' => '(555) 200-3003',
    ];

    /**
     * @return array<string, array{\Closure(): FHIROrganization}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function organizationProvider(): array
    {
        return [
            'deserialized from a request, primitives as strings' => [self::deserialized(...)],
            'built in code, primitives as typed elements' => [self::typed(...)],
        ];
    }

    /**
     * @param \Closure(): FHIROrganization $build
     */
    #[Test]
    #[DataProvider('organizationProvider')]
    public function parsesNameAddressPhoneAndPayerIds(\Closure $build): void
    {
        $service = new FhirOrganizationInsuranceService();
        $this->assertSame(self::EXPECTED, $service->parseFhirResource($build()));
    }

    #[Test]
    public function blankAndMissingElementsParseAsNull(): void
    {
        $organization = FhirOrganizationSerializer::deserialize([
            'resourceType' => 'Organization',
            'name' => '',
            'address' => [['city' => 'Testville']],
        ]);

        $service = new FhirOrganizationInsuranceService();
        $this->assertSame(
            [
                'uuid' => null,
                'name' => null,
                'cms_id' => null,
                'alt_cms_id' => null,
                'inactive' => 0,
                'line1' => null,
                'line2' => null,
                'zip' => null,
                'city' => 'Testville',
                'state' => null,
                'country' => null,
            ],
            $service->parseFhirResource($organization)
        );
    }

    #[Test]
    public function anInactiveInsurerKeepsOnlyAPhoneStillInUse(): void
    {
        $organization = FhirOrganizationSerializer::deserialize([
            'resourceType' => 'Organization',
            'name' => self::NAME,
            'active' => false,
            'telecom' => [
                ['system' => 'phone', 'value' => '(555) 200-0001', 'use' => 'old'],
                ['system' => 'phone', 'value' => '(555) 200-0002', 'period' => ['end' => '2001-01-01']],
                // the system this service emitted for the phone before it used 'phone'
                ['system' => 'work', 'value' => '(555) 200-0003'],
            ],
        ]);

        $record = (new FhirOrganizationInsuranceService())->parseFhirResource($organization);
        $this->assertSame([1, '(555) 200-0003'], [$record['inactive'] ?? null, $record['phone'] ?? null]);
    }

    private static function deserialized(): FHIROrganization
    {
        return FhirOrganizationSerializer::deserialize([
            'resourceType' => 'Organization',
            'name' => self::NAME,
            'identifier' => [
                ['system' => FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE, 'value' => 'PAYER1'],
                ['system' => 'http://example.org/other', 'value' => 'IGNORED'],
                ['system' => FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE, 'value' => 'PAYER2'],
                // a third has nowhere to go and does not overwrite the second
                ['system' => FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE, 'value' => 'PAYER3'],
            ],
            'telecom' => [
                ['system' => 'fax', 'value' => '(555) 200-9999'],
                ['system' => 'phone', 'value' => '(555) 200-3003'],
            ],
            'address' => [[
                'line' => ['4 Workbench Way', 'Suite 200', 'Floor 3'],
                'city' => 'Testville',
                'state' => 'CA',
                'postalCode' => '90001',
                'country' => 'US',
            ]],
        ]);
    }

    private static function typed(): FHIROrganization
    {
        $organization = new FHIROrganization();
        $organization->setName(new FHIRString(self::NAME));
        foreach (
            [
                [FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE, 'PAYER1'],
                ['http://example.org/other', 'IGNORED'],
                [FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE, 'PAYER2'],
                [FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE, 'PAYER3'],
            ] as [$system, $value]
        ) {
            $identifier = new FHIRIdentifier();
            $identifier->setSystem(new FHIRUri($system));
            $identifier->setValue(new FHIRString($value));
            $organization->addIdentifier($identifier);
        }
        foreach ([['fax', '(555) 200-9999'], ['phone', '(555) 200-3003']] as [$system, $value]) {
            $contactPoint = new FHIRContactPoint();
            $contactPoint->setSystem(new FHIRContactPointSystem(['value' => $system]));
            $contactPoint->setValue(new FHIRString($value));
            $organization->addTelecom($contactPoint);
        }
        $address = new FHIRAddress();
        foreach (['4 Workbench Way', 'Suite 200', 'Floor 3'] as $line) {
            $address->addLine(new FHIRString($line));
        }
        $address->setCity(new FHIRString('Testville'));
        $address->setState(new FHIRString('CA'));
        $address->setPostalCode(new FHIRString('90001'));
        $address->setCountry(new FHIRString('US'));
        $organization->addAddress($address);
        return $organization;
    }
}
