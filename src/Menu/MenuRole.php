<?php

/**
 * MenuRole class.
 * (note this consolidated several libraries and maintained the author/copyright credits)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

require_once(dirname(__FILE__) . "/../../library/registry.inc");

use OpenEMR\Common\Acl\AclMain;

class MenuRole
{

    /**
     * Constructor
     */
    public function __construct()
    {
        // This is where the magic happens to support special menu items.
        //   An empty menu_update_map array is created in MenuRole class
        //   constructor. Adding to this array will link special menu items
        //   to functions in the class.
        $this->menu_update_map = array();
    }

    /**
     * Collect the Menu for logged in user.
     *
     * @return array representation of the Menu
     */
    public function getMenu()
    {
    }

    /**
     * Build the html select element to list the MenuRole options.
     *
     * @var string $selected Current MenuRole for current users.
     * @return string Html select element to list the MenuRole options.
     */
    public function displayMenuRoleSelector($selected = "")
    {
    }

    /**
     * Collect the MenuRole for logged in user.
     *
     * @return string Identifier for the MenuRole
     */
    private function getMenuRole()
    {
    }

    protected function menuUpdateEntries(&$menu_list)
    {
        for ($idx = 0; $idx < count($menu_list); $idx++) {
            $entry = $menu_list[$idx];
            if (!isset($entry->url)) {
                if (isset($this->menu_update_map[$entry->label])) {
                    $function_temp = $this->menu_update_map[$entry->label];
                    $this->$function_temp($entry);
                }
            }

            // Translate the labels
            $entry->label = xlt($entry->label);
            // Recursive update of children
            if (isset($entry->children)) {
                $this->menuUpdateEntries($entry->children);
            }
        }
    }

    protected function menuApplyRestrictions(&$menu_list_src, &$menu_list_updated)
    {
        for ($idx = 0; $idx < count($menu_list_src); $idx++) {
            $srcEntry = $menu_list_src[$idx];
            $includeEntry = true;

            // If the entry has an ACL Requirements(currently only support loose), then test
            if (!empty($srcEntry->acl_req)) {
                if (is_array($srcEntry->acl_req[0])) {
                    $noneSet = true;
                    for ($aclIdx = 0; $aclIdx < count($srcEntry->acl_req); $aclIdx++) {
                        if ($this->menuAclCheck($srcEntry->acl_req[$aclIdx])) {
                            $noneSet = false;
                        }
                    }

                    if ($noneSet) {
                        $includeEntry = false;
                    }
                } else {
                    if (!$this->menuAclCheck($srcEntry->acl_req)) {
                        $includeEntry = false;
                    }
                }
            }

            // If the entry has loose global setting requirements, check
            // Note that global_req is a loose check (if more than 1 global, only 1 needs to pass to show the menu item)
            if (!empty($srcEntry->global_req)) {
                if (is_array($srcEntry->global_req)) {
                    $noneSet = true;
                    for ($globalIdx = 0; $globalIdx < count($srcEntry->global_req); $globalIdx++) {
                        $curSetting = $srcEntry->global_req[$globalIdx];
                        // ! at the start of the string means test the negation
                        if (substr($curSetting, 0, 1) === '!') {
                            $curSetting = substr($curSetting, 1);
                            // If the global isn't set at all, or if it is false, then show it
                            if (!isset($GLOBALS[$curSetting]) || !$GLOBALS[$curSetting]) {
                                $noneSet = false;
                            }
                        } else {
                            // If the setting is both set and true, then show it
                            if (isset($GLOBALS[$curSetting]) && $GLOBALS[$curSetting]) {
                                $noneSet = false;
                            }
                        }
                    }

                    if ($noneSet) {
                        $includeEntry = false;
                    }
                } else {
                    // ! at the start of the string means test the negation
                    if (substr($srcEntry->global_req, 0, 1) === '!') {
                        $globalSetting = substr($srcEntry->global_req, 1);
                        // If the setting is both set and true, then skip this entry
                        if (isset($GLOBALS[$globalSetting]) && $GLOBALS[$globalSetting]) {
                            $includeEntry = false;
                        }
                    } else {
                        // If the global isn't set at all, or if it is false then skip the entry
                        if (!isset($GLOBALS[$srcEntry->global_req]) || !$GLOBALS[$srcEntry->global_req]) {
                            $includeEntry = false;
                        }
                    }
                }
            }

            // If the entry has strict global setting requirements, check
            // Note that global_req_strict is a strict check (if more than 1 global, they all need to pass to show the menu item)
            if (!empty($srcEntry->global_req_strict)) {
                if (is_array($srcEntry->global_req_strict)) {
                    $allSet = true;
                    for ($globalIdx = 0; $globalIdx < count($srcEntry->global_req_strict); $globalIdx++) {
                        $curSetting = $srcEntry->global_req_strict[$globalIdx];
                        // ! at the start of the string means test the negation
                        if (substr($curSetting, 0, 1) === '!') {
                            $curSetting = substr($curSetting, 1);
                            // If the setting is both set and true, then do not show it
                            if (isset($GLOBALS[$curSetting]) && $GLOBALS[$curSetting]) {
                                $allSet = false;
                            }
                        } else {
                            // If the global isn't set at all, or if it is false, then do not show it
                            if (!isset($GLOBALS[$curSetting]) || !$GLOBALS[$curSetting]) {
                                $allSet = false;
                            }
                        }
                    }

                    if (!$allSet) {
                        $includeEntry = false;
                    }
                } else {
                    // ! at the start of the string means test the negation
                    if (substr($srcEntry->global_req_strict, 0, 1) === '!') {
                        $globalSetting = substr($srcEntry->global_req_strict, 1);
                        // If the setting is both set and true, then skip this entry
                        if (isset($GLOBALS[$globalSetting]) && $GLOBALS[$globalSetting]) {
                            $includeEntry = false;
                        }
                    } else {
                        // If the global isn't set at all, or if it is false then skip the entry
                        if (!isset($GLOBALS[$srcEntry->global_req_strict]) || !$GLOBALS[$srcEntry->global_req_strict]) {
                            $includeEntry = false;
                        }
                    }
                }
            }

            if (isset($srcEntry->children)) {
                // Iterate through and check the child elements
                $checked_children = array();
                $this->menuApplyRestrictions($srcEntry->children, $checked_children);
                $srcEntry->children = $checked_children;
            }

            if (!isset($srcEntry->url)) {
                // If this is a header only entry, and there are no child elements, then don't include it in the list.
                if (count($srcEntry->children) === 0) {
                    $includeEntry = false;
                }
            }

            if ($includeEntry) {
                array_push($menu_list_updated, $srcEntry);
            }
        }
    }

    // Permissions check for a particular acl_req array item.
    // Elements beyond the 2nd are ACL return values, one of which should be permitted.
    //
    private function menuAclCheck($arr)
    {
        if (isset($arr[2])) {
            for ($i = 2; isset($arr[$i]); ++$i) {
                if (substr($arr[0], 0, 1) == '!') {
                    if (!AclMain::aclCheckCore(substr($arr[0], 1), $arr[1], '', $arr[$i])) {
                        return true;
                    }
                } else {
                    if (AclMain::aclCheckCore($arr[0], $arr[1], '', $arr[$i])) {
                        return true;
                    }
                }
            }
        } else {
            if (substr($arr[0], 0, 1) == '!') {
                if (!AclMain::aclCheckCore(substr($arr[0], 1), $arr[1])) {
                    return true;
                }
            } else {
                if (AclMain::aclCheckCore($arr[0], $arr[1])) {
                    return true;
                }
            }
        }

        return false;
    }
}
