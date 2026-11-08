<?php

/**
 * Aggregator for the day sheet end-of-day report.
 *
 * Replaces the per-slot accumulator pattern (`$us0_fee`, `$us0_inspay`, ...
 * through `$us19_*`, plus parallel `$pro0..$pro19` slots) used by
 * interface/billing/print_daysheet_report_num1.php. The legacy code silently
 * dropped any rows beyond the 20th unique user or provider; this aggregator
 * accumulates every distinct key.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\DaySheet;

final class DaySheetAggregator
{
    /**
     * @param iterable<BillRow|array<string, mixed>> $rows
     */
    public function aggregate(iterable $rows): DaySheetTotals
    {
        /** @var array<string, SlotTotals> $byUser */
        $byUser = [];
        /** @var array<string, SlotTotals> $byProvider */
        $byProvider = [];
        $grand = new SlotTotals('');

        foreach ($rows as $row) {
            $bill = $row instanceof BillRow ? $row : BillRow::fromArray($row);

            $byUser[$bill->user] ??= new SlotTotals($bill->user);
            $byUser[$bill->user]->applyRow($bill);

            $byProvider[$bill->providerId] ??= new SlotTotals($bill->providerId);
            $byProvider[$bill->providerId]->applyRow($bill);

            $grand->applyRow($bill);
        }

        return new DaySheetTotals(
            userTotals: array_values(array_filter($byUser, static fn (SlotTotals $s): bool => !$s->isAllZero())),
            providerTotals: array_values(array_filter($byProvider, static fn (SlotTotals $s): bool => !$s->isAllZero())),
            grandTotals: $grand,
        );
    }
}
