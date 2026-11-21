<?php

/**
 * A day of the week as the seed file names it (Mon ... Sun).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

enum Weekday: string
{
    case Mon = 'Mon';
    case Tue = 'Tue';
    case Wed = 'Wed';
    case Thu = 'Thu';
    case Fri = 'Fri';
    case Sat = 'Sat';
    case Sun = 'Sun';

    /**
     * @return ?non-empty-list<self> null when the key is missing
     */
    public static function optionalList(MapReader $map, string $key): ?array
    {
        if (!$map->has($key)) {
            return null;
        }
        $days = [];
        foreach ($map->stringList($key) as $index => $name) {
            $days[] = self::tryFrom($name)
                ?? throw SeedFileException::at($map->pathOf($key) . '[' . $index . ']', 'expected Mon, Tue, Wed, Thu, Fri, Sat or Sun');
        }
        if ($days === []) {
            throw SeedFileException::at($map->pathOf($key), 'must name at least one day');
        }
        return $days;
    }

    public static function of(\DateTimeImmutable $date): self
    {
        return self::from($date->format('D'));
    }
}
