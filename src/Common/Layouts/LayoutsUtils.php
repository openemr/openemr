<?php

/**
 * LayoutsUtils class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2023 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Layouts;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\JsonUtils;

class LayoutsUtils
{
    /**
     * Returns the (translated) title of an active list option, or $option itself when it has none.
     */
    public static function getListItemTitle(string $list, ?string $option): ?string
    {
        $title = QueryUtils::fetchSingleValue(
            'SELECT `title` FROM `list_options` WHERE `list_id` = ? AND `option_id` = ? AND activity = 1',
            'title',
            [$list, $option]
        );
        if (!is_string($title) || $title === '' || $title === '0') {
            return $option;
        }
        return xl_list_label($title);
    }

    /**
     * Test if modifier $test is set in a layout row's edit options.
     *
     * A legacy string that also parses as a JSON number, such as "2" or "2E0",
     * has never matched anything.
     *
     * @param ?string $options JSON such as ["G","P","T"], or a legacy string of one-letter modifiers such as "GPT"
     */
    public static function isOption(?string $options, string $test): bool
    {
        return in_array($test, self::parseOptions($options), true);
    }

    /**
     * @return list<string>
     */
    private static function parseOptions(?string $options): array
    {
        // "0" and "null" have always meant no options here.
        if ($options === null || $options === '' || $options === '0' || $options === 'null') {
            return [];
        }

        try {
            $decoded = JsonUtils::decodeArray($options);
        } catch (\JsonException) {
            // Not JSON. Without a comma it is a legacy string of one-letter modifiers.
            // Modifiers are ASCII; any other byte has always made the whole value unreadable.
            if (str_contains($options, ',') || !mb_check_encoding($options, 'ASCII')) {
                return [];
            }
            return str_split(trim($options));
        }

        if ($decoded === null) {
            return [];
        }
        return array_values(array_filter($decoded, is_string(...)));
    }
}
