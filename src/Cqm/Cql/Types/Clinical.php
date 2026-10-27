<?php

/**
 * Code matching shared by Code, Concept and ValueSet.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final class Clinical
{
    /**
     * Flattens codes: a list is flattened, a concept or value set gives its
     * codes, anything else is itself.
     *
     * @return list<mixed>
     */
    public static function toCodeList(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        if (is_array($value)) {
            $list = [];
            foreach ($value as $item) {
                array_push($list, ...self::toCodeList($item));
            }
            return $list;
        }
        if ($value instanceof Concept || $value instanceof ValueSet) {
            return $value->codes;
        }
        return [$value];
    }

    /**
     * Whether any of the first codes matches any of the second: a string by
     * code, a code by code and system.
     *
     * @param list<mixed> $candidates
     * @param list<Code> $codes
     */
    public static function codesInList(array $candidates, array $codes): bool
    {
        foreach ($candidates as $candidate) {
            foreach ($codes as $code) {
                if (is_string($candidate)) {
                    if ($candidate === $code->code) {
                        return true;
                    }
                } elseif ($candidate instanceof Code && $candidate->code === $code->code && $candidate->system === $code->system) {
                    return true;
                }
            }
        }
        return false;
    }
}
