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
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
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
     * An insurer posted as JSON is stored as an insurance company, with its address, phone
     * and payer id, and reads back as an Organization of type ins.
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

        // the read side doesn't map an insurance company's zip to postalCode, so read the stored row
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

        $phone = QueryUtils::fetchSingleValue(
            "SELECT CONCAT(`area_code`, `prefix`, `number`) AS `phone` FROM `phone_numbers` WHERE `foreign_id` = ?",
            'phone',
            [$row['id']]
        );
        $this->assertSame('5552003003', $phone, "The posted work phone should be stored with the company");
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
     * The body FHIR clients send for an insurer: the explorer's "Create missing fixtures"
     * seed, plus a second address line, a country and a payer id.
     *
     * @return array<string, mixed>
     */
    private static function insurerBody(string $name): array
    {
        return [
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
            'telecom' => [['system' => 'phone', 'value' => '(555) 200-3003', 'use' => 'work']],
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
