<?php

/**
 * ListRestController empty-list regression tests
 *
 * `GET /api/patient/{pid}/{medication|surgery|dental_issue}` on a patient
 * with no rows of that type must return HTTP 200 with `[]`, not HTTP 404.
 * `RestControllerHelper::responseHandler` treats a falsy service result
 * (which includes PHP's empty array `[]`) as "not found", so without the
 * `listResponse()` short-circuit in `ListRestController::getAll`, an empty
 * list collapses to the single-resource 404 shape — breaking REST
 * clients that correctly treat the collection endpoint as a collection.
 *
 * This test file verifies the fix for all three list types backed by
 * `ListRestController`, using a freshly-installed fixture patient so the
 * "no rows" state is deterministic regardless of demo-data variance
 * across environments.
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
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ListRestControllerEmptyListTest extends TestCase
{
    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private int $freshPid;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();

        $pid = QueryUtils::fetchSingleValue(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ? ORDER BY pid DESC LIMIT 1",
            'pid',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . "%"]
        );
        $this->assertIsNumeric($pid, 'fixture patient must exist');
        $this->freshPid = (int) $pid;
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function listEndpointProvider(): array
    {
        return [
            'medication' => ['medication'],
            'surgery' => ['surgery'],
            'dental_issue' => ['dental_issue'],
        ];
    }

    #[Test]
    #[DataProvider('listEndpointProvider')]
    public function testEmptyListReturns200WithEmptyArray(string $resource): void
    {
        $path = "/apis/default/api/patient/" . $this->freshPid . "/" . $resource;

        $response = $this->testClient->get($path);
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            "GET $path on a fresh patient (no rows) must return 200, not 404. Body: " . (string) $response->getBody()
        );

        $raw = (string) $response->getBody();
        $this->assertSame('[]', trim($raw), "empty list must serialize as [] (got: $raw)");

        $body = json_decode($raw, true);
        $this->assertIsArray($body);
        $this->assertSame([], $body);
    }
}
