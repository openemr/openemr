<?php

/**
 * A row count from an aggregate query, narrowed to int.
 *
 * `COUNT()` and `SUM()` columns are typed `mixed`: the driver returns numeric
 * strings, and `SUM()` over no rows returns NULL. Report bodies concatenate
 * these values into their output, so they are narrowed once here rather than
 * at each use.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Reports;

final class MetricCount
{
    /**
     * A value that is not a non-negative whole number is not a count, and is
     * reported as zero rather than coerced.
     */
    public static function fromValue(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && $value !== '' && ctype_digit($value)) {
            return (int) $value;
        }
        return 0;
    }

    /**
     * The named column of a single-row aggregate result, narrowed to int.
     *
     * @param array<array-key, mixed>|false|null $row as querySingleRow() returns
     */
    public static function fromColumn(array|false|null $row, string $column): int
    {
        return self::fromValue(is_array($row) ? ($row[$column] ?? null) : null);
    }
}
