<?php
// +-----------------------------------------------------------------------------+
//OpenEMR - Open Source Electronic Medical Record
//    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
// Author:  Jacob T.Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+

namespace Zend\View\Helper\Openemr;
use Zend\View\Helper\AbstractHelper;
use Application\Listener\Listener;
/**
* Zend Module Menu
*/
class Menu extends AbstractHelper
{
  
  public function __construct()
  {
    $this->listener = new Listener;
  }
  
	/**
	* function getMenu
	* @param array $data module id and current controoler and current action
	* @return array menu details
	*/
	public function getMenu($data)
  {
    $module = isset($data['module']) ? $data['module'] : 0;
    $sql 	= "SELECT * FROM module_menu 
      WHERE status=1 AND module_id=?  
      ORDER BY group_id, order_id";
    $result = sqlStatement($sql,array($module));
    $arr = array();
    $i = 0;
    while ($row = sqlFetchArray($result)) {
      $arr[$i]['menu_id'] 	      = $this->escapeHtml($row['menu_id'],ENT_QUOTES);
      $arr[$i]['module_id']       = $this->escapeHtml($row['module_id'],ENT_QUOTES);
      $arr[$i]['menu_name'] 	    = $this->escapeHtml($row['menu_name'],ENT_QUOTES);
      $arr[$i]['parent_id'] 	    = $this->escapeHtml($row['parent_id'],ENT_QUOTES);
      $arr[$i]['controller_name'] = $this->escapeHtml($row['controller_name'],ENT_QUOTES);
      $arr[$i]['action'] 		      = $this->escapeHtml($row['action'],ENT_QUOTES);
      $arr[$i]['icon'] 		        = $this->escapeHtml($row['icon'],ENT_QUOTES);
      $arr[$i]['status'] 		      = $this->escapeHtml($row['status'],ENT_QUOTES);
      $arr[$i]['group_id'] 	      = $this->escapeHtml($row['group_id'],ENT_QUOTES);
      $arr[$i]['order_id'] 	      = $this->escapeHtml($row['order_id'],ENT_QUOTES);
      $arr[$i]['sub'] 		        = $row['parent_id'];
      if ($data['controller'] != '' && $data['action'] != '') {
        if (strtolower($data['controller']) == $row['controller_name'] && $data['action'] == $row['action']) {
          $key = $i;
          $arr[$i]['select'] = $this->escapeHtml($row['menu_id'],ENT_QUOTES);
          $group = $row['group_id'];
        }
      }
      $arr[$i]['child'] = $this->childCount($row['menu_id']);
      $i++;
    }
    if ($data['controller'] != '' && $data['action'] != '') {
      //$arr = $this->arrayMoveTop($arr, $key);
    }
    /**
    * selected menu item if child 
    * child menu shifted to main menu
    */
    for ($i = 0; $i <= count($arr); $i++) {
      if ($group != '' && $arr[$i]['group_id'] == $group) {
        if (isset($arr[$i]['select'])) {
          $arr[$i]['sub'] = 0;
        } else {
          $arr[$i]['sub'] = 1;
        }
      } 
    }
    return $arr;
  }
  
	/**
	* selected menu item at the top of the array
	* @param array $array
	* @param int $key selected menu id
	* @return array $array
	*/
	public function arrayMoveTop(&$array, $key) {
    $temp = array($key => $array[$key]);
    unset($array[$key]);
    $tmpArr = array();
    if (!empty($temp[$key]['menu_id'])) {
      $array = $temp + $array; 
    }
    foreach ($array as $k => $v) {
      array_push($tmpArr, $v);
    }
    return $array;
	}
	
	/**
	* Count submenus
	* @param int $id menu id
	* @return int count
	*/
	public function childCount($id) {
    $sql 	= "SELECT count(*) as child FROM module_menu WHERE status=1 AND parent_id=?";
    $result = sqlStatement($sql,array($id));
    $count = sqlFetchArray($result);
    return $count['child'];
	}
  
}