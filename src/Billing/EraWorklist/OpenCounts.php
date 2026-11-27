<?php

/**
 * How much of the ERA worklist is not marked worked yet.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

final readonly class OpenCounts
{
    /**
     * @param int $review claim payments left for review and not marked worked
     * @param int $files  received 835s that were not posted unattended and are not marked worked
     */
    public function __construct(public int $review, public int $files)
    {
    }
}
