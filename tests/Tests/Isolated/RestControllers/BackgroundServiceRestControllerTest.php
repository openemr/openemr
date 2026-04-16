<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers;

use OpenEMR\RestControllers\BackgroundServiceRestController;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[Group('isolated')]
#[Group('background-services')]
class BackgroundServiceRestControllerTest extends TestCase
{
    public function testRunAllDueReturnsResultsArray(): void
    {
        $results = [
            ['name' => 'phimail', 'status' => 'executed'],
            ['name' => 'Email_Service', 'status' => 'not_due'],
        ];
        $controller = new BackgroundServiceRestController(
            runner: new BackgroundServiceRunnerFixture($results),
        );

        $response = $controller->runAllDue();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['results' => $results], $this->decodeJsonBody($response));
    }

    public function testRunAllDueReturnsEmptyResultsWhenNothingDue(): void
    {
        // Returning HTTP 200 with an empty results array (rather than 204 or
        // an error) is the contract: the runner is always correct to invoke,
        // and "no services were due" is a successful no-op.
        $controller = new BackgroundServiceRestController(
            runner: new BackgroundServiceRunnerFixture([]),
        );

        $response = $controller->runAllDue();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['results' => []], $this->decodeJsonBody($response));
    }

    public function testRunAllDueReturnsHttp200EvenWhenServicesErrored(): void
    {
        // Per-service errors do not fail the request. The caller inspects
        // individual statuses; this mirrors how cron would continue to
        // advance remaining services after one misbehaves.
        $results = [
            ['name' => 'phimail', 'status' => 'executed'],
            ['name' => 'Email_Service', 'status' => 'error'],
        ];
        $controller = new BackgroundServiceRestController(
            runner: new BackgroundServiceRunnerFixture($results),
        );

        $response = $controller->runAllDue();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['results' => $results], $this->decodeJsonBody($response));
    }

    /**
     * @return array<mixed>
     */
    private function decodeJsonBody(Response $response): array
    {
        $body = $response->getContent();
        $this->assertIsString($body);
        $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
        return $decoded;
    }
}

/**
 * Runner fixture that returns a canned results array without touching the DB.
 */
class BackgroundServiceRunnerFixture extends BackgroundServiceRunner
{
    /**
     * @param list<array{name: string, status: string}> $results
     */
    public function __construct(private readonly array $results)
    {
    }

    public function run(?string $serviceName = null, bool $force = false): array
    {
        return $this->results;
    }
}
