<?php

/**
 * Background Service API Endpoint Test Cases.
 *
 * Covers the Standard REST API endpoints:
 *   GET  /api/background_service               — list all services
 *   GET  /api/background_service/:name         — single service by name
 *   POST /api/background_service/:name/$run    — execute a named service
 *   POST /api/background_service/$run          — advance all due services
 *
 * Execution of the per-service route is witnessed via a probe service
 * (BackgroundServiceApiProbe) that appends to a sentinel file. The row
 * is registered during setUp and removed during tearDown so no test-only
 * state leaks into other tests or production.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Background\BackgroundServiceDefinition;
use OpenEMR\Services\Background\BackgroundServiceRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function OpenEMR\Tests\Api\Fixtures\BackgroundService\Probe\apiProbeSentinelPath;

class BackgroundServiceApiTest extends TestCase
{
    private const LIST_ENDPOINT = "/apis/default/api/background_service";
    private const PROBE_NAME = '_api_background_service_probe';
    private const PROBE_FUNCTION = 'OpenEMR\\Tests\\Api\\Fixtures\\BackgroundService\\Probe\\markApiProbeSentinel';
    private const PROBE_REQUIRE_ONCE = 'tests/Tests/Api/Fixtures/BackgroundService/BackgroundServiceApiProbe.php';

    private ApiTestClient $testClient;
    private BackgroundServiceRegistry $registry;
    private string $sentinelPath;

    protected function setUp(): void
    {
        // Load the probe file in the test process so the sentinel-path
        // helper is available when asserting. The apache worker
        // handling the REST request re-evaluates the same file via the
        // runner's `require_once` on the probe's row.
        require_once __DIR__ . '/Fixtures/BackgroundService/BackgroundServiceApiProbe.php';

        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->registry = new BackgroundServiceRegistry();
        $this->sentinelPath = apiProbeSentinelPath();

        $this->removeSentinel();
        $this->registry->unregister(self::PROBE_NAME);
        $this->registerProbe();
    }

    protected function tearDown(): void
    {
        try {
            $this->registry->unregister(self::PROBE_NAME);
            $this->removeSentinel();
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
    }

    #[Test]
    public function testListReturnsSeededServices(): void
    {
        $response = $this->testClient->get(self::LIST_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());

        /** @var list<array<string, mixed>>|null $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        // Stock drid seeds phimail / MedEx / X12_SFTP / UUID_Service /
        // Email_Service and setUp adds the probe row, so the list must
        // be non-empty whichever subset is present.
        $this->assertNotEmpty($body, 'List should include seeded services plus the probe row.');

        $names = array_map(
            static fn(array $row): string => is_string($row['name'] ?? null) ? $row['name'] : '',
            $body,
        );
        $this->assertContains(self::PROBE_NAME, $names, 'Probe row must appear in the list response.');

        // Spot-check the per-row shape against the renderer contract so
        // a drift in field names surfaces here rather than in a client.
        $probeRow = null;
        foreach ($body as $row) {
            if (($row['name'] ?? null) === self::PROBE_NAME) {
                $probeRow = $row;
                break;
            }
        }
        $this->assertIsArray($probeRow);
        $this->assertArrayHasKey('title', $probeRow);
        $this->assertArrayHasKey('active', $probeRow);
        $this->assertArrayHasKey('running', $probeRow);
        $this->assertArrayHasKey('execute_interval', $probeRow);
        $this->assertArrayHasKey('next_run', $probeRow);
        $this->assertIsBool($probeRow['active']);
        $this->assertIsBool($probeRow['running']);
        $this->assertIsInt($probeRow['execute_interval']);
    }

    #[Test]
    public function testGetOneReturnsNamedService(): void
    {
        $response = $this->testClient->getOne(self::LIST_ENDPOINT, self::PROBE_NAME);
        $this->assertEquals(200, $response->getStatusCode());

        /** @var array<string, mixed>|null $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(self::PROBE_NAME, $body['name'] ?? null);
        $this->assertSame('API Integration Probe', $body['title'] ?? null);
        $this->assertSame(5, $body['execute_interval'] ?? null);
        $this->assertTrue($body['active'] ?? null);
    }

    #[Test]
    public function testGetOneReturns404ForUnknownService(): void
    {
        $response = $this->testClient->getOne(self::LIST_ENDPOINT, '_definitely_not_a_real_service_');
        $this->assertEquals(404, $response->getStatusCode());

        /** @var array<string, mixed>|null $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
    }

    #[Test]
    public function testRunByNameExecutesProbeService(): void
    {
        $this->assertFileDoesNotExist(
            $this->sentinelPath,
            'Sentinel must not exist before the probe is triggered.',
        );

        $response = $this->testClient->post(
            self::LIST_ENDPOINT . "/" . self::PROBE_NAME . '/$run',
            [],
        );

        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'POST $run should 200 for an active, due probe. Body: ' . (string) $response->getBody(),
        );

        /** @var array<string, mixed>|null $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(self::PROBE_NAME, $body['service'] ?? null);
        $this->assertSame('executed', $body['status'] ?? null);

        $this->assertFileExists(
            $this->sentinelPath,
            'Sentinel file must exist after the probe executes.',
        );
        $this->assertSame(
            1,
            $this->sentinelLineCount(),
            'Probe function should have executed exactly once.',
        );
    }

    #[Test]
    public function testRunByNameReturns404ForUnknownService(): void
    {
        $response = $this->testClient->post(
            self::LIST_ENDPOINT . '/_definitely_not_a_real_service_/$run',
            [],
        );
        $this->assertEquals(404, $response->getStatusCode());

        /** @var array<string, mixed>|null $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
    }

    #[Test]
    public function testRunAllDueReturnsResultsArray(): void
    {
        $response = $this->testClient->post(self::LIST_ENDPOINT . '/$run', []);
        $this->assertEquals(
            200,
            $response->getStatusCode(),
            'POST $run (all due) should 200 regardless of per-service outcome. Body: '
            . (string) $response->getBody(),
        );

        /** @var array<string, mixed>|null $body */
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('results', $body);
        $results = $body['results'];
        $this->assertIsArray($results);
        // The orchestrator reports every active+scheduled service
        // (including the probe) or returns [['name'=>'orchestrator',
        // 'status'=>'already_running']] if a concurrent run holds the
        // lock. Shape, not content, is what the route contract locks.
        foreach ($results as $row) {
            $this->assertIsArray($row);
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('status', $row);
            $this->assertIsString($row['name']);
            $this->assertIsString($row['status']);
        }
    }

    private function registerProbe(): void
    {
        $this->registry->register(new BackgroundServiceDefinition(
            name: self::PROBE_NAME,
            title: 'API Integration Probe',
            function: self::PROBE_FUNCTION,
            requireOnce: self::PROBE_REQUIRE_ONCE,
            executeInterval: 5,
            sortOrder: 999,
            active: true,
        ));
        // Force the probe to be due immediately. `register()` inserts a
        // fresh row with next_run = '0000-00-00 00:00:00' (the column
        // default), which MySQL in strict mode can interpret as an
        // invalid date and reject comparisons against it. Set an
        // unambiguous past timestamp so NOW() > next_run always holds.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `background_services` SET `next_run` = ? WHERE `name` = ?',
            ['1970-01-01 00:00:00', self::PROBE_NAME],
            true,
        );
    }

    private function removeSentinel(): void
    {
        if (is_file($this->sentinelPath)) {
            unlink($this->sentinelPath);
        }
    }

    private function sentinelLineCount(): int
    {
        $this->assertFileExists(
            $this->sentinelPath,
            'Sentinel file must exist before counting probe executions.',
        );
        $contents = file_get_contents($this->sentinelPath);
        $this->assertNotFalse($contents, 'Failed to read sentinel file.');
        return substr_count($contents, "\n");
    }
}
