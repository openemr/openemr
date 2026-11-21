<?php

/**
 * Values with weights, like {office_visit: 50, new_patient: 10}: each is picked with the chance its weight gives.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class WeightedChoices
{
    /**
     * @param non-empty-list<string> $values
     * @param non-empty-list<positive-int> $weights same order as $values
     */
    public function __construct(
        public array $values,
        public array $weights,
    ) {
        if (count($values) !== count($weights)) {
            throw new \DomainException('Each value needs one weight');
        }
    }

    public static function parse(MapReader $map, string $key): self
    {
        $choices = $map->map($key);
        $values = [];
        $weights = [];
        foreach ($choices->keys() as $value) {
            $weight = $choices->int($value);
            if ($weight < 0) {
                throw SeedFileException::at($choices->pathOf($value), 'weight must not be negative');
            }
            if ($weight > 0) {
                $values[] = $value;
                $weights[] = $weight;
            }
        }
        if ($values === [] || $weights === []) {
            throw SeedFileException::at($choices->path, 'needs at least one value with a weight above 0');
        }
        return new self($values, $weights);
    }
}
