<?php

/**
 * A Due Pt invoice list with each patient's credits netted, as a statement run nets them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

use OpenEMR\Billing\Invoice\InvoiceListRow;

/**
 * A Due Pt run nets each patient's credits on other visits against the checked
 * invoices and sends nothing when that leaves nothing due. So those patients'
 * pre-checked invoices are hidden unless asked for, and unchecked when shown.
 * Only pre-checked invoices count, as in a default run; an unchecked one (in
 * collections, say) stays listed as it is.
 */
final readonly class StatementListRows
{
    /**
     * @param list<InvoiceListRow> $rows
     * @param int $hiddenPatients patients left out because nothing is due after their credits
     */
    private function __construct(public array $rows, public int $hiddenPatients)
    {
    }

    /**
     * @param list<InvoiceListRow> $rows a Due Pt list
     * @param \Closure(array<int, list<int>>): array<int, string> $nothingDue PatientNetDue::nothingDue()
     */
    public static function net(array $rows, bool $showNothingDue, \Closure $nothingDue): self
    {
        $checked = [];
        foreach ($rows as $row) {
            if ($row->preChecked) {
                $checked[$row->pid][] = $row->id;
            }
        }
        $netByPatient = $checked === [] ? [] : $nothingDue($checked);

        $listed = [];
        $hidden = [];
        foreach ($rows as $row) {
            $netDue = $row->preChecked ? ($netByPatient[$row->pid] ?? null) : null;
            if ($netDue === null) {
                $listed[] = $row;
            } elseif ($showNothingDue) {
                $listed[] = $row->withNothingDue($netDue);
            } else {
                $hidden[$row->pid] = true;
            }
        }
        return new self($listed, count($hidden));
    }

    /**
     * Any other list, unchanged.
     *
     * @param list<InvoiceListRow> $rows
     */
    public static function asListed(array $rows): self
    {
        return new self($rows, 0);
    }
}
