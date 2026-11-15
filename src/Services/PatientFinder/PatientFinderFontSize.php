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
     * The Font Size menu's name for the size, translated.
     */
    public function label(): string
    {
        return match ($this) {
            self::Small => xl('Small'),
            self::Normal => xl('Normal'),
            self::Large => xl('Large'),
            self::ExtraLarge => xl('Extra Large'),
        };
    }

    /**
     * The Font Size menu: each size's value and translated name, smallest first.
     *
     * @return array<string, string>
     */
    public static function menu(): array
    {
        $menu = [];
        foreach (self::cases() as $size) {
            $menu[$size->value] = $size->label();
        }
        return $menu;
    }

    /**
     * Read a stored setting; a missing or unknown value is the normal size.
     */
    public static function fromSetting(?string $value): self
    {
        return self::tryFrom($value ?? '') ?? self::Normal;
    }
}
