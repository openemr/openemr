<?php
/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 * Copyright (C) 2017 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://www.open-emr.org
 */

include_once("$srcdir/registry.inc");

$menu_update_map=array();
$menu_update_map["Visit Forms"]="update_visit_forms";
$menu_update_map["Modules"]="update_modules_menu";
$menu_update_map["Create Visit"] = "update_create_visit";

function update_modules_menu(&$menu_list)
{
    $module_query = sqlStatement("select mod_id,mod_directory,mod_name,mod_nick_name,mod_relative_link,type from modules where mod_active = 1 AND sql_run= 1 order by mod_ui_order asc");
    if (sqlNumRows($module_query)) {
        while ($modulerow = sqlFetchArray($module_query)) {

            $module_hooks =  sqlStatement("SELECT msh.*,ms.obj_name,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh LEFT OUTER JOIN modules_settings AS ms ON
                                    obj_name=enabled_hooks AND ms.mod_id=msh.mod_id LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
                                    WHERE m.mod_id = ? AND fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='modules' ORDER BY m.mod_id",array($modulerow['mod_id']));

            $modulePath = "";
            $added 		= "";
            if($modulerow['type'] == 0) {
                $modulePath = $GLOBALS['customModDir'];
                $added		= "";
            }
            else{
                $added		= "index";
                $modulePath = $GLOBALS['zendModDir'];
            }
            $relative_link ="/interface/modules/".$modulePath."/".$modulerow['mod_relative_link'].$added;
            $mod_nick_name = $modulerow['mod_nick_name'] ? $modulerow['mod_nick_name'] : $modulerow['mod_name'];

            if (sqlNumRows($module_hooks) == 0) {
                // module without hooks in module section
                $acl_section = strtolower($modulerow['mod_directory']);
                if (zh_acl_check($_SESSION['authUserID'],$acl_section) ?  "" : "1")continue;
                $newEntry=new stdClass();
                $newEntry->label=xlt($mod_nick_name);
                $newEntry->url=$relative_link;
                $newEntry->requirement=0;
                $newEntry->target='mod';
                array_push($menu_list->children,$newEntry);
            } else {
                // module with hooks in module section
                $newEntry=new stdClass();
                $newEntry->requirement=0;
                $newEntry->icon="fa-caret-right";
                $newEntry->label=xlt($mod_nick_name);
                $newEntry->children=array();
                $jid = 0;
                $modid = '';
                while ($hookrow = sqlFetchArray($module_hooks)) {
                    if (zh_acl_check($_SESSION['authUserID'],$hookrow['obj_name']) ?  "" : "1")continue;

                    $relative_link ="/interface/modules/".$modulePath."/".$hookrow['mod_relative_link'].$hookrow['path'];
                    $mod_nick_name = $hookrow['menu_name'] ? $hookrow['menu_name'] : 'NoName';

                    if($jid==0 || ($modid!=$hookrow['mod_id'])){

                        $subEntry=new stdClass();
                        $subEntry->requirement=0;
                        $subEntry->target='mod';
                        $subEntry->menu_id='mod0';
                        $subEntry->label=xlt($mod_nick_name);
                        $subEntry->url=$relative_link;
                        $newEntry->children[] = $subEntry;
                    }
                    $jid++;
                }
                array_push($menu_list->children,$newEntry);
            }
       }
    }
}

// This creates menu entries for all encounter forms.
//
function update_visit_forms(&$menu_list) {
  $baseURL = "/interface/patient_file/encounter/load_form.php?formname=";
  $menu_list->children = array();
  // LBF Visit forms
  $lres = sqlStatement("SELECT * FROM list_options " .
    "WHERE list_id = 'lbfnames' AND activity = 1 ORDER BY seq, title");
  while ($lrow = sqlFetchArray($lres)) {
    $option_id = $lrow['option_id']; // should start with LBF
    $title = $lrow['title'];
    $formURL = $baseURL . urlencode($option_id);
    $formEntry = new stdClass();
    $formEntry->label = xl_form_title($title);
    $formEntry->url = $formURL;
    $formEntry->requirement = 2;
    $formEntry->target = 'enc';
    // Plug in ACO attribute, if any, of this LBF.
    $jobj = json_decode($lrow['notes'], true);
    if (!empty($jobj['aco'])) {
      $tmp = explode('|', $jobj['aco']);
      if (!empty($tmp[1])) {
        $formEntry->acl_req = array($tmp[0], $tmp[1], 'write', 'addonly');
      }
    }
    array_push($menu_list->children, $formEntry);
  }
  // Traditional forms
  $reg = getRegistered();
  if (!empty($reg)) {
    foreach ($reg as $entry) {
      $option_id = $entry['directory'];
      $title = trim($entry['nickname']);
      if ($option_id == 'fee_sheet' ) continue;
      if ($option_id == 'newpatient') continue;
      if (empty($title)) $title = $entry['name'];
      $formURL = $baseURL . urlencode($option_id);
      $formEntry = new stdClass();
      $formEntry->label = xl_form_title($title);
      $formEntry->url = $formURL;
      $formEntry->requirement = 2;
      $formEntry->target = 'enc';
      // Plug in ACO attribute, if any, of this form.
      $tmp = explode('|', $entry['aco_spec']);
      if (!empty($tmp[1])) {
        $formEntry->acl_req = array($tmp[0], $tmp[1], 'write', 'addonly');
      }
      array_push($menu_list->children, $formEntry);
    }
  }
}

function update_create_visit(&$menu_list) {
  $tmp = getRegistryEntryByDirectory('newpatient', 'aco_spec');
  if (!empty($tmp['aco_spec'])) {
    $tmp = explode('|', $tmp['aco_spec']);
    $menu_list->acl_req = array($tmp[0], $tmp[1], 'write', 'addonly');
  }
}

function menu_update_entries(&$menu_list)
{
    global $menu_update_map;
    for($idx=0;$idx<count($menu_list);$idx++)
    {

        $entry = $menu_list[$idx];
        if(!isset($entry->url))
        {
            if(isset($menu_update_map[$entry->label]))
            {
                $menu_update_map[$entry->label]($entry);
            }
        }
        // Translate the labels
        $entry->label=xlt($entry->label);
        // Recursive update of children
        if(isset($entry->children))
        {
            menu_update_entries($entry->children);
        }
    }
}

// Permissions check for a particular acl_req array item.
// Elements beyond the 2nd are ACL return values, one of which should be permitted.
//
function menu_acl_check($arr) {
  if (isset($arr[2])) {
    for ($i = 2; isset($arr[$i]); ++$i) {
      if (substr($arr[0], 0, 1) == '!') {
        if (!acl_check(substr($arr[0], 1), $arr[1], '', $arr[$i])) return TRUE;
      }
      else {
        if (acl_check($arr[0], $arr[1], '', $arr[$i])) return TRUE;
      }
    }
  }
  else {
    if (substr($arr[0], 0, 1) == '!') {
      if (!acl_check(substr($arr[0], 1), $arr[1])) return TRUE;
    }
    else {
      if (acl_check($arr[0], $arr[1])) return TRUE;
    }
  }
  return FALSE;
}

function menu_apply_restrictions(&$menu_list_src,&$menu_list_updated)
{
    for ($idx=0; $idx<count($menu_list_src); $idx++)
    {
        $srcEntry = $menu_list_src[$idx];
        $includeEntry = true;

        // If the entry has an ACL Requirements(currently only support loose), then test
        if (isset($srcEntry->acl_req))
        {
            if (is_array($srcEntry->acl_req[0]))
            {
                $noneSet = true;
                for ($aclIdx=0; $aclIdx<count($srcEntry->acl_req); $aclIdx++)
                {
                    if (menu_acl_check($srcEntry->acl_req[$aclIdx])) {
                        $noneSet = false;
                    }
                }
                if ($noneSet)
                {
                    $includeEntry = false;
                }
            }
            else
            {
                if (!menu_acl_check($srcEntry->acl_req))
                {
                    $includeEntry = false;
                }
            }
        }

        // If the entry has loose global setting requirements, check
        // Note that global_req is a loose check (if more than 1 global, only 1 needs to pass to show the menu item)
        if (isset($srcEntry->global_req))
        {
            if (is_array($srcEntry->global_req))
            {
                $noneSet = true;
                for ($globalIdx=0; $globalIdx<count($srcEntry->global_req); $globalIdx++)
                {
                    $curSetting = $srcEntry->global_req[$globalIdx];
                    // ! at the start of the string means test the negation
                    if (substr($curSetting,0,1) === '!')
                    {
                        $curSetting = substr($curSetting,1);
                        // If the global isn't set at all, or if it is false, then show it
                        if (!isset($GLOBALS[$curSetting]) || !$GLOBALS[$curSetting])
                        {
                            $noneSet = false;
                        }
                    }
                    else
                    {
                        // If the setting is both set and true, then show it
                        if (isset($GLOBALS[$curSetting]) && $GLOBALS[$curSetting])
                        {
                            $noneSet = false;
                        }
                    }

                }
                if ($noneSet)
                {
                    $includeEntry = false;
                }
            }
            else
            {
                // ! at the start of the string means test the negation
                if (substr($srcEntry->global_req,0,1) === '!')
                {
                    $globalSetting=substr($srcEntry->global_req,1);
                    // If the setting is both set and true, then skip this entry
                    if (isset($GLOBALS[$globalSetting]) && $GLOBALS[$globalSetting])
                    {
                        $includeEntry = false;
                    }
                }
                else
                {
                    // If the global isn't set at all, or if it is false then skip the entry
                    if (!isset($GLOBALS[$srcEntry->global_req]) || !$GLOBALS[$srcEntry->global_req])
                    {
                        $includeEntry = false;
                    }
                }
            }
        }

        // If the entry has strict global setting requirements, check
        // Note that global_req_strict is a strict check (if more than 1 global, they all need to pass to show the menu item)
        if (isset($srcEntry->global_req_strict))
        {
            if (is_array($srcEntry->global_req_strict))
            {
                $allSet = true;
                for ($globalIdx=0; $globalIdx<count($srcEntry->global_req_strict); $globalIdx++)
                {
                    $curSetting = $srcEntry->global_req_strict[$globalIdx];
                    // ! at the start of the string means test the negation
                    if (substr($curSetting,0,1) === '!')
                    {
                        $curSetting = substr($curSetting,1);
                        // If the setting is both set and true, then do not show it
                        if (isset($GLOBALS[$curSetting]) && $GLOBALS[$curSetting])
                        {
                            $allSet = false;
                        }
                    }
                    else
                    {
                        // If the global isn't set at all, or if it is false, then do not show it
                        if (!isset($GLOBALS[$curSetting]) || !$GLOBALS[$curSetting])
                        {
                            $allSet = false;
                        }
                    }

                }
                if (!$allSet)
                {
                    $includeEntry = false;
                }
            }
            else
            {
                // ! at the start of the string means test the negation
                if (substr($srcEntry->global_req_strict,0,1) === '!')
                {
                    $globalSetting=substr($srcEntry->global_req_strict,1);
                    // If the setting is both set and true, then skip this entry
                    if (isset($GLOBALS[$globalSetting]) && $GLOBALS[$globalSetting])
                    {
                        $includeEntry = false;
                    }
                }
                else
                {
                    // If the global isn't set at all, or if it is false then skip the entry
                    if (!isset($GLOBALS[$srcEntry->global_req_strict]) || !$GLOBALS[$srcEntry->global_req_strict])
                    {
                        $includeEntry = false;
                    }
                }
            }
        }

        if(isset($srcEntry->children))
        {
            // Iterate through and check the child elements
            $checked_children=array();
            menu_apply_restrictions($srcEntry->children,$checked_children);
            $srcEntry->children=$checked_children;
        }

        if(!isset($srcEntry->url))
        {
            // If this is a header only entry, and there are no child elements, then don't include it in the list.
            if(count($srcEntry->children)===0)
            {
                $includeEntry=false;
            }
        }
        if($includeEntry)
        {

            array_push($menu_list_updated,$srcEntry);
        }
    }
}
