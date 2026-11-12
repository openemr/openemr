<?php

/**
 * One service line (SVC loop 2110) of an ERA claim payment, or the 'Claim'
 * line ParseERA adds for claim-level amounts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

final readonly class ServiceLine
{
    /**
     * @param string           $modifier    modifiers joined with ':', or ''
     * @param ?float           $allowed     AMT*B6, when the payer sent it
     * @param ?string          $remark      LQ*HE remark code, when the payer sent one
     * @param list<Adjustment> $adjustments
     */
    public function __construct(
        public string $code,
        public string $modifier,
        public float $charge,
        public float $paid,
        public ?float $allowed,
        public ?string $remark,
        public array $adjustments,
    ) {
    }
}
