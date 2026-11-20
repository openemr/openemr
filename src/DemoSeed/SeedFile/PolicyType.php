<?php

/**
 * Primary, secondary or tertiary: the insurance_data `type` column.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

enum PolicyType: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Tertiary = 'tertiary';

    public static function parse(MapReader $map, string $key): self
    {
        return self::tryFrom($map->string($key))
            ?? throw SeedFileException::at($map->pathOf($key), 'must be primary, secondary or tertiary');
    }
}
