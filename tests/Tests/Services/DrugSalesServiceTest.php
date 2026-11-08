<?php

/**
 * DrugSalesServiceTest.php
 *
 * Regression coverage for an authenticated SQL injection in the Fee Sheet
 * inventory check: the warehouse value was interpolated directly into the
 * ORDER BY clause of the drug-inventory lookup in DrugSalesService::sellDrug().
 * These tests prove the value is now bound as a parameter and can no longer
 * break out of the SQL statement.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\DrugSalesService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DrugSalesServiceTest extends TestCase
{
    private DrugSalesService $service;

    private int $drugId;

    protected function setUp(): void
    {
        $this->service = new DrugSalesService();
        // A dispensable product is required to reach the inventory lookup that
        // holds the vulnerable ORDER BY clause; non-dispensable products return
        // earlier and never run it.
        $this->drugId = (int) QueryUtils::sqlInsert(
            "INSERT INTO drugs (name, dispensable) VALUES (?, 1)",
            ['Fee Sheet inventory ORDER BY regression product']
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM drugs WHERE drug_id = ?", [$this->drugId]);
    }

    /**
     * The warehouse value originates from attacker-controlled Fee Sheet POST data
     * (prod[N][warehouse]).  Before the fix it was interpolated into the ORDER BY
     * clause, so a value carrying an unbalanced quote broke out of the string
     * literal and produced a SQL syntax error - the throwing symptom of the
     * injection.  After the fix the value is bound as a parameter, so it is
     * treated as an ordinary (here nonexistent) warehouse id: the query executes
     * cleanly and, finding no matching inventory, reports the product as
     * unfillable (false) instead of throwing.
     */
    #[Test]
    #[DataProvider('maliciousWarehouseProvider')]
    public function warehouseValueCannotBreakOutOfOrderByClause(string $maliciousWarehouse): void
    {
        // "test only" mode runs the inventory lookup but performs no writes and
        // skips the encounter sanity check, so no encounter/patient fixtures are
        // needed to exercise the vulnerable query.
        $result = $this->service->sellDrug(
            $this->drugId,
            1,                  // quantity
            0,                  // fee
            1,                  // patient_id
            0,                  // encounter_id (unused in test-only mode)
            0,                  // prescription_id
            '',                 // sale_date
            '',                 // user (falls back to the session user)
            $maliciousWarehouse, // default_warehouse - the injection vector
            true                // testonly
        );

        $this->assertFalse(
            $result,
            'sellDrug() must treat the warehouse value as bound data and report insufficient inventory, '
            . 'not execute (or fail to parse) injected SQL'
        );
    }

    /**
     * Warehouse payloads whose unbalanced quote would break out of the ORDER BY
     * string literal if the value were interpolated instead of bound.  Each one
     * raises a SQL syntax error against the pre-fix code and is handled as an
     * inert literal against the fixed code.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function maliciousWarehouseProvider(): array
    {
        return [
            'lone quote'                => ["'"],
            'single trailing quote'     => ["1'"],
            'quote then extra sort term' => ["1',(SELECT 1)"],
            'quote then tautology'      => ["1' OR 1=1"],
        ];
    }
}
