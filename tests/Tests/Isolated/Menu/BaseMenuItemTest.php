<?php

/**
 * Isolated BaseMenuItem Test
 *
 * Tests the BaseMenuItem class.
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

class BaseMenuItemTest extends TestCase
{
    public function testConstructorHydratesProperties(): void
    {
        $item = new BaseMenuItem([
            'id' => 'menu-item-1',
            'displayText' => 'Dashboard',
            'url' => '/dashboard',
            'target' => '_blank',
        ]);

        $this->assertSame('menu-item-1', $item->getId());
        $this->assertSame('Dashboard', $item->getDisplayText());
        $this->assertSame('/dashboard', $item->getUrl());
        $this->assertSame('_blank', $item->getTarget());
    }

    public function testConstructorIgnoresUnknownProperties(): void
    {
        // Should not throw even with unknown properties
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'unknownProperty' => 'value',
        ]);

        $this->assertSame('test', $item->getId());
    }

    public function testGetChildrenReturnsEmptyCollectionWhenNotSet(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $children = $item->getChildren();
        $this->assertCount(0, $children);
    }

    public function testGetChildrenReturnsProvidedChildren(): void
    {
        $childItem = new BaseMenuItem([
            'id' => 'child',
            'displayText' => 'Child Item',
            'url' => '/child',
            'target' => '_self',
        ]);

        $children = new MenuItems([$childItem]);

        $item = new BaseMenuItem([
            'id' => 'parent',
            'displayText' => 'Parent',
            'url' => '/parent',
            'target' => '_self',
            'children' => $children,
        ]);

        $this->assertSame($children, $item->getChildren());
        $this->assertCount(1, $item->getChildren());
    }

    public function testGetRequirementsDefaultsToZero(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame(0, $item->getRequirements());
    }

    public function testGetRequirementsReturnsSetValue(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'requirements' => 5,
        ]);

        $this->assertSame(5, $item->getRequirements());
    }

    public function testGetAclDefaultsToEmptyArray(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame([], $item->getAcl());
    }

    public function testGetAclReturnsSetValue(): void
    {
        $acl = ['admin', 'super'];
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'acl' => $acl,
        ]);

        $this->assertSame($acl, $item->getAcl());
    }

    public function testGetGlobalReqStrictDefaultsToEmptyArray(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame([], $item->getGlobalReqStrict());
    }

    public function testGetGlobalReqStrictReturnsSetValue(): void
    {
        $globalReqStrict = ['feature_enabled' => true];
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'globalReqStrict' => $globalReqStrict,
        ]);

        $this->assertSame($globalReqStrict, $item->getGlobalReqStrict());
    }

    public function testGetGlobalReqDefaultsToEmptyArray(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame([], $item->getGlobalReq());
    }

    public function testGetGlobalReqReturnsArrayValue(): void
    {
        $globalReq = ['setting1', 'setting2'];
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'globalReq' => $globalReq,
        ]);

        $this->assertSame($globalReq, $item->getGlobalReq());
    }

    public function testGetGlobalReqReturnsStringValue(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'globalReq' => 'single_requirement',
        ]);

        $this->assertSame('single_requirement', $item->getGlobalReq());
    }

    public function testGetPreTextContentDefaultsToEmptyString(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame('', $item->getPreTextContent());
    }

    public function testGetPreTextContentReturnsSetValue(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'preTextContent' => '<i class="icon"></i>',
        ]);

        $this->assertSame('<i class="icon"></i>', $item->getPreTextContent());
    }

    public function testGetPostTextContentDefaultsToEmptyString(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame('', $item->getPostTextContent());
    }

    public function testGetPostTextContentReturnsSetValue(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'postTextContent' => '<span class="badge">New</span>',
        ]);

        $this->assertSame('<span class="badge">New</span>', $item->getPostTextContent());
    }

    public function testGetLinkClassListDefaultsToEmptyArray(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame([], $item->getLinkClassList());
    }

    public function testGetLinkClassListReturnsSetValue(): void
    {
        $classes = ['nav-link', 'active'];
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'linkClassList' => $classes,
        ]);

        $this->assertSame($classes, $item->getLinkClassList());
    }

    public function testGetLinkContainerClassListDefaultsToEmptyArray(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame([], $item->getLinkContainerClassList());
    }

    public function testGetLinkContainerClassListReturnsSetValue(): void
    {
        $classes = ['nav-item', 'dropdown'];
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'linkContainerClassList' => $classes,
        ]);

        $this->assertSame($classes, $item->getLinkContainerClassList());
    }

    public function testGetAttributesDefaultsToEmptyArray(): void
    {
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
        ]);

        $this->assertSame([], $item->getAttributes());
    }

    public function testGetAttributesReturnsSetValue(): void
    {
        $attributes = ['data-toggle' => 'modal', 'data-target' => '#myModal'];
        $item = new BaseMenuItem([
            'id' => 'test',
            'displayText' => 'Test',
            'url' => '/test',
            'target' => '_self',
            'attributes' => $attributes,
        ]);

        $this->assertSame($attributes, $item->getAttributes());
    }

    public function testCompleteMenuItemConfiguration(): void
    {
        $childItem = new BaseMenuItem([
            'id' => 'child',
            'displayText' => 'Child',
            'url' => '/child',
            'target' => '_self',
        ]);

        $item = new BaseMenuItem([
            'id' => 'main-menu',
            'displayText' => 'Main Menu',
            'url' => '/main',
            'target' => '_blank',
            'children' => new MenuItems([$childItem]),
            'requirements' => 3,
            'acl' => ['admin', 'docs'],
            'globalReqStrict' => ['strict_mode' => true],
            'globalReq' => ['global_setting'],
            'preTextContent' => '<i class="fa fa-home"></i>',
            'postTextContent' => '<span class="count">5</span>',
            'linkClassList' => ['nav-link'],
            'linkContainerClassList' => ['nav-item'],
            'attributes' => ['data-id' => '123'],
        ]);

        $this->assertSame('main-menu', $item->getId());
        $this->assertSame('Main Menu', $item->getDisplayText());
        $this->assertSame('/main', $item->getUrl());
        $this->assertSame('_blank', $item->getTarget());
        $this->assertCount(1, $item->getChildren());
        $this->assertSame(3, $item->getRequirements());
        $this->assertSame(['admin', 'docs'], $item->getAcl());
        $this->assertSame(['strict_mode' => true], $item->getGlobalReqStrict());
        $this->assertSame(['global_setting'], $item->getGlobalReq());
        $this->assertSame('<i class="fa fa-home"></i>', $item->getPreTextContent());
        $this->assertSame('<span class="count">5</span>', $item->getPostTextContent());
        $this->assertSame(['nav-link'], $item->getLinkClassList());
        $this->assertSame(['nav-item'], $item->getLinkContainerClassList());
        $this->assertSame(['data-id' => '123'], $item->getAttributes());
    }
}
