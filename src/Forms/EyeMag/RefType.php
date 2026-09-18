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

    /**
     * The prefix the measurements behind this prescription carry on the joined
     * `form_eye_*` record: `MRODSPH` is the manifest refraction's right-eye
     * sphere, `CTLOSCYL` the contact lens trial's left-eye cylinder, and so on.
     *
     * A wearing prescription is not a refraction at all -- it is the patient's
     * current glasses read back out of `form_eye_mag_wearing`, whose columns are
     * unprefixed -- so it has no prefix here.
     */
    public function columnPrefix(): ?string
    {
        return match ($this) {
            self::W => null,
            self::AR, self::MR, self::CR, self::CTL => $this->value,
        };
    }

    /**
     * The column this prescription's comments are read from.
     *
     * The three spectacle refractions share the one `CRCOMMENTS` column of the
     * refraction record. A wearing or contact lens prescription carries its own
     * unprefixed `COMMENTS` instead.
     */
    public function commentsColumn(): string
    {
        return match ($this) {
            self::AR, self::MR, self::CR => 'CRCOMMENTS',
            self::W, self::CTL => 'COMMENTS',
        };
    }

    /**
     * Whether this prescription records a near-add power alongside the distance
     * correction, in an `ODADD`/`OSADD` column pair.
     *
     * A cycloplegic refraction paralyzes accommodation, so there is no add to
     * measure; a contact lens trial keeps its add in the `CTLODADD` pair the
     * lens table reads, not in the spectacle add fields.
     */
    public function hasAddPower(): bool
    {
        return match ($this) {
            self::W, self::AR, self::MR => true,
            self::CR, self::CTL => false,
        };
    }
}
