<?php

/**
 * A relationship between two patients of a case (`cases[].links[]`):
 * `to` becomes a related person of `from`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class CaseLink
{
    public function __construct(
        public string $from,
        public string $to,
        public string $relationship,
        public string $role,
        public RelationFlags $flags,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['from', 'to', 'relationship', 'role', ...RelationFlags::KEYS]);
        $from = $map->string('from');
        $to = $map->string('to');
        if ($from === $to) {
            throw SeedFileException::at($map->pathOf('to'), 'a patient can not be linked to itself');
        }
        return new self(
            $from,
            $to,
            $lists->add(ListReference::RELATIONSHIP, $map->string('relationship'), $map->pathOf('relationship')),
            $lists->add(ListReference::ROLE, $map->string('role'), $map->pathOf('role')),
            RelationFlags::parse($map),
        );
    }
}
