<?php

/**
 * Database tests for product registration: a repeated opt-in or opt-out keeps one registration row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEEnvBag;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\ProductRegistrationService;
use OpenEMR\Services\SoftwareVersion;
use OpenEMR\Services\VersionServiceInterface;
use PHPUnit\Framework\TestCase;

final class ProductRegistrationServiceTest extends TestCase
{
    private const COLUMNS = [
        'id',
        'email',
        'opt_out',
        'auth_by_id',
        'telemetry_disabled',
        'last_ask_date',
        'last_ask_version',
        'options',
    ];

    /** @var list<array<mixed>> */
    private array $savedRows = [];

    protected function setUp(): void
    {
        $this->savedRows = QueryUtils::fetchRecords('SELECT * FROM `product_registration`');
        QueryUtils::sqlStatementThrowException('DELETE FROM `product_registration`');
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `product_registration`');
        $insert = 'INSERT INTO `product_registration` (`' . implode('`, `', self::COLUMNS) . '`) VALUES ('
            . implode(', ', array_fill(0, count(self::COLUMNS), '?')) . ')';
        foreach ($this->savedRows as $row) {
            $values = array_map(static fn(string $column): mixed => $row[$column] ?? null, self::COLUMNS);
            QueryUtils::sqlStatementThrowException($insert, $values);
        }
    }

    public function testOptingInTwiceUpdatesTheOneRegistration(): void
    {
        $service = $this->serviceReplying(new Response(201), new Response(201));

        $service->registerProduct('first@example.test');
        $service->registerProduct('second@example.test');

        $rows = QueryUtils::fetchRecords('SELECT `email`, `opt_out` FROM `product_registration`');
        $this->assertCount(1, $rows);
        $this->assertSame('second@example.test', $rows[0]['email'] ?? null);
        $this->assertSame('second@example.test', $service->getRegistrationEmail());
    }

    public function testOptingOutTwiceKeepsOneRow(): void
    {
        $service = $this->serviceReplying();

        $service->registerProduct('');
        $service->registerProduct('');

        $rows = QueryUtils::fetchRecords('SELECT `email`, `opt_out` FROM `product_registration`');
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['email'] ?? null);
        $this->assertEquals(1, $rows[0]['opt_out'] ?? null);
        $this->assertSame('', $service->getRegistrationEmail());
    }

    private function serviceReplying(Response ...$replies): ProductRegistrationService
    {
        $versionService = $this->createStub(VersionServiceInterface::class);
        $versionService->method('getSoftwareVersion')->willReturn(new SoftwareVersion(8, 1, 0, '', 0, 0, 0));
        $handler = HandlerStack::create(new MockHandler(array_values($replies)));
        $client = new Client(['handler' => $handler, 'http_errors' => false]);
        $globals = new OEGlobalsBag(['http_verify_ssl' => true]);
        return new ProductRegistrationService($client, $versionService, new OEEnvBag(), $globals);
    }
}
