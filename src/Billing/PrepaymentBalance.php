<?php

/**
 * A single open prepayment session with its unapplied balance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

/**
 * Read model for one row of the prepayment balance report.
 *
 * The query returns eleven columns of untyped data. Narrowing them here once,
 * in {@see self::fromRow()}, keeps the mixed-to-scalar conversions in a single
 * tested place instead of repeating them at every echo in the template.
 */
final readonly class PrepaymentBalance
{
    public function __construct(
        public int $sessionId,
        public int $patientId,
        public string $patientName,
        public string $reference,
        public ?string $checkDate,
        public int $daysOpen,
        public float $received,
        public float $applied,
        public float $inGlobal,
        public float $unapplied,
    ) {
    }

    /**
     * Build from a raw result row.
     *
     * Keys are read defensively rather than declared as array<string, mixed>:
     * QueryUtils::fetchRecords() is untyped, so the shape of a row cannot be
     * proven at the call site.
     *
     * @param array<array-key, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $checkDate = self::toStringOrNull($row['check_date'] ?? null);

        return new self(
            sessionId: self::toInt($row['session_id'] ?? null),
            patientId: self::toInt($row['patient_id'] ?? null),
            patientName: self::formatName(
                self::toStringValue($row['lname'] ?? null),
                self::toStringValue($row['fname'] ?? null),
                self::toStringValue($row['mname'] ?? null),
            ),
            reference: self::toStringValue($row['reference'] ?? null),
            checkDate: $checkDate,
            daysOpen: self::toInt($row['days_open'] ?? null),
            received: self::toFloat($row['pay_total'] ?? null),
            applied: self::toFloat($row['applied'] ?? null),
            inGlobal: self::toFloat($row['global_amount'] ?? null),
            unapplied: self::toFloat($row['unapplied'] ?? null),
        );
    }

    /**
     * Column totals for a result set.
     *
     * Lives on the read model rather than the service because BaseService
     * require_once's custom/code_types.inc.php at file scope, which runs a
     * query on include -- so merely autoloading the service needs a database,
     * even for a static call.
     *
     * @param list<self> $balances
     * @return array{received: float, applied: float, inGlobal: float, unapplied: float}
     */
    public static function totals(array $balances): array
    {
        $totals = ['received' => 0.0, 'applied' => 0.0, 'inGlobal' => 0.0, 'unapplied' => 0.0];

        foreach ($balances as $balance) {
            $totals['received'] += $balance->received;
            $totals['applied'] += $balance->applied;
            $totals['inGlobal'] += $balance->inGlobal;
            $totals['unapplied'] += $balance->unapplied;
        }

        return $totals;
    }

    /**
     * "Last, First Middle", collapsing whatever parts are absent.
     */
    private static function formatName(string $lname, string $fname, string $mname): string
    {
        $given = trim($fname . ($mname === '' ? '' : ' ' . $mname));
        if ($lname === '') {
            return $given;
        }
        if ($given === '') {
            return $lname;
        }

        return $lname . ', ' . $given;
    }

    private static function toStringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private static function toStringOrNull(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }
        $string = (string) $value;

        return $string === '' ? null : $string;
    }

    private static function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private static function toFloat(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
