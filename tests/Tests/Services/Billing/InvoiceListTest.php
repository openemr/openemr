<?php

/**
 * The invoice search's query: charges, copays, payments and adjustments per
 * encounter, and the insurance levels that make a dunning count negative.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\Invoice\InvoiceCategory;
use OpenEMR\Billing\Invoice\InvoiceList;
use OpenEMR\Billing\Invoice\InvoiceListRow;
use OpenEMR\Billing\Invoice\InvoiceSearchCriteria;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class InvoiceListTest extends TestCase
{
    private const COPAY_PID = 999931;
    private const INSURED_PID = 999932;
    private const PAID_PID = 999933;
    private const PRODUCT_PID = 999934;

    /** @var array<string, int> name => form_encounter.id */
    private array $ids = [];

    protected function setUp(): void
    {
        $this->cleanUp();
        $this->addPatient(self::COPAY_PID, 'Fay', 'Zcopay');
        $this->addPatient(self::INSURED_PID, 'Dee', 'Zinsured');
        $this->addPatient(self::PAID_PID, 'Eve', 'Zpaid');
        $this->addPatient(self::PRODUCT_PID, 'Gus', 'Zproduct');
        $this->ids['copay'] = $this->addEncounter(self::COPAY_PID, 99993101, 1, 50.00);
        $this->ids['insured'] = $this->addEncounter(self::INSURED_PID, 99993201, 0, 100.00);
        $this->ids['paid'] = $this->addEncounter(self::PAID_PID, 99993301, 1, 20.00);
        // A visit with a 30.00 service and a 12.50 product sale, and a visit that is a product sale only.
        $this->ids['product'] = $this->addEncounter(self::PRODUCT_PID, 99993401, 1, 30.00);
        $this->addSale(self::PRODUCT_PID, 99993401, 12.50);
        $this->ids['sale-only'] = $this->addEncounter(self::PRODUCT_PID, 99993402, 1, null);
        $this->addSale(self::PRODUCT_PID, 99993402, 12.50);
        // A 10.00 copay, a 5.00 adjustment, and a deleted payment that doesn't count.
        $this->addActivity(self::COPAY_PID, 99993101, 1, 10.00, 0.00, 'PCP');
        $this->addActivity(self::COPAY_PID, 99993101, 2, 0.00, 5.00, '');
        $this->addActivity(self::COPAY_PID, 99993101, 3, 50.00, 0.00, 'PP', deleted: true);
        $this->addActivity(self::PAID_PID, 99993301, 1, 20.00, 0.00, 'PP');
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, type, provider, pid, date, date_end) VALUES (?, 'primary', '1', ?, NULL, NULL)",
            [random_bytes(16), self::INSURED_PID]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testTheBalanceNetsCopaysAndAdjustmentsAndSkipsDeletedPayments(): void
    {
        $row = $this->only($this->find(InvoiceCategory::All, 'Zcopay'));

        $this->assertSame($this->ids['copay'], $row->id);
        $this->assertSame('35.00', $row->balance);
        $this->assertSame(10.0, $row->paid());
        $this->assertSame(0, $row->dunCount);
        $this->assertTrue($row->preChecked);
    }

    public function testAnOpenInsuranceLevelMakesTheDunningCountNegative(): void
    {
        $row = $this->only($this->find(InvoiceCategory::DueIns, 'Zinsured'));

        $this->assertSame(-1, $row->dunCount);
        $this->assertFalse($row->preChecked);
        $this->assertSame([], $this->find(InvoiceCategory::DuePt, 'Zinsured'));
    }

    public function testThePaidUpInvoiceIsListedOnlyForAll(): void
    {
        $this->assertCount(1, $this->find(InvoiceCategory::All, 'Zpaid'));
        $this->assertSame([], $this->find(InvoiceCategory::Open, 'Zpaid'));
    }

    public function testChargesIncludeProductSales(): void
    {
        $rows = $this->find(InvoiceCategory::Open, 'Zproduct');

        $this->assertSame([$this->ids['product'], $this->ids['sale-only']], array_map(static fn(InvoiceListRow $row): int => $row->id, $rows));
        $this->assertSame('42.50', $rows[0]->charges);
        $this->assertSame('42.50', $rows[0]->balance);
        $this->assertSame('12.50', $rows[1]->charges);
        $this->assertSame('12.50', $rows[1]->balance);
    }

    public function testTheMatchCountIsBeforeTheCategory(): void
    {
        [$condition, $binds] = InvoiceSearchCriteria::forEncounters([
            [(string) self::PAID_PID, '99993301'],
            [(string) self::COPAY_PID, '99993101'],
        ]);

        [$matched, $rows] = InvoiceList::find($condition, $binds, InvoiceCategory::DuePt, fromEra: true);

        $this->assertSame(2, $matched);
        $this->assertSame([$this->ids['copay']], array_map(static fn(InvoiceListRow $row): int => $row->id, $rows));
    }

    /**
     * @return list<InvoiceListRow>
     */
    private function find(InvoiceCategory $category, string $name): array
    {
        $condition = (new InvoiceSearchCriteria($category, name: $name))->condition();
        $this->assertNotNull($condition);
        return InvoiceList::find($condition[0], $condition[1], $category)[1];
    }

    /**
     * @param list<InvoiceListRow> $rows
     */
    private function only(array $rows): InvoiceListRow
    {
        $this->assertCount(1, $rows);
        return $rows[0];
    }

    private function addPatient(int $pid, string $fname, string $lname): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, billing_note) VALUES (?, ?, ?, ?, '1950-01-01', 'Female', '')",
            [$pid, (string) $pid, $fname, $lname]
        );
    }

    /**
     * @param ?float $fee the visit's one service charge; null for a visit with no services
     */
    private function addEncounter(int $pid, int $encounter, int $lastLevelClosed, ?float $fee): int
    {
        $id = QueryUtils::sqlInsert(
            "INSERT INTO form_encounter (date, reason, facility_id, pid, encounter, provider_id, last_level_billed, last_level_closed, stmt_count) " .
            "VALUES ('2026-09-01 00:00:00', 'invoice list test', 3, ?, ?, 1, 1, ?, 0)",
            [$pid, $encounter, $lastLevelClosed]
        );
        if ($fee === null) {
            return $id;
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO billing (date, code_type, code, pid, encounter, fee, units, activity, authorized, billed, code_text) " .
            "VALUES ('2026-09-01', 'CPT4', '99213', ?, ?, ?, 1, 1, 1, 1, 'Office visit')",
            [$pid, $encounter, $fee]
        );
        return $id;
    }

    private function addSale(int $pid, int $encounter, float $fee): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO drug_sales (drug_id, inventory_id, pid, encounter, user, sale_date, quantity, fee, billed) " .
            "VALUES (1, 1, ?, ?, 'admin', '2026-09-01', 1, ?, 0)",
            [$pid, $encounter, $fee]
        );
    }

    private function addActivity(int $pid, int $encounter, int $sequence, float $pay, float $adjust, string $accountCode, bool $deleted = false): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user, " .
            "session_id, memo, pay_amount, adj_amount, modified_time, follow_up, account_code, deleted) " .
            "VALUES (?, ?, ?, 'CPT4', '99213', '', 0, NOW(), 1, 0, '', ?, ?, NOW(), '', ?, " . ($deleted ? 'NOW()' : 'NULL') . ")",
            [$pid, $encounter, $sequence, $pay, $adjust, $accountCode]
        );
    }

    private function cleanUp(): void
    {
        foreach ([self::COPAY_PID, self::INSURED_PID, self::PAID_PID, self::PRODUCT_PID] as $pid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM drug_sales WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [$pid]);
        }
        $this->ids = [];
    }
}
