<?php

/**
 * FHIR Organization API tests for insurance companies.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Address\AddressData;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirPayloadReader;
use OpenEMR\Services\PhoneType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * POST /fhir/Organization with an insurance type is routed by FhirOrganizationService to
 * FhirOrganizationInsuranceService, which stores an insurance company. These tests post the
 * body a FHIR client sends (plain JSON, so every primitive arrives as a PHP string) and read
 * the stored company back through GET /fhir/Organization/:uuid.
 *
 * tearDown removes every insurance company whose name starts with NAME_PREFIX, with its
 * address, phone number and uuid_registry row.
 */
class OrganizationInsuranceFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/Organization";

    private const NAME_PREFIX = "test-fixture-FHIR Insurer";

    private const CMS_ID = "FHIR12345";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT `id`, `uuid` FROM `insurance_companies` WHERE `name` LIKE ?",
            [self::NAME_PREFIX . "%"]
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
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * An insurer posted as JSON is stored as an insurance company, with its address, phone,
     * fax and payer id, and reads back as an Organization of type ins.
     */
    public function testPostInsurerCreatesInsuranceCompany(): void
    {
        $name = self::NAME_PREFIX . " Create";
        $result = $this->testClient->post(self::ENDPOINT, self::insurerBody($name));
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), "POST should return 201 Created: " . $result->getBody());

        $row = $this->decodeJsonArray($result);
        $this->assertIsString($row['uuid'] ?? null, "POST should answer with the stored company's uuid");
        $this->assertIsInt($row['id'] ?? null, "POST should answer with the stored company's id");

        $readBack = $this->testClient->getOne(self::ENDPOINT, $row['uuid']);
        $this->assertSame(Response::HTTP_OK, $readBack->getStatusCode());
        $resource = $this->decodeJsonArray($readBack);
        $this->assertSame("Organization", $resource['resourceType'] ?? null);
        $this->assertSame($row['uuid'], $resource['id'] ?? null);
        $this->assertSame($name, $resource['name'] ?? null);
        $this->assertSame(
            ['ins'],
            self::codes($resource['type'] ?? null),
            "The stored company should read back as an insurance Organization"
        );

        $addresses = $resource['address'] ?? null;
        $this->assertIsArray($addresses);
        $address = $addresses[0] ?? null;
        $this->assertIsArray($address);
        $this->assertSame(['4 Workbench Way', 'Suite 200'], $address['line'] ?? null);
        $this->assertSame('Testville', $address['city'] ?? null);
        $this->assertSame('CA', $address['state'] ?? null);
        $this->assertSame('90001', $address['postalCode'] ?? null);
        $this->assertSame('US', $address['country'] ?? null);

        $stored = QueryUtils::querySingleRow(
            "SELECT `line1`, `line2`, `city`, `state`, `zip`, `country` FROM `addresses` WHERE `foreign_id` = ?",
            [$row['id']]
        );
        $this->assertSame(
            ['line1' => '4 Workbench Way', 'line2' => 'Suite 200', 'city' => 'Testville', 'state' => 'CA', 'zip' => '90001', 'country' => 'US'],
            $stored
        );

        $identifiers = $resource['identifier'] ?? null;
        $this->assertIsArray($identifiers);
        $values = array_map(static fn(mixed $identifier): mixed => is_array($identifier) ? ($identifier['value'] ?? null) : null, $identifiers);
        $this->assertSame([self::CMS_ID], $values);

        $numbers = [];
        foreach (
            QueryUtils::fetchRecords(
                "SELECT `type`, CONCAT(`area_code`, `prefix`, `number`) AS `digits` FROM `phone_numbers` WHERE `foreign_id` = ? ORDER BY `type`",
                [$row['id']]
            ) as $number
        ) {
            $numbers[] = [$number['type'], $number['digits']];
        }
        $this->assertSame(
            [[PhoneType::WORK->value, '5552003003'], [PhoneType::FAX->value, '5552009999']],
            $numbers,
            "The posted work phone and fax should be stored with the company, each with its type"
        );
    }

    /**
     * A company the insurance validator refuses (a name over 255 characters) answers 400,
     * and nothing is stored.
     */
    public function testPostInsurerFailingValidationReturnsBadRequest(): void
    {
        $name = self::NAME_PREFIX . " " . str_repeat("x", 255);
        $result = $this->testClient->post(self::ENDPOINT, self::insurerBody($name));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $result->getStatusCode(), "POST should return 400: " . $result->getBody());

        $stored = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS `stored` FROM `insurance_companies` WHERE `name` LIKE ?",
            'stored',
            [self::NAME_PREFIX . "%"]
        );
        $this->assertEquals(0, $stored, "Nothing should have been stored");
    }

    /**
     * HL7's lowercase type code, the one an insurer reads back with, also makes an insurer; so
     * does an inactive one, which stays inactive, and its phone reads back as a phone.
     */
    public function testPostInsurerWithTheHl7TypeCodeRoundTrips(): void
    {
        $name = self::NAME_PREFIX . " Lowercase";
        $body = self::insurerBody($name, [
            'active' => false,
            'type' => [['coding' => [['system' => FhirCodeSystemConstants::HL7_ORGANIZATION_TYPE, 'code' => 'ins']]]],
        ]);
        $result = $this->testClient->post(self::ENDPOINT, $body);
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), "POST should return 201 Created: " . $result->getBody());
        $row = $this->decodeJsonArray($result);
        $this->assertIsString($row['uuid'] ?? null);

        $inactive = QueryUtils::fetchSingleValue("SELECT `inactive` FROM `insurance_companies` WHERE `name` = ?", 'inactive', [$name]);
        $this->assertEquals(1, $inactive, "The insurer should be an insurance company, stored inactive");

        $resource = $this->decodeJsonArray($this->testClient->getOne(self::ENDPOINT, $row['uuid']));
        $this->assertFalse($resource['active'] ?? null);
        $telecoms = $resource['telecom'] ?? null;
        $this->assertIsArray($telecoms);
        $telecom = $telecoms[0] ?? null;
        $this->assertIsArray($telecom);
        $this->assertSame('phone', $telecom['system'] ?? null, "R4 has no 'work' contact point system");
    }

    /**
     * An insurer stored without an address reads back with no address, not an address list
     * holding null; so does one whose address row holds only the country the admin page
     * stores by default.
     */
    public function testInsurerWithoutAddressReadsBackWithoutOne(): void
    {
        $body = self::insurerBody(self::NAME_PREFIX . " No Address");
        unset($body['address']);
        $row = $this->postInsurer($body);
        $this->assertArrayNotHasKey('address', $this->readBack($row['uuid']));

        (new AddressService())->insert(new AddressData('', '', '', '', '', 'USA'), $row['id']);
        $this->assertArrayNotHasKey('address', $this->readBack($row['uuid']), "A country alone isn't an address");
    }

    /**
     * What a GET answers, posted back under another name, stores the same insurer: its address
     * (postal code and country included) and both numbers survive the round trip.
     */
    public function testReadThenPostKeepsTheInsurer(): void
    {
        $original = $this->postInsurer(self::insurerBody(self::NAME_PREFIX . " Original"));
        $originalResource = $this->readBack($original['uuid']);

        $copyBody = FhirPayloadReader::stringKeyed($originalResource);
        unset($copyBody['id'], $copyBody['meta'], $copyBody['text']);
        $copyBody['name'] = self::NAME_PREFIX . " Copy";
        $copy = $this->postInsurer($copyBody);

        $this->assertSame(self::comparable($originalResource), self::comparable($this->readBack($copy['uuid'])));
        $this->assertSame($this->storedParts($original['id']), $this->storedParts($copy['id']));
    }

    /**
     * A North American number written with its country code is stored as its own ten digits,
     * not as the first ten digits the number holds.
     */
    public function testPostStoresANumberWithItsCountryCodeAsItsTenDigits(): void
    {
        $body = self::insurerBody(self::NAME_PREFIX . " E164", ['telecom' => [
            ['system' => 'phone', 'value' => '+15552003003'],
            ['system' => 'fax', 'value' => '+1 555 200 9999'],
        ]]);
        $result = $this->testClient->post(self::ENDPOINT, $body);
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), "POST should return 201 Created: " . $result->getBody());
        $row = $this->decodeJsonArray($result);

        $numbers = [];
        foreach (
            QueryUtils::fetchRecords(
                "SELECT `type`, `area_code`, `prefix`, `number` FROM `phone_numbers` WHERE `foreign_id` = ? ORDER BY `type`",
                [$row['id'] ?? null]
            ) as $number
        ) {
            $numbers[] = [$number['type'], $number['area_code'], $number['prefix'], $number['number']];
        }
        $this->assertSame(
            [[PhoneType::WORK->value, '555', '200', '3003'], [PhoneType::FAX->value, '555', '200', '9999']],
            $numbers
        );
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unstorableProvider(): array
    {
        $address = ['line' => ['4 Workbench Way'], 'city' => 'Testville', 'state' => 'CA', 'postalCode' => '90001'];
        return [
            'zip wider than the column' => [['address' => [['postalCode' => '12345-67890'] + $address]]],
            'state wider than the column' => [['address' => [['state' => str_repeat('s', 36)] + $address]]],
            'address without a state' => [['address' => [array_diff_key($address, ['state' => true])]]],
            'lines joined wider than the column' => [['address' => [['line' => ['4 Workbench Way', str_repeat('a', 150), str_repeat('b', 150)]] + $address]]],
            'address given only as text' => [['address' => [['text' => '4 Workbench Way, Testville, CA 90001']]]],
            'address given only as a district' => [['address' => [['district' => 'Testville County']]]],
            'phone that is not a NANP number' => [['telecom' => [['system' => 'phone', 'value' => '+44 20 7946 0958']]]],
            'phone whose ten digits include another country code' => [['telecom' => [['system' => 'phone', 'value' => '+354 555 1234']]]],
            'fax whose ten digits include another country code' => [['telecom' => [['system' => 'fax', 'value' => '+354 555 1299']]]],
        ];
    }

    /**
     * What the company's address or phone rows cannot hold answers 400, with nothing stored,
     * rather than a 500 or a 201 that dropped it.
     *
     * @param array<string, mixed> $overrides
     */
    #[DataProvider('unstorableProvider')]
    public function testPostInsurerWithUnstorablePartsReturnsBadRequest(array $overrides): void
    {
        $result = $this->testClient->post(self::ENDPOINT, self::insurerBody(self::NAME_PREFIX . " Unstorable", $overrides));
        $this->assertSame(Response::HTTP_BAD_REQUEST, $result->getStatusCode(), "POST should return 400: " . $result->getBody());

        $stored = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS `stored` FROM `insurance_companies` WHERE `name` LIKE ?",
            'stored',
            [self::NAME_PREFIX . "%"]
        );
        $this->assertEquals(0, $stored, "Nothing should have been stored");
    }

    /**
     * The body FHIR clients send for an insurer: the explorer's "Create missing fixtures"
     * seed, plus a second address line, a country, a fax and a payer id.
     *
     * @param array<string, mixed> $overrides top-level elements to replace
     * @return array<string, mixed>
     */
    private static function insurerBody(string $name, array $overrides = []): array
    {
        return $overrides + [
            'resourceType' => 'Organization',
            'active' => true,
            'name' => $name,
            'identifier' => [[
                // the system the read side writes the payer id under
                'system' => FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE,
                'value' => self::CMS_ID,
            ]],
            'type' => [['coding' => [[
                'system' => 'http://terminology.hl7.org/CodeSystem/organization-type',
                'code' => 'Ins',
                'display' => 'Insurance Company',
            ]]]],
            'telecom' => [
                ['system' => 'phone', 'value' => '(555) 200-3003', 'use' => 'work'],
                ['system' => 'fax', 'value' => '(555) 200-9999', 'use' => 'work'],
            ],
            'address' => [[
                'line' => ['4 Workbench Way', 'Suite 200'],
                'city' => 'Testville',
                'state' => 'CA',
                'postalCode' => '90001',
                'country' => 'US',
            ]],
        ];
    }

    /**
     * Posts an insurer that should be created.
     *
     * @param array<string, mixed> $body
     * @return array{id: int, uuid: string}
     */
    private function postInsurer(array $body): array
    {
        $result = $this->testClient->post(self::ENDPOINT, $body);
        $this->assertSame(Response::HTTP_CREATED, $result->getStatusCode(), "POST should return 201 Created: " . $result->getBody());
        $row = $this->decodeJsonArray($result);
        $id = $row['id'] ?? null;
        $uuid = $row['uuid'] ?? null;
        $this->assertIsInt($id, "POST should answer with the stored company's id");
        $this->assertIsString($uuid, "POST should answer with the stored company's uuid");
        return ['id' => $id, 'uuid' => $uuid];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function readBack(string $uuid): array
    {
        $readBack = $this->testClient->getOne(self::ENDPOINT, $uuid);
        $this->assertSame(Response::HTTP_OK, $readBack->getStatusCode());
        return $this->decodeJsonArray($readBack);
    }

    /**
     * A read-back Organization without what differs between two stored companies: id, meta,
     * narrative, name, and the address period the read side dates from the request.
     *
     * @param array<array-key, mixed> $resource
     * @return array<array-key, mixed>
     */
    private static function comparable(array $resource): array
    {
        unset($resource['id'], $resource['meta'], $resource['text'], $resource['name']);
        $addresses = is_array($resource['address'] ?? null) ? $resource['address'] : [];
        $resource['address'] = array_map(
            static function (mixed $address): mixed {
                if (is_array($address)) {
                    unset($address['period']);
                }
                return $address;
            },
            $addresses
        );
        return $resource;
    }

    /**
     * The company's stored address and its numbers' parts by type.
     *
     * @return array{array<mixed>|false, list<array<mixed>>}
     */
    private function storedParts(int $companyId): array
    {
        return [
            QueryUtils::querySingleRow(
                "SELECT `line1`, `line2`, `city`, `state`, `zip`, `country` FROM `addresses` WHERE `foreign_id` = ?",
                [$companyId]
            ),
            QueryUtils::fetchRecords(
                "SELECT `type`, `country_code`, `area_code`, `prefix`, `number` FROM `phone_numbers` WHERE `foreign_id` = ? ORDER BY `type`",
                [$companyId]
            ),
        ];
    }

    /**
     * The codes of a CodeableConcept list.
     *
     * @return list<mixed>
     */
    private static function codes(mixed $concepts): array
    {
        $codes = [];
        foreach (is_array($concepts) ? $concepts : [] as $concept) {
            $codings = is_array($concept) ? ($concept['coding'] ?? []) : [];
            foreach (is_array($codings) ? $codings : [] as $coding) {
                $codes[] = is_array($coding) ? ($coding['code'] ?? null) : null;
            }
        }
        return $codes;
    }

    private static function baseUrl(): string
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true);
        return is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : "https://localhost";
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decodeJsonArray(ResponseInterface $response): array
    {
        $decoded = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($decoded, "Response body should be a JSON object or array");
        return $decoded;
    }
}
