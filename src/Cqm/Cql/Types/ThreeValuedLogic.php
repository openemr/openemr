<?php

/**
 * CQL's three-valued Boolean logic, where null means unknown.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final class ThreeValuedLogic
{
    public static function and(?bool ...$values): ?bool
    {
        if (in_array(false, $values, true)) {
            return false;
        }
        return in_array(null, $values, true) ? null : true;
    }

    public static function or(?bool ...$values): ?bool
    {
        if (in_array(true, $values, true)) {
            return true;
        }
        return in_array(null, $values, true) ? null : false;
    }

    public static function xor(?bool ...$values): ?bool
    {
        if (in_array(null, $values, true)) {
            return null;
        }
        $result = array_shift($values) ?? false;
        foreach ($values as $value) {
            $result = $result !== $value;
        }
        return $result;
    }

    public static function not(?bool $value): ?bool
    {
        return $value === null ? null : !$value;
    }

    public static function implies(?bool $left, ?bool $right): ?bool
    {
        if ($left === true) {
            return $right;
        }
        if ($left === false) {
            return true;
        }
        return $right === true ? true : null;
    }
}
