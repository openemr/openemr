<?php

/**
 * Patient Insurance API Endpoint Test Cases
 *
 * Covers the patient-compartment insurance endpoints:
 *   GET  /api/patient/{puuid}/insurance              — list
 *   GET  /api/patient/{puuid}/insurance/{uuid}       — single
 *   POST /api/patient/{puuid}/insurance              — create
 *   PUT  /api/patient/{puuid}/insurance/{uuid}       — update
 *
 * Insurance coverage lives in `insurance_data` keyed on `pid` + `type`
 * (primary/secondary/tertiary) + `date` (effective date). This test
 * seeds a fresh patient + a fresh insurance_company via FixtureManager
 * so the policy POST doesn't collide with demo data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PatientInsuranceApiTest extends TestCase
{
    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private string $fixturePuuid;
    private int $fixtureInsurerId;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $this->fixtureManager->installInsuranceCompanyFixture();

        $puuidBytes = QueryUtils::fetchSingleValue(
            "SELECT uuid FROM patient_data WHERE pubpid LIKE ? ORDER BY pid DESC LIMIT 1",
            'uuid',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . "%"]
        );
        $this->assertIsString($puuidBytes);
        $this->fixturePuuid = UuidRegistry::uuidToString($puuidBytes);

        $insurerId = QueryUtils::fetchSingleValue(
            "SELECT id FROM insurance_companies WHERE name = ? ORDER BY id DESC LIMIT 1",
            'id',
            ['test-fixture-insurer']
        );
        $this->assertIsNumeric($insurerId);
        $this->fixtureInsurerId = (int) $insurerId;
    }

    protected function tearDown(): void
    {
        // Remove in dependency order: insurance_data (coverage) rows
        // reference pid + provider (insurance_company id), so clean
        // those first. Wrap in try/finally so cleanupClient still runs.
        try {
            $this->fixtureManager->removeCoverageFixtures();
            $this->fixtureManager->removeInsuranceCompanyFixtures();
            $this->fixtureManager->removePatientFixtures();
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
    }

    private function listEndpoint(): string
    {
        return "/apis/default/api/patient/" . $this->fixturePuuid . "/insurance";
    }

    /**
     * @return array{uuid: string}
     */
    private function postFixturePolicy(string $type = 'primary'): array
    {
        $response = $this->testClient->post($this->listEndpoint(), [
            'type' => $type,
            'provider' => (string) $this->fixtureInsurerId,
            'policy_number' => 'TEST-POLICY-' . uniqid(),
            'subscriber_lname' => 'FixtureLast',
            'subscriber_fname' => 'FixtureFirst',
            'subscriber_relationship' => 'self',
            'subscriber_DOB' => '1980-01-01',
            'subscriber_street' => '123 Fixture Street',
            'subscriber_postal_code' => '62701',
            'subscriber_city' => 'Springfield',
            'subscriber_state' => 'IL',
            'subscriber_sex' => 'Male',
            'accept_assignment' => 'TRUE',
            'date' => '2026-01-01',
            'date_end' => null,
        ]);
        $this->assertEquals(200, $response->getStatusCode(), 'POST should 200. Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame([], $body['validationErrors'] ?? ['SENTINEL'], 'POST must produce no validation errors. Body: ' . (string) $response->getBody());
        $data = $body['data'];
        $this->assertIsArray($data);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertIsString($data['uuid']);
        return ['uuid' => $data['uuid']];
    }

    #[Test]
    public function testPostCreatesInsurancePolicy(): void
    {
        $created = $this->postFixturePolicy();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $created['uuid']
        );
    }

    #[Test]
    public function testGetAllListsCreatedPolicy(): void
    {
        $created = $this->postFixturePolicy();

        $response = $this->testClient->get($this->listEndpoint());
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertIsArray($data);
        $uuids = array_map(
            static fn($row): string => is_array($row) && is_string($row['uuid'] ?? null) ? (string) $row['uuid'] : '',
            $data
        );
        $this->assertContains($created['uuid'], $uuids);
    }

    #[Test]
    public function testGetOneReturnsCreatedPolicy(): void
    {
        $created = $this->postFixturePolicy();

        $response = $this->testClient->getOne($this->listEndpoint(), $created['uuid']);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $data = $body['data'];
        $this->assertIsArray($data);
        $this->assertEquals($created['uuid'], $data['uuid'] ?? null);
    }

    #[Test]
    public function testPutUpdatesInsurancePolicy(): void
    {
        $created = $this->postFixturePolicy();

        $renamed = 'FixtureLast-Renamed-' . uniqid();
        $putResponse = $this->testClient->put(
            $this->listEndpoint(),
            $created['uuid'],
            [
                'type' => 'primary',
                'provider' => (string) $this->fixtureInsurerId,
                'policy_number' => 'TEST-POLICY-UPDATED',
                'subscriber_lname' => $renamed,
                'subscriber_fname' => 'FixtureFirst',
                'subscriber_relationship' => 'self',
                'subscriber_DOB' => '1980-01-01',
                'subscriber_street' => '456 Updated Street',
                'subscriber_postal_code' => '62702',
                'subscriber_city' => 'Springfield',
                'subscriber_state' => 'IL',
                'subscriber_sex' => 'Male',
                'accept_assignment' => 'TRUE',
                'date' => '2026-01-01',
                'date_end' => null,
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode(), 'PUT should 200. Body: ' . (string) $putResponse->getBody());

        $follow = $this->testClient->getOne($this->listEndpoint(), $created['uuid']);
        $this->assertEquals(200, $follow->getStatusCode());
        $after = json_decode((string) $follow->getBody(), true);
        $this->assertIsArray($after);
        $data = $after['data'];
        $this->assertIsArray($data);
        $this->assertEquals($renamed, $data['subscriber_lname'] ?? null);
        $this->assertEquals('456 Updated Street', $data['subscriber_street'] ?? null);
    }
}
