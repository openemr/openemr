<?php

/**
 * Which invoices an invoice search lists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Invoice;

/**
 * The value is what the search form posts.
 */
enum InvoiceCategory: string
{
    /** Invoices with a balance (credit or due). */
    case Open = 'Open';
    /** Every invoice the other filters match, paid up or not. */
    case All = 'All';
    /** Owed by the patient: a balance due and no insurance level still open. */
    case DuePt = 'Due Pt';
    /** A balance due while an insurance level is still open. */
    case DueIns = 'Due Ins';

    /**
     * The posted category; anything else lists as Open, as the search always has.
     */
    public static function fromForm(string $value): self
    {
        return self::tryFrom($value) ?? self::Open;
    }

    public function label(): string
    {
        return match ($this) {
            self::Open => xl('Open'),
            self::All => xl('All'),
            self::DuePt => xl('Due Pt'),
            self::DueIns => xl('Due Ins'),
        };
    }
}
