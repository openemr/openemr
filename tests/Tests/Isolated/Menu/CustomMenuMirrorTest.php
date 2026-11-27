<?php

/**
 * The shipped custom menus stay byte-identical to the standard menus.
 *
 * sites/default/documents/custom_menus/Custom.json and its patient_menus counterpart are
 * shipped as exact copies of the standard menus: a user given the "Custom" menu role sees that
 * file as-is, and a site edits its own copy from there. A menu entry added to (or changed in)
 * only the standard menu is missing or different for those users, so each pair must match.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Menu;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CustomMenuMirrorTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function menuPairs(): array
    {
        return [
            'main menu' => [
                'interface/main/tabs/menu/menus/standard.json',
                'sites/default/documents/custom_menus/Custom.json',
            ],
            'patient menu' => [
                'interface/main/tabs/menu/menus/patient_menus/standard.json',
                'sites/default/documents/custom_menus/patient_menus/Custom.json',
            ],
        ];
    }

    #[Test]
    #[DataProvider('menuPairs')]
    public function customMenuIsAnExactCopyOfTheStandardMenu(string $standardPath, string $customPath): void
    {
        $standard = self::read($standardPath);
        $custom = self::read($customPath);

        self::assertSame(
            $standard,
            $custom,
            $customPath . ' must be an exact copy of ' . $standardPath
                . '; copy the standard menu over it.' . self::entryDifferences($standard, $custom)
        );
    }

    private static function read(string $relativePath): string
    {
        $contents = file_get_contents(dirname(__DIR__, 4) . '/' . $relativePath);
        self::assertIsString($contents, $relativePath . ' is readable');

        return $contents;
    }

    /**
     * The menu entries (menu_id and label) found in only one of the two menus, counted per
     * occurrence since menu_ids repeat; empty when both have the same entries and only fields
     * or formatting differ.
     */
    private static function entryDifferences(string $standardJson, string $customJson): string
    {
        $standard = self::entries(json_decode($standardJson, true));
        $custom = self::entries(json_decode($customJson, true));
        $lines = [];
        foreach (self::subtract($standard, $custom) as $entry) {
            $lines[] = '  missing from custom: ' . $entry;
        }
        foreach (self::subtract($custom, $standard) as $entry) {
            $lines[] = '  only in custom: ' . $entry;
        }

        return $lines === [] ? '' : "\n" . implode("\n", $lines);
    }

    /**
     * @return list<string> "<menu_id> (<label>)" for every entry, children included
     */
    private static function entries(mixed $menu): array
    {
        if (!is_array($menu)) {
            return [];
        }
        $entries = [];
        foreach ($menu as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $id = $entry['menu_id'] ?? null;
            $label = $entry['label'] ?? null;
            $entries[] = (is_string($id) ? $id : '(no menu_id)') . ' (' . (is_string($label) ? $label : '') . ')';
            array_push($entries, ...self::entries($entry['children'] ?? null));
        }

        return $entries;
    }

    /**
     * The entries of $from left after removing one occurrence for each entry of $remove.
     *
     * @param list<string> $from
     * @param list<string> $remove
     * @return list<string>
     */
    private static function subtract(array $from, array $remove): array
    {
        foreach ($remove as $entry) {
            $at = array_search($entry, $from, true);
            if ($at !== false) {
                unset($from[$at]);
            }
        }

        return array_values($from);
    }
}
