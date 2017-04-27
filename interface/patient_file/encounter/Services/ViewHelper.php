<?php
/**
 * File level docblock stuff
 *
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
            $params[2] = $limit;
            $params[3] = $offset;
            $sql .= " LIMIT ?, ?";
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
        $menuListItem = '<li><a href="{href}">{linkText}</a></li>';

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
                    array_push($submenu, $submenuTmpStr);
                }
                $submenuStr = implode("\n", $submenu);
                $submenuContainer = str_replace("{submenuListItems}", $submenuStr, $submenuList);
                $elementContainer = str_replace("{submenuList}", $submenuContainer, $menuListItemWithDropdown);
                $elementContainer = str_replace("{linkText}", $group['name'], $elementContainer);
                $menu = $menu . "\n" . $elementContainer;
            } else {
                $menu = $menu . str_replace("{linkText}", $group['name'], $menuListItem);
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
                $title = $row['title'];
                $jobj = json_decode($row['notes'], true);
                if (!empty($jobj['aco'])) {
                    $tmp = explode('|', $jobj['aco']);
                    if (!acl_check($tmp[0], $tmp[1], '', 'write') && !acl_check($tmp[0], $tmp[1], '', 'addonly')) {
                        continue;
                    }
                }
                $row = array(
                    'href' => '{rootdir}/patient_file/encounter/load_form.php?formname=' . urlencode($optionId),
                    'name' => xl_form_title($title),
                    'class' => 'lbf-menu-item'
                );
                $return[] = $row;
            }
            $name = xlt('Layout Based');
            return array('name' => $name, 'subItems' => $return);
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
                $tmp = array(
                    'href' => '#',
                    'name' => $nickname,
                );
                $return[count($return) - 1]['subItems'][] = $tmp;
            } else {
                $return[] = array('name' => $category);
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
