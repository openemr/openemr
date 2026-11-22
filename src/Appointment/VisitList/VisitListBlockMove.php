<?php

/**
 * VisitListBlockMove
 *
 * Works out where a saved Visit List block's patients go. Each date before the save maps to a
 * date after it (dateMap()), and each provider to a provider (providerMap()); a list's
 * patients go to the block occurrence of the mapped provider on the mapped date.
 *
 * Dates are paired by their place in the repeat pattern, counting excluded dates, when the
 * block repeats the same way as before: the 1st date of the pattern before the save → the 1st
 * after it. A start date moved by N days moves simple repeats by N days, and a "repeat on"
 * series ("2nd Tuesday of the month") keeps its pattern, which shifting by days would break.
 * When the save changed how the block repeats, each date maps to itself: an occurrence still
 * on its date keeps its patients.
 *
 * Patients whose date maps to nothing, or to a date the block is excluded from, stay where
 * they are, no longer on a list. Patients on dates before today stay where they are too: those
 * appointments are history (decided 2026-11-22). Patients already at their occurrence's start
 * and provider are left alone.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final class VisitListBlockMove
{
    /**
     * @param list<VisitList>        $before      the occurrences the save changed, with their patients
     * @param list<VisitListBlock>   $after       the block's occurrences after the save
     * @param array<int, int>        $providerMap each provider before the save → the provider after it
     * @param array<string, string>  $dateMap     each date before the save (Y-m-d) → the date after it
     * @param string                 $today       today (Y-m-d): patients on earlier dates are not moved
     */
    public static function plan(array $before, array $after, array $providerMap, array $dateMap, string $today): VisitListMovePlan
    {
        $afterByProvider = [];
        foreach ($after as $block) {
            $afterByProvider[$block->providerId][$block->date()] ??= $block;
        }

        $moves = [];
        $stranded = [];
        $past = [];
        foreach ($before as $list) {
            if ($list->block->date() < $today) {
                array_push($past, ...$list->entries);
                continue;
            }
            $to = $afterByProvider[$providerMap[$list->block->providerId] ?? 0][$dateMap[$list->block->date()] ?? ''] ?? null;
            foreach ($list->entries as $entry) {
                if ($to === null) {
                    $stranded[] = $entry;
                } elseif ($entry->providerId !== $to->providerId || $entry->start != $to->start) {
                    $moves[] = new VisitListMove($entry, $to);
                }
            }
        }
        return new VisitListMovePlan($moves, $stranded, $past);
    }

    /**
     * Pairs the dates of the repeat pattern before a save with those after it, in order: the
     * 1st with the 1st, and so on. Dates past the end of the shorter pattern map to nothing.
     *
     * @param list<string> $before the pattern's dates before the save (Y-m-d), excluded dates included
     * @param list<string> $after  the pattern's dates after it
     * @return array<string, string>
     */
    public static function dateMap(array $before, array $after): array
    {
        sort($before);
        sort($after);
        $map = [];
        foreach ($before as $index => $date) {
            if (isset($after[$index])) {
                $map[$date] = $after[$index];
            }
        }
        return $map;
    }

    /**
     * Pairs the providers before a save with those after it: a provider kept keeps their
     * patients; the providers removed hand theirs to the providers added, in order of ID.
     * A removed provider with no one added in their place maps to nobody.
     *
     * @param list<int> $before
     * @param list<int> $after
     * @return array<int, int>
     */
    public static function providerMap(array $before, array $after): array
    {
        $map = [];
        foreach (array_intersect($before, $after) as $kept) {
            $map[$kept] = $kept;
        }

        $removed = array_values(array_diff($before, $after));
        $added = array_values(array_diff($after, $before));
        sort($removed);
        sort($added);
        foreach ($removed as $index => $providerId) {
            if (isset($added[$index])) {
                $map[$providerId] = $added[$index];
            }
        }
        return $map;
    }
}
