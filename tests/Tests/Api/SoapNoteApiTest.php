<?php

/**
 * SOAP Note API Endpoint Test Cases
 *
 * Covers the integer-id-based soap-note sub-routes on an encounter:
 *   - GET  /api/patient/{pid}/encounter/{eid}/soap_note        — list
 *   - GET  /api/patient/{pid}/encounter/{eid}/soap_note/{sid}  — single
 *   - POST /api/patient/{pid}/encounter/{eid}/soap_note        — create
 *   - PUT  /api/patient/{pid}/encounter/{eid}/soap_note/{sid}  — update
 *
 * Uses `EncounterFixtureManager` for the patient/facility/encounter
 * chain. tearDown explicitly sweeps `form_soap` + the linked `forms`
 * row (formdir='soap' per `EncounterService::insertSoapNote`) scoped
 * to the fixture-pid prefix — `EncounterFixtureManager::removeFixtures()`
 * only deletes form_encounter + patient_data + facility, so these
 * rows would orphan otherwise. Analogous to the
 * `removeObservationFixtures()` sweep `VitalApiTest` uses for
 * `form_vitals`.
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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SoapNoteApiTest extends TestCase
{
    private const SUBJECTIVE_FIXTURE_PREFIX = "test-fixture-SoapNoteApiTest";

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

        $rows = QueryUtils::fetchRecords(
            "SELECT pid, encounter FROM form_encounter WHERE reason LIKE ? ORDER BY id DESC LIMIT 1",
            ['test-fixture-%']
        );
        $this->assertNotEmpty($rows);
        $first = $rows[0];
        $this->assertIsNumeric($first['pid']);
        $this->assertIsNumeric($first['encounter']);
        $this->fixturePid = (int) $first['pid'];
        $this->fixtureEid = (int) $first['encounter'];
    }

    protected function tearDown(): void
    {
        // Each POST writes a form_soap row + a `forms` row with
        // formdir='soap' (see EncounterService::insertSoapNote). The
        // EncounterFixtureManager removes form_encounter + patient_data
        // + facility but leaves form_soap and the linked forms row
        // behind. Scope the sweep to the fixture pid so we don't touch
        // other patients' soap notes.
        $pids = QueryUtils::fetchTableColumn(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ?",
            'pid',
            ['test-fixture-%']
        );
        if (count($pids) > 0) {
            $placeholders = implode(',', array_fill(0, count($pids), '?'));
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM form_soap WHERE pid IN ($placeholders)",
                $pids
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM forms WHERE pid IN ($placeholders) AND formdir = 'soap'",
                $pids
            );
        }
        $this->fixtureManager->removeFixtures();
    }

    private function listEndpoint(): string
    {
        return "/apis/default/api/patient/" . $this->fixturePid
            . "/encounter/" . $this->fixtureEid . "/soap_note";
    }

    /**
     * @return array{sid: int, fid: int, subjective: string}
     */
    private function postFixtureSoap(string $tag = ''): array
    {
        $subjective = self::SUBJECTIVE_FIXTURE_PREFIX . "-" . ($tag !== '' ? $tag : uniqid());
        $response = $this->testClient->post($this->listEndpoint(), [
            'subjective' => $subjective,
            'objective' => 'patient looks well',
            'assessment' => 'routine',
            'plan' => 'follow up',
        ]);
        $this->assertEquals(201, $response->getStatusCode(), 'POST should 201. Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('sid', $body);
        $this->assertArrayHasKey('fid', $body);
        $this->assertIsNumeric($body['sid']);
        $this->assertIsNumeric($body['fid']);
        return [
            'sid' => (int) $body['sid'],
            'fid' => (int) $body['fid'],
            'subjective' => $subjective,
        ];
    }

    #[Test]
    public function testPostCreatesSoapNote(): void
    {
        $created = $this->postFixtureSoap('post');
        $this->assertGreaterThan(0, $created['sid']);
        $this->assertGreaterThan(0, $created['fid']);
    }

    #[Test]
    public function testGetAll(): void
    {
        $created = $this->postFixtureSoap('getall');

        $response = $this->testClient->get($this->listEndpoint());
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body);
        $ids = array_map(
            static fn($row): int => is_array($row) && is_numeric($row['id'] ?? null) ? (int) $row['id'] : -1,
            $body
        );
        $this->assertContains($created['sid'], $ids);
    }

    #[Test]
    public function testGetOneReturnsSingleSoapNote(): void
    {
        $created = $this->postFixtureSoap('getone');

        $response = $this->testClient->getOne($this->listEndpoint(), (string) $created['sid']);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertIsNumeric($body['id']);
        $this->assertEquals($created['sid'], (int) $body['id']);
        $this->assertEquals($created['subjective'], $body['subjective'] ?? null);
    }

    #[Test]
    public function testPutUpdatesSoapNote(): void
    {
        $created = $this->postFixtureSoap('put');
        $renamed = self::SUBJECTIVE_FIXTURE_PREFIX . '-updated-' . uniqid();

        $putResponse = $this->testClient->put(
            $this->listEndpoint(),
            (string) $created['sid'],
            [
                'subjective' => $renamed,
                'objective' => 'updated objective',
                'assessment' => 'updated assessment',
                'plan' => 'updated plan',
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode(), 'PUT should 200. Body: ' . (string) $putResponse->getBody());

        $follow = $this->testClient->getOne($this->listEndpoint(), (string) $created['sid']);
        $this->assertEquals(200, $follow->getStatusCode());
        $after = json_decode((string) $follow->getBody(), true);
        $this->assertIsArray($after);
        $this->assertEquals($renamed, $after['subjective'] ?? null);
        $this->assertEquals('updated objective', $after['objective'] ?? null);
        $this->assertEquals('updated assessment', $after['assessment'] ?? null);
        $this->assertEquals('updated plan', $after['plan'] ?? null);
    }
}
