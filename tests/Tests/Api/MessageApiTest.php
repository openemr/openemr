<?php

/**
 * Message (pnotes) API Endpoint Test Cases
 *
 * Covers:
 *   - POST   /api/patient/{pid}/message        — create
 *   - PUT    /api/patient/{pid}/message/{mid}  — update
 *   - DELETE /api/patient/{pid}/message/{mid}  — delete
 *
 * Fills a post-batch audit gap — the three message routes had zero
 * api-tier coverage. Backed by `MessageService` which writes to
 * `pnotes`. No GET route exists at the api tier (list / single are
 * patient-portal only).
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

class MessageApiTest extends TestCase
{
    private const FIXTURE_TITLE = "test-fixture-MessageApiTest";
    private const DEMO_PATIENT_PID = 1;

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        try {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM pnotes WHERE title = ? AND pid = ?",
                [self::FIXTURE_TITLE, self::DEMO_PATIENT_PID]
            );
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
    }

    private function endpoint(?int $mid = null): string
    {
        $base = "/apis/default/api/patient/" . self::DEMO_PATIENT_PID . "/message";
        return $mid === null ? $base : $base . "/" . $mid;
    }

    private function postFixtureMessage(string $bodyText = 'fixture body text'): int
    {
        $response = $this->testClient->post($this->endpoint(), [
            'body' => $bodyText,
            'groupname' => 'Default',
            'from' => 'admin',
            'to' => 'admin',
            'title' => self::FIXTURE_TITLE,
            'message_status' => 'New',
        ]);
        $this->assertEquals(201, $response->getStatusCode(), 'POST should 201. Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('mid', $body);
        $this->assertIsNumeric($body['mid']);
        return (int) $body['mid'];
    }

    #[Test]
    public function testPostCreatesMessage(): void
    {
        $mid = $this->postFixtureMessage();
        $this->assertGreaterThan(0, $mid);

        // Verify the row landed in pnotes with the expected shape.
        $row = QueryUtils::querySingleRow(
            "SELECT id, pid, title, message_status FROM pnotes WHERE id = ?",
            [$mid]
        );
        $this->assertIsArray($row);
        $this->assertIsNumeric($row['pid']);
        $this->assertEquals(self::DEMO_PATIENT_PID, (int) $row['pid']);
        $this->assertEquals(self::FIXTURE_TITLE, $row['title']);
        $this->assertEquals('New', $row['message_status']);
    }

    #[Test]
    public function testPutUpdatesMessage(): void
    {
        $mid = $this->postFixtureMessage();

        $putResponse = $this->testClient->put(
            $this->endpoint(),
            (string) $mid,
            [
                'body' => 'updated body text',
                'groupname' => 'Default',
                'from' => 'admin',
                'to' => 'admin',
                'title' => self::FIXTURE_TITLE,
                'message_status' => 'Done',
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode(), 'PUT should 200. Body: ' . (string) $putResponse->getBody());

        $row = QueryUtils::querySingleRow(
            "SELECT message_status FROM pnotes WHERE id = ?",
            [$mid]
        );
        $this->assertIsArray($row);
        $this->assertEquals('Done', $row['message_status']);
    }

    #[Test]
    public function testDeleteRemovesMessage(): void
    {
        $mid = $this->postFixtureMessage();

        $deleteResponse = $this->testClient->delete($this->endpoint(), $mid);
        $this->assertEquals(200, $deleteResponse->getStatusCode());

        // MessageService::delete flips `deleted=1` rather than removing the
        // row — verify the row is marked deleted, not that it's gone.
        $row = QueryUtils::querySingleRow(
            "SELECT deleted FROM pnotes WHERE id = ?",
            [$mid]
        );
        $this->assertIsArray($row);
        $this->assertIsNumeric($row['deleted']);
        $this->assertEquals(1, (int) $row['deleted'], 'DELETE should soft-delete via deleted=1');
    }
}
