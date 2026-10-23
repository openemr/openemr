<?php

/**
 * Facility ZIP flags the claim hold reads.
 *
 * The generation log also contains the patient name, so the hold does
 * not search that text. These flags are set only by the ZIP checks.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

final readonly class FacilityZipDenial
{
    public const LEFT_OUT_NOT_SAVED = 'This claim was left out of the batch because it was not saved.';

    public const LEFT_OUT_NOT_BILLED = 'This claim was left out of the batch because it was not marked billed.';

    public function __construct(
        public bool $billing = false,
        public bool $service = false,
    ) {
    }

    /**
     * True when the billing or service facility ZIP will deny the claim.
     */
    public function willDeny(): bool
    {
        return $this->billing || $this->service;
    }
}
