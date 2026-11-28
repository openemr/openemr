<?php

/**
 * Hands out made-up phone numbers in the fictional 555-0100..0199
 * range, never the same one twice and never one the seed file spells out.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

final class PhoneNumberPool
{
    /** @var array<string, true> */
    private array $used = [];

    /**
     * @param non-empty-list<string> $areaCodes
     * @param list<string> $reserved numbers the seed file uses itself
     */
    public function __construct(private readonly array $areaCodes, array $reserved)
    {
        foreach ($reserved as $number) {
            $this->used[self::digits($number)] = true;
        }
    }

    public function next(Dice $dice): string
    {
        $capacity = count($this->areaCodes) * 100;
        for ($attempt = 0; $attempt < $capacity * 4; $attempt++) {
            $number = sprintf('(%s) 555-01%02d', $dice->pick($this->areaCodes), $dice->between(0, 99));
            if (!isset($this->used[self::digits($number)])) {
                $this->used[self::digits($number)] = true;
                return $number;
            }
        }
        throw new PlanException('Ran out of 555-01xx phone numbers; add area codes to contact_data');
    }

    private static function digits(string $number): string
    {
        return preg_replace('/\D/', '', $number) ?? $number;
    }
}
