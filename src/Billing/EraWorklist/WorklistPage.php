<?php

/**
 * The rows of a worklist view that fit on the page, and how many there are in all.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

/**
 * @template T
 */
final readonly class WorklistPage
{
    /**
     * @param list<T> $rows  the newest, up to the page's limit
     * @param int     $total how many rows match, shown or not
     */
    public function __construct(public array $rows, public int $total)
    {
    }
}
