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

use Lcobucci\Clock\FrozenClock;
use OpenEMR\Common\Database\QueryUtils;
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
use OpenEMR\Services\PhoneNumberService;
use OpenEMR\Services\PhoneType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * A database test because the service's constructors read the database (InsuranceCompanyService
 * registers missing uuids, BaseService reads the table's columns); parsing itself reads and
 * writes nothing. The one insert test's rows are rolled back; tearDown removes any left behind.
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

    protected function tearDown(): void
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT `id`, `uuid` FROM `insurance_companies` WHERE `name` LIKE ?",
            [self::NAME . "%"]
        );
        foreach ($rows as $row) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM `addresses` WHERE `foreign_id` = ?", [$row['id']]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `phone_numbers` WHERE `foreign_id` = ?", [$row['id']]);
            QueryUtils::fetchRecordsNoLog(
                "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_companies' AND `uuid` = ?",
                [$row['uuid']]
            );
            QueryUtils::fetchRecordsNoLog("DELETE FROM `insurance_companies` WHERE `id` = ?", [$row['id']]);
        }
    }

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

    /**
     * A blank phone doesn't stop the search for one in use, and a phone whose period ends later
     * is still in use.
     */
    #[Test]
    public function aBlankPhoneIsPassedOverAndAFutureEndIsInUse(): void
    {
        $service = new FhirOrganizationInsuranceService();
        $blankFirst = FhirOrganizationSerializer::deserialize([
            'resourceType' => 'Organization',
            'name' => self::NAME,
            'telecom' => [['system' => 'phone', 'value' => ''], ['system' => 'phone', 'value' => '(555) 200-0004']],
        ]);
        $endingLater = FhirOrganizationSerializer::deserialize([
            'resourceType' => 'Organization',
            'name' => self::NAME,
            'telecom' => [['system' => 'phone', 'value' => '(555) 200-0005', 'period' => ['end' => '2999-01-01']]],
        ]);

        $this->assertSame(
            ['(555) 200-0004', '(555) 200-0005'],
            [$service->parseFhirResource($blankFirst)['phone'] ?? null, $service->parseFhirResource($endingLater)['phone'] ?? null]
        );
    }

    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function periodEndProvider(): array
    {
        // whether a period with this end is still in use at 2026-11-04 15:00 UTC, the test's clock
        return [
            'a year past' => ['2020', false],
            'this year' => ['2026', true],
            'last month' => ['2026-10', false],
            'this month' => ['2026-11', true],
            'yesterday' => ['2026-11-03', false],
            'today' => ['2026-11-04', true],
            'an hour ago' => ['2026-11-04T14:00:00Z', false],
            'in an hour' => ['2026-11-04T16:00:00Z', true],
            // not a date (a lenient parse rolls it over to January 2026): no end, so in use
            'a month that does not exist' => ['2025-13', true],
        ];
    }

    /**
     * A period's end is read at the precision it is given in, for the phone and the address alike:
     * a year, a month or a date ends with its last second.
     */
    #[Test]
    #[DataProvider('periodEndProvider')]
    public function aPeriodEndIsReadAtItsPrecision(string $end, bool $inUse): void
    {
        $clock = new FrozenClock(new \DateTimeImmutable('2026-11-04T15:00:00Z'));
        $service = new FhirOrganizationInsuranceService(null, new PhoneNumberService(), $clock);
        $organization = FhirOrganizationSerializer::deserialize([
            'resourceType' => 'Organization',
            'name' => self::NAME,
            'telecom' => [['system' => 'phone', 'value' => '(555) 200-0006', 'period' => ['end' => $end]]],
            'address' => [
                ['city' => 'Testville', 'state' => 'CA'],
                ['city' => 'Endville', 'state' => 'CA', 'period' => ['start' => '2019-01-01', 'end' => $end]],
            ],
        ]);

        $record = $service->parseFhirResource($organization);
        $this->assertSame(
            $inUse ? ['(555) 200-0006', 'Endville'] : [null, 'Testville'],
            [$record['phone'] ?? null, $record['city'] ?? null]
        );
    }

    /**
     * A failure after the company row is written (here the fax insert) rolls back the company,
     * its address, its phone number and its uuid.
     */
    #[Test]
    public function aFailureAfterTheCompanyRowLeavesNothingStored(): void
    {
        $failingFax = new class extends PhoneNumberService {
            public function insert(mixed $data, mixed $foreignId, ?PhoneType $type = null): int
            {
                throw new \RuntimeException('test-fixture: the fax insert failed');
            }
        };
        $uuidsBefore = $this->uuidRegistryCount();
        $phonesBefore = $this->phoneCount();

        try {
            (new FhirOrganizationInsuranceService(null, $failingFax))->insert(self::deserialized());
            $this->fail("The failing insert should have thrown");
        } catch (\RuntimeException $exception) {
            $this->assertSame('test-fixture: the fax insert failed', $exception->getMessage());
        }

        $this->assertSame([], $this->storedIds(), "The company row should have been rolled back");
        $this->assertSame($uuidsBefore, $this->uuidRegistryCount(), "The company's uuid should have been rolled back");
        $this->assertSame($phonesBefore, $this->phoneCount(), "The phone should have been rolled back");
        $orphans = QueryUtils::fetchRecords(
            "SELECT a.`id` FROM `addresses` a LEFT JOIN `insurance_companies` i ON i.`id` = a.`foreign_id` "
            . "WHERE i.`id` IS NULL AND a.`line1` = ? AND a.`city` = ?",
            ['4 Workbench Way', 'Testville']
        );
        $this->assertSame([], $orphans, "The address should have been rolled back");
    }

    /**
     * @return list<array<mixed>>
     */
    private function storedIds(): array
    {
        return QueryUtils::fetchRecords("SELECT `id` FROM `insurance_companies` WHERE `name` LIKE ?", [self::NAME . "%"]);
    }

    private function uuidRegistryCount(): int
    {
        return count(QueryUtils::fetchRecords("SELECT `uuid` FROM `uuid_registry` WHERE `table_name` = 'insurance_companies'"));
    }

    private function phoneCount(): int
    {
        return count(QueryUtils::fetchRecords("SELECT `id` FROM `phone_numbers`"));
    }

    /**
     * @return array<string, array{list<array<string, mixed>>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function addressProvider(): array
    {
        return [
            'the current one, between two ended ones' => [
                [self::endedAddress('Endedtown', '2020-01-01'), ['city' => 'Testville', 'state' => 'CA'], self::endedAddress('Laterville', '2021-01-01')],
                'Testville',
            ],
            'the one that ended last, when all have ended' => [
                [self::endedAddress('Endedtown', '2020-01-01'), self::endedAddress('Laterville', '2021-01-01'), self::endedAddress('Earlyville', '2019-06-01')],
                'Laterville',
            ],
            'not one marked old' => [
                [['city' => 'Testville', 'state' => 'CA', 'use' => 'work'], ['city' => 'Oldtown', 'state' => 'NY', 'use' => 'old']],
                'Testville',
            ],
            'one whose end is still to come, as current' => [
                [self::endedAddress('Endedtown', '2020-01-01'), ['city' => 'Testville', 'state' => 'CA'], self::endedAddress('Futureville', '2999-01-01')],
                'Futureville',
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $addresses
     */
    #[Test]
    #[DataProvider('addressProvider')]
    public function takesTheCurrentAddress(array $addresses, string $city): void
    {
        $organization = FhirOrganizationSerializer::deserialize([
            'resourceType' => 'Organization',
            'name' => self::NAME,
            'address' => $addresses,
        ]);

        $service = new FhirOrganizationInsuranceService();
        $this->assertSame($city, $service->parseFhirResource($organization)['city'] ?? null);
    }

    /**
     * @return array<string, mixed> an address in CA whose period ends on $end
     */
    private static function endedAddress(string $city, string $end): array
    {
        return ['city' => $city, 'state' => 'CA', 'period' => ['start' => '2019-01-01', 'end' => $end]];
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
