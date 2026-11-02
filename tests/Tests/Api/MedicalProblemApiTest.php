<?php

/**
 * Medical Problem API Endpoint Test Cases
 *
 * Covers the top-level (non patient-scoped) standard REST endpoints
 * (Part of #12343):
 *   GET /api/medical_problem
 *   GET /api/medical_problem/:muuid
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Konstantin Sanilas <konstantinsanilas@gmail.com>
 * @copyright Copyright (c) 2026 Konstantin Sanilas <konstantinsanilas@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * The patient-scoped medical_problem routes are covered by
 * PatientAllergyConditionApiTest; this class covers the top-level routes,
 * which go through ConditionRestController::getAll() / getOne() without a
 * patient binding.
 *
 * Test data is seeded directly through FixtureManager::installConditionFixtures()
 * (one patient + one medical_problem row) and removed in tearDown.
 */
class MedicalProblemApiTest extends TestCase
{
    private const MEDICAL_PROBLEM_API_ENDPOINT = "/apis/default/api/medical_problem";

    /** Title used by FixtureManager::installConditionFixtures(). */
    private const SEEDED_CONDITION_TITLE = "Essential hypertension";

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private string $conditionUuid;
    private string $patientUuid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $seeded = $this->fixtureManager->installConditionFixtures();
        $this->patientUuid = $seeded['patientUuid'];
        $this->conditionUuid = $seeded['conditionUuid'];
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeConditionFixtures();
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * GET /api/medical_problem returns the standard envelope with a list of
     * medical problems that includes the seeded condition.
     */
    public function testGetAllReturnsSeededMedicalProblem(): void
    {
        $response = $this->testClient->get(self::MEDICAL_PROBLEM_API_ENDPOINT);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $body = $this->decodeBody($response);
        $this->assertSame([], $body["validationErrors"] ?? null);
        $this->assertSame([], $body["internalErrors"] ?? null);
        $this->assertArrayHasKey("data", $body);
        $this->assertIsArray($body["data"]);
        $this->assertNotEmpty($body["data"], "List should include at least the seeded medical problem");

        $seeded = null;
        foreach ($body["data"] as $record) {
            $this->assertIsArray($record);
            $this->assertSame("medical_problem", $record["type"] ?? null, "Only medical problems should be returned");
            if (($record["uuid"] ?? null) === $this->conditionUuid) {
                $seeded = $record;
            }
        }
        $this->assertNotNull($seeded, "List should include the seeded medical problem");
        $this->assertSeededMedicalProblem($seeded);
    }

    /**
     * GET /api/medical_problem/:muuid returns the single seeded condition
     * (data is an object, not a list).
     */
    public function testGetOneReturnsSeededMedicalProblem(): void
    {
        $response = $this->testClient->get(self::MEDICAL_PROBLEM_API_ENDPOINT . "/" . $this->conditionUuid);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $body = $this->decodeBody($response);
        $this->assertSame([], $body["validationErrors"] ?? null);
        $this->assertSame([], $body["internalErrors"] ?? null);
        $this->assertArrayHasKey("data", $body);
        $this->assertIsArray($body["data"]);
        $this->assertSame("medical_problem", $body["data"]["type"] ?? null);
        $this->assertSeededMedicalProblem($body["data"]);
    }

    /**
     * A well-formed but unknown uuid returns 404 with an empty data payload.
     */
    public function testGetOneUnknownUuidReturnsNotFound(): void
    {
        $unknownUuid = $this->fixtureManager->getUnregisteredUuid();

        $response = $this->testClient->get(self::MEDICAL_PROBLEM_API_ENDPOINT . "/" . $unknownUuid);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $body = $this->decodeBody($response);
        $this->assertSame([], $body["validationErrors"] ?? null);
        $this->assertSame([], $body["data"] ?? null);
    }

    /**
     * Listing without an access token is rejected.
     */
    public function testGetAllWithoutTokenReturnsUnauthorized(): void
    {
        $response = $this->createUnauthenticatedClient()->get(self::MEDICAL_PROBLEM_API_ENDPOINT);
        $this->assertUnauthorized($response);
    }

    /**
     * Reading one record without an access token is rejected.
     */
    public function testGetOneWithoutTokenReturnsUnauthorized(): void
    {
        $response = $this->createUnauthenticatedClient()
            ->get(self::MEDICAL_PROBLEM_API_ENDPOINT . "/" . $this->conditionUuid);
        $this->assertUnauthorized($response);
    }

    /**
     * @param array<array-key, mixed> $record
     */
    private function assertSeededMedicalProblem(array $record): void
    {
        $this->assertSame($this->conditionUuid, $record["uuid"] ?? null);
        $this->assertSame(self::SEEDED_CONDITION_TITLE, $record["title"] ?? null);
        $this->assertSame($this->patientUuid, $record["puuid"] ?? null, "Record should reference the seeded patient");
    }

    private function createUnauthenticatedClient(): ApiTestClient
    {
        // Deliberately do NOT call setAuthToken().
        return new ApiTestClient(getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost", false);
    }

    private function assertUnauthorized(ResponseInterface $response): void
    {
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $body = $this->decodeBody($response);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayNotHasKey("data", $body, "401 body should not carry medical problem data");
    }

    /**
     * @return array<array-key, mixed>
     */
    private function decodeBody(ResponseInterface $response): array
    {
        $decoded = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($decoded, "Response body should be a JSON object");
        return $decoded;
    }
}
