<?php

/**
 * MainMenuRole class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Listener;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Menu\MenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ModuleMenuSubscriber
 * @package Application\Listener
 *
 * Listens to OpenEMR menu events and adds menu items to the menu structure based upon which modules
 * have been installed through the Laminas Module system.
 *
 * This can be used as an example of how to use the OpenEMR event dispatcher and the module system to extend the
 * codebase without modifying the core OpenEMR files.  This facilitates easier upgrading and clean separations of concerns.
 */
class ModuleMenuSubscriber implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            MenuEvent::MENU_UPDATE => 'onMenuUpdate',
            MenuEvent::MENU_RESTRICT => 'onMenuRestrict'
        ];
    }

    /**
     * We inject our module system menu updates when the OpenEMR menu system has been updated.
     * In this case we add any modules that have registered as 'hook's to the Modules sub menu
     * and the reports sub menu.
     *
     * @param MenuEvent $menu
     * @return MenuEvent
     */
    public function onMenuUpdate(MenuEvent $menu)
    {
        $menuItems = $menu->getMenu();
        // we are working with objects so this will modify the objects in memory
        foreach ($menuItems as $menuItem) {
            // We don't use the label as the menu's have been translated at this point
            // We want to update the modules
            if ($menuItem->menu_id === 'modimg') {
                $this->updateModulesModulesMenu($menuItem);
            } elseif ($menuItem->menu_id === 'repimg') {
                $this->updateModulesReportsMenu($menuItem);
            }
        }
        return $menu;
    }

    /**
     * If we need to adjust anything in the menu's permissions for the modules
     * we can do that work here..
     */
    public function onMenuRestrict(MenuEvent $menu)
    {
        return $menu;
    }

    /**
     * Load modules created by modules system  This was originally in the core codebase but has been moved into the
     * module system as it really deals with module code.
     * @param $menu_list
     */
    private function updateModulesModulesMenu(&$menu_list)
    {
        // TODO: there's a lot of globals here.. these really need to be injected or extracted
        // out so we can test these things...
        $module_query = sqlStatement("select mod_id,mod_directory,mod_name,mod_nick_name,mod_relative_link,type from modules where mod_active = 1 AND sql_run= 1 order by mod_ui_order asc");
        if (sqlNumRows($module_query)) {
            while ($modulerow = sqlFetchArray($module_query)) {
                $module_hooks =  sqlStatement("SELECT msh.*,ms.obj_name,ms.menu_name,ms.path,m.mod_ui_name,m.type, m.mod_relative_link FROM modules_hooks_settings AS msh LEFT OUTER JOIN modules_settings AS ms ON
                                    obj_name=enabled_hooks AND ms.mod_id=msh.mod_id LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
                                    WHERE m.mod_id = ? AND fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='modules' ORDER BY m.mod_id", array($modulerow['mod_id']));

                $modulePath = "";
                $added      = "";
                if ($modulerow['type'] == 0) {
                    $modulePath = $GLOBALS['customModDir'];
                    $added      = "";
                } else {
                    $added      = "index";
                    $modulePath = $GLOBALS['zendModDir'];
                }

                $relative_link = "/interface/modules/" . $modulePath . "/" . $modulerow['mod_relative_link'] . $added;
                $mod_nick_name = $modulerow['mod_nick_name'] ? $modulerow['mod_nick_name'] : $modulerow['mod_name'];

                if (sqlNumRows($module_hooks) == 0) {
                    // module without hooks in module section
                    $acl_section = strtolower($modulerow['mod_directory']);
                    if (AclMain::zhAclCheck($_SESSION['authUserID'], $acl_section) ?  "" : "1") {
                        continue;
                    }

                    $newEntry = new \stdClass();
                    $newEntry->label = xlt($mod_nick_name);
                    $newEntry->url = $relative_link;
                    $newEntry->requirement = 0;
                    $newEntry->target = 'mod';
                    array_push($menu_list->children, $newEntry);
                } else {
                    // module with hooks in module section
                    $newEntry = new \stdClass();
                    $newEntry->requirement = 0;
                    $newEntry->icon = "fa-caret-right";
                    $newEntry->label = xlt($mod_nick_name);
                    $newEntry->children = array();
                    $jid = 0;
                    $modid = '';
                    while ($hookrow = sqlFetchArray($module_hooks)) {
                        if (AclMain::zhAclCheck($_SESSION['authUserID'], $hookrow['obj_name']) ?  "" : "1") {
                            continue;
                        }

                        $relative_link = "/interface/modules/" . $modulePath . "/" . $hookrow['mod_relative_link'] . $hookrow['path'];
                        $mod_nick_name = $hookrow['menu_name'] ? $hookrow['menu_name'] : 'NoName';

                        if ($jid == 0 || ($modid != $hookrow['mod_id'])) {
                            $subEntry = new \stdClass();
                            $subEntry->requirement = 0;
                            $subEntry->target = 'mod';
                            $subEntry->menu_id = 'mod0';
                            $subEntry->label = xlt($mod_nick_name);
                            $subEntry->url = $relative_link;
                            $newEntry->children[] = $subEntry;
                        }

                        $jid++;
                    }

                    array_push($menu_list->children, $newEntry);
                }
            }
        }
    }

    /**
     * load reports created by modules system
     * @param $menu_list  A tree of stdClass objects that represent a menu.
     */
    private function updateModulesReportsMenu(&$menu_list)
    {
        $module_query = sqlStatement("SELECT msh.*,ms.obj_name,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh LEFT OUTER JOIN modules_settings AS ms ON
                                    obj_name=enabled_hooks AND ms.mod_id=msh.mod_id LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
                                    WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='reports' ORDER BY mod_id");
        $reportsHooks = array();
        if (sqlNumRows($module_query)) {
            $jid = 0;
            $modid = '';

            while ($hookrow = sqlFetchArray($module_query)) {
                if ($hookrow['type'] == 0) {
                    $modulePath = $GLOBALS['customModDir'];
                    $added = "";
                } else {
                    $added = "index";
                    $modulePath = $GLOBALS['zendModDir'];
                }

                if ($jid == 0 || ($modid != $hookrow['mod_id'])) {
                    //create new label
                    $newEntry = new \stdClass();
                    $newEntry->requirement = 0;
                    $newEntry->icon = "fa-caret-right";
                    $newEntry->label = xlt($hookrow['mod_ui_name']);
                    $newEntry->children = array();

                    $reportsHooks[] = $newEntry;
                    array_unshift($menu_list->children, $newEntry);
                }

                if (AclMain::zhAclCheck($_SESSION['authUserID'], $hookrow['obj_name']) ?  "" : "1") {
                    continue;
                }

                $relative_link = "/interface/modules/" . $modulePath . "/" . $hookrow['mod_relative_link'] . $hookrow['path'];
                $mod_nick_name = $hookrow['menu_name'] ? $hookrow['menu_name'] : 'NoName';

                $subEntry = new \stdClass();
                $subEntry->requirement = 0;
                $subEntry->target = 'rep';
                $subEntry->menu_id = 'rep0';
                $subEntry->label = xlt($mod_nick_name);
                $subEntry->url = $relative_link;

                $reportsHooks[count($reportsHooks) - 1]->children[] = $subEntry;

                $jid++;
                $modid = $hookrow['mod_id'];
            }
        }
    }
}
