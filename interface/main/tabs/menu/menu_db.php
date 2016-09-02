<?php
/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
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
 * @link    http://www.open-emr.org
 */

function menu_entry_to_object($row)
{
    $retval=new stdClass();
    foreach($row as $key=>$value)
    {
        $retval->$key=$value;
    }
    $retval->requirement=intval($retval->requirement);
    $retval->children=array();
    if($retval->url==null)
    {
        unset($retval->url);
    }
    return $retval;
}
function load_menu($menu_set)
{
    
    $menuTables=" SHOW TABLES LIKE ?";
    $res=sqlQuery($menuTables,array("menu_trees"));
    if($res===false)
    {
        return array();
    }

    $menuQuery=" SELECT * FROM menu_trees, menu_entries WHERE menu_trees.entry_id=menu_entries.id AND menu_set=? ORDER BY parent, seq";
    $res=sqlStatement($menuQuery,array($menu_set));
    
    $retval=array();
    $entries=array();
    $parent_not_found=array();
    while($row=  sqlFetchArray($res))
    {
        $entries[$row['entry_id']]=menu_entry_to_object($row);
        if(empty($row['parent']))
        {
            array_push($retval,$entries[$row['entry_id']]);
        }
        else
        {
            if(isset($entries[$row['parent']]))
            {
                $parent=$entries[$row['parent']];
                array_push($parent->children,$entries[$row['entry_id']]);
                
            }
            else
            {
                array_push($parent_not_found,$entries[$row['entry_id']]);
            }
        }
    }
    foreach($parent_not_found as $row)
    {
            if(isset($entries[$row->parent]))
            {
                $parent=$entries[$row->parent];;
                array_push($parent->children,$row);
            }
            else
            {
                array_push($parent_not_found2,$row);
            }
        
    }
    return json_decode(json_encode($retval));       
}
