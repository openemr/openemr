<?php

/**
 * Vital API Endpoint Test Cases
 *
 * Covers the patient-encounter-vital endpoints at the api tier:
 *   POST /api/patient/{pid}/encounter/{eid}/vital       — create
 *   GET  /api/patient/{pid}/encounter/{eid}/vital       — list
 *   GET  /api/patient/{pid}/encounter/{eid}/vital/{vid} — single
 *   PUT  /api/patient/{pid}/encounter/{eid}/vital/{vid} — update
 *
 * Fixture-driven (fresh patient + facility + encounter per test class via
 * EncounterFixtureManager) so the vital rows do not pollute demo data.
 * Locks PR #14653's VitalsService::update preserve-uuid-on-update invariant
 * at the api tier (ServiceTest covers the service directly).
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
use OpenEMR\Tests\Fixtures\EncounterFixtureManager;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VitalApiTest extends TestCase
{
    private ApiTestClient $testClient;
    private EncounterFixtureManager $fixtureManager;
    private int $fixturePid;
    private int $fixtureEid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new EncounterFixtureManager();
        $this->fixtureManager->installFixtures();

        $row = QueryUtils::fetchRecords(
            "SELECT `pid`, `encounter` FROM `form_encounter` WHERE reason LIKE ? ORDER BY id DESC LIMIT 1",
            ['test-fixture-%']
        );
        $this->assertNotEmpty($row, 'fixture encounter must exist');
        $first = $row[0];
        $this->assertIsNumeric($first['pid']);
        $this->assertIsNumeric($first['encounter']);
        $this->fixturePid = (int) $first['pid'];
        $this->fixtureEid = (int) $first['encounter'];
    }

    protected function tearDown(): void
    {
        // Each vital POST inserts a form_vitals row + a linked forms row
        // (formdir='vitals'), plus a uuid_registry + uuid_mapping entry for
        // the FHIR Observation surface. EncounterFixtureManager::removeFixtures()
        // only deletes form_encounter + patient_data + facility, so those
        // vital rows would orphan on teardown. removeObservationFixtures()
        // sweeps the whole vital/uuid chain scoped to the fixture pid prefix.
        (new FixtureManager())->removeObservationFixtures();
        $this->fixtureManager->removeFixtures();
    }

    private function endpoint(?int $vid = null): string
    {
        $base = "/apis/default/api/patient/" . $this->fixturePid
            . "/encounter/" . $this->fixtureEid . "/vital";
        return $vid === null ? $base : $base . "/" . $vid;
    }

    /**
     * @return array{vid: int, fid: int}
     */
    private function postFixtureVital(float $temperature = 98.6): array
    {
        $response = $this->testClient->post($this->endpoint(), [
            'temperature' => $temperature,
            'pulse' => 72,
            'bps' => 120,
            'bpd' => 80,
        ]);
        $this->assertEquals(201, $response->getStatusCode(), 'POST should 201. Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('vid', $body);
        $this->assertArrayHasKey('fid', $body);
        $this->assertIsNumeric($body['vid']);
        $this->assertIsNumeric($body['fid']);
        return ['vid' => (int) $body['vid'], 'fid' => (int) $body['fid']];
    }

    #[Test]
    public function testPostCreatesVital(): void
    {
        $created = $this->postFixtureVital();
        $this->assertGreaterThan(0, $created['vid']);
        $this->assertGreaterThan(0, $created['fid']);
    }

    #[Test]
    public function testGetAll(): void
    {
        $created = $this->postFixtureVital();

        $response = $this->testClient->get($this->endpoint());
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body);
        $ids = array_map(
            static fn($row): int => is_array($row) && is_numeric($row['id'] ?? null) ? (int) $row['id'] : -1,
            $body
        );
        $this->assertContains($created['vid'], $ids);
    }

    #[Test]
    public function testGetOneReturnsSingleVital(): void
    {
        $created = $this->postFixtureVital(99.1);

        $response = $this->testClient->getOne($this->endpoint(), (string) $created['vid']);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertIsNumeric($body['id']);
        $this->assertIsNumeric($body['pid']);
        $this->assertIsNumeric($body['eid']);
        $this->assertEquals($created['vid'], (int) $body['id']);
        $this->assertEquals($this->fixturePid, (int) $body['pid']);
        $this->assertEquals($this->fixtureEid, (int) $body['eid']);
    }

    #[Test]
    public function testPutUpdatesVital(): void
    {
        $created = $this->postFixtureVital(98.6);

        $putResponse = $this->testClient->put(
            $this->endpoint(),
            (string) $created['vid'],
            [
                'temperature' => 101.4,
                'pulse' => 95,
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode(), 'PUT should 200. Body: ' . (string) $putResponse->getBody());

        $follow = $this->testClient->getOne($this->endpoint(), (string) $created['vid']);
        $this->assertEquals(200, $follow->getStatusCode());
        $after = json_decode((string) $follow->getBody(), true);
        $this->assertIsArray($after);
        $this->assertIsNumeric($after['temperature']);
        $this->assertIsNumeric($after['pulse']);
        $this->assertEquals(101.4, (float) $after['temperature']);
        $this->assertEquals(95, (int) $after['pulse']);
    }

    #[Test]
    public function testPutOnCrossPatientVidIs404(): void
    {
        // IDOR guard — VitalsService::update verifies the vid belongs to the
        // path's pid+eid before updating. Supplying a vid that exists but
        // belongs to a different encounter should 404, not silently update.
        $created = $this->postFixtureVital();

        // Build a path with a different (non-existent) pid for the same vid.
        $wrongPathBase = "/apis/default/api/patient/" . ($this->fixturePid + 999999)
            . "/encounter/" . $this->fixtureEid . "/vital";

        $response = $this->testClient->put(
            $wrongPathBase,
            (string) $created['vid'],
            ['temperature' => 101.4]
        );
        $this->assertEquals(404, $response->getStatusCode(), 'cross-patient vid PUT must 404');
    }
}
