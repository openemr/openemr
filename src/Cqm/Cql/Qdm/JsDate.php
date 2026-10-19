<?php

/**
 * Whether JavaScript's Date.parse reads a string as a date, for the
 * ISO 8601 forms QDM data carries. V8 also accepts many legacy forms
 * ("May 1 2001"); those are treated as not dates.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Qdm;

final class JsDate
{
    private const ISO = '/^[+-]?\d{4,6}(-\d{2}(-\d{2})?)?(T\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|[+-]\d{2}:?\d{2})?)?$/D';

    public static function parses(string $value): bool
    {
        if (preg_match(self::ISO, $value) !== 1) {
            return false;
        }
        // Date.parse is NaN (falsy) for an invalid calendar date, and 0 (also falsy) at the epoch.
        $time = strtotime($value);
        return $time !== false && $time !== 0;
    }
}
