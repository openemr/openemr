<?php

/**
 * VisitListMatcher
 *
 * Puts Visit List patients on their blocks. A patient belongs to a Visit List when the
 * provider, the date and the time window all match a Visit List block. Matching on those,
 * rather than on the block's pc_eid, survives editing one date of a repeating block, which
 * gives that date a new pc_eid.
 *
 * When blocks overlap, a patient goes on the block that started most recently at or before
 * the patient's stored start (the lower pc_eid on a tie). On each list patients are ordered
 * by list order, then stop number, then stored start, then pc_eid; an order or stop not yet
 * given sorts after those that are.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final class VisitListMatcher
{
    /**
     * @param list<VisitListBlock> $blocks
     * @param list<VisitListEntry> $entries
     */
    public function match(array $blocks, array $entries): VisitListSchedule
    {
        usort($blocks, self::compareBlocks(...));

        /** @var array<int, list<VisitListEntry>> $entriesByBlock keyed by index into $blocks */
        $entriesByBlock = [];
        $unplaced = [];
        foreach ($entries as $entry) {
            $index = self::blockIndexFor($blocks, $entry);
            if ($index === null) {
                $unplaced[] = $entry;
                continue;
            }
            $entriesByBlock[$index][] = $entry;
        }

        $lists = [];
        foreach ($blocks as $index => $block) {
            $onBlock = $entriesByBlock[$index] ?? [];
            usort($onBlock, self::compareEntries(...));
            $lists[] = new VisitList($block, $onBlock);
        }

        usort($unplaced, self::compareEntries(...));

        return new VisitListSchedule($lists, $unplaced);
    }

    /**
     * Index of the block the entry goes on, or null when no block contains it: the containing
     * block that started latest. $blocks is sorted by pc_eid within a start, so keeping the
     * first of equal starts picks the lower pc_eid.
     *
     * @param list<VisitListBlock> $blocks
     */
    private static function blockIndexFor(array $blocks, VisitListEntry $entry): ?int
    {
        $found = null;
        foreach ($blocks as $index => $block) {
            if (!$block->contains($entry)) {
                continue;
            }
            if ($found === null || $block->start > $blocks[$found]->start) {
                $found = $index;
            }
        }
        return $found;
    }

    private static function compareBlocks(VisitListBlock $a, VisitListBlock $b): int
    {
        return [$a->providerId, $a->start, $a->eventId] <=> [$b->providerId, $b->start, $b->eventId];
    }

    private static function compareEntries(VisitListEntry $a, VisitListEntry $b): int
    {
        return self::compareMissingLast($a->sequence, $b->sequence)
            ?: self::compareMissingLast($a->stop, $b->stop)
            ?: $a->start <=> $b->start
            ?: $a->eventId <=> $b->eventId;
    }

    private static function compareMissingLast(?int $a, ?int $b): int
    {
        if ($a === null || $b === null) {
            return ($a === null) <=> ($b === null);
        }
        return $a <=> $b;
    }
}
