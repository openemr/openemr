<?php

/**
 * The Modules menu opens for users allowed to manage modules.
 *
 * Regression test for issue #13414. The "Modules" top-level entry of the standard
 * menu required only menus/modle, so a role granted Administration > Manage Modules
 * (admin/manage_modules, the ACL the "Manage Modules" child and the Installer
 * controller check) never saw the menu that holds its only page. MenuRole shows an
 * entry when any of the alternatives in a list-of-lists acl_req passes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Menu;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StandardMenuModulesAclTest extends TestCase
{
    /**
     * Users who pass either the Modules menu ACL or the Manage Modules ACL see the menu.
     */
    #[Test]
    public function modulesMenuAcceptsTheManageModulesAcl(): void
    {
        $modules = self::entry(self::standardMenu(), 'modimg');

        self::assertSame([['menus', 'modle'], ['admin', 'manage_modules']], $modules['acl_req'] ?? null);
    }

    /**
     * The Manage Modules page itself keeps requiring admin/manage_modules.
     */
    #[Test]
    public function manageModulesKeepsItsOwnAcl(): void
    {
        $children = self::entry(self::standardMenu(), 'modimg')['children'] ?? null;
        self::assertIsArray($children);
        $manageModules = self::entry($children, 'adm0');

        self::assertSame(['admin', 'manage_modules'], $manageModules['acl_req'] ?? null);
    }

    /**
     * @return list<mixed>
     */
    private static function standardMenu(): array
    {
        $json = file_get_contents(dirname(__DIR__, 4) . '/interface/main/tabs/menu/menus/standard.json');
        self::assertIsString($json);
        $menu = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsList($menu);
        return $menu;
    }

    /**
     * @param array<mixed> $entries
     * @return array<mixed>
     */
    private static function entry(array $entries, string $menuId): array
    {
        foreach ($entries as $entry) {
            if (is_array($entry) && ($entry['menu_id'] ?? null) === $menuId) {
                return $entry;
            }
        }
        self::fail("menu entry $menuId not found");
    }
}
