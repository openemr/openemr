<?php

/**
 * Isolated tests for the Billing Manager's search criteria.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\BillingReport;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class BillingReportCriteriaTest extends TestCase
{
    /** @var array<mixed> */
    private array $savedRequest;

    protected function setUp(): void
    {
        $this->savedRequest = $_REQUEST;
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->savedRequest;
        unset($GLOBALS['billstring'], $GLOBALS['query_part'], $GLOBALS['query_part2'], $GLOBALS['auth']);
    }

    public function testUnbilledCountsNullBilledOnlyOnExistingChargeRows(): void
    {
        // billing is LEFT JOINed to encounters, so an encounter without charges
        // also has billing.billed NULL; it must not be listed as unbilled.
        $billstring = $this->billstringFor('billing.billed|=|0');

        $this->assertStringContainsString("billing.billed = '0'", $billstring);
        $this->assertStringContainsString('billing.id IS NOT NULL AND billing.billed IS NULL', $billstring);
        $this->assertDoesNotMatchRegularExpression('/OR billing\.billed IS NULL/', $billstring);
    }

    public function testEncountersWithoutBillingStillHaveTheirOwnFilter(): void
    {
        $this->assertSame(' AND billing.id is null', $this->billstringFor('billing.id|=|null'));
    }

    public function testBilled(): void
    {
        $this->assertSame(" AND billing.billed = '1'", $this->billstringFor('billing.billed|=|1'));
    }

    private function billstringFor(string $criterion): string
    {
        $_REQUEST['final_this_page_criteria'] = [$criterion];
        BillingReport::generateTheQueryPart();
        $billstring = $GLOBALS['billstring'] ?? null;
        $this->assertIsString($billstring);
        return $billstring;
    }
}
