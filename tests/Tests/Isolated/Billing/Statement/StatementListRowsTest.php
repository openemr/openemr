<?php

/**
 * A Due Pt list hides, or shows unchecked, patients their credits leave with nothing due.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Invoice\InvoiceListRow;
use OpenEMR\Billing\Statement\StatementListRows;
use PHPUnit\Framework\TestCase;

class StatementListRowsTest extends TestCase
{
    /** @var list<array<mixed>> what the netting was asked about */
    private array $asked = [];

    public function testAPatientWithNothingDueIsHiddenAndCounted(): void
    {
        $rows = [self::row(1, 11), self::row(1, 12), self::row(2, 21)];

        $list = StatementListRows::net($rows, false, $this->nothingDueFor([1 => '-5.00']));

        $this->assertSame([21], array_map(static fn(InvoiceListRow $row): int => $row->id, $list->rows));
        $this->assertSame(1, $list->hiddenPatients);
        $this->assertSame([[1 => [11, 12], 2 => [21]]], $this->asked);
    }

    public function testShowNothingDueListsThemUncheckedWithTheirNet(): void
    {
        $list = StatementListRows::net([self::row(1, 11), self::row(2, 21)], true, $this->nothingDueFor([1 => '-5.00']));

        $this->assertSame(0, $list->hiddenPatients);
        $this->assertCount(2, $list->rows);
        $this->assertFalse($list->rows[0]->preChecked);
        $this->assertSame('-5.00', $list->rows[0]->netDue);
        $this->assertTrue($list->rows[1]->preChecked);
        $this->assertNull($list->rows[1]->netDue);
    }

    public function testOnlyPreCheckedInvoicesAreNettedOrHidden(): void
    {
        // In collections: listed, not pre-checked, so a run wouldn't take it.
        $inCollections = self::row(1, 11, preChecked: false);

        $list = StatementListRows::net([$inCollections, self::row(1, 12)], false, $this->nothingDueFor([1 => '0.00']));

        $this->assertSame([11], array_map(static fn(InvoiceListRow $row): int => $row->id, $list->rows));
        $this->assertSame([[1 => [12]]], $this->asked);
    }

    public function testNothingIsAskedWhenNothingIsPreChecked(): void
    {
        $list = StatementListRows::net([self::row(1, 11, preChecked: false)], false, $this->nothingDueFor([]));

        $this->assertCount(1, $list->rows);
        $this->assertSame([], $this->asked);
    }

    public function testOtherListsAreUnchanged(): void
    {
        $rows = [self::row(1, 11)];

        $list = StatementListRows::asListed($rows);

        $this->assertSame($rows, $list->rows);
        $this->assertSame(0, $list->hiddenPatients);
    }

    /**
     * @param array<int, string> $nets pid => net
     * @return \Closure(array<int, list<int>>): array<int, string>
     */
    private function nothingDueFor(array $nets): \Closure
    {
        return function (array $checked) use ($nets): array {
            $this->asked[] = $checked;
            return $nets;
        };
    }

    private static function row(int $pid, int $id, bool $preChecked = true): InvoiceListRow
    {
        return new InvoiceListRow(
            id: $id,
            pid: $pid,
            encounter: $id * 10,
            date: '2026-09-01 00:00:00',
            lastStatementDate: '',
            fname: 'Pat',
            lname: 'Patient' . $pid,
            charges: '10.00',
            copays: null,
            payments: null,
            adjustments: null,
            balance: '10.00',
            inCollections: !$preChecked,
            dunCount: 0,
            preChecked: $preChecked,
        );
    }
}
