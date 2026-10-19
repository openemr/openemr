<?php

/**
 * Medication API Endpoint Test Cases
 *
 * Covers the patient-compartment medication endpoints backed by
 * ListService (shared with surgery + dental_issue, same shape):
 *   GET    /api/patient/{pid}/medication        — list
 *   GET    /api/patient/{pid}/medication/{mid}  — single
 *   POST   /api/patient/{pid}/medication        — create
 *   PUT    /api/patient/{pid}/medication/{mid}  — update
 *   DELETE /api/patient/{pid}/medication/{mid}  — delete
 *
 * Full HTTP round-trip regression gates for the api tier. PR #14653
 * fixed ListService::insert (now sets uuid) + getOne (now stringifies
 * uuid) at the service tier; those paths now return 200 end-to-end
 * instead of 500ing on uuid handling. ServiceTest covers the service
 * layer directly; this file covers the full request → response path
 * through the standard-api route table.
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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MedicationApiTest extends TestCase
{
    private const FIXTURE_TITLE_PREFIX = "test-fixture-MedicationApiTest";
    // pid=1 is the first demo patient seeded by drid. Scoping cleanup to
    // the FIXTURE_TITLE_PREFIX keeps the test contained even if the demo
    // patient already has real medication rows.
    private const DEMO_PATIENT_PID = 1;

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        // Sweep every medication row this test class created. Prefix match
        // on title also catches rows leaked by a prior failed run.
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `lists` WHERE `type` = 'medication' AND `title` LIKE ?",
            [self::FIXTURE_TITLE_PREFIX . "%"]
        );
    }

    private function endpoint(?int $mid = null): string
    {
        $base = "/apis/default/api/patient/" . self::DEMO_PATIENT_PID . "/medication";
        return $mid === null ? $base : $base . "/" . $mid;
    }

    /**
     * @return array{id: int, title: string}
     */
    private function postFixture(string $titleSuffix = ""): array
    {
        $title = self::FIXTURE_TITLE_PREFIX . "-" . ($titleSuffix !== "" ? $titleSuffix : uniqid());
        $response = $this->testClient->post($this->endpoint(), [
            'title' => $title,
            'begdate' => '2026-01-01 00:00:00',
            'enddate' => null,
            'diagnosis' => '',
        ]);
        $this->assertEquals(201, $response->getStatusCode(), 'POST should 201. Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('id', $body, 'POST response must carry the new lists.id. Body: ' . (string) $response->getBody());
        $this->assertIsNumeric($body['id']);
        return ['id' => (int) $body['id'], 'title' => $title];
    }

    #[Test]
    public function testGetAll(): void
    {
        $created = $this->postFixture();

        $response = $this->testClient->get($this->endpoint());
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        // ListService::getAll returns a bare JSON array of rows (no envelope).
        $this->assertNotEmpty($body, 'at least the fixture medication must be present');

        $titles = array_column($body, 'title');
        $this->assertContains($created['title'], $titles, 'the fixture title must appear in the list');

        // Every returned row must carry a stringified uuid (PR #14653's
        // ListService::getAll fix) — a regression that lost the stringify
        // would surface here as uuid being 16-byte binary.
        foreach ($body as $row) {
            $this->assertIsArray($row);
            $this->assertArrayHasKey('uuid', $row);
            if ($row['uuid'] !== null) {
                $this->assertIsString($row['uuid']);
                $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $row['uuid']);
            }
        }
    }

    #[Test]
    public function testPostCreatesMedicationWithUuid(): void
    {
        // PR #14653's ListService::insert fix assigns a uuid on insert.
        // Confirm the just-created row carries one by reading it back
        // through getAll — the POST response itself does not echo the
        // inserted row.
        $created = $this->postFixture('post-shape');

        $response = $this->testClient->get($this->endpoint());
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $found = null;
        foreach ($body as $row) {
            if (is_array($row) && ($row['title'] ?? null) === $created['title']) {
                $found = $row;
                break;
            }
        }
        $this->assertIsArray($found, 'POST should produce a medication findable via getAll');
        $this->assertIsString($found['uuid']);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $found['uuid']);
    }

    #[Test]
    public function testGetOneReturnsSingleMedication(): void
    {
        $created = $this->postFixture('getone');

        $response = $this->testClient->getOne($this->endpoint(), (string) $created['id']);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertEquals($created['title'], $body['title']);
        $this->assertEquals($created['id'], $body['id']);
        // PR #14653's ListService::getOne fix stringifies the uuid.
        $this->assertIsString($body['uuid']);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $body['uuid']);
    }

    #[Test]
    public function testDeleteRemovesMedication(): void
    {
        $created = $this->postFixture('delete');

        $deleteResp = $this->testClient->delete($this->endpoint(), $created['id']);
        $this->assertEquals(200, $deleteResp->getStatusCode());

        // Confirm the row is gone from the list.
        $followup = $this->testClient->get($this->endpoint());
        $followupBody = json_decode((string) $followup->getBody(), true);
        $this->assertIsArray($followupBody);
        // The list rows carry `id` as a string-coerced int; compare both
        // sides as string to sidestep PHPStan's cast-from-mixed narrowing.
        $followupIds = array_map(
            static fn (mixed $v): string => is_scalar($v) ? (string) $v : '',
            array_column($followupBody, 'id')
        );
        $this->assertNotContains(
            (string) $created['id'],
            $followupIds,
            'deleted medication must not appear in the list'
        );
    }
}
