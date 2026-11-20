<?php

/**
 * Deterministic random choices: each decision gets its own generator,
 * seeded from the loader seed and names for the decision, so adding a rule
 * or a patient doesn't change the choices made for the others.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

use OpenEMR\DemoSeed\SeedFile\DayRange;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

final readonly class Dice
{
    private function __construct(private Randomizer $randomizer)
    {
    }

    public static function for(int $seed, string ...$parts): self
    {
        $engineSeed = hash('sha256', implode('|', [(string) $seed, ...$parts]), true);
        return new self(new Randomizer(new Xoshiro256StarStar($engineSeed)));
    }

    /**
     * True with the given chance, 0-100.
     */
    public function percent(int $percent): bool
    {
        if ($percent >= 100) {
            return true;
        }
        if ($percent <= 0) {
            return false;
        }
        return $this->randomizer->getInt(1, 100) <= $percent;
    }

    public function between(int $min, int $max): int
    {
        return $this->randomizer->getInt(min($min, $max), max($min, $max));
    }

    public function inRange(DayRange $range): int
    {
        return $this->between($range->min, $range->max);
    }

    /**
     * @template T
     * @param non-empty-list<T> $items
     * @return T
     */
    public function pick(array $items): mixed
    {
        return $items[$this->randomizer->getInt(0, count($items) - 1)];
    }

    /**
     * Index of the item picked, each with the chance its weight gives.
     *
     * @param non-empty-list<positive-int> $weights
     */
    public function weighted(array $weights): int
    {
        $roll = $this->randomizer->getInt(1, array_sum($weights));
        foreach ($weights as $index => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $index;
            }
        }
        return count($weights) - 1;
    }
}
