<?php

/**
 * Refraction methods behind an eye exam spectacle or contact lens prescription.
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
 * How the refraction behind a prescription was measured. Stored in
 * `form_eye_mag_dispense.REFTYPE`.
 */
enum RefType: string
{
    /** Wearing: the patient's current glasses, re-prescribed unchanged. */
    case W = 'W';

    /** Auto-refraction. */
    case AR = 'AR';

    /** Manifest (dry) refraction. */
    case MR = 'MR';

    /** Cycloplegic (wet) refraction. */
    case CR = 'CR';

    /** Contact lens. */
    case CTL = 'CTL';

    /**
     * Translated label for the refraction method, as printed on the prescription.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::W => xlt('Duplicate Rx -- unchanged from current Rx{{The refraction did not change, New Rx=old Rx}}'),
            self::AR => xlt('Auto-Refraction'),
            self::MR => xlt('Manifest (Dry) Refraction'),
            self::CR => xlt('Cycloplegic (Wet) Refraction'),
            self::CTL => xlt('Contact Lens'),
        };
    }

    /**
     * Contact lens prescriptions print a different table and expire sooner than
     * spectacle prescriptions.
     */
    public function isContactLens(): bool
    {
        return $this === self::CTL;
    }
}
