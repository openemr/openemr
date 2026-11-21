<?php

/**
 * A provider's regular clinic hours: on each of its days, each 15-minute slot is booked with the fill chance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class ClinicSpec
{
    /**
     * @param ?list<Weekday> $weekdays null = the appointments section's days
     * @param non-empty-list<TimeWindow> $hours
     * @param int $fill percent of slots booked
     */
    public function __construct(
        public string $providerKey,
        public string $facilityKey,
        public ?array $weekdays,
        public array $hours,
        public int $fill,
        public string $path = '',
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['provider', 'facility', 'weekdays', 'hours', 'fill']);
        return new self(
            $map->string('provider'),
            $map->string('facility'),
            Weekday::optionalList($map, 'weekdays'),
            TimeWindow::listFromString($map->string('hours'), $map->pathOf('hours')),
            $map->percent('fill'),
            $map->path,
        );
    }
}
