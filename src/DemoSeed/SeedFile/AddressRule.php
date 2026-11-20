<?php

/**
 * One `rules.addresses[]` entry: an extra address a patient may get, in
 * the patient's own city (an old street address, a PO box, a temporary
 * address).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

final readonly class AddressRule
{
    /**
     * @param ?DayRange $endedDaysAgo set for an address no longer in use
     */
    public function __construct(
        public int $percent,
        public PatientCondition $when,
        public string $type,
        public string $use,
        public ?DayRange $startedDaysAgo,
        public ?DayRange $endedDaysAgo,
        public ?string $reason,
        public bool $poBox,
        public ?string $note,
    ) {
    }

    public static function parse(MapReader $map, ListReferenceCollector $lists): self
    {
        $map->rejectUnknownKeys(['percent', 'when', 'add']);
        $add = $map->map('add');
        $add->rejectUnknownKeys(['type', 'use', 'started_days_ago', 'ended_days_ago', 'reason', 'po_box', 'note']);
        $endedDaysAgo = DayRange::optional($add, 'ended_days_ago');
        $reason = $add->optionalString('reason');
        if ($reason !== null && $endedDaysAgo === null) {
            throw SeedFileException::at($add->pathOf('reason'), 'only goes with ended_days_ago');
        }
        return new self(
            $map->percent('percent'),
            PatientCondition::parse($map->optionalMap('when')),
            $lists->add(ListReference::ADDRESS_TYPE, $add->string('type'), $add->pathOf('type')),
            $lists->add(ListReference::ADDRESS_USE, $add->string('use'), $add->pathOf('use')),
            DayRange::optional($add, 'started_days_ago'),
            $endedDaysAgo,
            $reason,
            $add->optionalBool('po_box'),
            $add->optionalString('note'),
        );
    }
}
