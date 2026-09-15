<?php

/**
 * Insurance and Employer Search API Tests
 *
 * Full HTTP tests for the search query parameter support on
 * GET /api/insurance_company and GET /api/patient/:puuid/employer, including
 * the 400 responses for unsupported parameters and invalid values that the
 * controller allowlists and the hardened SearchFieldStatementResolver provide.
 *
 * Runs in the api test suite (requires a running OpenEMR instance; set
 * OPENEMR_BASE_URL_API when the instance is not at https://localhost).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\InsuranceCompanyService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InsuranceEmployerSearchApiTest extends TestCase
{
    private const COMPANY_ENDPOINT = "/apis/default/api/insurance_company";
    private const PATIENT_ENDPOINT = "/apis/default/api/patient";

    private ApiTestClient $client;
    private InsuranceCompanyService $companyService;
    private string $namePrefix;

    /** @var list<int|string> */
    private array $createdCompanyIds = [];

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->client = new ApiTestClient($baseUrl, false);
        $this->client->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
        $this->companyService = new InsuranceCompanyService();
        // unique per run so searches only match rows this test created
        $this->namePrefix = "test-fixture-ApiSearch-" . uniqid();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdCompanyIds as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM `addresses` WHERE `foreign_id` = ?", [$id]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `phone_numbers` WHERE `foreign_id` = ?", [$id]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `insurance_companies` WHERE `id` = ?", [$id]);
        }
        $this->client->cleanupRevokeAuth();
        $this->client->cleanupClient();
    }

    /**
     * Seeds through the service layer to keep this test focused on GET
     * behavior; POST coverage lives in InsuranceCompanyApiTest.
     */
    private function createCompany(string $nameSuffix, string $cmsId): void
    {
        $id = $this->companyService->insert([
            "name" => $this->namePrefix . " " . $nameSuffix,
            "attn" => "Claims",
            "cms_id" => $cmsId,
            "ins_type_code" => "2",
            "x12_receiver_id" => "",
            "x12_default_partner_id" => "",
            "alt_cms_id" => "",
            "line1" => "100 Insurance Blvd",
            "line2" => "",
            "city" => "Rutland",
            "state" => "VT",
            "zip" => "05701",
            "country" => "USA",
        ]);
        $this->createdCompanyIds[] = $id;
    }

    #[Test]
    public function testInsuranceCompanyGetAllWithoutParamsReturns200(): void
    {
        // regression test: GET all previously returned a ProcessingResult into
        // responseHandler(), which does not handle it
        $this->createCompany("Plain", "88880");
        $response = $this->client->get(self::COMPANY_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());
        /** @var array{data: list<array<string, mixed>>} $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body["data"]);
    }

    #[Test]
    public function testInsuranceCompanySearchByNameContains(): void
    {
        $this->createCompany("Alpha", "88881");
        $this->createCompany("Beta", "88882");

        $response = $this->client->get(self::COMPANY_ENDPOINT, ["name" => $this->namePrefix]);
        $this->assertEquals(200, $response->getStatusCode());
        /** @var array{data: list<array{name: string}>} $body */
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(2, $body["data"]);
        foreach ($body["data"] as $record) {
            $this->assertStringContainsString($this->namePrefix, $record["name"]);
        }
    }

    #[Test]
    public function testInsuranceCompanySearchByCmsIdExact(): void
    {
        $this->createCompany("Gamma", "88883");
        $this->createCompany("Delta", "88884");

        $response = $this->client->get(self::COMPANY_ENDPOINT, [
            "name" => $this->namePrefix,
            "cms_id" => "88883",
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        /** @var array{data: list<array{name: string}>} $body */
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $body["data"]);
        $this->assertStringContainsString("Gamma", $body["data"][0]["name"]);
    }

    #[Test]
    public function testInsuranceCompanyUnsupportedParamReturns400(): void
    {
        $response = $this->client->get(self::COMPANY_ENDPOINT, ["not_a_field" => "1"]);
        $this->assertEquals(400, $response->getStatusCode());
        /** @var array{validationErrors: array<int|string, mixed>} $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body["validationErrors"]);
    }

    #[Test]
    public function testInsuranceCompanyHostileParamNameReturns400(): void
    {
        // hostile parameter names must produce a clean 400, never a SQL error
        $response = $this->client->get(self::COMPANY_ENDPOINT, ["name; DROP TABLE x" => "1"]);
        $this->assertEquals(400, $response->getStatusCode());
        /** @var array{validationErrors: array<int|string, mixed>} $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertNotEmpty($body["validationErrors"]);
        // and the hostile name must not be reflected back
        $this->assertStringNotContainsString("DROP TABLE", (string) $response->getBody());
    }

    #[Test]
    public function testEmployerSearchMalformedPatientUuidReturns400(): void
    {
        $response = $this->client->get(self::PATIENT_ENDPOINT . "/not-a-uuid/employer");
        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function testEmployerSearchUnsupportedParamReturns400(): void
    {
        // valid uuid format is all that the route requires before the controller runs
        $uuid = "11111111-2222-3333-4444-555555555555";
        $response = $this->client->get(self::PATIENT_ENDPOINT . "/{$uuid}/employer", ["not_a_field" => "1"]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function testEmployerSearchInvalidDateValueReturns400(): void
    {
        $uuid = "11111111-2222-3333-4444-555555555555";
        $response = $this->client->get(self::PATIENT_ENDPOINT . "/{$uuid}/employer", ["start_date" => "notadate"]);
        $this->assertEquals(400, $response->getStatusCode());
    }

    #[Test]
    public function testEmployerSearchValidUuidNoDataReturnsEmpty200(): void
    {
        $uuid = "11111111-2222-3333-4444-555555555555";
        $response = $this->client->get(self::PATIENT_ENDPOINT . "/{$uuid}/employer");
        $this->assertEquals(200, $response->getStatusCode());
        /** @var array{data: list<mixed>} $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertEmpty($body["data"]);
    }
}
