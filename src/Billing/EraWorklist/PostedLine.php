<?php

/**
 * One payment or adjustment posted to a claim from a check.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

final readonly class PostedLine
{
    /**
     * @param string $code '' for a claim-level entry
     * @param string $memo the adjustment reason as posting wrote it
     */
    public function __construct(
        public string $code,
        public string $modifier,
        public float $paid,
        public float $adjusted,
        public string $memo,
    ) {
    }
}
