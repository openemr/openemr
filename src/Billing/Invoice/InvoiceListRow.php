<?php

/**
 * One invoice (encounter) in an invoice search's list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Invoice;

use OpenEMR\Billing\SLEOB;

/**
 * The amounts are as the query summed them (null when there is nothing to sum),
 * so the list shows them as it always has.
 */
final readonly class InvoiceListRow
{
    /**
     * @param int $id form_encounter.id
     * @param string $date service date, Y-m-d H:i:s
     * @param string $lastStatementDate Y-m-d, or '' when never sent
     * @param string $balance charges + copays - payments - adjustments, as "%.2f"
     * @param int $dunCount statements sent, or minus the insurance levels still open
     * @param bool $preChecked a statement run takes it by default: due from the patient and not in collections
     * @param ?string $netDue when a Due Pt patient's credits on other visits leave nothing due: that net, as "%.2f"
     */
    public function __construct(
        public int $id,
        public int $pid,
        public int $encounter,
        public string $date,
        public string $lastStatementDate,
        public string $fname,
        public string $lname,
        public ?string $charges,
        public ?string $copays,
        public ?string $payments,
        public ?string $adjustments,
        public string $balance,
        public bool $inCollections,
        public int $dunCount,
        public bool $preChecked,
        public ?string $netDue = null,
    ) {
    }

    /**
     * The row for a query result, or null when the category leaves it out.
     *
     * @param array<string, mixed> $row one row of InvoiceList's query
     * @param int $payerCount the patient's insurance levels on the service date (0-3)
     * @param bool $fromEra listing an ERA's invoices: paid-up ones stay listed
     */
    public static function fromQueryRow(array $row, int $payerCount, InvoiceCategory $category, bool $fromEra = false, bool $onlyWithDebt = false): ?self
    {
        $charges = self::amount($row['charges'] ?? null);
        $copays = self::amount($row['copays'] ?? null);
        $payments = self::amount($row['payments'] ?? null);
        $adjustments = self::amount($row['adjustments'] ?? null);
        $balance = sprintf('%.2f', (float) $charges + (float) $copays - (float) $payments - (float) $adjustments);
        $owed = (float) $balance;
        if ($onlyWithDebt && $owed <= 0) {
            return null;
        }
        if ($category !== InvoiceCategory::All && !$fromEra && $owed == 0) {
            return null;
        }

        $inCollections = stristr(self::text($row, 'billing_note'), 'IN COLLECTIONS') !== false
            || self::int($row, 'in_collection') === 1;
        $dunCount = SLEOB::dunningCount(self::int($row, 'stmt_count'), self::int($row, 'last_level_closed'), $payerCount);
        $due = $owed > 0;
        $included = match ($category) {
            InvoiceCategory::Open, InvoiceCategory::All => true,
            InvoiceCategory::DuePt => $due && $dunCount >= 0,
            InvoiceCategory::DueIns => $due && $dunCount < 0,
        };
        if (!$included) {
            return null;
        }

        return new self(
            id: self::int($row, 'id'),
            pid: self::int($row, 'pid'),
            encounter: self::int($row, 'encounter'),
            date: self::text($row, 'date'),
            lastStatementDate: self::text($row, 'last_stmt_date'),
            fname: self::text($row, 'fname'),
            lname: self::text($row, 'lname'),
            charges: $charges,
            copays: $copays,
            payments: $payments,
            adjustments: $adjustments,
            balance: $balance,
            inCollections: $inCollections,
            dunCount: $dunCount,
            preChecked: $dunCount >= 0 && $due && !$inCollections,
        );
    }

    /**
     * Listed although nothing is due after the patient's credits: not taken by a run.
     */
    public function withNothingDue(string $netDue): self
    {
        return new self(
            $this->id,
            $this->pid,
            $this->encounter,
            $this->date,
            $this->lastStatementDate,
            $this->fname,
            $this->lname,
            $this->charges,
            $this->copays,
            $this->payments,
            $this->adjustments,
            $this->balance,
            $this->inCollections,
            $this->dunCount,
            false,
            $netDue,
        );
    }

    public function serviceDate(): string
    {
        return substr($this->date, 0, 10);
    }

    /**
     * Payments other than copays.
     */
    public function paid(): float
    {
        return (float) $this->payments - (float) $this->copays;
    }

    private static function amount(mixed $value): ?string
    {
        return is_numeric($value) ? (string) $value : null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function int(array $row, string $key): int
    {
        return is_numeric($row[$key] ?? null) ? (int) $row[$key] : 0;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function text(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '';
    }
}
