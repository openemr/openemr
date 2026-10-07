<?php

/**
 * Turns EncounterService's column-oriented encounter list into options for a
 * select, most recent encounter first.
 *
 * The service returns three parallel lists ('ids', 'dates', 'categories'), so
 * the number of encounters is the length of one of those lists, not of the
 * outer array. It applies no ORDER BY, so the order is sorted here rather than
 * assumed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms\Types;

/**
 * @phpstan-type EncounterOption array{value: non-empty-string, label: string, date: string}
 */
final class EncounterOptions
{
    /**
     * @param  array<array-key, mixed> $encounterList as returned by
     *                                 EncounterService::getPatientEncounterListWithCategories()
     * @return list<EncounterOption>   most recent encounter first
     */
    public static function fromEncounterList(array $encounterList): array
    {
        $ids = is_array($encounterList['ids'] ?? null) ? $encounterList['ids'] : [];
        $dates = is_array($encounterList['dates'] ?? null) ? $encounterList['dates'] : [];
        $categories = is_array($encounterList['categories'] ?? null) ? $encounterList['categories'] : [];

        $options = [];
        foreach ($ids as $index => $id) {
            $value = self::asString($id);
            if ($value === '') {
                continue;
            }
            $date = self::asString($dates[$index] ?? null);
            $category = self::asString($categories[$index] ?? null);
            $label = trim($date . ' - ' . $category, " -");
            $options[] = ['value' => $value, 'label' => $label === '' ? $value : $label, 'date' => $date];
        }

        // Most recent first. The dates are 'Y-m-d', which sorts lexicographically.
        usort($options, fn(array $a, array $b): int => [$b['date'], $b['value']] <=> [$a['date'], $a['value']]);

        return $options;
    }

    /**
     * Whether the options already offer this encounter. A value the options do
     * not contain would be dropped by the browser on save, unlinking the record.
     *
     * @param list<EncounterOption> $options
     */
    public static function contains(array $options, string $value): bool
    {
        foreach ($options as $option) {
            if ($option['value'] === $value) {
                return true;
            }
        }
        return false;
    }

    private static function asString(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }
        return is_int($value) ? (string) $value : '';
    }
}
