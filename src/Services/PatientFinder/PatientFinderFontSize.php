<?php

/**
 * The text size a user picked for the Patient Finder's lists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

/**
 * Backed because the value is kept as a user setting and is the page's
 * `data-finder-font-size` attribute, which dynamic_finder.css sizes the lists by.
 */
enum PatientFinderFontSize: string
{
    case Small = 'small';
    case Normal = 'normal';
    case Large = 'large';
    case ExtraLarge = 'x-large';

    /** The user setting that keeps the choice, under the finder's setting prefix. */
    public const SETTING = 'patient_finder.font_size';

    /**
     * Read a stored setting; a missing or unknown value is the normal size.
     */
    public static function fromSetting(?string $value): self
    {
        return self::tryFrom($value ?? '') ?? self::Normal;
    }
}
