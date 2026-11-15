<?php

/**
 * Which invoices a search lists, and their balance, dunning count and pre-check.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Invoice;

use OpenEMR\Billing\Invoice\InvoiceCategory;
use OpenEMR\Billing\Invoice\InvoiceListRow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InvoiceListRowTest extends TestCase
{
    public function testTheBalanceNetsCopaysPaymentsAndAdjustments(): void
    {
        $row = InvoiceListRow::fromQueryRow(self::row(charges: '50.00', copays: '-10.00', payments: '0.00', adjustments: '5.00'), 0, InvoiceCategory::Open);

        $this->assertNotNull($row);
        $this->assertSame('35.00', $row->balance);
        $this->assertSame(10.0, $row->paid());
        $this->assertSame(9406, $row->pid);
        $this->assertSame(306, $row->id);
        $this->assertSame('2026-09-06', $row->serviceDate());
        $this->assertSame('', $row->lastStatementDate);
        $this->assertTrue($row->preChecked);
    }

    public function testSumsWithNothingToSumStayEmpty(): void
    {
        $row = InvoiceListRow::fromQueryRow(self::row(charges: '20.00', copays: null, payments: null, adjustments: null), 0, InvoiceCategory::Open);

        $this->assertNotNull($row);
        $this->assertNull($row->copays);
        $this->assertNull($row->adjustments);
        $this->assertSame('20.00', $row->balance);
    }

    /**
     * @param array<string, mixed> $row
     */
    #[DataProvider('categoryProvider')]
    public function testTheCategoryDecidesWhatIsListed(array $row, int $payerCount, InvoiceCategory $category, bool $listed): void
    {
        $this->assertSame($listed, InvoiceListRow::fromQueryRow($row, $payerCount, $category) !== null);
    }

    /**
     * @return array<string, array{array<string, mixed>, int, InvoiceCategory, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function categoryProvider(): array
    {
        $owed = self::row(charges: '40.00');
        $paid = self::row(charges: '20.00', payments: '20.00');
        $credit = self::row(charges: '0.00', payments: '30.00');
        // Insured, primary not yet closed: insurance owes.
        $insuranceOpen = self::row(charges: '100.00', lastLevelClosed: 0);
        return [
            'Open lists a balance due' => [$owed, 0, InvoiceCategory::Open, true],
            'Open lists a credit' => [$credit, 0, InvoiceCategory::Open, true],
            'Open leaves out a paid-up invoice' => [$paid, 0, InvoiceCategory::Open, false],
            'All lists a paid-up invoice' => [$paid, 0, InvoiceCategory::All, true],
            'Due Pt lists what the patient owes' => [$owed, 0, InvoiceCategory::DuePt, true],
            'Due Pt leaves out a credit' => [$credit, 0, InvoiceCategory::DuePt, false],
            'Due Pt leaves out what insurance still owes' => [$insuranceOpen, 1, InvoiceCategory::DuePt, false],
            'Due Ins lists what insurance still owes' => [$insuranceOpen, 1, InvoiceCategory::DueIns, true],
            'Due Ins leaves out what the patient owes' => [$owed, 0, InvoiceCategory::DueIns, false],
        ];
    }

    public function testTheDunningCountIsStatementsSentOrMinusTheOpenInsuranceLevels(): void
    {
        $sent = InvoiceListRow::fromQueryRow(self::row(charges: '40.00', stmtCount: 2), 1, InvoiceCategory::Open);
        $insuranceOpen = InvoiceListRow::fromQueryRow(self::row(charges: '40.00', stmtCount: 2, lastLevelClosed: 0), 2, InvoiceCategory::Open);

        $this->assertSame(2, $sent?->dunCount);
        $this->assertSame(-2, $insuranceOpen?->dunCount);
        $this->assertFalse($insuranceOpen->preChecked, 'a run does not take what insurance still owes');
    }

    public function testInCollectionsIsListedButNotTakenByARun(): void
    {
        $byNote = InvoiceListRow::fromQueryRow(self::row(charges: '25.00', billingNote: 'Sent: in collections 10/1'), 0, InvoiceCategory::DuePt);
        $byFlag = InvoiceListRow::fromQueryRow(self::row(charges: '25.00', inCollection: 1), 0, InvoiceCategory::DuePt);

        $this->assertTrue($byNote?->inCollections);
        $this->assertFalse($byNote->preChecked);
        $this->assertTrue($byFlag?->inCollections);
    }

    public function testAnErasPaidUpInvoiceStaysListed(): void
    {
        $paid = self::row(charges: '20.00', payments: '20.00');

        $this->assertNotNull(InvoiceListRow::fromQueryRow($paid, 0, InvoiceCategory::Open, fromEra: true));
    }

    public function testOnlyWithDebtLeavesOutCredits(): void
    {
        $credit = self::row(charges: '0.00', payments: '30.00');

        $this->assertNull(InvoiceListRow::fromQueryRow($credit, 0, InvoiceCategory::All, onlyWithDebt: true));
        $this->assertNotNull(InvoiceListRow::fromQueryRow(self::row(charges: '5.00'), 0, InvoiceCategory::All, onlyWithDebt: true));
    }

    public function testNothingDueUnchecksAndKeepsTheNet(): void
    {
        $row = InvoiceListRow::fromQueryRow(self::row(charges: '10.00'), 0, InvoiceCategory::DuePt);
        $this->assertNotNull($row);

        $shown = $row->withNothingDue('-20.00');

        $this->assertFalse($shown->preChecked);
        $this->assertSame('-20.00', $shown->netDue);
        $this->assertSame($row->balance, $shown->balance);
        $this->assertTrue($row->preChecked, 'the original row is unchanged');
    }

    /**
     * @return array<string, mixed>
     */
    private static function row(
        ?string $charges = '0.00',
        ?string $copays = null,
        ?string $payments = null,
        ?string $adjustments = null,
        int $stmtCount = 0,
        int $lastLevelClosed = 1,
        string $billingNote = '',
        int $inCollection = 0,
    ): array {
        return [
            'id' => 306, 'pid' => 9406, 'encounter' => 940601, 'date' => '2026-09-06 00:00:00',
            'last_level_billed' => 1, 'last_level_closed' => $lastLevelClosed, 'last_stmt_date' => null,
            'stmt_count' => $stmtCount, 'in_collection' => $inCollection,
            'fname' => 'Fay', 'mname' => '', 'lname' => 'Copay', 'pubpid' => '9406', 'billing_note' => $billingNote,
            'charges' => $charges, 'copays' => $copays, 'payments' => $payments, 'adjustments' => $adjustments,
        ];
    }
}
