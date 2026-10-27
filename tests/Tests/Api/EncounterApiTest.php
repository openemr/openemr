<?php

/**
 * Encounter API Endpoint Test Cases
 *
 * Covers the uuid-based encounter endpoints:
 *   - GET  /api/patient/{puuid}/encounter          — list
 *   - GET  /api/patient/{puuid}/encounter/{euuid}  — single
 *   - POST /api/patient/{puuid}/encounter          — create
 *   - PUT  /api/patient/{puuid}/encounter/{euuid}  — update
 *
 * Uses `EncounterFixtureManager` so the fresh patient / facility /
 * fixture-encounter life-cycle covers teardown of the new encounter
 * rows this test creates.
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
use OpenEMR\Tests\Fixtures\EncounterFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class EncounterApiTest extends TestCase
{
    private ApiTestClient $testClient;
    private EncounterFixtureManager $fixtureManager;
    private string $fixturePuuid;
    private string $fixtureEuuid;
    private int $officeVisitCatId;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new EncounterFixtureManager();
        $this->fixtureManager->installFixtures();

        // The fixture creates 13 patients but only ONE encounter — join
        // form_encounter back to patient_data to pick the specific
        // puuid/euuid pair the fixture seeded.
        $rows = QueryUtils::fetchRecords(
            "SELECT pd.uuid AS puuid, fe.uuid AS euuid "
            . "FROM form_encounter fe "
            . "JOIN patient_data pd ON fe.pid = pd.pid "
            . "WHERE fe.reason LIKE ? "
            . "ORDER BY fe.id DESC LIMIT 1",
            ['test-fixture-%']
        );
        $this->assertNotEmpty($rows, 'fixture encounter must exist');
        $first = $rows[0];
        $this->assertIsString($first['puuid']);
        $this->assertIsString($first['euuid']);
        $this->fixturePuuid = UuidRegistry::uuidToString($first['puuid']);
        $this->fixtureEuuid = UuidRegistry::uuidToString($first['euuid']);

        // pc_catid is a numeric FK into openemr_postcalendar_categories.
        // Resolve the demo install's "office_visit" category id so the
        // POST payload doesn't hard-code a value that drifts across
        // environments.
        $catId = QueryUtils::fetchSingleValue(
            "SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_constant_id = ?",
            'pc_catid',
            ['office_visit']
        );
        $this->assertIsNumeric($catId);
        $this->officeVisitCatId = (int) $catId;
    }

    protected function tearDown(): void
    {
        // testPostCreatesEncounter writes an encounter via the API, which
        // inserts a form_encounter row (removed by removeFixtures reason-
        // prefix sweep since we pass reason='test-fixture-...') AND a
        // `forms` row with formdir='newpatient' linking the encounter.
        // The forms row isn't scoped by reason, so sweep it explicitly
        // by the fixture pid before removing patient_data.
        $pids = QueryUtils::fetchTableColumn(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ?",
            'pid',
            ['test-fixture-%']
        );
        if (count($pids) > 0) {
            $placeholders = implode(',', array_fill(0, count($pids), '?'));
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM forms WHERE pid IN ($placeholders) AND formdir = 'newpatient'",
                $pids
            );
        }
        $this->fixtureManager->removeFixtures();
    }

    private function listEndpoint(): string
    {
        return "/apis/default/api/patient/" . $this->fixturePuuid . "/encounter";
    }

    #[Test]
    public function testGetAllListsFixtureEncounter(): void
    {
        $response = $this->testClient->get($this->listEndpoint());
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        // EncounterService::search returns rows keyed with `euuid` (not
        // the bare `uuid` field — that distinguishes it from facility /
        // provider uuids on the same joined row).
        $uuids = array_map(
            static fn($row): string => is_array($row) && is_string($row['euuid'] ?? null) ? (string) $row['euuid'] : '',
            $data
        );
        $this->assertContains($this->fixtureEuuid, $uuids);
    }

    #[Test]
    public function testGetOneReturnsFixtureEncounter(): void
    {
        $response = $this->testClient->getOne($this->listEndpoint(), $this->fixtureEuuid);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $data = $body['data'];
        $this->assertIsArray($data);
        // getOne returns a single-row envelope with the encounter's
        // `euuid` plus patient (puuid) context.
        $this->assertEquals($this->fixtureEuuid, $data['euuid'] ?? null);
    }

    #[Test]
    public function testPostCreatesEncounter(): void
    {
        $response = $this->testClient->post($this->listEndpoint(), [
            'pc_catid' => $this->officeVisitCatId,
            'class_code' => 'AMB',
            'reason' => 'test-fixture-EncounterApiTest-post',
        ]);
        $this->assertEquals(201, $response->getStatusCode(), 'POST should 201. Body: ' . (string) $response->getBody());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertIsArray($data);
        $this->assertArrayHasKey('euuid', $data);
        $this->assertIsString($data['euuid']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $data['euuid']
        );
    }

    #[Test]
    public function testPutUpdatesEncounterReason(): void
    {
        // DATABASE_UPDATE_CONTEXT validation requires `user` + `group`
        // in the payload. Unlike POST, the controller's put() handler
        // does NOT inject these from the authenticated session, so the
        // client has to supply them explicitly.
        $putResponse = $this->testClient->put(
            $this->listEndpoint(),
            $this->fixtureEuuid,
            [
                'pc_catid' => $this->officeVisitCatId,
                'class_code' => 'AMB',
                'reason' => 'test-fixture-EncounterApiTest-put-updated',
                'user' => 'admin',
                'group' => 'Default',
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode(), 'PUT should 200. Body: ' . (string) $putResponse->getBody());

        $follow = $this->testClient->getOne($this->listEndpoint(), $this->fixtureEuuid);
        $this->assertEquals(200, $follow->getStatusCode());
        $after = json_decode((string) $follow->getBody(), true);
        $this->assertIsArray($after);
        $data = $after['data'];
        $this->assertIsArray($data);
        $this->assertEquals('test-fixture-EncounterApiTest-put-updated', $data['reason'] ?? null);
    }
}
