<?php

/**
 * One patient on a written-out Visit List: a case patient, or any patient free that day.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class FixedVisitListEntry
{
    /**
     * @param ?string $patientKey case patient key; null = any patient
     */
    public function __construct(
        public ?string $patientKey,
        public int $order,
        public ?int $stop,
    ) {
        if ($order < 1 || ($stop !== null && $stop < 1)) {
            throw new \DomainException('Visit List order and stop must be positive');
        }
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['patient', 'any', 'order', 'stop']);
        $patientKey = $map->optionalString('patient');
        $any = $map->optionalBool('any');
        if (($patientKey === null) !== $any) {
            throw SeedFileException::at($map->path, 'give patient: <case patient key> or any: true');
        }
        $order = $map->int('order');
        if ($order < 1) {
            throw SeedFileException::at($map->pathOf('order'), 'must be at least 1');
        }
        $stop = $map->optionalInt('stop');
        if ($stop !== null && $stop < 1) {
            throw SeedFileException::at($map->pathOf('stop'), 'must be at least 1');
        }
        return new self($patientKey, $order, $stop);
    }
}
