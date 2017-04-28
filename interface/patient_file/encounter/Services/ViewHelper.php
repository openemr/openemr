<?php
/**
 * Helper functions for an encounter view files
 *
 * LICENSE: This program is free software; you can redistribute it and/or modify it under the terms of the GNU General
 * Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option)
 * any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public
 * License for more details. You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @subpackage Encounter
 * @license GNU GPL3
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (C) 2017 Robert Down
 */

namespace OpenEMR\Encounter\Services;

class ViewHelper
{

    private $oldCategory = '';

    static function getRegistry($state = "1", $limit = "unlimited", $offset = "0")
    {
        global $attendant_type;
        $sql = "SELECT * FROM registry WHERE {column}= 1 AND state LIKE ? ORDER BY category, priority, name";
        $params = array();
        $column = ($attendant_type == 'pid') ? 'patient_encounter' : 'therapy_group_encounter';
        $sql = str_replace("{column}", $column, $sql);
        $params[1] = $state;
        if ($limit != "unlimited") {
            $limit = escape_limit($limit);
            $offset = escape_limit($offset);
            $sql .= " LIMIT {$limit}, {$offset}";
        }
        $result = sqlStatement($sql, $params);
        if ($result) {
            $all = array();
            while ($row = sqlFetchArray($result)) {
                array_push($all, $row);
            }
            return $all;
        } else {
            return false;
        }
    }

    /**
     * Create a bootstrap-based navbar based on array
     *
     * $elements = array(
     *     'name' => 'Text displayed to user in link. Required',
     *     'href' => 'Location of menu item. Required',
     *     'atts' => ['Array of valid HTML5 attributes to attach to the anchor tag'],
     *     'linkClass' => 'Space separated string of extra classes to add to <a> element. Optional',
     *     'listItemClass' => 'Space separated string of extra classes to add to <li> element. Optional',
     *     'subItems' => array('Recursion of $elements array structure. Optional'));
     *
     * @todo This does not actually work, the links are not yet hooked up. RD 2017-04-19
     * @todo Handling the subItems list could be better - needs to be truly recursive. RD 2017-04-23
     *
     * @param $elements array
     * @return string
     */
    static function createEncounterMenu($elements)
    {
        // Standard menu item with no dropdown
        $menuListItem = '<li><a href="{href}" {class} {atts} >{linkText}</a></li>';

        $submenuListItem = '<li><a href="{href}" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{linkText}</a>';

        // Standard menu item dropdown support
        $menuListItemWithDropdown = '<li class="dropdown"><a href="{href}" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{linkText}&nbsp;<i class="fa fa-chevron-down"></i></a>{submenuList}</li>';

        // Dropdown menu
        $submenuList = '<ul class="dropdown-menu">{submenuListItems}</ul>';

        $menu = "";
        foreach ($elements as $group) {
            if (array_key_exists('subItems', $group)) {
                // We have a submenu
                $submenu = array();
                foreach ($group['subItems'] as $subItem) {
                    $submenuTmpStr = str_replace("{linkText}", $subItem['name'], $menuListItem);
                    $submenuTmpStr = str_replace("{href}", $subItem['href'], $submenuTmpStr);

                    $attsString = "";
                    if (array_key_exists('atts', $subItem)) {
                        foreach ($subItem['atts'] as $prop => $val) {
                            $attsString .= " {$prop}=\"{$val}\"";
                        }
                    }
                    $submenuTmpStr = str_replace("{atts}", $attsString, $submenuTmpStr);

                    $classTmp = "";
                    if (array_key_exists('class', $subItem)) {
                        $classTmp = ' class="{$subItem[class]}" ';
                    }
                    $submenuTmpStr = str_replace("{class}", $classTmp, $submenuTmpStr);

                    array_push($submenu, $submenuTmpStr);
                }

                $submenuStr = implode("\n", $submenu);
                $submenuContainer = str_replace("{submenuListItems}", $submenuStr, $submenuList);
                $elementContainer = str_replace("{submenuList}", $submenuContainer, $menuListItemWithDropdown);
                $elementContainer = str_replace("{linkText}", $group['name'], $elementContainer);

                if (array_key_exists('href', $group)) {
                    $elementContainer = str_replace("{href}", $group['href'], $elementContainer);
                }

                $attsString = "";
                if (array_key_exists('atts', $group)) {
                    foreach ($group['atts'] as $prop => $val) {
                        $attsString .= " {$prop}=\"{$val}\"";
                    }
                }
                $elementContainer = str_replace("{atts}", $attsString, $elementContainer);

                $menu = $menu . "\n" . $elementContainer;
            } else {
                $attsString = "";
                if (array_key_exists('atts', $group)) {
                    foreach ($group['atts'] as $prop => $val) {
                        $attsString .= " {$prop}=\"{$val}\"";
                    }
                }
                $elementContainer = str_replace("{atts}", $attsString, $menuListItem);

                $classTmp = "";
                if (array_key_exists('class', $group)) {
                    $classTmp = " class='{$group['class']}' ";
                }
                $elementContainer = str_replace("{class}", $classTmp, $elementContainer);

                $elementContainer = str_replace("{href}", $group['href'], $elementContainer);

                $menu = $menu . str_replace("{linkText}", $group['name'], $elementContainer);
            }
        }
        return $menu;
    }

