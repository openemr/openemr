<?php

/**
 * An extra address written out in a case.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

use OpenEMR\DemoSeed\PostalAddress;

final readonly class AddressEntry
{
    public function __construct(
        public string $type,
        public string $use,
        public PostalAddress $address,
        public ?int $startedDaysAgo,
        public ?int $endedDaysAgo,
        public ?string $reason,
        public ?string $note,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys([
            'type', 'use', 'street', 'city', 'state', 'postal_code', 'started_days_ago', 'ended_days_ago', 'reason', 'note',
        ]);
        $startedDaysAgo = $map->optionalInt('started_days_ago');
        $endedDaysAgo = $map->optionalInt('ended_days_ago');
        if ($startedDaysAgo !== null && $endedDaysAgo !== null && $endedDaysAgo > $startedDaysAgo) {
            throw SeedFileException::at($map->pathOf('ended_days_ago'), 'must not be before started_days_ago');
        }
        $reason = $map->optionalString('reason');
        if ($reason !== null && $endedDaysAgo === null) {
            throw SeedFileException::at($map->pathOf('reason'), 'only goes with ended_days_ago');
        }
        return new self(
            $lists->add(ListReference::ADDRESS_TYPE, $map->string('type'), $map->pathOf('type')),
            $lists->add(ListReference::ADDRESS_USE, $map->string('use'), $map->pathOf('use')),
            new PostalAddress(
                $map->string('street'),
                $map->string('city'),
                $lists->add(ListReference::STATE, $map->string('state'), $map->pathOf('state')),
                $map->string('postal_code'),
            ),
            $startedDaysAgo,
            $endedDaysAgo,
            $reason,
            $map->optionalString('note'),
        );
    }
}
