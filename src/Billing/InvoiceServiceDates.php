<?php

/**
 * The Service Date From / To limits of the EOB Posting invoice search.
 *
 * Either end may be left blank: From alone lists that date on, To alone up to
 * and including that date. Encounter dates carry a time of day, so the To day
 * is matched up to its end rather than to its midnight.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

final readonly class InvoiceServiceDates
{
    /**
     * @param string $from first service date, Y-m-d, or '' for no lower limit
     * @param string $to   last service date, Y-m-d, or '' for no upper limit
     */
    public function __construct(public string $from, public string $to)
    {
    }

    public function isSet(): bool
    {
        return $this->from !== '' || $this->to !== '';
    }

    /**
     * The condition on form_encounter's date, aliased as $column, with its binds in order.
     *
     * @return array{string, list<string>}
     */
    public function condition(string $column): array
    {
        $conditions = [];
        $binds = [];
        if ($this->from !== '') {
            $conditions[] = "$column >= ?";
            $binds[] = $this->from;
        }
        if ($this->to !== '') {
            $conditions[] = "$column < DATE_ADD(?, INTERVAL 1 DAY)";
            $binds[] = $this->to;
        }
        return [implode(' AND ', $conditions), $binds];
    }
}
