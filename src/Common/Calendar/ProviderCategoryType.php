<?php

/**
 * Map openemr_postcalendar_categories.pc_cattype to the legacy DOM pccattype
 * marker used in calendar event element ids.
 *
 * Provider-status categories (IN/OUT/LUNCH/etc.) use pc_cattype=1 and must open
 * add_edit_event with prov=true. Legacy templates put that flag in the third
 * segment of the event DOM id after a per-event category lookup.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Calendar;

final class ProviderCategoryType
{
    /**
     * Value stored in openemr_postcalendar_categories.pc_cattype for provider
     * status categories (IN / OUT / LUNCH / etc.).
     */
    public const PROVIDER_STATUS = 1;

    /**
     * Return the legacy pccattype DOM segment for a category type value.
     *
     * @param mixed $cattype Raw pc_cattype from the database row
     */
    public static function toDomMarker(mixed $cattype): string
    {
        return (is_numeric($cattype) && (int) $cattype === self::PROVIDER_STATUS) ? 'true' : '';
    }

    /**
     * Whether the category type is a provider-status category.
     *
     * @param mixed $cattype Raw pc_cattype from the database row
     */
    public static function isProviderStatus(mixed $cattype): bool
    {
        return self::toDomMarker($cattype) === 'true';
    }
}
