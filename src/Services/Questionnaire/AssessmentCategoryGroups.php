<?php

/**
 * Available questionnaires grouped by category, for the patient dashboard's assessment list.
 *
 * Grouped by category id, not title: two ids can share a title (a fallback title, or two list
 * options named alike), and each group keeps its own fold state. A group's key is the user
 * setting that remembers whether it is folded away, from CollapsedSections::categoryKey().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

use OpenEMR\Services\Globals\CollapsedSections;

final class AssessmentCategoryGroups
{
    /**
     * Group repository rows in the order they arrive. Rows without a positive id are left out.
     *
     * @param list<array<string, mixed>> $records rows from getAvailableQuestionnaires()
     * @param string $unassignedTitle heading for rows with no category, already translated
     * @return array<string, array{title: string, records: list<array<string, mixed>>}>
     */
    public static function fromRecords(array $records, string $unassignedTitle): array
    {
        $groups = [];
        foreach ($records as $record) {
            if (!self::hasPositiveId($record['id'] ?? null)) {
                continue;
            }

            $category = self::text($record['category'] ?? null);
            $key = CollapsedSections::categoryKey($category);
            $groups[$key] ??= ['title' => self::title($record, $category, $unassignedTitle), 'records' => []];
            $groups[$key]['records'][] = $record;
        }

        return $groups;
    }

    /**
     * The list option's title, or one made from its id when the option is missing or
     * inactive, or the unassigned heading when there is no category.
     *
     * @param array<string, mixed> $record
     */
    private static function title(array $record, string $category, string $unassignedTitle): string
    {
        $title = self::text($record['category_title'] ?? null);
        if ($title !== '') {
            return $title;
        }

        return $category !== '' ? ucwords(str_replace(['_', '-'], ' ', $category)) : $unassignedTitle;
    }

    private static function hasPositiveId(mixed $id): bool
    {
        if (is_int($id)) {
            return $id > 0;
        }

        return is_string($id) && ctype_digit($id) && (int)$id > 0;
    }

    private static function text(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        return is_int($value) || is_float($value) ? (string)$value : '';
    }
}
