<?php

/**
 * The signed-in user who marks a worklist row worked.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

final readonly class WorklistUser
{
    /**
     * @param int $id users.id
     */
    public function __construct(public int $id)
    {
        if ($id <= 0) {
            throw new \DomainException('A worklist user needs a user id');
        }
    }
}
