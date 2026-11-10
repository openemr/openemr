<?php

/**
 * An expanded value set: the codes a measure's value set contains.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final readonly class ValueSet
{
    /**
     * @param list<Code> $codes
     */
    public function __construct(
        public string $oid,
        public ?string $version = null,
        public array $codes = [],
    ) {
    }

    /**
     * The "code in valueset" test. A bare code string matches on the code
     * alone, which is ambiguous, and an error, when it matches a value set
     * spanning several code systems.
     */
    public function hasMatch(mixed $other): bool
    {
        $codes = Clinical::toCodeList($other);
        if (count($codes) === 1 && is_string($codes[0])) {
            $matchFound = false;
            $multipleSystems = false;
            $firstSystem = $this->codes[0]->system ?? null;
            foreach ($this->codes as $code) {
                if ($code->system !== $firstSystem) {
                    $multipleSystems = true;
                }
                if ($code->code === $codes[0]) {
                    $matchFound = true;
                }
                if ($multipleSystems && $matchFound) {
                    throw new \RuntimeException('In (valueset) is ambiguous -- multiple codes with multiple code systems exist in value set.');
                }
            }
            return $matchFound;
        }
        return Clinical::codesInList($codes, $this->codes);
    }

    /**
     * The value set's codes without duplicates (ExpandValueSet).
     *
     * @return list<Code>
     */
    public function expand(): array
    {
        $expanded = [];
        foreach ($this->codes as $code) {
            foreach ($expanded as $unique) {
                if (
                    $unique->code === $code->code
                    && $unique->system === $code->system
                    && $unique->version === $code->version
                    && $unique->display === $code->display
                ) {
                    continue 2;
                }
            }
            $expanded[] = $code;
        }
        return $expanded;
    }
}
