<?php

/**
 * A phone, email or other contact method written out in a case.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class TelecomEntry
{
    public function __construct(
        public string $system,
        public string $use,
        public string $value,
        public bool $primary,
        public ?string $note,
        public ?int $startedDaysAgo,
        public ?int $endsInDays,
        public ?Inactivation $inactive,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['system', 'use', 'value', 'primary', 'note', 'started_days_ago', 'ends_in_days', 'inactive']);
        $inactive = $map->optionalMap('inactive');
        $endsInDays = $map->optionalInt('ends_in_days');
        if ($endsInDays !== null && $inactive !== null) {
            throw SeedFileException::at($map->pathOf('ends_in_days'), 'can not go with inactive');
        }
        return new self(
            $lists->add(ListReference::TELECOM_SYSTEM, $map->string('system'), $map->pathOf('system')),
            $lists->add(ListReference::TELECOM_USE, $map->string('use'), $map->pathOf('use')),
            $map->string('value'),
            $map->optionalBool('primary'),
            $map->optionalString('note'),
            $map->optionalInt('started_days_ago'),
            $endsInDays,
            $inactive === null ? null : Inactivation::parse($inactive),
        );
    }

    /**
     * @return list<self>
     */
    public static function parseList(MapReader $owner, string $key, ListReferenceCollector $lists): array
    {
        return array_map(
            static fn (MapReader $entry): self => self::parse($entry, $lists),
            $owner->optionalMapList($key),
        );
    }
}
