<?php

/**
 * One `rules.related_people.people[]` entry: when it applies, how often,
 * and the person it adds.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class RelatedPersonRule
{
    /**
     * @param non-empty-list<string> $relationships one is picked per patient
     */
    public function __construct(
        public PatientCondition $when,
        public int $percent,
        public array $relationships,
        public string $role,
        public RelationFlags $flags,
        public bool $sameAddress,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['when', 'percent', 'add']);
        $add = $map->map('add');
        $add->rejectUnknownKeys(['relationship', 'role', 'same_address', ...RelationFlags::KEYS]);
        $relationships = $add->stringOrList('relationship');
        $lists->addAll(ListReference::RELATIONSHIP, $relationships, $add->pathOf('relationship'));
        return new self(
            PatientCondition::parse($map->optionalMap('when')),
            $map->percent('percent'),
            $relationships,
            $lists->add(ListReference::ROLE, $add->string('role'), $add->pathOf('role')),
            RelationFlags::parse($add),
            $add->optionalBool('same_address'),
        );
    }
}
