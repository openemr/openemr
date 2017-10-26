<?php
/**
 * OpenEMR (http://open-emr.org)
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OpenEMR\Encounter\Services;

/**
 * Helper functions for an encounter view files.
 *
 * @package OpenEMR\Encounter\Services
 * @subpackage Encounter
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down
 */
class ViewHelper
{

    /**
     * Get standard encounter menu items from DB.
     *
     * @param string $state Limit SQL based on this value in the LIKE clause
     * @param string $limit Limit results to this number of results
     * @param string $offset Offset the results by this number
     * @return array|bool
     */
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
     * Create a bootstrap-based navbar list based on array.
     *
     * ```php
     * $elements = array(
     *     'name' => 'Text displayed to user in link. Required',
     *     'href' => 'Location of menu item. Required',
     *     'atts' => ['Array of valid HTML5 attributes to attach to the anchor tag'],
     *     'linkClass' => 'Space separated string of extra classes to add to <a> element. Optional',
     *     'listItemClass' => 'Space separated string of extra classes to add to <li> element. Optional',
     *     'subItems' => ['Recursion of $elements array structure. Optional'];
     * ```
     *
     * @todo Handling the subItems list could be better - needs to be truly recursive. RD 2017-04-23
     * @todo Eventually would be good to break out the templating to a twig template. RD 2017-05-02
     *
     * @param array $elements
     * @return string
     */
    static function createEncounterMenu(array $elements)
    {
        // Standard menu item with no dropdown
        $menuListItem = '<li><a href="{href}" {class} {atts} ><i class="fa fa-fw"></i>&nbsp;{linkText}</a></li>';

        $submenuListItem = '<li><a href="{href}" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">{linkText}</a>';

        // Standard menu item dropdown support
        $iconChevronDirection = $_SESSION['language_direction'] == 'rtl' ? 'fa-chevron-left' : 'fa-chevron-right';
        $menuListItemWithDropdown = '<li class="dropdown"><a href="{href}" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false"><i class="fa fa-fw ' . $iconChevronDirection .'"></i>&nbsp;{linkText}</a>{submenuList}</li>';

        // Dropdown menu
        $submenuList = '<ul class="nav navbar-stacked hidden submenu">{submenuListItems}</ul>';

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

                $menu = $menu . str_replace("{linkText}", xlt($group['name']), $elementContainer);
            }
        }

        return $menu;
    }

    /**
     * Create an array of elements based on layout based forms
     *
     * Similar to parseRegistry, create an array that can be processed by
     * createEncounterMenu() to show a link to LBFs
     *
     * @return array|bool
     */
    static function getLayoutBasedForms()
    {
        $sql = "SELECT grp_form_id AS option_id, grp_title AS title, grp_aco_spec " .
        "FROM layout_group_properties WHERE " .
        "grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_activity = 1 " .
        "ORDER BY grp_seq, grp_title";

        $result = sqlStatement($sql);
        $return = array();
        if (sqlNumRows($result)) {
            while ($row = sqlFetchArray($result)) {
                $optionId = $row['option_id'];
                $encodedOptionId = urlencode($optionId);
                $title = $row['title'];
                if (!empty($row['grp_aco_spec'])) {
                    $tmp = explode('|', $row['grp_aco_spec']);
                    if (!acl_check($tmp[0], $tmp[1], '', 'write') && !acl_check($tmp[0], $tmp[1], '', 'addonly')) {
                        continue;
                    }
                }
                $row = [
                    'href' => "{$GLOBALS['rootdir']}/patient_file/encounter/load_form.php?formname={$encodedOptionId}",
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

    /**
     * Turn getRegistry() into a createEncounterMenu() compatible array
     *
     * @param array $registry
     * @param string $oldCategory
     * @return array
     */
    static function parseRegistry(array $registry, $oldCategory = '')
    {
        $prevCategory = 'null';
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

            $formName = urlencode($item['directory']);
            $rootDir = "/interface";
            $tmp = [
                'href' => "{$GLOBALS['rootdir']}/patient_file/encounter/load_form.php?formname={$formName}",
                'name' => xl_form_title($nickname),
                'class' => 'menu-item-action',
            ];

            $previousElement = (count($return) == 0) ? 0 : count($return) - 1;

            if ($category == $prevCategory) {
                $return[$previousElement]['subItems'][] = $tmp;
            } else {
                $return[] = [
                    'name' => $category,
                    'href' => '#',
                    'subItems' => [
                        $tmp
                    ],
                ];
            }

            $prevCategory = $category;
        }

        return $return;
    }

    /**
     * Create an array of module menu items
     *
     * Similar to parseRegistry, create an array that can be processed by
     * createEncounterMenu() to show a link to modules
     *
     * @return array|bool
     */
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
        if (sqlNumRows($result) > 0) {
            while ($row = sqlFetchArray($result)) {
                $lastItem = count($return) - 1;
                $tmpRow['name'] = $row['mod_ui_name'];

                $path = $GLOBALS['customModDir'];
                $added = "";
                if ($row['type'] != 0) {
                    $path = $GLOBALS['zendModDir'];
                    $added = "index";
                }

                $href = "{$GLOBALS['rootdir']}/modules/{$path}/{$row['path']}";
                $nickname = $row['menu_name'] ? $row['menu_name'] : 'Noname';

                if ($modId == $row['mod_id']) {
                    // Subitem
                    if (!array_key_exists('subItems', $return[$lastItem])) {
                        $return[$lastItem]['subItems'] = array();
                    }

                    $tmpRow['href'] = $href;
                    $return[$lastItem]['subItems'][] = $tmpRow;
                } else {
                    $tmpRow['href'] = $href;
                    $return[] = $tmpRow;
                }
            }

            $name = xlt("Other");
            return array('name' => $name, 'href' => '#', 'subItems' => $return);
        } else {
            return false;
        }
    }
}
