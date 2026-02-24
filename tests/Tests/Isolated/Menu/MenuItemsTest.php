<?php

/**
 * Isolated MenuItems Test
 *
 * Tests the MenuItems collection class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Menu;

use OpenEMR\Menu\BaseMenuItem;
use OpenEMR\Menu\MenuItemInterface;
use OpenEMR\Menu\MenuItems;
use PHPUnit\Framework\TestCase;

class MenuItemsTest extends TestCase
{
    public function testConstructorWithEmptyArray(): void
    {
        $items = new MenuItems([]);
        $this->assertCount(0, $items);
    }

    public function testConstructorWithValidMenuItems(): void
    {
        $menuItem = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test Item',
            'url' => '/test',
            'target' => '_self',
        ]);

        $items = new MenuItems([$menuItem]);
        $this->assertCount(1, $items);
    }

    public function testConstructorWithMultipleValidItems(): void
    {
        $item1 = new BaseMenuItem([
            'id' => 'item1',
            'displayText' => 'Item 1',
            'url' => '/item1',
            'target' => '_self',
        ]);
        $item2 = new BaseMenuItem([
            'id' => 'item2',
            'displayText' => 'Item 2',
            'url' => '/item2',
            'target' => '_self',
        ]);

        $items = new MenuItems([$item1, $item2]);
        $this->assertCount(2, $items);
    }

    public function testConstructorRejectsInvalidItem(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All MenuItems must implement MenuItemInterface');

        new MenuItems(['invalid string']);
    }

    public function testConstructorRejectsNonInterfaceObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All MenuItems must implement MenuItemInterface');

        new MenuItems([new \stdClass()]);
    }

    public function testOffsetSetWithValidItem(): void
    {
        $items = new MenuItems([]);
        $menuItem = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $items[] = $menuItem;
        $this->assertCount(1, $items);
    }

    public function testOffsetSetWithKeyAndValidItem(): void
    {
        $items = new MenuItems([]);
        $menuItem = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $items['myKey'] = $menuItem;
        $this->assertSame($menuItem, $items['myKey']);
    }

    public function testOffsetSetRejectsInvalidItem(): void
    {
        $items = new MenuItems([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All MenuItems must implement MenuItemInterface');

        $items[] = 'invalid';
    }

    public function testOffsetSetRejectsNonInterfaceObject(): void
    {
        $items = new MenuItems([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All MenuItems must implement MenuItemInterface');

        $items[] = new \stdClass();
    }

    public function testValidateEntryWithEmptyArray(): void
    {
        $this->expectNotToPerformAssertions();
        MenuItems::validateEntry([]);
    }

    public function testValidateEntryWithValidMenuItem(): void
    {
        $this->expectNotToPerformAssertions();
        $menuItem = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        MenuItems::validateEntry($menuItem);
    }

    public function testValidateEntryWithArrayOfValidItems(): void
    {
        $this->expectNotToPerformAssertions();
        $item1 = new BaseMenuItem([
            'id' => 'item1',
            'displayText' => 'Item 1',
            'url' => '/item1',
            'target' => '_self',
        ]);
        $item2 = new BaseMenuItem([
            'id' => 'item2',
            'displayText' => 'Item 2',
            'url' => '/item2',
            'target' => '_self',
        ]);

        MenuItems::validateEntry([$item1, $item2]);
    }

    public function testValidateEntryWithNonInterfaceObject(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All MenuItems must implement MenuItemInterface, stdClass found');

        MenuItems::validateEntry(new \stdClass());
    }

    public function testIterableCollection(): void
    {
        $item1 = new BaseMenuItem([
            'id' => 'item1',
            'displayText' => 'Item 1',
            'url' => '/item1',
            'target' => '_self',
        ]);
        $item2 = new BaseMenuItem([
            'id' => 'item2',
            'displayText' => 'Item 2',
            'url' => '/item2',
            'target' => '_self',
        ]);

        $items = new MenuItems([$item1, $item2]);
        $count = 0;

        foreach ($items as $item) {
            $this->assertNotNull($item);
            $count++;
        }

        $this->assertSame(2, $count);
    }
}