    /**
     * Create an array of elements based on layout based forms
     *
     * Similar to parseRegistry, create an array that can be processed by createEncounterMenu() to show a link to LBFs
     *
     * @return array|bool
     */
    static function getLayoutBasedForms()
    {
        $sql = "SELECT * FROM list_options WHERE list_id = 'lbfnames' AND activity = 1 ORDER BY seq, title";
        $result = sqlStatement($sql);
        $return = array();
        if (sqlNumRows($result)) {
            while ($row = sqlFetchArray($result)) {
                $optionId = $row['option_id'];
                $encodedOptionId = urlencode($optionId);
                $title = $row['title'];
                $jobj = json_decode($row['notes'], true);
                if (!empty($jobj['aco'])) {
                    $tmp = explode('|', $jobj['aco']);
                    if (!acl_check($tmp[0], $tmp[1], '', 'write') && !acl_check($tmp[0], $tmp[1], '', 'addonly')) {
                        continue;
                    }
                }
                $row = [
                    'href' => "/interface/patient_file/encounter/load_form.php?formname={$encodedOptionId}",
                    'name' => xl_form_title($title),
                    'class' => 'menu-item-action'
                ];
                $return[] = $row;
            }
            $name = xlt('Layout Based');
            return array('name' => $name, 'href' => '#', 'subItems' => $return);
        } else {
            return false;
        }
    }

    static function parseRegistry($registry, $oldCategory = '')
    {
        global $old_category;
        $prevCategory = '';
        $return = array();
        foreach ($registry as $item) {
            $tmp = explode('|', $item['aco_spec']);
            // Check permission to create forms of this type.
            if (count($tmp) > 1) {
                if (!acl_check($tmp[0], $tmp[1], '', 'write') && !acl_check($tmp[0], $tmp[1], '', 'addonly')) {
                    continue;
                }
            }
            $category = (trim($item['category']) == '') ? xlt("Miscellaneous") : xlt(trim($item['category']));
            $nickname = (trim($item['nickname']) == '') ? $item['name'] : $item['nickname'];

            if ($category == $prevCategory) {
                $formName = urlencode($item['directory']);
                $rootDir = "/interface";
                $tmp = [
                    'href' => "{$rootDir}/patient_file/encounter/load_form.php?formname={$formName}",
                    'name' => xl_form_title($nickname),
                    'class' => 'menu-item-action'
                ];
                $return[count($return) - 1]['subItems'][] = $tmp;
            } else {
                $return[] = [
                    'name' => $category,
                    'href' => '#'
                ];
            }

            $prevCategory = $category;
        }

        return $return;
    }

    static function getModuleMenuItems()
    {
        $sql = "SELECT msh.*, ms.menu_name, ms.path, m.mod_ui_name, m.type
            FROM modules_hooks_settings AS msh 
            LEFT OUTER JOIN modules_settings AS ms ON obj_name=enabled_hooks AND ms.mod_id=msh.mod_id
            LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
            WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='encounter'
            ORDER BY mod_id";

        $result = sqlStatement($sql);
        $return = array();
        $modId = "";
        while ($row = sqlFetchArray($result)) {
            $lastItem = count($return) - 1;
            $tmpRow['name'] = $row['mod_ui_name'];

            $path = $GLOBALS['customModDir'];
            $added = "";
            if ($row['type'] != 0) {
                $path = $GLOBALS['zendModDir'];
                $added = "index";
            }

            $href = "../../modules/{$path}/{$row['path']}";
            $nickname = $row['menu_name'] ? $row['menu_name'] : 'Noname';

            if ($modId == $row['mod_id']) {
                // Subitem
                if (!array_key_exists('subItems', $return[$lastItem])) {
                    $return[$lastItem]['subItems'] = array();
                }
                $tmpRow['href'] = $href;
                $return[$lastItem]['subItems'][] = $tmpRow;
            } else {
                $tmpRow['href'] = "#";
                $return[] = $tmpRow;
            }
        }
    }

}
