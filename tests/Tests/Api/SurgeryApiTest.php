<?php

/**
 * Surgery API Endpoint Test Cases
 *
 * Covers the patient-compartment surgery endpoints backed by
 * ListService (same shape as medication + dental_issue):
 *   GET    /api/patient/{pid}/surgery        — list
 *   GET    /api/patient/{pid}/surgery/{sid}  — single
 *   POST   /api/patient/{pid}/surgery        — create
 *   PUT    /api/patient/{pid}/surgery/{sid}  — update
 *   DELETE /api/patient/{pid}/surgery/{sid}  — delete
 *
 * Full HTTP round-trip regression gates for the api tier. PR #14653
 * fixed ListService::insert (sets uuid) + getOne (stringifies uuid)
 * + getAll (handles NULL uuid). ServiceTest covers the service layer
 * directly; this file covers the full request → response path through
 * the standard-api route table.
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

class SurgeryApiTest extends TestCase
{
    private const FIXTURE_TITLE_PREFIX = "test-fixture-SurgeryApiTest";
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
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `lists` WHERE `type` = 'surgery' AND `title` LIKE ?",
            [self::FIXTURE_TITLE_PREFIX . "%"]
        );
    }

    private function endpoint(?int $sid = null): string
    {
        $base = "/apis/default/api/patient/" . self::DEMO_PATIENT_PID . "/surgery";
        return $sid === null ? $base : $base . "/" . $sid;
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
        $this->assertArrayHasKey('id', $body);
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
        $this->assertNotEmpty($body);

        $titles = array_column($body, 'title');
        $this->assertContains($created['title'], $titles);

        foreach ($body as $row) {
            $this->assertIsArray($row);
            $this->assertArrayHasKey('uuid', $row);
            if ($row['uuid'] !== null) {
                $this->assertIsString($row['uuid']);
                $this->assertMatchesRegularExpression(
                    '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
                    $row['uuid']
                );
            }
        }
    }

    #[Test]
    public function testPostCreatesSurgeryWithUuid(): void
    {
        $created = $this->postFixture('post-shape');

        $response = $this->testClient->get($this->endpoint());
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $found = null;
        foreach ($body as $row) {
            if (is_array($row) && isset($row['id']) && is_numeric($row['id']) && (int) $row['id'] === $created['id']) {
                $found = $row;
                break;
            }
        }
        $this->assertIsArray($found, 'POST should produce a surgery findable via getAll');
        $this->assertIsString($found['uuid']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $found['uuid']
        );
    }

    #[Test]
    public function testGetOneReturnsSingleSurgery(): void
    {
        $created = $this->postFixture('getone');

        $response = $this->testClient->getOne($this->endpoint(), (string) $created['id']);
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertEquals($created['title'], $body['title']);
        $this->assertEquals($created['id'], $body['id']);
        $this->assertIsString($body['uuid']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $body['uuid']
        );
    }

    #[Test]
    public function testDeleteRemovesSurgery(): void
    {
        $created = $this->postFixture('delete');

        $deleteResp = $this->testClient->delete($this->endpoint(), $created['id']);
        $this->assertEquals(200, $deleteResp->getStatusCode());

        $followup = $this->testClient->getOne($this->endpoint(), (string) $created['id']);
        $this->assertEquals(404, $followup->getStatusCode(), 'deleted surgery must 404 on subsequent single-GET');
    }
}
