<?php

/**
 * An insurance company a received 835 can be posted for by hand.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

final readonly class InsuranceCompanyOption
{
    /**
     * @param int $id insurance_companies.id
     */
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}
