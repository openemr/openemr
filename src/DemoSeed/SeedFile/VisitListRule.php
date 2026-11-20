<?php

/**
 * A Visit List block made on each of a provider's days, with a number of patients drawn for it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class VisitListRule
{
    /**
     * @param ?list<Weekday> $weekdays null = the appointments section's days
     * @param PatientCondition $prefer patients who match are drawn first
     */
    public function __construct(
        public string $providerKey,
        public string $facilityKey,
        public ?array $weekdays,
        public TimeWindow $time,
        public int $minPatients,
        public int $maxPatients,
        public string $patientCategory,
        public PatientCondition $prefer,
        public string $path = '',
    ) {
        if ($minPatients < 1 || $maxPatients < $minPatients) {
            throw new \DomainException('Visit List patient count must have 1 <= min <= max');
        }
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['provider', 'facility', 'weekdays', 'time', 'patients', 'patient_category', 'prefer']);
        [$min, $max] = self::patientCount($map);
        return new self(
            $map->string('provider'),
            $map->string('facility'),
            Weekday::optionalList($map, 'weekdays'),
            TimeWindow::fromString($map->string('time'), $map->pathOf('time')),
            $min,
            $max,
            $map->optionalString('patient_category') ?? AppointmentsSpec::DEFAULT_VISIT_LIST_CATEGORY,
            PatientCondition::parse($map->optionalMap('prefer')),
            $map->path,
        );
    }

    /**
     * @return array{int, int}
     */
    private static function patientCount(MapReader $map): array
    {
        $value = $map->raw('patients');
        if (is_int($value) && $value >= 1) {
            return [$value, $value];
        }
        if (is_string($value) && preg_match('/^\s*(\d{1,3})\s*-\s*(\d{1,3})\s*$/', $value, $matches) === 1) {
            $min = (int) $matches[1];
            $max = (int) $matches[2];
            if ($min >= 1 && $max >= $min) {
                return [$min, $max];
            }
        }
        throw SeedFileException::at($map->pathOf('patients'), 'expected a number of patients like 6 or a range like "5-9"');
    }
}
