<?php

/**
 * Who marked a worklist row worked, and when.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

final readonly class WorkedMark
{
    /**
     * @param string $by the user's name, or the username when the user has no name on file
     */
    public function __construct(public string $by, public \DateTimeImmutable $at)
    {
    }
}
