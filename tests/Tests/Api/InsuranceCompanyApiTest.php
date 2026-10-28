<?php

/**
 * InsuranceCompany API Endpoint Tests
 *
 * The insurance company REST API has a pre-existing bug:
 * - GET one: Binary UUID in raw row causes JSON encoding error
 * (POST/PUT previously fataled on a missing InsuranceCompanyService::validate();
 * restored alongside the search parameter support.)
 * (GET all previously did not handle ProcessingResult; fixed by wiring
 * search parameters through to InsuranceCompanyService::search().
 * InsuranceEmployerSearchApiTest covers the GET all search behavior.)
 *
 * These tests document the remaining broken state.
 * Service-layer tests in InsuranceCompanyServiceTest and AddressServiceTest
 * cover the AddressData DTO integration that this PR introduces.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InsuranceCompanyApiTest extends TestCase
{
    private const API_ENDPOINT = "/apis/default/api/insurance_company";
    private const FIXTURE_NAME_PREFIX = "test-fixture-Validate";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        // remove any insurance companies this test class created (the prefix
        // match also sweeps rows leaked by earlier runs)
        $rows = QueryUtils::fetchRecords(
            "SELECT `id` FROM `insurance_companies` WHERE `name` LIKE ?",
            [self::FIXTURE_NAME_PREFIX . "%"]
        );
        foreach ($rows as $row) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM `addresses` WHERE `foreign_id` = ?", [$row['id']]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `phone_numbers` WHERE `foreign_id` = ?", [$row['id']]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `insurance_companies` WHERE `id` = ?", [$row['id']]);
        }
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testGetInsuranceTypes(): void
    {
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $this->testClient->get("/apis/default/api/insurance_type");
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body, 'Should return at least one insurance type');
    }

    #[Test]
    public function testGetOneReturns404ForMissingId(): void
    {
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $this->testClient->getOne(self::API_ENDPOINT, '999999999');
        $this->assertEquals(404, $response->getStatusCode());
    }

    #[Test]
    public function testPostWithValidDataCreatesCompany(): void
    {
        // regression test: post() previously fataled because
        // InsuranceCompanyService::validate() did not exist
        // (empty-string optionals are omitted: the validator applies length
        // rules to present-but-empty values)
        $response = $this->testClient->post(self::API_ENDPOINT, [
            'name' => self::FIXTURE_NAME_PREFIX . " Restored Insurance " . uniqid(),
            'ins_type_code' => '1',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    #[Test]
    public function testPostWithInvalidAddressReturns400(): void
    {
        // company fields valid, address invalid: exercises the second
        // validation branch, which converts a Particle ValidationResult
        // into a ProcessingResult for the 400 response
        $response = $this->testClient->post(self::API_ENDPOINT, [
            'name' => self::FIXTURE_NAME_PREFIX . " Bad Address " . uniqid(),
            'ins_type_code' => '1',
            'line1' => 'X',
        ]);

        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function testPostWithMissingNameReturns400(): void
    {
        $response = $this->testClient->post(self::API_ENDPOINT, [
            'ins_type_code' => '1',
        ]);

        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function testGetAll(): void
    {
        // GET /api/insurance_company — list endpoint. Seed one fixture via
        // POST, then confirm the list surfaces it with a UUID (the UUID
        // path matters: InsuranceCompanyService::search() must stringify
        // the binary column so the response is JSON-encodable).
        $uniqueName = self::FIXTURE_NAME_PREFIX . " List " . uniqid();
        $postResponse = $this->testClient->post(self::API_ENDPOINT, [
            'name' => $uniqueName,
            'ins_type_code' => '1',
        ]);
        $this->assertEquals(201, $postResponse->getStatusCode());

        $response = $this->testClient->get(self::API_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertIsArray($data);

        // Find the fixture row by name and also assert its uuid is a
        // valid UUID string. The weaker "name is in the list" check
        // would pass even if the response omitted uuid entirely, which
        // misses the serialization invariant this test is supposed to
        // protect (PR #14653's InsuranceCompanyService::search uuid-
        // stringify path).
        $found = null;
        foreach ($data as $row) {
            if (is_array($row) && ($row['name'] ?? null) === $uniqueName) {
                $found = $row;
                break;
            }
        }
        $this->assertIsArray($found, 'fixture company must appear in the list');
        $this->assertIsString($found['uuid']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $found['uuid']
        );
    }

    #[Test]
    public function testGetOneByIdSuccess(): void
    {
        // GET /api/insurance_company/{id} on a known-good id. PR #14653
        // fixed getOneById to stringify the binary uuid column; this test
        // locks that invariant at the api tier (ServiceTest covers it at
        // the service tier). The id in the API path is the numeric
        // insurance_companies.id, not the uuid. Response is a bare JSON
        // object of the row (no envelope).
        $uniqueName = self::FIXTURE_NAME_PREFIX . " GetOne " . uniqid();
        $postResponse = $this->testClient->post(self::API_ENDPOINT, [
            'name' => $uniqueName,
            'ins_type_code' => '1',
        ]);
        $this->assertEquals(201, $postResponse->getStatusCode());
        // Insurance company POST returns a bare {"iid": N} shape (no
        // envelope), unlike Patient/Facility which wrap in {data, …}.
        $postBody = json_decode((string) $postResponse->getBody(), true);
        $this->assertIsArray($postBody);
        $this->assertArrayHasKey('iid', $postBody);
        $createdId = $postBody['iid'];
        $this->assertIsNumeric($createdId);

        $response = $this->testClient->getOne(self::API_ENDPOINT, (string) $createdId);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        // Bare-row shape — the row fields live at the top level.
        $this->assertEquals($uniqueName, $body['name']);
        $this->assertEquals($createdId, $body['id']);
        $this->assertIsString($body['uuid']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $body['uuid']
        );
    }

    #[Test]
    public function testPutWithPartialPayloadNarrowsToPatch(): void
    {
        // PUT /api/insurance_company/{id} with a partial payload must act
        // as a semantic PATCH on the named columns — i.e. only the fields
        // the client submitted change, every other column keeps its stored
        // value. Pre-fix, InsuranceCompanyService::update() bound each
        // insurance_companies column from $data unconditionally, so a
        // partial payload emitted PHP warnings on the missing keys and
        // wiped every omitted column to NULL (destructive write masquerading
        // as PATCH).
        $uniqueName = self::FIXTURE_NAME_PREFIX . " Put " . uniqid();
        $postResponse = $this->testClient->post(self::API_ENDPOINT, [
            'name' => $uniqueName,
            'attn' => 'original-attn',
            'cms_id' => '12345',
            'ins_type_code' => '2',
            'x12_receiver_id' => 'rcv-x12',
            'x12_default_partner_id' => '98765',
            'alt_cms_id' => 'alt-67890',
        ]);
        $this->assertEquals(201, $postResponse->getStatusCode());
        $postBody = json_decode((string) $postResponse->getBody(), true);
        $this->assertIsArray($postBody);
        $createdId = $postBody['iid'];
        $this->assertIsNumeric($createdId);

        $renamed = self::FIXTURE_NAME_PREFIX . " Put Renamed " . uniqid();
        $putResponse = $this->testClient->put(
            self::API_ENDPOINT,
            (string) $createdId,
            ['name' => $renamed]
        );
        $this->assertEquals(200, $putResponse->getStatusCode());

        $follow = $this->testClient->getOne(self::API_ENDPOINT, (string) $createdId);
        $this->assertEquals(200, $follow->getStatusCode());
        $after = json_decode((string) $follow->getBody(), true);
        $this->assertIsArray($after);
        $this->assertEquals($renamed, $after['name']);
        // Every other column the POST set must have survived the PATCH.
        $this->assertEquals('original-attn', $after['attn']);
        $this->assertEquals('12345', $after['cms_id']);
        $this->assertEquals('2', $after['ins_type_code']);
        $this->assertEquals('rcv-x12', $after['x12_receiver_id']);
        $this->assertEquals('98765', $after['x12_default_partner_id']);
        $this->assertEquals('alt-67890', $after['alt_cms_id']);
    }

    #[Test]
    public function testPutAddressFieldsMergeOverStoredAddress(): void
    {
        // Address handling has to honour PATCH semantics across the
        // insurance_companies / addresses join:
        //   - A payload that submits only one address field (eg `line1`)
        //     must apply that change without wiping the other address
        //     columns, even though AddressData::fromArray() populates
        //     missing keys with empty strings.
        //   - A payload that submits NO address fields must leave the
        //     stored address untouched (addressService->update on an
        //     unchanged AddressData would wipe fields).
        //   - A payload that submits address fields on a company that
        //     has no stored addresses row (POST without city+state skips
        //     the address insert) must insert, not fail silently.
        //
        // This test covers the first two cases by seeding a full address
        // via POST, then PUTting only `line1`, and asserting the other
        // address columns survive.
        $uniqueName = self::FIXTURE_NAME_PREFIX . " PutAddr " . uniqid();
        $postResponse = $this->testClient->post(self::API_ENDPOINT, [
            'name' => $uniqueName,
            'ins_type_code' => '1',
            'line1' => '123 Example St',
            'line2' => 'Suite 4',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip' => '62701',
            'country' => 'USA',
        ]);
        $this->assertEquals(201, $postResponse->getStatusCode());
        $postBody = json_decode((string) $postResponse->getBody(), true);
        $this->assertIsArray($postBody);
        $createdId = $postBody['iid'];
        $this->assertIsNumeric($createdId);

        // The controller's PUT handler still runs the INSERT-context
        // validator (requires `name`), so include it unchanged on the
        // PATCH — the point of this test is the address-merge path, not
        // the insurance_companies validator context.
        $putResponse = $this->testClient->put(
            self::API_ENDPOINT,
            (string) $createdId,
            [
                'name' => $uniqueName,
                'line1' => '456 Updated Ave',
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode());

        // getOneById returns only insurance_companies columns, but the
        // list endpoint (search) LEFT JOINs addresses, so use the list
        // and locate the fixture row by id to assert the address state.
        $after = $this->findRowInListById((int) $createdId);
        $this->assertEquals('456 Updated Ave', $after['line1'] ?? null);
        $this->assertEquals('Suite 4', $after['line2'] ?? null);
        $this->assertEquals('Springfield', $after['city'] ?? null);
        $this->assertEquals('IL', $after['state'] ?? null);
        $this->assertEquals('62701', $after['zip'] ?? null);
        $this->assertEquals('USA', $after['country'] ?? null);
    }

    #[Test]
    public function testPutInsertsAddressRowForCompanyThatHadNone(): void
    {
        // POST without city+state intentionally skips the address insert
        // (mirrors the pre-existing insert() guard). A subsequent PUT
        // that supplies address fields must create the addresses row —
        // AddressService::update() returns null when no addresses row
        // exists for this foreign_id, so the service has to fall through
        // to AddressService::insert().
        $uniqueName = self::FIXTURE_NAME_PREFIX . " PutAddrInsert " . uniqid();
        $postResponse = $this->testClient->post(self::API_ENDPOINT, [
            'name' => $uniqueName,
            'ins_type_code' => '1',
        ]);
        $this->assertEquals(201, $postResponse->getStatusCode());
        $postBody = json_decode((string) $postResponse->getBody(), true);
        $this->assertIsArray($postBody);
        $createdId = $postBody['iid'];
        $this->assertIsNumeric($createdId);

        $putResponse = $this->testClient->put(
            self::API_ENDPOINT,
            (string) $createdId,
            [
                'name' => $uniqueName,
                'line1' => '789 Fresh St',
                'city' => 'Metropolis',
                'state' => 'IL',
                'zip' => '62960',
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode());

        $after = $this->findRowInListById((int) $createdId);
        $this->assertEquals('789 Fresh St', $after['line1'] ?? null);
        $this->assertEquals('Metropolis', $after['city'] ?? null);
        $this->assertEquals('IL', $after['state'] ?? null);
        $this->assertEquals('62960', $after['zip'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function findRowInListById(int $id): array
    {
        $response = $this->testClient->get(self::API_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $data = $body['data'] ?? [];
        $this->assertIsArray($data);
        foreach ($data as $row) {
            if (is_array($row) && is_numeric($row['id'] ?? null) && (int) $row['id'] === $id) {
                /** @var array<string, mixed> $row */
                return $row;
            }
        }
        $this->fail("row id=$id not found in list response");
    }
}
