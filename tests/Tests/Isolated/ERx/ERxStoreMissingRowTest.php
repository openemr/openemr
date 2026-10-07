<?php

/**
 * Pins how interface/eRxStore.php handles sqlQuery() misses and hits.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\ERx;

use OpenEMR\Tests\Isolated\CollectsPhpDiagnosticsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Each test runs in its own process because the fixture declares the global
 * sqlQuery(). A patient with vitals is not covered: formFetch() escapes its
 * column list against the live schema, which needs a database.
 *
 * @phpstan-type ERxRow array<string, int|string>
 */
#[Group('isolated')]
final class ERxStoreMissingRowTest extends TestCase
{
    use CollectsPhpDiagnosticsTrait;

    /**
     * @param ERxRow|null $row
     * @param array<string, string>|int|string|null $expected
     */
    #[DataProvider('lookupProvider')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLookup(string $method, int|string $argument, ?array $row, array|int|string|null $expected): void
    {
        require_once __DIR__ . '/fixtures/erx_store_sql_doubles.inc';
        require_once __DIR__ . '/../../../../interface/eRxStore.php';
        $GLOBALS['erx_store_test_row'] = $row;
        $result = null;

        $diagnostics = self::diagnosticsFrom(static function () use (&$result, $method, $argument): void {
            $store = new \eRxStore();
            $result = $method === 'selectFederalEin' ? $store->selectFederalEin() : $store->$method($argument);
        });

        self::assertSame([], $diagnostics, 'a lookup must not read offsets on false');
        self::assertSame($expected, $result, 'a miss keeps the value it produced before; a hit is returned');
    }

    /**
     * @return array<string, array{string, int|string, ERxRow|null, array<string, string>|int|string|null}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function lookupProvider(): array
    {
        $noVitals = ['height' => '0.00', 'height_units' => 'cm', 'weight' => '0.00', 'weight_units' => 'kg'];

        return [
            'unknown username' => ['selectUserIdByUserName', 'nobody', null, null],
            'known username' => ['selectUserIdByUserName', 'jdoe', ['id' => 7], 7],
            'no primary facility' => ['selectFederalEin', 0, null, null],
            'primary facility' => ['selectFederalEin', 0, ['federal_ein' => '12-3456789'], '12-3456789'],
            'unknown patient import status' => ['getPatientImportStatusByPatientId', 99, null, null],
            'patient import status' => ['getPatientImportStatusByPatientId', 5, ['soap_import_status' => 2], 2],
            'patient with no vitals' => ['getPatientVitalsByPatientId', 99, null, $noVitals],
        ];
    }
}
