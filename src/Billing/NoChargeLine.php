<?php

/**
 * A claim's procedure line that carries no charge, and when that is expected.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

final class NoChargeLine
{
    /**
     * The Service Category (list "superbill", stored in codes.superbill) for codes that are
     * billed for reporting only, at no charge: quality measure G-codes and CPT II codes.
     */
    public const QUALITY_MEASURES_CATEGORY = 'quality_measures';

    /**
     * Whether a fee is no charge: 0.00 as much as 0, an empty fee, or one that is not a number.
     */
    public static function hasNoCharge(mixed $fee): bool
    {
        if (is_string($fee)) {
            $fee = trim($fee);
        }

        return !is_numeric($fee) || (float) $fee === 0.0;
    }
}
