<?php

/**
 * Groups form rows into category submenus for the main menu's form lists
 * (Patient > Visit Forms, and the Blank Forms lists).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Menu;

final class FormCategoryMenu
{
    /**
     * One submenu per form category, each holding its forms, in the order the
     * rows give them. Categories are compared trimmed, so a category stored
     * with stray whitespace joins its namesake; an empty one is shown as
     * Miscellaneous, as the encounter's forms menu does.
     *
     * @param array<mixed> $rows        form rows as getFormsByCategory() returns them
     * @param int          $requirement menu requirement level for the category entries
     * @param \Closure(string, array<mixed>): \stdClass $formEntry builds one form's menu
     *        entry (url, target, ACL) from its directory and row; the label is set here
     * @return list<\stdClass>
     */
    public static function build(array $rows, int $requirement, \Closure $formEntry): array
    {
        $labels = [];
        $forms = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $category = is_string($row['category'] ?? null) ? trim($row['category']) : '';
            if ($category === '') {
                $category = 'Miscellaneous';
            }
            $labels[$category] ??= xl_form_title($category);
            $directory = is_string($row['directory'] ?? null) ? $row['directory'] : '';
            $form = $formEntry($directory, $row);
            $form->label = xl_form_title(self::title($row));
            $forms[$category][] = $form;
        }

        $menu = [];
        foreach ($labels as $category => $label) {
            $entry = new \stdClass();
            $entry->label = $label;
            $entry->icon = 'fa-caret-right';
            $entry->requirement = $requirement;
            $entry->children = $forms[$category] ?? [];
            $menu[] = $entry;
        }
        return $menu;
    }

    /**
     * The form's nickname, or its name when there is no nickname. A nickname
     * of '0' falls through to the name, as the empty() check this replaces did.
     *
     * @param array<mixed> $row
     */
    public static function title(array $row): string
    {
        $nickname = is_string($row['nickname'] ?? null) ? trim($row['nickname']) : '';
        if ($nickname !== '' && $nickname !== '0') {
            return $nickname;
        }
        return is_string($row['name'] ?? null) ? trim($row['name']) : '';
    }
}
