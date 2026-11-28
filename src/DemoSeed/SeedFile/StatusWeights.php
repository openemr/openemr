<?php

/**
 * Appointment status weights (list `apptstat`) for past days, appointments today that have started when the loader runs, and the rest (later today and future days).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class StatusWeights
{
    public function __construct(
        public WeightedChoices $past,
        public WeightedChoices $today,
        public WeightedChoices $future,
    ) {
    }

    public static function none(): self
    {
        $none = new WeightedChoices(['-'], [1]);
        return new self($none, $none, $none);
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['past', 'today', 'future']);
        $parsed = [];
        foreach (['past', 'today', 'future'] as $key) {
            $choices = WeightedChoices::parse($map, $key);
            $lists->addAll('apptstat', $choices->values, $map->pathOf($key));
            $parsed[] = $choices;
        }
        return new self(...$parsed);
    }
}
