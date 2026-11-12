<?php

/**
 * One claim adjustment (CAS) on an ERA service line.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

final readonly class Adjustment
{
    /**
     * @param string $groupCode  CAS01: CO, PR, OA, PI, or CR for a balancing line ParseERA adds
     * @param string $reasonCode CAS02, or 'Balancing'
     */
    public function __construct(
        public string $groupCode,
        public string $reasonCode,
        public float $amount,
    ) {
    }
}
