<?php

/**
 * A date/time precision, the unit of CQL date arithmetic and comparison.
 *
 * Backed by cql-execution's unit names; ELM writes the same names
 * capitalized ("Day"), which fromElm() accepts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

enum Precision: string
{
    case Year = 'year';
    case Month = 'month';
    case Week = 'week';
    case Day = 'day';
    case Hour = 'hour';
    case Minute = 'minute';
    case Second = 'second';
    case Millisecond = 'millisecond';

    public static function fromElm(string $name): self
    {
        return self::tryFrom(strtolower($name)) ?? throw new \UnexpectedValueException("Unknown precision $name");
    }

    /**
     * The fields of a CQL DateTime, most to least significant. Week is a unit
     * of arithmetic, not a field.
     *
     * @return list<self>
     */
    public static function dateTimeFields(): array
    {
        return [self::Year, self::Month, self::Day, self::Hour, self::Minute, self::Second, self::Millisecond];
    }

    /**
     * @return list<self>
     */
    public static function dateFields(): array
    {
        return [self::Year, self::Month, self::Day];
    }

    /**
     * Whether comparisons at this precision normalize timezone offsets first:
     * only for hours and finer, or when no precision is given.
     */
    public static function normalizesOffsets(?self $precision): bool
    {
        return $precision === null
            || in_array($precision, [self::Hour, self::Minute, self::Second, self::Millisecond], true);
    }
}
