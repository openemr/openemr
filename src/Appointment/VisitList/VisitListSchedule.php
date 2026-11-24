<?php

/**
 * VisitListSchedule
 *
 * The Visit Lists found for some providers and dates, and any Visit List patients that no
 * block claims (for example after their block was deleted or moved to another day).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final readonly class VisitListSchedule
{
    /**
     * @param list<VisitList>      $lists     ordered by provider, then start, then block event ID
     * @param list<VisitListEntry> $unplaced  Visit List patients no block claims, in list order
     */
    public function __construct(
        public array $lists,
        public array $unplaced,
    ) {
    }

    public static function empty(): self
    {
        return new self([], []);
    }

    /**
     * The list a patient appointment is on, or null when no block claims it.
     */
    public function listFor(int $entryEventId): ?VisitList
    {
        foreach ($this->lists as $list) {
            foreach ($list->entries as $entry) {
                if ($entry->eventId === $entryEventId) {
                    return $list;
                }
            }
        }
        return null;
    }
}
