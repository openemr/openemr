<?php

/**
 * Lens types available on an eye exam spectacle prescription.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

/**
 * The lens type a spectacle prescription is written for. The backing values are
 * the numeric codes stored in `form_eye_mag_wearing.RX_TYPE` and passed through
 * the print form as `rx_type`; the case names are the labels the form renders.
 */
enum RxType: string
{
    case Single = '0';
    case Bifocal = '1';
    case Trifocal = '2';
    case Progressive = '3';

    /**
     * The lens type the print form falls back to when the request carries no
     * recognized code.
     */
    public const DEFAULT = self::Single;

    /**
     * Resolves the label stored in `form_eye_mag_dispense.RXTYPE`.
     *
     * The dispense table records the lens type by name rather than by the code
     * `form_eye_mag_wearing.RX_TYPE` holds, so a dispensed prescription is read
     * back through here instead of {@see self::tryFrom()}.
     */
    public static function fromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $label) {
                return $case;
            }
        }

        return null;
    }

    /**
     * The `checked` attribute for this lens type's radio button, given the lens
     * type the prescription was written for.
     */
    public function checkedAttribute(?self $selected): string
    {
        return $this === $selected ? "checked='checked'" : '';
    }
}
