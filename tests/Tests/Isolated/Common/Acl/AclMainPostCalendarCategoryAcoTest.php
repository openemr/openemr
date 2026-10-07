<?php

/**
 * Pins how AclMain::fetchPostCalendarCategoryACO() handles sqlQuery() misses and hits.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Acl;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Tests\Isolated\CollectsPhpDiagnosticsTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

/**
 * Each test runs in its own process because the fixture declares the global
 * sqlQuery().
 */
#[Group('isolated')]
final class AclMainPostCalendarCategoryAcoTest extends TestCase
{
    use CollectsPhpDiagnosticsTrait;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testMissingCategoryReturnsNullWithoutWarnings(): void
    {
        require_once __DIR__ . '/fixtures/acl_main_sql_doubles.inc';
        $result = 'unset';

        $diagnostics = self::diagnosticsFrom(static function () use (&$result): void {
            $result = AclMain::fetchPostCalendarCategoryACO(999);
        });

        self::assertSame([], $diagnostics, 'a missing category row must not read offsets on false');
        self::assertNull($result, 'a missing category has no ACO');
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testMatchedCategoryReturnsAcoSpec(): void
    {
        require_once __DIR__ . '/fixtures/acl_main_sql_doubles.inc';
        $GLOBALS['acl_main_test_row'] = ['aco_spec' => 'encounters|notes'];
        $result = null;

        $diagnostics = self::diagnosticsFrom(static function () use (&$result): void {
            $result = AclMain::fetchPostCalendarCategoryACO(5);
        });

        self::assertSame([], $diagnostics, 'a matched row raises no diagnostics');
        self::assertSame('encounters|notes', $result, 'the guard must not swallow a real ACO');
    }
}
