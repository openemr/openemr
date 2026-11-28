<?php

/**
 * A Visit List written out for one day, with each patient's Order # and Stop #.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class FixedVisitList
{
    /**
     * @param int $day days after the loader runs; moved to the next of the
     *        appointments section's days when it isn't one
     * @param non-empty-list<FixedVisitListEntry> $patients
     */
    public function __construct(
        public string $providerKey,
        public string $facilityKey,
        public int $day,
        public TimeWindow $time,
        public string $patientCategory,
        public array $patients,
        public string $path = '',
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['provider', 'facility', 'day', 'time', 'patient_category', 'patients']);
        $patients = array_map(FixedVisitListEntry::parse(...), $map->mapList('patients'));
        if ($patients === []) {
            throw SeedFileException::at($map->pathOf('patients'), 'must list at least one patient');
        }
        $orders = array_map(static fn (FixedVisitListEntry $entry): int => $entry->order, $patients);
        if (count($orders) !== count(array_unique($orders))) {
            throw SeedFileException::at($map->pathOf('patients'), 'each patient needs its own order');
        }
        return new self(
            $map->string('provider'),
            $map->string('facility'),
            $map->int('day'),
            TimeWindow::fromString($map->string('time'), $map->pathOf('time')),
            $map->optionalString('patient_category') ?? AppointmentsSpec::DEFAULT_VISIT_LIST_CATEGORY,
            $patients,
            $map->path,
        );
    }
}
