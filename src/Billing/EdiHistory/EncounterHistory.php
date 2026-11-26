<?php

/**
 * Orders an encounter's facts into a dated history, listing each remittance once.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

/**
 * Sorts events by date (undated last, input order preserved within a date) and drops a
 * remittance that the same content already produced (#15530 step 3). A remittance stored
 * in both documents/era and EDI History carries one dedupe key, so it is listed once.
 */
final class EncounterHistory
{
    public const KIND_BILLED = 'billed';
    public const KIND_ACK = 'ack';
    public const KIND_STATUS = 'status';
    public const KIND_REMITTANCE = 'remittance';
    public const KIND_POSTED = 'posted';

    /**
     * @param list<EncounterEvent> $events
     * @return list<EncounterEvent>
     */
    public static function assemble(array $events): array
    {
        $seen = [];
        $kept = [];
        foreach ($events as $event) {
            if ($event->kind === self::KIND_REMITTANCE && $event->dedupeKey !== '') {
                if (isset($seen[$event->dedupeKey])) {
                    continue;
                }
                $seen[$event->dedupeKey] = true;
            }
            $kept[] = $event;
        }

        $indexed = [];
        foreach ($kept as $i => $event) {
            $indexed[] = [$i, $event];
        }
        usort($indexed, static function (array $a, array $b): int {
            [$ia, $ea] = $a;
            [$ib, $eb] = $b;
            if ($ea->date === '' && $eb->date === '') {
                return $ia <=> $ib;
            }
            if ($ea->date === '') {
                return 1;
            }
            if ($eb->date === '') {
                return -1;
            }
            $cmp = strcmp($ea->date, $eb->date);
            return $cmp !== 0 ? $cmp : $ia <=> $ib;
        });

        return array_map(static fn(array $pair): EncounterEvent => $pair[1], $indexed);
    }
}