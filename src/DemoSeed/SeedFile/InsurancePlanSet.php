<?php

/**
 * One `rules.insurance.plans[]` entry: who it applies to and the ways they
 * can be insured.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class InsurancePlanSet
{
    /**
     * @param non-empty-list<InsuranceOption> $options
     */
    public function __construct(
        public PatientCondition $when,
        public array $options,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $when = PatientCondition::parse($map->optionalMap('when'));
        if (!$map->has('choose')) {
            return new self($when, [InsuranceOption::parse($map, ['when'])]);
        }
        $map->rejectUnknownKeys(['when', 'choose']);
        $options = array_map(
            static fn (MapReader $option): InsuranceOption => InsuranceOption::parse($option),
            $map->mapList('choose'),
        );
        if ($options === []) {
            throw SeedFileException::at($map->pathOf('choose'), 'needs at least one choice');
        }
        return new self($when, $options);
    }
}
