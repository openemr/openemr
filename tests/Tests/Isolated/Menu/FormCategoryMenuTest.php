<?php

/**
 * Isolated FormCategoryMenu Test
 *
 * Pins the grouping behind Patient > Visit Forms and the Blank Forms lists:
 * every form lands in its category's submenu (Visit Forms used to drop all of
 * them, leaving empty category headings), and a category stored with a
 * trailing newline joins its namesake instead of forming a second submenu.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Menu;

use OpenEMR\Menu\FormCategoryMenu;
use PHPUnit\Framework\TestCase;

final class FormCategoryMenuTest extends TestCase
{
    /**
     * @param array<mixed> $rows
     * @return list<\stdClass>
     */
    private static function build(array $rows): array
    {
        return FormCategoryMenu::build($rows, 2, static function (string $directory): \stdClass {
            $entry = new \stdClass();
            $entry->url = 'form:' . $directory;
            return $entry;
        });
    }

    /**
     * @param list<\stdClass> $menu
     * @return array<string, list<string>> category label => form labels
     */
    private static function shape(array $menu): array
    {
        $shape = [];
        foreach ($menu as $category) {
            $label = is_string($category->label ?? null) ? $category->label : '';
            $children = is_array($category->children ?? null) ? $category->children : [];
            foreach ($children as $form) {
                self::assertInstanceOf(\stdClass::class, $form);
                $shape[$label][] = is_string($form->label ?? null) ? $form->label : '';
            }
            $shape[$label] ??= [];
        }
        return $shape;
    }

    public function testEveryFormLandsInItsCategory(): void
    {
        $menu = self::build([
            ['category' => 'Administrative', 'directory' => 'fee_sheet', 'name' => 'Fee Sheet', 'nickname' => ''],
            ['category' => 'Clinical', 'directory' => 'vitals', 'name' => 'Vitals', 'nickname' => ''],
            ['category' => 'Clinical', 'directory' => 'soap', 'name' => 'SOAP', 'nickname' => ''],
        ]);

        self::assertSame(['Administrative' => ['Fee Sheet'], 'Clinical' => ['Vitals', 'SOAP']], self::shape($menu));
        self::assertSame(2, $menu[0]->requirement);
        self::assertSame('fa-caret-right', $menu[0]->icon);
        $clinical = $menu[1]->children;
        self::assertIsArray($clinical);
        self::assertInstanceOf(\stdClass::class, $clinical[0]);
        self::assertSame('form:vitals', $clinical[0]->url, 'the caller builds the entry; the label is added');
    }

    public function testCategoryWithTrailingNewlineJoinsItsNamesake(): void
    {
        // info.txt lines are read with their newline, so a form registered
        // through Forms Admin is stored with "Clinical\n" as its category.
        $menu = self::build([
            ['category' => 'Clinical', 'directory' => 'vitals', 'name' => 'Vitals', 'nickname' => ''],
            ['category' => "Clinical\n", 'directory' => 'phq9', 'name' => "PHQ-9\n", 'nickname' => ''],
        ]);

        self::assertSame(['Clinical' => ['Vitals', 'PHQ-9']], self::shape($menu));
    }

    public function testMissingCategoryIsMiscellaneous(): void
    {
        $menu = self::build([
            ['category' => '', 'directory' => 'custom', 'name' => 'Custom Form', 'nickname' => ''],
            ['directory' => 'other', 'name' => 'Other Form'],
        ]);

        self::assertSame(['Miscellaneous' => ['Custom Form', 'Other Form']], self::shape($menu));
    }

    public function testNicknameIsPreferredUnlessEmptyOrZero(): void
    {
        self::assertSame('Vitals Short', FormCategoryMenu::title(['name' => 'Vitals', 'nickname' => ' Vitals Short ']));
        self::assertSame('Vitals', FormCategoryMenu::title(['name' => 'Vitals', 'nickname' => '']));
        self::assertSame('Vitals', FormCategoryMenu::title(['name' => 'Vitals', 'nickname' => '0']));
        self::assertSame('Vitals', FormCategoryMenu::title(['name' => 'Vitals']));
        self::assertSame('', FormCategoryMenu::title([]));
    }

    public function testNoFormsGivesNoCategories(): void
    {
        self::assertSame([], self::build([]));
        self::assertSame([], self::build(['not a row']));
    }
}
