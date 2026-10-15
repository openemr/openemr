<?php

/**
 * Turns loosely typed input (JSON bodies, database rows) into the types the
 * module expects, so nothing downstream handles "mixed".
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace Exetazo\Grapheus;

final class Val
{
    public static function str(mixed $v, int $max = 0): string
    {
        $s = is_scalar($v) ? trim((string) $v) : '';
        return $max > 0 ? mb_substr($s, 0, $max) : $s;
    }

    public static function int(mixed $v): int
    {
        return is_numeric($v) ? (int) $v : 0;
    }

    public static function bool(mixed $v): bool
    {
        if (is_bool($v)) {
            return $v;
        }
        return is_scalar($v) && in_array(strtolower((string) $v), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public static function map(mixed $v): array
    {
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $k => $x) {
            $out[(string) $k] = $x;
        }
        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function maps(mixed $v): array
    {
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $x) {
            $out[] = self::map($x);
        }
        return $out;
    }

    /**
     * @return list<string>
     */
    public static function strings(mixed $v): array
    {
        if (!is_array($v)) {
            return [];
        }
        $out = [];
        foreach ($v as $x) {
            $s = self::str($x);
            if ($s !== '') {
                $out[] = $s;
            }
        }
        return $out;
    }
}
