<?php

/**
 * The plain, dated statements a biller can copy into a reconsideration letter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use DateTimeImmutable;

/**
 * Facts, not arguments (#15530 step 4): each line states what a file said and when.
 * Why the payer is wrong is left to the person writing the letter. A day count is the
 * difference of two dates the files give; nothing is inferred and no date is guessed.
 */
final class ClaimFilingStatements
{
    /**
     * @param list<EncounterEvent> $events the history, already ordered
     * @return list<string>
     */
    public static function statements(array $events): array
    {
        $lines = [];
        foreach ($events as $event) {
            $line = $event->date !== ''
                ? 'On ' . $event->date . ', ' . $event->title . '.'
                : 'Undated: ' . $event->title . '.';
            if ($event->detail !== '') {
                $line .= ' ' . $event->detail . '.';
            }
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * Days from the date of service to each dated fact. A fact before the service date
     * gives a negative count, as the dates read; an undated fact gives none.
     *
     * @param list<EncounterEvent> $events
     * @return list<array{date: string, days: int, title: string}>
     */
    public static function daysFromService(string $serviceDate, array $events): array
    {
        $from = self::day($serviceDate);
        if ($from === null) {
            return [];
        }
        $out = [];
        foreach ($events as $event) {
            $to = self::day($event->date);
            if ($to === null) {
                continue;
            }
            $out[] = [
                'date' => $event->date,
                'days' => (int) $from->diff($to)->format('%r%a'),
                'title' => $event->title,
            ];
        }
        return $out;
    }

    private static function day(string $value): ?DateTimeImmutable
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m) !== 1) {
            return null;
        }
        if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
            return null;
        }
        return new DateTimeImmutable($value);
    }
}