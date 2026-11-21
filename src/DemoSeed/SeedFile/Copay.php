<?php

/**
 * Reads a copay in whole dollars.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final class Copay
{
    public static function optional(MapReader $map, string $key): ?int
    {
        $copay = $map->optionalInt($key);
        if ($copay !== null && ($copay < 0 || $copay > 10000)) {
            throw SeedFileException::at($map->pathOf($key), 'must be whole dollars from 0 to 10000');
        }
        return $copay;
    }
}
