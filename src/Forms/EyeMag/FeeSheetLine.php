<?php

/**
 * A billing line the eye exam's Populate Fee Sheet writes, built as the fee sheet builds it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

use OpenEMR\Billing\HcpcsDrugDefaults;

/**
 * Units, fee, NDC and modifier for a code the eye exam bills (a visit code or a
 * Tests Performed entry), the way the fee sheet fills them when that code is added:
 * a HCPCS code related to an inventory drug takes the drug's NDC and units, the fee
 * is the unit price times the units, and the code's own modifier (e.g. JZ) applies
 * unless one was typed for the test.
 */
final readonly class FeeSheetLine
{
    public function __construct(
        public string $codeText,
        public int $units,
        public ?string $fee,
        public string $ndcInfo,
        public string $modifier,
    ) {
    }

    /**
     * @param array{code_text: string, units: string, fee: ?string, modifier: string} $charge
     *        from FeeSheetChargeLookup::find(); its fee is the price of one unit
     * @param ?HcpcsDrugDefaults $drug the inventory drug related to a HCPCS code, if any
     * @param string $typedModifier the modifier entered on the exam, or ''
     */
    public static function fromCharge(array $charge, ?HcpcsDrugDefaults $drug, string $typedModifier): self
    {
        $units = $drug->units ?? (ctype_digit($charge['units']) ? (int) $charge['units'] : 0);
        if ($units < 1) {
            $units = 1;
        }
        $fee = $charge['fee'];
        if ($fee !== null && is_numeric($fee)) {
            $fee = sprintf('%01.2f', (float) $fee * $units);
        }
        $typedModifier = trim($typedModifier);

        return new self(
            $charge['code_text'],
            $units,
            $fee,
            $drug->ndcInfo ?? '',
            $typedModifier !== '' ? $typedModifier : $charge['modifier'],
        );
    }
}
