<?php

/**
 * One policy in a `rules.insurance` plan: which companies it may be with,
 * who holds it, the copay, and the chance the patient has it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class PolicyRule
{
    /**
     * @param non-empty-list<string> $companyKeys one is picked per patient
     * @param ?int $copay whole dollars
     */
    public function __construct(
        public array $companyKeys,
        public Subscriber $subscriber,
        public ?int $copay,
        public int $percent,
    ) {
    }

    public static function parse(MapReader $map): self
    {
        $map->rejectUnknownKeys(['from', 'subscriber', 'copay', 'percent']);
        return new self(
            $map->stringOrList('from'),
            Subscriber::parseRule($map, 'subscriber'),
            Copay::optional($map, 'copay'),
            $map->has('percent') ? $map->percent('percent') : 100,
        );
    }
}
