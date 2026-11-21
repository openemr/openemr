<?php

/**
 * Names for people the loader makes up (`names`).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class NamePools
{
    /**
     * @param non-empty-list<string> $female
     * @param non-empty-list<string> $male
     * @param non-empty-list<string> $last
     */
    public function __construct(
        public array $female,
        public array $male,
        public array $last,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['female', 'male', 'last']);
        return new self($map->stringOrList('female'), $map->stringOrList('male'), $map->stringOrList('last'));
    }
}
